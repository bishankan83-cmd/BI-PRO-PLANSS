<?php
session_start();
include('include/config.php');

if (empty($_SESSION['id'])) {
    header('location:index.php');
    exit();
}

$orderId = mysqli_real_escape_string($con, $_GET['oid']);
$userId  = $_SESSION['id'];

$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
if (!$queryUser) die("User query failed: " . mysqli_error($con));
$userData = mysqli_fetch_assoc($queryUser);
if (!$userData) { header('location:index.php'); exit; }

$stmt = $con->prepare("
    SELECT tor.order_id, tor.customer_id, tor.order_date, tor.status, tor.total_items, tor.total_quantity,
           u.fullName AS name
    FROM tire_orders tor
    JOIN users u ON u.id = tor.customer_id
    WHERE tor.order_id = ? AND tor.customer_id = ?
");
if (!$stmt) die("Order prepare failed: " . $con->error);
$stmt->bind_param("si", $orderId, $userId);
$stmt->execute();
$orderResult = $stmt->get_result();
$order       = $orderResult->fetch_assoc();
$stmt->close();

if (!$order) { header('location:order-history.php'); exit; }

$stmtItems = $con->prepare("
    SELECT ti.item_id, ti.icode, ti.quantity, ti.unit_price, ti.product_id,
           rs.brand, rs.t_size
    FROM tire_order_items ti
    LEFT JOIN realstock rs ON ti.icode = rs.icode
    WHERE ti.order_id = ?
    ORDER BY ti.item_id
");
if (!$stmtItems) die("Items prepare failed: " . $con->error);
$stmtItems->bind_param("s", $orderId);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();
$orderItems  = [];
while ($item = $itemsResult->fetch_assoc()) $orderItems[] = $item;
$stmtItems->close();

/* Initials */
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false)
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Order #<?php echo htmlentities($order['order_id']); ?> — ATIRE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ─── SF UI DISPLAY FONT FACES ───────────────────────────────────────────── */
@font-face { font-family:'SF UI Display'; font-weight:500; font-style:normal; src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:600; font-style:normal; src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:700; font-style:normal; src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:800; font-style:normal; src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:900; font-style:normal; src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); }

/* ─── CSS VARIABLES ──────────────────────────────────────────────────────── */
:root {
    --orange:      #f28018;
    --orange-dk:   #d06e10;
    --orange-lt:   rgba(242,128,24,0.10);
    --orange-glow: rgba(242,128,24,0.18);
    --gray-50:     #f9f9f9;
    --gray-100:    #f2f2f2;
    --gray-200:    #e4e4e4;
    --gray-300:    #d0d0d0;
    --gray-400:    #b0b0b0;
    --gray-500:    #888888;
    --gray-700:    #444444;
    --gray-900:    #1a1a1a;
    --white:       #ffffff;
    --bg:          #f3f4f6;
    --success:     #16a34a;
    --success-lt:  rgba(22,163,74,0.08);
    --warning:     #d97706;
    --warning-lt:  rgba(217,119,6,0.08);
    --error:       #dc2626;
    --error-lt:    rgba(220,38,38,0.08);
    --info:        #2563eb;
    --info-lt:     rgba(37,99,235,0.08);
    --font:       'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --radius-xs:   4px;
    --radius-sm:   8px;
    --radius-md:   12px;
    --radius-lg:   16px;
    --shadow-sm:   0 1px 6px rgba(0,0,0,0.06);
    --shadow:      0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:   0 6px 28px rgba(0,0,0,0.12);
    --shadow-lg:   0 12px 48px rgba(0,0,0,0.14);
    --trans:       0.18s cubic-bezier(0.4,0,0.2,1);
    --hdr-h:       60px;
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
    overflow-x: hidden;
}

::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--gray-300); border-radius:99px; }
::-webkit-scrollbar-thumb:hover { background:var(--orange); }

