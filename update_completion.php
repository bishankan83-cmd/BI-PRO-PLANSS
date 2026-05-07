<?php
// update_completion.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the AJAX request
    $icode = $_POST["icode"];
    $isChecked = $_POST["isChecked"];

    // Validate and sanitize data (you should perform more robust validation in a real-world scenario)
    $icode = filter_var($icode, FILTER_SANITIZE_STRING);
    $isChecked = filter_var($isChecked, FILTER_VALIDATE_BOOLEAN);

    // Establish a database connection
    $hostname = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    $connection = mysqli_connect($hostname, $username, $password, $database);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Update the completion status in the database
    $updateQuery = "UPDATE `process` SET `is_completed` = $isChecked WHERE `icode` = '$icode'";
    $result = mysqli_query($connection, $updateQuery);

    if ($result) {
        echo "Update successful";
    } else {
        echo "Update failed";
    }

    // Close the database connection
    mysqli_close($connection);
} else {
    // Invalid request method
    http_response_code(400);
    echo "Invalid request method";
}
?>
