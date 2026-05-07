<?php
// Replace with your database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Step 1: Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Fetch ERP number from the derp table
$erpSql = "SELECT erp FROM derp"; // Assuming there's a column named 'erp' in the derp table
$result = $conn->query($erpSql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $erpNumber = $row['erp'];
} else {
    echo "No ERP number found in the derp table.\n";
    $conn->close();
    exit; // Make sure to exit the script if no ERP number is found
}

$updateSql = "UPDATE stock
              INNER JOIN worder ON stock.icode = worder.icode
              INNER JOIN tobeplan1 ON stock.icode = tobeplan1.icode
              SET stock.cstock = stock.cstock + (worder.new - tobeplan1.tobe)
              WHERE worder.erp = '$erpNumber'"; // Use the retrieved ERP number as the filter

if ($conn->query($updateSql) === TRUE) {
    echo "Data in the cstock column of the stock table has been updated successfully.\n";
} else {
    echo "Error updating data in the stock table: " . $conn->error . "\n";
}

// Close the database connection
$conn->close();

// Redirect to another_page.php after updates
header("Location: deleteerp4.php");
exit; // Make sure to exit the script to prevent further execution
?>
