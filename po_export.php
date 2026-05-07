<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $file = $_FILES['file']['tmp_name'];

    // Load the Excel file
    try {
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip the header row and insert data
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header row
            $po = $conn->real_escape_string($row[0]);
            $name = $conn->real_escape_string($row[1]);

            // Insert data into the table
            $sql = "INSERT INTO purchase_orderss (PO, Name) VALUES ('$po', '$name')";
            if (!$conn->query($sql)) {
                echo "Error: " . $conn->error . "<br>";
            }
        }
        echo "Data imported successfully!";
    } catch (Exception $e) {
        echo "Error loading file: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Upload Excel File</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".xls, .xlsx" required>
        <button type="submit" name="submit">Upload</button>
    </form>
</body>
</html>

