<?php
// Database connection parameters
$host = 'localhost'; // Your database host
$username = 'planatir_task_managemen'; // Your database username
$password = 'Bishan@1919'; // Your database password
$database = 'planatir_task_managemen'; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$icode = $_POST['icode'];
$description = $_POST['description'];
$time_taken = $_POST['time_taken'];
$is_available = isset($_POST['is_available']) ? 1 : 0;
$availability_date = $_POST['availability_date'];
// $cuing_group_id is omitted because it's auto-incremented
$cuing_group_name = $_POST['cuing_group_name'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO tire (icode, description, time_taken, is_available, availability_date, cuing_group_name) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiiis", $icode, $description, $time_taken, $is_available, $availability_date, $cuing_group_name);

// Execute the statement
if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
