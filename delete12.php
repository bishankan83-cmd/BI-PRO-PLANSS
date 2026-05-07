<?php

// Database connection parameters
$servername = "localhost"; // Change this if your MySQL server is hosted elsewhere
$username = "planatir_task_managemen"; // Change this to your MySQL username
$password = "Bishan@1919"; // Change this to your MySQL password
$database = "planatir_task_managemen"; // Change this to the name of your MySQL database

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define SQL query to delete duplicate rows
$sql = "
DELETE p1 
FROM plannew p1
JOIN plannew p2 ON p1.erp = p2.erp AND p1.icode = p2.icode AND p1.mold_id = p2.mold_id AND p1.cavity_id = p2.cavity_id AND p1.tires_per_mold = p2.tires_per_mold
WHERE p1.id > p2.id
";

// Execute deletion query
if ($conn->query($sql) === TRUE) {
    echo "Duplicate rows deleted successfully";
} else {
    echo "Error deleting duplicate rows: " . $conn->error;
}

// Close connection
$conn->close();

?>
