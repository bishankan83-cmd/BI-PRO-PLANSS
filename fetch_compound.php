<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a connection to the database
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['compound_name'])) {
    $icode = $_GET['compound_name'];

    // Query the database to fetch the description based on the item code
    $sql = "SELECT erp_code FROM compounds WHERE compound_name = '$icode'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $description = $row['erp_code'];
        echo $description;
    }
}

// Close the database connection
$conn->close();
?>
