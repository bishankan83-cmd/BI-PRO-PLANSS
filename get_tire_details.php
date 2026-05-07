<?php
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed.']));
}

$icode = $_GET['icode'] ?? '';

if ($icode) {
    $stmt = $conn->prepare("
        SELECT 
            icode, 
            Description AS description, 
            Brand AS brand, 
            CONCAT(Type, '-', Spec, '-', Colour, '-', Rim) AS size, 
            greenweight AS green_weight 
        FROM tire_details 
        WHERE icode = ?
    ");
    $stmt->bind_param("s", $icode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'No details found for the selected tire code.']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request.']);
}

$conn->close();
?>
