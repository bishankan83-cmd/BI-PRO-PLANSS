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

// Check if start_batch and end_batch are set and not empty
if (isset($_POST["start_batch"]) && isset($_POST["end_batch"]) && !empty($_POST["start_batch"]) && !empty($_POST["end_batch"])) {
    // Get start_batch and end_batch values from POST
    $start_batch = $_POST["start_batch"];
    $end_batch = $_POST["end_batch"];

    // Query to fetch data between batches
    $sql = "SELECT * FROM another_table_name3 WHERE batch BETWEEN ? AND ?";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bind_param("ss", $start_batch, $end_batch);

    // Execute statement
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Check if there are results
    if ($result->num_rows > 0) {
        // Prepare INSERT statement for another_table_name7
        $insert_sql = "INSERT INTO another_table_name7 (id, serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, rebound) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);

        // Bind parameters for insertion
        $insert_stmt->bind_param("iisssssssssssssssssss", $id, $serial_number, $inputDate, $shift, $compound_name, $description, $cstock, $batch, $pallet, $created_at, $weight, $quality_approved, $expire_date, $staff_name, $sg_value, $hardness, $mh, $ml, $t10, $t90, $rebound);

        // Fetch each row and insert into another_table_name7
        while($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $serial_number = $row["serial_number"];
            $inputDate = $row["inputDate"];
            $shift = $row["shift"];
            $compound_name = $row["compound_name"];
            $description = $row["description"];
            $cstock = $row["cstock"];
            $batch = $row["batch"];
            $pallet = $row["pallet"];
            $created_at = $row["created_at"];
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
            $rebound = $row["rebound"];

            // Execute INSERT statement
            $insert_stmt->execute();
        }

        // Close INSERT statement
        $insert_stmt->close();

        echo "Data inserted into another_table_name7 successfully.";
    } else {
        echo "No results found for the specified batch range.";
    }

    // Close statement
    $stmt->close();
} else {
    echo "Invalid batch range.";
}

// Close connection
$conn->close();
?>
