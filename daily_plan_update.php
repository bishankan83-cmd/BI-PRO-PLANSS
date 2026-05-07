<?php
// Database connection parameters
$servername = "localhost"; // Change to your MySQL server hostname
$username = "planatir_task_managemen"; // Change to your MySQL username
$password = "Bishan@1919"; // Change to your MySQL password
$database = "planatir_task_managemen"; // Change to your MySQL database name

// Create a database connection
$connection = mysqli_connect($servername, $username, $password, $database);

// Check the connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define the template table and realstock table names
$templateTable = 'daily_plan_data1';
$realstockTable = 'realstock';
$backupTable = 'daily_plan_data'; // New table to store backup data

// Copy data from daily_plan_data1 to backup table
$copyQuery = "INSERT INTO $backupTable SELECT * FROM $templateTable";
$copyResult = mysqli_query($connection, $copyQuery);

if (!$copyResult) {
    echo "Error copying data to $backupTable: " . mysqli_error($connection);
    mysqli_close($connection);
    exit();
}

// Update cstock in the realstock table based on icode from the template table
$updateQuery = "UPDATE $realstockTable r
                JOIN (
                    SELECT t.icode, SUM(t.AdditionalData) AS TotalAdditionalData
                    FROM $templateTable t
                    GROUP BY t.icode
                ) t ON r.icode = t.icode
                SET r.cstock = r.cstock + t.TotalAdditionalData";

$updateResult = mysqli_query($connection, $updateQuery);

if ($updateResult) {
    // Update successful

    // Now, delete all data in the daily_plan_data1 table
    $deleteQuery = "DELETE FROM $templateTable";
    $deleteResult = mysqli_query($connection, $deleteQuery);

    if ($deleteResult) {
        // Deletion successful
       
       
      

        // Add a button to go to the dashboard
        echo '<a href="import22b.php"><button>Click To Next</button></a>';
    } else {
        echo "Error deleting data from $templateTable: " . mysqli_error($connection);
    }
} else {
    echo "Error updating cstock: " . mysqli_error($connection);
}

// Close the database connection
mysqli_close($connection);
?>
