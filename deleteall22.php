<?php

$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$connection = mysqli_connect($host, $username, $password, $database);

if (mysqli_connect_errno()) {
    die('Failed to connect to the database: ' . mysqli_connect_error());
}


$deleteProductionPlan = "DELETE FROM production_plan";
mysqli_query($connection, $deleteProductionPlan);

$deleteTireCavity = "DELETE FROM tire_cavity";
mysqli_query($connection, $deleteTireCavity);

$deleteTireMolddd = "DELETE FROM tire_molddd";
mysqli_query($connection, $deleteTireMolddd);

$deleteQuickPlan = "DELETE FROM quick_plan";
mysqli_query($connection, $deleteQuickPlan);

$deleteprocess = "DELETE FROM process";
mysqli_query($connection, $deleteprocess);

$deletetobe = "DELETE FROM tobeplan";
mysqli_query($connection, $deletetobe);




mysqli_close($connection);

header("Location: check_date32.php");
exit;
?>