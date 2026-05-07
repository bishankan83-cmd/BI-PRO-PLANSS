<?php
// Database configuration
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Connect to database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define variables
$errors = [];
$successMsg = "";
$action = "";
$redirectUrl = "erp_stock_dash.php"; // Change this to your desired destination page

// Process import request
if (isset($_POST["import"])) {
    $action = "import";
    
    // Validate file upload
    if (!isset($_FILES["excelFile"]) || $_FILES["excelFile"]["error"] != 0) {
        $errors[] = "Error uploading file. Please try again.";
    } else {
        $file = $_FILES["excelFile"];
        $fileName = $file["name"];
        $fileType = $file["type"];
        $fileTmpName = $file["tmp_name"];
        
        // Check file extension
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ["xlsx", "xls", "csv"];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Invalid file format. Please upload Excel or CSV files only.";
        } else {
            // Include the PhpSpreadsheet library - make sure it's installed
            require 'vendor/autoload.php';
            
            try {
                // Load the Excel file
                if ($fileExtension == "csv") {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                } elseif ($fileExtension == "xlsx") {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                }
                
                $spreadsheet = $reader->load($fileTmpName);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                // Skip header row if exists
                $startRow = (isset($_POST["hasHeader"]) && $_POST["hasHeader"] == "1") ? 1 : 0;
                
                // Get default category from form
                $defaultCategory = isset($_POST["defaultCategory"]) ? $_POST["defaultCategory"] : "HOLD";
                
                // Get categorization method
                $categorizationMethod = isset($_POST["categorizationMethod"]) ? $_POST["categorizationMethod"] : "auto";
                
                // Prepare statement for stock_erp_tem table
                $stmtStockErpTem = $conn->prepare("INSERT INTO stock_erp_tem (serial_number, prev_serial, date, tyre_code, description, qty) VALUES (?, ?, ?, ?, ?, ?)");
                
                // Prepare statement for categorized items
                $stmtCategorized = $conn->prepare("INSERT INTO categorized_stock (serial_number, original_serial, date, tyre_code, description, qty, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                // Track statistics
                $rowsInserted = 0;
                $rowsCategorized = 0;
                $totalRows = count($rows) - $startRow;
                
                // Begin transaction
                $conn->begin_transaction();
                
                // Process each row in the Excel file
                for ($i = $startRow; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Skip empty rows
                    if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3]) && empty($row[4])) {
                        continue;
                    }
                    
                    // Get original serial number from Excel
                    $originalSerialNumber = trim(strval($row[0] ?? ''));
                    
                    // Check if the serial number is already in the correct format
                    if (preg_match('/^\d{6}-\d{5}$/', $originalSerialNumber)) {
                        // If already in correct format, keep it as is
                        $transformedSerialNumber = $originalSerialNumber;
                    } else {
                        // Transform serial number format (e.g., 32505031 -> 032025-05031)
                        $transformedSerialNumber = formatSerialNumber($originalSerialNumber);
                    }
                    
                    // Format date to MySQL format (assuming date in cell 1)
                    $excelDate = $row[1];
                    if (is_numeric($excelDate)) {
                        // Convert Excel date number to PHP date
                        $unixDate = ($excelDate - 25569) * 86400;
                        $mysqlDate = date("Y-m-d", $unixDate);
                    } else {
                        // Try to parse the date string
                        $dateObj = DateTime::createFromFormat("d/m/Y", $excelDate);
                        if (!$dateObj) {
                            $dateObj = DateTime::createFromFormat("Y-m-d", $excelDate);
                        }
                        $mysqlDate = $dateObj ? $dateObj->format("Y-m-d") : date("Y-m-d");
                    }
                    
                    // Get values for other fields
                    $tyreCode = $row[2] ?? "";
                    $description = $row[3] ?? "";
                    $qty = (int)($row[4] ?? 0);
                    
                    // Validate data
                    if (empty($originalSerialNumber) || empty($tyreCode)) {
                        continue; // Skip this row
                    }
                    
                    // Check if serial number exists in production_serial table
                    $checkQuery = "SELECT COUNT(*) as count FROM production_serial WHERE serial_number = ?";
                    $checkStmt = $conn->prepare($checkQuery);
                    $checkStmt->bind_param("s", $transformedSerialNumber);
                    $checkStmt->execute();
                    $result = $checkStmt->get_result();
                    $row = $result->fetch_assoc();
                    $exists = $row['count'] > 0;
                    $checkStmt->close();
                    
                    if ($exists) {
                        // If serial exists in production_serial, insert into stock_erp_tem
                        $stmtStockErpTem->bind_param("sssssi", $transformedSerialNumber, $originalSerialNumber, $mysqlDate, $tyreCode, $description, $qty);
                        if ($stmtStockErpTem->execute()) {
                            $rowsInserted++;
                        }
                    } else {
                        // If serial NOT in production_serial, categorize and insert into categorized_stock
                        
                        // Determine category based on method
                        if ($categorizationMethod === "manual") {
                            // Use the default category selected by user
                            $category = $defaultCategory;
                        } else {
                            // Use automatic categorization logic
                            $category = categorizeSerialNumber($transformedSerialNumber, $tyreCode);
                        }
                        
                        $stmtCategorized->bind_param("sssssss", $transformedSerialNumber, $originalSerialNumber, $mysqlDate, $tyreCode, $description, $qty, $category);
                        if ($stmtCategorized->execute()) {
                            $rowsCategorized++;
                        }
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                // Success message
                $successMsg = "Successfully processed $totalRows records. Inserted $rowsInserted into stock and categorized $rowsCategorized items.";
                
                // Start session to store success message
                session_start();
                $_SESSION['import_success'] = $successMsg;
                
                // Automatically redirect to dashboard after successful import
                header("Location: $redirectUrl");
                exit();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $errors[] = "Error processing Excel file: " . $e->getMessage();
            }
        }
    }
}

