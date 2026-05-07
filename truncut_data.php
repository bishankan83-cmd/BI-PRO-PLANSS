<?php
$sourceServername = "localhost";
$sourceUsername = "planatir_task_managemen";
$sourcePassword = "Bishan@1919";
$sourceDatabase = "planatir_task_managemen";
$sourceTable = "tobeplan1";

$destinationServername = "localhost";
$destinationUsername = "planatir_task_managemen";
$destinationPassword = "Bishan@1919";
$destinationDatabase = "planatir_task_managemen";
$destinationTable = "tobeplan";

// Create a connection to the source database
$sourceConn = new mysqli($sourceServername, $sourceUsername, $sourcePassword, $sourceDatabase);

// Check connection to the source database
if ($sourceConn->connect_error) {
    die("Source connection failed: " . $sourceConn->connect_error);
}

// Fetch data from the source table
$sqlSelect = "SELECT * FROM $sourceTable";
$result = $sourceConn->query($sqlSelect);

if ($result->num_rows > 0) {
    // Create a connection to the destination database
    $destinationConn = new mysqli($destinationServername, $destinationUsername, $destinationPassword, $destinationDatabase);

    // Check connection to the destination database
    if ($destinationConn->connect_error) {
        die("Destination connection failed: " . $destinationConn->connect_error);
    }

    // Insert data into the destination table
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];
        $tobe = $row['tobe'];
        $erp = $row['erp'];
        $stockonhand = $row['stockonhand'];

        $sqlInsert = "INSERT INTO $destinationTable (icode, tobe, erp, stockonhand) VALUES ('$icode', $tobe, '$erp', $stockonhand)";
        
        if ($destinationConn->query($sqlInsert) !== TRUE) {
            echo "Error inserting data: " . $destinationConn->error . "<br>";
        }
    }

    echo "Data transferred successfully.<br>";

    // Close the connection to the destination database
    $destinationConn->close();
} else {
    echo "No data found in $sourceTable";
}

// Close the connection to the source database
$sourceConn->close();

header("Location: deleteplan2.php"); // Replace "new_page.php" with the actual filename
exit();
?>
