






<?php
// MySQL database credentials
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Delete all data from the stock table (you had used the same variable name multiple times)
$deleteStockSql = "DELETE FROM stock";
$conn->query($deleteStockSql);

// Delete all data from the tobeplan1 table
$deleteTobeplan1Sql = "DELETE FROM tobeplan1";
$conn->query($deleteTobeplan1Sql);

// Delete all data from the wcopy table
$deleteWcopySql = "DELETE FROM wcopy";
$conn->query($deleteWcopySql);

// Commit the transaction if all queries are successful
$conn->commit();

$conn->close();

header("Location: wcopyb.php");
exit();
?>
</div>
</body>
</html>
