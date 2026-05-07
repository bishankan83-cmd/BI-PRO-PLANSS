<?php
// Replace these variables with your database credentials
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a connection to the database
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to calculate the total amount of positive transitions for each erp
$sumQuery = "
SELECT
  `erp`,
  SUM(CASE WHEN `tobe` > 0 THEN `tobe` ELSE 0 END) AS `total_positive_amount`
FROM
  `tobeplan1`
GROUP BY
  `erp`";

// Execute the sum calculation query
$result = $conn->query($sumQuery);

// Display the results
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ERP</th><th>Total Positive Amount</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["erp"] . "</td>";
        echo "<td>" . $row["total_positive_amount"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No results found";
}

// Close the database connection
$conn->close();
?>