<?php
ini_set('display_errors', 0);
error_reporting(0);

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$dbname     = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("DB error"); }

$filter    = $_GET['filter']    ?? 'all';
$search    = trim($_GET['search']    ?? '');
$brand_f   = trim($_GET['brand']     ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to']   ?? '');

$where_parts = ["1=1"];
$params = []; $types = '';

if ($filter === 'today') {
    $where_parts[] = "DATE(gs.generated_at) = CURDATE()";
} elseif ($filter === 'week') {
    $where_parts[] = "gs.generated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter === 'month') {
    $where_parts[] = "MONTH(gs.generated_at)=MONTH(NOW()) AND YEAR(gs.generated_at)=YEAR(NOW())";
}

if ($search !== '') {
    $where_parts[] = "(gs.serial_number LIKE ? OR gs.icode LIKE ? OR gs.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $types .= 'sss';
}
if ($brand_f !== '') { $where_parts[] = "gs.brand = ?"; $params[] = $brand_f; $types .= 's'; }
if ($date_from !== '') { $where_parts[] = "DATE(gs.generated_at) >= ?"; $params[] = $date_from; $types .= 's'; }
if ($date_to !== '') { $where_parts[] = "DATE(gs.generated_at) <= ?"; $params[] = $date_to; $types .= 's'; }

$where_sql = implode(' AND ', $where_parts);

$sql = "SELECT gs.id, gs.serial_number, gs.icode, gs.brand, gs.description, gs.maxload, gs.date, gs.generated_at
        FROM generated_serials_uk gs WHERE $where_sql ORDER BY gs.generated_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query($sql);
}

$filename = 'generated_labels_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');
// BOM for Excel UTF-8
fputs($out, "\xEF\xBB\xBF");

fputcsv($out, ['ID', 'Serial Number', 'Item Code', 'Brand', 'Description', 'Max Load (kg)', 'Date', 'Generated At']);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row['id'],
            $row['serial_number'],
            $row['icode'],
            $row['brand'] ?? '',
            $row['description'] ?? '',
            $row['maxload'] ?? '',
            $row['date'] ?? '',
            $row['generated_at'],
        ]);
    }
}

fclose($out);
$conn->close();
exit;