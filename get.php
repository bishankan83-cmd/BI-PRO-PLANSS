





<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_plann';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919'; // Update this with the actual password
$targetDb = 'planatir_task_managemen';

// Connect to source database
$sourceConn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);
if ($sourceConn->connect_error) {
    die("Source connection failed: " . $sourceConn->connect_error);
}

// Connect to target database
$targetConn = new mysqli($targetHost, $targetUser, $targetPass, $targetDb);
if ($targetConn->connect_error) {
    die("Target connection failed: " . $targetConn->connect_error);
}

// Check if the corresponding table in the other database contains data
$checkQuery = "SELECT * FROM plannew";
$checkResult = $sourceConn->query($checkQuery);

if ($checkResult && $checkResult->num_rows > 0) {
    // Step 1: Delete existing data in the target table
    $deleteQuery = "DELETE FROM plannew";
    if ($targetConn->query($deleteQuery) === TRUE) {
        echo "Existing data in target table deleted successfully.<br>";
    } else {
        die("Error deleting data in target table: " . $targetConn->error);
    }

    // Step 2: Fetch data from source table
    $selectQuery = "SELECT * FROM plannew";
    $result = $sourceConn->query($selectQuery);

    if ($result && $result->num_rows > 0) {
        // Prepare insert query for the target table
        while ($row = $result->fetch_assoc()) {
            $insertQuery = "INSERT INTO plannew (id, plan_id, erp, Customer, icode, description, tobe, press, press_name, mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name, start_date, end_date, tires_per_mold) 
                            VALUES (
                                '{$row['id']}', '{$row['plan_id']}', '{$row['erp']}', '{$row['Customer']}', '{$row['icode']}', '{$row['description']}', '{$row['tobe']}', '{$row['press']}', 
                                '{$row['press_name']}', '{$row['mold_id']}', '{$row['mold_name']}', '{$row['cavity_id']}', '{$row['cavity_name']}', '{$row['cuing_group_id']}', 
                                '{$row['cuing_group_name']}', '{$row['start_date']}', '{$row['end_date']}', '{$row['tires_per_mold']}'
                            )";

            if (!$targetConn->query($insertQuery)) {
                echo "Error inserting data: " . $targetConn->error . "<br>";
            }
        }
        echo "Data copied successfully.<br>";
    } else {
        echo "No data found in source table.<br>";
    }
} else {
    echo "No data found in source table, no deletion or insertion performed.<br>";
}

// Close connections
$sourceConn->close();
$targetConn->close();
?>









<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_plann';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919'; // Update this with the actual password
$targetDb = 'planatir_task_managemen';

// Connect to source database
$sourceConn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);
if ($sourceConn->connect_error) {
    die("Source connection failed: " . $sourceConn->connect_error);
}

// Connect to target database
$targetConn = new mysqli($targetHost, $targetUser, $targetPass, $targetDb);
if ($targetConn->connect_error) {
    die("Target connection failed: " . $targetConn->connect_error);
}

// Step 1: Check if the corresponding table in the other database contains data
$checkQuery = "SELECT * FROM tobeplan1";
$checkResult = $sourceConn->query($checkQuery);

if ($checkResult && $checkResult->num_rows > 0) {
    // If data exists, delete existing data in the target table
    $deleteQuery = "DELETE FROM tobeplan1";
    if ($targetConn->query($deleteQuery) === TRUE) {
        echo "Existing data in target table deleted successfully.<br>";
    } else {
        die("Error deleting data in target table: " . $targetConn->error);
    }
} else {
    echo "No data found in source table, skipping deletion.<br>";
}

// Step 2: Fetch data from source table
$selectQuery = "SELECT * FROM tobeplan1";
$result = $sourceConn->query($selectQuery);

if ($result && $result->num_rows > 0) {
    // Prepare insert query for the target table
    $insertQuery = "INSERT INTO tobeplan1 (id, icode, tobe, erp, stockonhand) VALUES ";

    $insertValues = [];
    while ($row = $result->fetch_assoc()) {
        // Collect data for each row
        $insertValues[] = "('". $row['id'] ."', '". $row['icode'] ."', '". $row['tobe'] ."', '". $row['erp'] ."', '". $row['stockonhand'] ."')";
    }

    // If we have values to insert, construct and execute the insert query
    if (count($insertValues) > 0) {
        $insertQuery .= implode(", ", $insertValues);

        if ($targetConn->query($insertQuery) === TRUE) {
            echo "Data copied successfully.<br>";
        } else {
            echo "Error inserting data: " . $targetConn->error . "<br>";
        }
    } else {
        echo "No data to insert.<br>";
    }
} else {
    echo "No data found in source table.<br>";
}

// Close connections
$sourceConn->close();
$targetConn->close();
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
    
    //echo "Dates updated successfully!";
} catch(PDOException $e) {
  //  echo "Error: " . $e->getMessage();
}


?>




<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Establish database connection
$connection = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    die('Failed to connect to the database: ' . mysqli_connect_error());
}

// Delete all records from the "tobeplan_plan2" table
$deleteTobeQuery = "DELETE FROM tobeplan_plan2";
if (!mysqli_query($connection, $deleteTobeQuery)) {
    die('Error deleting records from tobeplan_plan2: ' . mysqli_error($connection));
}