// Function to categorize serial numbers
function categorizeSerialNumber($serialNumber, $tyreCode) {
    // Example categorization logic - customize based on your specific requirements
    
    // Extract components from serial number
    $parts = explode('-', $serialNumber);
    $prefix = $parts[0] ?? '';
    $suffix = $parts[1] ?? '';
    
    // Check for specific patterns to determine category
    // This is placeholder logic - replace with your actual business rules
    
    // Example logic based on the first two digits of the suffix
    if (!empty($suffix)) {
        $firstTwoDigits = substr($suffix, 0, 2);
        
        if ($firstTwoDigits >= '90') {
            return 'CROSSCUT';
        } elseif ($firstTwoDigits >= '70') {
            return 'BGRADE';
        } elseif ($firstTwoDigits >= '50') {
            return 'AGRADE';
        }
    }
    
    // Default category if no specific matches
    return 'HOLD';
}

function formatSerialNumber($serialNumber) {
    // Remove any non-numeric characters
    $numericSerial = preg_replace('/[^0-9]/', '', $serialNumber);
    
    // Ensure the serial number is padded to at least 8 digits
    $paddedSerial = str_pad($numericSerial, 8, "0", STR_PAD_LEFT);
    
    // Special handling for different length scenarios
    if (strlen($paddedSerial) >= 9) {
        // For 9+ digit numbers
        if (substr($paddedSerial, 0, 1) == '0') {
            // If starts with 0: 032505031 -> 032025-05031
            $firstPart = substr($paddedSerial, 0, 2);       // "03"
            $secondPart = "20" . substr($paddedSerial, 2, 2); // "2025"
            $thirdPart = substr($paddedSerial, 4);          // "05031"
        } else {
            // If doesn't start with 0: 112001556 -> 112020-01556
            $firstPart = substr($paddedSerial, 0, 2);       // "11"
            $secondPart = "20" . substr($paddedSerial, 2, 2); // "2020"
            $thirdPart = substr($paddedSerial, 4);          // "01556"
        }
    } else {
        // For shorter numbers (original logic)
        $firstPart = "0" . substr($paddedSerial, 0, 1);     // First digit with leading zero
        $secondPart = "20" . substr($paddedSerial, 1, 2);   // Next two digits as year
        $thirdPart = substr($paddedSerial, 3);              // Remaining digits
    }
    
    // Combine with hyphen in the right position
    return $firstPart . $secondPart . "-" . $thirdPart;
}

