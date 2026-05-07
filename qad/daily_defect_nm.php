<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$serialNumber = "";
$size = "";
$brand = "";
$type = "";
$color = "";
$description = "";
$gaugeOk = "";
$gaugeCenter = "";
$gaugeEdge = "";
$defect = "";
$searchError = "";
$successMessage = "";
$errorMessage = "";

// Get all available serial numbers from tire_data and tire_grinding_data tables
$serialNumbers = array();
$serialNumberQuery = "SELECT serialNumber FROM tire_data";
$serialNumberResult = $conn->query($serialNumberQuery);
if ($serialNumberResult && $serialNumberResult->num_rows > 0) {
    while($row = $serialNumberResult->fetch_assoc()) {
        $serialNumbers[] = $row['serialNumber'];
    }
}

$grindingSerialQuery = "SELECT SIR_NO FROM tire_grinding_data";
$grindingSerialResult = $conn->query($grindingSerialQuery);
if ($grindingSerialResult && $grindingSerialResult->num_rows > 0) {
    while($row = $grindingSerialResult->fetch_assoc()) {
        if (!in_array($row['SIR_NO'], $serialNumbers)) {
            $serialNumbers[] = $row['SIR_NO'];
        }
    }
}

// Process search form
if (isset($_POST['search'])) {
    $serialNumber = trim($_POST['serial_number']); // Trim to avoid whitespace issues
    
    if (empty($serialNumber)) {
        $searchError = "Please enter a serial number";
    } else {
        $checkGrindingData = "SELECT * FROM tire_grinding_data WHERE SIR_NO = ?";
        $stmtCheck = $conn->prepare($checkGrindingData);
        if (!$stmtCheck) {
            error_log("Prepare failed for tire_grinding_data check: " . $conn->error);
            $searchError = "Database error during search";
        } else {
            $stmtCheck->bind_param("s", $serialNumber);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            
            if ($resultCheck->num_rows > 0) {
                $searchError = "This serial number already exists in the grinding records.";
            } else {
                $sql = "SELECT * FROM tire_data WHERE serialNumber = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed for tire_data: " . $conn->error);
                    $searchError = "Database error during search";
                } else {
                    $stmt->bind_param("s", $serialNumber);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $size = $row['tireCode'];
                        $brand = $row['brand'];
                        $type = "";
                        $color = "";
                        $description = "";
                        $defect = "";
                    } else {
                        $size = "";
                        $brand = "";
                        $type = "";
                        $color = "";
                        $description = "";
                        $defect = "";
                    }
                    $stmt->close();
                }
            }
            $stmtCheck->close();
        }
    }
}

