


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
$templateTable = 'template';
$realstockTable = 'realstock';

// Update cstock in the realstock table based on icode from the template table
$query = "UPDATE $realstockTable r
          JOIN $templateTable t ON r.icode = t.icode
          SET r.cstock = r.cstock - t.cstock";

$result = mysqli_query($connection, $query);

if ($result) {
    echo "cstock updated successfully in the realstock table.";
} else {
    echo "Error updating cstock: " . mysqli_error($connection);
}

// SQL query to copy data from template table to target table
$sql11 = "INSERT INTO template2b (id, icode, cstock, date, shift, reason, reject)
        SELECT id, icode, cstock, date, shift, reason, reject
        FROM template";

if ($connection->query($sql11) === TRUE) {
    echo "Data copied successfully!";
} else {
    echo "Error copying data: " . $conn->error;
}


// SQL query to delete data from the template table
$sql22 = "DELETE FROM template";

if ($connection->query($sql22) === TRUE) {
    echo "Data deleted successfully!";
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close the database connection
mysqli_close($connection);
header("Location: get_email.php");
exit();
?>


