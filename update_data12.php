<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve data sent via POST
$id = $_POST['id']; // ID of the row to update
$column = $_POST['column']; // Name of the column to update
$newValue = $_POST['newValue']; // New value for the column

// Prepare SQL statement to update data
$sql = "UPDATE bom_new45 SET `$column` = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Bind parameters and execute the statement
$stmt->bind_param("si", $newValue, $id);
$stmt->execute();

// Check if update was successful
if ($stmt->affected_rows > 0) {
    echo "Data updated successfully";
} else {
    echo "No rows updated";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
