<?php
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    die("No order ID provided.");
}

$host = 'localhost';
$dbname = 'planatir_cms';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM tire_orders WHERE order_id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found.");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Discount - Order #<?= htmlspecialchars($id) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 480px;
            width: 100%;
        }
        .order-id {
            color: #888;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .status-pending {
            background-color: #fff8e1;
            border: 1px solid #ffc107;
            color: #856404;
        }
        .status-success {
            background-color: #e8f5e9;
            border: 1px solid #4caf50;
            color: #2e7d32;
        }
        .status-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .status-message {
            font-size: 16px;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 32px;
            background-color: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #1565c0;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
<div class="card">

    <a href="account-manager-dashboard.php" class="btn-back">← Back</a>

    <h2>Discount Processing</h2>
    <div class="order-id">Order ID: <strong><?= htmlspecialchars($order['order_id']) ?></strong></div>

    <?php if ($order['status'] === 'Manager_confirm_disc'): ?>
        <div class="status-box status-pending">
            <div class="status-icon">⏳</div>
            <div class="status-message">The marketing manager has not yet confirmed this order.</div>
        </div>

    <?php elseif ($order['status'] === 'Manager_confirm_disc_success'): ?>
        <div class="status-box status-success">
            <div class="status-icon">✅</div>
            <div class="status-message">The marketing manager has confirmed the discount.</div>
        </div>
        <a href="gate_order.php?id=<?= urlencode($order['order_id']) ?>" class="btn">
            Proceed to Order Gate
        </a>

    <?php else: ?>
        <div class="status-box" style="background:#f5f5f5; border:1px solid #ccc; color:#555;">
            <div class="status-icon">ℹ️</div>
            <div class="status-message">Status: <?= htmlspecialchars($order['status']) ?></div>
        </div>
    <?php endif; ?>

</div>
</body>
</html>