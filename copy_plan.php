<?php
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

// Query to create the new table (if it doesn't exist)
$createTableQuery = "
    CREATE TABLE IF NOT EXISTS `plannew_copy` (
        `plan_id` int(11) NOT NULL,
        `erp` varchar(50) NOT NULL,
        `Customer` varchar(100) NOT NULL,
        `icode` varchar(50) NOT NULL,
        `description` varchar(100) NOT NULL,
        `tobe` int(11) NOT NULL,
        `press` int(11) NOT NULL,
        `press_name` varchar(50) NOT NULL,
        `mold_id` int(11) NOT NULL,
        `mold_name` varchar(50) NOT NULL,
        `cavity_id` int(11) NOT NULL,
        `cavity_name` varchar(50) NOT NULL,
        `cuing_group_id` int(11) DEFAULT NULL,
        `cuing_group_name` varchar(50) DEFAULT NULL,
        `start_date` datetime DEFAULT NULL,
        `end_date` datetime DEFAULT NULL,
        `tires_per_mold` int(11) NOT NULL DEFAULT 0
    )
";

if ($conn->query($createTableQuery) === TRUE) {
    echo "Table plannew_copy created successfully\n";

    // Query to copy data from plannew to plannew_copy
    $copyDataQuery = "INSERT INTO `plannew` SELECT * FROM `plannew1`";

    if ($conn->query($copyDataQuery) === TRUE) {
      

        // Query to delete data from the original table (plannew)
        $deleteDataQuery = "DELETE FROM `plannew1`";

        if ($conn->query($deleteDataQuery) === TRUE) {
            echo "Data deleted from plannew successfully\n";
        } else {
            echo "Error deleting data from plannew: " . $conn->error . "\n";
        }
    } else {
        echo "Error copying data: " . $conn->error . "\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Close connection
$conn->close();
header("Location: getprocess.php");
exit();

?>
