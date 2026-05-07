<?php
// Database connection details
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

// SQL query to fetch data with positive 'tobe' values and brand information
$sql = "SELECT 
            t.id, 
            t.icode, 
            t.tobe, 
            t.erp, 
            t.stockonhand, 
            w.brand 
        FROM 
            tobeplan1 t
        JOIN 
            worder w
        ON 
            t.icode = w.icode
        WHERE 
            t.tobe > 0";

$result = $conn->query($sql);

// SQL query to get the sum of 'tobe' values
$sum_sql = "SELECT SUM(tobe) AS total_tobe FROM tobeplan1 WHERE tobe > 0";
$sum_result = $conn->query($sum_sql);

// Fetch the sum of 'tobe' values
$total_tobe = 0;
if ($sum_result->num_rows > 0) {
    $sum_row = $sum_result->fetch_assoc();
    $total_tobe = $sum_row["total_tobe"];
}

// Display results
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>ICODE</th><th>TOBE</th><th>ERP</th><th>Stock on Hand</th><th>Brand</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["icode"] . "</td>";
        echo "<td>" . $row["tobe"] . "</td>";
        echo "<td>" . $row["erp"] . "</td>";
        echo "<td>" . $row["stockonhand"] . "</td>";
        echo "<td>" . $row["brand"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

// Display the sum of 'tobe' values
echo "<br><strong>Total Sum of TOBE:</strong> " . $total_tobe;

// Close connection
$conn->close();
?>
