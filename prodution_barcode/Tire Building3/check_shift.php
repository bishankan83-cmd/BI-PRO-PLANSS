<?php
$host     = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['exists'=>false,'all'=>[],'error'=>'DB connection failed']);
    exit;
}

$date = isset($_GET['date']) ? trim($_GET['date']) : '';

if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['exists'=>false,'all'=>[]]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT shift FROM shifts WHERE log_date = :date ORDER BY id ASC");
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($rows) > 0) {
        echo json_encode(['exists'=>true,'shift'=>$rows[0],'all'=>$rows,'count'=>count($rows)]);
    } else {
        echo json_encode(['exists'=>false,'shift'=>null,'all'=>[],'count'=>0]);
    }
} catch (PDOException $e) {
    echo json_encode(['exists'=>false,'all'=>[],'error'=>'Query failed']);
}
?>