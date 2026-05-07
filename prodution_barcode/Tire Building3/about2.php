<?php
session_start();

// ─── Database connection ─────────────────────────────────────────────────────
$host     = "localhost";
$dbname   = "planatir_task_managemen";
$username = "planatir_task_managemen";
$password = "Bishan@1919";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add required columns if they don't exist
$scanTables = ['qr_scanned_data', 'qr_scanned_data2', 'qr_scanned_data3'];
foreach ($scanTables as $t) {
    $conn->query("ALTER TABLE $t ADD COLUMN IF NOT EXISTS weight_difference DECIMAL(10,2)");
    $conn->query("ALTER TABLE $t ADD COLUMN IF NOT EXISTS category_weight   DECIMAL(10,2)");
}

// ─── Helper functions ────────────────────────────────────────────────────────

function getTotalCategoryWeight($conn, $jobNumber, $batchNumber) {
    $tables = ['qr_scanned_data', 'qr_scanned_data2', 'qr_scanned_data3'];
    $totalWeight = 0;
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT SUM(category_weight) AS total_weight FROM $table WHERE job_number = ? AND batch_number = ?");
        $stmt->bind_param("ss", $jobNumber, $batchNumber);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $totalWeight += $data['total_weight'] ?? 0;
        $stmt->close();
    }
    return $totalWeight;
}

function getMatchingDetails($conn, $jobNumber, $batchNumber, $tableName) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS quantity, SUM(category_weight) AS table_total_weight FROM $tableName WHERE job_number = ? AND batch_number = ?");
    $stmt->bind_param("ss", $jobNumber, $batchNumber);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return [
        'quantity'           => $data['quantity'],
        'table_total_weight' => $data['table_total_weight'] ?? 0
    ];
}

function getActualWeight($conn, $compoundName) {
    $stmt = $conn->prepare("SELECT actweigt FROM compound_data WHERE compound_name = ?");
    $stmt->bind_param("s", $compoundName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) return $result->fetch_assoc()['actweigt'];
    return null;
}

function getCompoundWeights($conn, $tireCode) {
    $stmt = $conn->prepare("SELECT a,b,c,d,e,f,g,h,i,j,k,l,m,o,p,q,r FROM bom_new WHERE icode = ?");
    $stmt->bind_param("s", $tireCode);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) return $result->fetch_assoc();
    return null;
}

function getCategoryWeight($weights, $category) {
    $category = strtolower($category);
    return isset($weights[$category]) ? $weights[$category] : null;
}

function processQRData($qrData) {
    if (preg_match("/\|CN-([^|]+)\|BN-([^|]+)\|JN-([^|]+)/i", $qrData, $m)) {
        return ['compound_name' => $m[1], 'batch_number' => $m[2], 'job_number' => $m[3]];
    }
    return false;
}

// ════════════════════════════════════════════════════════════════════════════
// SECTION 1 — TIRE DETAILS ENTRY logic (top of page)
// ════════════════════════════════════════════════════════════════════════════

$serialNumber = '';
$tireCode     = '';

if (isset($_GET['serialNumber'])) {
    $serialNumber = htmlspecialchars($_GET['serialNumber']);

    $stmt = $conn->prepare("SELECT tireCode FROM tire_data WHERE serialNumber = ?");
    $stmt->bind_param("s", $serialNumber);
    $stmt->execute();
    $result   = $stmt->get_result();
    $tireCode = ($result->num_rows > 0) ? $result->fetch_assoc()['tireCode'] : 'N/A';
    $stmt->close();
} else {
    die("Error: Serial number not provided.");
}

// Handle QR preview POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['qrData']) && isset($_POST['scanner'])) {
    $qrData        = htmlspecialchars($_POST['qrData']);
    $scanner       = $_POST['scanner'];
    $processedData = processQRData($qrData);

    if ($processedData) {
        $_SESSION[$scanner . '_data'] = [
            'qr_data'        => $qrData,
            'processed_data' => $processedData,
            'tire_code'      => $tireCode
        ];
        $cw = getCompoundWeights($conn, $tireCode);
        if ($cw) $_SESSION[$scanner . '_weights'] = $cw;

        $stmt = $conn->prepare("SELECT cat, c_cat FROM Compound_name WHERE compound_name = ?");
        $stmt->bind_param("s", $processedData['compound_name']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION[$scanner . '_cat']   = $row['cat'];
            $_SESSION[$scanner . '_c_cat'] = $row['c_cat'];
        } else {
            $_SESSION[$scanner . '_cat']   = "Compound name not found.";
            $_SESSION[$scanner . '_c_cat'] = "Unknown";
        }
        $stmt->close();
    } else {
        $_SESSION[$scanner . '_error'] = "Invalid QR code format. Please try again.";
    }
}

