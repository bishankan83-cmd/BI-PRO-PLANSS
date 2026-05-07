

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

// Check if there is any data in the tables
$sql_check_tobeplan1 = "SELECT COUNT(*) AS count FROM tobeplan1";
$sql_check_plannew = "SELECT COUNT(*) AS count FROM plannew";
$sql_check_stock = "SELECT COUNT(*) AS count FROM stock";

$result_tobeplan1 = $conn->query($sql_check_tobeplan1);
$result_plannew = $conn->query($sql_check_plannew);
$result_stock = $conn->query($sql_check_stock);

// Check if any of the tables are empty
if ($result_tobeplan1 && $result_plannew && $result_stock) {
    $row_tobeplan1 = $result_tobeplan1->fetch_assoc();
    $row_plannew = $result_plannew->fetch_assoc();
    $row_stock = $result_stock->fetch_assoc();
    
    // If any of the tables are empty, delete all data from all tables
    if ($row_tobeplan1['count'] == 0 || $row_plannew['count'] == 0 || $row_stock['count'] == 0) {
        $sql_delete_tobeplan1 = "DELETE FROM tobeplan1";
        $sql_delete_plannew = "DELETE FROM plannew";
        $sql_delete_stock = "DELETE FROM stock";
        
        $conn->query($sql_delete_tobeplan1);
        $conn->query($sql_delete_plannew);
        $conn->query($sql_delete_stock);
        
        echo "All data deleted successfully.";
    } else {
        echo "Data exists in all tables. No action taken.";
    }
} else {
    echo "Error checking data in tables: " . $conn->error;
}

// Close connection
$conn->close();
?>





<?php


include 'includes/checkauthenticator.php';

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

// Check if plannew table has data
$sql = "SELECT COUNT(*) AS count FROM plannew";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$count_plannew = $row['count'];

// Check if stock table has data
$sql = "SELECT COUNT(*) AS count FROM stock";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$count_stock = $row['count'];

// Check if tobeplan1 table has data
$sql = "SELECT COUNT(*) AS count FROM tobeplan1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$count_tobeplan1 = $row['count'];

// If plannew, stock, and tobeplan1 have data, delete data from new_plan_data, new_stock_data, new_tobeplan_data
if ($count_plannew > 0 && $count_stock > 0 && $count_tobeplan1 > 0) {
    // Delete data from new_plan_data
    $sql = "DELETE FROM new_plan_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data deleted from new_plan_data successfully<br>";
    } else {
        //echo "Error deleting data from new_plan_data: " . $conn->error;
    }
    
    // Delete data from new_stock_data
    $sql = "DELETE FROM new_stock_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data deleted from new_stock_data successfully<br>";
    } else {
        //echo "Error deleting data from new_stock_data: " . $conn->error;
    }
    
    // Delete data from new_tobeplan_data
    $sql = "DELETE FROM new_tobeplan_data";
    if ($conn->query($sql) === TRUE) {
      //  echo "Data deleted from new_tobeplan_data successfully<br>";
    } else {
       // echo "Error deleting data from new_tobeplan_data: " . $conn->error;
    }
} else {
    // Transfer data from new_plan_data to plannew
    $sql = "INSERT INTO plannew SELECT * FROM new_plan_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data transferred from new_plan_data to plannew successfully<br>";
        // Truncate new_plan_data
        $sql = "TRUNCATE TABLE new_plan_data";
        $conn->query($sql);
    } else {
       // echo "Error transferring data from new_plan_data to plannew: " . $conn->error;
    }
    
    // Transfer data from new_stock_data to stock
    $sql = "INSERT INTO stock SELECT * FROM new_stock_data";
    if ($conn->query($sql) === TRUE) {
        //echo "Data transferred from new_stock_data to stock successfully<br>";
        // Truncate new_stock_data
        $sql = "TRUNCATE TABLE new_stock_data";
        $conn->query($sql);
    } else {
       // echo "Error transferring data from new_stock_data to stock: " . $conn->error;
    }
    
    // Transfer data from new_tobeplan_data to tobeplan1
    $sql = "INSERT INTO tobeplan1 SELECT * FROM new_tobeplan_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data transferred from new_tobeplan_data to tobeplan1 successfully<br>";
        // Truncate new_tobeplan_data
        $sql = "TRUNCATE TABLE new_tobeplan_data";
        $conn->query($sql);
    } else {
      //  echo "Error transferring data from new_tobeplan_data to tobeplan1: " . $conn->error;
    }
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

// Transfer data from plannew to new_plan_data
$sql = "INSERT INTO new_plan_data SELECT * FROM plannew";
if ($conn->query($sql) === TRUE) {
    //echo "Data transferred from plannew to new_plan_data successfully<br>";
} else {
    echo "Error transferring data: " . $conn->error;
}

// Transfer data from stock to new_stock_data
$sql = "INSERT INTO new_stock_data SELECT * FROM stock";
if ($conn->query($sql) === TRUE) {
    //echo "Data transferred from stock to new_stock_data successfully<br>";
} else {
   // echo "Error transferring data: " . $conn->error;
}

// Transfer data from tobeplan1 to new_tobeplan_data
$sql = "INSERT INTO new_tobeplan_data SELECT * FROM tobeplan1";
if ($conn->query($sql) === TRUE) {
   // echo "Data transferred from tobeplan1 to new_tobeplan_data successfully<br>";
} else {
   // echo "Error transferring data: " . $conn->error;
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
//header("Location: deleteplan2b.php");
//exit();

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

//header("Location: wcopyb.php");
//exit();
?>
</div>
</body>
</html>


<?php
// Source database connection
$sourceHost = 'localhost';
$sourceUsername = 'planatir_task_managemen';
$sourcePassword = 'Bishan@1919';
$sourceDatabase = 'planatir_task_managemen';

$sourceConnection = new mysqli($sourceHost, $sourceUsername, $sourcePassword, $sourceDatabase);
if ($sourceConnection->connect_error) {
    die("Source database connection failed: " . $sourceConnection->connect_error);
}

// Destination database connection
$destHost = 'localhost';
$destUsername = 'planatir_task_managemen';
$destPassword = 'Bishan@1919';
$destDatabase = 'planatir_task_managemen';

$destConnection = new mysqli($destHost, $destUsername, $destPassword, $destDatabase);
if ($destConnection->connect_error) {
    die("Destination database connection failed: " . $destConnection->connect_error);
}

// Copy data from source to destination
$copyQuery = "INSERT INTO wcopy SELECT * FROM worder";
if ($sourceConnection->query($copyQuery) === TRUE) {
   //echo "Data copied successfully.";
} else {
    echo "Error copying data: " . $sourceConnection->error;
}

// Close connections
$sourceConnection->close();
$destConnection->close();

               //header("Location: convertstockb.php");
              //exit();
?>



<?php
// Establish a connection to the MySQL database
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

    // Copy data from "realstock" table to "stock" table
    $copyQuery = "INSERT INTO stock SELECT * FROM realstock";
    
    if ($conn->query($copyQuery) === TRUE) {
       header("Location:testingbisb.php");
exit();
    } else {
        echo "Error copying data: " . $conn->error;
        header("Location: testingbisb.php");
        exit();

    }

// Close the database connection

?>


