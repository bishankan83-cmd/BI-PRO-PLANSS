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

// SQL query to get daily data for the current month
$sql = "SELECT
            DATE(Date) AS Day,
            SUM(CAST(AdditionalData AS DECIMAL)) AS TotalAdditionalData
        FROM
            daily_plan_data
        WHERE
            YEAR(Date) = YEAR(CURDATE()) AND
            MONTH(Date) = MONTH(CURDATE())
        GROUP BY
            DATE(Date)";

$result = $conn->query($sql);

$days = [];
$totals = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $days[] = $row['Day'];
        $totals[] = $row['TotalAdditionalData'];
    }
}

$conn->close();

// Output data as JSON
header('Content-Type: application/json');
echo json_encode([
    'days' => $days,
    'totals' => $totals
]);
?>
