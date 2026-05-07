<?php
// Replace these with your actual database credentials
$hostname = "localhost:3306";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to count the number of rows in the wcopy table
$countQuery = "SELECT COUNT(*) AS row_count FROM `wcopy`";
$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$rowCount = $countRow["row_count"];

if ($rowCount > 0) {
    // There is data in the wcopy table, proceed with moving data
    // Query to find the minimum date
    $minDateQuery = "SELECT MIN(`date`) AS min_date FROM `wcopy`";
    $minDateResult = $conn->query($minDateQuery);
    $minDateRow = $minDateResult->fetch_assoc();
    $minDate = $minDateRow["min_date"];

    // SQL query to insert data into copied_work table
    $insertQuery = "INSERT INTO `copied_work` (`id`, `date`, `Customer`, `wono`, `ref`, `erp`, `icode`, `t_size`, `brand`, `col`, `fit`, `rim`, `cons`, `fweight`, `ptv`, `new`, `cbm`, `kgs`)
                    SELECT `id`, `date`, `Customer`, `wono`, `ref`, `erp`, `icode`, `t_size`, `brand`, `col`, `fit`, `rim`, `cons`, `fweight`, `ptv`, `new`, `cbm`, `kgs`
                    FROM `wcopy`
                    WHERE `date` = '$minDate'";

    $resultInsert = $conn->query($insertQuery);

    if ($resultInsert) {
        // Delete the copied data from wcopy table
        $deleteQuery = "DELETE FROM `wcopy` WHERE `date` = '$minDate'";
        $resultDelete = $conn->query($deleteQuery);

        if ($resultDelete) {
            // Data moved to copied_work and deleted from wcopy successfully.
        } else {
            // Error deleting data from wcopy: " . $conn->error;
        }
    } else {
        // Error inserting data into copied_work: " . $conn->error;
    }
} else {
    // There is no data in the wcopy table, redirect to dashboard.php
    header("Location: copytobe.php");
    exit();
}

// Close the connection
$conn->close();

header("Location: subtractR1.php");
exit();
?>
