<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
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




// Delete existing duplicates
$deleteQuery = "
    DELETE b1
    FROM bcompound b1
    JOIN bcompound b2 ON 
        b1.inputDate = b2.inputDate AND 
        b1.shift = b2.shift AND 
        b1.compound_name = b2.compound_name AND 
        b1.description = b2.description AND 
        b1.cstock = b2.cstock AND 
        b1.batch = b2.batch AND 
        b1.pallet = b2.pallet AND 
        b1.weight = b2.weight AND 
        b1.serial_number = b2.serial_number
";

// Execute delete query
$conn->query($deleteQuery);

// Close connection
$conn->close();
?> 
