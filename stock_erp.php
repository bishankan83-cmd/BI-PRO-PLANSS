<button onclick="window.location.href='qad.php'" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Go to Dashboard</button>




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
                
                // Update the SQL statement to include prev_serial column
                $stmt = $conn->prepare("INSERT INTO stock_erp_tem (serial_number, prev_serial, date, tyre_code, description, qty) VALUES (?, ?, ?, ?, ?, ?)");
                
                // Track statistics
                $rowsInserted = 0;
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
                    
                    // Bind parameters and execute
                    $stmt->bind_param("sssssi", $transformedSerialNumber, $originalSerialNumber, $mysqlDate, $tyreCode, $description, $qty);
                    if ($stmt->execute()) {
                        $rowsInserted++;
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                // Success message
                $successMsg = "Successfully imported $rowsInserted out of $totalRows records.";
                
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
                    <div class="dash-card-title">Pending Item</div>
                    <div class="dash-card-value"><?php echo number_format($totalTempRecords); ?></div>
                    <div class="mt-2">
                        <span class="badge badge-temp">Temp Table</span>
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
            
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div class="dash-card-title">Dashboard</div>
                    <a href="<?php echo $redirectUrl; ?>" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-right-circle me-1"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Tabbed Navigation for Tables -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="m-0">Stock Records</h3>
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
                                    <?php if (empty($lastTempRecords)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="empty-state">
                                                    <i class="bi bi-inbox"></i>
                                                    <p>No Pending Item found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($lastTempRecords as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['id']); ?></td>
                                                <td><?php echo htmlspecialchars($record['serial_number']); ?></td>
                                                <td><?php echo htmlspecialchars($record['prev_serial'] ?? 'N/A'); ?></td>
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
                                        <th>Original Serial</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($lastRecords)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="empty-state">
                                                    <i class="bi bi-inbox"></i>
                                                    <p>No records found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($lastRecords as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['id']); ?></td>
                                                <td><?php echo htmlspecialchars($record['serial_number']); ?></td>
                                                <td><?php echo htmlspecialchars($record['prev_serial'] ?? 'N/A'); ?></td>
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
                </div>
            </div>
        </div>
    </div>
    
    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="excelFile" class="form-label">Select Excel File to Import</label>
                            <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls, .csv" required>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="hasHeader" name="hasHeader" checked>
                            <label class="form-check-label" for="hasHeader">
                                First row contains headers
                            </label>
                        </div>
                        
                        <div class="guidelines mt-4">
                            <h5>Guidelines for preparing your Excel file:</h5>
                            <ol>
                                <li>Ensure your Excel file has these columns in order: Serial Number, Date, Tyre Code, Description, Quantity</li>
                                <li>Serial Number must be a unique identifier for each record</li>
                                <li>Serial Numbers will be formatted automatically:
                                    <ul>
                                        <li>Example: <span class="serial-format">32505031</span> → <span class="serial-format">032025-05031</span></li>
                                        <li>If already in the correct format like <span class="serial-format">032025-05031</span>, it will be kept as is</li>
                                        <li>Original format will be preserved in the database</li>
                                    </ul>
                                </li>
                                <li>Date should be in YYYY-MM-DD or DD/MM/YYYY format</li>
                                <li>Tyre Code is required for each row</li>
                                <li>Quantity should be a whole number</li>
                                <li>Empty rows will be skipped</li>
                            </ol>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="import" class="btn btn-primary">Import Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-close alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    // Don't auto-close alerts that have success buttons
                    if (!alert.querySelector('.btn-success')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });

        // Add notification to dashboard
        window.onload = function() {
            // Check if there's a successful import
            <?php if(!empty($successMsg)): ?>
            // Automatically redirect after 2 seconds
            setTimeout(function() {
                window.location.href = "<?php echo $redirectUrl; ?>";
            }, 2000);
            <?php endif; ?>
        };
    </script>
</body>
</html>