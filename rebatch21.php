<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// SOLUTION 2: Use INSERT IGNORE to skip duplicates
/*
$insert_query = "INSERT IGNORE INTO target_table3 (id, serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, T52, rebound) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
*/

// SOLUTION 3: Use ON DUPLICATE KEY UPDATE to update existing records
/*
$insert_query = "INSERT INTO target_table3 (id, serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, T52, rebound) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE serial_number=VALUES(serial_number), inputDate=VALUES(inputDate), shift=VALUES(shift), compound_name=VALUES(compound_name), description=VALUES(description), cstock=VALUES(cstock), batch=VALUES(batch), pallet=VALUES(pallet), weight=VALUES(weight), quality_approved=VALUES(quality_approved), expire_date=VALUES(expire_date), staff_name=VALUES(staff_name), sg_value=VALUES(sg_value), hardness=VALUES(hardness), mh=VALUES(mh), ml=VALUES(ml), t10=VALUES(t10), t90=VALUES(t90), T52=VALUES(T52), rebound=VALUES(rebound)";
*/

// SOLUTION 4: Check if record exists before inserting
$insert_query = "INSERT INTO target_table3 (id, serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, T52, rebound) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$check_query = "SELECT COUNT(*) FROM target_table3 WHERE id = ?";

$stmt = $connection->prepare($insert_query);
$check_stmt = $connection->prepare($check_query);

$stmt->bind_param("sssssssssssssssssssss", $id, $serial_number, $inputDate, $shift, $compound_name, $description, $cstock, $batch, $pallet, $weight, $quality_approved, $expire_date, $staff_name, $sg_value, $hardness, $mh, $ml, $t10, $t90, $t52, $rebound);
$check_stmt->bind_param("s", $id);

// Fetch all data from the table
$query = "SELECT * FROM target_table2";
$result = mysqli_query($connection, $query);

// Check if there are any results
if (mysqli_num_rows($result) > 0) {
    $success_count = 0;
    $skipped_count = 0;
    $error_count = 0;
    
    // Output data rows
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["id"];
        $inputDate = $row["inputDate"];
        $serial_number = $row["serial_number"];
        $shift = $row["shift"];
        $compound_name = $row["compound_name"];
        $description = $row["description"];
        $cstock = $row["cstock"];
        $batch = $row["batch"];
        $pallet = $row["pallet"];
        $weight = $row["weight"];
        $quality_approved = $row["quality_approved"];
        $expire_date = $row["expire_date"];
        $staff_name = $row["staff_name"];
        $sg_value = $row["sg_value"];
        $hardness = $row["hardness"];
        $mh = $row["mh"];
        $ml = $row["ml"];
        $t10 = $row["t10"];
        $t90 = $row["t90"];
        $t52 = $row["T52"]; // New column added
        $rebound = $row["rebound"];
        
        // Check if record already exists
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_row()[0];
        
        if ($exists == 0) {
            // Record doesn't exist, insert it
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
                echo "Error inserting record ID $id: " . $stmt->error . "<br>";
            }
        } else {
            // Record already exists, skip it
            $skipped_count++;
            echo "Skipped duplicate record ID: $id<br>";
        }
    }
    
    // Close statements
    $stmt->close();
    $check_stmt->close();
    
    // Provide feedback to the user
    echo "Data migration completed. Success: $success_count, Skipped: $skipped_count, Errors: $error_count";
    
    if ($error_count == 0) {
        header("Location: rebatch3.php");
        exit();
    }
} else {
    echo "No data found to move.";
}

// Close connection
$connection->close();
?>