/* ─── HEADER ─────────────────────────────────────────────────────────────── */
.hdr {
    position:sticky; top:0; z-index:400;
    background:var(--white);
    border-bottom:2.5px solid var(--orange);
    box-shadow:0 2px 20px rgba(0,0,0,0.08);
    height:var(--hdr-h);
}
.hdr-inner {
    max-width:1800px; margin:0 auto;
    padding:0 1.8rem; height:100%;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
.brand-logo { height:30px; width:auto; }
.hdr-right { display:flex; align-items:center; gap:8px; }
.hdr-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 15px; border-radius:var(--radius-sm);
    font-family:var(--font); font-weight:700; font-size:12px; letter-spacing:.03em;
    text-decoration:none;
    border:1.5px solid var(--gray-200);
    background:var(--white); color:var(--gray-500);
    cursor:pointer; transition:var(--trans);
}
.hdr-btn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.hdr-btn.active { background:var(--orange); border-color:var(--orange); color:var(--white); }
.hdr-btn.active:hover { background:var(--orange-dk); }
.avatar {
    width:34px; height:34px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:12px;
    box-shadow:0 2px 8px rgba(242,128,24,0.35);
}

/* ─── PAGE SHELL ─────────────────────────────────────────────────────────── */
.page-shell { max-width:1800px; margin:0 auto; padding:0 1.8rem 6rem; }

/* ─── HERO BANNER ────────────────────────────────────────────────────────── */
.hero-banner {
    background:var(--white);
    border-bottom:1px solid var(--gray-100);
    padding:1rem 0 0.9rem;
    margin-bottom:1.8rem;
    display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap;
}
.hero-eyebrow {
    font-size:9px; font-weight:800; color:var(--orange);
    letter-spacing:.22em; text-transform:uppercase;
    margin-bottom:5px; display:flex; align-items:center; gap:6px;
}
.hero-eyebrow::before { content:''; width:16px; height:2px; background:var(--orange); border-radius:2px; }
.hero-title {
    font-size:clamp(22px,3vw,34px); font-weight:900;
    color:var(--gray-900); letter-spacing:-.02em; line-height:1;
}
.hero-title span { color:var(--orange); }
.hero-sub { font-size:12px; font-weight:500; color:var(--gray-400); margin-top:5px; }
.hero-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

/* ─── DRAFT BADGE ─────────────────────────────────────────────────────────── */
.draft-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--warning-lt); border:1.5px solid var(--warning);
    color:var(--warning); border-radius:20px;
    padding:4px 12px; font-size:10px; font-weight:800;
    letter-spacing:.06em; text-transform:uppercase;
    animation:pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.6;} }

/* ─── ORDER ID PILL ──────────────────────────────────────────────────────── */
.oid-pill {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--orange-lt); border:1px solid rgba(242,128,24,0.25);
    color:var(--orange); border-radius:var(--radius-sm);
    padding:3px 10px; font-size:13px; font-weight:900; letter-spacing:.03em;
}
.oid-pill i { font-size:10px; }

/* ─── SUMMARY BOX ─────────────────────────────────────────────────────────── */
.summary-box {
    background:var(--white); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md); padding:1.3rem 1.5rem;
    margin-bottom:1.5rem; box-shadow:var(--shadow-sm);
    border-left:4px solid var(--orange);
}
.summary-box-title {
    font-size:10.5px; font-weight:800; color:var(--gray-500);
    text-transform:uppercase; letter-spacing:.12em;
    display:flex; align-items:center; gap:6px; margin-bottom:1rem;
}
.summary-box-title i { color:var(--orange); font-size:10px; }
.summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; }
.summary-item { display:flex; flex-direction:column; }
.summary-label { font-size:9.5px; font-weight:700; color:var(--gray-400); text-transform:uppercase; letter-spacing:.10em; margin-bottom:3px; }
.summary-value { font-size:20px; font-weight:900; color:var(--gray-900); letter-spacing:-.02em; }

/* ─── STATUS BADGE ───────────────────────────────────────────────────────── */
.status-badge {
    display:inline-flex; align-items:center; padding:3px 10px;
    border-radius:20px; font-size:10px; font-weight:800;
    text-transform:uppercase; letter-spacing:.06em;
}
.status-pending  { background:rgba(217,119,6,0.10);  color:var(--warning); border:1px solid rgba(217,119,6,0.25); }
.status-in-process { background:var(--info-lt);      color:var(--info);    border:1px solid rgba(37,99,235,0.25); }
.status-closed   { background:var(--success-lt);     color:var(--success); border:1px solid rgba(22,163,74,0.25); }

