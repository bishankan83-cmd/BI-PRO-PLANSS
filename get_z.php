<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

// Connect to the database
$conn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to check if there is data in the process table
$sql = "SELECT * FROM process";
$result = $conn->query($sql);

// Check if there is data in the process table
if ($result->num_rows > 0) {
    // Display a message for 5 minutes before refreshing the page
    echo "<script>";
    echo "alert('Please update the plan before refreshing.');";
    echo "setTimeout(function(){ window.location.href = 'plannew45new2.php'; }, 3);"; // 300000 milliseconds = 5 minutes
    echo "</script>";
} else {
    // Redirect to another page if there is no data in the process table
    header("Location: get.php");
    exit;
}

// Close the connection
$conn->close();
?>
