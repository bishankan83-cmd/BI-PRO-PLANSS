<?php
// Database configuration - Update these with your actual database details
$db_host = 'localhost';
$db_user = 'planatir_task_managemen';
$db_password = 'Bishan@1919';
$db_name = 'planatir_task_managemen';


// Connect to the database
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// For debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$message = '';
$status = '';
$processed = 0;
$not_found = 0;

// Process the uploaded Excel file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["excel_file"])) {
    // Check if file was uploaded without errors
    if ($_FILES["excel_file"]["error"] == 0) {
        $file_tmp = $_FILES["excel_file"]["tmp_name"];
        $file_ext = strtolower(pathinfo($_FILES["excel_file"]["name"], PATHINFO_EXTENSION));
        
        // Check file extension
        if ($file_ext == "xlsx" || $file_ext == "xls") {
            // Include the PhpSpreadsheet library (make sure to install it via Composer)
            require 'vendor/autoload.php';
            
            try {
                // Load the Excel file
                if ($file_ext == "xlsx") {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                }
                
                $spreadsheet = $reader->load($file_tmp);
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                
                // Begin transaction for data integrity
                $conn->begin_transaction();
                
                // Prepare statements for database operations
                $check_stmt = $conn->prepare("SELECT * FROM stock_erp_tem WHERE serial_number = ?");
                $insert_finish_stmt = $conn->prepare("INSERT INTO stock_erp_finish (serial_number, date, tyre_code, description, qty) VALUES (?, ?, ?, ?, ?)");
                $insert_stock_stmt = $conn->prepare("INSERT INTO stock_erp (prev_serial, serial_number, date, tyre_code, description, qty) VALUES (?, ?, ?, ?, ?, ?)");
                $delete_stmt = $conn->prepare("DELETE FROM stock_erp_tem WHERE id = ?");
                
                // Process each row in the Excel file (skip header row)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $serial_number = $worksheet->getCell('A' . $row)->getValue(); // Assuming serial_number is in column B
                    
                    if (empty($serial_number)) {
                        continue; // Skip empty rows
                    }
                    
                    // Check if serial_number exists in stock_erp_tem
                    $check_stmt->bind_param("s", $serial_number);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        // Found a match
                        $record = $result->fetch_assoc();
                        
                        // Insert into stock_erp_finish - Note: No prev_serial in this table based on your structure
                        $insert_finish_stmt->bind_param(
                            "ssssi",
                            $record['serial_number'],
                            $record['date'],
                            $record['tyre_code'],
                            $record['description'],
                            $record['qty']
                        );
                        $insert_finish_stmt->execute();
                        
                        // Insert into stock_erp
                        $insert_stock_stmt->bind_param(
                            "issssi",
                            $record['prev_serial'],
                            $record['serial_number'],
                            $record['date'],
                            $record['tyre_code'],
                            $record['description'],
                            $record['qty']
                        );
                        $insert_stock_stmt->execute();
                        
                        // Delete from stock_erp_tem
                        $delete_stmt->bind_param("i", $record['id']);
                        $delete_stmt->execute();
                        
                        $processed++;
                    } else {
                        $not_found++;
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                $message = "Excel file processed successfully. Processed $processed records. $not_found records not found.";
                $status = "success";

                 // Redirect to another page on success
                 header("Location: erp_stock_dash2.php");
                 exit();
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $message = "Error processing file: " . $e->getMessage();
                $status = "error";
            }
        } else {
            $message = "Only Excel files (.xlsx, .xls) are allowed.";
            $status = "error";
        }
    } else {
        $message = "Error uploading file. Error code: " . $_FILES["excel_file"]["error"];
        $status = "error";
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
    <title>Stock Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        
        .instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            display: inline-block;
            background: #4CAF50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn:hover {
            background: #45a049;
        }
        
        .alert {
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stock Transfer System</h1>
        
        <div class="instructions">
            <h3>Instructions</h3>
            <p>This system allows you to upload an Excel file containing stock data. The system will:</p>
            <ol>
                <li>Read the serial numbers from the Excel file</li>
                <li>Check if each serial number exists in the stock_erp_tem table</li>
                <li>For matching records: 
                    <ul>
                        <li>Insert the record into the stock_erp_finish table</li>
                        <li>Insert the record into the stock_erp table</li>
                        <li>Delete the record from the stock_erp_tem table</li>
                    </ul>
                </li>
            </ol>
            <p><strong>Note:</strong> Make sure your Excel file has a column with serial numbers (column B is used by default).</p>
        </div>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="excel_file">Select Excel File:</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx, .xls" required>
            </div>
            <button type="submit" class="btn">Process File</button>
        </form>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($status == "success" && $processed > 0): ?>
            <div class="results">
                <h3>Processing Summary</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Records processed successfully</td>
                            <td><?php echo $processed; ?></td>
                        </tr>
                        <tr>
                            <td>Records not found in database</td>
                            <td><?php echo $not_found; ?></td>
                        </tr>
                        <tr>
                            <td>Total records in Excel</td>
                            <td><?php echo $processed + $not_found; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>