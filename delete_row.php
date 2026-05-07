<?php
// Include your database connection details here
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

// Get the process ID from the request
$processId = $_POST['id'];

// Perform the deletion query
$sql = "DELETE FROM `process_plan` WHERE `id` = '$processId'";
$result = $conn->query($sql);

// Close the database connection
$conn->close();

// You can send a response back to the client if needed
?>
