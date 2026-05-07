<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check for PhpSpreadsheet and install if not present
if (!file_exists('vendor/autoload.php')) {
    echo '<div class="notification warning">PhpSpreadsheet not found. Installing...</div>';
    // Create composer.json if it doesn't exist
    if (!file_exists('composer.json')) {
        file_put_contents('composer.json', json_encode([
            'require' => [
                'phpoffice/phpspreadsheet' => '^1.28'
            ]
        ], JSON_PRETTY_PRINT));
    }
    
    // Try to install via composer
    $output = null;
    $return_var = null;
    exec('composer install 2>&1', $output, $return_var);
    
    if ($return_var !== 0) {
        echo '<div class="notification error">Failed to install PhpSpreadsheet. Please run "composer require phpoffice/phpspreadsheet" manually.</div>';
    } else {
        echo '<div class="notification success">PhpSpreadsheet installed successfully!</div>';
    }
}

// If autoload exists, require it
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
}

// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen"; 
$password = "Bishan@1919"; 
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    echo "Error creating database: " . $conn->error;
}

// Select the database
$conn->select_db($dbname);

// Create tables if they don't exist - Added formatted_serial column to production_serial table
$sql = "CREATE TABLE IF NOT EXISTS production_serial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    formatted_serial VARCHAR(50) NOT NULL,
    icode VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_serial_number (serial_number),
    INDEX idx_formatted_serial (formatted_serial),
    INDEX idx_icode (icode)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating production_serial table: " . $conn->error;
}

$sql = "CREATE TABLE IF NOT EXISTS reverse_serial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    formatted_serial VARCHAR(50) NOT NULL,
    comment VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_serial_number (serial_number),
    INDEX idx_formatted_serial (formatted_serial)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating reverse_serial table: " . $conn->error;
}

// Function to format serial numbers according to the specified rules
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

// Process form for adding comments and inserting into database
if(isset($_POST['processData'])) {
    // Process each serial number
    foreach($_POST['serial_numbers'] as $index => $serial_number) {
        $icode = $_POST['icodes'][$index];
        $formatted_serial = formatSerialNumber($serial_number);
        
        if(empty($icode)) {
            // Missing icode - get comment
            $comment = !empty($_POST['custom_comments'][$index]) ? 
                        $_POST['custom_comments'][$index] : 
                        $_POST['comments'][$index];
            
            // Insert into reverse_serial table with formatted serial
            $stmt = $conn->prepare("INSERT INTO reverse_serial (serial_number, formatted_serial, comment) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE formatted_serial = VALUES(formatted_serial), comment = VALUES(comment)");
            $stmt->bind_param("sss", $serial_number, $formatted_serial, $comment);
            $result = $stmt->execute();
            $stmt->close();
        } else {
            // Insert into production_serial table with formatted serial
            $stmt = $conn->prepare("INSERT INTO production_serial (serial_number, formatted_serial, icode) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE formatted_serial = VALUES(formatted_serial), icode = VALUES(icode)");
            $stmt->bind_param("sss", $serial_number, $formatted_serial, $icode);
            $result = $stmt->execute();
            $stmt->close();
        }
    }
    
    $uploadMessage = '<div class="notification success">Data processed successfully! Records have been inserted into the appropriate tables with formatted serial numbers.</div>';
}

// Process Excel file upload
$uploadMessage = "";
$importData = [];
$hasData = false;

// Function to check if required headers exist
function checkRequiredHeaders($headers) {
    $required = ['serial_number', 'icode'];
    foreach ($required as $field) {
        if (!in_array(strtolower($field), array_map('strtolower', $headers))) {
            return false;
        }
    }
    return true;
}

