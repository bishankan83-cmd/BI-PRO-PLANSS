<?php
session_start();
require_once 'vendor/autoload.php'; // For PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Initialize logging
$logFile = __DIR__ . '/logs/app.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", 
                   $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Create table if not exists
    $createTable = "
    CREATE TABLE IF NOT EXISTS attendance_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        department VARCHAR(100) NOT NULL,
        name VARCHAR(100) NOT NULL,
        employee_no VARCHAR(50) NOT NULL,
        date_time DATETIME NOT NULL,
        location_id VARCHAR(50),
        id_number VARCHAR(50),
        verify_code VARCHAR(50),
        card_no VARCHAR(50),
        UNIQUE KEY unique_employee_date (employee_no, date_time)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($createTable);
    
} catch(PDOException $e) {
    logMessage("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}

// Function to create Excel template
function createExcelTemplate($filename = 'attendance_template.xlsx') {
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = [
            'A1' => 'Department',
            'B1' => 'Name',
            'C1' => 'Employee No',
            'D1' => 'Date Time (YYYY-MM-DD HH:MM:SS)',
            'E1' => 'Location ID',
            'F1' => 'ID Number',
            'G1' => 'Verify Code',
            'H1' => 'Card No'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle($cell)->getFill()->getStartColor()->setARGB('FFE0E0E0');
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Add data validation
        $validation = $sheet->getDataValidation('D2:D1000');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DATE);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Invalid Date');
        $validation->setError('Please enter date in format: YYYY-MM-DD HH:MM:SS');
        $validation->setPromptTitle('Date Format');
        $validation->setPrompt('Enter date and time in format: YYYY-MM-DD HH:MM:SS');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        logMessage("Excel template created: $filename");
        return $filename;
    } catch (Exception $e) {
        logMessage("Failed to create Excel template: " . $e->getMessage());
        throw $e;
    }
}

// Function to validate employee number format - Updated to accept numbers only
function validateEmployeeNo($employee_no) {
    // Allow numeric employee numbers (can be just digits)
    return preg_match('/^\d+$/', $employee_no);
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Enhanced import function
function smartImportToDatabase($pdo, $filename) {
    try {
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if ($fileExtension === 'csv') {
            return importCSVToDatabase($pdo, $filename);
        }
        
        return importExcelToDatabase($pdo, $filename);
    } catch (Exception $e) {
        logMessage("Import failed: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Import failed: ' . $e->getMessage()
        ];
    }
}

// Excel import function
function importExcelToDatabase($pdo, $filename) {
    try {
        $reader = IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        
        $insertedCount = 0;
        $errors = [];
        
        $sql = "INSERT INTO attendance_records 
                (department, name, employee_no, date_time, location_id, id_number, verify_code, card_no) 
                VALUES (:department, :name, :employee_no, :date_time, :location_id, :id_number, :verify_code, :card_no)";
        $stmt = $pdo->prepare($sql);
        
        $pdo->beginTransaction();
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $department = sanitizeInput($sheet->getCell('A' . $row)->getValue() ?? '');
            $name = sanitizeInput($sheet->getCell('B' . $row)->getValue() ?? '');
            $employee_no = sanitizeInput($sheet->getCell('C' . $row)->getValue() ?? '');
            $date_time = $sheet->getCell('D' . $row)->getValue();
            $location_id = sanitizeInput($sheet->getCell('E' . $row)->getValue() ?? '');
            $id_number = sanitizeInput($sheet->getCell('F' . $row)->getValue() ?? '');
            $verify_code = sanitizeInput($sheet->getCell('G' . $row)->getValue() ?? '');
            $card_no = sanitizeInput($sheet->getCell('H' . $row)->getValue() ?? '');
            
            if (empty($department) && empty($name) && empty($employee_no)) {
                continue;
            }
            
            // Validate required fields and employee number format
            if (empty($department) || empty($name) || empty($employee_no) || empty($date_time)) {
                $errors[] = "Row $row: Missing required fields";
                continue;
            }
            
            if (!validateEmployeeNo($employee_no)) {
                $errors[] = "Row $row: Invalid employee number format (must be numeric)";
                continue;
            }
            
            if (is_numeric($date_time)) {
                $date_time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date_time)->format('Y-m-d H:i:s');
            }
            
            try {
                $stmt->execute([
                    ':department' => $department,
                    ':name' => $name,
                    ':employee_no' => $employee_no,
                    ':date_time' => $date_time,
                    ':location_id' => $location_id ?: null,
                    ':id_number' => $id_number ?: null,
                    ':verify_code' => $verify_code ?: null,
                    ':card_no' => $card_no ?: null
                ]);
                $insertedCount++;
            } catch (PDOException $e) {
                $errors[] = "Row $row: " . $e->getMessage();
            }
        }
        
        $pdo->commit();
        logMessage("Excel import completed: $insertedCount records inserted");
        return [
            'success' => true,
            'inserted' => $insertedCount,
            'errors' => $errors
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// CSV import function
function importCSVToDatabase($pdo, $filename) {
    try {
        $insertedCount = 0;
        $errors = [];
        
        $sql = "INSERT INTO attendance_records 
                (department, name, employee_no, date_time, location_id, id_number, verify_code, card_no) 
                VALUES (:department, :name, :employee_no, :date_time, :location_id, :id_number, :verify_code, :card_no)";
        $stmt = $pdo->prepare($sql);
        
        $pdo->beginTransaction();
        
        if (($handle = fopen($filename, "r")) !== FALSE) {
            // Detect and handle UTF-8 BOM
            $bom = fread($handle, 3);
            if ($bom != "\xEF\xBB\xBF") {
                rewind($handle);
            }
            
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row == 1) continue;
                
                if (count(array_filter($data)) == 0) continue;
                
                if (count($data) < 4) {
                    $errors[] = "Row $row: Insufficient data columns";
                    continue;
                }
                
                $department = sanitizeInput(trim($data[0] ?? ''));
                $name = sanitizeInput(trim($data[1] ?? ''));
                $employee_no = sanitizeInput(trim($data[2] ?? ''));
                $date_time = trim($data[3] ?? '');
                $location_id = sanitizeInput(trim($data[4] ?? ''));
                $id_number = sanitizeInput(trim($data[5] ?? ''));
                $verify_code = sanitizeInput(trim($data[6] ?? ''));
                $card_no = sanitizeInput(trim($data[7] ?? ''));
                
                if (empty($department) || empty($name) || empty($employee_no) || empty($date_time)) {
                    $errors[] = "Row $row: Missing required fields";
                    continue;
                }
                
                if (!validateEmployeeNo($employee_no)) {
                    $errors[] = "Row $row: Invalid employee number format (must be numeric)";
                    continue;
                }
                
                try {
                    $stmt->execute([
                        ':department' => $department,
                        ':name' => $name,
                        ':employee_no' => $employee_no,
                        ':date_time' => $date_time,
                        ':location_id' => $location_id ?: null,
                        ':id_number' => $id_number ?: null,
                        ':verify_code' => $verify_code ?: null,
                        ':card_no' => $card_no ?: null
                    ]);
                    $insertedCount++;
                } catch (PDOException $e) {
                    $errors[] = "Row $row: " . $e->getMessage();
                }
            }
            fclose($handle);
        }
        
        $pdo->commit();
        logMessage("CSV import completed: $insertedCount records inserted");
        return [
            'success' => true,
            'inserted' => $insertedCount,
            'errors' => $errors
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Export function
function exportDatabaseToExcel($pdo, $filename = 'attendance_export.xlsx') {
    try {
        $sql = "SELECT * FROM attendance_records ORDER BY date_time DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $records = $stmt->fetchAll();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['ID', 'Department', 'Name', 'Employee No', 'Date Time', 'Location ID', 'ID Number', 'Verify Code', 'Card No'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle($col . '1')->getFill()->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }
        
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $record['id']);
            $sheet->setCellValue('B' . $row, $record['department']);
            $sheet->setCellValue('C' . $row, $record['name']);
            $sheet->setCellValue('D' . $row, $record['employee_no']);
            $sheet->setCellValue('E' . $row, $record['date_time']);
            $sheet->setCellValue('F' . $row, $record['location_id']);
            $sheet->setCellValue('G' . $row, $record['id_number']);
            $sheet->setCellValue('H' . $row, $record['verify_code']);
            $sheet->setCellValue('I' . $row, $record['card_no']);
            $row++;
        }
        
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        logMessage("Database exported to: $filename");
        return $filename;
    } catch (Exception $e) {
        logMessage("Export failed: " . $e->getMessage());
        throw $e;
    }
}

// File upload processing
function processUploadedFile($pdo) {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != 0) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        return ['success' => false, 'message' => 'Invalid CSRF token'];
    }
    
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $originalName = $_FILES['excel_file']['name'];
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['xlsx', 'xls', 'csv'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Invalid file format. Please upload .xlsx, .xls, or .csv files only.'];
    }
    
    if ($_FILES['excel_file']['size'] > 10 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 10MB.'];
    }
    
    // Validate file MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['excel_file']['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'csv' => 'text/csv'
    ];
    
    if (!in_array($mime, $allowedMimes)) {
        return ['success' => false, 'message' => 'Invalid file type detected.'];
    }
    
    $filename = $uploadDir . time() . '_' . preg_replace("/[^A-Za-z0-9._-]/", '', $originalName);
    
    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $filename)) {
        $result = smartImportToDatabase($pdo, $filename);
        unlink($filename);
        return $result;
    }
    
    return ['success' => false, 'message' => 'File upload failed'];
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CLI interface
if (php_sapi_name() === 'cli') {
    echo "Attendance Records Excel Manager\n";
    echo "================================\n";
    
    while (true) {
        echo "\nChoose an option:\n";
        echo "1. Create Excel template\n";
        echo "2. Import Excel/CSV file\n";
        echo "3. Export database to Excel\n";
        echo "4. View recent records\n";
        echo "5. Exit\n";
        echo "Enter choice (1-5): ";
        
        $choice = trim(fgets(STDIN));
        
        switch ($choice) {
            case '1':
                $filename = createExcelTemplate();
                echo "Excel template created: $filename\n";
                break;
                
            case '2':
                echo "Enter path to Excel/CSV file: ";
                $filename = trim(fgets(STDIN));
                if (file_exists($filename)) {
                    $result = smartImportToDatabase($pdo, $filename);
                    if ($result['success']) {
                        echo "Import completed! Records inserted: " . $result['inserted'] . "\n";
                        if (!empty($result['errors'])) {
                            echo "Errors:\n";
                            foreach ($result['errors'] as $error) {
                                echo "- $error\n";
                            }
                        }
                    } else {
                        echo "Import failed: " . $result['message'] . "\n";
                    }
                } else {
                    echo "File not found!\n";
                }
                break;
                
            case '3':
                $filename = exportDatabaseToExcel($pdo);
                echo "Database exported to: $filename\n";
                break;
                
            case '4':
                $sql = "SELECT * FROM attendance_records ORDER BY date_time DESC LIMIT 10";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $records = $stmt->fetchAll();
                
                echo "\nRecent Records:\n";
                echo str_pad("ID", 5) . str_pad("Department", 15) . str_pad("Name", 20) . str_pad("Employee No", 15) . "Date Time\n";
                echo str_repeat("-", 70) . "\n";
                
                foreach ($records as $record) {
                    echo str_pad($record['id'], 5) . 
                         str_pad($record['department'], 15) . 
                         str_pad($record['name'], 20) . 
                         str_pad($record['employee_no'], 15) . 
                         $record['date_time'] . "\n";
                }
                break;
                
            case '5':
                echo "Goodbye!\n";
                exit;
                
            default:
                echo "Invalid choice!\n";
        }
    }
}

