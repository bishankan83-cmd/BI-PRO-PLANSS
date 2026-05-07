<?php

$host = 'localhost'; // The hostname of the database server (e.g., 'localhost')
$username = 'planatir_task_managemen'; // The username to access the database
$password = 'Bishan@1919'; // The password to access the database
$database = 'planatir_task_managemen'; // The name of the database

// Create a connection
$connection = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    die('Failed to connect to the database: ' . mysqli_connect_error());
}



// Delete records from the tire_cavity table
$deleteTireCavity = "DELETE FROM dates";
mysqli_query($connection, $deleteTireCavity);

// Delete records from the tire_cavity table
$deleteTireCavity = "DELETE FROM template";
mysqli_query($connection, $deleteTireCavity);

// Close the database connection
mysqli_close($connection);

header("Location: import22.php");
exit();
?>