// Handle confirm insert POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_insert'])) {
    $scanner        = $_POST['confirm_scanner'];
    $CN             = $_POST['compound_name'];
    $BN             = $_POST['batch_number'];
    $JN             = $_POST['job_number'];
    $TC             = $_POST['tire_code'];
    $catValue       = $_SESSION[$scanner . '_cat'];
    $compWeights    = $_SESSION[$scanner . '_weights'];
    $categoryWeight = getCategoryWeight($compWeights, $catValue);
    $actualWeight   = getActualWeight($conn, $CN);

    $table_name = 'qr_scanned_data';
    if ($scanner == 'scanner2') $table_name = 'qr_scanned_data2';
    if ($scanner == 'scanner3') $table_name = 'qr_scanned_data3';

    $det              = getMatchingDetails($conn, $JN, $BN, $table_name);
    $newTotal         = $det['table_total_weight'] + $categoryWeight;
    $weightDifference = $actualWeight - $newTotal;

    $stmt = $conn->prepare("INSERT INTO $table_name (compound_name, batch_number, job_number, serial_number, tire_code, weight_difference, category_weight) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdd", $CN, $BN, $JN, $serialNumber, $TC, $weightDifference, $categoryWeight);

    if ($stmt->execute()) {
        $_SESSION[$scanner . '_success'] = "Data successfully inserted into the database.";
        unset($_SESSION[$scanner . '_data']);
        if ($weightDifference < 0) {
            header("Location: negative_weight.php?serialNumber=" . urlencode($serialNumber));
            exit();
        }
    } else {
        $_SESSION[$scanner . '_error'] = "Database insert failed: " . $stmt->error;
    }
    $stmt->close();
}

// Auto-focus: first scanner without active preview
$autoFocusScanner = 1;
for ($i = 1; $i <= 3; $i++) {
    if (!isset($_SESSION["scanner{$i}_data"])) {
        $autoFocusScanner = $i;
        break;
    }
}

// ════════════════════════════════════════════════════════════════════════════
// SECTION 2 — SERIAL NUMBER VALIDATION logic (bottom of page)
// ════════════════════════════════════════════════════════════════════════════

$val_message            = '';
$val_buttonEnabled      = false;
$val_tireCode           = '';
$val_scannedCount       = 0;
$val_baseRequiredDisplay = true;
$val_row1 = $val_row2 = $val_row3 = ['count' => 0];
$val_serialNumber = $serialNumber; // default to current serial

// If the validation form was submitted with a different serial
if (isset($_GET['checkSerial']) && !empty($_GET['checkSerial'])) {
    $val_serialNumber = htmlspecialchars($_GET['checkSerial']);
} 