// Web interface handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'No action specified']);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'create_template':
            try {
                $filename = createExcelTemplate();
                echo json_encode(['success' => true, 'filename' => $filename]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Template creation failed']);
            }
            break;
            
        case 'import':
            $result = processUploadedFile($pdo);
            echo json_encode($result);
            break;
            
        case 'export':
            try {
                $filename = exportDatabaseToExcel($pdo);
                echo json_encode(['success' => true, 'filename' => $filename]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Export failed']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Web interface
if (!isset($_GET['api'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records Excel Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .button:hover { background: #005a87; }
        .button:disabled { background: #ccc; cursor: not-allowed; }
        .upload-area { border: 2px dashed #ccc; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .loading { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        h1, h2 { color: #333; }
        
        /* Loading spinner styles */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007cba;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007cba;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Attendance Records Excel Manager</h1>
        
        <h2>Create Excel Template</h2>
        <p>Download an Excel template with proper formatting:</p>
        <button class="button" onclick="createTemplate()">Download Template</button>
        
        <h2>Import Excel/CSV File</h2>
        <div class="upload-area">
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <p><strong>Supported formats:</strong> .xlsx, .xls, .csv</p>
                <p><strong>Note:</strong> Employee numbers must be numeric (e.g., 123, 456, 789)</p>
                <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                <br><br>
                <button type="submit" class="button">Import to Database</button>
            </form>
        </div>
        
        <h2>Export Database</h2>
        <p>Export all attendance records to Excel:</p>
        <button class="button" onclick="exportData()">Export to Excel</button>
        
        <div id="result"></div>
        
        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <h3>Processing Your File...</h3>
                <p>Please wait while we import your data to the database.</p>
            </div>
        </div>
    </div>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        function createTemplate() {
            showLoading();
            fetch('?api=1', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=create_template&csrf_token=<?php echo $_SESSION['csrf_token']; ?>'
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    window.location.href = data.filename;
                } else {
                    document.getElementById('result').innerHTML = `<div class="result error">
                        <h3>Error</h3>
                        <p>${data.message}</p>
                    </div>`;
                }
            })
            .catch(error => {
                hideLoading();
                document.getElementById('result').innerHTML = `<div class="result error">
                    <h3>Error</h3>
                    <p>Request failed: ${error.message}</p>
                </div>`;
            });
        }
        
        function exportData() {
            showLoading();
            fetch('?api=1', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=export&csrf_token=<?php echo $_SESSION['csrf_token']; ?>'
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    window.location.href = data.filename;
                } else {
                    document.getElementById('result').innerHTML = `<div class="result error">
                        <h3>Error</h3>
                        <p>${data.message}</p>
                    </div>`;
                }
            })
            .catch(error => {
                hideLoading();
                document.getElementById('result').innerHTML = `<div class="result error">
                    <h3>Error</h3>
                    <p>Request failed: ${error.message}</p>
                </div>`;
            });
        }
        
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            showLoading();
            
            // Disable the submit button to prevent multiple submissions
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span>Importing...';
            
            const formData = new FormData(this);
            formData.append('action', 'import');
            
            fetch('?api=1', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                // Re-enable the submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Import to Database';
                
                const resultDiv = document.getElementById('result');
                if (data.success) {
                    let html = `<div class="result success">
                        <h3>🎉 Import Successful!</h3>
                        <p><strong>Records inserted:</strong> ${data.inserted}</p>`;
                    if (data.errors && data.errors.length > 0) {
                        html += '<p><strong>Errors encountered:</strong></p><ul>';
                        data.errors.forEach(error => {
                            html += `<li>${error}</li>`;
                        });
                        html += '</ul>';
                    }
                    html += '<p>Your data has been successfully imported to the database!</p></div>';
                    resultDiv.innerHTML = html;
                    
                    // Clear the form
                    this.reset();
                } else {
                    resultDiv.innerHTML = `<div class="result error">
                        <h3>❌ Import Failed</h3>
                        <p>${data.message}</p>
                    </div>`;
                }
            })
            .catch(error => {
                hideLoading();
                
                // Re-enable the submit button
                submitButton.disabled = false;
                submitButton.innerHTML = 'Import to Database';
                
                document.getElementById('result').innerHTML = `<div class="result error">
                    <h3>❌ Error</h3>
                    <p>Request failed: ${error.message}</p>
                </div>`;
            });
        });
    </script>
</body>
</html>
<?php
}
?>