// Process record submission
if (isset($_POST['submit'])) {
    $serialNumber = trim($_POST['serial_number']);
    $size = trim($_POST['size']);
    $brand = trim($_POST['brand']);
    $type = trim($_POST['type']);
    $color = trim($_POST['color']);
    $description = trim($_POST['description']);
    $gaugeOk = isset($_POST['gauge_ok']) ? 1 : 0;
    $gaugeCenter = isset($_POST['gauge_center']) ? 1 : 0;
    $gaugeEdge = isset($_POST['gauge_edge']) ? 1 : 0;
    $defect = trim($_POST['defect']);
    $operator = trim($_POST['checked_by']);
    
    $formValid = true;
    
    if (empty($serialNumber)) {
        $errorMessage = "Serial number is required.";
        $formValid = false;
    }
    
    if (empty($operator)) {
        $errorMessage = "Operator field is required.";
        $formValid = false;
    }
    
    if ($formValid) {
        // Check if serial number already exists in grinding data
        $checkGrindingData = "SELECT * FROM tire_grinding_data WHERE SIR_NO = ?";
        $stmtCheck = $conn->prepare($checkGrindingData);
        if (!$stmtCheck) {
            error_log("Prepare failed for tire_grinding_data check: " . $conn->error);
            $errorMessage = "Database error during submission";
            $formValid = false;
        } else {
            $stmtCheck->bind_param("s", $serialNumber);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            
            if ($resultCheck->num_rows > 0) {
                $errorMessage = "This serial number already exists in the grinding records.";
                $formValid = false;
            }
            $stmtCheck->close();
        }
        
        if ($formValid) {
            $tableCheck = $conn->query("SHOW TABLES LIKE 'tire_grinding_data'");
            if ($tableCheck->num_rows == 0) {
                error_log("Creating tire_grinding_data table");
                $createTable = "CREATE TABLE tire_grinding_data (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    entry_date DATE DEFAULT CURRENT_DATE,
                    SIR_NO VARCHAR(50) NOT NULL,
                    size VARCHAR(50),
                    brand VARCHAR(50),
                    type VARCHAR(50),
                    color VARCHAR(50),
                    description VARCHAR(255),
                    gauge_ok TINYINT(1) DEFAULT 0,
                    gauge_center TINYINT(1) DEFAULT 0,
                    gauge_edge TINYINT(1) DEFAULT 0,
                    defect VARCHAR(100),
                    operator VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if ($conn->query($createTable) !== TRUE) {
                    error_log("Table creation failed: " . $conn->error);
                    $errorMessage = "Error creating table: " . $conn->error;
                    $formValid = false;
                }
            } else {
                $columnCheck = $conn->query("SHOW COLUMNS FROM tire_grinding_data LIKE 'description'");
                if ($columnCheck->num_rows == 0) {
                    $alterTable = "ALTER TABLE tire_grinding_data ADD COLUMN description VARCHAR(255) AFTER color";
                    if (!$conn->query($alterTable)) {
                        error_log("Table alteration failed: " . $conn->error);
                        $errorMessage = "Error altering table: " . $conn->error;
                        $formValid = false;
                    }
                }
            }
            
            if ($formValid) {
                $sql = "INSERT INTO tire_grinding_data (SIR_NO, size, brand, type, color, description, gauge_ok, gauge_center, gauge_edge, defect, operator) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed for insert: " . $conn->error);
                    $errorMessage = "Database error during insertion";
                } else {
                    $stmt->bind_param("ssssssiiiss", 
                        $serialNumber, $size, $brand, $type, $color, $description,
                        $gaugeOk, $gaugeCenter, $gaugeEdge, $defect, $operator);
                    
                    if ($stmt->execute()) {
                        $successMessage = "Tire grinding record successfully added!";
                        if (!in_array($serialNumber, $serialNumbers)) {
                            $serialNumbers[] = $serialNumber;
                        }
                        // Reset form fields
                        $serialNumber = "";
                        $size = "";
                        $brand = "";
                        $type = "";
                        $color = "";
                        $description = "";
                        $gaugeOk = "";
                        $gaugeCenter = "";
                        $gaugeEdge = "";
                        $defect = "";
                    } else {
                        error_log("Insert failed: " . $stmt->error);
                        $errorMessage = "Error inserting record: " . $stmt->error;
                    }
                    
                    $stmt->close();
                }
            }
        }
    }
}

