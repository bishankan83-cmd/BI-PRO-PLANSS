<?php
$servername = "localhost";
$username = "planatir_task_managemen"; 
$password = "Bishan@1919"; 
$dbname = "planatir_task_managemen";



$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
