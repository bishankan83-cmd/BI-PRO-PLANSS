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

// SQL query to update erp in production_plan table with data from process table
$sql = "UPDATE process  pp
        JOIN  production_plan p ON pp.mold_id = p.mold_id
        SET pp.erp = p.erp";

if ($conn->query($sql) === TRUE) {
    echo "ERP updated successfully";
} else {
    echo "Error updating ERP: " . $conn->error;
}

// Close connection
$conn->close();
header("Location: plannew562.php");
exit();
?>
