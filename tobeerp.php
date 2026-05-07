<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get the 'ref' value corresponding to 'erp' from the 'worder' database table
$refSql = "SELECT erp, ref FROM worder";
$refResult = $conn->query($refSql);

// Create an associative array to store 'ref' values for each 'erp'
$refValues = array();
while ($row = $refResult->fetch_assoc()) {
    $refValues[$row['erp']] = $row['ref'];
}

// SQL query to get the sum of positive 'tobe' assets related to each 'erp' from the 'tobeplan1' table
$sql = "SELECT erp, SUM(CASE WHEN tobe > 0 THEN tobe ELSE 0 END) AS total_tobe_assets
        FROM tobeplan1
        GROUP BY erp";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ERP</th><th>Total TOBE Assets</th><th>Ref</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $erp = $row["erp"];
        $totalTobeAssets = $row["total_tobe_assets"];
        $ref = isset($refValues[$erp]) ? $refValues[$erp] : "Not found";
        echo "<tr><td>$erp</td><td>$totalTobeAssets</td><td>$ref</td></tr>";
    }
    echo "</table>";
} else {
    echo "No results found.";
}

// Close the database connection
$conn->close();
?>



<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get the sum of positive 'tobe' assets related to each 'erp' and their corresponding references
$sql = "SELECT t.erp, SUM(CASE WHEN t.tobe > 0 THEN t.tobe ELSE 0 END) AS total_tobe_assets, w.reference
        FROM tobeplan1 t
        LEFT JOIN worder w ON t.erp = w.erp
        GROUP BY t.erp";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ERP</th><th>Total TOBE Assets</th><th>Reference</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["erp"] . "</td><td>" . $row["total_tobe_assets"] . "</td><td>" . $row["reference"] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No results found.";
}

// Close the database connection
$conn->close();
?>