// Get total count of regular stock_erp
$countQuery = "SELECT COUNT(*) as total FROM stock_erp";
$countResult = $conn->query($countQuery);
$totalRecords = ($countResult && $row = $countResult->fetch_assoc()) ? $row['total'] : 0;

// Get total count of stock_erp_tem
$countTempQuery = "SELECT COUNT(*) as total FROM stock_erp_tem";
$countTempResult = $conn->query($countTempQuery);
$totalTempRecords = ($countTempResult && $row = $countTempResult->fetch_assoc()) ? $row['total'] : 0;

// Get total count of categorized records
$countCategorizedQuery = "SELECT COUNT(*) as total FROM categorized_stock";
$countCategorizedResult = $conn->query($countCategorizedQuery);
$totalCategorizedRecords = ($countCategorizedResult && $row = $countCategorizedResult->fetch_assoc()) ? $row['total'] : 0;

// Get count by category
$categoryCounts = [];
$categoriesQuery = "SELECT category, COUNT(*) as count FROM categorized_stock GROUP BY category";
$categoriesResult = $conn->query($categoriesQuery);

if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categoryCounts[$row['category']] = $row['count'];
    }
}

// Get last 10 records from stock_erp
$lastRecordsQuery = "SELECT * FROM stock_erp ORDER BY id DESC LIMIT 10";
$lastRecordsResult = $conn->query($lastRecordsQuery);
$lastRecords = [];

if ($lastRecordsResult) {
    while ($row = $lastRecordsResult->fetch_assoc()) {
        $lastRecords[] = $row;
    }
}

// Get last 10 records from stock_erp_tem
$lastTempRecordsQuery = "SELECT * FROM stock_erp_tem ORDER BY id DESC LIMIT 10";
$lastTempRecordsResult = $conn->query($lastTempRecordsQuery);
$lastTempRecords = [];

if ($lastTempRecordsResult) {
    while ($row = $lastTempRecordsResult->fetch_assoc()) {
        $lastTempRecords[] = $row;
    }
}

// Get last 10 categorized records
$lastCategorizedQuery = "SELECT * FROM categorized_stock ORDER BY id DESC LIMIT 10";
$lastCategorizedResult = $conn->query($lastCategorizedQuery);
$lastCategorizedRecords = [];

