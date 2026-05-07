<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['tireCode'])) {
    echo json_encode(['error' => 'No tire code provided']);
    exit;
}

$tireCode = $conn->real_escape_string($_GET['tireCode']);
$query = "SELECT Brand as brand, greenweight as tireWeight FROM tire_details WHERE icode = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $tireCode);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Tire code not found']);
}

$stmt->close();
$conn->close();
?>