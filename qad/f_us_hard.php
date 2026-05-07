<?php
// Database connection configuration
$servername = "localhost";
$username = "planatir_task_managemen"; // Replace with your database username
$password = "Bishan@1919"; // Replace with your database password
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$serialNumber = "";
$tireCode = "";
$brand = "";
$icode = ""; // For storing icode
$icodeDescription = ""; // For storing description of icode
$searchError = "";
$successMessage = "";
$errorMessage = "";

// Get all available serial numbers from tire_data table
$serialNumbers = array();
$serialNumberQuery = "SELECT serialNumber FROM tire_data";
$serialNumberResult = $conn->query($serialNumberQuery);
if ($serialNumberResult && $serialNumberResult->num_rows > 0) {
    while($row = $serialNumberResult->fetch_assoc()) {
        $serialNumbers[] = $row['serialNumber'];
    }
}

// Process search form
if (isset($_POST['search'])) {
    $serialNumber = $_POST['serial_number'];
    
    // Validate serial number
    if (empty($serialNumber)) {
        $searchError = "Please enter a serial number";
    } else {
        // First query tire_data table for the tire information
        $sql = "SELECT * FROM tire_data WHERE serialNumber = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $tireData = $result->fetch_assoc();
            $tireCode = $tireData['tireCode'];
            $brand = $tireData['brand'];
            $icode = $tireData['tireCode'] ?? ''; // Get icode if it exists in tire_data
            
            // If icode exists in tire_data, get description from tire_details
            if (!empty($icode)) {
                $detailsSql = "SELECT Description FROM tire_details WHERE icode = ?";
                $detailsStmt = $conn->prepare($detailsSql);
                $detailsStmt->bind_param("s", $icode);
                $detailsStmt->execute();
                $detailsResult = $detailsStmt->get_result();
                
                if ($detailsResult->num_rows > 0) {
                    $detailsData = $detailsResult->fetch_assoc();
                    $icodeDescription = $detailsData['Description'];
                }
                
                $detailsStmt->close();
            }
            
            // If icode doesn't exist in tire_data but tireCode does, try to find matching icode in tire_details
            if (empty($icode) && !empty($tireCode)) {
                // This assumes there might be some relationship between tireCode and iCode
                $detailsSql = "SELECT icode, Description FROM tire_details WHERE Description LIKE CONCAT('%', ?, '%')";
                $searchTerm = $tireCode;
                $detailsStmt = $conn->prepare($detailsSql);
                $detailsStmt->bind_param("s", $searchTerm);
                $detailsStmt->execute();
                $detailsResult = $detailsStmt->get_result();
                
                if ($detailsResult->num_rows > 0) {
                    $detailsData = $detailsResult->fetch_assoc();
                    $icode = $detailsData['icode'];
                    $icodeDescription = $detailsData['Description'];
                }
                
                $detailsStmt->close();
            }
            
            // Check if quality record already exists
            $checkSql = "SELECT id FROM f_hquality_records WHERE SerialNumber = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $serialNumber);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $searchError = "Quality record already exists for this serial number.";
                $tireCode = "";
                $brand = "";
                $icode = "";
                $icodeDescription = "";
            }
            
            $checkStmt->close();
        } else {
            $searchError = "No tire data found for this serial number.";
        }
        
        $stmt->close();
    }
}

