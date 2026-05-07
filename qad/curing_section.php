<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_task_managemen');

// Initialize variables
$formSubmitted = false;
$cycles = [];
$successMessage = '';
$errorMessage = '';
$dbError = '';

// Database connection
$conn = null;
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $dbError = "Database connection error: " . $e->getMessage();
    error_log($dbError);
}

// Function to sanitize string inputs
function sanitizeString($conn, $value) {
    return $value ? mysqli_real_escape_string($conn, trim($value)) : '';
}

// Function to validate numeric input
function validateNumeric($value, $fieldName) {
    if ($value === '' || $value === null) {
        return null; // Allow empty values
    }
    if (!is_numeric($value)) {
        throw new Exception("Invalid $fieldName: must be a number");
    }
    return (float)$value;
}

// Function to validate time input
function validateTime($value, $fieldName) {
    if ($value && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
        throw new Exception("Invalid $fieldName: must be in HH:MM or HH:MM:SS format");
    }
    return $value ?: null;
}

// Function to insert inspection data
function insertInspection($conn, $date, $shift, $pressOperator, $pressNo, $cycleNo, $hydraulicPressure, $serial) {
    $stmt = $conn->prepare("INSERT INTO quality_inspection 
        (inspection_date, shift, press_operator, press_no, cycle_no, hydraulic_pressure, 
        serialNumber, brand_version, size, 
        base_temp, center_temp, tread_temp, 
        pattern_left_temp, pattern_center_temp, pattern_right_temp, 
        mould_temp, in_time, 
        pressure_1_3_cy, pressure_2_3_cy, pressure_before_unload, 
        start_time, finish_time, actual_cure_time, quality_check, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    // Sanitize stringSquared string inputs
    $date = sanitizeString($conn, $date ?: date('Y-m-d'));
    $shift = sanitizeString($conn, $shift ?: '');
    $pressOperator = sanitizeString($conn, $pressOperator ?: '');
    $pressNo = sanitizeString($conn, $pressNo ?: '');
    $serialNumber = sanitizeString($conn, $serial['serialNumber'] ?: '');
    $brandVersion = sanitizeString($conn, $serial['brand_version'] ?: 'Unknown');
    $size = sanitizeString($conn, $serial['size'] ?: '');
    
    // Validate and convert numeric inputs
    $cycleNo = (int)$cycleNo;
    $hydraulicPressure = validateNumeric($hydraulicPressure ?? '', 'Hydraulic Pressure');
    $baseTemp = validateNumeric($serial['green_temp']['base'] ?? '', 'Base Temp');
    $centerTemp = validateNumeric($serial['green_temp']['center'] ?? '', 'Center Temp');
    $treadTemp = validateNumeric($serial['green_temp']['tread'] ?? '', 'Tread Temp');
    $patternLeftTemp = validateNumeric($serial['pattern_temp']['left'] ?? '', 'Pattern Left Temp');
    $patternCenterTemp = validateNumeric($serial['pattern_temp']['center'] ?? '', 'Pattern Center Temp');
    $patternRightTemp = validateNumeric($serial['pattern_temp']['right'] ?? '', 'Pattern Right Temp');
    $mouldTemp = validateNumeric($serial['mould_temp'] ?? '', 'Mould Temp');
    
    // Validate time inputs
    $inTime = validateTime($serial['in_time'] ?? '', 'In Time');
    $startTime = validateTime($serial['actual_times']['start'] ?? '', 'Start Time');
    $finishTime = validateTime($serial['actual_times']['finish'] ?? '', 'Finish Time');
    $actualCureTime = validateTime($serial['actual_times']['actual_cure_time'] ?? '', 'Actual Cure Time');
    
    // Validate other numeric inputs
    $pressure1_3 = validateNumeric($serial['hydraulic_pressure']['at_1_3_cy'] ?? '', 'Pressure at 1/3 Cy');
    $pressure2_3 = validateNumeric($serial['hydraulic_pressure']['at_2_3_cy'] ?? '', 'Pressure at 2/3 Cy');
    $pressureBeforeUnload = validateNumeric($serial['hydraulic_pressure']['just_before_unload'] ?? '', 'Pressure Before Unload');
    
    // Sanitize quality check
    $qualityCheck = sanitizeString($conn, $serial['quality_check'] ?: null);
    
    // Bind parameters with correct types
    $stmt->bind_param(
        "ssssidssssdddddsdddsssss",
        $date,
        $shift,
        $pressOperator,
        $pressNo,
        $cycleNo,
        $hydraulicPressure,
        $serialNumber,
        $brandVersion,
        $size,
        $baseTemp,
        $centerTemp,
        $treadTemp,
        $patternLeftTemp,
        $patternCenterTemp,
        $patternRightTemp,
        $mouldTemp,
        $inTime,
        $pressure1_3,
        $pressure2_3,
        $pressureBeforeUnload,
        $startTime,
        $finishTime,
        $actualCureTime,
        $qualityCheck
    );
    
    if (!$stmt->execute()) {
        error_log("Failed to execute insertInspection: " . $stmt->error);
        throw new Exception("Failed to insert inspection data: " . $stmt->error);
    }
    
    error_log("Successfully inserted inspection for serialNumber=$serialNumber, cycle_no=$cycleNo, date=$date");
    $stmt->close();
}

// AJAX search endpoint
if (isset($_GET['search_serial'])) {
    header('Content-Type: application/json');
    $search = filter_input(INPUT_GET, 'search_serial', FILTER_SANITIZE_STRING);
    $result = [];
    
    if ($conn && $search !== false) {
        $stmt = $conn->prepare("SELECT serialNumber, brand, tireCode, tireWeight, pressNumber 
                                FROM tire_data 
                                WHERE serialNumber LIKE ? 
                                LIMIT 10");
        $searchParam = "%$search%";
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $queryResult = $stmt->get_result();
        
        while ($row = $queryResult->fetch_assoc()) {
            $result[] = [
                'id' => $row['serialNumber'],
                'text' => $row['serialNumber'],
                'brand' => $row['brand'] ?? 'Unknown',
                'tireCode' => $row['tireCode'] ?? '',
                'tireWeight' => $row['tireWeight'] ?? '0',
                'pressNumber' => $row['pressNumber'] ?? ''
            ];
        }
        $stmt->close();
    } else {
        error_log("Database connection failed or invalid search input for serial search: " . ($dbError ?: 'Invalid input'));
        $result = ['error' => 'Database connection failed or invalid search input'];
    }
    
    error_log("Search term: $search, Results: " . json_encode($result));
    echo json_encode(['results' => $result]);
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formSubmitted = true;
    
    try {
        if (!$conn || $conn->connect_error) {
            throw new Exception("Database connection lost. Please try again.");
        }

        $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
        $shift = filter_input(INPUT_POST, 'shift', FILTER_SANITIZE_STRING);
        $pressOperator = filter_input(INPUT_POST, 'press_operator', FILTER_SANITIZE_STRING);
        $pressNo = filter_input(INPUT_POST, 'press_no', FILTER_SANITIZE_STRING);
        $cycleCount = filter_input(INPUT_POST, 'cycle_count', FILTER_VALIDATE_INT);
        
        error_log("Form submitted: date=$date, shift=$shift, pressOperator=$pressOperator, pressNo=$pressNo, cycleCount=$cycleCount");
        
        if (!$date || !$shift || !$pressOperator || !$pressNo || $cycleCount === false) {
            throw new Exception("Required header fields (date, shift, press operator, or press number) are missing or invalid.");
        }
        
        // Process cycle data
        for ($i = 0; $i < $cycleCount; $i++) {
            if (!isset($_POST["cycle_no_$i"])) {
                error_log("Skipping cycle $i: cycle_no_$i not set");
                continue;
            }
            
            $cycle = [
                'cycle_no' => filter_input(INPUT_POST, "cycle_no_$i", FILTER_VALIDATE_INT),
                'hydraulic_pressure' => validateNumeric($_POST["hydraulic_pressure_$i"] ?? null, 'Hydraulic Pressure'),
                'serials' => []
            ];
            
            if ($cycle['cycle_no'] === false) {
                throw new Exception("Invalid cycle number for cycle $i.");
            }
            
            if ($cycle['hydraulic_pressure'] === null) {
                throw new Exception("Hydraulic Pressure for cycle $i is required.");
            }
            
            $serialCount = filter_input(INPUT_POST, "serial_count_$i", FILTER_VALIDATE_INT);
            if ($serialCount === false) {
                throw new Exception("Invalid serial count for cycle $i.");
            }
            
            error_log("Processing cycle $i: cycle_no={$cycle['cycle_no']}, serial_count=$serialCount");
            
            for ($j = 0; $j < $serialCount; $j++) {
                $serial = [
                    'serialNumber' => filter_input(INPUT_POST, "serialNumber_${i}_${j}", FILTER_SANITIZE_STRING),
                    'brand_version' => filter_input(INPUT_POST, "brand_version_${i}_${j}", FILTER_SANITIZE_STRING) ?: 'Unknown',
                    'size' => filter_input(INPUT_POST, "size_${i}_${j}", FILTER_SANITIZE_STRING),
                    'green_temp' => [
                        'base' => validateNumeric($_POST["base_${i}_${j}"] ?? '', 'Base Temp'),
                        'center' => validateNumeric($_POST["center_${i}_${j}"] ?? '', 'Center Temp'),
                        'tread' => validateNumeric($_POST["tread_${i}_${j}"] ?? '', 'Tread Temp'),
                    ],
                    'pattern_temp' => [
                        'left' => validateNumeric($_POST["pattern_left_${i}_${j}"] ?? '', 'Pattern Left Temp'),
                        'center' => validateNumeric($_POST["pattern_center_${i}_${j}"] ?? '', 'Pattern Center Temp'),
                        'right' => validateNumeric($_POST["pattern_right_${i}_${j}"] ?? '', 'Pattern Right Temp'),
                    ],
                    'mould_temp' => validateNumeric($_POST["mould_temp_${i}_${j}"] ?? '', 'Mould Temp'),
                    'in_time' => filter_input(INPUT_POST, "in_time_${i}_${j}", FILTER_SANITIZE_STRING),
                    'hydraulic_pressure' => [
                        'at_1_3_cy' => validateNumeric($_POST["at_1_3_cy_${i}_${j}"] ?? '', 'Pressure at 1/3 Cy'),
                        'at_2_3_cy' => validateNumeric($_POST["at_2_3_cy_${i}_${j}"] ?? '', 'Pressure at 2/3 Cy'),
                        'just_before_unload' => validateNumeric($_POST["just_before_unload_${i}_${j}"] ?? '', 'Pressure Before Unload'),
                    ],
                    'actual_times' => [
                        'start' => filter_input(INPUT_POST, "start_time_${i}_${j}", FILTER_SANITIZE_STRING),
                        'finish' => filter_input(INPUT_POST, "finish_time_${i}_${j}", FILTER_SANITIZE_STRING),
                        'actual_cure_time' => filter_input(INPUT_POST, "actual_cure_time_${i}_${j}", FILTER_SANITIZE_STRING),
                    ],
                    'quality_check' => filter_input(INPUT_POST, "quality_check_${i}_${j}", FILTER_SANITIZE_STRING),
                ];
                
                error_log("Cycle $i, Serial $j: serialNumber=" . ($serial['serialNumber'] ?: 'empty') . 
                         ", brand_version=" . ($serial['brand_version'] ?: 'empty') . 
                         ", size=" . ($serial['size'] ?: 'empty') . 
                         ", in_time=" . ($serial['in_time'] ?: 'empty') . 
                         ", quality_check=" . ($serial['quality_check'] ?: 'empty'));
                
                if (empty($serial['serialNumber'])) {
                    throw new Exception("Serial number is missing for serial $j in cycle $i.");
                }
                if (empty($serial['size'])) {
                    throw new Exception("Size is missing for serial $j in cycle $i.");
                }
                
                $cycle['serials'][] = $serial;
            }
            
            $cycles[] = $cycle;
        }
        
        // Save to database
        if ($conn) {
            try {
                $conn->begin_transaction();
                
                // Validate serial numbers against tire_data
                $validatedSerials = [];
                foreach ($cycles as $cycle) {
                    foreach ($cycle['serials'] as $serial) {
                        $serialNumber = $serial['serialNumber'];
                        if (!isset($validatedSerials[$serialNumber])) {
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM tire_data WHERE serialNumber = ?");
                            $stmt->bind_param("s", $serialNumber);
                            $stmt->execute();
                            $stmt->bind_result($count);
                            $stmt->fetch();
                            $stmt->close();
                            if ($count == 0) {
                                throw new Exception("Invalid serial number: " . htmlspecialchars($serialNumber));
                            }
                            $validatedSerials[$serialNumber] = true;
                        }
                    }
                }
                
                // Insert all data into quality_inspection
                foreach ($cycles as $cycle) {
                    foreach ($cycle['serials'] as $serial) {
                        insertInspection(
                            $conn,
                            $date,
                            $shift,
                            $pressOperator,
                            $pressNo,
                            $cycle['cycle_no'],
                            $cycle['hydraulic_pressure'],
                            $serial
                        );
                    }
                }
                
                $conn->commit();
                $successMessage = "Quality record successfully added!";
                
            } catch (Exception $e) {
                $conn->rollback();
                $errorMessage = "Error saving data: " . $e->getMessage();
                error_log($errorMessage);
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $errorMessage = "Database transaction error: " . $e->getMessage();
                error_log($errorMessage);
            }
        } else {
            $errorMessage = "Database connection not available. Data could not be saved.";
            error_log($errorMessage);
        }
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        error_log($errorMessage);
    }
}

// Close database connection
if ($conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRE Quality Inspection Sheet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #343a40;
            --text-color: #000000;
            --bg-color: #f0f0f0;
            --border-color: #000000;
            --input-border: #CCCCCC;
            --card-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        body {
            background-color: var(--bg-color);
            font-family: 'Open Sans', sans-serif;
            padding-bottom: 40px;
        }
        
        .header-logo {
            max-height: 60px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #000000 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-bottom: 4px solid var(--primary-color);
        }
        
        .page-title {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            font-size: 28px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-link {
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            color: white !important;
            padding: 0.5rem 1rem;
            margin: 0 5px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-color);
            color: var(--text-color) !important;
        }
        
        .container {
            max-width: 1200px;
            padding: 0 20px;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary-color), #FF9F45);
            color: var(--text-color);
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            font-size: 18px;
            padding: 15px 20px;
            border: none;
        }
        
        .card-header i {
            margin-right: 10px;
        }
        
        .card-body {
            padding: 25px;
            background-color: white;
        }
        
        .form-label {
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--input-border);
            padding: 12px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
        }
        
        .form-control:disabled, .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        
        .btn {
            border-radius: 40px;
            padding: 10px 25px;
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .btn-primary:hover {
            background-color: #e67615;
            border-color: #e67615;
            color: var(--text-color);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary:hover {
            background-color: #23272b;
            border-color: #23272b;
        }
        
        .btn-danger {
            background-color: rgb(66, 66, 100);
            border-color: rgb(66, 66, 100);
        }
        
        .btn-danger:hover {
            background-color: rgb(66, 66, 100);
            border-color: rgb(66, 66, 100);
        }
        
        .section-title {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            color: var(--secondary-color);
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
            margin-bottom: 20px;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
        .highlight-message {
            font-size: 16px;
            color: white;
            background-color: black;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            animation: blink 2s infinite;
            border: 2px solid var(--primary-color);
        }
        
        .select2-container--default .select2-selection--single {
            height: 47px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 47px;
            padding-left: 12px;
            color: var(--text-color);
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 45px;
        }
        
        .select2-dropdown {
            border-color: var(--input-border);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .measurement-group {
            position: relative;
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
        }
        
        .measurement-title {
            position: absolute;
            top: -12px;
            left: 20px;
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            font-size: 14px;
        }
        
        .footer {
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
            font-size: 14px;
            border-top: 3px solid var(--primary-color);
        }
        
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background-color: #f9f9f9;
            color: var(--secondary-color);
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            border-bottom: 2px solid var(--input-border);
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .serial-select-container {
            min-width: 120px;
        }
        
        .form-control-sm {
            padding: 8px;
            font-size: 14px;
        }
        
        .is-invalid-select2 .select2-selection {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="atire.png" alt="ATIRE Logo" class="header-logo me-3">
                    <h1 class="page-title">Quality System</h1>
                </div>
                <nav></nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="text-center mb-4">
            <h2 class casket fw-bold">QUALITY INSPECTION SHEET</h2>
            <p class="text-muted">Enter and review quality data for tire curing process</p>
        </div>
        
        <?php if (!empty($dbError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($dbError); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($formSubmitted && empty($errorMessage)): ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clipboard-list"></i> Submitted Quality Inspection Data
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <strong>Date:</strong> <?php echo htmlspecialchars($date); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Shift:</strong> <?php echo htmlspecialchars($shift); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Press Operator:</strong> <?php echo htmlspecialchars($pressOperator); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Press No:</strong> <?php echo htmlspecialchars($pressNo); ?>
                        </div>
                    </div>
                    
                    <?php foreach($cycles as $index => $cycle): ?>
                        <div class="measurement-group">
                            <div class="measurement-title">Cycle No. <?php echo htmlspecialchars($cycle['cycle_no']); ?></div>
                            <div class="mb-3">
                                <strong>Hydraulic Pressure (Bar):</strong> <?php echo htmlspecialchars($cycle['hydraulic_pressure']); ?>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Serial No</th>
                                            <th rowspan="2">Brand Version</th>
                                            <th rowspan="2">Size</th>
                                            <th colspan="3">Green Tire Temp. Prior to Curing</th>
                                            <th colspan="3">Pattern Temp.</th>
                                            <th rowspan="2">Mould Temp</th>
                                            <th rowspan="2">In Time</th>
                                            <th colspan="3">Actual Hydraulic Pressure (Bar)</th>
                                            <th colspan="3">Time</th>
                                            <th rowspan="2">Quality Check</th>
                                        </tr>
                                        <tr>
                                            <th>Base</th>
                                            <th>Center</th>
                                            <th>Tread</th>
                                            <th>Left</th>
                                            <th>Center</th>
                                            <th>Right</th>
                                            <th>at 1/3 Cy</th>
                                            <th>at 2/3 Cy</th>
                                            <th>Just Before Unload</th>
                                            <th>Start</th>
                                            <th>Finish</th>
                                            <th>Actual Cure Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($cycle['serials'] as $serial): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($serial['serialNumber']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['brand_version']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['size']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['green_temp']['base']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['green_temp']['center']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['green_temp']['tread']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['pattern_temp']['left']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['pattern_temp']['center']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['pattern_temp']['right']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['mould_temp']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['in_time']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['hydraulic_pressure']['at_1_3_cy']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['hydraulic_pressure']['at_2_3_cy']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['hydraulic_pressure']['just_before_unload']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['actual_times']['start']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['actual_times']['finish']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['actual_times']['actual_cure_time']); ?></td>
                                                <td><?php echo htmlspecialchars($serial['quality_check']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center">
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Add New Inspection
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clipboard-list"></i> Quality Inspection Form
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="qualityForm">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="date" class="form-label required-field">Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="shift" class="form-label required-field">Shift</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <select class="form-select" id="shift" name="shift" required>
                                        <option value="">Select Shift</option>
                                        <option value="A - (Day)">A - (Day)</option>
                                        <option value="B - (Night)">B - (Night)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="press_operator" class="form-label required-field">Press Operator</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="press_operator" name="press_operator" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="press_no" class="form-label required-field">Press No</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-cog"></i></span>
                                    <input type="text" class="form-control" id="press_no" name="press_no" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-title">CURING SECTION</div>
                        
                        <div id="cycles-container"></div>
                        
                        <input type="hidden" id="cycle_count" name="cycle_count" value="0">
                        
                        <div class="highlight-message mb-4">
                            Please ensure all measurements are accurate before submission
                        </div>
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-primary me-3" id="add-cycle">
                                <i class="fas fa-plus me-2"></i> Add Cycle
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Quality Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Template for cycle -->
            <template id="cycle-template">
                <div class="measurement-group cycle" data-cycle-index="0">
                    <div class="measurement-title">Cycle No. <span class="cycle-number">1</span></div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label required-field">Hydraulic Pressure (Bar)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                <input type="text" class="form-control hydraulic-pressure" name="hydraulic_pressure_0" value="140" required>
                            </div>
                        </div>
                        <div class="col-md-9 d-flex align-items-end justify-content-end">
                            <button type="button" class="btn btn-primary add-serial me-2">
                                <i class="fas fa-plus me-2"></i> Add Serial
                            </button>
                            <button type="button" class="btn btn-danger remove-cycle">
                                <i class="fas fa-trash me-2"></i> Remove Cycle
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="cycle_no_0" value="1">
                    <input type="hidden" class="serial_count" name="serial_count_0" value="0">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2">Serial No</th>
                                    <th rowspan="2">Brand Version</th>
                                    <th rowspan="2">Size</th>
                                    <th colspan="3">Green Tire Temp. Prior to Curing</th>
                                    <th colspan="3">Pattern Temp.</th>
                                    <th rowspan="2">Mould Temp</th>
                                    <th rowspan="2">In Time</th>
                                    <th colspan="3">Actual Hydraulic Pressure (Bar)</th>
                                    <th colspan="3">Time</th>
                                    <th rowspan="2">Quality Check</th>
                                    <th rowspan="2">Action</th>
                                </tr>
                                <tr>
                                    <th>Base</th>
                                    <th>Center</th>
                                    <th>Tread</th>
                                    <th>Left</th>
                                    <th>Center</th>
                                    <th>Right</th>
                                    <th>at 1/3 Cy</th>
                                    <th>at 2/3 Cy</th>
                                    <th>Just Before Unload</th>
                                    <th>Start</th>
                                    <th>Finish</th>
                                    <th>Actual Cure Time</th>
                                </tr>
                            </thead>
                            <tbody class="serials-container"></tbody>
                        </table>
                    </div>
                </div>
            </template>
            
            <!-- Template for serial row -->
            <template id="serial-template">
                <tr class="serial-row" data-serial-index="0">
                    <td class="serial-select-container">
                        <select class="form-select select2 serial-select" name="serialNumber_0_0" required></select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm brand-version" name="brand_version_0_0" value="Unknown" required readonly></td>
                    <td><input type="text" class="form-control form-control-sm tire-size" name="size_0_0" required></td>
                    <td><input type="text" class="form-control form-control-sm" name="base_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="center_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="tread_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="pattern_left_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="pattern_center_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="pattern_right_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="mould_temp_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="in_time_0_0" placeholder="HH:MM:SS"></td>
                    <td><input type="text" class="form-control form-control-sm" name="at_1_3_cy_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="at_2_3_cy_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="just_before_unload_0_0"></td>
                    <td><input type="text" class="form-control form-control-sm" name="start_time_0_0" placeholder="HH:MM:SS"></td>
                    <td><input type="text" class="form-control form-control-sm" name="finish_time_0_0" placeholder="HH:MM:SS"></td>
                    <td><input type="text" class="form-control form-control-sm" name="actual_cure_time_0_0" placeholder="HH:MM:SS"></td>
                    <td><input type="text" class="form-control form-control-sm" name="quality_check_0_0"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-serial"><i class="fas fa-trash"></i></button></td>
                </tr>
            </template>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>© 2025 ATIRE Quality System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const cyclesContainer = document.getElementById('cycles-container');
            const cycleTemplate = document.getElementById('cycle-template');
            const serialTemplate = document.getElementById('serial-template');
            const addCycleBtn = document.getElementById('add-cycle');
            const cycleCountInput = document.getElementById('cycle_count');
            
            let cycleCount = 0;
            
            // Add Cycle
            addCycleBtn.addEventListener('click', function() {
                addCycle();
            });
            
            // Add first cycle automatically
            addCycle();
            
            function addCycle() {
                const cycleIndex = cycleCount;
                cycleCount++;
                
                const cycleClone = document.importNode(cycleTemplate.content, true);
                const cycleElement = cycleClone.querySelector('.cycle');
                cycleElement.dataset.cycleIndex = cycleIndex;
                
                cycleClone.querySelector('.cycle-number').textContent = cycleIndex + 1;
                
                const cycleNoInput = cycleClone.querySelector('input[name^="cycle_no_"]');
                cycleNoInput.name = `cycle_no_${cycleIndex}`;
                cycleNoInput.value = cycleIndex + 1;
                
                const hydraulicPressureInput = cycleClone.querySelector('.hydraulic-pressure');
                hydraulicPressureInput.name = `hydraulic_pressure_${cycleIndex}`;
                hydraulicPressureInput.id = `hydraulic_pressure_${cycleIndex}`;
                
                const serialCountInput = cycleClone.querySelector('.serial_count');
                serialCountInput.name = `serial_count_${cycleIndex}`;
                
                const addSerialBtn = cycleClone.querySelector('.add-serial');
                addSerialBtn.addEventListener('click', function() {
                    addSerial(cycleElement);
                });
                
                const removeCycleBtn = cycleClone.querySelector('.remove-cycle');
                removeCycleBtn.addEventListener('click', function() {
                    if (cycleCount > 1) {
                        cycleElement.remove();
                        cycleCount--;
                        updateCycleCount();
                        reindexCycles();
                    } else {
                        alert('At least one cycle is required');
                    }
                });
                
                cyclesContainer.appendChild(cycleClone);
                addSerial(cycleElement);
                updateCycleCount();
            }
            
            function addSerial(cycleElement) {
                const cycleIndex = parseInt(cycleElement.dataset.cycleIndex);
                const serialsContainer = cycleElement.querySelector('.serials-container');
                const serialCountInput = cycleElement.querySelector('.serial_count');
                let serialCount = parseInt(serialCountInput.value);
                
                const serialClone = document.importNode(serialTemplate.content, true);
                const serialRow = serialClone.querySelector('.serial-row');
                serialRow.dataset.serialIndex = serialCount;
                
                const inputs = serialClone.querySelectorAll('input');
                inputs.forEach(input => {
                    const nameParts = input.name.split('_');
                    input.name = `${nameParts[0]}_${cycleIndex}_${serialCount}`;
                });
                
                const selects = serialClone.querySelectorAll('select');
                selects.forEach(select => {
                    const nameParts = select.name.split('_');
                    select.name = `${nameParts[0]}_${cycleIndex}_${serialCount}`;
                    select.id = `${nameParts[0]}_${cycleIndex}_${serialCount}`;
                });
                
                const serialSelect = serialClone.querySelector('.serial-select');
                
                const removeSerialBtn = serialClone.querySelector('.remove-serial');
                removeSerialBtn.addEventListener('click', function() {
                    if (serialsContainer.childElementCount > 1) {
                        serialRow.remove();
                        reindexSerials(cycleElement);
                    } else {
                        alert('At least one serial is required per cycle');
                    }
                });
                
                serialsContainer.appendChild(serialClone);
                serialCount++;
                serialCountInput.value = serialCount;
                
                // Initialize Select2 with validation
                $(serialSelect).select2({
                    placeholder: 'Select a serial number',
                    minimumInputLength: 0,
                    ajax: {
                        url: '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search_serial: params.term || ''
                            };
                        },
                        processResults: function(data) {
                            console.log('Select2 AJAX response:', data);
                            return {
                                results: data.results.map(item => ({
                                    id: item.id,
                                    text: item.text,
                                    brand: item.brand || 'Unknown',
                                    tireCode: item.tireCode || ''
                                }))
                            };
                        },
                        error: function(xhr, status, error) {
                            console.error('Select2 AJAX error:', status, error);
                            alert('Error fetching serial numbers. Please try again.');
                        },
                        cache: true
                    },
                    width: '100%'
                }).on('select2:select', function(e) {
                    const data = e.params.data;
                    const row = $(this).closest('tr');
                    const sizeInput = row.find('.tire-size');
                    const brandInput = row.find('.brand-version');
                    
                    brandInput.val(data.brand || 'Unknown');
                    sizeInput.val(data.tireCode || '');
                    
                    if (!data.tireCode) {
                        sizeInput.addClass('is-invalid');
                        sizeInput.focus();
                    } else {
                        sizeInput.removeClass('is-invalid');
                    }
                    
                    // Remove invalid class if brand is set
                    brandInput.removeClass('is-invalid');
                    
                    $(this).closest('.serial-select-container').removeClass('is-invalid-select2');
                }).on('select2:open', function() {
                    if (!$(this).val()) {
                        $(this).closest('.serial-select-container').addClass('is-invalid-select2');
                    }
                });
                
                serialRow.querySelector('.tire-size').addEventListener('input', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
                
                serialRow.querySelector('.brand-version').addEventListener('input', function() {
                    if (this.value.trim() === '') {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            }
            
            function updateCycleCount() {
                cycleCountInput.value = cycleCount;
            }
            
            function reindexCycles() {
                const cycles = document.querySelectorAll('.cycle');
                cycles.forEach((cycle, index) => {
                    cycle.dataset.cycleIndex = index;
                    cycle.querySelector('.cycle-number').textContent = index + 1;
                    const cycleNoInput = cycle.querySelector('input[name^="cycle_no_"]');
                    cycleNoInput.name = `cycle_no_${index}`;
                    cycleNoInput.value = index + 1;
                    const hydraulicPressureInput = cycle.querySelector('.hydraulic-pressure');
                    hydraulicPressureInput.name = `hydraulic_pressure_${index}`;
                    hydraulicPressureInput.id = `hydraulic_pressure_${index}`;
                    const serialCountInput = cycle.querySelector('.serial_count');
                    serialCountInput.name = `serial_count_${index}`;
                    reindexSerials(cycle, true);
                });
            }
            
            function reindexSerials(cycleElement, updateCycleIndexOnly = false) {
                const cycleIndex = parseInt(cycleElement.dataset.cycleIndex);
                const serials = cycleElement.querySelectorAll('.serial-row');
                const serialCountInput = cycleElement.querySelector('.serial_count');
                
                serials.forEach((serial, index) => {
                    if (!updateCycleIndexOnly) {
                        serial.dataset.serialIndex = index;
                    }
                    
                    const inputs = serial.querySelectorAll('input');
                    inputs.forEach(input => {
                        const nameParts = input.name.split('_');
                        input.name = `${nameParts[0]}_${cycleIndex}_${index}`;
                    });
                    
                    const selects = serial.querySelectorAll('select');
                    selects.forEach(select => {
                        const nameId = select.name.split('_')[0];
                        select.name = `${nameId}_${cycleIndex}_${index}`;
                        select.id = `${nameId}_${cycleIndex}_${index}`;
                    });
                });
                
                serialCountInput.value = serials.length;
            }
            
            // Enhanced form validation
            $('#qualityForm').on('submit', function(e) {
                let isValid = true;
                let errorMessages = [];
                
                // Validate header fields
                $(this).find('#date, #shift, #press_operator, #press_no').each(function() {
                    if ($(this).val().trim() === '') {
                        $(this).addClass('is-invalid');
                        isValid = false;
                        errorMessages.push($(this).prev().text() + ' is required.');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                // Validate cycles and serials
                $(this).find('.cycle').each(function(cycleIndex) {
                    const hydraulicPressure = $(this).find('.hydraulic-pressure');
                    if (hydraulicPressure.val().trim() === '' || isNaN(hydraulicPressure.val())) {
                        hydraulicPressure.addClass('is-invalid');
                        isValid = false;
                        errorMessages.push(`Hydraulic Pressure for cycle ${cycleIndex + 1} is required and must be a number.`);
                    } else {
                        hydraulicPressure.removeClass('is-invalid');
                    }
                    
                    // Validate serials
                    $(this).find('.serial-row').each(function(serialIndex) {
                        const serialSelect = $(this).find('.serial-select');
                        const sizeInput = $(this).find('.tire-size');
                        
                        if (!serialSelect.val()) {
                            serialSelect.closest('.serial-select-container').addClass('is-invalid-select2');
                            isValid = false;
                            errorMessages.push(`Serial number is required for serial ${serialIndex + 1} in cycle ${cycleIndex + 1}.`);
                        } else {
                            serialSelect.closest('.serial-select-container').removeClass('is-invalid-select2');
                        }
                        
                        if (!sizeInput.val().trim()) {
                            sizeInput.addClass('is-invalid');
                            isValid = false;
                            errorMessages.push(`Size is required for serial ${serialIndex + 1} in cycle ${cycleIndex + 1}.`);
                        } else {
                            sizeInput.removeClass('is-invalid');
                        }
                    });
                });
                
                // Validate numeric fields
                $(this).find('input[name*="hydraulic_pressure"], input[name*="base_"], input[name*="center_"], input[name*="tread_"], input[name*="pattern_"], input[name*="mould_temp"], input[name*="at_1_3_cy"], input[name*="at_2_3_cy"], input[name*="just_before_unload"]').each(function() {
                    const value = $(this).val().trim();
                    if (value && isNaN(value)) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                        errorMessages.push($(this).closest('td').prevAll('th').first().text() + ' must be a number.');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                // Validate time fields
                $(this).find('input[name*="in_time"], input[name*="start_time"], input[name*="finish_time"], input[name*="actual_cure_time"]').each(function() {
                    const value = $(this).val().trim();
                    if (value && !/^\d{2}:\d{2}(:\d{2})?$/.test(value)) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                        errorMessages.push($(this).closest('td').prevAll('th').first().text() + ' must be in HH:MM or HH:MM:SS format.');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please correct the following errors:\n- ' + errorMessages.join('\n- '));
                }
            });
            
            // Remove invalid class on input change
            $('input, select').on('change', function() {
                if ($(this).val().trim() !== '') {
                    $(this).removeClass('is-invalid');
                    if ($(this).hasClass('serial-select')) {
                        $(this).closest('.serial-select-container').removeClass('is-invalid-select2');
                    }
                }
            });
            
            // Auto-close alerts
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);
        });
    </script>
</body>
</html>