/* ─── COMPARISON SECTION ──────────────────────────────────────────────────── */
.compare-section {
    display:none;
    background:var(--white); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md); padding:1.3rem 1.5rem;
    margin-bottom:1.5rem; box-shadow:var(--shadow-sm);
    border-left:4px solid var(--info);
}
.compare-section.show { display:block; animation:fadeUp .3s ease; }
@keyframes fadeUp { from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);} }
.compare-title { font-size:10.5px; font-weight:800; color:var(--gray-500); text-transform:uppercase; letter-spacing:.12em; display:flex; align-items:center; gap:6px; margin-bottom:1rem; }
.compare-title i { color:var(--info); font-size:10px; }
.compare-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
.compare-col h4 { font-size:11px; font-weight:800; color:var(--gray-700); text-transform:uppercase; letter-spacing:.10em; display:flex; align-items:center; gap:5px; margin-bottom:10px; }
.compare-col h4 i { font-size:10px; }
.compare-tile {
    background:var(--gray-50); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-sm); padding:10px 13px; margin-bottom:8px;
}
.compare-tile.highlight { border-color:var(--warning); background:var(--warning-lt); }
.compare-tile-label { font-size:9.5px; font-weight:700; color:var(--gray-400); text-transform:uppercase; letter-spacing:.10em; margin-bottom:3px; }
.compare-tile-value { font-size:18px; font-weight:900; color:var(--gray-900); letter-spacing:-.02em; }
.compare-tile-value.changed { color:var(--warning); }
.compare-tile-value.ok { color:var(--success); }

