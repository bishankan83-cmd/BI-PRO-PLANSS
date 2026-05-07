


<?php
// Database connection parameters
$servername = "localhost"; // Change to your MySQL server hostname
$username = "planatir_task_managemen"; // Change to your MySQL username
$password = "Bishan@1919"; // Change to your MySQL password
$database = "planatir_task_managemen"; // Change to your MySQL database name

// Create a database connection
$connection = mysqli_connect($servername, $username, $password, $database);

// Check the connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define the template table and realstock table names
$templateTable = 'daily_plan_data1';
$realstockTable = 'realstock';

// Update cstock in the realstock table based on icode from the template table
$query = "UPDATE $realstockTable r
          JOIN $templateTable t ON r.icode = t.icode
          SET r.cstock = r.cstock + t.cstock";

$result = mysqli_query($connection, $query);

if ($result) {
    echo "cstock updated successfully in the realstock table.";
} else {
    echo "Error updating cstock: " . mysqli_error($connection);
}

// Close the database connection
mysqli_close($connection);
//header("Location: deletepro.php");
//exit();
?>
