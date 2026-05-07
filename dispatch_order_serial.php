<?php
require 'vendor/autoload.php'; // Composer autoload for PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Function to check if serial number is already properly formatted
function isValidSerialFormat($serialNumber) {
    // Check if it already follows the pattern X1XYYYY-NNNNN
    // For example: 032025-05031 or 112020-01556
    return preg_match('/^\d{2}20\d{2}-\d+$/', $serialNumber);
}

// Function to format serial number only if it's not already formatted
function formatSerialNumber($serialNumber) {
    // If already in correct format, return it unchanged
    if (isValidSerialFormat($serialNumber)) {
        return $serialNumber;
    }
    
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

// Database connection configuration
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to validate and sanitize input
function validateInput($value, $type = 'string') {
    $value = trim($value);
    
    switch($type) {
        case 'int':
            return filter_var($value, FILTER_VALIDATE_INT) !== false ? $value : null;
        case 'string':
        default:
            return !empty($value) ? $value : null;
    }
}

// Handle file upload
$errors = [];
$successCount = 0;
$failedRows = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['excel_file'])) {
    $allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel' // .xls
    ];

    // Validate file type
    if (!in_array($_FILES['excel_file']['type'], $allowedTypes)) {
        $errors[] = "Invalid file type. Please upload an Excel file.";
    }

    if (empty($errors)) {
        try {
            // Start a database transaction
            $pdo->beginTransaction();

            // Load the Excel file
            $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            // Prepare the insert statement
            $sql = "INSERT INTO dwork_ser_tem (serial_number, icode, description, ref, erp) 
                    VALUES (:serial_number, :icode, :description, :ref, :erp)";
            $stmt = $pdo->prepare($sql);

            // Start from row 2 to skip headers
            for ($row = 2; $row <= $highestRow; $row++) {
                // Read row data
                $serial_number = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $icode = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $description = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $ref = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $erp = $worksheet->getCellByColumnAndRow(6, $row)->getValue();

                // Skip completely empty rows
                if (empty($serial_number) && empty($icode) && empty($erp)) {
                    continue;
                }

                // Validate required fields
                if (empty($serial_number) || empty($icode) || empty($erp)) {
                    $failedRows[] = [
                        'row' => $row, 
                        'reason' => 'Missing required fields (Serial Number, ICode, or ERP)'
                    ];
                    continue;
                }

                // Format serial number only if not already formatted
                $formatted_serial_number = formatSerialNumber($serial_number);

                // Sanitize inputs
                $sanitized_serial_number = validateInput($formatted_serial_number);
                $sanitized_icode = validateInput($icode);
                $sanitized_description = validateInput($description);
                $sanitized_ref = validateInput($ref);
                $sanitized_erp = validateInput($erp, 'int');

                // Validate ERP as integer
                if ($sanitized_erp === null) {
                    $failedRows[] = [
                        'row' => $row, 
                        'reason' => 'Invalid ERP value'
                    ];
                    continue;
                }

                try {
                    // Bind parameters
                    $stmt->bindParam(':serial_number', $sanitized_serial_number);
                    $stmt->bindParam(':icode', $sanitized_icode);
                    $stmt->bindParam(':description', $sanitized_description);
                    $stmt->bindParam(':ref', $sanitized_ref);
                    $stmt->bindParam(':erp', $sanitized_erp, PDO::PARAM_INT);

                    // Execute the statement
                    $stmt->execute();
                    $successCount++;
                } catch(PDOException $e) {
                    $failedRows[] = [
                        'row' => $row, 
                        'reason' => $e->getMessage()
                    ];
                }
            }

            // Commit the transaction if no failures
            if (empty($failedRows)) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }

        } catch(Exception $e) {
            // Rollback the transaction in case of any error
            $pdo->rollBack();
            $errors[] = "Error processing Excel file: " . $e->getMessage();
        }
    }
}

