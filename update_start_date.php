<?php
// Include your database connection code here

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $icode = $_POST["icode"];
    $newStartDate = $_POST["newStartDate"];

    // Perform the database update based on your database structure
    $hostname = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    $connection = mysqli_connect($hostname, $username, $password, $database);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $updateQuery = "UPDATE `tire` SET availability_date = ? WHERE icode = ?";
    $stmt = mysqli_prepare($connection, $updateQuery);
    mysqli_stmt_bind_param($stmt, "si", $newStartDate, $icode);

    if (mysqli_stmt_execute($stmt)) {
        echo "Start date updated successfully";
    } else {
        echo "Failed to update start date";
    }

    mysqli_close($connection);
}
?>
