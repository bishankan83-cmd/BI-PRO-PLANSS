

<?php
// Database connection details
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

// SQL query to delete all data from tobeplan_tem
$sql = "DELETE FROM tobeplan_tem";

// Execute the query
if ($conn->query($sql) === TRUE) {
  //  echo "All data deleted successfully.";
} else {
  //  echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>



<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete all data from the tobeplan_plan table
$sqlDeleteAllData = "DELETE FROM `tobeplan_plan`";
if ($conn->query($sqlDeleteAllData) === TRUE) {
    echo "All data deleted from the tobeplan_plan table successfully.<br>";
} else {
    echo "Error deleting data from tobeplan_plan: " . $conn->error;
}

// Copy the data from tobeplan12345 into tobeplan_plan
$sqlCopyData = "INSERT INTO `tobeplan_plan` SELECT * FROM `tobeplan12345`";
if ($conn->query($sqlCopyData) === TRUE) {
    echo "Data copied from tobeplan12345 to tobeplan_plan successfully.<br>";

    // Delete data from tobeplan12345
    $sqlDeleteData = "DELETE FROM `tobeplan12345`";
    if ($conn->query($sqlDeleteData) === TRUE) {
        echo "Data deleted from tobeplan12345 successfully.<br>";
    } else {
        echo "Error deleting data from tobeplan12345: " . $conn->error;
    }


    // SQL to remove duplicate rows
$sql = "
DELETE t1
FROM tobeplan_plan t1
JOIN tobeplan_plan t2
ON t1.icode = t2.icode AND t1.erp = t2.erp AND t1.id > t2.id
";

// Execute query
if ($conn->query($sql) === TRUE) {
echo "Duplicate rows removed successfully";
} else {
echo "Error removing duplicate rows: " . $conn->error;
}
    // Close the MySQLi connection
    $conn->close();

    // Redirect to another page
    header("Location: delete_raw.php");
    exit();
} else {
    echo "Error copying data to tobeplan_plan: " . $conn->error;
}

// Close the MySQLi connection
$conn->close();
?>


