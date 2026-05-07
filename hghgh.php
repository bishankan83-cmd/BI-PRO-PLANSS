<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to insert data from plannew to plannew_tem
$sql_insert = "INSERT INTO plannew_tem (
                id, plan_id, erp, Customer, icode, description, tobe, press, press_name,
                mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name,
                start_date, end_date, tires_per_mold
            )
            SELECT 
                id, plan_id, erp, Customer, icode, description, tobe, press, press_name,
                mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name,
                start_date, end_date, tires_per_mold
            FROM plannew";

// Execute the query to insert data
if (mysqli_query($conn, $sql_insert)) {
    echo "Data inserted successfully into plannew_tem.<br>";
} else {
    echo "Error inserting data: " . mysqli_error($conn) . "<br>";
}

// SQL query to delete rows older than one month from plannew_tem
$sql_delete = "DELETE FROM plannew_tem 
               WHERE created_at < NOW() - INTERVAL 1 WEEK";

// Execute the query to delete old data
if (mysqli_query($conn, $sql_delete)) {
    echo "Old data deleted successfully from plannew_tem.";
} else {
    echo "Error deleting old data: " . mysqli_error($conn);
}

// Close the connection
mysqli_close($conn);
?>











<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919'; // Update this with the actual password
$targetDb = 'planatir_plann';

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

// Close connections
$sourceConn->close();      

$targetConn->close();

header("Location: get_process2.php");
exit();
?>



