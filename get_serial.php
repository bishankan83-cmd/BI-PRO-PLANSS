<?php
require 'vendor/autoload.php'; // Include Composer autoloader for PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Verify table existence
    $check_tables = $conn->query("SHOW TABLES LIKE 'get_serial'");
    if ($check_tables->num_rows == 0) {
        throw new Exception("Table 'get_serial' does not exist in database '$dbname'.");
    }
    $check_tables = $conn->query("SHOW TABLES LIKE 'stock_erp'");
    if ($check_tables->num_rows == 0) {
        throw new Exception("Table 'stock_erp' does not exist in database '$dbname'.");
    }

    // SQL query using LEFT JOIN to avoid NOT IN issues
    $query = "
        SELECT g.serial_number
        FROM get_serial g
        LEFT JOIN stock_erp s
            ON g.serial_number = s.serial_number
        WHERE s.serial_number IS NULL
    ";

    // Execute query
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    // Check if result is empty
    if ($result->num_rows == 0) {
        echo "No serial numbers found in 'get_serial' that are not in 'stock_erp'.";
    }

    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header
    $sheet->setCellValue('A1', 'Serial Number');

    // Populate data
    $row = 2; // Start from row 2 to account for header
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['serial_number']);
        $row++;
    }

    // Create Excel file
    $filename = 'serial_numbers_not_in_stock.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);

    echo "Excel file '$filename' has been created successfully.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Close database connection
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
?>