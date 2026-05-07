
<?php
// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to delete all data from the table
$sql = "DELETE FROM `importmix`";

if ($conn->query($sql) === TRUE) {
    echo "All data deleted successfully";
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close connection
$conn->close();
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel Data</title>
</head>
<body>
    <h2>Import Excel Data into Database</h2>
    <form action="import_process.php" method="post" enctype="multipart/form-data">
        <label for="excel_file">Select Excel File:</label>
        <input type="file" name="excel_file" id="excel_file"><br><br>
        <input type="submit" value="Import Data" name="submit">
    </form>
</body>
</html>
