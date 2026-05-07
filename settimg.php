<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query
$sql = "
INSERT INTO tire_details (icode, Brand, Colour, greenweight, stgreenweight, Description)
SELECT 
    b.icode,
    b.Brand,
    b.Color,
    b.`Grand Totalcompound weight` AS greenweight,
    b.`Green Tire weight` AS stgreenweight,
    b.`t_size` AS Description
FROM bom_new b
LEFT JOIN tire_details t ON b.icode = t.icode
WHERE t.icode IS NULL;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records inserted successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>
