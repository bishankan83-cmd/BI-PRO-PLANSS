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

// Delete all data from the tobeplan table
$sqlDeleteAllData = "DELETE FROM `tobeplan`";
if ($conn->query($sqlDeleteAllData) === TRUE) {
    echo "All data deleted from the tobeplan table successfully.<br>";
} else {
    echo "Error deleting data from tobeplan: " . $conn->error;
}

// Copy the data from tobeplan12345 into tobeplan
$sqlCopyData = "INSERT INTO `tobeplan` SELECT * FROM `tobeplan12345`";
if ($conn->query($sqlCopyData) === TRUE) {
    echo "Data copied from tobeplan12345 to tobeplan successfully.<br>";

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
FROM tobeplan t1
JOIN tobeplan t2
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
    header("Location: updatepro.php");
    exit();
} else {
    echo "Error copying data to tobeplan: " . $conn->error;
}

// Close the MySQLi connection
$conn->close();
?>
