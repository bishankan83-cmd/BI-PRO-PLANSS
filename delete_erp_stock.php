<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
$host = "localhost";
$user = "planatir_task_managemen";
$pass = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Upload Excel File
if (isset($_POST["submit"])) {
    $file = $_FILES["excel_file"]["tmp_name"];

    // Load Spreadsheet
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $serialNumbers = [];

    // Extract serial numbers
    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // Skip header
        $serialNumbers[] = $row[0]; // Assuming serial_number is in the first column
    }

    if (!empty($serialNumbers)) {
        $serialNumbersStr = "'" . implode("','", $serialNumbers) . "'";

        // Step 1: Backup Deleted Data
        $backupQuery = "INSERT INTO stock_erp_backup (id, serial_number, date, tyre_code, description, qty)
                        SELECT * FROM stock_erp WHERE serial_number IN ($serialNumbersStr)";
        $conn->query($backupQuery);

        // Step 2: Delete Data from stock_erp
        $deleteQuery = "DELETE FROM stock_erp WHERE serial_number IN ($serialNumbersStr)";
        $conn->query($deleteQuery);
    }

    // Set a success message
    $_SESSION['import_success'] = true;

    // Redirect to another page
    header('Location: erp_stock_dash.php');
    exit;
}
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
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background-color:rgb(218, 128, 10);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
        }
        .form-control, .form-check {
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color:rgb(223, 189, 78);
            border: none;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color:rgb(0, 0, 0);
        }
        .btn-success {
            background-color: #1cc88a;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-success:hover {
            background-color: #17a673;
        }
        .alert {
            margin-top: 20px;
            border-radius: 8px;
        }
        .guidelines {
            background-color: #e8f4ff;
            border-left: 4px solid #4e73df;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .feature-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: #4e73df;
        }
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 30px;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f8f9fa;
            color: #4e73df;
        }
        .section-title {
            font-size: 24px;
            color: #4e73df;
            margin: 30px 0 15px 0;
            font-weight: 600;
        }
        .dash-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            height: 100%;
        }
        .dash-card-title {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .dash-card-value {
            font-size: 32px;
            font-weight: 600;
            color: #4e73df;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
            background-color: #e9ecef;
        }
        .tab.active {
            background-color: white;
            font-weight: bold;
            border-top: 3px solid #4e73df;
        }
        .tab-content {
            display: none;
            background-color: white;
            padding: 20px;
            border-radius: 0 5px 5px 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="m-0">Stock ERP - Excel Manager</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['import_success'])): ?>
                    <div class="alert alert-success">Data Imported Successfully!</div>
                    <?php unset($_SESSION['import_success']); ?>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Select Excel File to Import</label>
                        <input class="form-control" type="file" id="excel_file" name="excel_file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
