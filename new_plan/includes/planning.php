<?php
// ============================================================
// PLANNING ENGINE
// Core scheduling logic: picks best start time based on
// mold availability + cavity availability, then saves the plan.
// ============================================================
require_once __DIR__ . '/db.php';

// ── Search Tires ──────────────────────────────────────────────
function searchTires(string $query): array {
    $db = getDB();
    $q = '%' . $query . '%';
    $stmt = $db->prepare("
        SELECT t.id, t.icode, t.description, t.time_taken,
               GROUP_CONCAT(DISTINCT tm.mold_id ORDER BY tm.mold_id SEPARATOR ', ') AS compatible_molds
        FROM tire t
        LEFT JOIN tire_mold tm ON tm.icode = t.icode
        WHERE t.description LIKE :q OR t.icode LIKE :q2
        GROUP BY t.id, t.icode, t.description, t.time_taken
        LIMIT 50
    ");
    $stmt->execute([':q' => $q, ':q2' => $q]);
    return $stmt->fetchAll();
}

// ── Get Molds for a Tire ──────────────────────────────────────
function getMoldsForTire(int $icode): array {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT m.mold_id, m.mold_name, m.availability_date, m.is_available,
               GROUP_CONCAT(DISTINCT mp.press_id ORDER BY mp.press_id SEPARATOR ',') AS press_ids
        FROM tire_mold tm
        JOIN mold m ON m.mold_id = tm.mold_id
        LEFT JOIN mold_press mp ON mp.mold_id = m.mold_id
        WHERE tm.icode = :icode
        GROUP BY m.mold_id, m.mold_name, m.availability_date, m.is_available
        ORDER BY m.availability_date ASC
    ");
    $stmt->execute([':icode' => $icode]);
    return $stmt->fetchAll();
}

// ── Get Cavities for a Press ──────────────────────────────────
function getCavitiesForPress(int $press_id): array {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.cavity_id, c.cavity_name, c.availability_date, c.is_available, c.cavity_group_id
        FROM press_cavity pc
        JOIN cavity c ON c.cavity_id = pc.cavity_id
        WHERE pc.press_id = :press_id AND c.is_available = 1
        ORDER BY c.availability_date ASC
    ");
    $stmt->execute([':press_id' => $press_id]);
    return $stmt->fetchAll();
}

// ── Get All Presses ───────────────────────────────────────────
function getAllPresses(): array {
    $db = getDB();
    $stmt = $db->query("
        SELECT DISTINCT pc.press_id,
               CONCAT('Press-', pc.press_id) AS press_name,
               COUNT(pc.cavity_id) AS cavity_count
        FROM press_cavity pc
        WHERE pc.press_id > 0
        GROUP BY pc.press_id
        ORDER BY pc.press_id
    ");
    return $stmt->fetchAll();
}

// ── Calculate Best Schedule ───────────────────────────────────
/**
 * Given a tire's icode and its time_taken (minutes),
 * find the earliest possible start time across all compatible
 * molds → presses → cavities.
 *
 * Returns an array of options sorted by planned_start ASC.
 */
function calculateScheduleOptions(int $icode, int $time_taken, ?int $preferred_press = null): array {
    $molds    = getMoldsForTire($icode);
    $options  = [];
    $now      = new DateTime();

    foreach ($molds as $mold) {
        $mold_ready = new DateTime($mold['availability_date'] ?? 'now');
        // Mold can't start before now
        if ($mold_ready < $now) $mold_ready = clone $now;

        $press_ids = array_filter(explode(',', $mold['press_ids'] ?? ''));

        foreach ($press_ids as $press_id) {
            $press_id = (int) $press_id;
            if ($preferred_press && $press_id !== $preferred_press) continue;

            $cavities = getCavitiesForPress($press_id);
            foreach ($cavities as $cavity) {
                $cav_ready = new DateTime($cavity['availability_date'] ?? 'now');
                if ($cav_ready < $now) $cav_ready = clone $now;

                // Effective start = latest of mold_ready and cavity_ready
                $start = max($mold_ready, $cav_ready);

                $end = clone $start;
                $end->modify("+{$time_taken} minutes");

                $options[] = [
                    'mold_id'      => $mold['mold_id'],
                    'mold_name'    => $mold['mold_name'],
                    'press_id'     => $press_id,
                    'press_name'   => "Press-{$press_id}",
                    'cavity_id'    => $cavity['cavity_id'],
                    'cavity_name'  => $cavity['cavity_name'],
                    'mold_ready'   => $mold_ready->format('Y-m-d H:i'),
                    'cavity_ready' => $cav_ready->format('Y-m-d H:i'),
                    'planned_start'=> $start->format('Y-m-d H:i'),
                    'planned_end'  => $end->format('Y-m-d H:i'),
                    'wait_minutes' => (int)(($start->getTimestamp() - $now->getTimestamp()) / 60),
                ];
            }
        }
    }

    // Sort by planned_start, then by wait_minutes
    usort($options, fn($a, $b) =>
        strcmp($a['planned_start'], $b['planned_start']) ?:
        ($a['wait_minutes'] - $b['wait_minutes'])
    );

    return array_slice($options, 0, 30); // Top 30 options
}

// ── Save Plan ──────────────────────────────────────────────────
function savePlan(array $data): int {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO production_plan
            (icode, tire_description, mold_id, press_id, cavity_id, cavity_name,
             planned_start, planned_end, time_taken, notes)
        VALUES
            (:icode, :desc, :mold_id, :press_id, :cavity_id, :cavity_name,
             :start, :end, :time_taken, :notes)
    ");
    $stmt->execute([
        ':icode'       => $data['icode'],
        ':desc'        => $data['tire_description'],
        ':mold_id'     => $data['mold_id'],
        ':press_id'    => $data['press_id'],
        ':cavity_id'   => $data['cavity_id'],
        ':cavity_name' => $data['cavity_name'],
        ':start'       => $data['planned_start'],
        ':end'         => $data['planned_end'],
        ':time_taken'  => $data['time_taken'],
        ':notes'       => $data['notes'] ?? null,
    ]);
    return (int) $db->lastInsertId();
}

// ── Get Production Plan ───────────────────────────────────────
function getProductionPlan(string $date_from = '', string $date_to = '', string $status = ''): array {
    $db   = getDB();
    $sql  = "SELECT * FROM production_plan WHERE 1=1";
    $params = [];
    if ($date_from) { $sql .= " AND planned_start >= :from"; $params[':from'] = $date_from . ' 00:00:00'; }
    if ($date_to)   { $sql .= " AND planned_start <= :to";   $params[':to']   = $date_to   . ' 23:59:59'; }
    if ($status)    { $sql .= " AND status = :status"; $params[':status'] = $status; }
    $sql .= " ORDER BY planned_start ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ── Update Plan Status ─────────────────────────────────────────
function updatePlanStatus(int $id, string $status): bool {
    $db = getDB();
    $stmt = $db->prepare("UPDATE production_plan SET status=:s WHERE id=:id");
    return $stmt->execute([':s' => $status, ':id' => $id]);
}

// ── Delete Plan ────────────────────────────────────────────────
function deletePlan(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM production_plan WHERE id=:id");
    return $stmt->execute([':id' => $id]);
}

// ── Dashboard Stats ────────────────────────────────────────────
function getDashboardStats(): array {
    $db = getDB();
    return [
        'total_planned'    => $db->query("SELECT COUNT(*) FROM production_plan WHERE status='planned'")->fetchColumn(),
        'in_progress'      => $db->query("SELECT COUNT(*) FROM production_plan WHERE status='in_progress'")->fetchColumn(),
        'completed_today'  => $db->query("SELECT COUNT(*) FROM production_plan WHERE status='completed' AND DATE(planned_end)=CURDATE()")->fetchColumn(),
        'total_molds'      => $db->query("SELECT COUNT(*) FROM mold WHERE is_available=1")->fetchColumn(),
        'total_cavities'   => $db->query("SELECT COUNT(*) FROM cavity WHERE is_available=1")->fetchColumn(),
        'total_tires'      => $db->query("SELECT COUNT(DISTINCT icode) FROM tire")->fetchColumn(),
    ];
}
