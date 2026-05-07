<?php

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

// SQL query to calculate total time and tires_per_mold for each plan_id
$sql = "SELECT plan_id, SUM(TIMESTAMPDIFF(MINUTE, start_date, end_date)) AS total_time, SUM(tires_per_mold) AS total_tires_per_mold
        FROM plannew
        GROUP BY plan_id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $planId = $row["plan_id"];
        $totalTime = $row["total_time"];
        $totalTiresPerMold = $row["total_tires_per_mold"];

        // Calculate average time per tire
        $averageTimePerTire = ($totalTiresPerMold > 0) ? $totalTime / $totalTiresPerMold : 0;

        echo "Plan ID: $planId, Total Time: $totalTime minutes, Total Tires per Mold: $totalTiresPerMold, Average Time per Tire: $averageTimePerTire minutes per tire<br>";
    }
} else {
    echo "0 results";
}

$conn->close();

?>





