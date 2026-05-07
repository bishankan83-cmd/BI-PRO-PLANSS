<?php

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

// SQL to remove duplicate rows
$sql = "
    DELETE t1
    FROM tobeplan t1
    JOIN tobeplan t2
    ON t1.icode = t2.icode AND t1.erp = t2.erp AND t1.id > t2.id
";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "Duplicate rows removed successfully";
} else {
    echo "Error removing duplicate rows: " . $conn->error;
}

// Close connection
$conn->close();
header("Location: sleep3.php");
exit();

?>
