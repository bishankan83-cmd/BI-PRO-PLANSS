<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Set a longer timeout for the MySQL connection (e.g., 5 minutes)
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 600);

    // Delete all data from the tobeplannew1 table
    $delete_sql = "DELETE FROM tobeplannew1";

    if ($conn->query($delete_sql) === TRUE) {
        echo "Data deleted from the tobeplannew1 table successfully.";
    } else {
        echo "Error deleting data from the tobeplannew1 table: " . $conn->error;
    }
} catch (mysqli_sql_exception $ex) {
    // Handle the database error gracefully
    echo "An error occurred while processing your request. Please try again later.";
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Data Page</title>
</head>
<body>
    <p><a href="quickplan2.php">NEXT</a></p>
</body>
</html>
