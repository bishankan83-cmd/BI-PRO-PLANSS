<?php
// ============================================================
// DATABASE CONFIGURATION
// Edit these values to match your server setup
// ============================================================


define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'planatir_task_managemen');         // Your MySQL username
define('DB_PASS', 'Bishan@1919');             // Your MySQL password
define('DB_NAME', 'planatir_task_managemen'); // Your database name

// ── Connect ──────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT
             . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ── Helpers ───────────────────────────────────────────────────
function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getInput(string $key, mixed $default = null): mixed {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    return $body[$key] ?? $_POST[$key] ?? $_GET[$key] ?? $default;
}
