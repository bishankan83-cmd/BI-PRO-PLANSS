<?php
$host     = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $logDate = isset($_POST['logDate']) ? trim($_POST['logDate']) : '';
    $shift   = isset($_POST['shift'])   ? trim($_POST['shift'])   : '';

    if (empty($logDate) || empty($shift)) {
        die("Log date and shift are required.");
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $logDate)) {
        die("Invalid date format.");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO shifts (log_date, shift) VALUES (:logDate, :shift)");
        $stmt->bindParam(':logDate', $logDate);
        $stmt->bindParam(':shift',   $shift);

        if ($stmt->execute()) {
            header("Location: about1.php");
            exit;
        } else {
            die("Error saving shift data.");
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>