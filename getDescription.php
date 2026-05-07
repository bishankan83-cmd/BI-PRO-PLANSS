<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a connection to the tire database
$tireConn = new mysqli($hostname, $username, $password,$database);

if ($tireConn->connect_error) {
    die('Connection to the tire database failed: ' . $tireConn->connect_error);
}

// Get the item code from the AJAX request
$itemCode = $_GET['icode'];

// Fetch the description from the tire database
$description = '';
$tireSql = "SELECT description FROM tire WHERE icode = '$itemCode'";
$result = $tireConn->query($tireSql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $description = $row['description'];
}

// Close the database connection
$tireConn->close();

// Return the description as the response
echo $description;
?>
