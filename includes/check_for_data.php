<?php
// Database connection details (update these with your database information)
$hostname = "localhost:3306";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection to the database
$conn = new mysqli($hostname, $username, $password, $database);

// Check for a successful connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your SQL query to check for new data in a specific table (modify as needed)
$sql = "SELECT COUNT(*) as count FROM template";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];

    if ($count > 0) {
        // New data is available
        echo 'data_available';
    } else {
        // No new data
        echo 'no_data_available';
    }

    $result->close();
} else {
    echo 'error'; // Handle any errors in the query execution
}

// Close the database connection
$conn->close();
?>
