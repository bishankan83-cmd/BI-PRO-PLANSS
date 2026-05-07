<?php
// Enable error reporting and display errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';


$connection = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    die('Failed to connect to the database: ' . mysqli_connect_error());
}





// Delete records from the tire_cavity table
$deletenew_table2 = "DELETE FROM new_table2";
mysqli_query($connection, $deletenew_table2);

// Delete records from the tire_molddd table
$deleteresult = "DELETE FROM result_table";
mysqli_query($connection, $deleteresult);

// Delete records from the quick_plan table
$deletenew_table = "DELETE FROM new_table";
mysqli_query($connection, $deletenew_table);

// Delete records from the quick_plan table
$deletenew_table3 = "DELETE FROM new_table3";
mysqli_query($connection, $deletenew_table3);



// Close the database connection
mysqli_close($connection);


header("Location: indidateR.php")

?>


