<?php
$servername = "localhost";
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
$sql_copy = "INSERT INTO dwork2 (id, date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs, quantity)
        SELECT id, date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs, quantity
        FROM dwork";

// Execute the copy query
if ($conn->query($sql_copy) === TRUE) {
    echo "Data copied successfully!";
    
    // SQL query to delete all data from dwork
    $sql_delete = "DELETE FROM dwork";
    
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

header("Location: dispatch1.php");
exit();
?>
