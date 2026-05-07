<?php
// Database connection
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

// Get the cavity name from the query parameter
$cavityName = isset($_GET['pressNumber']) ? $_GET['pressNumber'] : '';

// Prepare SQL with a WHERE clause if cavity name is provided
if (!empty($cavityName)) {
    // Get Icodes associated with this cavity name and join with tire_details for description
    $sql = "SELECT DISTINCT dp.Icode as code,
                   CONCAT(dp.Icode, ' (', td.description, ')') as description
            FROM daily_plan dp
            LEFT JOIN tire_details td ON dp.Icode = td.Icode
            WHERE dp.CavityName = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cavityName);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If no cavity name provided, get all Icodes (fallback)
    $sql = "SELECT DISTINCT dp.Icode as code,
                   CONCAT(dp.Icode, ' (', td.description, ')') as description
            FROM daily_plan dp
            LEFT JOIN tire_details td ON dp.Icode = td.Icode";
    $result = $conn->query($sql);
}

$tireCodes = array();

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tireCodes[] = array(
            'code' => $row['code'],
            'description' => $row['description']
        );
    }
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode(['tireCodes' => $tireCodes]);

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>