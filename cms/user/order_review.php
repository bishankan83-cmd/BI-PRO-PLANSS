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

// ── AUTO-CREATE customer_reference column if it doesn't exist ────────────────
try {
    if (!columnExists($pdo, 'tire_orders', 'customer_reference')) {
        $pdo->exec("ALTER TABLE tire_orders ADD COLUMN customer_reference VARCHAR(120) NULL DEFAULT NULL AFTER customer_comment");
    }
} catch (PDOException $e) {
    // Non-fatal – column may already exist or permissions differ
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

                // ── SAVE CUSTOMER REFERENCE ──────────────────────────────────
                case 'save_reference':
                    $ref = trim($_POST['customer_reference'] ?? '');
                    // Enforce max length (120 chars)
                    $ref = mb_substr($ref, 0, 120);
                    $updateFields = [];
                    $updateValues = [];
                    if (columnExists($pdo, 'tire_orders', 'customer_reference')) {
                        $updateFields[] = 'customer_reference = ?';
                        $updateValues[] = $ref;
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
                    $message = "Customer reference saved!";
                    $messageType = 'success';
                    break;

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
                    $message = "Comment saved successfully!";
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
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total_items, SUM(oi.quantity) as total_quantity FROM tire_order_items oi WHERE oi.{$itemOrderLinkColumn} = ?");
                        $stmt->execute([$orderId]);
                        $totals = $stmt->fetch();
                        if (columnExists($pdo, 'tire_orders', 'total_items') && columnExists($pdo, 'tire_orders', 'total_quantity')) {
                            $stmt = $pdo->prepare("UPDATE tire_orders SET total_items = ?, total_quantity = ?, updated_at = ? WHERE {$orderPkColumn} = ?");
                            $stmt->execute([$totals['total_items'], $totals['total_quantity'], date('Y-m-d H:i:s'), $orderId]);
                        }
                        $pdo->commit();
                        $message = "New items added successfully!";
                        $messageType = 'success';
                    }
                    break;
                    
                case 'update_order':
                    $updatedItems = json_decode($_POST['order_data'] ?? '', true);
                    if (!empty($updatedItems)) {
                        $stmt = $pdo->prepare("DELETE FROM tire_order_items WHERE {$itemOrderLinkColumn} = ?");
                        $stmt->execute([$orderId]);
                        foreach ($updatedItems as $item) {
                            if (!isset($item['id'], $item['icode'], $item['quantity']) || $item['quantity'] <= 0) {
                                throw new Exception("Invalid order item data");
                            }
                            $stmt = $pdo->prepare("INSERT INTO tire_order_items ({$itemOrderLinkColumn}, product_id, icode, quantity) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$orderId, $item['id'], $item['icode'], (int)$item['quantity']]);
                        }
                        if (columnExists($pdo, 'tire_orders', 'total_items')) {
                            $stmt = $pdo->prepare("UPDATE tire_orders SET total_items = ?, total_quantity = ?, updated_at = ? WHERE {$orderPkColumn} = ?");
                            $stmt->execute([count($updatedItems), array_sum(array_column($updatedItems, 'quantity')), date('Y-m-d H:i:s'), $orderId]);
                        }
                        $pdo->commit();
                        $message = "Order updated successfully!";
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
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
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
    
    $stmt = $pdo->prepare("SELECT o.*, u.fullName, u.userEmail FROM tire_orders o JOIN users u ON o.{$customerIdColumn} = u.id WHERE o.{$orderPkColumn} = ? AND o.{$customerIdColumn} = ?");
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
    
    $stmt = $pdo->prepare("SELECT oi.{$itemPkColumn} as order_item_id, oi.product_id, oi.icode, oi.quantity, r.t_size, r.brand, r.col, r.rim, t.fweight FROM tire_order_items oi LEFT JOIN realstock r ON oi.product_id = r.id LEFT JOIN tire_details t ON oi.icode = t.icode WHERE oi.{$itemOrderLinkColumn} = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT r.id, r.icode, r.t_size, r.brand, r.col, r.rim, t.fweight FROM realstock r LEFT JOIN tire_details t ON r.icode = t.icode ORDER BY r.brand, r.t_size");
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

// Build initials
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false)
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));

// Status label helper
$statusMap = [
    'pending'       => ['label' => 'Pending',   'class' => 'status-pending'],
    'cus_confirmed' => ['label' => 'Confirmed',  'class' => 'status-confirmed'],
    'confirmed'     => ['label' => 'Confirmed',  'class' => 'status-confirmed'],
    'cancelled'     => ['label' => 'Cancelled',  'class' => 'status-cancelled'],
];
$statusKey  = strtolower($orderData['status'] ?? 'pending');
$statusInfo = $statusMap[$statusKey] ?? ['label' => ucfirst($statusKey), 'class' => 'status-pending'];

