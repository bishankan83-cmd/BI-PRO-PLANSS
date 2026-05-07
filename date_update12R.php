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

// Fetch the largest date from the daily_plan_data table
$getMaxDateSql = "SELECT MAX(date) AS max_date FROM daily_plan_data";
$result = $conn->query($getMaxDateSql);
$row = $result->fetch_assoc();
$availability_date = $row['max_date'];

// Check if the result is valid
if (!$availability_date) {
    die("Failed to retrieve the largest date from the daily_plan_data table.");
}

// Update the availability_date of all presses in the 'press' table
$updatePressSql = "UPDATE press SET availability_date = '$availability_date'";
$conn->query($updatePressSql);

// Update the availability_date of all molds in the 'mold' table
$updateMoldSql = "UPDATE mold SET availability_date = '$availability_date'";
$conn->query($updateMoldSql);

// Update the availability_date of all cavities in the 'cavity' table
$updateCavitySql = "UPDATE cavity SET availability_date = '$availability_date'";
$conn->query($updateCavitySql);

// Check if there are records in the 'process' table
$checkProcessSql = "SELECT COUNT(*) AS process_count FROM process";
$result = $conn->query($checkProcessSql);
$row = $result->fetch_assoc();
$processCount = $row['process_count'];

// Redirect based on the presence of records in the 'process' table
if ($processCount == 0) {
    echo "<p>No records found in the 'process' table. Redirecting...</p>";
    header("refresh:1;url=plannew34new234R.php"); // Redirect after 3 seconds
} else {
    // Count distinct ERP numbers in the 'process' table
    $countErpSql = "SELECT COUNT(DISTINCT erp) AS erp_count FROM process";
    $result = $conn->query($countErpSql);
    $row = $result->fetch_assoc();
    $erpCount = $row['erp_count'];

    // Redirect based on the count of distinct ERP numbers
    if ($erpCount > 1) {
        echo "<p>Multiple distinct ERP numbers found. Redirecting...</p>";
        header("refresh:1;url=copy_completeR.php"); // Redirect after 3 seconds
    } else {
        echo "<p> An order has not been processed before, please process it first Redirecting...</p>";
        echo "<button onclick=\"window.location.href='plannew45.php';\">OK</button>"; // Button for redirect
        exit(); // Exit after displaying the message and button
    }
}

$conn->close(); // Close the database connection
?>