// Process quality record submission
if (isset($_POST['submit'])) {
    // Get form data
    $serialNumber = $_POST['serial_number'];
    $tireCode = $_POST['tire_code'];
    $brand = $_POST['brand'];
    $icode = $_POST['icode']; // Get icode
    $icodeDescription = $_POST['icode_description']; // Get icode description
    $hardness1 = $_POST['hardness1'];
    $hardness2 = $_POST['hardness2'];
    $hardness3 = $_POST['hardness3'];
    $hardness4 = $_POST['hardness4'];
    $us1 = $_POST['us1'];
    $us2 = $_POST['us2'];
    $us3 = $_POST['us3'];
    $us4 = $_POST['us4'];
    $us5 = $_POST['us5'];
    $us6 = $_POST['us6'];
    $checked_by = $_POST['checked_by'];
    $remarks = $_POST['remarks'];
    
    // Validate form data
    $formValid = true;
    
    // Validate hardness measurements
    if (!is_numeric($hardness1) || !is_numeric($hardness2) || !is_numeric($hardness3) || !is_numeric($hardness4)) {
        $errorMessage = "All hardness values must be numeric.";
        $formValid = false;
    }
    
    // Validate ultrasonic measurements
    if (!is_numeric($us1) || !is_numeric($us2) || !is_numeric($us3) || 
        !is_numeric($us4) || !is_numeric($us5) || !is_numeric($us6)) {
        $errorMessage = "All ultrasonic values must be numeric.";
        $formValid = false;
    }
    
    // Validate checked_by
    if (empty($checked_by)) {
        $errorMessage = "Please enter who checked this record.";
        $formValid = false;
    }
    
    // If form is valid, insert the record
    if ($formValid) {
        // Insert the record with the correct number of parameters
        $sql = "INSERT INTO f_hquality_records (SerialNumber, tireCode, brand, icode, icode_description, 
                Hardness1, Hardness2, Hardness3, Hardness4, 
                US1, US2, US3, US4, US5, US6, 
                checked_by, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters correctly (s for string, d for double)
        $stmt->bind_param("sssssddddddddddss", 
            $serialNumber, $tireCode, $brand, $icode, $icodeDescription,
            $hardness1, $hardness2, $hardness3, $hardness4, 
            $us1, $us2, $us3, $us4, $us5, $us6, 
            $checked_by, $remarks);
        
        if ($stmt->execute()) {
            $successMessage = "Quality record successfully added!";
            // Reset form data
            $serialNumber = "";
            $tireCode = "";
            $brand = "";
            $icode = "";
            $icodeDescription = "";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>US & HARDNESS SHEET OF FINISH TYRE RESILENT, POB (BLACK / NY)</title>
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

        /* Animation for the message */
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
        
        /* Select2 custom styling */
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
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="atire.png" alt="ATIRE Logo" class="header-logo me-3">
                    <h1 class="page-title"> Quality System</h1>
                </div>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-edit"></i> Data Entry
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_records.php">
                                <i class="fas fa-table"></i> View Records
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">US & HARDNESS SHEET OF FINISH TYRE RESILENT, POB (BLACK / NY)</h2>
            <p class="text-muted">Enter and review quality data for finished tires</p>
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
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-9">
                            <label for="serial_number" class="form-label required-field">Serial Number</label>
                            <select class="form-select select2" id="serial_number" name="serial_number" required>
                                <option value="">Select a Serial Number</option>
                                <?php foreach($serialNumbers as $sn): ?>
                                <option value="<?php echo htmlspecialchars($sn); ?>" <?php echo ($sn === $serialNumber) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sn); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
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
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($tireCode) && !empty($brand)): ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clipboard-list"></i> Quality Record Form
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="serial_number" value="<?php echo htmlspecialchars($serialNumber); ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="tire_code" class="form-label">Tire Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control" id="tire_code" name="tire_code" 
                                    value="<?php echo htmlspecialchars($tireCode); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="brand" class="form-label">Brand</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                    value="<?php echo htmlspecialchars($brand); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="icode" class="form-label">I-Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input type="text" class="form-control" id="icode" name="icode" 
                                    value="<?php echo htmlspecialchars($icode); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="icode_description" class="form-label">Description</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <input type="text" class="form-control" id="icode_description" name="icode_description" 
                                    value="<?php echo htmlspecialchars($icodeDescription); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-group">
                        <div class="measurement-title">Hardness Measurements</div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="hardness1" class="form-label required-field">Hardness 1</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="hardness1" name="hardness1" required placeholder="e.g. 65">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="hardness2" class="form-label required-field">Hardness 2</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="hardness2" name="hardness2" required placeholder="e.g. 64">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="hardness3" class="form-label required-field">Hardness 3</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="hardness3" name="hardness3" required placeholder="e.g. 66">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="hardness4" class="form-label required-field">Hardness 4</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="hardness4" name="hardness4" required placeholder="e.g. 65">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-group">
                        <div class="measurement-title">Ultrasonic Measurements</div>
                        <div class="row">
                            <div class="col-md-2">
                                <label for="us1" class="form-label required-field">US 1</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us1" name="us1" required placeholder="e.g. 59.5">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="us2" class="form-label required-field">US 2</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us2" name="us2" required placeholder="e.g. 58.5">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="us3" class="form-label required-field">US 3</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us3" name="us3" required placeholder="e.g. 59.0">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="us4" class="form-label required-field">US 4</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us4" name="us4" required placeholder="e.g. 59.2">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="us5" class="form-label required-field">US 5</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us5" name="us5" required placeholder="e.g. 58.7">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="us6" class="form-label required-field">US 6</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us6" name="us6" required placeholder="e.g. 59.1">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="checked_by" class="form-label required-field">Checked By</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-check"></i></span>
                                <input type="text" class="form-control" id="checked_by" name="checked_by" required placeholder="Enter your name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="remarks" class="form-label">Remarks</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                
<textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Optional comments about this quality check"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="highlight-message mb-4">
                        Please ensure all measurements are accurate before submission
                    </div>

                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i> Save Quality Record
                        </button>
                        <button type="reset" class="btn btn-secondary px-5 ms-3">
                            <i class="fas fa-undo me-2"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ATIRE Quality System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "Select a Serial Number",
                allowClear: true,
                width: '100%'
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);
            
            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;
                
                // Check required fields
                $(this).find('[required]').each(function() {
                    if ($(this).val() === '') {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields marked with *');
                }
            });
            
            // Remove validation styling on input
            $('input, select, textarea').on('change', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>