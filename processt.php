<?php
// Database connection details
$servername = "localhost"; // Change this to your MySQL server name if different
$username = "planatir_task_managemen"; // Change this to your MySQL username
$password = "Bishan@1919"; // Change this to your MySQL password
$dbname = "planatir_task_managemen"; // Change this to your MySQL database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to insert records from processt table where is_completed is 1 into process table
$sql = "INSERT INTO `processt` (
            `id`,
            `icode`,
            `mold_id`,
            `tires_per_mold`,
            `cavity_id`,
            `mold_name`,
            `cavity_name`,
            `press_name`,
            `press_id`,
            `erp`,
            `serial`,
            `is_completed`,
            `is_highlighted`,
            `start_date`
        )
        SELECT
            `id`,
            `icode`,
            `mold_id`,
            `tires_per_mold`,
            `cavity_id`,
            `mold_name`,
            `cavity_name`,
            `press_name`,
            `press_id`,
            `erp`,
            `serial`,
            `is_completed`,
            `is_highlighted`,
            `start_date`
        FROM
            `process`
        WHERE
            `is_completed` = 1";

if (mysqli_query($conn, $sql)) {
    echo "Records inserted successfully!";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>