// If import is successful, redirect to display page
if ($successCount > 0 && empty($errors) && empty($failedRows)) {
    // Store success message in session
    session_start();
    $_SESSION['import_success'] = "Successfully imported $successCount records.";
    
    // Redirect to the display page
    header("Location: dispatch_order_serial2.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dwork_ser_tem Excel Import</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        
        h1, h2, h3 {
            font-family: 'Cantarell', sans-serif;
            color: #343a40;
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center;
            color: #343a40;
            border-bottom: 3px solid #F28018;
            padding-bottom: 10px;
        }
        
        h2 {
            font-size: 24px;
            margin: 20px 0 15px;
            color: #343a40;
        }
        
        /* Card Styles */
        .card {
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            background-color: #fff;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #343a40;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 2px solid #F28018;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #343a40;
        }
        
        input[type="file"] {
            display: block;
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
            transition: border-color 0.3s;
        }
        
        input[type="file"]:hover {
            border-color: #F28018;
        }
        
        .btn {
            display: inline-block;
            background-color: #F28018;
            color: #fff;
            font-weight: 600;
            padding: 12px 25px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            background-color: #e07016;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        /* Table Styles */
        .stockr-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .stockr-table th {
            background-color: #F28018;
            color: #fff;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 15px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .stockr-table td {
            border: 1px solid #e9ecef;
            padding: 12px 15px;
            text-align: left;
            font-family: 'Open Sans', sans-serif;
            font-weight: 400;
            color: #495057;
        }
        
        .stockr-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .stockr-table tr:hover {
            background-color: #f1f3f5;
        }
        
        /* Alert Styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 5px solid;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        /* Template Info Styles */
        .template-info {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .template-info h2 {
            color: #343a40;
            border-bottom: 2px solid #F28018;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .template-info ol, .template-info ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        
        .template-info li {
            margin-bottom: 8px;
        }
        
        .template-info ul li {
            list-style-type: circle;
        }
        
        /* Additional Styles from User Requirements */
        .search-form {
            text-align: center;
            margin: 20px 0;
        }
        
        .search-form input[type="text"] {
            padding: 12px;
            width: 300px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Cantarell', sans-serif;
            margin-right: 10px;
        }
        
        .search-form button {
            background-color: #343a40;
            color: #FFFFFF;
            padding: 12px 25px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-form button:hover {
            background-color: #495057;
        }
        
        .select-container {
            margin: 20px 0;
            text-align: center;
        }
        
        select {
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Cantarell', sans-serif;
            background-color: #fff;
            min-width: 200px;
        }
        
        .highlight-message {
            font-size: 16px;
            color: #fff;
            background-color: #343a40;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            border-left: 5px solid #F28018;
        }
        
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
        .blink {
            animation: blink 2s infinite;
        }
        
        .button-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 60px;
            margin-bottom: 20px;
        }
        
        .file-upload-input {
            position: relative;
            width: 100%;
            height: 100%;
            outline: none;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }
        
        .file-upload-text {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #f8f9fa;
            border: 2px dashed #adb5bd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .file-upload-text i {
            margin-right: 10px;
            font-size: 24px;
        }
        
        .file-upload-wrapper:hover .file-upload-text {
            border-color: #F28018;
            color: #F28018;
        }
        
        .selected-file {
            margin-top: 10px;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-file-excel"></i> dwork_ser_tem Excel Import</h1>
        
        <div class="card">
            <div class="card-header">
                Upload Excel Data
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="excel_file">Select Excel File:</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" required class="file-upload-input">
                            <div class="file-upload-text">
                                <i class="fas fa-cloud-upload-alt"></i> Drag & drop your Excel file or click to browse
                            </div>
                        </div>
                        <div class="selected-file" id="selectedFile">No file selected</div>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="btn">
                            <i class="fas fa-upload"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        // Display errors
        if (!empty($errors)) {
            echo '<div class="alert alert-danger">';
            foreach ($errors as $error) {
                echo "<p><i class='fas fa-exclamation-circle'></i> $error</p>";
            }
            echo '</div>';
        }
        
        // Display success message
        if ($successCount > 0 && empty($failedRows)) {
            echo '<div class="alert alert-success">';
            echo "<p><i class='fas fa-check-circle'></i> Successfully imported $successCount records.</p>";
            echo '</div>';
        }
        
        // Display failed rows
        if (!empty($failedRows)) {
            echo "<div class='card'>";
            echo "<div class='card-header'><i class='fas fa-exclamation-triangle'></i> Failed Rows</div>";
            echo "<div class='card-body'>";
            echo "<table class='stockr-table'>";
            echo "<tr><th>Row</th><th>Reason</th></tr>";
            foreach ($failedRows as $failedRow) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($failedRow['row']) . "</td>";
                echo "<td>" . htmlspecialchars($failedRow['reason']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            echo "</div>";
        }
        ?>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Excel File Template
            </div>
            <div class="card-body">
                <p>Please use the following column order in your Excel file:</p>
                <ol>
                    <li><strong>ID</strong> (Optional, will be auto-generated)</li>
                    <li><strong>Serial Number</strong> (Required) - Can be either:
                        <ul>
                            <li>Already formatted as X1XYYYY-NNNNN (e.g., 032025-05031)</li>
                            <li>Unformatted numbers that will be automatically formatted</li>
                        </ul>
                    </li>
                    <li><strong>ICode</strong> (Required)</li>
                    <li><strong>Description</strong> (Optional)</li>
                    <li><strong>Reference</strong> (Optional)</li>
                    <li><strong>ERP</strong> (Required, Integer)</li>
                </ol>
                <div class="highlight-message">
                    <i class="fas fa-lightbulb"></i> Make sure all required fields are populated in your Excel file to avoid import errors!
                </div>
            </div>
        </div>
        
        
    </div>
    
    <script>
        // Display selected filename
        document.getElementById('excel_file').addEventListener('change', function() {
            var fileName = this.files[0] ? this.files[0].name : 'No file selected';
            document.getElementById('selectedFile').textContent = fileName;
            
            if (this.files[0]) {
                document.getElementById('selectedFile').style.color = '#28a745';
                document.querySelector('.file-upload-text').style.borderColor = '#28a745';
                document.querySelector('.file-upload-text').innerHTML = '<i class="fas fa-check"></i> File selected';
            } else {
                document.getElementById('selectedFile').style.color = '#6c757d';
                document.querySelector('.file-upload-text').style.borderColor = '#adb5bd';
                document.querySelector('.file-upload-text').innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Drag & drop your Excel file or click to browse';
            }
        });
    </script>
</body>
</html>