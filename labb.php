<?php
// Database connection details
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Fetch records
function fetchCompounds($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM bcompound255");
        $compounds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $compounds;
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

$compounds = fetchCompounds($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Compound Details</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Compound Details</h2>

<table>
    <tr>
        <th>IID</th>
        <th>ID</th>
        <th>Input Date</th>
        <th>Shift</th>
        <th>Compound Name</th>
        <th>Description</th>
        <th>CStock</th>
        <th>Batch</th>
        <th>Pallet</th>
        <th>Created At</th>
        <th>Weight</th>
        <th>Serial Number</th>
    </tr>
    <?php foreach ($compounds as $compound): ?>
    <tr>
        <td><?php echo htmlspecialchars($compound['iid']); ?></td>
        <td><?php echo htmlspecialchars($compound['id']); ?></td>
        <td><?php echo htmlspecialchars($compound['inputDate']); ?></td>
        <td><?php echo htmlspecialchars($compound['shift']); ?></td>
        <td><?php echo htmlspecialchars($compound['compound_name']); ?></td>
        <td><?php echo htmlspecialchars($compound['description']); ?></td>
        <td><?php echo htmlspecialchars($compound['cstock']); ?></td>
        <td><?php echo htmlspecialchars($compound['batch']); ?></td>
        <td><?php echo htmlspecialchars($compound['pallet']); ?></td>
        <td><?php echo htmlspecialchars($compound['created_at']); ?></td>
        <td><?php echo htmlspecialchars($compound['weight']); ?></td>
        <td><?php echo htmlspecialchars($compound['serial_number']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