// Always run validation check for the current serial number
if (!empty($val_serialNumber)) {
    $stmt = $conn->prepare("SELECT tireCode FROM tire_data WHERE serialNumber = ?");
    $stmt->bind_param("s", $val_serialNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $val_tireCode = $result->fetch_assoc()['tireCode'];

        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM qr_scanned_data  WHERE serial_number = ?");
        $stmt->bind_param("s", $val_serialNumber); $stmt->execute();
        $val_row1 = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM qr_scanned_data2 WHERE serial_number = ?");
        $stmt->bind_param("s", $val_serialNumber); $stmt->execute();
        $val_row2 = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM qr_scanned_data3 WHERE serial_number = ?");
        $stmt->bind_param("s", $val_serialNumber); $stmt->execute();
        $val_row3 = $stmt->get_result()->fetch_assoc();

        $val_scannedCount = ($val_row1['count'] > 0 ? 1 : 0)
                          + ($val_row2['count'] > 0 ? 1 : 0)
                          + ($val_row3['count'] > 0 ? 1 : 0);

        // Check if base component is required
        $baseRequired = true;
        if (!empty($val_tireCode)) {
            $stmt = $conn->prepare("SELECT b, c FROM bom_new WHERE icode = ?");
            $stmt->bind_param("s", $val_tireCode);
            $stmt->execute();
            $bom = $stmt->get_result();
            if ($bom->num_rows > 0) {
                $bd = $bom->fetch_assoc();
                if (empty($bd['b']) && empty($bd['c'])) $baseRequired = false;
            } else {
                $baseRequired = false;
            }
            $val_baseRequiredDisplay = $baseRequired;
        }

        if ($baseRequired) {
            $val_buttonEnabled = ($val_scannedCount == 3);
        } else {
            $val_buttonEnabled = (($val_row2['count'] > 0 ? 1 : 0) + ($val_row3['count'] > 0 ? 1 : 0) == 2);
        }

        $val_message = $val_buttonEnabled
            ? '<div class="val-alert val-success"><i class="fas fa-check-circle"></i> All required components have been successfully scanned. You may proceed to the next step.</div>'
            : '<div class="val-alert val-error"><i class="fas fa-exclamation-circle"></i> Not all required components have been scanned yet. Please complete the scanning process.</div>';

    } else {
        $val_message = '<div class="val-alert val-error"><i class="fas fa-exclamation-circle"></i> Serial number not found in the database.</div>';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="assets/custom/images/shortcut.png">
    <title>Tire Details Entry</title>

    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i|Cantarell:400,700" rel="stylesheet">
    <link href="assets/vendor/fontawesome/css/fontawesome-all.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        /* ── Shared container ── */
        .main-container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 20px;
            background-color: #F28018;
            border-radius: 8px;
        }

        /* ── Scanner sections ── */
        .scanner-section {
            margin-bottom: 20px;
            padding: 20px;
            border: 2px solid #CCCCCC;
            border-radius: 8px;
            background-color: #ffffff;
            transition: box-shadow 0.3s, border-color 0.3s;
        }
        .scanner-section:hover { box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .scanner-section.active-scanner {
            border-color: #F28018;
            box-shadow: 0 0 14px rgba(242,128,24,0.3);
        }
        .scanner-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #000;
            font-family: 'Cantarell', sans-serif;
            padding-bottom: 10px;
            border-bottom: 2px solid #F28018;
        }
        .scan-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .scan-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s;
        }
        .scan-input:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 5px rgba(242,128,24,0.3);
        }
        .preview-button, .confirm-button {
            background-color: #000;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            white-space: nowrap;
        }
        .preview-button:hover, .confirm-button:hover { background-color: #333; }

        /* ── Preview box ── */
        .preview-box {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }
        .preview-title {
            font-weight: bold;
            color: #000;
            font-family: 'Cantarell', sans-serif;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #F28018;
        }
        .data-label {
            font-weight: bold;
            color: #000;
            font-family: 'Cantarell', sans-serif;
            min-width: 150px;
            display: inline-block;
        }
        .data-value { color: #333; font-family: 'Open Sans', sans-serif; }
        .weight-value { font-size: 1.2em; color: #F28018; font-weight: bold; }
        .positive-diff { color: #28a745; font-weight: bold; }
        .negative-diff { color: #dc3545; font-weight: bold; }

        .preview-data { padding: 20px; }
        .preview-data .row { margin-bottom: 20px; }
        .preview-data .col-md-4,
        .preview-data .col-md-12 {
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .preview-data .col-md-4 h4 { margin-top: 0; }

        /* ── Inline messages ── */
        .message {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-family: 'Open Sans', sans-serif;
        }
        .error-message   { background-color: #ffe6e6; color: #721c24; border-left: 4px solid #dc3545; }
        .success-message { background-color: #e6ffe6; color: #155724; border-left: 4px solid #28a745; }

        /* ── Category badges ── */
        .category-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            color: #fff;
            font-weight: bold;
            margin-left: 10px;
        }
        .category-0 { background-color: #6c757d; }
        .category-1 { background-color: #28a745; }
        .category-2 { background-color: #17a2b8; }
        .category-3 { background-color: #dc3545; }

        /* ════════════════════════════════════════
           VALIDATION SECTION (bottom)
        ════════════════════════════════════════ */
        .val-container {
            margin: 0 auto;
            max-width: 800px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .val-title {
            text-align: center;
            margin-bottom: 30px;
            color: #000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 20px;
            background-color: #F28018;
            border-radius: 8px;
        }
        .val-search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        .val-search-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .val-search-input:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 5px rgba(242,128,24,0.3);
        }
        .val-search-button {
            background-color: #000;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }
        .val-search-button:hover { background-color: #333; }

        .val-alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-family: 'Open Sans', sans-serif;
        }
        .val-success { background-color: #e6ffe6; color: #155724; border-left: 4px solid #28a745; }
        .val-error   { background-color: #ffe6e6; color: #721c24; border-left: 4px solid #dc3545; }

        .val-result-section {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        .val-info-item { margin-bottom: 15px; }
        .val-info-label {
            font-weight: bold;
            color: #000;
            font-family: 'Cantarell', sans-serif;
            margin-bottom: 5px;
            display: block;
        }
        .val-info-value {
            color: #333;
            font-family: 'Open Sans', sans-serif;
            padding: 10px;
            background-color: #fff;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        .val-progress-wrap {
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            margin-top: 10px;
            overflow: hidden;
        }
        .val-progress-fill {
            height: 100%;
            background-color: #F28018;
            border-radius: 10px;
            text-align: center;
            line-height: 20px;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            transition: width 0.5s ease-in-out;
        }
        .val-text-success { color: #28a745; }
        .val-text-danger  { color: #dc3545; }
        .val-text-optional { color: #6c757d; font-style: italic; font-size: 12px; margin-left: 5px; }
        .scan-status-item { padding: 4px 0; }

        .val-action-button {
            display: block;
            width: 100%;
            background-color: <?php echo $val_buttonEnabled ? '#F28018' : '#cccccc'; ?>;
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 40px;
            cursor: <?php echo $val_buttonEnabled ? 'pointer' : 'not-allowed'; ?>;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
        }
        .val-action-button:hover {
            background-color: <?php echo $val_buttonEnabled ? '#d86d0f' : '#cccccc'; ?>;
            color: #fff;
            text-decoration: none;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .main-container, .val-container { padding: 12px; }
            .scanner-section { padding: 15px; }
            .scan-form, .val-search-form { flex-direction: column; }
            .scan-input, .val-search-input { width: 100%; margin-bottom: 10px; }
            .preview-button, .val-search-button { width: 100%; }
            .data-label { min-width: auto; display: block; margin-bottom: 5px; }
            .preview-data .col-md-4 { margin-bottom: 20px; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════════════════════
     SECTION 1 — TIRE DETAILS ENTRY
══════════════════════════════════════════════════════════════════════════ -->
<div class="main-container">
    <h1 class="page-title">
        <i class="fas fa-cog"></i> Tire Details Entry
    </h1>

    <?php
    $scannerMeta = [
        1 => ['title' => 'Base QR Code Scanner',    'placeholder' => 'Scan Base QR Code here'],
        2 => ['title' => 'Cushion QR Code Scanner',  'placeholder' => 'Scan Cushion QR Code here'],
        3 => ['title' => 'Thread QR Code Scanner',   'placeholder' => 'Scan Thread QR Code here'],
    ];

    function generateScannerSection($scannerNum, $autoFocusScanner, $scannerMeta, $serialNumber) {
        global $conn;
        $scannerKey = "scanner" . $scannerNum;
        $tableName  = "qr_scanned_data" . ($scannerNum > 1 ? $scannerNum : "");
        $isActive   = ($scannerNum === $autoFocusScanner);
        $meta       = $scannerMeta[$scannerNum];
        ?>
        <div class="scanner-section <?php echo $isActive ? 'active-scanner' : ''; ?>"
             id="scannerSection<?php echo $scannerNum; ?>">

            <h2 class="scanner-title">
                <i class="fas fa-qrcode"></i> <?php echo $meta['title']; ?>
            </h2>

            <?php if (isset($_SESSION[$scannerKey . '_error'])): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION[$scannerKey . '_error']; unset($_SESSION[$scannerKey . '_error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION[$scannerKey . '_success'])): ?>
                <div class="message success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION[$scannerKey . '_success']; unset($_SESSION[$scannerKey . '_success']); ?>
                </div>
            <?php endif; ?>

            <form class="scan-form" method="POST"
                  action="?serialNumber=<?php echo urlencode($serialNumber); ?>">
                <input type="text"
                       class="scan-input"
                       id="qrInput<?php echo $scannerNum; ?>"
                       name="qrData"
                       placeholder="<?php echo $meta['placeholder']; ?>"
                       autocomplete="off"
                       required>
                <input type="hidden" name="scanner" value="<?php echo $scannerKey; ?>">
                <button type="submit" class="preview-button">
                    <i class="fas fa-search"></i> Preview Data
                </button>
            </form>

            <?php if (isset($_SESSION[$scannerKey . '_data'])): ?>
                <div class="preview-box">
                    <h3 class="preview-title">
                        <i class="fas fa-file-alt"></i> QR Code Data Preview
                    </h3>
                    <div class="preview-data">
                        <div class="row">

                            <!-- Compound Information -->
                            <div class="col-md-4">
                                <h4><i class="fas fa-flask"></i> Compound Information</h4>
                                <p>
                                    <span class="data-label">Compound Name:</span>
                                    <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']; ?></span>
                                </p>
                                <p>
                                    <span class="data-label">Batch Number:</span>
                                    <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['batch_number']; ?></span>
                                </p>
                                <p>
                                    <span class="data-label">Job Number:</span>
                                    <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['job_number']; ?></span>
                                </p>
                                <p>
                                    <span class="data-label">Tire Code:</span>
                                    <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['tire_code']; ?></span>
                                </p>
                            </div>

                            <!-- Category & Weight Information -->
                            <div class="col-md-4">
                                <h4><i class="fas fa-tag"></i> Category Information</h4>
                                <?php if (isset($_SESSION[$scannerKey . '_cat'], $_SESSION[$scannerKey . '_c_cat'])): ?>
                                    <p>
                                        <span class="data-label">Category:</span>
                                        <span class="data-value">
                                            <?php echo $_SESSION[$scannerKey . '_cat']; ?>
                                            <?php $c_cat = $_SESSION[$scannerKey . '_c_cat'];
                                                  if (is_numeric($c_cat)): ?>
                                                <span class="category-badge category-<?php echo $c_cat; ?>">
                                                    C_Category <?php echo $c_cat; ?>
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    </p>
                                <?php endif; ?>

                                <?php if (isset($_SESSION[$scannerKey . '_weights'], $_SESSION[$scannerKey . '_cat'])): ?>
                                    <h4><i class="fas fa-weight"></i> Weight Information</h4>
                                    <?php $categoryWeight = getCategoryWeight($_SESSION[$scannerKey . '_weights'], $_SESSION[$scannerKey . '_cat']);
                                          if ($categoryWeight !== null): ?>
                                        <p>
                                            <span class="data-label">Category Weight:</span>
                                            <span class="weight-value"><?php echo number_format($categoryWeight, 2); ?> kg</span>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-warning">No weight found for this category.</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Actual Weight & Batch Stats -->
                            <div class="col-md-4">
                                <h4><i class="fas fa-balance-scale"></i> Actual Weight Details</h4>
                                <?php $actualWeight = getActualWeight($conn, $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']);
                                      if ($actualWeight !== null): ?>
                                    <p>
                                        <span class="data-label">Actual Weight:</span>
                                        <span class="weight-value"><?php echo number_format($actualWeight, 2); ?> kg</span>
                                    </p>
                                <?php endif; ?>

                                <?php
                                $jobNumber   = $_SESSION[$scannerKey . '_data']['processed_data']['job_number'];
                                $batchNumber = $_SESSION[$scannerKey . '_data']['processed_data']['batch_number'];
                                $details     = getMatchingDetails($conn, $jobNumber, $batchNumber, $tableName);
                                ?>
                                <h4><i class="fas fa-clipboard-list"></i> Batch Statistics</h4>
                                <p>
                                    <span class="data-label">Total Scans:</span>
                                    <span class="weight-value"><?php echo $details['quantity']; ?></span>
                                </p>
                                <p>
                                    <span class="data-label">Total Weight:</span>
                                    <span class="weight-value"><?php echo number_format($details['table_total_weight'], 2); ?> kg</span>
                                </p>
                            </div>
                        </div>

                        <!-- Weight Analysis -->
                        <div class="row">
                            <div class="col-md-12">
                                <?php if (isset($actualWeight, $categoryWeight)): ?>
                                    <h4><i class="fas fa-calculator"></i> Weight Analysis</h4>
                                    <?php
                                    $newTotal         = $details['table_total_weight'] + $categoryWeight;
                                    $weightDifference = $actualWeight - $newTotal;
                                    $colorClass       = $weightDifference >= 0 ? 'positive-diff' : 'negative-diff';
                                    ?>
                                    <p>
                                        <span class="data-label">Weight Difference:</span>
                                        <span class="weight-value <?php echo $colorClass; ?>">
                                            <?php echo number_format($weightDifference, 2); ?> kg
                                        </span>
                                    </p>
                                    <p>
                                        <span class="data-label">New Total Weight:</span>
                                        <span class="weight-value"><?php echo number_format($newTotal, 2); ?> kg</span>
                                    </p>
                                    <?php if ($weightDifference < 0): ?>
                                        <div class="alert alert-warning mt-3">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Warning: Total weight would exceed actual weight!
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div><!-- /.preview-data -->

                    <!-- Confirm Insert -->
                    <form method="POST"
                          action="?serialNumber=<?php echo urlencode($serialNumber); ?>"
                          class="mt-3">
                        <input type="hidden" name="confirm_scanner" value="<?php echo $scannerKey; ?>">
                        <input type="hidden" name="compound_name"   value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']; ?>">
                        <input type="hidden" name="batch_number"    value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['batch_number']; ?>">
                        <input type="hidden" name="job_number"      value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['job_number']; ?>">
                        <input type="hidden" name="tire_code"       value="<?php echo $_SESSION[$scannerKey . '_data']['tire_code']; ?>">
                        <button type="submit" name="confirm_insert" class="confirm-button">
                            <i class="fas fa-save"></i> Confirm &amp; Insert Data
                        </button>
                    </form>
                </div><!-- /.preview-box -->
            <?php endif; ?>
        </div><!-- /.scanner-section -->
        <?php
    }

    for ($i = 1; $i <= 3; $i++) {
        generateScannerSection($i, $autoFocusScanner, $scannerMeta, $serialNumber);
    }
    ?>
</div><!-- /.main-container -->


<!-- ══════════════════════════════════════════════════════════════════════════
     SECTION 2 — SERIAL NUMBER VALIDATION
══════════════════════════════════════════════════════════════════════════ -->
<div class="val-container">
    <h1 class="val-title">
        <i class="fas fa-barcode"></i> Serial Number Validation
    </h1>

    <form method="GET" class="val-search-form" id="valForm"
          action="?serialNumber=<?php echo urlencode($serialNumber); ?>">
        <input type="text"
               class="val-search-input"
               id="valSerialInput"
               name="checkSerial"
               placeholder="Enter or scan serial number"
               value="<?php echo htmlspecialchars($val_serialNumber); ?>"
               autocomplete="off"
               required>
        <button type="submit" class="val-search-button">
            <i class="fas fa-search"></i> Check
        </button>
    </form>

    <?php echo $val_message; ?>

    <?php if (!empty($val_serialNumber)): ?>
        <div class="val-result-section">

            <div class="val-info-item">
                <span class="val-info-label">Serial Number</span>
                <div class="val-info-value"><?php echo htmlspecialchars($val_serialNumber); ?></div>
            </div>

            <?php if (!empty($val_tireCode)): ?>
            <div class="val-info-item">
                <span class="val-info-label">Tire Code</span>
                <div class="val-info-value"><?php echo htmlspecialchars($val_tireCode); ?></div>
            </div>
            <?php endif; ?>

            <div class="val-info-item">
                <span class="val-info-label">Scanning Progress</span>
                <?php
                $totalRequired = $val_baseRequiredDisplay ? 3 : 2;
                $reqScanned    = $val_scannedCount;
                if (!$val_baseRequiredDisplay && $val_row1['count'] > 0) $reqScanned--;
                $pct = $totalRequired > 0 ? (min($reqScanned, $totalRequired) / $totalRequired) * 100 : 0;
                ?>
                <div class="val-progress-wrap">
                    <div class="val-progress-fill" style="width:<?php echo $pct; ?>%">
                        <?php echo "$reqScanned/$totalRequired"; ?>
                    </div>
                </div>
            </div>

            <div class="val-info-item">
                <span class="val-info-label">Scan Status</span>
                <div class="val-info-value">
                    <div class="scan-status-item">
                        <i class="fas <?php echo $val_row1['count'] > 0 ? 'fa-check-circle val-text-success' : 'fa-times-circle val-text-danger'; ?>"></i>
                        Base Component
                        <?php if (!$val_baseRequiredDisplay): ?>
                            <span class="val-text-optional">(Optional for this tire code)</span>
                        <?php endif; ?>
                    </div>
                    <div class="scan-status-item">
                        <i class="fas <?php echo $val_row2['count'] > 0 ? 'fa-check-circle val-text-success' : 'fa-times-circle val-text-danger'; ?>"></i>
                        Cushion Component
                    </div>
                    <div class="scan-status-item">
                        <i class="fas <?php echo $val_row3['count'] > 0 ? 'fa-check-circle val-text-success' : 'fa-times-circle val-text-danger'; ?>"></i>
                        Thread Component
                    </div>
                </div>
            </div>
        </div>

        <a href="<?php echo $val_buttonEnabled ? 'about1.php?serialNumber=' . urlencode($val_serialNumber) : '#'; ?>"
           class="val-action-button"
           <?php echo !$val_buttonEnabled ? 'onclick="return false;"' : ''; ?>>
            <i class="fas fa-arrow-right"></i> Continue to Next Step
        </a>

        <?php if (!$val_buttonEnabled): ?>
            <p style="text-align:center;margin-top:10px;color:#721c24;">
                <i class="fas fa-exclamation-circle"></i>
                Please complete all required component scans to proceed.
            </p>
        <?php endif; ?>
    <?php endif; ?>
</div><!-- /.val-container -->


<!-- ─── Scripts ─────────────────────────────────────────────────────────── -->
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    const autoFocusScannerNum = <?php echo $autoFocusScanner; ?>;

    document.addEventListener('DOMContentLoaded', function () {

        // ── Activate (highlight + scroll + focus) the correct QR scanner ──
        function activateScanner(num) {
            var section = document.getElementById('scannerSection' + num);
            var input   = document.getElementById('qrInput' + num);
            if (!section || !input) return;

            document.querySelectorAll('.scanner-section').forEach(function (el) {
                el.classList.remove('active-scanner');
            });
            section.classList.add('active-scanner');
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(function () { input.focus(); }, 400);
        }

        // On every load go straight to the right scanner
        activateScanner(autoFocusScannerNum);

        // Re-activate after a successful insert
        if (document.querySelectorAll('.success-message').length > 0) {
            activateScanner(autoFocusScannerNum);
        }

        // ── Validation section: barcode scanner auto-submit ──
        var valInput = document.getElementById('valSerialInput');
        if (valInput) {
            var lastTime    = Date.now();
            var scanTimeout = null;

            valInput.addEventListener('keydown', function (e) {
                var now      = Date.now();
                var timeDiff = now - lastTime;
                lastTime     = now;

                if (e.key === 'Enter' && valInput.value.length > 0) {
                    e.preventDefault();
                    document.getElementById('valForm').submit();
                    return;
                }
                if (timeDiff < 50) {
                    clearTimeout(scanTimeout);
                    scanTimeout = setTimeout(function () {
                        if (valInput.value.length > 5) {
                            document.getElementById('valForm').submit();
                        }
                    }, 300);
                }
            });
        }

        // ── Fade out inline messages after 3 s ──
        document.querySelectorAll('.message').forEach(function (msg) {
            msg.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(function () {
                msg.style.opacity = '0';
                setTimeout(function () { msg.remove(); }, 500);
            }, 3000);
        });
    });
</script>
</body>
</html>