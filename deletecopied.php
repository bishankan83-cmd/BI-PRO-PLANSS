<?php
// MySQL database credentials
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete all data from the copied_work table
$deleteCopiedWorkSql = "DELETE FROM copied_work";
$conn->query($deleteCopiedWorkSql);

// Close the database connection
$conn->close();

// Redirect to plannew34R.php
header("Location: plannew34R.php");
exit();
?>
