<?php
// Database connection configuration
$servername = "localhost";
$username = "planatir_task_managemen"; 
$password = "Bishan@1919"; 
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

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
$operator = "";
$searchError = "";
$successMessage = "";
$errorMessage = "";

// Get all available serial numbers from tire_data table
$serialNumbers = array();
$serialNumberQuery = "SELECT DISTINCT serialNumber FROM tire_data ORDER BY serialNumber";
$serialNumberResult = $conn->query($serialNumberQuery);
if ($serialNumberResult && $serialNumberResult->num_rows > 0) {
    while($row = $serialNumberResult->fetch_assoc()) {
        if (!empty($row['serialNumber'])) {
            $serialNumbers[] = $row['serialNumber'];
        }
    }
}

// Also get serial numbers from tire_grinding_data table if they don't already exist in the array
$grindingSerialQuery = "SELECT DISTINCT SIR_NO FROM tire_grinding_data ORDER BY SIR_NO";
$grindingSerialResult = $conn->query($grindingSerialQuery);
if ($grindingSerialResult && $grindingSerialResult->num_rows > 0) {
    while($row = $grindingSerialResult->fetch_assoc()) {
        if (!empty($row['SIR_NO']) && !in_array($row['SIR_NO'], $serialNumbers)) {
            $serialNumbers[] = $row['SIR_NO'];
        }
    }
}

// Sort serial numbers
sort($serialNumbers);

