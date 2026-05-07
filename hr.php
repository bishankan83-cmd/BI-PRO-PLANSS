<?php
// config.php - Database configuration
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_plann';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Include PhpSpreadsheet library
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Handle form submission for single entry
if (isset($_POST['action']) && $_POST['action'] == 'add_attendance') {
    $number = $_POST['number'];
    $name = $_POST['name'];
    $location = $_POST['location'];
    $shift = $_POST['shift'];
    $in_time = $_POST['in_time'] ?: null;
    $out_time = $_POST['out_time'] ?: null;
    $in_time_2 = $_POST['in_time_2'] ?: null;
    $out_time_2 = $_POST['out_time_2'] ?: null;
    $late = isset($_POST['late']) ? 1 : 0;
    $not_present = isset($_POST['not_present']) ? 1 : 0;
    $dot = $_POST['dot'] ?: null;
    
    try {
        $sql = "INSERT INTO employee_attendance (Number, Name, Location, Shift, In_Time, Out_Time, In_Time_2, Out_Time_2, Late, NOT_Present, DOT) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$number, $name, $location, $shift, $in_time, $out_time, $in_time_2, $out_time_2, $late, $not_present, $dot]);
        $success_message = "Attendance record added successfully!";
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle Excel file upload
if (isset($_POST['action']) && $_POST['action'] == 'upload_excel' && isset($_FILES['excel_file'])) {
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = $_FILES['excel_file']['name'];
    $fileTmpName = $_FILES['excel_file']['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if ($fileExtension == 'xlsx' || $fileExtension == 'xls') {
        $uploadPath = $uploadDir . uniqid() . '_' . $fileName;
        
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            try {
                $spreadsheet = IOFactory::load($uploadPath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                $success_count = 0;
                $error_count = 0;
                $skipped_count = 0;
                $errors = [];
                
                // Skip header row (assuming first row contains headers)
                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Improved empty row detection
                    $hasData = false;
                    foreach ($row as $cell) {
                        if (!empty(trim($cell))) {
                            $hasData = true;
                            break;
                        }
                    }
                    
                    if (!$hasData) {
                        $skipped_count++;
                        continue;
                    }
                    
                    try {
                        // Map Excel columns to database fields with proper trimming
                        $number = !empty(trim($row[0])) ? trim($row[0]) : null;
                        $name = !empty(trim($row[1])) ? trim($row[1]) : null;
                        $location = !empty(trim($row[2])) ? trim($row[2]) : null;
                        $shift = !empty(trim($row[3])) ? trim($row[3]) : null;
                        $in_time = !empty(trim($row[4])) ? trim($row[4]) : null;
                        $out_time = !empty(trim($row[5])) ? trim($row[5]) : null;
                        $in_time_2 = !empty(trim($row[6])) ? trim($row[6]) : null;
                        $out_time_2 = !empty(trim($row[7])) ? trim($row[7]) : null;
                        $late = isset($row[8]) ? trim($row[8]) : '';
                        $not_present = isset($row[9]) ? trim($row[9]) : '';
                        $dot = !empty(trim($row[10])) ? trim($row[10]) : null;
                        
                        // Validate required fields more strictly
                        if (empty($number) || empty($name)) {
                            $errors[] = "Row " . ($i + 1) . ": Number and Name are required (Number: '$number', Name: '$name')";
                            $error_count++;
                            continue;
                        }
                        
                        // Validate number is actually numeric
                        if (!is_numeric($number)) {
                            $errors[] = "Row " . ($i + 1) . ": Number must be numeric (got: '$number')";
                            $error_count++;
                            continue;
                        }
                        
                        // Convert Excel time format to MySQL time format
                        $in_time = formatExcelTime($in_time);
                        $out_time = formatExcelTime($out_time);
                        $in_time_2 = formatExcelTime($in_time_2);
                        $out_time_2 = formatExcelTime($out_time_2);
                        $dot = formatExcelTime($dot);
                        
                        // Convert boolean values more comprehensively
                        $late = convertToBoolean($late);
                        $not_present = convertToBoolean($not_present);
                        
                        $sql = "INSERT INTO employee_attendance (Number, Name, Location, Shift, In_Time, Out_Time, In_Time_2, Out_Time_2, Late, NOT_Present, DOT) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$number, $name, $location, $shift, $in_time, $out_time, $in_time_2, $out_time_2, $late, $not_present, $dot]);
                        $success_count++;
                        
                    } catch(PDOException $e) {
                        $errors[] = "Row " . ($i + 1) . ": Database error - " . $e->getMessage();
                        $error_count++;
                    }
                }
                
                // Clean up uploaded file
                unlink($uploadPath);
                
                $bulk_message = "Excel import completed: $success_count records added, $error_count errors, $skipped_count empty rows skipped.";
                if (!empty($errors)) {
                    $bulk_errors = $errors;
                }
                
            } catch(Exception $e) {
                $error_message = "Error reading Excel file: " . $e->getMessage();
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
            }
        } else {
            $error_message = "Error uploading file.";
        }
    } else {
        $error_message = "Please upload a valid Excel file (.xlsx or .xls).";
    }
}

// Function to convert various boolean representations to 0 or 1
function convertToBoolean($value) {
    if (empty($value)) {
        return 0;
    }
    
    $value = strtolower(trim($value));
    
    if (in_array($value, ['yes', 'true', '1', 'y', 't'])) {
        return 1;
    }
    
    if (in_array($value, ['no', 'false', '0', 'n', 'f'])) {
        return 0;
    }
    
    // If numeric, treat as boolean
    if (is_numeric($value)) {
        return (int)$value > 0 ? 1 : 0;
    }
    
    return 0; // Default to false
}

// Function to format Excel time to MySQL time format
function formatExcelTime($excelTime) {
    if (empty($excelTime)) {
        return null;
    }
    
    $excelTime = trim($excelTime);
    
    // If it's already in HH:MM:SS format
    if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $excelTime)) {
        return $excelTime;
    }
    
    // If it's in HH:MM format
    if (preg_match('/^\d{1,2}:\d{2}$/', $excelTime)) {
        return $excelTime . ':00';
    }
    
    // If it's an Excel serial number
    if (is_numeric($excelTime)) {
        try {
            $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelTime);
            return $time->format('H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }
    
    return null;
}

// Generate Excel template
if (isset($_GET['download_template'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $headers = ['Number', 'Name', 'Location', 'Shift', 'In_Time', 'Out_Time', 'In_Time_2', 'Out_Time_2', 'Late', 'NOT_Present', 'DOT'];
    $sheet->fromArray($headers, null, 'A1');
    
    // Style the header row
    $sheet->getStyle('A1:K1')->getFont()->setBold(true);
    $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setARGB('FFCCCCCC');
    
    // Add sample data
    $sampleData = [
        [1, 'John Doe', 'Office A', 'Morning', '09:00:00', '17:00:00', '', '', 'No', 'No', ''],
        [2, 'Jane Smith', 'Office B', 'Evening', '14:00:00', '22:00:00', '', '', 'No', 'No', ''],
        [3, 'Bob Johnson', 'Office A', 'Night', '22:00:00', '06:00:00', '', '', 'Yes', 'No', '']
    ];
    $sheet->fromArray($sampleData, null, 'A2');
    
    // Auto-size columns
    foreach (range('A', 'K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Add data validation for boolean columns
    $validation = $sheet->getCell('I2')->getDataValidation();
    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(true);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input error');
    $validation->setError('Value is not in list.');
    $validation->setPromptTitle('Pick from list');
    $validation->setPrompt('Please pick a value from the drop-down list.');
    $validation->setFormula1('"Yes,No"');
    
    // Copy validation to other cells
    $sheet->duplicateStyle($sheet->getStyle('I2'), 'I3:I1000');
    $sheet->duplicateStyle($sheet->getStyle('I2'), 'J2:J1000');
    
    // Set response headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="attendance_template.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Export current data to Excel
if (isset($_GET['export_data'])) {
    try {
        $sql = "SELECT * FROM employee_attendance ORDER BY Number";
        $stmt = $pdo->query($sql);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headers = ['Number', 'Name', 'Location', 'Shift', 'In_Time', 'Out_Time', 'In_Time_2', 'Out_Time_2', 'Late', 'NOT_Present', 'DOT'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style the header row
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFCCCCCC');
        
        // Add data
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $record['Number']);
            $sheet->setCellValue('B' . $row, $record['Name']);
            $sheet->setCellValue('C' . $row, $record['Location']);
            $sheet->setCellValue('D' . $row, $record['Shift']);
            $sheet->setCellValue('E' . $row, $record['In_Time']);
            $sheet->setCellValue('F' . $row, $record['Out_Time']);
            $sheet->setCellValue('G' . $row, $record['In_Time_2']);
            $sheet->setCellValue('H' . $row, $record['Out_Time_2']);
            $sheet->setCellValue('I' . $row, $record['Late'] ? 'Yes' : 'No');
            $sheet->setCellValue('J' . $row, $record['NOT_Present'] ? 'Yes' : 'No');
            $sheet->setCellValue('K' . $row, $record['DOT']);
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="attendance_export_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    } catch(Exception $e) {
        $error_message = "Error exporting data: " . $e->getMessage();
    }
}

// Fetch existing records for display
$records = [];
try {
    $sql = "SELECT * FROM employee_attendance ORDER BY Number DESC LIMIT 10";
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $fetch_error = "Error fetching records: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance - Excel Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success { color: green; border: 1px solid green; background-color: #f0fff0; }
        .error { color: red; border: 1px solid red; background-color: #fff0f0; }
        .info { color: blue; border: 1px solid blue; background-color: #f0f0ff; }
        .button {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            margin-right: 10px;
            border-radius: 4px;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        .button.green { background: #28a745; }
        .button.blue { background: #007cba; }
        .form-section {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: #fafafa;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .form-table td {
            border: none;
            padding: 5px;
        }
        .form-table input, .form-table select {
            width: 200px;
            padding: 5px;
        }
        .instructions {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Employee Attendance Management System</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="message success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($bulk_message)): ?>
            <div class="message success">
                <?php echo $bulk_message; ?>
                <?php if (isset($bulk_errors) && !empty($bulk_errors)): ?>
                    <div style="margin-top: 10px;">
                        <strong>Errors (showing first 20):</strong>
                        <ul>
                            <?php foreach (array_slice($bulk_errors, 0, 20) as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                            <?php if (count($bulk_errors) > 20): ?>
                                <li><em>... and <?php echo count($bulk_errors) - 20; ?> more errors</em></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="margin: 20px 0;">
            <a href="?download_template=1" class="button blue">Download Excel Template</a>
            <a href="?export_data=1" class="button green">Export Current Data</a>
        </div>
        
        <h2>Upload Excel File</h2>
        <div class="instructions">
            <p><strong>Excel Format Instructions:</strong></p>
            <ul>
                <li><strong>Required columns:</strong> Number (must be numeric), Name (cannot be empty)</li>
                <li><strong>Optional columns:</strong> Location, Shift, In_Time, Out_Time, In_Time_2, Out_Time_2, Late, NOT_Present, DOT</li>
                <li><strong>Time format:</strong> HH:MM:SS (e.g., 09:30:00) or HH:MM (e.g., 09:30)</li>
                <li><strong>Boolean values:</strong> Use Yes/No, True/False, 1/0, or Y/N</li>
                <li><strong>First row must contain column headers</strong></li>
                <li><strong>Empty rows will be automatically skipped</strong></li>
            </ul>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="form-section">
            <input type="hidden" name="action" value="upload_excel">
            <input type="file" name="excel_file" accept=".xlsx,.xls" required style="margin: 10px 0;">
            <br>
            <input type="submit" value="Upload Excel File" class="button blue">
        </form>
        
        <h2>Add Single Record</h2>
        <form method="POST" class="form-section">
            <input type="hidden" name="action" value="add_attendance">
            
            <table class="form-table">
                <tr>
                    <td><label>Employee Number*:</label></td>
                    <td><input type="number" name="number" required></td>
                    <td><label>Employee Name*:</label></td>
                    <td><input type="text" name="name" required></td>
                </tr>
                <tr>
                    <td><label>Location:</label></td>
                    <td><input type="text" name="location"></td>
                    <td><label>Shift:</label></td>
                    <td>
                        <select name="shift">
                            <option value="">Select Shift</option>
                            <option value="Morning">Morning</option>
                            <option value="Evening">Evening</option>
                            <option value="Night">Night</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>In Time:</label></td>
                    <td><input type="time" name="in_time"></td>
                    <td><label>Out Time:</label></td>
                    <td><input type="time" name="out_time"></td>
                </tr>
                <tr>
                    <td><label>In Time 2:</label></td>
                    <td><input type="time" name="in_time_2"></td>
                    <td><label>Out Time 2:</label></td>
                    <td><input type="time" name="out_time_2"></td>
                </tr>
                <tr>
                    <td><label>Late:</label></td>
                    <td><input type="checkbox" name="late"></td>
                    <td><label>Not Present:</label></td>
                    <td><input type="checkbox" name="not_present"></td>
                </tr>
                <tr>
                    <td><label>DOT:</label></td>
                    <td><input type="time" name="dot"></td>
                    <td colspan="2"><em>* Required fields</em></td>
                </tr>
            </table>
            
            <input type="submit" value="Add Record" class="button green" style="margin-top: 10px;">
        </form>
        
        <h2>Recent Records (Last 10)</h2>
        <?php if (isset($fetch_error)): ?>
            <div class="message error"><?php echo $fetch_error; ?></div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Shift</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>In Time 2</th>
                        <th>Out Time 2</th>
                        <th>Late</th>
                        <th>Not Present</th>
                        <th>DOT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['Number']); ?></td>
                        <td><?php echo htmlspecialchars($record['Name']); ?></td>
                        <td><?php echo htmlspecialchars($record['Location'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($record['Shift'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($record['In_Time'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($record['Out_Time'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($record['In_Time_2'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($record['Out_Time_2'] ?? ''); ?></td>
                        <td><?php echo $record['Late'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $record['NOT_Present'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars($record['DOT'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="11" style="text-align: center;">No records found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>