// AJAX handler to fetch tire data
if (isset($_GET['fetch_tire_data']) && !empty($_GET['serial'])) {
    error_log("Fetching tire data for serial: " . $_GET['serial']);
    $serial = trim($_GET['serial']);
    
    $checkGrindingData = "SELECT * FROM tire_grinding_data WHERE SIR_NO = ?";
    $stmtCheck = $conn->prepare($checkGrindingData);
    if (!$stmtCheck) {
        error_log("Prepare failed for tire_grinding_data: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    $stmtCheck->bind_param("s", $serial);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($resultCheck->num_rows > 0) {
        $response = [
            'success' => false,
            'message' => 'This serial number already exists in grinding records'
        ];
    } else {
        $sql = "SELECT * FROM tire_data WHERE serialNumber = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed for tire_data: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        $stmt->bind_param("s", $serial);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response = [
                'success' => true,
                'data' => [
                    'size' => $row['tireCode'] ?? '',
                    'brand' => $row['brand'] ?? '',
                    'weight' => $row['tireWeight'] ?? '',
                    'press' => $row['pressNumber'] ?? '',
                    'type' => '',
                    'color' => '',
                    'description' => ''
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Tire data not found'
            ];
        }
        $stmt->close();
    }
    $stmtCheck->close();
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily POB Tyres Grinding Book Data Entry - ATIRE Quality System</title>
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
            background-color: #F28018;
            border-color: #F28018;
            color: rgb(6, 6, 6);
        }

        .btn-primary:hover {
            background-color: rgb(221, 117, 20);
            border-color: rgb(221, 117, 20);
            color: rgb(6, 6, 6);
        }

        .btn-secondary {
            background-color: #343a40;
            border-color: #343a40;
            color: rgb(245, 246, 249);
        }

        .btn-secondary:hover {
            background-color: #343a40;
            border-color: #343a40;
            color: rgb(245, 246, 249);
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

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .spinning {
            animation: spin 1s linear infinite;
        }

        .field-readonly {
            background-color: #e9ecef;
            cursor: not-allowed;
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
            <h2 class="fw-bold">Daily POB Tyres Grinding Book Data Entry</h2>
            <p class="text-muted">Enter and review grinding data for tires</p>
        </div>

        <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-search"></i> Search Tire
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-9">
                            <label for="serial_number" class="form-label required-field">Serial Number (SIR NO)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <select class="form-select select2" id="serial_number" name="serial_number" required>
                                    <option value="">Select a Serial Number</option>
                                    <?php foreach($serialNumbers as $sn): ?>
                                    <option value="<?php echo htmlspecialchars($sn); ?>" <?php echo ($sn === $serialNumber) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sn); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="data-source-indicator" class="mt-1"></div>
                            <div class="text-danger mt-2" id="serial-error"><?php echo htmlspecialchars($searchError); ?></div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="fetch-data-btn" title="Fetch data from master database">
                                <i class="fas fa-sync-alt"></i> Fetch Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-clipboard-list"></i> Tire Grinding Data Form
            </div>
            <div class="card-body">
                <form method="post" action="" id="grinding-form">
                    <input type="hidden" name="serial_number" id="form-serial-number" value="<?php echo htmlspecialchars($serialNumber); ?>">

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="size" class="form-label">Tire Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control field-readonly" id="size" name="size" value="<?php echo htmlspecialchars($size); ?>" readonly>
                            </div>
                            <small class="text-muted">Auto-populated from master database</small>
                        </div>
                        <div class="col-md-3">
                            <label for="brand" class="form-label">Brand</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control field-readonly" id="brand" name="brand" value="<?php echo htmlspecialchars($brand); ?>" readonly>
                            </div>
                            <small class="text-muted">Auto-populated from master database</small>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <input type="text" class="form-control field-readonly" id="type" name="type" value="<?php echo htmlspecialchars($type); ?>" readonly>
                            </div>
                            <small class="text-muted">Auto-populated if available</small>
                        </div>
                        <div class="col-md-3">
                            <label for="color" class="form-label">Color</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-palette"></i></span>
                                <input type="text" class="form-control field-readonly" id="color" name="color" value="<?php echo htmlspecialchars($color); ?>" readonly>
                            </div>
                            <small class="text-muted">Auto-populated if available</small>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <input type="text" class="form-control field-readonly" id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" readonly>
                            </div>
                            <small class="text-muted">Auto-populated if available</small>
                        </div>
                    </div>

                    <div id="additional-data" class="row mb-4" style="display: none;">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header bg-info text-white">
                                    Additional Tire Information
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Tire Weight</label>
                                            <p class="form-control-plaintext border rounded px-2" id="tire-weight">-</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Press Number</label>
                                            <p class="form-control-plaintext border rounded px-2" id="press-number">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="measurement-group">
                        <div class="measurement-title">Defect Details</div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="defect" class="form-label">Defect</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                                    <input type="text" class="form-control" id="defect" name="defect" placeholder="Enter the defect details" value="<?php echo htmlspecialchars($defect); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="measurement-group">
                        <div class="measurement-title">Gauge Measurements</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="gauge_ok" name="gauge_ok" value="1" <?php echo $gaugeOk ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="gauge_ok">
                                        Gauge - OK
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="gauge_center" name="gauge_center" value="1" <?php echo $gaugeCenter ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="gauge_center">
                                        Gauge - Center
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="gauge_edge" name="gauge_edge" value="1" <?php echo $gaugeEdge ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="gauge_edge">
                                        Gauge - Edge
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="checked_by" class="form-label required-field">Operator/Checked By</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-check"></i></span>
                                <input type="text" class="form-control" id="checked_by" name="checked_by" required placeholder="Enter your name">
                            </div>
                        </div>
                    </div>

                    <div class="highlight-message mb-4">
                        Please ensure all data is accurate before submission
                    </div>

                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-primary px-5" id="submit-btn">
                            <i class="fas fa-save me-2"></i> Save Grinding Record
                        </button>
                        <button type="button" class="btn btn-secondary px-5 ms-3" id="reset-btn">
                            <i class="fas fa-undo me-2"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
            $('.select2').select2({
                placeholder: "Select a Serial Number",
                allowClear: true,
                tags: true,
                createTag: function (params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newTag: true
                    }
                },
                width: '100%'
            });

            // Update hidden serial number field on select change
            $('#serial_number').on('change', function() {
                const serialNumber = $(this).val()?.trim();
                $('#form-serial-number').val(serialNumber);
                $('#serial-error').text('');
                
                if (!serialNumber) {
                    resetForm();
                    $('#submit-btn').prop('disabled', true);
                    return;
                }
                
                fetchTireData(serialNumber);
            });

            // Fetch data on button click
            $('#fetch-data-btn').on('click', function() {
                const serialNumber = $('#serial_number').val()?.trim();
                if (!serialNumber) {
                    $('#serial-error').text('Please enter a serial number first');
                    return;
                }
                
                $('#form-serial-number').val(serialNumber);
                fetchTireData(serialNumber);
            });

            function fetchTireData(serialNumber) {
                const icon = $('#fetch-data-btn').find('i');
                icon.addClass('spinning');
                resetFormFields();
                
                $.ajax({
                    url: '?fetch_tire_data=1',
                    data: { serial: serialNumber },
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('AJAX response:', response);
                        icon.removeClass('spinning');
                        
                        if (response.success) {
                            $('#size').val(response.data.size || '');
                            $('#brand').val(response.data.brand || '');
                            $('#type').val(response.data.type || '');
                            $('#color').val(response.data.color || '');
                            $('#description').val(response.data.description || '');
                            $('#tire-weight').text(response.data.weight || 'N/A');
                            $('#press-number').text(response.data.press || 'N/A');
                            $('#additional-data').show();
                            $('#data-source-indicator').html('<span class="badge bg-success data-source-badge">Data loaded from master database</span>');
                            $('#submit-btn').prop('disabled', false);
                        } else {
                            if (response.message.includes("already exists")) {
                                $('#serial-error').text(response.message);
                                $('#submit-btn').prop('disabled', true);
                            } else {
                                $('#data-source-indicator').html('<span class="badge bg-warning text-dark data-source-badge">Serial number not found in master database</span>');
                                $('#serial-error').text('Warning: This serial number was not found in the master database. You can still continue but data will not be pre-filled.');
                                $('#submit-btn').prop('disabled', false);
                            }
                            $('#additional-data').hide();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX error:', textStatus, errorThrown);
                        icon.removeClass('spinning');
                        $('#serial-error').text('Error fetching tire data. Please try again.');
                        $('#data-source-indicator').html('<span class="badge bg-danger data-source-badge">Error fetching data</span>');
                        $('#additional-data').hide();
                        $('#submit-btn').prop('disabled', true);
                    }
                });
            }

            function resetFormFields() {
                $('#size').val('');
                $('#brand').val('');
                $('#type').val('');
                $('#color').val('');
                $('#description').val('');
                $('#defect').val('');
                $('#gauge_ok').prop('checked', false);
                $('#gauge_center').prop('checked', false);
                $('#gauge_edge').prop('checked', false);
                $('#checked_by').val('');
                $('#data-source-indicator').html('');
                $('#additional-data').hide();
            }

            $('#reset-btn').on('click', function() {
                resetForm();
            });

            function resetForm() {
                $('#serial_number').val('').trigger('change');
                resetFormFields();
                $('#form-serial-number').val('');
                $('#serial-error').text('');
                $('#submit-btn').prop('disabled', true);
            }

            // Simplified form submission validation
            $('#grinding-form').on('submit', function(e) {
                const serialNumber = $('#form-serial-number').val()?.trim();
                const operator = $('#checked_by').val()?.trim();
                
                if (!serialNumber) {
                    e.preventDefault();
                    $('#serial-error').text('Please enter a serial number');
                    $('#serial_number').focus();
                    return false;
                }
                
                if (!operator) {
                    e.preventDefault();
                    $('#checked_by').addClass('is-invalid');
                    $('#checked_by').focus();
                    return false;
                }
                
                $('#checked_by').removeClass('is-invalid');
                console.log('Form submitting with serial:', serialNumber, 'operator:', operator);
                return true;
            });

            // Remove invalid class on input change
            $('#checked_by, #serial_number').on('input change', function() {
                if ($(this).val().trim() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });

            // Auto-hide alerts
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);
        });
    </script>
</body>
</html>