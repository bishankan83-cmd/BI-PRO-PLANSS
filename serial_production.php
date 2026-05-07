<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* your entire style section remains SAME as you posted */
        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .logo {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            font-size: 28px;
            color: #000000;
        }
        
        .logo span {
            color: #F28018;
        }
        
        .status-panel {
            background-color: #f8f9fa;
            border-left: 4px solid #F28018;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 8px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-form input[type="text"] {
            padding: 12px;
            width: 300px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Cantarell', sans-serif;
        }
        
        .button-primary {
            background-color: #000000;
            color: #FFFFFF;
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .button-primary:hover {
            background-color: #333333;
            transform: translateY(-2px);
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        select {
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Cantarell', sans-serif;
            background-color: white;
        }
        
        .stockr-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        
        .stockr-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 15px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .stockr-table td {
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 15px;
            text-align: left;
            font-family: 'Open Sans', sans-serif;
        }
        
        .stockr-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .card {
            border-radius: 15px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: #343a40;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            padding: 15px 20px;
            border-bottom: 3px solid #F28018;
        }
        
        .card-body {
            padding: 20px;
            background-color: white;
        }
        
        .card-body p {
            font-size: 16px;
            margin: 10px 0;
            line-height: 1.5;
        }
        
        .highlight-message {
            font-size: 16px;
            color: #ffffff;
            background-color: #343a40;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            border-left: 4px solid #F28018;
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        footer {
            margin-top: 40px;
            padding: 20px 0;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">STOCKR<span>.io</span></div>
        </header>
        
        <div class="status-panel">
            <?php
            // Database connection
            $servername = "localhost";    // your server
            $username = "planatir_task_managemen";  // your database username
            $password = "Bishan@1919";  // your database password
            $dbname = "planatir_task_managemen";    // your database name

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Get the latest production_date
            $sql = "SELECT production_date FROM production_serial ORDER BY production_date DESC LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "✅ Your last inserted data was on: <strong>" . date("d-m-Y", strtotime($row['production_date'])) . "</strong>";
            } else {
                echo "❌ No production data available.";
            }

            $conn->close();
            ?>
        </div>
          
    </div>
</body>
</html>



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
$username = "planatir_task_managemen"; // Change to your database username
$password = "Bishan@1919"; // Change to your database password
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

// Create tables if they don't exist - Add production_date column to both tables
$sql = "CREATE TABLE IF NOT EXISTS production_serial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    icode VARCHAR(50) NOT NULL,
    production_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_serial_number (serial_number),
    INDEX idx_icode (icode),
    INDEX idx_production_date (production_date)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating production_serial table: " . $conn->error;
}

$sql = "CREATE TABLE IF NOT EXISTS reverse_serial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(50) NOT NULL UNIQUE,
    comment VARCHAR(255) NOT NULL,
    production_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_serial_number (serial_number),
    INDEX idx_production_date (production_date)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating reverse_serial table: " . $conn->error;
}

// Process Excel file upload
$uploadMessage = "";
$importData = [];
$hasData = false;
$production_date = date('Y-m-d'); // Default to current date

// Process form for adding comments and inserting into database
if(isset($_POST['processData'])) {
    // Get the production date
    $production_date = $_POST['production_date'];
    
    // Process each serial number
    foreach($_POST['serial_numbers'] as $index => $serial_number) {
        $icode = $_POST['icodes'][$index];
        
        if(empty($icode)) {
            // Missing icode - get comment
            $comment = !empty($_POST['custom_comments'][$index]) ? 
                        $_POST['custom_comments'][$index] : 
                        $_POST['comments'][$index];
            
            // Insert into reverse_serial table - Add production_date
            $stmt = $conn->prepare("INSERT INTO reverse_serial (serial_number, comment, production_date) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE comment = VALUES(comment), production_date = VALUES(production_date)");
            $stmt->bind_param("sss", $serial_number, $comment, $production_date);
            $result = $stmt->execute();
            $stmt->close();
        } else {
            // Insert into production_serial table - Add production_date
            $stmt = $conn->prepare("INSERT INTO production_serial (serial_number, icode, production_date) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE icode = VALUES(icode), production_date = VALUES(production_date)");
            $stmt->bind_param("sss", $serial_number, $icode, $production_date);
            $result = $stmt->execute();
            $stmt->close();
        }
    }
    
    $uploadMessage = '<div class="notification success">Data processed successfully! Records have been inserted into the appropriate tables with production date: ' . htmlspecialchars($production_date) . '</div>';
}

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
    // Get the production date from the form
    $production_date = $_POST['production_date'];
    
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
                            $importData[] = [
                                'serial_number' => $row[$serialIndex],
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
                                $importData[] = [
                                    'serial_number' => $row[$serialIndex],
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

        .date-info {
            background-color: var(--gray-100);
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .date-label {
            font-weight: 600;
            color: var(--gray-700);
        }
        
        .date-value {
            color: var(--primary-color);
            font-weight: 500;
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
                        <label for="production_date"><i class="fas fa-calendar-alt"></i> Production Date:</label>
                        <input type="date" name="production_date" id="production_date" class="form-control" value="<?php echo $production_date; ?>" required>
                        <p class="help-text">Select the production date for this batch of serial numbers</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="excelFile"><i class="fas fa-file-excel"></i> Select File:</label>
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
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo date('d M Y', strtotime($production_date)); ?></h3>
                        <p>Production Date</p>
                    </div>
                </div>
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
                    <div class="date-info">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="date-label">Production Date:</span>
                        <span class="date-value"><?php echo date('d M Y', strtotime($production_date)); ?></span>
                    </div>
                    
                    <form action="" method="post">
                        <input type="hidden" name="production_date" value="<?php echo htmlspecialchars($production_date); ?>">
                        
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>ICode</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($importData as $index => $row) {
                                        $serial_number = htmlspecialchars($row['serial_number']);
                                        $icode = htmlspecialchars($row['icode']);
                                        
                                        $rowClass = empty($icode) ? 'missing-icode' : '';
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td>
                                            <?php echo $serial_number; ?>
                                            <input type="hidden" name="serial_numbers[]" value="<?php echo $serial_number; ?>">
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
                                <i class="fas fa-info-circle"></i> <?php echo $missingIcodeCount; ?> records with missing ICode will be inserted into reverse_serial table with production date <?php echo date('d M Y', strtotime($production_date)); ?>.
                            </p>
                            <p class="help-text">
                                <i class="fas fa-info-circle"></i> <?php echo $completeCount; ?> complete records will be inserted into production_serial table with production date <?php echo date('d M Y', strtotime($production_date)); ?>.
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
        // Add any JavaScript functionality here
        $(document).ready(function() {
            // Example: Highlight table rows on hover
            $('table tbody tr').hover(
                function() {
                    $(this).css('background-color', 'rgba(242, 128, 24, 0.1)');
                },
                function() {
                    $(this).css('background-color', '');
                }
            );
        });
    </script>
</body>
</html>