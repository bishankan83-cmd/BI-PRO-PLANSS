<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Get data from POST request
$id = $_POST['id'];
$field = $_POST['field'];
$value = $_POST['value'];

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize the value to prevent SQL injection (you can improve this)
$value = $conn->real_escape_string($value);

// Prepare the update SQL statement
$sql = "UPDATE dwork SET $field = '$value' WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully.";
} else {
    echo "Error updating record: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
