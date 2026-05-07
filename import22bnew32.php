
<?php

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to delete duplicate rows
$sql = "DELETE t1 FROM `worder` t1
        INNER JOIN `worder` t2 
        ON 
          t1.`date` = t2.`date` AND 
          t1.`Customer` = t2.`Customer` AND 
          t1.`wono` = t2.`wono` AND 
          t1.`ref` = t2.`ref` AND 
          t1.`erp` = t2.`erp` AND 
          t1.`icode` = t2.`icode` AND 
          t1.`t_size` = t2.`t_size` AND 
          t1.`brand` = t2.`brand` AND 
          t1.`col` = t2.`col` AND 
          t1.`fit` = t2.`fit` AND 
          t1.`rim` = t2.`rim` AND 
          t1.`cons` = t2.`cons` AND 
          t1.`fweight` = t2.`fweight` AND 
          t1.`ptv` = t2.`ptv` AND 
          t1.`new` = t2.`new` AND 
          t1.`cbm` = t2.`cbm` AND 
          t1.`kgs` = t2.`kgs` AND
          t1.`id` > t2.`id`";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Duplicate rows deleted successfully";
} else {
    echo "Error deleting duplicate rows: " . $conn->error;
}

// Close the database connection
$conn->close();

?>

<?php

// Database connection parameters
$servername = "localhost"; // Change this if your MySQL server is hosted elsewhere
$username = "planatir_task_managemen"; // Change this to your MySQL username
$password = "Bishan@1919"; // Change this to your MySQL password
$database = "planatir_task_managemen"; // Change this to the name of your MySQL database

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define SQL query to delete duplicate rows
$sql = "
DELETE p1 
FROM plannew p1
JOIN plannew p2 ON p1.erp = p2.erp AND p1.icode = p2.icode AND p1.mold_id = p2.mold_id AND p1.cavity_id = p2.cavity_id
WHERE p1.id > p2.id
";

// Execute deletion query
if ($conn->query($sql) === TRUE) {
    echo "Duplicate rows deleted successfully";
} else {
    echo "Error deleting duplicate rows: " . $conn->error;
}

// Close connection
$conn->close();

?>


























<?php

// Database connection parameters
$servername = "localhost"; // Change this if your MySQL server is hosted elsewhere
$username = "planatir_task_managemen"; // Change this to your MySQL username
$password = "Bishan@1919"; // Change this to your MySQL password
$database = "planatir_task_managemen"; // Change this to the name of your MySQL database

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define SQL query to delete duplicate rows
$sql = "
DELETE p1 
FROM plannew p1
JOIN plannew p2 ON p1.erp = p2.erp AND p1.icode = p2.icode AND p1.mold_id = p2.mold_id AND p1.cavity_id = p2.cavity_id AND p1.tires_per_mold = p2.tires_per_mold
WHERE p1.id > p2.id
";

// Execute deletion query
if ($conn->query($sql) === TRUE) {
    echo "Duplicate rows deleted successfully";
} else {
    echo "Error deleting duplicate rows: " . $conn->error;
}

// Close connection
$conn->close();

?>



<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "UPDATE worder
            JOIN work_order ON worder.erp = work_order.erp
            SET worder.date = work_order.datetime";
    
    $conn->exec($sql);
    
    echo "Dates updated successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
header("Location: deleteplan2b2.php");
exit();

?>