if ($lastCategorizedResult) {
    while ($row = $lastCategorizedResult->fetch_assoc()) {
        $lastCategorizedRecords[] = $row;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock ERP - Excel Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 24px;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background: linear-gradient(135deg, #8a6d3b 0%, #6d5022 100%);
            color: white;
            font-weight: bold;
            padding: 18px 20px;
            border-bottom: none;
        }
        .form-control, .form-check {
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #8a6d3b;
            box-shadow: 0 0 0 0.25rem rgba(138, 109, 59, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3a3a3a 0%, #000000 100%);
            border: none;
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #8a6d3b 0%, #6d5022 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-light {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        .btn-light:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        .alert {
            margin-top: 20px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 16px 20px;
        }
        .guidelines {
            background-color: #f0f7ff;
            border-left: 4px solid #8a6d3b;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .feature-icon {
            font-size: 36px;
            margin-bottom: 15px;
            color: #8a6d3b;
            transition: transform 0.3s ease;
        }
        .dash-card:hover .feature-icon {
            transform: scale(1.2);
        }
        .dash-card {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .dash-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .dash-card-title {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .dash-card-value {
            font-size: 38px;
            font-weight: 700;
            color: #8a6d3b;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .table-responsive {
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background: linear-gradient(135deg, #f3f3f3 0%, #e6e6e6 100%);
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #8a6d3b;
            padding: 12px 15px;
        }
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(138, 109, 59, 0.05);
        }
        .serial-format {
            color: #8a6d3b;
            font-weight: bold;
        }
        .section-title {
            position: relative;
            margin-bottom: 25px;
            padding-bottom: 15px;
            color: #333;
            font-weight: 600;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #8a6d3b;
        }
        .nav-tabs {
            border-bottom: 2px solid #8a6d3b;
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 10px 20px;
            margin-right: 5px;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link.active {
            background-color: #8a6d3b;
            color: white;
            border: none;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            background-color: rgba(138, 109, 59, 0.1);
            border: none;
        }
        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        .badge-temp {
            background-color: #8a6d3b;
            color: white;
        }
        .badge-regular {
            background-color: #28a745;
            color: white;
        }
        .badge-hold {
            background-color: #dc3545;
            color: white;
        }
        .badge-agrade {
            background-color: #17a2b8;
            color: white;
        }
        .badge-bgrade {
            background-color: #fd7e14;
            color: white;
        }
        .badge-crosscut {
            background-color: #6610f2;
            color: white;
        }
        .tab-content {
            padding: 20px 0;
        }
        .page-header {
            margin-bottom: 30px;
            text-align: center;
            padding: 20px 0;
            position: relative;
        }
        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .page-subtitle {
            color: #6c757d;
            font-size: 18px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #d1d1d1;
        }
        .empty-state p {
            font-size: 18px;
        }
        .category-selector {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .form-switch .form-check-input {
            width: 3em;
        }
        .form-section {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .dash-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Stock ERP - Excel Manager</h1>
            <p class="page-subtitle">Manage and import stock data efficiently</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMsg); ?>
                <div class="mt-2">
                    <a href="<?php echo $redirectUrl; ?>" class="btn btn-sm btn-success">
                        <i class="bi bi-arrow-right-circle me-1"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-database"></i>
                    </div>
                    <div class="dash-card-title">Total Records</div>
                    <div class="dash-card-value"><?php echo number_format($totalRecords); ?></div>
                    <div class="mt-2">
                        <span class="badge badge-regular">Regular Table</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-table"></i>
                    </div>
                    <div class="dash-card-title">Pending Items</div>
                    <div class="dash-card-value"><?php echo number_format($totalTempRecords); ?></div>
                    <div class="mt-2">
                        <span class="badge badge-temp">Temp Table</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-tag"></i>
                    </div>
                    <div class="dash-card-title">Categorized Items</div>
                    <div class="dash-card-value"><?php echo number_format($totalCategorizedRecords); ?></div>
                    <div class="mt-2">
                        <span class="badge badge-hold">HOLD: <?php echo number_format($categoryCounts['HOLD'] ?? 0); ?></span>
                        <span class="badge badge-agrade">A: <?php echo number_format($categoryCounts['AGRADE'] ?? 0); ?></span>
                        <span class="badge badge-bgrade">B: <?php echo number_format($categoryCounts['BGRADE'] ?? 0); ?></span>
                        <span class="badge badge-crosscut">X: <?php echo number_format($categoryCounts['CROSSCUT'] ?? 0); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <div class="dash-card-title">Import Data</div>
                    <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload me-1"></i> Upload Excel
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabbed Navigation for Tables -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="m-0">Stock Records</h3>
                <a href="<?php echo $redirectUrl; ?>" class="btn btn-light">
                    <i class="bi bi-speedometer2 me-1"></i> Go to Dashboard
                </a>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="stockTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="temp-tab" data-bs-toggle="tab" data-bs-target="#temp-table" type="button" role="tab" aria-controls="temp-table" aria-selected="true">
                            <i class="bi bi-hourglass me-1"></i> Pending Stock
                            <span class="badge bg-secondary ms-1"><?php echo $totalTempRecords; ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="regular-tab" data-bs-toggle="tab" data-bs-target="#regular-table" type="button" role="tab" aria-controls="regular-table" aria-selected="false">
                            <i class="bi bi-box me-1"></i> Regular Stock
                            <span class="badge bg-secondary ms-1"><?php echo $totalRecords; ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="categorized-tab" data-bs-toggle="tab" data-bs-target="#categorized-table" type="button" role="tab" aria-controls="categorized-table" aria-selected="false">
                            <i class="bi bi-tags me-1"></i> Categorized Stock
                            <span class="badge bg-secondary ms-1"><?php echo $totalCategorizedRecords; ?></span>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="stockTabsContent">
                    <!-- Pending Stock Table -->
                    <div class="tab-pane fade show active" id="temp-table" role="tabpanel" aria-labelledby="temp-tab">
                        <h4 class="section-title">Last 10 Pending Stock Records</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Serial Number</th>
                                        <th>Original Serial</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
if (empty($lastTempRecords)): ?>
    <tr>
        <td colspan="7" class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>No pending stock records found.</p>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($lastTempRecords as $record): ?>
        <tr>
            <td><?php echo htmlspecialchars($record['id']); ?></td>
            <td class="serial-format"><?php echo htmlspecialchars($record['serial_number']); ?></td>
            <td><?php echo htmlspecialchars($record['prev_serial']); ?></td>
            <td><?php echo htmlspecialchars($record['date']); ?></td>
            <td><?php echo htmlspecialchars($record['tyre_code']); ?></td>
            <td><?php echo htmlspecialchars($record['description']); ?></td>
            <td><?php echo htmlspecialchars($record['qty']); ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Regular Stock Table -->
                    <div class="tab-pane fade" id="regular-table" role="tabpanel" aria-labelledby="regular-tab">
                        <h4 class="section-title">Last 10 Regular Stock Records</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Serial Number</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($lastRecords)): ?>
                                        <tr>
                                            <td colspan="6" class="empty-state">
                                                <i class="bi bi-inbox"></i>
                                                <p>No regular stock records found.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($lastRecords as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['id']); ?></td>
                                                <td class="serial-format"><?php echo htmlspecialchars($record['serial_number']); ?></td>
                                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                                <td><?php echo htmlspecialchars($record['tyre_code']); ?></td>
                                                <td><?php echo htmlspecialchars($record['description']); ?></td>
                                                <td><?php echo htmlspecialchars($record['qty']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Categorized Stock Table -->
                    <div class="tab-pane fade" id="categorized-table" role="tabpanel" aria-labelledby="categorized-tab">
                        <h4 class="section-title">Last 10 Categorized Stock Records</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Serial Number</th>
                                        <th>Original Serial</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($lastCategorizedRecords)): ?>
                                        <tr>
                                            <td colspan="8" class="empty-state">
                                                <i class="bi bi-inbox"></i>
                                                <p>No categorized stock records found.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($lastCategorizedRecords as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['id']); ?></td>
                                                <td class="serial-format"><?php echo htmlspecialchars($record['serial_number']); ?></td>
                                                <td><?php echo htmlspecialchars($record['original_serial']); ?></td>
                                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                                <td><?php echo htmlspecialchars($record['tyre_code']); ?></td>
                                                <td><?php echo htmlspecialchars($record['description']); ?></td>
                                                <td><?php echo htmlspecialchars($record['qty']); ?></td>
                                                <td>
                                                    <?php 
                                                    $categoryClass = '';
                                                    switch ($record['category']) {
                                                        case 'HOLD': $categoryClass = 'badge-hold'; break;
                                                        case 'AGRADE': $categoryClass = 'badge-agrade'; break;
                                                        case 'BGRADE': $categoryClass = 'badge-bgrade'; break;
                                                        case 'CROSSCUT': $categoryClass = 'badge-crosscut'; break;
                                                        default: $categoryClass = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $categoryClass; ?>"><?php echo htmlspecialchars($record['category']); ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Guidelines Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="m-0">Import Guidelines & Instructions</h3>
            </div>
            <div class="card-body">
                <div class="guidelines">
                    <h5><i class="bi bi-info-circle me-2"></i> How Stock Import Works</h5>
                    <p>This system processes Excel files containing stock data and handles them based on a specific logic:</p>
                    <ol>
                        <li>If a serial number <strong>exists</strong> in the <code>production_serial</code> table, the record is inserted into the <code>stock_erp_tem</code> table for regular processing.</li>
                        <li>If a serial number <strong>does not exist</strong> in the <code>production_serial</code> table, the record is categorized and inserted into the <code>categorized_stock</code> table with a specific category.</li>
                    </ol>
                    
                    <h5 class="mt-4"><i class="bi bi-file-earmark-excel me-2"></i> Excel File Requirements</h5>
                    <ul>
                        <li>Supported formats: <strong>XLSX, XLS, CSV</strong></li>
                        <li>Column format should be:
                            <ul>
                                <li>Column 1: Serial Number</li>
                                <li>Column 2: Date</li>
                                <li>Column 3: Tyre Code</li>
                                <li>Column 4: Description</li>
                                <li>Column 5: Quantity</li>
                            </ul>
                        </li>
                        <li>You can choose whether your file has a header row or not</li>
                    </ul>
                    
                    <h5 class="mt-4"><i class="bi bi-tag me-2"></i> Categorization Method</h5>
                    <p>You can choose between two methods for categorizing non-production serial numbers:</p>
                    <ul>
                        <li><strong>Automatic</strong>: Uses the system's built-in logic to categorize records (default)</li>
                        <li><strong>Manual</strong>: Assigns all non-production records to the category you specify</li>
                    </ul>
                    
                    <h5 class="mt-4"><i class="bi bi-123 me-2"></i> Serial Number Format</h5>
                    <p>The system will transform serial numbers to the standard format <span class="serial-format">MMYYYY-NNNNN</span> where:</p>
                    <ul>
                        <li><strong>MM</strong>: Month code (01-12)</li>
                        <li><strong>YYYY</strong>: Year</li>
                        <li><strong>NNNNN</strong>: Unique identifier</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #8a6d3b 0%, #6d5022 100%); color: white;">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="bi bi-cloud-upload me-2"></i> Import Excel Stock Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-section">
                            <div class="mb-3">
                                <label for="excelFile" class="form-label">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Select Excel File
                                </label>
                                <input type="file" class="form-control" id="excelFile" name="excelFile" required>
                                <div class="form-text">Supported formats: XLSX, XLS, CSV</div>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="hasHeader" name="hasHeader" value="1" checked>
                                <label class="form-check-label" for="hasHeader">
                                    File has header row (first row contains column names)
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-section category-selector">
                            <label class="mb-3">
                                <i class="bi bi-tag me-1"></i> Categorization Method for Non-Production Serials
                            </label>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="useManualCategorization" onchange="toggleCategorizationMethod()">
                                <label class="form-check-label" for="useManualCategorization">
                                    Use manual categorization
                                </label>
                            </div>
                            
                            <div id="categorizationMethodSelect">
                                <input type="hidden" name="categorizationMethod" id="categorizationMethod" value="auto">
                            </div>
                            
                            <div id="manualCategorySelect" style="display: none;">
                                <label for="defaultCategory" class="form-label">Choose Default Category</label>
                                <select class="form-select" id="defaultCategory" name="defaultCategory">
                                    <option value="HOLD">HOLD</option>
                                    <option value="AGRADE">AGRADE</option>
                                    <option value="BGRADE">BGRADE</option>
                                    <option value="CROSSCUT">CROSSCUT</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="import" class="btn btn-primary">
                                <i class="bi bi-cloud-upload me-1"></i> Import Data
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for interactions -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCategorizationMethod() {
            const useManual = document.getElementById('useManualCategorization').checked;
            document.getElementById('manualCategorySelect').style.display = useManual ? 'block' : 'none';
            document.getElementById('categorizationMethod').value = useManual ? 'manual' : 'auto';
        }
        
        // Check for success message in session storage
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('import_success')) {
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success';
                successAlert.innerText = 'Import completed successfully!';
                document.querySelector('.container').prepend(successAlert);
                
                // Remove the parameter after showing the message
                const url = new URL(window.location);
                url.searchParams.delete('import_success');
                window.history.replaceState({}, '', url);
                
                // Auto hide after 5 seconds
                setTimeout(() => {
                    successAlert.remove();
                }, 5000);
            }
        });
    </script>
</body>
</html>