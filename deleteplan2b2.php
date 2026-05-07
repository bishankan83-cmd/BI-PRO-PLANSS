
<?php
// Database connection details
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

// SQL query to delete all data from plan_stock table
$sql = "DELETE FROM `plannew_stock`";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data deleted successfully from plan_stock";
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close the connection
$conn->close();
?>



<?php
// Database connection details
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

// SQL query to insert data into plannew_backup from plannew where erp = '967'
$sql = "
    INSERT INTO plannew_stock (
        id, 
        plan_id, 
        erp, 
        Customer, 
        icode, 
        description, 
        tobe, 
        press, 
        press_name, 
        mold_id, 
        mold_name, 
        cavity_id, 
        cavity_name, 
        cuing_group_id, 
        cuing_group_name, 
        start_date, 
        end_date, 
        tires_per_mold
    )
    SELECT 
        id, 
        plan_id, 
        erp, 
        Customer, 
        icode, 
        description, 
        tobe, 
        press, 
        press_name, 
        mold_id, 
        mold_name, 
        cavity_id, 
        cavity_name, 
        cuing_group_id, 
        cuing_group_name, 
        start_date, 
        end_date, 
        tires_per_mold
    FROM plannew
    WHERE erp = '1'
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully";
} else {
    echo "Error inserting data: " . $conn->error;
}

// Close the connection
$conn->close();
?>





<?php
// Database connection details
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

// SQL query to delete data where erp is '967' in plannew table
$sql = "DELETE FROM `plannew` WHERE `erp` = '1'";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data deleted successfully";
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close the connection
$conn->close();
?>







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


