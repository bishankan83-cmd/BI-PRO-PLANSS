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

// SQL query to get monthly data for the current year
$sql = "SELECT
            MONTH(Date) AS Month,
            SUM(CAST(AdditionalData AS DECIMAL)) AS TotalAdditionalData
        FROM
            daily_plan_data
        WHERE
            YEAR(Date) = YEAR(CURDATE())
        GROUP BY
            MONTH(Date)";

$result = $conn->query($sql);

$months = [];
$totals = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $months[] = $row['Month'];
        $totals[] = $row['TotalAdditionalData'];
    }
}

// Format months as full month names
$monthNames = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
    7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$monthLabels = array_map(function($month) use ($monthNames) {
    return $monthNames[$month];
}, $months);

$conn->close();

// Output data as JSON
header('Content-Type: application/json');
echo json_encode([
    'months' => $monthLabels,
    'totals' => $totals
]);
?>
