<?php
$erp_number = $_POST['erp_number'];
$select_option = $_POST['select_option'];
$dispatch_date = $_POST['dispatch_date'];
$dispatch_month = $_POST['dispatch_month'];

// Replace with your database connection details
$servername = "localhost";
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Sanitize inputs
$erp_number = $conn->real_escape_string($erp_number);
$select_option = $conn->real_escape_string($select_option);
$dispatch_date = $conn->real_escape_string($dispatch_date);
$dispatch_month = $conn->real_escape_string($dispatch_month);

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO pros (erp_number, select_option, dispatch_date, dispatch_month) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $erp_number, $select_option, $dispatch_date, $dispatch_month);
$stmt->execute();
$stmt->close();

// Close the database connection
$conn->close();

// Redirect to another page
header('Location: dispatch_order_serial.php');
exit;
?>
