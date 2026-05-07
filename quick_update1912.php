<?php
// Database connection information
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Insert missing icodes with positive tobe numbers into quick_new19
$insertQuery = "
    INSERT INTO quick_new19 (icode, tobe)
    SELECT tb.icode, tb.tobe
    FROM tobeplan tb
    LEFT JOIN quick_plan qp ON tb.icode = qp.icode
    WHERE tb.tobe > 0
    AND qp.icode IS NULL;
";

if ($conn->query($insertQuery) === TRUE) {
    echo "Records inserted successfully into quick_new19 table.\n";
} else {
    echo "Error inserting records: " . $conn->error . "\n";
}


// Close the database connection
$conn->close();
header("Location: quick_update192.php");
exit();
?>