// Process Excel file
if(isset($_POST['upload']) && isset($_FILES["excelFile"]) && $_FILES["excelFile"]["error"] == 0) {
    $filename = $_FILES["excelFile"]["name"];
    $filetype = $_FILES["excelFile"]["type"];
    $filesize = $_FILES["excelFile"]["size"];
    $tempFile = $_FILES["excelFile"]["tmp_name"];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Verify file size - 5MB maximum
    $maxsize = 5 * 1024 * 1024;
    if($filesize > $maxsize) {
        $uploadMessage = '<div class="notification error">File size is larger than the allowed limit (5MB)</div>';
    } 
    // Verify file extension
    elseif(!in_array($ext, ['xlsx', 'xls', 'csv'])) {
        $uploadMessage = '<div class="notification error">Please select a valid excel file format (xlsx, xls, csv)</div>';
    } 
    else {
        // Process the file
        if ($ext == "csv") {
            // Process CSV file
            if(($handle = fopen($tempFile, "r")) !== FALSE) {
                $header = fgetcsv($handle);
                
                // Check required headers
                if (!checkRequiredHeaders($header)) {
                    $uploadMessage = '<div class="notification error">CSV file must contain serial_number and icode columns</div>';
                } else {
                    // Map the actual column indexes (case-insensitive)
                    $headerLower = array_map('strtolower', $header);
                    $serialIndex = array_search('serial_number', $headerLower);
                    $icodeIndex = array_search('icode', $headerLower);
                    
                    while(($row = fgetcsv($handle)) !== FALSE) {
                        if (isset($row[$serialIndex]) && !empty($row[$serialIndex])) {
                            $serial_number = $row[$serialIndex];
                            $formatted_serial = formatSerialNumber($serial_number);
                            $importData[] = [
                                'serial_number' => $serial_number,
                                'formatted_serial' => $formatted_serial,
                                'icode' => isset($row[$icodeIndex]) ? $row[$icodeIndex] : ''
                            ];
                        }
                    }
                    fclose($handle);
                    $hasData = true;
                    $uploadMessage = '<div class="notification success">CSV file processed successfully!</div>';
                }
            } else {
                $uploadMessage = '<div class="notification error">Could not open the CSV file</div>';
            }
        } else {
            // Process Excel files (xlsx, xls) using PhpSpreadsheet
            if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    
                    // Get headers from first row
                    $header = $rows[0];
                    
                    // Check required headers
                    if (!checkRequiredHeaders($header)) {
                        $uploadMessage = '<div class="notification error">Excel file must contain serial_number and icode columns</div>';
                    } else {
                        // Map the actual column indexes (case-insensitive)
                        $headerLower = array_map('strtolower', $header);
                        $serialIndex = array_search('serial_number', $headerLower);
                        $icodeIndex = array_search('icode', $headerLower);
                        
                        // Skip header row
                        for ($i = 1; $i < count($rows); $i++) {
                            $row = $rows[$i];
                            if (isset($row[$serialIndex]) && !empty($row[$serialIndex])) {
                                $serial_number = $row[$serialIndex];
                                $formatted_serial = formatSerialNumber($serial_number);
                                $importData[] = [
                                    'serial_number' => $serial_number,
                                    'formatted_serial' => $formatted_serial,
                                    'icode' => isset($row[$icodeIndex]) ? $row[$icodeIndex] : ''
                                ];
                            }
                        }
                        
                        $hasData = true;
                        $uploadMessage = '<div class="notification success">Excel file processed successfully!</div>';
                    }
                } catch (Exception $e) {
                    $uploadMessage = '<div class="notification error">Error processing Excel file: ' . $e->getMessage() . '</div>';
                }
            } else {
                $uploadMessage = '<div class="notification error">PhpSpreadsheet library is required to process Excel files. Please install it using Composer or switch to CSV format.</div>';
            }
        }
    }
}

