<?php
// Database connection parameters
$servername = "localhost"; // Change to your MySQL server hostname
$username = "planatir_task_managemen"; // Change to your MySQL username
$password = "Bishan@1919"; // Change to your MySQL password
$database = "planatir_task_managemen"; // Change to your MySQL database name

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to copy data from template table to target table
$sql11 = "INSERT INTO template2b (id, icode, cstock, date, shift, reason)
        SELECT id, icode, cstock, date, shift, reason
        FROM template";

if ($conn->query($sql11) === TRUE) {
    echo "Data copied successfully!";
} else {
    echo "Error copying data: " . $conn->error;
}


// SQL query to delete data from the template table
$sql22 = "DELETE FROM template";

if ($conn->query($sql22) === TRUE) {
    echo "Data deleted successfully!";
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
