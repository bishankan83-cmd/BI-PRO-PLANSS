<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'planatir_task_managemen';
$user = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create database connection
$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rmCode = $_POST['RM_code'] ?? '';
    if (!empty($rmCode)) {
        $stmt = $conn->prepare("SELECT band_size FROM rm_band_data WHERE RM_code = ?");
        $stmt->bind_param('s', $rmCode);
        $stmt->execute();
        $stmt->bind_result($bandSize);
        if ($stmt->fetch()) {
            echo json_encode(['success' => true, 'band_size' => $bandSize]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Band size not found']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid RM code']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
