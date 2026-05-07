<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hostname = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to find the minimum date
$minDateQuery = "SELECT MIN(`date`) AS min_date FROM `wcopy`";
$minDateResult = $conn->query($minDateQuery);
$minDateRow = $minDateResult->fetch_assoc();
$minDate = $minDateRow["min_date"];

if (!empty($minDate)) {
    // Use INSERT IGNORE to skip duplicate entries
    $insertQuery = "INSERT IGNORE INTO `copied_work` (`id`, `date`, `Customer`, `wono`, `ref`, `erp`, `icode`, `t_size`, `brand`, `col`, `fit`, `rim`, `cons`, `fweight`, `ptv`, `new`, `cbm`, `kgs`)
                    SELECT `id`, `date`, `Customer`, `wono`, `ref`, `erp`, `icode`, `t_size`, `brand`, `col`, `fit`, `rim`, `cons`, `fweight`, `ptv`, `new`, `cbm`, `kgs`
                    FROM `wcopy`
                    WHERE `date` = ?";

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("s", $minDate);
    $resultInsert = $stmt->execute();

    if ($resultInsert) {
        // Delete the copied data from wcopy table
        $deleteQuery = "DELETE FROM `wcopy` WHERE `date` = ?";
        $stmtDelete = $conn->prepare($deleteQuery);
        $stmtDelete->bind_param("s", $minDate);
        $resultDelete = $stmtDelete->execute();

        if (!$resultDelete) {
            echo "Error deleting data from wcopy: " . $conn->error;
        }
        $stmtDelete->close();
    } else {
        echo "Error inserting data into copied_work: " . $conn->error;
    }
    $stmt->close();
} else {
    // No data in the wcopy table, redirect to another PHP page
    $conn->close();
    header("Location: planning.php");
    exit();
}

// Close the connection
$conn->close();

header("Location: sleep7.php");
exit();
?>
