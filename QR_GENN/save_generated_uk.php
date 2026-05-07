<?php
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$dbname     = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit();
}

// Read JSON payload
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body || !isset($body['records']) || !is_array($body['records'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit();
}

$records = $body['records'];
$today   = date('Y-m-d');

// Use session as the authoritative source for who is saving;
// fall back to the payload values only if session is somehow missing.
$created_by_id   = $_SESSION['user']      ?? ($body['created_by_id']   ?? '');
$created_by_name = $_SESSION['emp_name']  ?? ($body['created_by_name'] ?? 'Unknown');

$success_count = 0;
$fail_count    = 0;

$stmt = $conn->prepare(
    "INSERT INTO generated_serials_uk
       (serial_number, icode, brand, description, date, maxload, created_by_id, created_by_name)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

foreach ($records as $row) {
    $serial_number = trim($row['serial_number'] ?? '');
    $icode         = trim($row['tyre_code']      ?? $row['icode'] ?? '');
    $brand         = trim($row['brand']          ?? '');
    $description   = trim($row['description']    ?? '');
    $maxload       = trim($row['maxload']         ?? '');

    if (empty($serial_number) || empty($icode)) {
        $fail_count++;
        continue;
    }

    $stmt->bind_param(
        "ssssssss",
        $serial_number,
        $icode,
        $brand,
        $description,
        $today,
        $maxload,
        $created_by_id,
        $created_by_name
    );

    if ($stmt->execute()) {
        $success_count++;
    } else {
        $fail_count++;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'success'       => $success_count > 0,
    'saved'         => $success_count,
    'failed'        => $fail_count,
]);
?>