// AJAX handler for fetching tire data
if (isset($_GET['fetch_tire_data']) && !empty($_GET['serial'])) {
    $serial = trim($_GET['serial']);
    
    // First check if serial exists in tire_grinding_data table
    $checkGrindingData = "SELECT SIR_NO FROM tire_grinding_data WHERE SIR_NO = ?";
    $stmtCheck = $conn->prepare($checkGrindingData);
    $stmtCheck->bind_param("s", $serial);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($resultCheck->num_rows > 0) {
        $response = [
            'success' => false,
            'message' => 'This serial number already exists in the grinding records.',
            'exists' => true
        ];
    } else {
        // Then check if we can find this serial in tire_data
        $sql = "SELECT td.*, tt.type, tt.Colour, tt.description 
               FROM tire_data td 
               LEFT JOIN tire_details tt ON td.tireCode = tt.icode 
               WHERE td.serialNumber = ?";
        
        $stmt = $conn->prepare($sql);
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
                    'type' => $row['type'] ?? '',
                    'color' => $row['Colour'] ?? '',
                    'description' => $row['description'] ?? ''
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Tire data not found in master database',
                'exists' => false
            ];
        }
        $stmt->close();
    }
    $stmtCheck->close();
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Process search form
if (isset($_POST['search'])) {
    $serialNumber = trim($_POST['serial_number']);
    
    // Validate serial number
    if (empty($serialNumber)) {
        $searchError = "Please enter a serial number";
    } else {
        // First check if serial exists in tire_grinding_data table
        $checkGrindingData = "SELECT * FROM tire_grinding_data WHERE SIR_NO = ?";
        $stmtCheck = $conn->prepare($checkGrindingData);
        $stmtCheck->bind_param("s", $serialNumber);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows > 0) {
            $searchError = "This serial number already exists in the grinding records.";
        } else {
            // Then check if we can find this serial in tire_data
            $sql = "SELECT td.*, tt.type, tt.Colour, tt.description 
                   FROM tire_data td 
                   LEFT JOIN tire_details tt ON td.tireCode = tt.icode 
                   WHERE td.serialNumber = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $serialNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size = $row['tireCode'] ?? "";
                $brand = $row['brand'] ?? "";
                $type = $row['type'] ?? "";
                $color = $row['Colour'] ?? "";
                $description = $row['description'] ?? "";
            } else {
                // Serial not found in master data, but allow manual entry
                $size = "";
                $brand = "";
                $type = "";
                $color = "";
                $description = "";
            }
            $defect = "";
            $stmt->close();
        }
        $stmtCheck->close();
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
    $operator = trim($_POST['operator']);
    
    // Validate form data
    $formValid = true;
    
    if (empty($serialNumber)) {
        $errorMessage = "Serial number is required.";
        $formValid = false;
    } elseif (empty($operator)) {
        $errorMessage = "Operator field is required.";
        $formValid = false;
    } else {
        // Check if serial number already exists in grinding data
        $checkDuplicate = "SELECT SIR_NO FROM tire_grinding_data WHERE SIR_NO = ?";
        $stmtDup = $conn->prepare($checkDuplicate);
        $stmtDup->bind_param("s", $serialNumber);
        $stmtDup->execute();
        $dupResult = $stmtDup->get_result();
        
        if ($dupResult->num_rows > 0) {
            $errorMessage = "This serial number already exists in the grinding records.";
            $formValid = false;
        }
        $stmtDup->close();
    }
    
    if ($formValid) {
        // Check if table exists, create if not
        $tableCheck = $conn->query("SHOW TABLES LIKE 'tire_grinding_data'");
        if ($tableCheck->num_rows == 0) {
            $createTable = "CREATE TABLE tire_grinding_data (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                entry_date DATE DEFAULT (CURRENT_DATE),
                SIR_NO VARCHAR(50) NOT NULL UNIQUE,
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_sir_no (SIR_NO),
                INDEX idx_entry_date (entry_date)
            )";
            
            if ($conn->query($createTable) !== TRUE) {
                $errorMessage = "Error creating table: " . $conn->error;
                $formValid = false;
            }
        } else {
            // Check if description column exists, add if not
            $columnCheck = $conn->query("SHOW COLUMNS FROM tire_grinding_data LIKE 'description'");
            if ($columnCheck->num_rows == 0) {
                $alterTable = "ALTER TABLE tire_grinding_data ADD COLUMN description VARCHAR(255) AFTER color";
                $conn->query($alterTable);
            }
        }
        
        if ($formValid) {
            $sql = "INSERT INTO tire_grinding_data (SIR_NO, size, brand, type, color, description, gauge_ok, gauge_center, gauge_edge, defect, operator) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssiisss", 
                $serialNumber, $size, $brand, $type, $color, $description,
                $gaugeOk, $gaugeCenter, $gaugeEdge, $defect, $operator);
            
            if ($stmt->execute()) {
                $successMessage = "Tire grinding record successfully added!";
                // Add to serial numbers array if not already there
                if (!in_array($serialNumber, $serialNumbers)) {
                    $serialNumbers[] = $serialNumber;
                    sort($serialNumbers);
                }
                // Clear form data
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
                $operator = "";
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily POB Tyres Grinding Book Data Entry</title>
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
        
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
            color: white;
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
        
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
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
        
        .data-source-badge {
            font-size: 0.8rem;
            padding: 3px 8px;
            margin-left: 10px;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .spinning {
            animation: spin 1s linear infinite;
        }
        
        .fetch-data-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            border: 2px dashed #dee2e6;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
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
                <nav>
                    <!-- Navigation can be added here -->
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Daily POB Tyres Grinding Book Data Entry</h2>
            <p class="text-muted">Enter and review tire grinding data</p>
        </div>
        
        <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-search"></i> Search Tire
            </div>
            <div class="card-body">
                <form method="post" action="" id="search-form">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="serial_number" class="form-label required-field">Serial Number (SIR NO)</label>
                            <select class="form-select select2" id="serial_number" name="serial_number" required>
                                <option value="">Select or type a Serial Number</option>
                                <?php foreach($serialNumbers as $sn): ?>
                                <option value="<?php echo htmlspecialchars($sn); ?>" <?php echo ($sn === $serialNumber) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sn); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="data-source-indicator" class="mt-1"></div>
                            <?php if (!empty($searchError)): ?>
                                <div class="text-danger mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($searchError); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" name="search" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="fetch-data-btn" class="btn btn-info w-100">
                                <i class="fas fa-download me-2"></i> Fetch Data
                            </button>
                        </div>
                    </div>
                    
                    <div class="fetch-data-section">
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Use "Search" to check if serial exists or "Fetch Data" to load tire details from master database
                            </small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (empty($searchError) || (isset($_POST['search']) && empty($serialNumber))): ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clipboard-list"></i> Tire Grinding Data Form
            </div>
            <div class="card-body">
                <form method="post" action="" id="grinding-form">
                    <input type="hidden" name="serial_number" id="form-serial-number" value="<?php echo htmlspecialchars($serialNumber); ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="size" class="form-label">Tire Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control" id="size" name="size" value="<?php echo htmlspecialchars($size); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="brand" class="form-label">Brand</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($brand); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label">Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <input type="text" class="form-control" id="type" name="type" value="<?php echo htmlspecialchars($type); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="color" class="form-label">Color</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-palette"></i></span>
                                <input type="text" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($color); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="description" class="form-label">Description</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($description); ?>">
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
                                        <i class="fas fa-check-circle text-success me-1"></i>Gauge - OK
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="gauge_center" name="gauge_center" value="1" <?php echo $gaugeCenter ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="gauge_center">
                                        <i class="fas fa-dot-circle text-warning me-1"></i>Gauge - Center
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="gauge_edge" name="gauge_edge" value="1" <?php echo $gaugeEdge ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="gauge_edge">
                                        <i class="fas fa-circle text-info me-1"></i>Gauge - Edge
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-      6">
                            <label for="defect" class="form-label">Defect</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-exclamation-triangle"></i></span>
                                <input type="text" class="form-control" id="defect" name="defect" value="<?php echo htmlspecialchars($defect); ?>" placeholder="Enter any defects found">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="operator" class="form-label required-field">Operator</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="operator" name="operator" value="<?php echo htmlspecialchars($operator); ?>" required placeholder="Enter operator name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i> Save Grinding Record
                        </button>
                        <button type="reset" class="btn btn-secondary btn-lg">
                            <i class="fas fa-undo me-2"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($serialNumber) && empty($searchError)): ?>
        <div class="highlight-message">
            <i class="fas fa-info-circle me-2"></i>
            Serial Number: <?php echo htmlspecialchars($serialNumber); ?> - Ready for Grinding Data Entry
        </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; 2024 ATIRE Quality System - Daily POB Tyres Grinding Book Data Entry</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 for serial number dropdown
            $('#serial_number').select2({
                placeholder: 'Select or type a Serial Number',
                allowClear: true,
                tags: true,
                width: '100%',
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                },
                templateResult: function (data) {
                    var $result = $('<span></span>');
                    $result.text(data.text);
                    if (data.newTag) {
                        $result.append(' <em>(new)</em>');
                    }
                    return $result;
                }
            });
            
            // Update hidden form field when serial number changes
            $('#serial_number').on('change', function() {
                $('#form-serial-number').val($(this).val());
                $('#data-source-indicator').empty();
            });
            
            // Fetch tire data functionality
            $('#fetch-data-btn').on('click', function() {
                var serialNumber = $('#serial_number').val();
                
                if (!serialNumber) {
                    alert('Please select or enter a serial number first.');
                    return;
                }
                
                var $btn = $(this);
                var $icon = $btn.find('i');
                var originalText = $btn.html();
                
                // Show loading state
                $btn.prop('disabled', true);
                $icon.removeClass('fa-download').addClass('fa-spinner spinning');
                $btn.html('<i class="fas fa-spinner spinning me-2"></i> Fetching...');
                
                // Clear previous indicators
                $('#data-source-indicator').empty();
                
                $.ajax({
                    url: window.location.href,
                    method: 'GET',
                    data: {
                        fetch_tire_data: 1,
                        serial: serialNumber
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Populate form fields with fetched data
                            $('#size').val(response.data.size || '');
                            $('#brand').val(response.data.brand || '');
                            $('#type').val(response.data.type || '');
                            $('#color').val(response.data.color || '');
                            $('#description').val(response.data.description || '');
                            
                            // Show success indicator
                            $('#data-source-indicator').html(
                                '<span class="badge bg-success data-source-badge">' +
                                '<i class="fas fa-check me-1"></i>Data loaded from master database' +
                                '</span>'
                            );
                            
                            // Show success message
                            showMessage('Tire data successfully loaded!', 'success');
                        } else {
                            if (response.exists) {
                                // Serial exists in grinding records
                                $('#data-source-indicator').html(
                                    '<span class="badge bg-danger data-source-badge">' +
                                    '<i class="fas fa-times me-1"></i>Already exists in grinding records' +
                                    '</span>'
                                );
                                showMessage(response.message, 'danger');
                            } else {
                                // Serial not found in master data
                                $('#data-source-indicator').html(
                                    '<span class="badge bg-warning data-source-badge">' +
                                    '<i class="fas fa-exclamation-triangle me-1"></i>Not found in master database' +
                                    '</span>'
                                );
                                showMessage('Serial not found in master database. You can enter data manually.', 'warning');
                            }
                        }
                    },
                    error: function() {
                        showMessage('Error fetching tire data. Please try again.', 'danger');
                        $('#data-source-indicator').html(
                            '<span class="badge bg-danger data-source-badge">' +
                            '<i class="fas fa-times me-1"></i>Error fetching data' +
                            '</span>'
                        );
                    },
                    complete: function() {
                        // Restore button state
                        $btn.prop('disabled', false);
                        $btn.html(originalText);
                    }
                });
            });
            
            // Form validation
            $('#grinding-form').on('submit', function(e) {
                var serialNumber = $('#form-serial-number').val();
                var operator = $('#operator').val();
                
                if (!serialNumber.trim()) {
                    e.preventDefault();
                    alert('Please select or enter a serial number.');
                    $('#serial_number').focus();
                    return false;
                }
                
                if (!operator.trim()) {
                    e.preventDefault();
                    alert('Please enter the operator name.');
                    $('#operator').focus();
                    return false;
                }
                
                // Confirm submission
                if (!confirm('Are you sure you want to save this grinding record?')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert-dismissible').fadeOut();
            }, 5000);
            
            // Form reset functionality
            $('button[type="reset"]').on('click', function() {
                if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
                    $('#serial_number').val('').trigger('change');
                    $('#form-serial-number').val('');
                    $('#data-source-indicator').empty();
                    // Reset all form fields
                    $('#grinding-form')[0].reset();
                }
            });
            
            // Function to show temporary messages
            function showMessage(message, type) {
                var alertClass = 'alert-' + type;
                var iconClass = type === 'success' ? 'fa-check-circle' : 
                               type === 'danger' ? 'fa-exclamation-triangle' : 
                               'fa-info-circle';
                
                var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                               '<i class="fas ' + iconClass + ' me-2"></i>' + message +
                               '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                               '</div>';
                
                // Remove existing temporary alerts
                $('.temp-alert').remove();
                
                // Add new alert
                var $alert = $(alertHtml).addClass('temp-alert');
                $('.container').prepend($alert);
                
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    $alert.fadeOut();
                }, 5000);
            }
            
            // Enhanced keyboard navigation
            $(document).on('keydown', function(e) {
                // Ctrl+S to save form
                if (e.ctrlKey && e.which === 83) {
                    e.preventDefault();
                    if ($('#grinding-form').length) {
                        $('#grinding-form').submit();
                    }
                }
                
                // Escape to clear serial number field
                if (e.which === 27) {
                    if ($('#serial_number').is(':focus')) {
                        $('#serial_number').val('').trigger('change');
                    }
                }
            });
            
            // Prevent accidental page refresh
            let formChanged = false;
            $('#grinding-form input, #grinding-form select, #grinding-form textarea').on('change input', function() {
                formChanged = true;
            });
            
            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
            
            $('#grinding-form').on('submit', function() {
                formChanged = false;
            });
        });
    </script>
</body>
</html>