// Current reference value
$currentRef = $orderData['customer_reference'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review Order #<?php echo htmlspecialchars($orderId); ?> — ATIRE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ─── SF UI DISPLAY FONT FACES ───────────────────────────────────────────── */
@font-face { font-family:'SF UI Display'; font-weight:500; font-style:normal; src:url('/font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:600; font-style:normal; src:url('/font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:700; font-style:normal; src:url('/font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:800; font-style:normal; src:url('/font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:900; font-style:normal; src:url('/font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); }

/* ─── CSS VARIABLES ──────────────────────────────────────────────────────── */
:root {
    --orange:       #f28018;
    --orange-dk:    #d06e10;
    --orange-lt:    rgba(242,128,24,0.10);
    --orange-glow:  rgba(242,128,24,0.18);
    --gray-50:      #f9f9f9;
    --gray-100:     #f2f2f2;
    --gray-200:     #e4e4e4;
    --gray-300:     #d0d0d0;
    --gray-400:     #b0b0b0;
    --gray-500:     #888888;
    --gray-700:     #444444;
    --gray-900:     #1a1a1a;
    --white:        #ffffff;
    --bg:           #f3f4f6;
    --green:        #27ae60;
    --green-lt:     rgba(39,174,96,0.10);
    --green-glow:   rgba(39,174,96,0.18);
    --red:          #e74c3c;
    --red-lt:       rgba(231,76,60,0.10);
    --blue:         #3498db;
    --blue-lt:      rgba(52,152,219,0.10);
    --purple:       #8e44ad;
    --purple-lt:    rgba(142,68,173,0.10);
    --purple-glow:  rgba(142,68,173,0.18);
    --font:        'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --radius-xs:    4px;
    --radius-sm:    8px;
    --radius-md:    12px;
    --radius-lg:    16px;
    --shadow-sm:    0 1px 6px rgba(0,0,0,0.06);
    --shadow:       0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:    0 6px 28px rgba(0,0,0,0.12);
    --shadow-lg:    0 12px 48px rgba(0,0,0,0.14);
    --trans:        0.18s cubic-bezier(0.4,0,0.2,1);
    --hdr-h:        60px;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--gray-700);
    min-height: 100vh;
    font-size: 13.5px;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
}

/* ─── SCROLLBAR ──────────────────────────────────────────────────────────── */
::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--gray-300); border-radius:99px; }
::-webkit-scrollbar-thumb:hover { background:var(--orange); }

/* ─── HEADER ─────────────────────────────────────────────────────────────── */
.hdr {
    position: sticky; top:0; z-index:400;
    background: var(--white);
    border-bottom: 2.5px solid var(--orange);
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    height: var(--hdr-h);
}
.hdr-inner {
    max-width: 1400px; margin:0 auto;
    padding: 0 1.8rem;
    height: 100%;
    display: flex; align-items:center; justify-content:space-between; gap:1rem;
}
.brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
.brand-logo { height:30px; width:auto; }
.hdr-right { display:flex; align-items:center; gap:8px; }
.hdr-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 15px; border-radius:var(--radius-sm);
    font-weight:700; font-size:12px; letter-spacing:.03em;
    text-decoration:none;
    border:1.5px solid var(--gray-200);
    background:var(--white); color:var(--gray-500);
    cursor:pointer; transition:var(--trans);
    font-family:var(--font);
}
.hdr-btn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.avatar {
    width:34px; height:34px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:12px;
    box-shadow:0 2px 8px rgba(242,128,24,0.35);
}

/* ─── MODE BANNERS ───────────────────────────────────────────────────────── */
.mode-banner {
    display:none; align-items:center; justify-content:center; gap:10px;
    padding:10px 1.8rem;
    font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
    color:var(--white);
}
.mode-banner.show { display:flex; }
.mode-banner-edit  { background:var(--blue); }
.mode-banner-add   { background:var(--green); }

/* ─── MESSAGE BAR ────────────────────────────────────────────────────────── */
.msg-bar { padding:.7rem 1.8rem 0; max-width:1400px; margin:0 auto; }
.msg {
    padding:10px 14px; border-radius:var(--radius-sm);
    display:flex; align-items:center; gap:10px;
    font-weight:600; font-size:13px;
    border-left:3px solid var(--orange);
    background:rgba(242,128,24,0.07); color:#7a4400;
}
.msg.error  { background:rgba(231,76,60,0.07); color:#7a1a1a; border-color:var(--red); }
.msg.success{ background:rgba(39,174,96,0.07); color:#174d2a; border-color:var(--green); }

/* ─── PAGE SHELL ─────────────────────────────────────────────────────────── */
.page-shell { max-width:1400px; margin:0 auto; padding:1.8rem 1.8rem 6rem; }

/* ─── HERO SECTION ───────────────────────────────────────────────────────── */
.page-hero {
    background:var(--white);
    border-radius:var(--radius-lg);
    box-shadow:var(--shadow);
    padding:1.8rem 2rem;
    margin-bottom:1.4rem;
    display:flex; align-items:flex-start; justify-content:space-between; gap:1.5rem; flex-wrap:wrap;
}
.hero-eyebrow {
    font-size:9px; font-weight:800; color:var(--orange);
    letter-spacing:.22em; text-transform:uppercase;
    margin-bottom:6px; display:flex; align-items:center; gap:6px;
}
.hero-eyebrow::before { content:''; width:16px; height:2px; background:var(--orange); border-radius:2px; }
.hero-title {
    font-size:clamp(26px,3vw,38px); font-weight:900;
    color:var(--gray-900); letter-spacing:-.02em; line-height:1.05;
}
.hero-title span { color:var(--orange); }
.hero-sub { font-size:12px; font-weight:500; color:var(--gray-400); margin-top:4px; }

/* Status pill */
.status-pill {
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 13px; border-radius:20px;
    font-size:10.5px; font-weight:800; letter-spacing:.10em; text-transform:uppercase;
    margin-top:10px;
}
.status-pill::before { content:''; width:7px; height:7px; border-radius:50%; }
.status-pending   { background:rgba(243,156,18,0.12); color:#7a5200; }
.status-pending::before { background:#f39c12; }
.status-confirmed { background:rgba(39,174,96,0.12); color:#174d2a; }
.status-confirmed::before { background:var(--green); }
.status-cancelled { background:rgba(231,76,60,0.12); color:#7a1a1a; }
.status-cancelled::before { background:var(--red); }

/* ─── STAT CARDS ROW ─────────────────────────────────────────────────────── */
.stat-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:1.4rem; }
.stat-card {
    flex:1; min-width:160px;
    background:var(--white);
    border-radius:var(--radius-md);
    border:1.5px solid var(--gray-200);
    padding:1rem 1.2rem;
    box-shadow:var(--shadow-sm);
    transition:var(--trans);
}
.stat-card:hover { border-color:var(--orange); box-shadow:0 4px 18px rgba(242,128,24,0.12); }
.stat-card-icon {
    width:30px; height:30px; border-radius:var(--radius-xs);
    background:var(--orange-lt); color:var(--orange);
    display:flex; align-items:center; justify-content:center;
    font-size:12px; margin-bottom:10px;
}
.stat-card-val { font-size:26px; font-weight:900; color:var(--gray-900); letter-spacing:-.03em; line-height:1; }
.stat-card-lbl { font-size:10px; font-weight:700; color:var(--gray-400); letter-spacing:.10em; text-transform:uppercase; margin-top:4px; }

/* ─── SECTION CARD ───────────────────────────────────────────────────────── */
.section-card {
    background:var(--white);
    border-radius:var(--radius-lg);
    border:1.5px solid var(--gray-200);
    box-shadow:var(--shadow-sm);
    overflow:hidden;
    margin-bottom:1.4rem;
}
.section-hd {
    padding:.85rem 1.4rem;
    border-bottom:1.5px solid var(--gray-100);
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    background:var(--white);
}
.section-hd-title {
    font-size:12px; font-weight:800; color:var(--gray-700);
    letter-spacing:.10em; text-transform:uppercase;
    display:flex; align-items:center; gap:8px;
}
.section-hd-title .ico {
    width:26px; height:26px; border-radius:var(--radius-xs);
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-size:11px;
}
.section-hd-title .ico.purple { background:var(--purple); }
.section-hd-actions { display:flex; gap:8px; }

/* ─── ACTION BUTTON VARIANTS ─────────────────────────────────────────────── */
.btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; border:none; border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12px; font-weight:700;
    letter-spacing:.04em; text-transform:uppercase;
    cursor:pointer; transition:var(--trans); text-decoration:none;
}
.btn:hover { transform:translateY(-1px); }

.btn-orange { background:var(--orange); color:var(--white); }
.btn-orange:hover { background:var(--orange-dk); box-shadow:0 4px 14px rgba(242,128,24,0.30); }

.btn-ghost {
    background:var(--white); color:var(--gray-500);
    border:1.5px solid var(--gray-200);
}
.btn-ghost:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }

.btn-green { background:var(--green); color:var(--white); }
.btn-green:hover { background:#229954; box-shadow:0 4px 14px rgba(39,174,96,0.28); }

.btn-red { background:var(--red); color:var(--white); }
.btn-red:hover { background:#c0392b; box-shadow:0 4px 14px rgba(231,76,60,0.28); }

.btn-blue { background:var(--blue); color:var(--white); }
.btn-blue:hover { background:#2980b9; box-shadow:0 4px 14px rgba(52,152,219,0.28); }

.btn-purple { background:var(--purple); color:var(--white); }
.btn-purple:hover { background:#7d3c98; box-shadow:0 4px 14px rgba(142,68,173,0.28); }

.btn-lg { padding:12px 24px; font-size:13px; }

/* ─── ITEMS TABLE ────────────────────────────────────────────────────────── */
.tbl-scroll { overflow-x:auto; }
table.order-tbl {
    width:100%; border-collapse:collapse;
    table-layout:auto; min-width:700px;
}
table.order-tbl thead {
    background:#f7f7f7;
    position:sticky; top:0; z-index:5;
}
table.order-tbl thead::after {
    content:''; display:block;
    position:absolute; bottom:-2px; left:0; right:0;
    height:2px; background:var(--gray-200);
}
table.order-tbl th {
    padding:8px 11px; text-align:left;
    font-size:10px; font-weight:800; color:var(--gray-500);
    letter-spacing:.11em; text-transform:uppercase;
    white-space:nowrap;
    border-right:1px solid var(--gray-200);
    border-bottom:2px solid var(--gray-200);
}
table.order-tbl th:last-child { border-right:none; }
table.order-tbl th i { color:var(--orange); margin-right:4px; font-size:9px; }
table.order-tbl tbody tr {
    border-bottom:1px solid var(--gray-100);
    transition:background var(--trans);
}
table.order-tbl tbody tr:last-child { border-bottom:none; }
table.order-tbl tbody tr:nth-child(even) { background:#fafafa; }
table.order-tbl tbody tr:hover { background:rgba(242,128,24,0.03); }
table.order-tbl td { padding:8px 11px; font-size:12.5px; font-weight:500; color:var(--gray-700); vertical-align:middle; }
td.tc-no { font-size:10px; font-weight:700; color:var(--gray-400); text-align:center; }
td.tc-code { font-weight:800; font-size:13px; color:var(--orange); white-space:nowrap; }
td.tc-brand { font-weight:700; color:var(--gray-900); white-space:nowrap; }
td.tc-num { font-weight:700; text-align:right; white-space:nowrap; }
td.tc-sub { font-weight:800; color:var(--gray-900); text-align:right; white-space:nowrap; }
.color-badge, .rim-badge {
    display:inline-flex; align-items:center;
    padding:2px 7px; border-radius:20px;
    font-size:10px; font-weight:700;
    background:var(--gray-100); color:var(--gray-500);
    border:1px solid var(--gray-200);
}

/* Qty controls */
.qty-display { font-weight:800; font-size:13px; color:var(--gray-900); }
.qty-edit-wrap {
    display:none; align-items:center; gap:5px;
}
.qty-edit-wrap.show { display:flex; }
.qty-step-btn {
    width:28px; height:28px; border:none;
    background:var(--orange); color:var(--white);
    border-radius:var(--radius-xs); cursor:pointer;
    font-size:10px; transition:var(--trans);
    display:flex; align-items:center; justify-content:center;
}
.qty-step-btn:hover { background:var(--orange-dk); }
.qty-inp-edit {
    width:58px; text-align:center;
    border:2px solid var(--orange); border-radius:var(--radius-sm);
    padding:4px; font-family:var(--font); font-weight:800; font-size:13px; color:var(--gray-700);
    outline:none; -moz-appearance:textfield;
}
.qty-inp-edit::-webkit-inner-spin-button,
.qty-inp-edit::-webkit-outer-spin-button { -webkit-appearance:none; }
.qty-inp-edit:focus { box-shadow:0 0 0 3px var(--orange-glow); }

.rm-btn {
    background:none; border:1.5px solid var(--gray-200); color:var(--gray-400);
    padding:5px 9px; border-radius:var(--radius-xs);
    cursor:pointer; font-size:10.5px; font-family:var(--font); font-weight:700;
    display:inline-flex; align-items:center; gap:4px;
    transition:var(--trans);
}
.rm-btn:hover { border-color:var(--red); color:var(--red); background:var(--red-lt); }
.edit-col { display:none; }

/* Empty state */
.empty-state { text-align:center; padding:3rem 2rem; }
.empty-state i { font-size:2.5rem; color:var(--gray-300); margin-bottom:12px; display:block; }
.empty-state h3 { font-size:16px; font-weight:800; color:var(--gray-700); }
.empty-state p { font-size:12.5px; color:var(--gray-400); margin-top:4px; font-weight:500; }

/* ─── ADD ITEMS PANEL ────────────────────────────────────────────────────── */
.add-panel {
    display:none;
    border-top:2px dashed var(--green);
}
.add-panel.show { display:block; }
.add-panel-inner { padding:1.2rem 1.4rem; }
.add-filters {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:12px; margin-bottom:1rem;
}
.add-filters input,
.add-filters select {
    width:100%; padding:7px 10px;
    border:1.5px solid var(--gray-200);
    border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12.5px; font-weight:600;
    color:var(--gray-700); background:var(--white);
    outline:none; transition:var(--trans);
}
.add-filters input:focus,
.add-filters select:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
.add-tbl-scroll { max-height:320px; overflow-y:auto; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); }
table.add-tbl { width:100%; border-collapse:collapse; min-width:600px; }
table.add-tbl thead { background:#f0faf4; position:sticky; top:0; z-index:5; }
table.add-tbl th {
    padding:8px 11px; text-align:left;
    font-size:10px; font-weight:800; color:#2d7a4f;
    letter-spacing:.11em; text-transform:uppercase;
    border-bottom:2px solid #c3e6cb;
    white-space:nowrap;
}
table.add-tbl td { padding:7px 11px; border-bottom:1px solid var(--gray-100); font-size:12.5px; font-weight:500; color:var(--gray-700); vertical-align:middle; }
table.add-tbl tbody tr:hover { background:#f0faf4; }
table.add-tbl tbody tr.row-selected { background:#d4edda; }
.qty-add-wrap { display:flex; align-items:center; gap:5px; }
.qty-inp-add {
    width:55px; text-align:center;
    border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    padding:4px; font-family:var(--font); font-weight:800; font-size:13px; color:var(--gray-700);
    outline:none; -moz-appearance:textfield; transition:var(--trans);
}
.qty-inp-add::-webkit-inner-spin-button,
.qty-inp-add::-webkit-outer-spin-button { -webkit-appearance:none; }
.qty-inp-add.has-val { border-color:var(--green); background:rgba(39,174,96,0.07); color:var(--green); }
.add-panel-footer {
    padding:.9rem 1.4rem;
    border-top:1.5px solid var(--gray-100);
    display:flex; gap:8px; justify-content:flex-end;
    background:#f9fef9;
}

/* Edit actions bar */
.edit-actions-bar {
    display:none; padding:.9rem 1.4rem;
    border-top:1.5px solid var(--gray-100);
    background:rgba(52,152,219,0.05);
    gap:8px;
}
.edit-actions-bar.show { display:flex; }

/* ─── REFERENCE SECTION ──────────────────────────────────────────────────── */
.ref-view { padding:1.2rem 1.4rem; }

.ref-display {
    display:flex; align-items:center; gap:14px;
    background:var(--gray-50);
    border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md);
    padding:.9rem 1.1rem;
    min-height:50px;
}
.ref-display-icon {
    width:34px; height:34px; flex-shrink:0;
    border-radius:var(--radius-xs);
    background:var(--purple-lt); color:var(--purple);
    display:flex; align-items:center; justify-content:center;
    font-size:13px;
}
.ref-display-text {
    flex:1; font-size:14px; font-weight:800;
    color:var(--gray-900); letter-spacing:.01em;
    word-break:break-all;
}
.ref-display-text.empty-ref {
    font-weight:500; font-style:italic;
    color:var(--gray-400); font-size:13px;
}
.ref-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 9px; border-radius:20px;
    background:var(--purple-lt); color:var(--purple);
    font-size:9.5px; font-weight:800; letter-spacing:.08em; text-transform:uppercase;
    border:1px solid rgba(142,68,173,0.20);
    flex-shrink:0;
}

/* Reference edit panel */
.ref-edit {
    display:none;
    padding:1.2rem 1.4rem;
    border-top:1.5px solid var(--gray-100);
    background:#fdf8ff;
}
.ref-edit.show { display:block; }

.ref-input-wrap {
    display:flex; align-items:center; gap:0;
    border:2px solid var(--purple);
    border-radius:var(--radius-sm);
    overflow:hidden;
    transition:var(--trans);
    background:var(--white);
}
.ref-input-wrap:focus-within {
    box-shadow:0 0 0 3px var(--purple-glow);
    border-color:var(--purple);
}
.ref-prefix {
    padding:0 12px;
    background:var(--purple-lt);
    border-right:2px solid rgba(142,68,173,0.20);
    height:42px;
    display:flex; align-items:center;
    font-size:11px; font-weight:800; color:var(--purple);
    letter-spacing:.06em; text-transform:uppercase;
    white-space:nowrap; flex-shrink:0;
}
.ref-prefix i { margin-right:5px; }
.ref-inp {
    flex:1; border:none; outline:none;
    padding:0 13px;
    height:42px;
    font-family:var(--font); font-size:14px; font-weight:700;
    color:var(--gray-900);
    background:transparent;
    letter-spacing:.02em;
}
.ref-inp::placeholder { color:var(--gray-400); font-weight:500; font-size:13px; }
.ref-char-counter {
    text-align:right; font-size:10.5px; font-weight:600;
    color:var(--gray-400); margin-top:5px;
}
.ref-char-counter.warn { color:var(--orange); }
.ref-char-counter.over { color:var(--red); }
.ref-hint {
    margin-top:8px; font-size:11.5px; font-weight:500; color:var(--gray-400);
    display:flex; align-items:flex-start; gap:6px;
}
.ref-hint i { color:var(--purple); margin-top:1px; flex-shrink:0; }
.ref-edit-footer { display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; align-items:center; }

/* ─── COMMENT SECTION ────────────────────────────────────────────────────── */
.comment-view { padding:1.2rem 1.4rem; }
.comment-bubble {
    background:var(--gray-50);
    border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md);
    padding:1rem 1.2rem;
    font-size:13px; font-weight:500; color:var(--gray-700);
    line-height:1.6; white-space:pre-wrap; word-wrap:break-word;
    min-height:52px;
}
.comment-bubble.empty-comment { color:var(--gray-400); font-style:italic; }
.comment-edit {
    display:none; padding:1.2rem 1.4rem; border-top:1.5px solid var(--gray-100);
    background:#f8fff8;
}
.comment-edit.show { display:block; }
.comment-edit textarea {
    width:100%; padding:10px 12px;
    border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    font-family:var(--font); font-size:13px; font-weight:500; color:var(--gray-700);
    resize:vertical; min-height:110px; outline:none; transition:var(--trans);
    background:var(--white);
}
.comment-edit textarea:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
.char-counter {
    text-align:right; font-size:10.5px; font-weight:600; color:var(--gray-400);
    margin-top:4px;
}
.char-counter.warn { color:var(--orange); }
.char-counter.over { color:var(--red); }
.comment-edit-footer { display:flex; gap:8px; margin-top:12px; flex-wrap:wrap; }

/* ─── FINAL ACTION BAR ───────────────────────────────────────────────────── */
.final-bar {
    position:fixed; bottom:0; left:0; right:0; z-index:300;
    background:var(--white);
    border-top:2.5px solid var(--orange);
    box-shadow:0 -4px 28px rgba(0,0,0,0.10);
    padding:12px 1.8rem;
    display:flex; align-items:center; justify-content:flex-end; gap:12px;
}
.final-bar-info {
    flex:1; font-size:11.5px; font-weight:600; color:var(--gray-400);
    display:flex; align-items:center; gap:6px;
}
.final-bar-info strong { color:var(--gray-900); font-weight:900; }

/* ─── MODAL ──────────────────────────────────────────────────────────────── */
.modal-veil {
    position:fixed; inset:0; background:rgba(0,0,0,0.60);
    display:none; align-items:center; justify-content:center; z-index:900;
    backdrop-filter:blur(3px);
}
.modal-veil.show { display:flex; }
.modal-box {
    background:var(--white); border-radius:var(--radius-lg);
    width:100%; max-width:460px;
    box-shadow:var(--shadow-lg);
    overflow:hidden;
    animation:modalIn .25s ease;
}
@keyframes modalIn { from{opacity:0;transform:scale(.94) translateY(-12px);} to{opacity:1;transform:scale(1) translateY(0);} }
.modal-hdr {
    padding:1.2rem 1.4rem;
    display:flex; align-items:center; gap:10px;
}
.modal-hdr-icon {
    width:36px; height:36px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; flex-shrink:0;
}
.modal-hdr-icon.green  { background:rgba(39,174,96,0.15); color:var(--green); }
.modal-hdr-icon.red    { background:rgba(231,76,60,0.12); color:var(--red); }
.modal-hdr h3 { font-size:16px; font-weight:900; color:var(--gray-900); letter-spacing:-.01em; }
.modal-body { padding:0 1.4rem 1.2rem; font-size:13px; color:var(--gray-500); font-weight:500; line-height:1.6; }
.modal-body strong { color:var(--gray-900); }
.modal-ftr {
    padding:1rem 1.4rem;
    border-top:1.5px solid var(--gray-100);
    display:flex; gap:8px; justify-content:flex-end;
    background:var(--gray-50);
}

/* ─── RESPONSIVE ─────────────────────────────────────────────────────────── */
@media(max-width:700px) {
    .page-shell { padding:1rem 1rem 6rem; }
    .page-hero { flex-direction:column; }
    .stat-row { gap:8px; }
    .stat-card { min-width:120px; }
    .final-bar { flex-direction:column; align-items:stretch; }
    .final-bar-info { justify-content:center; }
    .ref-prefix { display:none; }
}
</style>
</head>
<body>

<!-- ═══════════════════════════════════ HEADER ═══════════════════════════ -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-right">
            <a href="place_order.php" class="hdr-btn"><i class="fas fa-plus"></i> New Order</a>
            <a href="dashboard.php" class="hdr-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        </div>
    </div>
</header>

<!-- Mode Banners -->
<div class="mode-banner mode-banner-edit" id="editBanner">
    <i class="fas fa-pen"></i> Edit Mode Active — modify quantities or remove items
</div>
<div class="mode-banner mode-banner-add" id="addBanner">
    <i class="fas fa-plus-circle"></i> Add Items Mode — select quantities from inventory
</div>

<?php if ($message): ?>
<div class="msg-bar">
    <div class="msg <?php echo htmlspecialchars($messageType); ?>">
        <i class="fas fa-<?php echo $messageType==='success'?'check-circle':($messageType==='error'?'times-circle':'exclamation-circle'); ?>"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════ PAGE ═════════════════════════════ -->
<div class="page-shell">

    <!-- ── HERO ──────────────────────────────────────────────────────────── -->
    <div class="page-hero">
        <div>
            <div class="hero-eyebrow">Order Review</div>
            <div class="hero-title">Order <span>#<?php echo htmlspecialchars($orderId); ?></span></div>
            <div class="hero-sub"><?php echo htmlspecialchars($userData['fullName']); ?> &nbsp;·&nbsp; <?php echo date('F j, Y', strtotime($orderData['created_at'] ?? 'now')); ?></div>
            <div class="status-pill <?php echo $statusInfo['class']; ?>"><?php echo $statusInfo['label']; ?></div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
            <div style="font-size:10px;font-weight:700;color:var(--gray-400);letter-spacing:.10em;text-transform:uppercase;">Customer</div>
            <div style="font-size:14px;font-weight:800;color:var(--gray-900);"><?php echo htmlspecialchars($userData['fullName']); ?></div>
            <div style="font-size:12px;font-weight:500;color:var(--gray-500);"><?php echo htmlspecialchars($userData['userEmail']); ?></div>
            <?php if (!empty($currentRef)): ?>
            <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                <span style="font-size:9.5px;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.08em;">Your Ref:</span>
                <span style="font-size:12px;font-weight:900;color:var(--purple);letter-spacing:.03em;"><?php echo htmlspecialchars($currentRef); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── STAT CARDS ─────────────────────────────────────────────────────── -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-layer-group"></i></div>
            <div class="stat-card-val" id="statItems"><?php echo $totalItems; ?></div>
            <div class="stat-card-lbl">Product Lines</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-boxes"></i></div>
            <div class="stat-card-val" id="statQty"><?php echo $totalQuantity; ?></div>
            <div class="stat-card-lbl">Total Units</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-weight-hanging"></i></div>
            <div class="stat-card-val" id="statWt"><?php echo number_format($totalWeight, 0); ?></div>
            <div class="stat-card-lbl">Total Weight (kg)</div>
        </div>
    </div>

    <!-- ── ORDER ITEMS ─────────────────────────────────────────────────────── -->
    <div class="section-card">
        <div class="section-hd">
            <div class="section-hd-title">
                <div class="ico"><i class="fas fa-box-open"></i></div>
                Order Items
            </div>
            <div class="section-hd-actions">
                <button type="button" class="btn btn-ghost" onclick="toggleAddMode()"><i class="fas fa-plus"></i> Add Items</button>
                <button type="button" class="btn btn-ghost" onclick="toggleEditMode()"><i class="fas fa-pen"></i> Edit</button>
            </div>
        </div>

        <div class="tbl-scroll">
            <table class="order-tbl" id="orderTbl">
                <thead>
                    <tr>
                        <th style="text-align:center;">#</th>
                        <th><i class="fas fa-barcode"></i>Code</th>
                        <th><i class="fas fa-ruler"></i>Size</th>
                        <th><i class="fas fa-tag"></i>Brand</th>
                        <th><i class="fas fa-palette"></i>Color</th>
                        <th><i class="fas fa-cog"></i>Rim</th>
                        <th><i class="fas fa-weight"></i>Wt (kg)</th>
                        <th><i class="fas fa-sort-numeric-up"></i>Qty</th>
                        <th><i class="fas fa-calculator"></i>Subtotal</th>
                        <th class="edit-col"><i class="fas fa-tools"></i></th>
                    </tr>
                </thead>
                <tbody id="orderTbody">
                <?php $rn=1; foreach($orderItems as $item):
                    $fw=(float)($item['fweight']??0);
                    $subtotal=$item['quantity']*$fw;
                ?>
                    <tr
                        data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>"
                        data-icode="<?php echo htmlspecialchars($item['icode']??''); ?>"
                        data-size="<?php echo htmlspecialchars($item['t_size']??''); ?>"
                        data-brand="<?php echo htmlspecialchars($item['brand']??''); ?>"
                        data-color="<?php echo htmlspecialchars($item['col']??''); ?>"
                        data-rim="<?php echo htmlspecialchars($item['rim']??''); ?>"
                        data-fweight="<?php echo $fw; ?>"
                        data-quantity="<?php echo $item['quantity']; ?>">
                        <td class="tc-no"><?php echo $rn++; ?></td>
                        <td class="tc-code"><?php echo htmlspecialchars($item['icode']??'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['t_size']??'N/A'); ?></td>
                        <td class="tc-brand"><?php echo htmlspecialchars($item['brand']??'N/A'); ?></td>
                        <td><span class="color-badge"><?php echo htmlspecialchars($item['col']??'—'); ?></span></td>
                        <td><span class="rim-badge"><?php echo htmlspecialchars($item['rim']??'—'); ?></span></td>
                        <td class="tc-num"><?php echo number_format($fw,2); ?></td>
                        <td>
                            <span class="qty-display"><?php echo $item['quantity']; ?></span>
                            <div class="qty-edit-wrap">
                                <button type="button" class="qty-step-btn" onclick="stepQty(this,-1)"><i class="fas fa-minus"></i></button>
                                <input type="number" class="qty-inp-edit" min="1" value="<?php echo $item['quantity']; ?>" onchange="onQtyChange(this)" oninput="onQtyChange(this)">
                                <button type="button" class="qty-step-btn" onclick="stepQty(this,1)"><i class="fas fa-plus"></i></button>
                            </div>
                        </td>
                        <td class="tc-sub item-subtotal"><?php echo number_format($subtotal,2); ?> kg</td>
                        <td class="edit-col">
                            <button type="button" class="rm-btn" onclick="removeRow(this)"><i class="fas fa-times"></i> Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($orderItems)): ?>
                    <tr><td colspan="10">
                        <div class="empty-state"><i class="fas fa-inbox"></i><h3>No Items</h3><p>This order has no items yet.</p></div>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit save / cancel bar -->
        <div class="edit-actions-bar" id="editActionsBar">
            <button type="button" class="btn btn-green" onclick="saveEdits()"><i class="fas fa-check"></i> Save Changes</button>
            <button type="button" class="btn btn-ghost" onclick="cancelEdit()"><i class="fas fa-times"></i> Cancel</button>
        </div>

        <!-- Add items panel -->
        <div class="add-panel" id="addPanel">
            <div class="add-panel-inner">
                <div class="add-filters">
                    <input type="text" id="addSearch" placeholder="Search code, size, brand…" oninput="filterAddItems()">
                    <select id="addBrandFilter" onchange="filterAddItems()">
                        <option value="">All Brands</option>
                        <?php
                        $brands = array_unique(array_column($availableInventory,'brand'));
                        sort($brands);
                        foreach($brands as $b): if(!empty($b)): ?>
                        <option value="<?php echo htmlspecialchars($b); ?>"><?php echo htmlspecialchars($b); ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="add-tbl-scroll">
                    <table class="add-tbl" id="addTbl">
                        <thead>
                            <tr>
                                <th><i class="fas fa-barcode" style="font-size:9px;margin-right:3px;"></i>Code</th>
                                <th><i class="fas fa-ruler" style="font-size:9px;margin-right:3px;"></i>Size</th>
                                <th><i class="fas fa-tag" style="font-size:9px;margin-right:3px;"></i>Brand</th>
                                <th><i class="fas fa-weight" style="font-size:9px;margin-right:3px;"></i>Weight</th>
                                <th><i class="fas fa-sort-numeric-up" style="font-size:9px;margin-right:3px;"></i>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($availableInventory as $inv): $fw=(float)($inv['fweight']??0); ?>
                            <tr
                                data-product-id="<?php echo htmlspecialchars($inv['id']); ?>"
                                data-icode="<?php echo htmlspecialchars($inv['icode']??''); ?>"
                                data-size="<?php echo htmlspecialchars($inv['t_size']??''); ?>"
                                data-brand="<?php echo htmlspecialchars($inv['brand']??''); ?>"
                                data-color="<?php echo htmlspecialchars($inv['col']??''); ?>"
                                data-rim="<?php echo htmlspecialchars($inv['rim']??''); ?>"
                                data-fweight="<?php echo $fw; ?>">
                                <td><strong style="color:var(--orange);"><?php echo htmlspecialchars($inv['icode']??'N/A'); ?></strong></td>
                                <td><?php echo htmlspecialchars($inv['t_size']??'N/A'); ?></td>
                                <td style="font-weight:700;"><?php echo htmlspecialchars($inv['brand']??'N/A'); ?></td>
                                <td style="text-align:right;font-weight:700;"><?php echo number_format($fw,2); ?></td>
                                <td>
                                    <div class="qty-add-wrap">
                                        <button type="button" class="qty-step-btn" onclick="stepAddQty(this,-1)"><i class="fas fa-minus"></i></button>
                                        <input type="number" class="qty-inp-add" min="0" value="0" onchange="onAddQtyChange(this)" oninput="onAddQtyChange(this)">
                                        <button type="button" class="qty-step-btn" onclick="stepAddQty(this,1)"><i class="fas fa-plus"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="add-panel-footer">
                <button type="button" class="btn btn-green" onclick="saveAddedItems()"><i class="fas fa-check"></i> Add Selected</button>
                <button type="button" class="btn btn-ghost" onclick="cancelAddMode()"><i class="fas fa-times"></i> Cancel</button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         ── CUSTOMER REFERENCE ─────────────────────────────────────────────
         ══════════════════════════════════════════════════════════════════ -->
    <div class="section-card">
        <div class="section-hd">
            <div class="section-hd-title">
                <div class="ico purple"><i class="fas fa-fingerprint"></i></div>
                Your Reference
            </div>
            <div class="section-hd-actions">
                <button type="button" class="btn btn-ghost" id="refToggleBtn" onclick="toggleRefEdit()">
                    <i class="fas fa-pen"></i>
                    <?php echo !empty($currentRef) ? 'Edit' : 'Add'; ?> Reference
                </button>
            </div>
        </div>

        <!-- View mode -->
        <div class="ref-view" id="refView">
            <div class="ref-display">
                <div class="ref-display-icon"><i class="fas fa-hashtag"></i></div>
                <?php if (!empty($currentRef)): ?>
                    <div class="ref-display-text" id="refDisplayText"><?php echo htmlspecialchars($currentRef); ?></div>
                    <div class="ref-badge"><i class="fas fa-check"></i> Set</div>
                <?php else: ?>
                    <div class="ref-display-text empty-ref" id="refDisplayText">No reference added yet.</div>
                    <div class="ref-badge" style="background:var(--gray-100);color:var(--gray-400);border-color:var(--gray-200);">
                        <i class="fas fa-minus"></i> None
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit mode -->
        <div class="ref-edit" id="refEditPanel">
            <form method="POST" id="refForm" onsubmit="return validateRef()">
                <input type="hidden" name="action" value="save_reference">

                <div class="ref-input-wrap">
                    <div class="ref-prefix"><i class="fas fa-hashtag"></i> Your Ref</div>
                    <input
                        type="text"
                        class="ref-inp"
                        id="refInp"
                        name="customer_reference"
                        maxlength="120"
                        placeholder="e.g. PO-2024-001, INV-ABC, STORE-REF…"
                        value="<?php echo htmlspecialchars($currentRef); ?>"
                        oninput="updateRefCount()"
                        autocomplete="off"
                        spellcheck="false">
                </div>

                <div class="ref-char-counter" id="refCharCounter">0 / 120</div>

                <div class="ref-hint">
                    <i class="fas fa-info-circle"></i>
                    Enter your own internal reference such as a PO number, invoice ID, or store code.
                    This will be printed on your order confirmation and visible to the ATIRE team.
                </div>

                <div class="ref-edit-footer">
                    <button type="submit" class="btn btn-purple"><i class="fas fa-save"></i> Save Reference</button>
                    <button type="button" class="btn btn-ghost" onclick="toggleRefEdit()"><i class="fas fa-times"></i> Cancel</button>
                    <button type="button" class="btn btn-ghost" onclick="clearRef()" style="margin-left:auto;">
                        <i class="fas fa-eraser"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── COMMENT ─────────────────────────────────────────────────────────── -->
    <div class="section-card">
        <div class="section-hd">
            <div class="section-hd-title">
                <div class="ico"><i class="fas fa-comment-dots"></i></div>
                Order Comment
            </div>
            <div class="section-hd-actions">
                <button type="button" class="btn btn-ghost" onclick="toggleCommentEdit()"><i class="fas fa-pen"></i> <?php echo !empty($orderData['customer_comment']??'') ? 'Edit' : 'Add'; ?> Comment</button>
            </div>
        </div>
        <div class="comment-view">
            <?php $cc=$orderData['customer_comment']??''; ?>
            <div class="comment-bubble <?php echo empty($cc)?'empty-comment':''; ?>" id="commentBubble">
                <?php echo empty($cc) ? 'No comment added yet.' : htmlspecialchars($cc); ?>
            </div>
        </div>
        <div class="comment-edit" id="commentEditPanel">
            <form method="POST" id="commentForm">
                <input type="hidden" name="action" value="save_comment">
                <textarea id="commentTa" name="customer_comment" maxlength="1000"
                    placeholder="Add a note or special instruction for this order…"
                    oninput="updateCharCount()"><?php echo htmlspecialchars($cc); ?></textarea>
                <div class="char-counter" id="charCounter">0 / 1000</div>
                <div class="comment-edit-footer">
                    <button type="submit" class="btn btn-orange"><i class="fas fa-save"></i> Save</button>
                    <button type="button" class="btn btn-ghost" onclick="toggleCommentEdit()"><i class="fas fa-times"></i> Cancel</button>
                    <button type="button" class="btn btn-ghost" onclick="clearComment()" style="margin-left:auto;"><i class="fas fa-eraser"></i> Clear</button>
                </div>
            </form>
        </div>
    </div>

</div><!-- /page-shell -->

<!-- ═══════════════════════════════ FINAL ACTION BAR ════════════════════ -->
<div class="final-bar">
    <div class="final-bar-info">
        <i class="fas fa-info-circle" style="color:var(--orange);"></i>
        Order <strong>#<?php echo htmlspecialchars($orderId); ?></strong> &nbsp;·&nbsp; Review carefully before confirming.
    </div>
    <button type="button" class="btn btn-ghost btn-lg" onclick="showModal('cancelModal')">
        <i class="fas fa-times-circle"></i> Cancel Order
    </button>
    <button type="button" class="btn btn-orange btn-lg" onclick="showModal('confirmModal')">
        <i class="fas fa-check-circle"></i> Confirm Order
    </button>
</div>

<!-- ═══════════════════════════════════ MODALS ══════════════════════════ -->
<!-- Confirm -->
<div class="modal-veil" id="confirmModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-icon green"><i class="fas fa-check-circle"></i></div>
            <h3>Confirm Order?</h3>
        </div>
        <div class="modal-body">
            This will submit order <strong>#<?php echo htmlspecialchars($orderId); ?></strong> for fulfillment. You won't be able to edit it after confirmation.
        </div>
        <div class="modal-ftr">
            <button type="button" class="btn btn-ghost" onclick="hideModal('confirmModal')">Go Back</button>
            <form method="POST" style="display:contents;">
                <input type="hidden" name="action" value="confirm_order">
                <button type="submit" class="btn btn-green"><i class="fas fa-check"></i> Yes, Confirm</button>
            </form>
        </div>
    </div>
</div>

<!-- Cancel -->
<div class="modal-veil" id="cancelModal">
    <div class="modal-box">
        <div class="modal-hdr">
            <div class="modal-hdr-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>Cancel Order?</h3>
        </div>
        <div class="modal-body">
            This will permanently delete order <strong>#<?php echo htmlspecialchars($orderId); ?></strong> and all its items. <span style="color:var(--red);font-weight:700;">This cannot be undone.</span>
        </div>
        <div class="modal-ftr">
            <button type="button" class="btn btn-ghost" onclick="hideModal('cancelModal')">Keep Order</button>
            <form method="POST" style="display:contents;">
                <input type="hidden" name="action" value="cancel_order">
                <button type="submit" class="btn btn-red"><i class="fas fa-trash"></i> Yes, Cancel</button>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════ JS ══════════════════════════════ -->
<script>
let isEditMode = false;
let isAddMode  = false;
let originalRows = [];
let addSelections = new Map();

/* ── INIT ────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    snapshotRows();
    recalcTotals();
    updateCharCount();
    updateRefCount();
});

function snapshotRows() {
    originalRows = Array.from(document.querySelectorAll('#orderTbody tr[data-product-id]')).map(r => ({
        productId : r.dataset.productId,
        icode     : r.dataset.icode,
        size      : r.dataset.size,
        brand     : r.dataset.brand,
        color     : r.dataset.color,
        rim       : r.dataset.rim,
        fweight   : parseFloat(r.dataset.fweight),
        quantity  : parseInt(r.dataset.quantity)
    }));
}

/* ── HELPERS ─────────────────────────────────────────────────────────── */
function esc(s) {
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}
function fmtN(n, d=2) { return (+n).toLocaleString('en-US', {minimumFractionDigits:d, maximumFractionDigits:d}); }

/* ── TOTALS ──────────────────────────────────────────────────────────── */
function recalcTotals() {
    const rows = document.querySelectorAll('#orderTbody tr[data-product-id]');
    let items=0, qty=0, wt=0;
    rows.forEach(r => {
        items++;
        const inp = r.querySelector('.qty-inp-edit');
        const q = inp ? parseInt(inp.value)||0 : parseInt(r.dataset.quantity)||0;
        const fw = parseFloat(r.dataset.fweight)||0;
        qty += q; wt += q*fw;
    });
    document.getElementById('statItems').textContent = items;
    document.getElementById('statQty').textContent   = qty;
    document.getElementById('statWt').textContent    = fmtN(wt, 0);
}

/* ── QTY CONTROLS (ORDER TABLE) ──────────────────────────────────────── */
function stepQty(btn, dir) {
    const inp = btn.parentElement.querySelector('.qty-inp-edit');
    inp.value = Math.max(1, (parseInt(inp.value)||1) + dir);
    onQtyChange(inp);
}
function onQtyChange(inp) {
    const row = inp.closest('tr');
    const q = parseInt(inp.value)||1;
    const fw = parseFloat(row.dataset.fweight)||0;
    row.querySelector('.item-subtotal').textContent = fmtN(q*fw) + ' kg';
    recalcTotals();
}

/* ── QTY CONTROLS (ADD PANEL) ────────────────────────────────────────── */
function stepAddQty(btn, dir) {
    const inp = btn.parentElement.querySelector('.qty-inp-add');
    inp.value = Math.max(0, (parseInt(inp.value)||0) + dir);
    onAddQtyChange(inp);
}
function onAddQtyChange(inp) {
    const q   = parseInt(inp.value)||0;
    const row = inp.closest('tr');
    if (q > 0) {
        inp.classList.add('has-val');
        row.classList.add('row-selected');
        addSelections.set(row.dataset.productId, {
            id      : row.dataset.productId,
            icode   : row.dataset.icode,
            size    : row.dataset.size,
            brand   : row.dataset.brand,
            color   : row.dataset.color,
            rim     : row.dataset.rim,
            fweight : parseFloat(row.dataset.fweight)||0,
            quantity: q
        });
    } else {
        inp.classList.remove('has-val');
        row.classList.remove('row-selected');
        addSelections.delete(row.dataset.productId);
    }
}

/* ── REMOVE ROW ──────────────────────────────────────────────────────── */
function removeRow(btn) {
    if (!confirm('Remove this item from the order?')) return;
    btn.closest('tr').remove();
    document.querySelectorAll('#orderTbody tr[data-product-id] td.tc-no').forEach((td, i) => td.textContent = i+1);
    recalcTotals();
    if (!document.querySelectorAll('#orderTbody tr[data-product-id]').length) {
        alert('All items removed. Returning to dashboard.');
        window.location.href = 'dashboard.php';
    }
}

/* ── EDIT MODE ───────────────────────────────────────────────────────── */
function toggleEditMode() {
    isEditMode ? cancelEdit() : enableEdit();
}
function enableEdit() {
    if (isAddMode) cancelAddMode(true);
    isEditMode = true;
    document.getElementById('editBanner').classList.add('show');
    document.getElementById('editActionsBar').classList.add('show');
    document.querySelectorAll('.qty-display').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.qty-edit-wrap').forEach(el => el.classList.add('show'));
    document.querySelectorAll('.edit-col').forEach(el => el.style.display = 'table-cell');
}
function cancelEdit(silent=false) {
    if (!silent && !confirm('Discard all changes?')) return;
    isEditMode = false;
    document.getElementById('editBanner').classList.remove('show');
    document.getElementById('editActionsBar').classList.remove('show');
    document.querySelectorAll('.qty-display').forEach(el => el.style.display = 'inline');
    document.querySelectorAll('.qty-edit-wrap').forEach(el => el.classList.remove('show'));
    document.querySelectorAll('.edit-col').forEach(el => el.style.display = 'none');
    rebuildRows(originalRows);
    recalcTotals();
}
function rebuildRows(data) {
    const tbody = document.getElementById('orderTbody');
    tbody.innerHTML = data.map((item, idx) => `
        <tr data-product-id="${esc(item.productId)}" data-icode="${esc(item.icode)}" data-size="${esc(item.size)}"
            data-brand="${esc(item.brand)}" data-color="${esc(item.color)}" data-rim="${esc(item.rim)}"
            data-fweight="${item.fweight}" data-quantity="${item.quantity}">
            <td class="tc-no">${idx+1}</td>
            <td class="tc-code">${esc(item.icode)}</td>
            <td>${esc(item.size)}</td>
            <td class="tc-brand">${esc(item.brand)}</td>
            <td><span class="color-badge">${esc(item.color||'—')}</span></td>
            <td><span class="rim-badge">${esc(item.rim||'—')}</span></td>
            <td class="tc-num">${fmtN(item.fweight)}</td>
            <td>
                <span class="qty-display">${item.quantity}</span>
                <div class="qty-edit-wrap">
                    <button type="button" class="qty-step-btn" onclick="stepQty(this,-1)"><i class="fas fa-minus"></i></button>
                    <input type="number" class="qty-inp-edit" min="1" value="${item.quantity}" onchange="onQtyChange(this)" oninput="onQtyChange(this)">
                    <button type="button" class="qty-step-btn" onclick="stepQty(this,1)"><i class="fas fa-plus"></i></button>
                </div>
            </td>
            <td class="tc-sub item-subtotal">${fmtN(item.quantity * item.fweight)} kg</td>
            <td class="edit-col" style="display:none;">
                <button type="button" class="rm-btn" onclick="removeRow(this)"><i class="fas fa-times"></i> Remove</button>
            </td>
        </tr>`).join('');
}
function saveEdits() {
    const rows = document.querySelectorAll('#orderTbody tr[data-product-id]');
    if (!rows.length) { alert('No items in order.'); return; }
    const items = Array.from(rows).map(r => {
        const q = parseInt(r.querySelector('.qty-inp-edit').value)||0;
        if (q < 1) throw new Error('Invalid quantity');
        return { id:r.dataset.productId, icode:r.dataset.icode, size:r.dataset.size,
                 brand:r.dataset.brand, color:r.dataset.color, rim:r.dataset.rim,
                 fweight:parseFloat(r.dataset.fweight), quantity:q };
    });
    if (confirm('Save changes to this order?')) {
        const frm = document.createElement('form');
        frm.method = 'POST';
        frm.innerHTML = `<input type="hidden" name="action" value="update_order"><input type="hidden" name="order_data" value='${JSON.stringify(items)}'>`;
        document.body.appendChild(frm);
        frm.submit();
    }
}

/* ── ADD MODE ────────────────────────────────────────────────────────── */
function toggleAddMode() {
    isAddMode ? cancelAddMode() : enableAddMode();
}
function enableAddMode() {
    if (isEditMode) cancelEdit(true);
    isAddMode = true;
    document.getElementById('addBanner').classList.add('show');
    document.getElementById('addPanel').classList.add('show');
    document.getElementById('addPanel').scrollIntoView({ behavior:'smooth', block:'start' });
}
function cancelAddMode(silent=false) {
    if (!silent && addSelections.size && !confirm('Discard selected items?')) return;
    isAddMode = false;
    document.getElementById('addBanner').classList.remove('show');
    document.getElementById('addPanel').classList.remove('show');
    document.querySelectorAll('.qty-inp-add').forEach(inp => {
        inp.value = 0; inp.classList.remove('has-val');
        inp.closest('tr').classList.remove('row-selected');
    });
    addSelections.clear();
}
function filterAddItems() {
    const q = document.getElementById('addSearch').value.toLowerCase();
    const b = document.getElementById('addBrandFilter').value.toLowerCase();
    document.querySelectorAll('#addTbl tbody tr').forEach(r => {
        const txt = (r.dataset.icode+r.dataset.size+r.dataset.brand).toLowerCase();
        r.style.display = ((!q||txt.includes(q)) && (!b||r.dataset.brand.toLowerCase()===b)) ? '' : 'none';
    });
}
function saveAddedItems() {
    if (!addSelections.size) { alert('Please select at least one item.'); return; }
    const items = Array.from(addSelections.values());
    if (confirm(`Add ${items.length} item line(s) to the order?`)) {
        const frm = document.createElement('form');
        frm.method = 'POST';
        frm.innerHTML = `<input type="hidden" name="action" value="add_items"><input type="hidden" name="new_items_data" value='${JSON.stringify(items)}'>`;
        document.body.appendChild(frm);
        frm.submit();
    }
}

/* ── CUSTOMER REFERENCE ──────────────────────────────────────────────── */
function toggleRefEdit() {
    const panel = document.getElementById('refEditPanel');
    const btn   = document.getElementById('refToggleBtn');
    const isOpen = panel.classList.toggle('show');
    if (isOpen) {
        updateRefCount();
        document.getElementById('refInp').focus();
        btn.innerHTML = '<i class="fas fa-times"></i> Close';
    } else {
        // Restore the original label based on whether a ref exists
        const hasRef = document.getElementById('refDisplayText').textContent.trim() !== 'No reference added yet.';
        btn.innerHTML = `<i class="fas fa-pen"></i> ${hasRef ? 'Edit' : 'Add'} Reference`;
    }
}
function clearRef() {
    if (confirm('Clear the reference?')) {
        document.getElementById('refInp').value = '';
        updateRefCount();
        document.getElementById('refInp').focus();
    }
}
function updateRefCount() {
    const inp = document.getElementById('refInp');
    const n   = inp ? inp.value.length : 0;
    const el  = document.getElementById('refCharCounter');
    if (!el) return;
    el.textContent = n + ' / 120';
    el.className = 'ref-char-counter' + (n > 110 ? ' over' : n > 90 ? ' warn' : '');
}
function validateRef() {
    // Allow saving an empty reference (to clear it)
    return true;
}

/* ── COMMENT ─────────────────────────────────────────────────────────── */
function toggleCommentEdit() {
    const panel = document.getElementById('commentEditPanel');
    panel.classList.toggle('show');
    if (panel.classList.contains('show')) {
        updateCharCount();
        document.getElementById('commentTa').focus();
    }
}
function clearComment() {
    if (confirm('Clear the comment?')) {
        document.getElementById('commentTa').value = '';
        updateCharCount();
    }
}
function updateCharCount() {
    const ta = document.getElementById('commentTa');
    const n  = ta.value.length;
    const el = document.getElementById('charCounter');
    el.textContent = n + ' / 1000';
    el.className = 'char-counter' + (n > 950 ? ' over' : n > 800 ? ' warn' : '');
}

/* ── MODALS ──────────────────────────────────────────────────────────── */
function showModal(id) { document.getElementById(id).classList.add('show'); }
function hideModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal-veil').forEach(v => v.addEventListener('click', e => { if(e.target===v) v.classList.remove('show'); }));
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-veil').forEach(v => v.classList.remove('show'));
    }
});
</script>
</body>
</html>
<?php $pdo = null; ?>