




<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to copy data from 'process_plan' table to 'old_process' table
$sql = "INSERT INTO old_process (
    
    icode,
    mold_id,
    tires_per_mold,
    cavity_id,
    mold_name,
    cavity_name,
    press_name,
    press_id,
    erp,
    serial,
    start_date
)
SELECT
    
    icode,
    mold_id,
    tires_per_mold,
    cavity_id,
    mold_name,
    cavity_name,
    press_name,
    press_id,
    erp,
    serial,
    start_date
FROM
    process_plan";

if ($conn->query($sql) === TRUE) {
    echo "Data copied successfully.";
} else {
    echo "Error copying data: " . $conn->error;
}

// Close the connection
$conn->close();
header("Location: sleep.php");
exit();
?>