// Delete all records from the "process_plan2" table
$deleteProcessQuery = "DELETE FROM process_plan2";
if (!mysqli_query($connection, $deleteProcessQuery)) {
    die('Error deleting records from process_plan2: ' . mysqli_error($connection));
}



?>




<?php

// Database connection settings
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL query to insert data into the tobeplan_plan2 table from the tobeplan1 table
    $sql = "INSERT INTO tobeplan_plan2 (`erp`, `icode`, `tobe`, `stockonhand`)
            SELECT tp.`erp`, tp.`icode`, tp.`tobe`, tp.`stockonhand`
            FROM tobeplan1 tp
            LEFT JOIN new_plan_data pn ON tp.`erp` = pn.`erp` AND tp.`icode` = pn.`icode`
            WHERE pn.`id` IS NULL
            AND tp.`tobe` > 0";
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "Data inserted successfully into the tobeplan_plan2 table.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$conn = null;

?>




<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to insert data into the process_plan2 table
    $sql = "
    INSERT INTO process_plan2 (icode, mold_id, cavity_id, mold_name, cavity_name, press_name, press_id)
    SELECT 
        pnt.icode,
        pnt.mold_id,
        pnt.cavity_id,
        pnt.mold_name,
        pnt.cavity_name,
        pnt.press_name,
        pnt.press AS press_id
    FROM 
        plannew_tem pnt
    JOIN 
        tobeplan_plan2 tpp ON tpp.icode = pnt.icode
    WHERE 
        pnt.created_at = (
            SELECT MAX(created_at)
            FROM plannew_tem sub
            WHERE sub.icode = pnt.icode
        );
    ";

    // Execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    echo "Data successfully inserted into the process_plan2 table.";

    // Remove duplicate entries in the process_plan2 table based on `icode`, `mold_id`, and `cavity_id`
    $deleteDuplicates = "
    DELETE FROM process_plan2
    WHERE id NOT IN (
        SELECT MIN(id)
        FROM (
            SELECT id, icode, mold_id, cavity_id
            FROM process_plan2
        ) AS tires_per_mold
        GROUP BY icode, mold_id, cavity_id
    );
    ";
    
    // Execute the query to remove duplicates
    $stmt = $conn->prepare($deleteDuplicates);
    $stmt->execute();

    echo "Duplicate data successfully removed from the process_plan2 table.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>



<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to insert data into the process_plan2 table
    $sql = "
    INSERT INTO process_plan2 (icode, mold_id, cavity_id, mold_name, cavity_name, press_name, press_id)
    SELECT 
        pnt.icode,
        pnt.mold_id,
        pnt.cavity_id,
        pnt.mold_name,
        pnt.cavity_name,
        pnt.press_name,
        pnt.press AS press_id
    FROM 
        plannew_tem pnt
    JOIN 
        tobeplan_plan2 tpp ON tpp.icode = pnt.icode
    WHERE 
        pnt.created_at = (
            SELECT MAX(created_at)
            FROM plannew_tem sub
            WHERE sub.icode = pnt.icode
        );
    ";

    // Execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    echo "Data successfully inserted into the process_plan2 table.";

    // Remove duplicate entries in the process_plan2 table based on `icode`, `mold_id`, and `cavity_id`
    $deleteDuplicates = "
    DELETE FROM process_plan2
    WHERE id NOT IN (
        SELECT MIN(id)
        FROM (
            SELECT id, icode, mold_id, cavity_id
            FROM process_plan2
        ) AS tires_per_mold
        GROUP BY icode, mold_id, cavity_id
    );
    ";
    
    // Execute the query to remove duplicates
    $stmt = $conn->prepare($deleteDuplicates);
    $stmt->execute();

    echo "Duplicate data successfully removed from the process_plan2 table.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>



<?php
// Database connection settings
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

// SQL query to update 'erp' in 'process_plan2' for each 'icode'
$sql = "UPDATE process_plan2 pp
        JOIN tobeplan_plan2 tp ON pp.icode = tp.icode
        SET pp.erp = tp.erp";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating records: " . $conn->error;
}

// Close connection
$conn->close();
?>




<?php
// Database connection settings
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

// SQL query to update 'tires_per_mold' in 'process_plan2'
$sql = "UPDATE process_plan2 pp
        JOIN tobeplan_plan2 tp ON pp.icode = tp.icode AND pp.erp = tp.erp
        SET pp.tires_per_mold = CEIL(CASE 
            WHEN (SELECT COUNT(*) 
                  FROM process_plan2 sub 
                  WHERE sub.icode = pp.icode AND sub.erp = pp.erp) = 0 
            THEN 0 
            ELSE tp.tobe / (
                SELECT COUNT(*) 
                FROM process_plan2 sub 
                WHERE sub.icode = pp.icode AND sub.erp = pp.erp
            )
        END);";

// Execute the update query
if ($conn->query($sql) === TRUE) {
    echo "Records updated successfully.";

    // Check if there is data in 'tobeplan_plan2' table
    $checkDataQuery = "SELECT COUNT(*) FROM tobeplan_plan2";
    $result = $conn->query($checkDataQuery);

    if ($result && $result->fetch_row()[0] > 0) {
        // Redirect to plannew56.php if there is data
        header("Location: plannew5656.php");
    } else {
        // Redirect to planning.php if there is no data
        header("Location: import22bnew3.php");
    }
    exit();
} else {
    echo "Error updating records: " . $conn->error;
}

// Close connection
$conn->close();
?>

