<?php
// Database connection settings
$hostname = 'localhost:3306';
$username = 'root';
$password = '';
$database = 'task_management';

// Connect to the database
$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve tire production data
$tireQuery = "SELECT icode, tobe FROM tobeplan";
$tireResult = mysqli_query($conn, $tireQuery);

// Retrieve mold details
$moldQuery = "SELECT * FROM mold_details";
$moldResult = mysqli_query($conn, $moldQuery);

// Create a new table for selected molds and cavities
$selectedTable = 'selected_molds_cavities';
$createTableQuery = "CREATE TABLE IF NOT EXISTS $selectedTable (mold_id INT, press_id INT, cavity_id INT)";
mysqli_query($conn, $createTableQuery);

// Iterate through tire production data
while ($tireRow = mysqli_fetch_assoc($tireResult)) {
    $tireIcode = $tireRow['icode'];
    $tireName = $tireRow['name'];
    $tireTires = $tireRow['number_of_tires'];

    // Match molds with the tire production data
    while ($moldRow = mysqli_fetch_assoc($moldResult)) {
        $moldIcode = $moldRow['mold_icode'];
        $moldPressId = $moldRow['press_id'];
        $moldCavityId = $moldRow['cavity_id'];

        // Check if the mold matches the tire production
        if ($moldIcode == $tireIcode) {
            // Insert the mold and cavity into the selected table
            $insertQuery = "INSERT INTO $selectedTable (mold_id, press_id, cavity_id) VALUES ('$moldIcode', '$moldPressId', '$moldCavityId')";
            mysqli_query($conn, $insertQuery);
        }
    }

    // Reset mold details result pointer
    mysqli_data_seek($moldResult, 0);
}

// Close the database connection
mysqli_close($conn);
?>