// Generate a sample Excel file template
if (isset($_GET['download_template'])) {
    if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = ['serial_number', 'icode'];
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $sheet->getStyleByColumnAndRow($col, 1)->getFont()->setBold(true);
            $col++;
        }
        
        // Add sample data
        $sampleData = [
            ['42500784', '743071'],
            ['42500783', '742071'],
            ['42500782', ''], // Example with missing icode
        ];
        
        $row = 2;
        foreach ($sampleData as $rowData) {
            $col = 1;
            foreach ($rowData as $value) {
                $sheet->setCellValueByColumnAndRow($col, $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Create Excel file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="serial_template.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    } else {
        echo '<div class="notification error">PhpSpreadsheet library is required to generate template.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serial Production Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #f28018;
            --secondary-color: #000000;
            --background-color: #f5f5f5;
            --white: #ffffff;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-700: #374151;
            --red-500: #ef4444;
            --green-500: #10b981;
            --yellow-500: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--gray-700);
            line-height: 1.5;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .card {
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            padding: 20px;
            color: white;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.2);
            outline: none;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .notification {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error {
            background-color: #fee2e2;
            color: var(--red-500);
            border-left: 4px solid var(--red-500);
        }

        .warning {
            background-color: #fef3c7;
            color: var(--yellow-500);
            border-left: 4px solid var(--yellow-500);
        }

        .success {
            background-color: #d1fae5;
            color: var(--green-500);
            border-left: 4px solid var(--green-500);
        }

        .table-container {
            overflow-x: auto;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody tr:nth-child(even) {
            background-color: var(--gray-100);
        }

        tbody tr:hover {
            background-color: rgba(242, 128, 24, 0.05);
        }

        .missing-icode {
            background-color: rgba(255, 224, 224, 0.5);
        }

        .comment-box {
            margin-top: 10px;
        }

        .comment-box label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--gray-700);
        }

        .comment-group {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .help-text {
            font-size: 14px;
            color: var(--gray-700);
            opacity: 0.7;
            margin-top: 5px;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-card {
            background-color: var(--white);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 200px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .stat-info h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .stat-info p {
            font-size: 14px;
            color: var(--gray-700);
            opacity: 0.8;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-error {
            background-color: #fee2e2;
            color: var(--red-500);
        }

        .status-success {
            background-color: #d1fae5;
            color: var(--green-500);
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .stats {
                flex-direction: column;
            }
            
            th, td {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Serial Production Management System</h1>
            <p>Upload, process, and manage production serial numbers efficiently</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-upload"></i> Upload Excel or CSV File</h2>
            </div>
            <div class="card-body">
                <div class="btn-group" style="margin-bottom: 20px;">
                    <a href="?download_template=1" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="excelFile">Select File:</label>
                        <input type="file" name="excelFile" id="excelFile" class="form-control" accept=".xlsx, .xls, .csv" required>
                        <p class="help-text">Supported formats: Excel (.xlsx, .xls) and CSV (.csv). Max file size: 5MB</p>
                    </div>
                    <div class="btn-group">
                        <button type="submit" name="upload" class="btn btn-primary">
                            <i class="fas fa-file-import"></i> Upload and Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php 
        // Display upload message if any
        echo $uploadMessage;
        
        // Display uploaded data if available
        if($hasData && !empty($importData)) {
            $missingIcodeCount = 0;
            $completeCount = 0;
            
            foreach($importData as $row) {
                if(empty($row['icode'])) {
                    $missingIcodeCount++;
                } else {
                    $completeCount++;
                }
            }
        ?>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($importData); ?></h3>
                        <p>Total Records</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completeCount; ?></h3>
                        <p>Complete Records</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $missingIcodeCount; ?></h3>
                        <p>Records With Issues</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-table"></i> Uploaded Data</h2>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Formatted Serial</th>
                                        <th>ICode</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($importData as $index => $row) {
                                        $serial_number = htmlspecialchars($row['serial_number']);
                                        $formatted_serial = htmlspecialchars($row['formatted_serial']);
                                        $icode = htmlspecialchars($row['icode']);
                                        
                                        $rowClass = empty($icode) ? 'missing-icode' : '';
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td>
                                            <?php echo $serial_number; ?>
                                            <input type="hidden" name="serial_numbers[]" value="<?php echo $serial_number; ?>">
                                        </td>
                                        <td>
                                            <?php echo $formatted_serial; ?>
                                            <input type="hidden" name="formatted_serials[]" value="<?php echo $formatted_serial; ?>">
                                        </td>
                                        <td>
                                            <?php echo $icode ? $icode : '<span style="color: var(--red-500);">Missing</span>'; ?>
                                            <input type="hidden" name="icodes[]" value="<?php echo $icode; ?>">
                                        </td>
                                        <td>
                                            <?php if(empty($icode)): ?>
                                                <span class="status-badge status-error">Missing ICode</span>
                                                <div class="comment-box">
                                                    <label>Comment:</label>
                                                    <div class="comment-group">
                                                        <select name="comments[]" class="form-control">
                                                            <option value="Missing ICode">Missing ICode</option>
                                                            <option value="Invalid Product">Invalid Product</option>
                                                            <option value="Awaiting Verification">Awaiting Verification</option>
                                                            <option value="Requires Manual Entry">Requires Manual Entry</option>
                                                        </select>
                                                        <span>or</span>
                                                        <input type="text" name="custom_comments[]" placeholder="Custom comment" class="form-control">
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="status-badge status-success">Complete</span>
                                                <input type="hidden" name="comments[]" value="">
                                                <input type="hidden" name="custom_comments[]" value="">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div style="margin-top: 30px;">
                            <p class="help-text">
                                <i class="fas fa-info-circle"></i> <?php echo $missingIcodeCount; ?> records with missing ICode will be inserted into reverse_serial table.
                            </p>
                            <p class="help-text">
                                <i class="fas fa-info-circle"></i> <?php echo $completeCount; ?> complete records will be inserted into production_serial table.
                            </p>
                        </div>
                        
                        <div class="btn-group" style="margin-top: 20px;">
                            <button type="submit" name="processData" class="btn btn-primary">
                                <i class="fas fa-database"></i> Process and Save Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
    // Example: Highlight table rows on hover
    $('tbody tr').hover(
        function() {
            $(this).css('background-color', 'rgba(242, 128, 24, 0.1)');
        },
        function() {
            // Reset to original background color based on even/odd row
            if ($(this).index() % 2 === 0) {
                $(this).css('background-color', '');
            } else {
                $(this).css('background-color', 'var(--gray-100)');
            }
            
            // Preserve the missing-icode background if applicable
            if ($(this).hasClass('missing-icode')) {
                $(this).css('background-color', 'rgba(255, 224, 224, 0.5)');
            }
        }
    );
    
    // Toggle between dropdown and custom comment input
    $('select[name="comments[]"]').change(function() {
        const customInput = $(this).closest('.comment-group').find('input[name="custom_comments[]"]');
        if ($(this).val() === 'Requires Manual Entry') {
            customInput.prop('disabled', false).focus();
        } else {
            customInput.prop('disabled', true).val('');
        }
    });
    
    // Form validation before submission
    $('form').submit(function(e) {
        let hasErrors = false;
        const missingIcodeRows = $('.missing-icode');
        
        missingIcodeRows.each(function() {
            const select = $(this).find('select[name="comments[]"]');
            const customInput = $(this).find('input[name="custom_comments[]"]');
            
            if (select.val() === 'Requires Manual Entry' && customInput.val().trim() === '') {
                customInput.css('border-color', 'var(--red-500)');
                hasErrors = true;
            } else {
                customInput.css('border-color', '');
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            alert('Please provide custom comments for all records marked as "Requires Manual Entry"');
        }
    });
    
    // Automatically adjust textarea height
    $('textarea.form-control').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Show/hide confirmation message
    setTimeout(function() {
        $('.notification').fadeOut(1000);
    }, 5000);
});