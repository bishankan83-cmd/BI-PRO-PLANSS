<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919';
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

// Step 1: Check if there's data in the source table
$checkQuery = "SELECT COUNT(*) as count FROM plannew";
$checkResult = $sourceConn->query($checkQuery);
$row = $checkResult->fetch_assoc();
$dataCount = $row['count'];

if ($dataCount > 0) {
    // Step 2: Delete existing data in the target table only if source has data
    $deleteQuery = "DELETE FROM plannew";
    if ($targetConn->query($deleteQuery) === TRUE) {
        echo "Existing data in target table deleted successfully.<br>";
    } else {
        die("Error deleting data in target table: " . $targetConn->error);
    }

    // Step 3: Fetch and insert data from source table
    $selectQuery = "SELECT * FROM plannew";
    $result = $sourceConn->query($selectQuery);

    if ($result && $result->num_rows > 0) {
        // Track successful insertions
        $successCount = 0;
        $totalCount = $result->num_rows;
        
        // Prepare insert query for the target table
        while ($row = $result->fetch_assoc()) {
            // Escape string values to prevent SQL injection
            $escapedRow = array_map(function($value) use ($targetConn) {
                return $targetConn->real_escape_string($value);
            }, $row);
            
            $insertQuery = "INSERT INTO plannew (id, plan_id, erp, Customer, icode, description, tobe, press, press_name, mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name, start_date, end_date, tires_per_mold) 
                            VALUES (
                                '{$escapedRow['id']}', '{$escapedRow['plan_id']}', '{$escapedRow['erp']}', '{$escapedRow['Customer']}', '{$escapedRow['icode']}', '{$escapedRow['description']}', '{$escapedRow['tobe']}', '{$escapedRow['press']}', 
                                '{$escapedRow['press_name']}', '{$escapedRow['mold_id']}', '{$escapedRow['mold_name']}', '{$escapedRow['cavity_id']}', '{$escapedRow['cavity_name']}', '{$escapedRow['cuing_group_id']}', 
                                '{$escapedRow['cuing_group_name']}', '{$escapedRow['start_date']}', '{$escapedRow['end_date']}', '{$escapedRow['tires_per_mold']}'
                            )";

            if ($targetConn->query($insertQuery)) {
                $successCount++;
            } else {
                echo "Error inserting data: " . $targetConn->error . "<br>";
            }
        }
        echo "Data copied successfully: $successCount of $totalCount records.<br>";
    } else {
        echo "No data found in source table (unexpected since count was > 0).<br>";
    }
} else {
    echo "No data found in source table. Target database was not modified.<br>";
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

// Step 1: Check if there is data in the source table
$selectQuery = "SELECT * FROM tobeplan1";
$result = $sourceConn->query($selectQuery);

if ($result && $result->num_rows > 0) {
    // Step 2: Delete existing data in the target table only if source table has data
    $deleteQuery = "DELETE FROM tobeplan1";
    if ($targetConn->query($deleteQuery) === TRUE) {
        echo "Existing data in target table deleted successfully.<br>";
    } else {
        die("Error deleting data in target table: " . $targetConn->error);
    }

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
    echo "No data found in source table. Target table remains unchanged.<br>";
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
$targetPass = 'Bishan@1919';
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

// Step 1: Check if there is data in the source table
$selectQuery = "SELECT * FROM stock";
$result = $sourceConn->query($selectQuery);

if ($result && $result->num_rows > 0) {
    // Step 2: Delete existing data in the target table only if source table has data
    $deleteQuery = "DELETE FROM stock";
    if ($targetConn->query($deleteQuery) === TRUE) {
        echo "Existing data in target table deleted successfully.<br>";
    } else {
        die("Error deleting data in target table: " . $targetConn->error);
    }

    // Prepare insert query for the target table
    $insertQuery = "INSERT INTO stock (id, icode, t_size, brand, col, rim, gweight, cstock) VALUES ";
    $insertValues = [];
    
    while ($row = $result->fetch_assoc()) {
        // Escape string values to prevent SQL injection
        $id = $sourceConn->real_escape_string($row['id']);
        $icode = $sourceConn->real_escape_string($row['icode']);
        $t_size = $sourceConn->real_escape_string($row['t_size']);
        $brand = $sourceConn->real_escape_string($row['brand']);
        $col = $sourceConn->real_escape_string($row['col']);
        $rim = $sourceConn->real_escape_string($row['rim']);
        $gweight = $sourceConn->real_escape_string($row['gweight']);
        $cstock = $sourceConn->real_escape_string($row['cstock']);
        
        // Collect data for each row
        $insertValues[] = "('$id', '$icode', '$t_size', '$brand', '$col', '$rim', '$gweight', '$cstock')";
    }
    
    // If we have values to insert, construct and execute the insert query
    if (count($insertValues) > 0) {
        $insertQuery .= implode(", ", $insertValues);
        
        if ($targetConn->query($insertQuery) === TRUE) {
            echo "Data copied successfully: " . count($insertValues) . " records.<br>";
        } else {
            echo "Error inserting data: " . $targetConn->error . "<br>";
        }
    } else {
        echo "No data to insert.<br>";
    }
} else {
    echo "No data found in source table. Target table remains unchanged.<br>";
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
$targetPass = 'Bishan@1919';
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

// Step 1: Check if there's data in the source table
$checkQuery = "SELECT COUNT(*) as count FROM  new_process";
$checkResult = $sourceConn->query($checkQuery);
$row = $checkResult->fetch_assoc();
$dataCount = $row['count'];

if ($dataCount > 0) {
    // Step 2: Delete existing data in the target table only if source has data
    $deleteQuery = "DELETE FROM new_process";
    if ($targetConn->query($deleteQuery) === TRUE) {
        echo "Existing data in target table deleted successfully.<br>";
    } else {
        die("Error deleting data in target table: " . $targetConn->error);
    }

    // Step 3: Fetch and insert data from source table
    $selectQuery = "SELECT * FROM  new_process";
    $result = $sourceConn->query($selectQuery);

    if ($result && $result->num_rows > 0) {
        // Track successful insertions
        $successCount = 0;
        $totalCount = $result->num_rows;
        
        // Prepare insert query for the target table
        while ($row = $result->fetch_assoc()) {
            // Escape string values to prevent SQL injection
            $escapedRow = array_map(function($value) use ($targetConn) {
                return $targetConn->real_escape_string($value);
            }, $row);
            
            // Adjusted Insert Query for new_process table
            $insertQuery = "INSERT INTO new_process (id, icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date)
                            VALUES (
                                '{$escapedRow['id']}', '{$escapedRow['icode']}', '{$escapedRow['mold_id']}', '{$escapedRow['tires_per_mold']}', '{$escapedRow['cavity_id']}', 
                                '{$escapedRow['mold_name']}', '{$escapedRow['cavity_name']}', '{$escapedRow['press_name']}', '{$escapedRow['press_id']}', 
                                '{$escapedRow['erp']}', '{$escapedRow['serial']}', '{$escapedRow['is_completed']}', '{$escapedRow['is_highlighted']}', 
                                '{$escapedRow['first_tobe']}', '{$escapedRow['start_date']}'
                            )";

            if ($targetConn->query($insertQuery)) {
                $successCount++;
            } else {
                echo "Error inserting data: " . $targetConn->error . "<br>";
            }
        }
        echo "Data copied successfully: $successCount of $totalCount records.<br>";
    } else {
        echo "No data found in source table (unexpected since count was > 0).<br>";
    }
} else {
    echo "No data found in source table. Target database was not modified.<br>";
}

// Close connections
$sourceConn->close();
$targetConn->close();
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

// Redirect to dashboard
header("Location: get_new.php");
exit(); // Ensure subsequent code is not executed after redirect

// Close connection
$conn->close();
?>
