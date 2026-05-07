<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock ERP Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            background-color: #f7f9fc;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .alert {
            margin-top: 20px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .match {
            background-color: #d1e7dd !important;
        }
        .mismatch {
            background-color: #f8d7da !important;
        }
        .new-entry {
            background-color: #cfe2ff !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1 class="my-4 text-center">Stock ERP Management System</h1>
        
        <?php
        // Database connection
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            die('<div class="alert alert-danger">Connection failed: ' . $conn->connect_error . '</div>');
        }

        // Process Excel upload
        if(isset($_POST['upload'])) {
            if(isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
                $file_name = $_FILES['excel_file']['name'];
                $file_tmp = $_FILES['excel_file']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Check if file is Excel
                if($file_ext == "xlsx" || $file_ext == "xls" || $file_ext == "csv") {
                    // Create upload directory if it doesn't exist
                    $upload_dir = "uploads/";
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $new_file_name = $upload_dir . time() . "_" . $file_name;
                    
                    if(move_uploaded_file($file_tmp, $new_file_name)) {
                        // Process Excel file
                        require 'vendor/autoload.php'; // Make sure you have PHPSpreadsheet installed
                        
                        if($file_ext == "csv") {
                            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                        } else {
                            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                        }
                        
                        $spreadsheet = $reader->load($new_file_name);
                        $worksheet = $spreadsheet->getActiveSheet();
                        $rows = $worksheet->toArray();
                        
                        // Skip header row
                        array_shift($rows);
                        
                        $success_count = 0;
                        
                        foreach($rows as $row) {
                            if(isset($row[0]) && $row[0] != '') {
                                // Adjusted for the new Excel format (missing prev_serial)
                                $serial_number = $conn->real_escape_string($row[0]);
                                $date = date('Y-m-d', strtotime($row[1]));
                                $tyre_code = $conn->real_escape_string($row[2]);
                                $description = $conn->real_escape_string($row[3]);
                                $qty = intval($row[4]);
                                
                                // Set prev_serial to blank for now
                                $prev_serial = '';
                                
                                $sql = "INSERT INTO stock_erp_tem (prev_serial, serial_number, date, tyre_code, description, qty) 
                                        VALUES ('$prev_serial', '$serial_number', '$date', '$tyre_code', '$description', $qty)";
                                
                                if($conn->query($sql) === TRUE) {
                                    $success_count++;
                                }
                            }
                        }
                        
                        echo '<div class="alert alert-success">' . $success_count . ' records imported successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger">Error uploading file!</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Only Excel files (XLSX, XLS, CSV) are allowed!</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Please select a file to upload!</div>';
            }
        }
        
        // Process the transfer from temp to main table
        if(isset($_POST['transfer'])) {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Find all serial_numbers in temp table
                $sql = "SELECT DISTINCT serial_number FROM stock_erp_tem";
                $result = $conn->query($sql);
                
                $transferred = 0;
                
                if($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $serial = $conn->real_escape_string($row['serial_number']);
                        
                        // Get all records with this serial number
                        $get_sql = "SELECT * FROM stock_erp_tem WHERE serial_number = '$serial'";
                        $get_result = $conn->query($get_sql);
                        
                        while($temp_row = $get_result->fetch_assoc()) {
                            // Insert into main table
                            $insert_sql = "INSERT INTO stock_erp (prev_serial, serial_number, date, tyre_code, description, qty) 
                                          VALUES (
                                              '{$conn->real_escape_string($temp_row['prev_serial'])}',
                                              '{$conn->real_escape_string($temp_row['serial_number'])}',
                                              '{$temp_row['date']}',
                                              '{$conn->real_escape_string($temp_row['tyre_code'])}',
                                              '{$conn->real_escape_string($temp_row['description'])}',
                                              {$temp_row['qty']}
                                          )";
                            
                            $conn->query($insert_sql);
                            $transferred++;
                        }
                        
                        // Delete from temp table
                        $delete_sql = "DELETE FROM stock_erp_tem WHERE serial_number = '$serial'";
                        $conn->query($delete_sql);
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    echo '<div class="alert alert-success">' . $transferred . ' records transferred successfully!</div>';
                } else {
                    echo '<div class="alert alert-info">No records found in temporary table for transfer.</div>';
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                echo '<div class="alert alert-danger">Error during transfer: ' . $e->getMessage() . '</div>';
            }
        }
        
        // Generate Excel template
        if(isset($_POST['generate_template'])) {
            require 'vendor/autoload.php'; // Make sure you have PHPSpreadsheet installed
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers for the updated format
            $headers = ['serial_number', 'date', 'tyre_code', 'description', 'qty'];
            $col = 'A';
            foreach($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $col++;
            }
            
            // Create writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="stock_erp_template.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        }

        // Get comparison table name if set
        $compare_table = isset($_POST['compare_table']) ? $_POST['compare_table'] : '';
        
        // Get available tables for comparison
        $tables_query = "SHOW TABLES";
        $tables_result = $conn->query($tables_query);
        $available_tables = [];
        
        if ($tables_result->num_rows > 0) {
            while($table = $tables_result->fetch_row()) {
                if ($table[0] != 'stock_erp_tem' && $table[0] != 'stock_erp') {
                    $available_tables[] = $table[0];
                }
            }
        }

        // Comparison logic
        $comparison_results = [];
        $show_comparison = false;
        
        if(isset($_POST['compare']) && !empty($compare_table)) {
            $show_comparison = true;
            
            // Check if comparison table has the right structure
            $table_check_query = "SHOW COLUMNS FROM `$compare_table`";
            $table_check_result = $conn->query($table_check_query);
            $valid_table = false;
            
            if ($table_check_result) {
                $columns = [];
                while($column = $table_check_result->fetch_assoc()) {
                    $columns[] = $column['Field'];
                }
                
                // Check if the table has at least a serial_number field
                if (in_array('serial_number', $columns)) {
                    $valid_table = true;
                    
                    // Get all serial numbers from temp table
                    $temp_serials_query = "SELECT DISTINCT serial_number FROM stock_erp_tem";
                    $temp_serials_result = $conn->query($temp_serials_query);
                    
                    if ($temp_serials_result->num_rows > 0) {
                        while($row = $temp_serials_result->fetch_assoc()) {
                            $serial = $conn->real_escape_string($row['serial_number']);
                            
                            // Check if this serial exists in the comparison table
                            $check_query = "SELECT * FROM `$compare_table` WHERE serial_number = '$serial'";
                            $check_result = $conn->query($check_query);
                            
                            if ($check_result->num_rows > 0) {
                                // Serial exists in comparison table - check details
                                $compare_row = $check_result->fetch_assoc();
                                
                                // Get temp table data
                                $temp_query = "SELECT * FROM stock_erp_tem WHERE serial_number = '$serial'";
                                $temp_result = $conn->query($temp_query);
                                $temp_row = $temp_result->fetch_assoc();
                                
                                $matches = [];
                                $mismatches = [];
                                
                                // Check common fields
                                foreach ($columns as $column) {
                                    if (isset($temp_row[$column]) && isset($compare_row[$column])) {
                                        if ($temp_row[$column] == $compare_row[$column]) {
                                            $matches[] = $column;
                                        } else {
                                            $mismatches[] = $column;
                                        }
                                    }
                                }
                                
                                $comparison_results[] = [
                                    'serial_number' => $serial,
                                    'status' => 'exists',
                                    'temp_data' => $temp_row,
                                    'compare_data' => $compare_row,
                                    'matches' => $matches,
                                    'mismatches' => $mismatches
                                ];
                            } else {
                                // Serial does not exist in comparison table
                                $temp_query = "SELECT * FROM stock_erp_tem WHERE serial_number = '$serial'";
                                $temp_result = $conn->query($temp_query);
                                $temp_row = $temp_result->fetch_assoc();
                                
                                $comparison_results[] = [
                                    'serial_number' => $serial,
                                    'status' => 'new',
                                    'temp_data' => $temp_row,
                                    'compare_data' => null
                                ];
                            }
                        }
                    }
                } else {
                    echo '<div class="alert alert-danger">The selected table does not have a serial_number field for comparison.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Error accessing the comparison table.</div>';
            }
        }
        ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Excel Template & Upload</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <button type="submit" name="generate_template" class="btn btn-success mb-3">
                                <i class="bi bi-file-earmark-excel"></i> Generate Excel Template
                            </button>
                        </form>
                        
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Upload Excel File</label>
                                <input type="file" class="form-control" id="excel_file" name="excel_file" required>
                                <small class="text-muted">Accepted formats: XLSX, XLS, CSV</small>
                            </div>
                            <button type="submit" name="upload" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload & Process
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Data Transfer</h4>
                    </div>
                    <div class="card-body">
                        <p>Transfer data from temporary table to main table based on serial numbers.</p>
                        <form method="post">
                            <button type="submit" name="transfer" class="btn btn-success">
                                <i class="bi bi-arrow-right"></i> Transfer Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">Database Comparison</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="compare_table" class="form-label">Select table to compare with stock_erp_tem</label>
                                <select class="form-select" id="compare_table" name="compare_table" required>
                                    <option value="">Select a table</option>
                                    <?php foreach($available_tables as $table): ?>
                                    <option value="<?php echo htmlspecialchars($table); ?>" <?php echo ($compare_table == $table) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($table); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="compare" class="btn btn-info">
                                <i class="bi bi-search"></i> Compare Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Display Comparison Results -->
        <?php if($show_comparison && !empty($comparison_results)): ?>
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Comparison Results: stock_erp_tem vs <?php echo htmlspecialchars($compare_table); ?></h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($comparison_results as $result): ?>
                            <tr class="<?php 
                                if($result['status'] == 'new') {
                                    echo 'new-entry';
                                } elseif(empty($result['mismatches'])) {
                                    echo 'match';
                                } else {
                                    echo 'mismatch';
                                }
                            ?>">
                                <td><?php echo htmlspecialchars($result['serial_number']); ?></td>
                                <td>
                                    <?php if($result['status'] == 'new'): ?>
                                        <span class="badge bg-primary">New Entry</span>
                                    <?php elseif(empty($result['mismatches'])): ?>
                                        <span class="badge bg-success">Exact Match</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Mismatch</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($result['status'] == 'new'): ?>
                                        This serial number does not exist in the comparison table.
                                    <?php elseif(empty($result['mismatches'])): ?>
                                        All fields match between tables.
                                    <?php else: ?>
                                        <strong>Mismatched fields:</strong> 
                                        <?php echo implode(', ', $result['mismatches']); ?>
                                        <button class="btn btn-sm btn-outline-info mt-2" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#details-<?php echo $result['serial_number']; ?>">
                                            Show Details
                                        </button>
                                        <div class="collapse mt-2" id="details-<?php echo $result['serial_number']; ?>">
                                            <div class="card card-body">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Field</th>
                                                            <th>Temp Table Value</th>
                                                            <th>Comparison Table Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach($result['mismatches'] as $field): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($field); ?></td>
                                                            <td><?php echo htmlspecialchars($result['temp_data'][$field]); ?></td>
                                                            <td><?php echo htmlspecialchars($result['compare_data'][$field]); ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Display Temp Table Data -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Temporary Stock Data</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
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
                            <?php
                            $sql = "SELECT * FROM stock_erp_tem ORDER BY id DESC";
                            $result = $conn->query($sql);
                            
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
                                    echo "<td>" . $row['date'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tyre_code']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                    echo "<td>" . $row['qty'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Display Main Table Data -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">Main Stock Data</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Prev Serial</th>
                                <th>Serial Number</th>
                                <th>Date</th>
                                <th>Tyre Code</th>
                                <th>Description</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM stock_erp ORDER BY id DESC LIMIT 50";
                            $result = $conn->query($sql);
                            
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['prev_serial']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
                                    echo "<td>" . $row['date'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tyre_code']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                    echo "<td>" . $row['qty'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>No records found</td></tr>";
                            }
                            
                            // Close connection
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>