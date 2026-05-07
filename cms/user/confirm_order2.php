<?php
// Start session for user authentication
session_start();

// Database Configuration
define('DB_SERVER', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_cms');

// Check if user is logged in
if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header('location:index2.php');
    exit;
}

// Check if order_id exists
if (!isset($_GET['order_id']) || empty(trim($_GET['order_id']))) {
    header('location:dashboard.php');
    exit;
}

$userId = $_SESSION['id'];
$orderId = trim($_GET['order_id']);
$message = '';
$messageType = 'info';

// Establish database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT fullName, userEmail FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
    if (!$userData) {
        header('location:index2.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// Helper functions
function getPrimaryKeyColumn($pdo, $tableName) {
    try {
        $stmt = $pdo->query("SHOW KEYS FROM `{$tableName}` WHERE Key_name = 'PRIMARY'");
        $result = $stmt->fetch();
        return $result ? $result['Column_name'] : 'id';
    } catch (PDOException $e) {
        return 'id';
    }
}

function columnExists($pdo, $tableName, $columnName) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$columnName}'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function tableExists($pdo, $tableName) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            $pdo->beginTransaction();
            
            $orderPkColumn = getPrimaryKeyColumn($pdo, 'tire_orders');
            $itemPkColumn = getPrimaryKeyColumn($pdo, 'tire_order_items');
            
            $stmt = $pdo->query("SHOW COLUMNS FROM tire_order_items");
            $itemColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $itemOrderLinkColumn = in_array('order_id', $itemColumns) ? 'order_id' : 
                                   (in_array('tire_order_id', $itemColumns) ? 'tire_order_id' : 'order_ref');
            
            switch ($_POST['action']) {
                case 'save_comment':
                    $comment = trim($_POST['customer_comment'] ?? '');
                    
                    $updateFields = [];
                    $updateValues = [];
                    
                    if (columnExists($pdo, 'tire_orders', 'customer_comment')) {
                        $updateFields[] = 'customer_comment = ?';
                        $updateValues[] = $comment;
                    }
                    
                    if (columnExists($pdo, 'tire_orders', 'updated_at')) {
                        $updateFields[] = 'updated_at = ?';
                        $updateValues[] = date('Y-m-d H:i:s');
                    }
                    
                    if (!empty($updateFields)) {
                        $updateValues[] = $orderId;
                        $sql = "UPDATE tire_orders SET " . implode(', ', $updateFields) . " WHERE {$orderPkColumn} = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($updateValues);
                    }
                    
                    $pdo->commit();
                    $message = "✅ Your comment has been saved successfully!";
                    $messageType = 'success';
                    break;

                case 'add_items':
                    $newItems = json_decode($_POST['new_items_data'] ?? '', true);
                    
                    if (!empty($newItems)) {
                        foreach ($newItems as $item) {
                            if (!isset($item['id'], $item['icode'], $item['quantity']) || $item['quantity'] <= 0) {
                                throw new Exception("Invalid order item data");
                            }
                            
                            $insertFields = [$itemOrderLinkColumn, 'product_id', 'icode', 'quantity'];
                            $insertValues = [$orderId, $item['id'], $item['icode'], (int)$item['quantity']];
                            $placeholders = ['?', '?', '?', '?'];
                            
                            $sql = "INSERT INTO tire_order_items (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($insertValues);
                        }
                        
                        // Update order totals
                        $stmt = $pdo->prepare("
                            SELECT COUNT(*) as total_items, SUM(oi.quantity) as total_quantity
                            FROM tire_order_items oi
                            WHERE oi.{$itemOrderLinkColumn} = ?
                        ");
                        $stmt->execute([$orderId]);
                        $totals = $stmt->fetch();
                        
                        if (columnExists($pdo, 'tire_orders', 'total_items') && columnExists($pdo, 'tire_orders', 'total_quantity')) {
                            $stmt = $pdo->prepare("UPDATE tire_orders SET total_items = ?, total_quantity = ?, updated_at = ? WHERE {$orderPkColumn} = ?");
                            $stmt->execute([$totals['total_items'], $totals['total_quantity'], date('Y-m-d H:i:s'), $orderId]);
                        }
                        
                        $pdo->commit();
                        $message = "✅ New items added successfully!";
                        $messageType = 'success';
                    }
                    break;
                    
                case 'update_order':
                    $updatedItems = json_decode($_POST['order_data'] ?? '', true);
                    
                    if (!empty($updatedItems)) {
                        // Delete existing items
                        $stmt = $pdo->prepare("DELETE FROM tire_order_items WHERE {$itemOrderLinkColumn} = ?");
                        $stmt->execute([$orderId]);
                        
                        // Insert updated items
                        foreach ($updatedItems as $item) {
                            if (!isset($item['id'], $item['icode'], $item['quantity']) || $item['quantity'] <= 0) {
                                throw new Exception("Invalid order item data");
                            }
                            
                            $stmt = $pdo->prepare("INSERT INTO tire_order_items ({$itemOrderLinkColumn}, product_id, icode, quantity) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$orderId, $item['id'], $item['icode'], (int)$item['quantity']]);
                        }
                        
                        // Update totals
                        if (columnExists($pdo, 'tire_orders', 'total_items')) {
                            $stmt = $pdo->prepare("UPDATE tire_orders SET total_items = ?, total_quantity = ?, updated_at = ? WHERE {$orderPkColumn} = ?");
                            $stmt->execute([count($updatedItems), array_sum(array_column($updatedItems, 'quantity')), date('Y-m-d H:i:s'), $orderId]);
                        }
                        
                        $pdo->commit();
                        $message = "✅ Order updated successfully!";
                        $messageType = 'success';
                    }
                    break;
                    
                case 'confirm_order':
                    if (columnExists($pdo, 'tire_orders', 'status')) {
                        $stmt = $pdo->prepare("UPDATE tire_orders SET status = ?, confirmed_at = ?, updated_at = ? WHERE {$orderPkColumn} = ?");
                        $stmt->execute(['cus_confirmed', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $orderId]);
                    }
                    
                    $pdo->commit();
                    $_SESSION['order_confirmed'] = true;
                    header('Location: sent_mail2.php?order_id=' . urlencode($orderId));
                    exit;
                    break;
                    
                case 'cancel_order':
                    $stmt = $pdo->prepare("DELETE FROM tire_order_items WHERE {$itemOrderLinkColumn} = ?");
                    $stmt->execute([$orderId]);
                    
                    $stmt = $pdo->prepare("DELETE FROM tire_orders WHERE {$orderPkColumn} = ?");
                    $stmt->execute([$orderId]);
                    
                    $pdo->commit();
                    $_SESSION['order_cancelled'] = true;
                    header('Location: dashboard.php');
                    exit;
                    break;
            }
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "❌ Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch order details
$orderData = null;
$orderItems = [];
$availableInventory = [];

try {
    $orderPkColumn = getPrimaryKeyColumn($pdo, 'tire_orders');
    $customerIdColumn = columnExists($pdo, 'tire_orders', 'customer_id') ? 'customer_id' : 'user_id';
    
    $stmt = $pdo->prepare("
        SELECT o.*, u.fullName, u.userEmail 
        FROM tire_orders o 
        JOIN users u ON o.{$customerIdColumn} = u.id 
        WHERE o.{$orderPkColumn} = ? AND o.{$customerIdColumn} = ?
    ");
    $stmt->execute([$orderId, $userId]);
    $orderData = $stmt->fetch();
    
    if (!$orderData) {
        $_SESSION['error_message'] = "Order not found";
        header('location:dashboard.php');
        exit;
    }
    
    $itemPkColumn = getPrimaryKeyColumn($pdo, 'tire_order_items');
    $itemOrderLinkColumn = columnExists($pdo, 'tire_order_items', 'order_id') ? 'order_id' : 
                           (columnExists($pdo, 'tire_order_items', 'tire_order_id') ? 'tire_order_id' : 'order_ref');
    
    $stmt = $pdo->prepare("
        SELECT 
            oi.{$itemPkColumn} as order_item_id,
            oi.product_id, oi.icode, oi.quantity,
            r.t_size, r.brand, r.col, r.rim,
            t.fweight
        FROM tire_order_items oi
        LEFT JOIN realstock r ON oi.product_id = r.id
        LEFT JOIN tire_details t ON oi.icode = t.icode
        WHERE oi.{$itemOrderLinkColumn} = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
    
    $stmt = $pdo->query("
        SELECT r.id, r.icode, r.t_size, r.brand, r.col, r.rim, t.fweight 
        FROM realstock r 
        LEFT JOIN tire_details t ON r.icode = t.icode 
        ORDER BY r.brand, r.t_size
    ");
    $availableInventory = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $message = "Error loading order: " . $e->getMessage();
    $messageType = 'error';
}

$totalItems = count($orderItems);
$totalQuantity = array_sum(array_column($orderItems, 'quantity'));
$totalWeight = 0;
foreach ($orderItems as $item) {
    $totalWeight += ($item['quantity'] * (float)($item['fweight'] ?? 0));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Order #<?php echo htmlspecialchars($orderId); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #F28018;
            --primary-dark: #d66f14;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #3498db;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --white: #ffffff;
            --border: #dfe6e9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: var(--white);
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .back-btn:hover {
            background: var(--white);
            color: var(--primary);
        }

        .message {
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid var(--success);
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid var(--danger);
        }

        .content { padding: 40px 30px; }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.confirmed, .status-badge.cus_confirmed {
            background: #d4edda;
            color: #155724;
        }

        .order-info-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .info-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary);
        }

        .items-table-wrapper {
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }

        .items-table thead {
            background: linear-gradient(135deg, var(--dark) 0%, #34495e 100%);
            color: var(--white);
        }

        .items-table th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
        }

        .items-table tbody tr:hover {
            background: #fff8f0;
        }

        .qty-display {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--dark);
        }

        .qty-edit-container {
            display: none;
            align-items: center;
            gap: 5px;
        }

        .qty-edit-container.active {
            display: flex;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: var(--primary);
            color: var(--white);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .qty-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }

        .qty-input-edit {
            width: 70px;
            text-align: center;
            border: 2px solid var(--primary);
            border-radius: 8px;
            padding: 8px 5px;
            font-weight: 600;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-success { background: var(--success); color: var(--white); }
        .btn-danger { background: var(--danger); color: var(--white); }
        .btn-warning { background: var(--warning); color: var(--white); }
        .btn-info { background: var(--info); color: var(--white); }
        .btn-secondary { background: #95a5a6; color: var(--white); }
        .btn-lg { padding: 18px 35px; font-size: 1.2rem; }

        .action-section {
            background: var(--light);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .action-section h3 {
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .final-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            padding: 30px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
        }

        .edit-mode-banner, .add-items-mode-banner {
            color: var(--white);
            padding: 15px 30px;
            text-align: center;
            font-weight: 600;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .edit-mode-banner {
            background: linear-gradient(135deg, var(--info) 0%, #2980b9 100%);
        }

        .add-items-mode-banner {
            background: linear-gradient(135deg, var(--success) 0%, #229954 100%);
        }

        .edit-mode-banner.active, .add-items-mode-banner.active {
            display: flex;
        }

        .add-items-section {
            display: none;
            background: #e8f5e9;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .add-items-section.active {
            display: block;
        }

        .add-items-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }

        .add-items-table thead {
            background: linear-gradient(135deg, var(--success) 0%, #229954 100%);
            color: var(--white);
        }

        .add-items-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .add-items-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }

        .add-items-table tbody tr:hover {
            background: #e8f5e9;
        }

        .add-items-table tbody tr.selected {
            background: #c8e6c9;
        }

        .qty-container-add {
            display: flex;
            align-items: center;
            gap: 5px;
            max-width: 150px;
        }

        .qty-input-add {
            width: 60px;
            text-align: center;
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 8px 5px;
            font-weight: 600;
        }

        .qty-input-add.has-value {
            border-color: var(--success);
            background: #d4edda;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 20px;
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 25px 30px;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 2px solid var(--border);
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .remove-item-btn {
            background: var(--danger);
            color: var(--white);
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .remove-item-btn:hover {
            background: #c0392b;
        }

        .totals-summary {
            background: linear-gradient(135deg, var(--success) 0%, #229954 100%);
            color: var(--white);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .total-item {
            text-align: center;
        }

        .total-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .total-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .add-items-filter {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .add-items-filter input, .add-items-filter select {
            padding: 10px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
        }

        /* Comment Display Section */
        .comment-display-section {
            background: linear-gradient(135deg, #fff9e6 0%, #ffe6b3 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            border-left: 5px solid var(--warning);
        }

        .comment-display-section h3 {
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .comment-content {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            min-height: 60px;
            line-height: 1.6;
            color: var(--dark);
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .comment-empty {
            color: #999;
            font-style: italic;
        }

        .comment-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        /* Comment Edit Section */
        .comment-edit-section {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: none;
        }

        .comment-edit-section.active {
            display: block;
        }

        .comment-edit-section h3 {
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .comment-edit-section textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }

        .char-count {
            text-align: right;
            margin-top: 5px;
            font-size: 0.9rem;
            color: #666;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.3rem;
            }
            
            .order-info-grid, .action-buttons, .final-actions {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-clipboard-check"></i>
                Review Order #<?php echo htmlspecialchars($orderId); ?>
            </h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <div class="edit-mode-banner" id="editModeBanner">
            <i class="fas fa-edit"></i>
            <span>Edit Mode Active</span>
        </div>

        <div class="add-items-mode-banner" id="addItemsModeBanner">
            <i class="fas fa-plus-circle"></i>
            <span>Add Items Mode Active</span>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="content">
            <div class="order-info-section">
                <h2 style="color: var(--dark); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-info-circle"></i>
                    Order Information
                    <span class="status-badge <?php echo strtolower($orderData['status'] ?? 'pending'); ?>">
                        <?php echo htmlspecialchars($orderData['status'] ?? 'Pending'); ?>
                    </span>
                </h2>
                
                <div class="order-info-grid">
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-hashtag"></i> Order ID</div>
                        <div class="info-value">#<?php echo htmlspecialchars($orderId); ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-user"></i> Customer</div>
                        <div class="info-value" style="font-size: 1.2rem;">
                            <?php echo htmlspecialchars($orderData['fullName']); ?>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-label"><i class="fas fa-calendar"></i> Order Date</div>
                        <div class="info-value" style="font-size: 1rem;">
                            <?php echo date('M d, Y', strtotime($orderData['created_at'] ?? 'now')); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="totals-summary">
                <h3 style="color: var(--white); margin-bottom: 20px;">
                    <i class="fas fa-calculator"></i>
                    Order Summary
                </h3>
                <div class="totals-grid">
                    <div class="total-item">
                        <div class="total-label">Total Items</div>
                        <div class="total-value" id="displayTotalItems"><?php echo $totalItems; ?></div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Total Quantity</div>
                        <div class="total-value" id="displayTotalQuantity"><?php echo $totalQuantity; ?></div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Total Weight</div>
                        <div class="total-value" id="displayTotalWeight"><?php echo number_format($totalWeight, 2); ?> kg</div>
                    </div>
                </div>
            </div>

            <h2 class="section-title">
                <i class="fas fa-box-open"></i>
                Order Items
            </h2>

            <div class="items-table-wrapper">
                <table class="items-table" id="orderItemsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Size</th>
                            <th>Brand</th>
                            <th>Color</th>
                            <th>Rim</th>
                            <th>Weight (kg)</th>
                            <th>Quantity</th>
                            <th>Subtotal (kg)</th>
                            <th class="edit-column" style="display: none;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNum = 1; ?>
                        <?php foreach ($orderItems as $item): ?>
                            <tr data-product-id="<?php echo $item['product_id']; ?>"
                                data-icode="<?php echo htmlspecialchars($item['icode'] ?? ''); ?>"
                                data-size="<?php echo htmlspecialchars($item['t_size'] ?? ''); ?>"
                                data-brand="<?php echo htmlspecialchars($item['brand'] ?? ''); ?>"
                                data-color="<?php echo htmlspecialchars($item['col'] ?? ''); ?>"
                                data-rim="<?php echo htmlspecialchars($item['rim'] ?? ''); ?>"
                                data-fweight="<?php echo (float)($item['fweight'] ?? 0); ?>"
                                data-quantity="<?php echo $item['quantity']; ?>">
                                <td><?php echo $rowNum++; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['icode'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['t_size'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['col'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['rim'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format((float)($item['fweight'] ?? 0), 2); ?></td>
                                <td>
                                    <span class="qty-display"><?php echo $item['quantity']; ?></span>
                                    <div class="qty-edit-container">
                                        <button type="button" class="qty-btn" onclick="decrementQty(this)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="qty-input-edit" min="1" 
                                               value="<?php echo $item['quantity']; ?>"
                                               onchange="updateSubtotal(this)">
                                        <button type="button" class="qty-btn" onclick="incrementQty(this)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="item-subtotal">
                                    <?php echo number_format($item['quantity'] * (float)($item['fweight'] ?? 0), 2); ?> kg
                                </td>
                                <td class="edit-column" style="display: none;">
                                    <button type="button" class="remove-item-btn" onclick="removeItem(this)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="add-items-section" id="addItemsSection">
                <h3>Available Items to Add</h3>

                <div class="add-items-filter">
                    <input type="text" id="searchItems" placeholder="Search..." onkeyup="filterItems()">
                    <select id="brandFilter" onchange="filterItems()">
                        <option value="">All Brands</option>
                        <?php 
                        $brands = array_unique(array_column($availableInventory, 'brand'));
                        sort($brands);
                        foreach ($brands as $brand): 
                            if (!empty($brand)):
                        ?>
                            <option value="<?php echo htmlspecialchars($brand); ?>">
                                <?php echo htmlspecialchars($brand); ?>
                            </option>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </select>
                </div>

                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="add-items-table" id="addItemsTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Size</th>
                                <th>Brand</th>
                                <th>Weight (kg)</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableInventory as $item): ?>
                                <tr data-product-id="<?php echo $item['id']; ?>"
                                    data-icode="<?php echo htmlspecialchars($item['icode'] ?? ''); ?>"
                                    data-size="<?php echo htmlspecialchars($item['t_size'] ?? ''); ?>"
                                    data-brand="<?php echo htmlspecialchars($item['brand'] ?? ''); ?>"
                                    data-color="<?php echo htmlspecialchars($item['col'] ?? ''); ?>"
                                    data-rim="<?php echo htmlspecialchars($item['rim'] ?? ''); ?>"
                                    data-fweight="<?php echo (float)($item['fweight'] ?? 0); ?>">
                                    <td><strong><?php echo htmlspecialchars($item['icode'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['t_size'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['brand'] ?? 'N/A'); ?></td>
                                    <td><?php echo number_format((float)($item['fweight'] ?? 0), 2); ?></td>
                                    <td>
                                        <div class="qty-container-add">
                                            <button type="button" class="qty-btn" onclick="decrementAddQty(this)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="qty-input-add" min="0" value="0"
                                                   onchange="updateAddSelection(this)">
                                            <button type="button" class="qty-btn" onclick="incrementAddQty(this)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: flex-end;">
                    <button type="button" class="btn btn-success" onclick="saveAddedItems()">
                        <i class="fas fa-check"></i> Add Selected
                    </button>
                    <button type="button" class="btn btn-danger" onclick="cancelAddItems()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>

            <div class="action-section">
                <h3><i class="fas fa-tools"></i> Actions</h3>
                <div class="action-buttons">
                    <button type="button" class="btn btn-success" onclick="enableAddItems()">
                        <i class="fas fa-plus-circle"></i> Add Items
                    </button>
                    <button type="button" class="btn btn-info" onclick="enableEdit()">
                        <i class="fas fa-edit"></i> Edit Order
                    </button>
                </div>
            </div>

            <div class="action-section" id="editActions" style="display: none; background: #e3f2fd;">
                <h3><i class="fas fa-save"></i> Save Changes</h3>
                <div class="action-buttons">
                    <button type="button" class="btn btn-success" onclick="saveChanges()">
                        <i class="fas fa-check"></i> Save
                    </button>
                    <button type="button" class="btn btn-danger" onclick="cancelEdit()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>

            <!-- Comment Display Section -->
            <div class="comment-display-section" id="commentDisplay">
                <h3>
                    <i class="fas fa-comment-dots"></i>
                    Your Comment
                </h3>
                <div class="comment-content" id="commentContent">
                    <?php 
                    $customerComment = $orderData['customer_comment'] ?? '';
                    if (!empty($customerComment)): 
                        echo htmlspecialchars($customerComment);
                    else:
                        echo '<span class="comment-empty">No comment added yet</span>';
                    endif;
                    ?>
                </div>
                <div class="comment-actions">
                    <button type="button" class="btn btn-warning" onclick="editComment()">
                        <i class="fas fa-edit"></i> <?php echo !empty($customerComment) ? 'Edit' : 'Add'; ?> Comment
                    </button>
                </div>
            </div>

            <!-- Comment Edit Section -->
            <div class="comment-edit-section" id="commentEdit">
                <h3>
                    <i class="fas fa-pencil-alt"></i>
                    Edit Your Comment
                </h3>
                <form method="POST" id="commentForm">
                    <input type="hidden" name="action" value="save_comment">
                    <textarea id="customerComment" name="customer_comment" 
                              placeholder="Enter your comment or special instructions..." 
                              maxlength="1000" oninput="updateCharCount()"><?php echo htmlspecialchars($orderData['customer_comment'] ?? ''); ?></textarea>
                    <div class="char-count">
                        <span id="charCount">0</span> / 1000 characters
                    </div>
                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Comment
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelCommentEdit()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="clearComment()">
                            <i class="fas fa-eraser"></i> Clear
                        </button>
                    </div>
                </form>
            </div>

            <div class="final-actions">
                <button type="button" class="btn btn-success btn-lg" onclick="confirmOrder()">
                    <i class="fas fa-check-circle"></i> Confirm Order
                </button>
                <button type="button" class="btn btn-danger btn-lg" onclick="cancelOrder()">
                    <i class="fas fa-times-circle"></i> Cancel Order
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--success) 0%, #229954 100%);">
                <h2><i class="fas fa-check-circle"></i> Confirm Order</h2>
            </div>
            <div class="modal-body">
                <p><strong>Confirm this order?</strong></p>
                <p>This order will be sent to fulfillment.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('confirmModal')">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="confirm_order">
                    <button type="submit" class="btn btn-success">Confirm</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal-overlay" id="cancelModal">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);">
                <h2><i class="fas fa-exclamation-triangle"></i> Cancel Order</h2>
            </div>
            <div class="modal-body">
                <p><strong>Cancel this order?</strong></p>
                <p style="color: var(--danger);">⚠️ This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('cancelModal')">No</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="cancel_order">
                    <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let isEditMode = false;
        let isAddMode = false;
        let originalData = [];
        let newItems = new Map();

        document.addEventListener('DOMContentLoaded', function() {
            saveOriginal();
            updateTotals();
            updateCharCount();
        });

        function saveOriginal() {
            const rows = document.querySelectorAll('#orderItemsTable tbody tr');
            originalData = Array.from(rows).map(row => ({
                productId: row.dataset.productId,
                icode: row.dataset.icode,
                size: row.dataset.size,
                brand: row.dataset.brand,
                color: row.dataset.color,
                rim: row.dataset.rim,
                fweight: parseFloat(row.dataset.fweight),
                quantity: parseInt(row.dataset.quantity)
            }));
        }

        function enableAddItems() {
            if (isAddMode) return;
            if (isEditMode) cancelEdit();
            
            isAddMode = true;
            document.getElementById('addItemsModeBanner').classList.add('active');
            document.getElementById('addItemsSection').classList.add('active');
            document.getElementById('addItemsSection').scrollIntoView({ behavior: 'smooth' });
        }

        function cancelAddItems() {
            if (!confirm('Discard selected items?')) return;
            
            isAddMode = false;
            document.getElementById('addItemsModeBanner').classList.remove('active');
            document.getElementById('addItemsSection').classList.remove('active');
            
            document.querySelectorAll('.qty-input-add').forEach(input => {
                input.value = 0;
                input.classList.remove('has-value');
                input.closest('tr').classList.remove('selected');
            });
            
            newItems.clear();
        }

        function incrementAddQty(btn) {
            const input = btn.parentElement.querySelector('.qty-input-add');
            input.value = parseInt(input.value || 0) + 1;
            updateAddSelection(input);
        }

        function decrementAddQty(btn) {
            const input = btn.parentElement.querySelector('.qty-input-add');
            input.value = Math.max(0, parseInt(input.value || 0) - 1);
            updateAddSelection(input);
        }

        function updateAddSelection(input) {
            const qty = parseInt(input.value) || 0;
            const row = input.closest('tr');
            
            if (qty > 0) {
                input.classList.add('has-value');
                row.classList.add('selected');
                newItems.set(row.dataset.productId, {
                    id: row.dataset.productId,
                    icode: row.dataset.icode,
                    size: row.dataset.size,
                    brand: row.dataset.brand,
                    color: row.dataset.color,
                    rim: row.dataset.rim,
                    fweight: parseFloat(row.dataset.fweight) || 0,
                    quantity: qty
                });
            } else {
                input.classList.remove('has-value');
                row.classList.remove('selected');
                newItems.delete(row.dataset.productId);
            }
        }

        function saveAddedItems() {
            if (newItems.size === 0) {
                alert('Select items to add');
                return;
            }
            
            const items = Array.from(newItems.values());
            if (confirm(`Add ${items.length} items?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="add_items">
                    <input type="hidden" name="new_items_data" value='${JSON.stringify(items)}'>
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function filterItems() {
            const search = document.getElementById('searchItems').value.toLowerCase();
            const brand = document.getElementById('brandFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#addItemsTable tbody tr');
            
            rows.forEach(row => {
                const text = (row.dataset.icode + row.dataset.size + row.dataset.brand).toLowerCase();
                const matchSearch = search === '' || text.includes(search);
                const matchBrand = brand === '' || row.dataset.brand.toLowerCase() === brand;
                row.style.display = (matchSearch && matchBrand) ? '' : 'none';
            });
        }

        function enableEdit() {
            if (isEditMode) return;
            if (isAddMode) cancelAddItems();
            
            isEditMode = true;
            document.getElementById('editModeBanner').classList.add('active');
            document.getElementById('editActions').style.display = 'block';
            
            document.querySelectorAll('.qty-display').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.qty-edit-container').forEach(el => el.classList.add('active'));
            document.querySelectorAll('.edit-column').forEach(el => el.style.display = 'table-cell');
        }

        function cancelEdit() {
            if (!confirm('Discard changes?')) return;
            
            isEditMode = false;
            document.getElementById('editModeBanner').classList.remove('active');
            document.getElementById('editActions').style.display = 'none';
            
            document.querySelectorAll('.qty-display').forEach(el => el.style.display = 'inline');
            document.querySelectorAll('.qty-edit-container').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.edit-column').forEach(el => el.style.display = 'none');
            
            restoreOriginal();
            updateTotals();
        }

        function restoreOriginal() {
            const tbody = document.querySelector('#orderItemsTable tbody');
            tbody.innerHTML = '';
            
            originalData.forEach((item, idx) => {
                const row = document.createElement('tr');
                Object.keys(item).forEach(key => row.dataset[key] = item[key]);
                
                const subtotal = (item.quantity * item.fweight).toFixed(2);
                row.innerHTML = `
                    <td>${idx + 1}</td>
                    <td><strong>${item.icode}</strong></td>
                    <td>${item.size}</td>
                    <td>${item.brand}</td>
                    <td>${item.color}</td>
                    <td>${item.rim}</td>
                    <td>${item.fweight.toFixed(2)}</td>
                    <td>
                        <span class="qty-display">${item.quantity}</span>
                        <div class="qty-edit-container">
                            <button type="button" class="qty-btn" onclick="decrementQty(this)"><i class="fas fa-minus"></i></button>
                            <input type="number" class="qty-input-edit" min="1" value="${item.quantity}" onchange="updateSubtotal(this)">
                            <button type="button" class="qty-btn" onclick="incrementQty(this)"><i class="fas fa-plus"></i></button>
                        </div>
                    </td>
                    <td class="item-subtotal">${subtotal} kg</td>
                    <td class="edit-column" style="display: none;">
                        <button type="button" class="remove-item-btn" onclick="removeItem(this)"><i class="fas fa-trash"></i> Remove</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function incrementQty(btn) {
            const input = btn.parentElement.querySelector('.qty-input-edit');
            input.value = parseInt(input.value || 1) + 1;
            updateSubtotal(input);
        }

        function decrementQty(btn) {
            const input = btn.parentElement.querySelector('.qty-input-edit');
            input.value = Math.max(1, parseInt(input.value || 1) - 1);
            updateSubtotal(input);
        }

        function updateSubtotal(input) {
            const row = input.closest('tr');
            const qty = parseInt(input.value) || 1;
            const weight = parseFloat(row.dataset.fweight) || 0;
            row.querySelector('.item-subtotal').textContent = (qty * weight).toFixed(2) + ' kg';
            updateTotals();
        }

        function removeItem(btn) {
            if (!confirm('Remove this item?')) return;
            btn.closest('tr').remove();
            
            const rows = document.querySelectorAll('#orderItemsTable tbody tr');
            rows.forEach((row, idx) => row.querySelector('td:first-child').textContent = idx + 1);
            
            updateTotals();
            if (rows.length === 0) {
                alert('All items removed. Order will be cancelled.');
                window.location.href = 'dashboard.php';
            }
        }

        function updateTotals() {
            const rows = document.querySelectorAll('#orderItemsTable tbody tr');
            let totalItems = rows.length;
            let totalQty = 0;
            let totalWeight = 0;
            
            rows.forEach(row => {
                const input = row.querySelector('.qty-input-edit');
                const qty = input ? parseInt(input.value) || 0 : parseInt(row.dataset.quantity) || 0;
                const weight = parseFloat(row.dataset.fweight) || 0;
                totalQty += qty;
                totalWeight += (qty * weight);
            });
            
            document.getElementById('displayTotalItems').textContent = totalItems;
            document.getElementById('displayTotalQuantity').textContent = totalQty;
            document.getElementById('displayTotalWeight').textContent = totalWeight.toFixed(2);
        }

        function saveChanges() {
            const rows = document.querySelectorAll('#orderItemsTable tbody tr');
            if (rows.length === 0) {
                alert('No items in order');
                return;
            }
            
            const items = Array.from(rows).map(row => {
                const qty = parseInt(row.querySelector('.qty-input-edit').value) || 0;
                if (qty < 1) throw new Error('Invalid quantity');
                
                return {
                    id: row.dataset.productId,
                    icode: row.dataset.icode,
                    size: row.dataset.size,
                    brand: row.dataset.brand,
                    color: row.dataset.color,
                    rim: row.dataset.rim,
                    fweight: parseFloat(row.dataset.fweight),
                    quantity: qty
                };
            });
            
            if (confirm('Save changes?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_order">
                    <input type="hidden" name="order_data" value='${JSON.stringify(items)}'>
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function confirmOrder() {
            document.getElementById('confirmModal').classList.add('active');
        }

        function cancelOrder() {
            document.getElementById('cancelModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Comment functions
        function editComment() {
            document.getElementById('commentDisplay').style.display = 'none';
            document.getElementById('commentEdit').classList.add('active');
            updateCharCount();
        }

        function cancelCommentEdit() {
            document.getElementById('commentDisplay').style.display = 'block';
            document.getElementById('commentEdit').classList.remove('active');
        }

        function clearComment() {
            if (confirm('Clear comment?')) {
                document.getElementById('customerComment').value = '';
                updateCharCount();
            }
        }

        function updateCharCount() {
            const textarea = document.getElementById('customerComment');
            const counter = document.getElementById('charCount');
            const length = textarea.value.length;
            counter.textContent = length;
            counter.style.color = length > 900 ? 'var(--danger)' : (length > 750 ? 'var(--warning)' : '#666');
        }

        // Close modals on outside click
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', e => {
                if (e.target === modal) modal.classList.remove('active');
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
                if (isEditMode) cancelEdit();
                if (isAddMode) cancelAddItems();
            }
        });
    </script>
</body>
</html>