/* ─── CARD ───────────────────────────────────────────────────────────────── */
.card {
    background:var(--white); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md); overflow:hidden;
    box-shadow:var(--shadow-sm); margin-bottom:1.5rem;
}
.card-header {
    padding:.9rem 1.3rem; border-bottom:1.5px solid var(--gray-100);
    background:var(--gray-50);
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.card-title {
    font-size:12px; font-weight:800; color:var(--gray-700);
    letter-spacing:.07em; text-transform:uppercase;
    display:flex; align-items:center; gap:8px;
}
.card-title-icon {
    width:26px; height:26px; border-radius:var(--radius-xs);
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-size:10.5px; flex-shrink:0;
}
.card-body { padding:1.3rem; }

/* ─── ITEM ROW ───────────────────────────────────────────────────────────── */
.item-row {
    display:grid;
    grid-template-columns:2fr 2fr 1fr auto;
    gap:1rem;
    padding:1rem 1.1rem;
    background:var(--gray-50);
    border:1.5px solid var(--gray-200);
    border-radius:var(--radius-sm);
    margin-bottom:.75rem;
    align-items:end;
    transition:background var(--trans), border-color var(--trans), box-shadow var(--trans);
}
.item-row:hover { background:var(--orange-lt); border-color:rgba(242,128,24,0.25); }
.item-row.modified {
    border-color:var(--warning);
    background:var(--warning-lt);
    box-shadow:0 0 0 3px rgba(217,119,6,0.10);
}

/* ─── FORM GROUP ─────────────────────────────────────────────────────────── */
.fg { display:flex; flex-direction:column; }
.fg label {
    display:flex; align-items:center; gap:5px;
    margin-bottom:4px;
    font-size:9.5px; font-weight:700; color:var(--gray-500);
    text-transform:uppercase; letter-spacing:.09em;
}
.fg label i { color:var(--orange); font-size:8.5px; }
.fg label i.success-ico { color:var(--success); }
.fg input {
    padding:7px 10px;
    border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12.5px; font-weight:600;
    color:var(--gray-700); background:var(--white);
    transition:var(--trans); outline:none;
}
.fg input:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
.fg input[readonly] { background:var(--gray-100); color:var(--gray-500); cursor:default; }
.fg input.editable {
    border-color:var(--success);
    background:var(--success-lt);
}
.fg input.editable:focus { border-color:var(--success); box-shadow:0 0 0 3px rgba(22,163,74,0.15); }
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
input[type=number] { -moz-appearance:textfield; }

/* ─── BUTTONS ────────────────────────────────────────────────────────────── */
.btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 16px; border:none; border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12px; font-weight:800;
    letter-spacing:.05em; text-transform:uppercase;
    cursor:pointer; text-decoration:none; transition:var(--trans);
}
.btn-sm { padding:6px 12px; font-size:11px; }
.btn-orange { background:var(--orange); color:var(--white); box-shadow:0 3px 12px rgba(242,128,24,0.28); }
.btn-orange:hover { background:var(--orange-dk); transform:translateY(-1px); box-shadow:0 5px 18px rgba(242,128,24,0.36); }
.btn-ghost {
    background:var(--white); color:var(--gray-500);
    border:1.5px solid var(--gray-200);
}
.btn-ghost:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.btn-success-solid { background:var(--success); color:var(--white); box-shadow:0 3px 10px rgba(22,163,74,0.25); }
.btn-success-solid:hover { background:#15803d; transform:translateY(-1px); }
.btn-danger-solid { background:var(--error); color:var(--white); }
.btn-danger-solid:hover { background:#b91c1c; transform:translateY(-1px); }
.btn-warning-solid { background:var(--warning); color:var(--white); }
.btn-warning-solid:hover { background:#b45309; transform:translateY(-1px); }

/* ─── FORM ACTIONS ───────────────────────────────────────────────────────── */
.form-actions {
    display:flex; gap:10px; justify-content:space-between;
    padding-top:1.3rem; border-top:1.5px solid var(--gray-100);
    margin-top:.5rem; flex-wrap:wrap;
}

/* ─── CHANGES INDICATOR ──────────────────────────────────────────────────── */
.changes-indicator {
    position:fixed; bottom:1.5rem; right:1.5rem; z-index:500;
    background:var(--warning); color:var(--white);
    padding:10px 16px; border-radius:var(--radius-sm);
    box-shadow:var(--shadow-md);
    display:none; align-items:center; gap:8px;
    font-family:var(--font); font-size:11.5px; font-weight:800;
    letter-spacing:.05em; text-transform:uppercase;
    animation:bounceIn .4s cubic-bezier(.34,1.56,.64,1);
}
.changes-indicator.active { display:flex; }
@keyframes bounceIn { from{transform:scale(0.6);}to{transform:scale(1);} }
.changes-indicator i { font-size:13px; }

/* ─── EMPTY STATE ─────────────────────────────────────────────────────────── */
.empty-state { text-align:center; padding:3.5rem 2rem; }
.empty-state i { font-size:3rem; color:var(--gray-300); display:block; margin-bottom:14px; }
.empty-state h3 { font-size:16px; font-weight:800; color:var(--gray-700); }
.empty-state p  { font-size:12.5px; color:var(--gray-400); margin-top:5px; font-weight:500; }

/* ─── RESPONSIVE ─────────────────────────────────────────────────────────── */
@media(max-width:900px) {
    .item-row { grid-template-columns:1fr 1fr; }
}
@media(max-width:600px) {
    .page-shell { padding:0 1rem 6rem; }
    .item-row { grid-template-columns:1fr; }
    .compare-grid { grid-template-columns:1fr; }
    .hero-banner { flex-direction:column; align-items:flex-start; }
    .changes-indicator { left:1rem; right:1rem; }
}
</style>
</head>
<body>

<!-- ═══════════════════════════════ HEADER ════════════════════════════════ -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-right">
            <a href="view_order_details.php?oid=<?php echo urlencode($orderId); ?>" class="hdr-btn">
                <i class="fas fa-eye"></i> View Original
            </a>
            <a href="order-history.php" class="hdr-btn">
                <i class="fas fa-arrow-left"></i> Orders
            </a>
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        </div>
    </div>
</header>

<!-- ═══════════════════════════════ PAGE SHELL ════════════════════════════ -->
<div class="page-shell">

    <!-- HERO -->
    <div class="hero-banner">
        <div>
            <div class="hero-eyebrow">Draft Mode</div>
            <div class="hero-title">
                Edit Order <span>#<?php echo htmlentities($order['order_id']); ?></span>
            </div>
            <div class="hero-sub" style="display:flex;align-items:center;gap:10px;margin-top:7px;flex-wrap:wrap;">
                <span>Modify quantities — database remains unchanged.</span>
                <span class="draft-badge"><i class="fas fa-exclamation-triangle"></i> Changes Not Saved</span>
            </div>
        </div>
        <div class="hero-actions">
            <button type="button" id="createNewOrderBtn" onclick="createNewOrderWithData()" class="btn btn-orange">
                <i class="fas fa-plus-circle"></i> Create New Order
            </button>
            <button type="button" onclick="resetToOriginal()" class="btn btn-ghost">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </div>

    <!-- ORIGINAL SUMMARY -->
    <div class="summary-box">
        <div class="summary-box-title">
            <i class="fas fa-database"></i>
            Original Order (Database)
            <span class="oid-pill"><i class="fas fa-hashtag"></i><?php echo htmlentities($order['order_id']); ?></span>
        </div>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-label">Order Date</span>
                <span class="summary-value" style="font-size:15px;"><?php echo date('M j, Y', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Items</span>
                <span class="summary-value" id="originalItemsCount"><?php echo htmlentities($order['total_items']); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Quantity</span>
                <span class="summary-value" id="originalQuantity"><?php echo htmlentities($order['total_quantity']); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Status</span>
                <span class="summary-value">
                    <?php
                    $status      = $order['status'] ?? 'pending';
                    $statusClass = str_replace(' ', '-', $status);
                    echo '<span class="status-badge status-' . htmlentities($statusClass) . '">' . htmlentities(ucfirst($status)) . '</span>';
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- LIVE COMPARISON (shown when changes made) -->
    <div class="compare-section" id="compareSection">
        <div class="compare-title"><i class="fas fa-chart-bar"></i>Live Preview Comparison</div>
        <div class="compare-grid">
            <div class="compare-col">
                <h4><i class="fas fa-box"></i> Total Items</h4>
                <div class="compare-tile">
                    <div class="compare-tile-label">Original</div>
                    <div class="compare-tile-value" id="compOriginalItems"><?php echo htmlentities($order['total_items']); ?></div>
                </div>
                <div class="compare-tile highlight">
                    <div class="compare-tile-label">Preview</div>
                    <div class="compare-tile-value" id="compPreviewItems">—</div>
                </div>
            </div>
            <div class="compare-col">
                <h4><i class="fas fa-boxes"></i> Total Quantity</h4>
                <div class="compare-tile">
                    <div class="compare-tile-label">Original</div>
                    <div class="compare-tile-value" id="compOriginalQty"><?php echo htmlentities($order['total_quantity']); ?></div>
                </div>
                <div class="compare-tile highlight">
                    <div class="compare-tile-label">Preview</div>
                    <div class="compare-tile-value" id="compPreviewQty">—</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ITEMS CARD -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-title-icon"><i class="fas fa-boxes"></i></div>
                Order Items — Quantity Editable Only
            </div>
            <span style="font-size:11px;font-weight:700;color:var(--gray-400);">
                <i class="fas fa-lock" style="color:var(--orange);margin-right:3px;font-size:9px;"></i>
                Item code &amp; details are read-only
            </span>
        </div>
        <div class="card-body">
            <div id="itemsContainer">
                <?php
                $itemCounter = 0;
                foreach ($orderItems as $item):
                    $itemCounter++;
                    $detailsText = ($item['brand'] && $item['t_size'])
                        ? $item['brand'] . ' — ' . $item['t_size']
                        : 'N/A';
                ?>
                <div class="item-row" id="item-<?php echo $itemCounter; ?>"
                     data-original-qty="<?php echo htmlentities($item['quantity']); ?>"
                     data-original-price="<?php echo htmlentities($item['unit_price']); ?>">

                    <div class="fg">
                        <label><i class="fas fa-barcode"></i>Item Code</label>
                        <input type="text" value="<?php echo htmlentities($item['icode']); ?>" readonly>
                        <input type="hidden" class="item-icode" value="<?php echo htmlentities($item['icode']); ?>">
                    </div>

                    <div class="fg">
                        <label><i class="fas fa-tag"></i>Brand / Size</label>
                        <input type="text" value="<?php echo htmlentities($detailsText); ?>" readonly>
                    </div>

                    <div class="fg">
                        <label><i class="fas fa-edit success-ico"></i>Quantity</label>
                        <input type="number" class="item-qty editable" min="1"
                               value="<?php echo htmlentities($item['quantity']); ?>"
                               onchange="updatePreview(<?php echo $itemCounter; ?>)"
                               oninput="updatePreview(<?php echo $itemCounter; ?>)">
                    </div>

                    <div class="fg" style="justify-content:flex-end;">
                        <label>&nbsp;</label>
                        <button type="button" onclick="removeItemFromPreview(<?php echo $itemCounter; ?>)" class="btn btn-danger-solid btn-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($orderItems)): ?>
                <div id="emptyState" class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No items in this order</h3>
                    <p>You can still use "Create New Order" to create an empty order template.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" onclick="resetToOriginal()" class="btn btn-warning-solid">
                    <i class="fas fa-undo"></i> Reset to Original
                </button>
                <button type="button" id="createNewOrderBtnBottom" onclick="createNewOrderWithData()" class="btn btn-orange">
                    <i class="fas fa-plus-circle"></i> Create New Order with This Data
                </button>
            </div>
        </div>
    </div>

</div><!-- /page-shell -->

<!-- ═══════════════════════════════ CHANGES INDICATOR ════════════════════ -->
<div class="changes-indicator" id="changesIndicator">
    <i class="fas fa-exclamation-triangle"></i>
    Draft — changes not saved
</div>

<!-- ═══════════════════════════════ JS ═══════════════════════════════════ -->
<script>
const originalData = {
    items:         <?php echo json_encode($orderItems); ?>,
    status:        '<?php echo addslashes($order['status']); ?>',
    orderId:       '<?php echo addslashes($order['order_id']); ?>',
    totalItems:    <?php echo (int)$order['total_items']; ?>,
    totalQuantity: <?php echo (int)$order['total_quantity']; ?>
};

let itemIdCounter = <?php echo $itemCounter; ?>;
let hasChanges    = false;

/* ── Mark changed ─────────────────────────────────────────────────────── */
function markAsModified() {
    hasChanges = true;
    document.getElementById('changesIndicator').classList.add('active');
    document.getElementById('compareSection').classList.add('show');
    updatePreviewSummary();
}

/* ── Update row highlight ─────────────────────────────────────────────── */
function updatePreview(rowId) {
    const row = document.getElementById(`item-${rowId}`);
    if (!row) return;
    const originalQty = parseInt(row.dataset.originalQty) || 0;
    const currentQty  = parseInt(row.querySelector('.item-qty').value) || 0;
    row.classList.toggle('modified', currentQty !== originalQty);
    markAsModified();
}

/* ── Remove item row ──────────────────────────────────────────────────── */
function removeItemFromPreview(rowId) {
    const row = document.getElementById(`item-${rowId}`);
    if (!row) return;
    if (!confirm('Remove this item from the preview? (Original order is unchanged.)')) return;
    row.remove();
    markAsModified();
    const container = document.getElementById('itemsContainer');
    if (!container.querySelector('.item-row')) {
        container.innerHTML = `<div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No items in preview</h3>
            <p>"Create New Order" will still work — it will create an empty order template.</p>
        </div>`;
    }
}

/* ── Update comparison panel ──────────────────────────────────────────── */
function updatePreviewSummary() {
    const rows  = document.querySelectorAll('.item-row');
    let totalItems = rows.length, totalQty = 0;
    rows.forEach(r => { const q = r.querySelector('.item-qty'); if (q) totalQty += parseInt(q.value) || 0; });

    const elItems = document.getElementById('compPreviewItems');
    const elQty   = document.getElementById('compPreviewQty');
    elItems.textContent = totalItems;
    elQty.textContent   = totalQty;
    elItems.className = 'compare-tile-value ' + (totalItems !== originalData.totalItems ? 'changed' : 'ok');
    elQty.className   = 'compare-tile-value ' + (totalQty   !== originalData.totalQuantity ? 'changed' : 'ok');
}

/* ── Reset to original ────────────────────────────────────────────────── */
function resetToOriginal() {
    if (!confirm('Reset all preview changes and reload original order data?')) return;
    window.location.reload();
}

/* ── Build URL with preview data ──────────────────────────────────────── */
function getPreviewDataAsURL(baseUrl) {
    const rows  = document.querySelectorAll('.item-row');
    const items = [];
    rows.forEach(row => {
        const ic = row.querySelector('.item-icode');
        const qt = row.querySelector('.item-qty');
        if (ic && qt) {
            const qty = parseInt(qt.value) || 0;
            if (qty > 0) items.push({ icode: ic.value, qty });
        }
    });
    const oidParam = encodeURIComponent(originalData.orderId);
    if (!items.length) return `${baseUrl}?source=preview&original_oid=${oidParam}`;
    return `${baseUrl}?items=${encodeURIComponent(JSON.stringify(items))}&source=preview&original_oid=${oidParam}`;
}

/* ── Create new order ──────────────────────────────────────────────────── */
function createNewOrderWithData() {
    const rows = document.querySelectorAll('.item-row');
    if (!rows.length) {
        if (confirm('No items to include. Create an empty order template?')) {
            window.location.href = 'create_new_revise.php?source=preview&original_oid=' + encodeURIComponent(originalData.orderId);
        }
        return;
    }
    if (confirm('Create a new order using this preview data?\n\nYou will be redirected to the order creation page with items pre-filled.')) {
        window.location.href = getPreviewDataAsURL('create_new_revise.php');
    }
}

/* ── Load items from URL ──────────────────────────────────────────────── */
function loadPreviewFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('items')) return;
    let items;
    try { items = JSON.parse(decodeURIComponent(urlParams.get('items'))); }
    catch (e) { return; }
    if (!confirm('This URL contains preview data. Load it?')) return;
    const container = document.getElementById('itemsContainer');
    container.innerHTML = '';
    items.forEach(item => {
        itemIdCounter++;
        const div = document.createElement('div');
        div.className      = 'item-row modified';
        div.id             = `item-${itemIdCounter}`;
        div.dataset.originalQty   = '0';
        div.dataset.originalPrice = '0';
        div.innerHTML = `
            <div class="fg">
                <label><i class="fas fa-barcode"></i>Item Code</label>
                <input type="text" value="${escHtml(item.icode)}" readonly>
                <input type="hidden" class="item-icode" value="${escHtml(item.icode)}">
            </div>
            <div class="fg">
                <label><i class="fas fa-tag"></i>Brand / Size</label>
                <input type="text" value="Loaded from URL" readonly>
            </div>
            <div class="fg">
                <label><i class="fas fa-edit success-ico"></i>Quantity</label>
                <input type="number" class="item-qty editable" min="1" value="${parseInt(item.qty)||1}"
                       onchange="updatePreview(${itemIdCounter})" oninput="updatePreview(${itemIdCounter})">
            </div>
            <div class="fg" style="justify-content:flex-end;">
                <label>&nbsp;</label>
                <button type="button" onclick="removeItemFromPreview(${itemIdCounter})" class="btn btn-danger-solid btn-sm">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        container.appendChild(div);
    });
    markAsModified();
    alert('Preview data loaded from URL.');
    const cleanUrl = window.location.href.split('?')[0] + '?oid=' + encodeURIComponent(originalData.orderId);
    window.history.replaceState({}, document.title, cleanUrl);
}

function escHtml(str) {
    return String(str).replace(/[&<>"']/g, m =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}

function ensureButtonsEnabled() {
    ['createNewOrderBtn','createNewOrderBtnBottom'].forEach(id => {
        const b = document.getElementById(id);
        if (b) { b.disabled = false; b.style.pointerEvents = 'all'; b.style.opacity = '1'; }
    });
}

/* ── Init ─────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    ensureButtonsEnabled();
    loadPreviewFromURL();
    document.getElementById('changesIndicator').classList.add('active');
    setInterval(ensureButtonsEnabled, 1000);
    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) { e.returnValue = 'You have unsaved preview changes.'; }
    });
});

document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') { e.preventDefault(); resetToOriginal(); }
});
</script>
</body>
</html>