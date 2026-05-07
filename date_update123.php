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

// Check if there are any records in the process table
$checkProcessSql = "SELECT COUNT(*) as count FROM process";
$result = $conn->query($checkProcessSql);
$row = $result->fetch_assoc();
$count = $row['count'];

// Set the redirect location based on whether there are records in the process table
$redirectLocation = $count > 0 ? "plan_before.php" : "plannew34new2.php";

// Set the date and time to today's 07:00
$availability_date = date("Y-m-d 07:00:00");

// Update the availability_date of all presses in the 'press' table
$updatePressSql = "UPDATE press SET availability_date = '$availability_date'";
$conn->query($updatePressSql);

// Update the availability_date of all molds in the 'mold' table
$updateMoldSql = "UPDATE mold SET availability_date = '$availability_date'";
$conn->query($updateMoldSql);

// Update the availability_date of all cavities in the 'cavity' table
$updateCavitySql = "UPDATE cavity SET availability_date = '$availability_date'";
$conn->query($updateCavitySql);

// Commit the transaction if all queries are successful
$conn->commit();

// Redirect to the specified page after the updates
header("Location: $redirectLocation");
exit();
?>
