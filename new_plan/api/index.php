<?php
// ============================================================
// API ROUTER  –  /api/index.php
// Handles all AJAX requests from the frontend
// ============================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/planning.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // ── Search tires ──────────────────────────────────────
        case 'search_tires':
            $q = trim(getInput('q', ''));
            if (strlen($q) < 2) jsonResponse(['tires' => []]);
            jsonResponse(['tires' => searchTires($q)]);

        // ── Get molds for tire ────────────────────────────────
        case 'get_molds':
            $icode = (int) getInput('icode');
            jsonResponse(['molds' => getMoldsForTire($icode)]);

        // ── Get all presses ───────────────────────────────────
        case 'get_presses':
            jsonResponse(['presses' => getAllPresses()]);

        // ── Get cavities for press ────────────────────────────
        case 'get_cavities':
            $press_id = (int) getInput('press_id');
            jsonResponse(['cavities' => getCavitiesForPress($press_id)]);

        // ── Calculate schedule options ────────────────────────
        case 'schedule':
            $icode      = (int) getInput('icode');
            $time_taken = (int) getInput('time_taken');
            $press      = (int) getInput('press_id', 0);
            if (!$icode || !$time_taken) jsonResponse(['error' => 'Missing icode or time_taken'], 400);
            $options = calculateScheduleOptions($icode, $time_taken, $press ?: null);
            jsonResponse(['options' => $options, 'count' => count($options)]);

        // ── Save a plan ───────────────────────────────────────
        case 'save_plan':
            $required = ['icode','tire_description','mold_id','press_id','cavity_id',
                         'cavity_name','planned_start','planned_end','time_taken'];
            $data = [];
            foreach ($required as $f) {
                $val = getInput($f);
                if ($val === null || $val === '') jsonResponse(['error' => "Missing: $f"], 400);
                $data[$f] = $val;
            }
            $data['notes'] = getInput('notes', '');
            $id = savePlan($data);
            jsonResponse(['success' => true, 'id' => $id]);

        // ── Get production plan (schedule view) ───────────────
        case 'get_plan':
            $from   = getInput('from', '');
            $to     = getInput('to', '');
            $status = getInput('status', '');
            jsonResponse(['plans' => getProductionPlan($from, $to, $status)]);

        // ── Update status ──────────────────────────────────────
        case 'update_status':
            $id     = (int) getInput('id');
            $status = getInput('status', '');
            $allowed = ['planned','in_progress','completed','cancelled'];
            if (!$id || !in_array($status, $allowed)) jsonResponse(['error' => 'Invalid params'], 400);
            updatePlanStatus($id, $status);
            jsonResponse(['success' => true]);

        // ── Delete plan ────────────────────────────────────────
        case 'delete_plan':
            $id = (int) getInput('id');
            if (!$id) jsonResponse(['error' => 'Missing id'], 400);
            deletePlan($id);
            jsonResponse(['success' => true]);

        // ── Dashboard stats ────────────────────────────────────
        case 'stats':
            jsonResponse(getDashboardStats());

        default:
            jsonResponse(['error' => 'Unknown action'], 404);
    }
} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
