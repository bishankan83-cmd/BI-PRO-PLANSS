<?php
// Database configuration
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

if (isset($_GET['tireCode'])) {
    $tireCode = $_GET['tireCode'];

    // Updated query to fetch data from tire_details and get CavityName as pressNumber from daily_plan
    $sql = "SELECT 
                t.Brand AS brand, 
                t.greenweight AS tireWeight, 
                d.CavityName AS pressNumber 
            FROM tire_details t
            LEFT JOIN daily_plan d ON t.icode = d.Icode
            WHERE t.icode = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tireCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "No details found for the provided tire code."]);
    }

    $stmt->close();
}

$conn->close();
?>
