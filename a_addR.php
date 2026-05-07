<?php
// Database connection parameters
$servername = "localhost:3306"; // Change to your MySQL server hostname
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
$templateTable = 'dworkr';
$realstockTable = 'realstock';

// Update cstock in the realstock table based on icode from the template table
$query = "UPDATE $realstockTable r
          JOIN $templateTable t ON r.icode = t.icode
          SET r.cstock = r.cstock + t.quantity";

$result = mysqli_query($connection, $query);

if ($result) {
    echo "cstock updated successfully in the realstock table.";
} else {
    echo "Error updating cstock: " . mysqli_error($connection);
}

// Close the database connection
mysqli_close($connection);

?>


<?php
$servername = "localhost:3306";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to copy data from dwork to new_table
$sql_copy = "INSERT INTO worder (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs)
        SELECT date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs
        FROM dworkr";

// Execute the copy query
if ($conn->query($sql_copy) === TRUE) {
    echo "Data copied successfully!";
    
    // SQL query to delete all data from dwork
    $sql_delete = "DELETE FROM dworkr";
    
    // Execute the delete query
    if ($conn->query($sql_delete) === TRUE) {
        echo "Data in dwork table deleted successfully!";
    } else {
        echo "Error deleting data from dwork table: " . $conn->error;
    }
} else {
    echo "Error copying data: " . $conn->error;
}

// Close the database connection
$conn->close();

header("Location: dashboard.php");
exit();
?>
