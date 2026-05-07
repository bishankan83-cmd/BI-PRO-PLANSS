<?php
// Step 1: Connect to MySQL database
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Perform the JOIN operation and update cavity_id in quick_plan
$sql = "UPDATE quick_plan
        JOIN plannew ON quick_plan.icode = plannew.icode AND quick_plan.mold_id = plannew.mold_id
        SET quick_plan.cavity_id = plannew.cavity_id";

if ($conn->query($sql) === TRUE) {
    echo "Cavity_id updated successfully in quick_plan based on matching icode and mold_id.<br>";
} else {
    echo "Error updating records: " . $conn->error;
}

$conn->close();
header("Location: sleep4.php");
exit();
?>
