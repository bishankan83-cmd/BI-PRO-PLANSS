<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$dbname     = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

// Only accept POST with JSON body
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid or empty JSON payload.']);
    exit;
}

$inserted = 0;
$skipped  = 0;
$errors   = [];

$stmt = $conn->prepare("
    INSERT INTO generated_serials_uk
        (serial_number, icode, brand, description, date, maxload)
    VALUES (?, ?, ?, ?, CURDATE(), ?)
");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

foreach ($data as $row) {
    $serial      = trim($row['serial_number'] ?? '');
    $icode       = trim($row['tyre_code']     ?? '');
    $brand       = trim($row['brand']         ?? '');
    $description = trim($row['description']   ?? '');
    $maxload     = trim($row['maxload']        ?? '');

    if (empty($serial) || empty($icode)) {
        $skipped++;
        continue;
    }

    $stmt->bind_param("sssss", $serial, $icode, $brand, $description, $maxload);

    if ($stmt->execute()) {
        $inserted++;
    } else {
        $errors[] = "Failed for serial '$serial': " . $stmt->error;
        $skipped++;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'success'  => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'errors'   => $errors,
]);