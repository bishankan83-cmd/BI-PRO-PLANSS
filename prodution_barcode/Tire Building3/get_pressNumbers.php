<?php
// Assuming you have a database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";
    
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to get distinct cavity names from daily_plan table
$sql = "SELECT DISTINCT CavityName FROM daily_plan WHERE CavityName IS NOT NULL ORDER BY CavityName";
$result = $conn->query($sql);

$pressNumbers = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pressNumbers[] = array(
            'pressNumber' => $row['CavityName']
        );
    }
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode(['pressNumbers' => $pressNumbers]);

$conn->close();
?>