<?php
// Database connection
$host = 'localhost';
$db = 'planatir_task_managemen';
$user = 'planatir_task_managemen';
$password = 'Bishan@1919';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get tireCode from request
    $tireCode = $_GET['tireCode'] ?? null;

    if (!$tireCode) {
        throw new Exception("Tire code is required");
    }

    // Fetch details from the tire_details table
    $stmt = $pdo->prepare("SELECT icode, brand, greenweight AS tireWeight
                          FROM tire_details 
                          WHERE icode = ?");
    $stmt->execute([$tireCode]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$details) {
        echo json_encode(['error' => 'No details found for the selected tire code.']);
    } else {
        echo json_encode($details);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>