<?php
// Replace these values with your database connection details
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

// Initialize the search term
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch data from the mold_mapping table based on the search term
$sql1 = "SELECT * FROM mold_mapping WHERE cavity_name LIKE '%$searchTerm%' OR icode LIKE '%$searchTerm%' OR mold_name LIKE '%$searchTerm%'";
$result1 = $conn->query($sql1);

// Fetch data from the mold_mapping2 table based on the search term
$sql2 = "SELECT * FROM mold_mapping2 WHERE cavity_name LIKE '%$searchTerm%' OR icode LIKE '%$searchTerm%' OR mold_name LIKE '%$searchTerm%'";
$result2 = $conn->query($sql2);

// Display data from mold_mapping table in a table
if ($result1->num_rows > 0) {
    echo "<h2>Mold Mapping Table</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Cavity Name</th><th>Icode</th><th>Mold Name</th><th>Start Date</th><th>End Date</th><th>ERP</th><th>Ref No</th></tr>";
    while ($row = $result1->fetch_assoc()) {
        // Retrieve ref number from worder table matching the erp
        $erp = $row["erp"];
        $refSql = "SELECT ref FROM worder WHERE erp = '$erp'";
        $refResult = $conn->query($refSql);
        $refRow = $refResult->fetch_assoc();

        // Display data in the table row
        echo "<tr><td>" . $row["cavity_name"] . "</td><td>" . $row["icode"] . "</td><td>" . $row["mold_name"] . "</td><td>" . $row["start_date"] . "</td><td>" . $row["end_date"] . "</td><td>" . $row["erp"] . "</td><td>" . ($refRow ? $refRow["ref"] : "N/A") . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

// Display data from mold_mapping2 table in a table
if ($result2->num_rows > 0) {
    echo "<h2>Mold Mapping2 Table</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Cavity Name</th><th>Icode</th><th>Mold Name</th><th>Start Date</th><th>End Date</th><th>ERP</th><th>Ref No</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        // Retrieve ref number from worder table matching the erp
        $erp = $row["erp"];
        $refSql = "SELECT ref FROM worder WHERE erp = '$erp'";
        $refResult = $conn->query($refSql);
        $refRow = $refResult->fetch_assoc();

        // Display data in the table row
        echo "<tr><td>" . $row["cavity_name"] . "</td><td>" . $row["icode"] . "</td><td>" . $row["mold_name"] . "</td><td>" . $row["start_date"] . "</td><td>" . $row["end_date"] . "</td><td>" . $row["erp"] . "</td><td>" . ($refRow ? $refRow["ref"] : "N/A") . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

// Close connection
$conn->close();
?> 
