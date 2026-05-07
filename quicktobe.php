<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming your source table is named "tobeplan" and the destination table is "tobeplannew"
$sourceTable = "tobeplannew";
$destinationTable = "tobeplannew1";

// Check if there is data in the source table
$sqlCount = "SELECT COUNT(*) as count FROM $sourceTable";
$resultCount = $conn->query($sqlCount);

if ($resultCount->num_rows > 0) {
    $rowCount = $resultCount->fetch_assoc();
    $count = $rowCount['count'];

    if ($count > 0) {
        // Find the row with the lowest ID in the source table
        $sqlSelect = "SELECT * FROM $sourceTable ORDER BY id ASC LIMIT 1";
        $result = $conn->query($sqlSelect);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Insert the data into the destination table
            $sqlInsert = "INSERT INTO $destinationTable (id, icode, tobe, erp, stockonhand) VALUES (
                " . $row['id'] . ",
                '" . $row['icode'] . "',
                " . $row['tobe'] . ",
                '" . $row['erp'] . "',
                " . $row['stockonhand'] . "
            )";

            if ($conn->query($sqlInsert) === TRUE) {
                echo "Data with the lowest ID moved successfully.";

                // Delete the row from the source table
                $sqlDelete = "DELETE FROM $sourceTable WHERE id = " . $row['id'];
                if ($conn->query($sqlDelete) === TRUE) {
                    echo "Row deleted from the source table.";

                    // Redirect to another PHP page
                    header("Location: quickgo.php");
                    exit; // Ensure no further output is sent
                } else {
                    echo "Error deleting row from the source table: " . $conn->error;
                }
            } else {
                echo "Error moving data: " . $conn->error;
            }
        }
    } else {
        // Redirect to another PHP page if there is no data in the source table
        header("Location: quickplan_update.php");
        exit; // Ensure no further output is sent
    }
} else {
    echo "Error checking data: " . $conn->error;
}

// Close the MySQL connection
$conn->close();
?>
