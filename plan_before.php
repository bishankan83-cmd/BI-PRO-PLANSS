
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
$deleteQuery = "DELETE FROM tobeplan1";
if ($targetConn->query($deleteQuery) === TRUE) {
    echo "Existing data in target table deleted successfully.<br>";
} else {
    die("Error deleting data in target table: " . $targetConn->error);
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

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Establish a database connection
$connection = mysqli_connect($host, $username, $password, $database);

// Check for a successful connection
if (mysqli_connect_errno()) {
    die('Failed to connect to the database: ' . mysqli_connect_error());
}

// Copy data query (assuming $copyDataQuery is defined somewhere)
// Uncomment this line if $copyDataQuery is set.
// mysqli_query($connection, $copyDataQuery);

// Array of delete queries
$deleteQueries = [
    "DELETE FROM production_plan",
    "DELETE FROM tire_cavity",
    "DELETE FROM tire_molddd",
    "DELETE FROM quick_plan",
    "DELETE FROM process_plan",
    "DELETE FROM tobeplan_plan",
    "DELETE FROM tobeplan_tem",
    "DELETE FROM process_plan_tem",
];

// Execute each delete query
foreach ($deleteQueries as $query) {
    if (mysqli_query($connection, $query)) {
        echo "Successfully executed: $query\n";
    } else {
        echo "Error executing query: $query - " . mysqli_error($connection) . "\n";
    }
}

// Close the database connection
mysqli_close($connection);
?>




<?php
// Database connection parameters
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

// Define SQL queries to delete data from tables
$sql_delete_tobeplan = "DELETE FROM tobeplan_plan";
$sql_delete_process = "DELETE FROM process_plan";
$sql_delete_quick_new19 = "DELETE FROM quick_new19";
$sql_delete_quick_new199 = "DELETE FROM tobeplan12345";
$sql_delete_quick_new199 = "DELETE FROM tobeplan_tem";
$sql_delete_quick_new199 = "DELETE FROM process_plan_tem";


// Execute SQL queries
if ($conn->query($sql_delete_tobeplan) === TRUE &&
    $conn->query($sql_delete_process) === TRUE &&
    $conn->query($sql_delete_quick_new199) === TRUE &&
    $conn->query($sql_delete_quick_new19) === TRUE) {
    // Redirect to another page after successful deletion
    header("Location: dashboard.php");
    exit(); // Ensure subsequent code is not executed after redirect
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close connection
$conn->close();
?>
