<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

define('DB_HOST', 'localhost');
define('DB_NAME', 'planatir_cms');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pi_file'])) {

    function fail(string $msg): array {
        return ['success' => false, 'message' => $msg];
    }

    function ok(string $msg, array $stats): array {
        return ['success' => true, 'message' => $msg, 'stats' => $stats];
    }

    do {
        // ── Validate Customer ID ──────────────────────────────────────────────
        if (empty($_POST['cus_id']) || !is_numeric($_POST['cus_id'])) {
            $result = fail('Please enter a valid Customer ID.');
            break;
        }
        $cus_id = (int) $_POST['cus_id'];

        // ── Validate PI Date ──────────────────────────────────────────────────
        if (empty($_POST['pi_date'])) {
            $result = fail('Please enter the PI Date.');
            break;
        }
        $piDate = date('Y-m-d 00:00:00', strtotime($_POST['pi_date']));
        if (!$piDate || $piDate === '1970-01-01 00:00:00') {
            $result = fail('Invalid PI Date. Please select a valid date.');
            break;
        }

        // ── Validate Mode ─────────────────────────────────────────────────────
        $mode = $_POST['import_mode'] ?? 'update';
        if (!in_array($mode, ['insert', 'update', 'both'])) {
            $result = fail('Invalid import mode selected.');
            break;
        }

        // ── Validate Upload ───────────────────────────────────────────────────
        if ($_FILES['pi_file']['error'] !== UPLOAD_ERR_OK) {
            $result = fail('Upload error. Please try again.');
            break;
        }

        $ext = strtolower(pathinfo($_FILES['pi_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $result = fail('Invalid file type. Only .xlsx / .xls allowed.');
            break;
        }

        // ── Read Excel ────────────────────────────────────────────────────────
        try {
            $spreadsheet = IOFactory::load($_FILES['pi_file']['tmp_name']);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            $result = fail('Failed to read Excel file: ' . $e->getMessage());
            break;
        }

        // ── Find Item Code and Unit Price columns ─────────────────────────────
        $headerRow = null;
        $itemCol   = null;
        $priceCol  = null;

        foreach ($rows as $i => $row) {
            foreach ($row as $j => $cell) {
                $v = strtolower(trim((string) $cell));
                if (in_array($v, ['item code', 'item  code', 'itemcode'])) {
                    $itemCol   = $j;
                    $headerRow = $i;
                }
                if (strpos($v, 'unit price') !== false || in_array($v, ['unitprice', 'price'])) {
                    $priceCol = $j;
                }
            }
            if ($headerRow !== null && $itemCol !== null && $priceCol !== null) break;
        }

        if ($headerRow === null || $itemCol === null || $priceCol === null) {
            $result = fail('Could not find "Item Code" or "Unit Price" columns in the Excel file.');
            break;
        }

        // ── Extract Data Rows ─────────────────────────────────────────────────
        $items = [];
        for ($i = $headerRow + 1; $i < count($rows); $i++) {
            $code  = trim((string) ($rows[$i][$itemCol]  ?? ''));
            $price = trim((string) ($rows[$i][$priceCol] ?? ''));
            if ($code === '' || !is_numeric($code) || $price === '' || !is_numeric($price)) continue;
            $items[] = ['icode' => (int) $code, 'price' => (float) $price];
        }

        if (empty($items)) {
            $result = fail('No valid item rows found in the Excel file.');
            break;
        }

        // ── Connect to DB ─────────────────────────────────────────────────────
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            $result = fail('Database connection failed: ' . $e->getMessage());
            break;
        }

        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];

        // ── MODE: UPDATE ONLY ─────────────────────────────────────────────────
        if ($mode === 'update') {
            $stmt = $pdo->prepare("
                UPDATE customer_items
                SET    price = :price, updated_at = :updated_at
                WHERE  icode = :icode AND cus_id = :cus_id
            ");
            foreach ($items as $item) {
                $stmt->execute([
                    ':price'      => $item['price'],
                    ':updated_at' => $piDate,
                    ':icode'      => $item['icode'],
                    ':cus_id'     => $cus_id,
                ]);
                $stmt->rowCount() > 0 ? $stats['updated']++ : $stats['skipped']++;
            }
        }

        // ── MODE: INSERT ONLY ─────────────────────────────────────────────────
        elseif ($mode === 'insert') {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO customer_items (cus_id, icode, price, updated_at)
                VALUES (:cus_id, :icode, :price, :updated_at)
            ");
            foreach ($items as $item) {
                $stmt->execute([
                    ':cus_id'     => $cus_id,
                    ':icode'      => $item['icode'],
                    ':price'      => $item['price'],
                    ':updated_at' => $piDate,
                ]);
                $stmt->rowCount() > 0 ? $stats['inserted']++ : $stats['skipped']++;
            }
        }

        // ── MODE: BOTH (UPSERT) ───────────────────────────────────────────────
        elseif ($mode === 'both') {
            // First check which icodes already exist for this customer
            $icodes      = array_column($items, 'icode');
            $placeholders = implode(',', array_fill(0, count($icodes), '?'));
            $checkStmt   = $pdo->prepare("
                SELECT icode FROM customer_items
                WHERE  cus_id = ? AND icode IN ($placeholders)
            ");
            $checkStmt->execute(array_merge([$cus_id], $icodes));
            $existingCodes = array_flip($checkStmt->fetchAll(PDO::FETCH_COLUMN));

            $insertStmt = $pdo->prepare("
                INSERT INTO customer_items (cus_id, icode, price, updated_at)
                VALUES (:cus_id, :icode, :price, :updated_at)
            ");
            $updateStmt = $pdo->prepare("
                UPDATE customer_items
                SET    price = :price, updated_at = :updated_at
                WHERE  icode = :icode AND cus_id = :cus_id
            ");

            foreach ($items as $item) {
                if (isset($existingCodes[$item['icode']])) {
                    // Exists → Update
                    $updateStmt->execute([
                        ':price'      => $item['price'],
                        ':updated_at' => $piDate,
                        ':icode'      => $item['icode'],
                        ':cus_id'     => $cus_id,
                    ]);
                    $stats['updated']++;
                } else {
                    // Not exists → Insert
                    $insertStmt->execute([
                        ':cus_id'     => $cus_id,
                        ':icode'      => $item['icode'],
                        ':price'      => $item['price'],
                        ':updated_at' => $piDate,
                    ]);
                    $stats['inserted']++;
                }
            }
        }

        // ── Build Result Message ──────────────────────────────────────────────
        $parts = [];
        if ($stats['inserted'] > 0) $parts[] = "{$stats['inserted']} inserted";
        if ($stats['updated']  > 0) $parts[] = "{$stats['updated']} updated";
        if ($stats['skipped']  > 0) $parts[] = "{$stats['skipped']} skipped";
        $result = ok('Done! ' . implode(', ', $parts) . '.', $stats);

    } while (false);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PI Price Importer</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 42px 40px;
            width: 100%;
            max-width: 580px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
        }

        .logo {
            width: 48px; height: 48px;
            background: #1a73e8;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .logo svg { width: 26px; height: 26px; fill: #fff; }

        h2 { font-size: 22px; color: #1a1a2e; margin-bottom: 6px; }
        .sub { font-size: 13px; color: #777; margin-bottom: 28px; }

        label.field-label {
            display: block;
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .5px;
            color: #555; margin-bottom: 8px;
        }

        .form-group { margin-bottom: 20px; }

        .form-row {
            display: flex; gap: 16px; margin-bottom: 20px;
        }
        .form-row .form-group { flex: 1; margin-bottom: 0; }

        input[type="number"],
        input[type="date"] {
            width: 100%; padding: 12px;
            border: 1px solid #d0d7e0; border-radius: 8px;
            font-size: 14px; color: #1a1a2e;
            transition: border-color .2s;
        }
        input[type="number"]:focus,
        input[type="date"]:focus {
            outline: none; border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,.1);
        }

        /* ── Mode Selector ─────────────────────────────────────────────────── */
        .mode-group { margin-bottom: 22px; }

        .mode-options {
            display: flex; gap: 10px;
        }

        .mode-option {
            flex: 1;
            position: relative;
        }
        .mode-option input[type="radio"] {
            position: absolute; opacity: 0; width: 0; height: 0;
        }
        .mode-option label {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 6px;
            padding: 14px 10px;
            border: 2px solid #d0d7e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all .2s;
            text-align: center;
            text-transform: none;
            letter-spacing: 0;
            font-size: 13px;
            font-weight: 600;
            color: #555;
        }
        .mode-option label .mode-icon { font-size: 22px; }
        .mode-option label .mode-desc { font-size: 11px; font-weight: 400; color: #888; }

        .mode-option input[type="radio"]:checked + label {
            border-color: #1a73e8;
            background: #f0f6ff;
            color: #1a73e8;
        }
        .mode-option input[type="radio"]:checked + label .mode-desc { color: #5a9aef; }
        .mode-option label:hover { border-color: #1a73e8; background: #f8fbff; }

        /* ── Drop Zone ─────────────────────────────────────────────────────── */
        .drop-zone {
            border: 2px dashed #c8d0db; border-radius: 10px;
            padding: 30px 20px; text-align: center;
            cursor: pointer; transition: border-color .2s, background .2s;
            margin-bottom: 22px; position: relative;
        }
        .drop-zone:hover, .drop-zone.dragover { border-color: #1a73e8; background: #f0f6ff; }
        .drop-zone input[type="file"] {
            position: absolute; inset: 0; opacity: 0;
            cursor: pointer; width: 100%; height: 100%;
        }
        .drop-zone .icon { font-size: 34px; margin-bottom: 8px; }
        .drop-zone p { font-size: 13px; color: #666; }
        .drop-zone p span { color: #1a73e8; font-weight: 600; }
        #file-name { font-size: 12px; color: #1a73e8; margin-top: 6px; font-weight: 600; min-height: 16px; }

        .mapping {
            background: #f4f8ff; border-radius: 8px;
            padding: 14px 16px; margin-bottom: 24px;
            font-size: 13px; color: #444;
        }
        .mapping .map-header {
            display: flex; justify-content: space-between;
            font-weight: 700; color: #1a1a2e;
            padding-bottom: 6px; border-bottom: 1px solid #dde5f0; margin-bottom: 6px;
        }
        .mapping .map-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .mapping .map-row span:last-child { color: #1a73e8; font-weight: 600; }

        .info-box {
            background: #e3f2fd; border-left: 4px solid #1a73e8;
            padding: 12px; border-radius: 4px;
            margin-bottom: 20px; font-size: 12px;
            color: #1a1a2e; line-height: 1.6;
        }

        button[type="submit"] {
            width: 100%; padding: 14px;
            background: #1a73e8; color: #fff;
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            transition: background .2s, transform .1s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        button[type="submit"]:hover  { background: #1558c0; }
        button[type="submit"]:active { transform: scale(.98); }

        /* ── Alert ─────────────────────────────────────────────────────────── */
        .alert {
            margin-top: 22px; padding: 16px;
            border-radius: 8px; font-size: 13px; line-height: 1.7;
        }
        .alert.success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #43a047; }
        .alert.error   { background: #ffebee; color: #c62828; border-left: 4px solid #ef5350; }

        .stat-row {
            display: flex; gap: 16px; margin-bottom: 8px;
        }
        .stat-badge {
            display: flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.6);
            border-radius: 6px; padding: 6px 12px;
            font-weight: 700; font-size: 15px;
        }
        .stat-badge.ins { color: #1565c0; }
        .stat-badge.upd { color: #2e7d32; }
        .stat-badge.skp { color: #e65100; }
    </style>
</head>
<body>
<div class="card">

    <div class="logo">
        <svg viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8 17v-2h8v2H8zm0-4v-2h8v2H8zm0-4V7h5v2H8z"/>
        </svg>
    </div>

    <h2>PI Price Importer</h2>
    <p class="sub">Upload the PI Excel file to update customer item prices automatically.</p>

    <form method="POST" enctype="multipart/form-data">

        <!-- Customer ID + PI Date -->
        <div class="form-row">
            <div class="form-group">
                <label class="field-label">Customer ID</label>
                <input type="number" name="cus_id" id="cus_id" placeholder="Enter customer ID"
                       value="<?= htmlspecialchars($_POST['cus_id'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="field-label">PI Date</label>
                <input type="date" name="pi_date" id="pi_date"
                       value="<?= htmlspecialchars($_POST['pi_date'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>

        <!-- Import Mode -->
        <div class="mode-group">
            <label class="field-label">Import Mode</label>
            <div class="mode-options">

                <div class="mode-option">
                    <input type="radio" name="import_mode" id="mode_update" value="update"
                           <?= (($_POST['import_mode'] ?? 'update') === 'update') ? 'checked' : '' ?>>
                    <label for="mode_update">
                        <span class="mode-icon">✏️</span>
                        Update Only
                        <span class="mode-desc">Update existing records only</span>
                    </label>
                </div>

                <div class="mode-option">
                    <input type="radio" name="import_mode" id="mode_insert" value="insert"
                           <?= (($_POST['import_mode'] ?? '') === 'insert') ? 'checked' : '' ?>>
                    <label for="mode_insert">
                        <span class="mode-icon">➕</span>
                        Insert Only
                        <span class="mode-desc">Add new records only</span>
                    </label>
                </div>

                <div class="mode-option">
                    <input type="radio" name="import_mode" id="mode_both" value="both"
                           <?= (($_POST['import_mode'] ?? '') === 'both') ? 'checked' : '' ?>>
                    <label for="mode_both">
                        <span class="mode-icon">🔄</span>
                        Both (Upsert)
                        <span class="mode-desc">Insert new + update existing</span>
                    </label>
                </div>

            </div>
        </div>

        <!-- File Upload -->
        <label class="field-label">PI Excel File (.xlsx / .xls)</label>
        <div class="drop-zone" id="dropZone">
            <input type="file" name="pi_file" id="pi_file" accept=".xlsx,.xls" required>
            <div class="icon">&#128194;</div>
            <p><span>Click to browse</span> or drag &amp; drop here</p>
            <div id="file-name"></div>
        </div>

        <div class="info-box" id="modeInfo">
            <!-- Dynamically updated by JS -->
        </div>

        <label class="field-label">Field Mapping</label>
        <div class="mapping">
            <div class="map-header">
                <span>Excel Column</span><span>DB Column</span>
            </div>
            <div class="map-row"><span>Item Code (numeric)</span><span>icode</span></div>
            <div class="map-row"><span>Unit Price</span><span>price</span></div>
            <div class="map-row"><span>PI Date (manual input)</span><span>updated_at</span></div>
            <div class="map-row"><span>Customer ID (input)</span><span>cus_id</span></div>
        </div>

        <button type="submit">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                <path d="M19 9h-4V3H9v6H5l7 7 7-7zm-8 10v2H5v-2h6zm4 0h6v2h-6v-2z"/>
            </svg>
            Import &amp; Update Prices
        </button>

    </form>

    <?php if ($result !== null): ?>
        <?php if ($result['success']): ?>
            <div class="alert success">
                <div class="stat-row">
                    <?php if ($result['stats']['inserted'] > 0): ?>
                        <div class="stat-badge ins">➕ <?= $result['stats']['inserted'] ?> Inserted</div>
                    <?php endif; ?>
                    <?php if ($result['stats']['updated'] > 0): ?>
                        <div class="stat-badge upd">✏️ <?= $result['stats']['updated'] ?> Updated</div>
                    <?php endif; ?>
                    <?php if ($result['stats']['skipped'] > 0): ?>
                        <div class="stat-badge skp">⏭️ <?= $result['stats']['skipped'] ?> Skipped</div>
                    <?php endif; ?>
                </div>
                <?= htmlspecialchars($result['message']) ?>
            </div>
        <?php else: ?>
            <div class="alert error">
                &#10060; <?= htmlspecialchars($result['message']) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<script>
    // ── File drop ────────────────────────────────────────────────────────────
    const input    = document.getElementById('pi_file');
    const dropZone = document.getElementById('dropZone');
    const fileName = document.getElementById('file-name');

    input.addEventListener('change', () => {
        fileName.textContent = input.files[0]?.name ?? '';
    });
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault(); dropZone.classList.remove('dragover');
        input.files = e.dataTransfer.files;
        fileName.textContent = input.files[0]?.name ?? '';
    });

    // ── Mode info box ────────────────────────────────────────────────────────
    const modeInfo = {
        update: `<strong>✏️ Update Only:</strong><br>
                 • Only existing records that match the item code + customer ID will be updated<br>
                 • New item codes not found in the DB will be skipped<br>
                 • Safe to use when you only want to refresh prices`,
        insert: `<strong>➕ Insert Only:</strong><br>
                 • Only new records will be inserted into the DB<br>
                 • Item codes that already exist for this customer are skipped<br>
                 • Use this when adding a new customer's price list for the first time`,
        both:   `<strong>🔄 Both (Upsert):</strong><br>
                 • Existing records are updated with the new price<br>
                 • New item codes not yet in the DB are inserted automatically<br>
                 • Recommended for keeping a full price list in sync`
    };

    function updateInfo() {
        const selected = document.querySelector('input[name="import_mode"]:checked')?.value ?? 'update';
        document.getElementById('modeInfo').innerHTML = modeInfo[selected];
    }

    document.querySelectorAll('input[name="import_mode"]').forEach(r => {
        r.addEventListener('change', updateInfo);
    });

    updateInfo(); // run on page load
</script>
</body>
</html>