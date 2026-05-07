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
