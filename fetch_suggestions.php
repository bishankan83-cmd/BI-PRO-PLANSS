<?php
// Database connection details
$servername = "localhost"; // Use your actual server name and port if necessary
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the type and query from the request
$type = isset($_GET['type']) ? $_GET['type'] : '';
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Validate the input
if (!in_array($type, ['icode', 'description', 'mold_id', 'mold_size', 'per_day'])) {
    die(json_encode([])); // Invalid type, return empty array
}

// Prepare the SQL statement based on the type
$sql = "";
switch ($type) {
    case 'icode':
        $sql = "SELECT DISTINCT icode FROM mold_list WHERE icode LIKE ? LIMIT 10";
        break;
    case 'description':
        $sql = "SELECT DISTINCT description FROM tire_details WHERE description LIKE ? LIMIT 10";
        break;
    case 'mold_id':
        $sql = "SELECT DISTINCT mold_id FROM mold_list WHERE mold_id LIKE ? LIMIT 10";
        break;
    case 'mold_size':
        $sql = "SELECT DISTINCT mold_size FROM mold_list WHERE mold_size LIKE ? LIMIT 10";
        break;
    case 'per_day':
        $sql = "SELECT DISTINCT per_day FROM mold_list WHERE per_day LIKE ? LIMIT 10";
        break;
}

// Prepare the statement
$stmt = $conn->prepare($sql);
$likeQuery = '%' . $query . '%'; // Use LIKE for partial matches
$stmt->bind_param('s', $likeQuery);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the results
$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row[$type]; // Store the value for the type requested
}

// Return suggestions as JSON
echo json_encode($suggestions);

// Close the connection
$stmt->close();
$conn->close();
?>
