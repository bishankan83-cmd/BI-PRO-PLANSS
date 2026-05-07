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

// Prepare and bind parameters for insertion
$insert_query = "INSERT INTO another_table_name1 (id, serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, T52, rebound) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $connection->prepare($insert_query);
$stmt->bind_param("sssssssssssssssssssss", $id, $serial_number, $inputDate, $shift, $compound_name, $description, $cstock, $batch, $pallet, $weight, $quality_approved, $expire_date, $staff_name, $sg_value, $hardness, $mh, $ml, $t10, $t90, $t52, $rebound);

// Fetch all data from the table
$query = "SELECT * FROM another_table_nameb";
$result = mysqli_query($connection, $query);

// Check if there are any results
if (mysqli_num_rows($result) > 0) {
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
        
        // Execute the insertion statement
        $stmt->execute();
    }

    // Close statement
    $stmt->close();

    // Provide feedback to the user
    echo "Data moved and inserted successfully.";
    header("Location: barcode.php");
    exit();
} else {
    echo "No data found to move.";
}

// Close connection
$connection->close();

?>
