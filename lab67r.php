<?php

// Establish a connection to your database
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

// SQL query to insert data into another_table_name3
$sql = "
INSERT INTO another_table_name11 (serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, rebound)
SELECT serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, rebound
FROM another_table_name33
ORDER BY CAST(batch AS UNSIGNED) ASC;
";

if ($conn->query($sql) === TRUE) {
    // Redirect to another page
    header("Location: lab2r.php");
    exit; // Ensure script stops here to prevent further execution
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();

?>
