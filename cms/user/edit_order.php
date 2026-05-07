<?php
session_start();
include('include/config.php');

if (empty($_SESSION['id'])) {
    header('location:index.php');
    exit();
}

$orderId = intval($_GET['oid']);
$userId  = $_SESSION['id'];
$message = '';
$messageType = '';

$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
if (!$queryUser) { die("User query failed: " . mysqli_error($con)); }
$userData = mysqli_fetch_assoc($queryUser);
if (!$userData) { header('location:index.php'); exit; }

$stmt = $con->prepare("
    SELECT tor.order_id, tor.customer_id, tor.order_date, tor.status, tor.total_items, tor.total_quantity,
           u.fullName AS name
    FROM tire_orders tor
    JOIN users u ON u.id = tor.customer_id
    WHERE tor.order_id = ? AND tor.customer_id = ?
");
if (!$stmt) { die("Order prepare failed: " . $con->error); }
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();
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
if (!$stmtItems) { die("Items prepare failed: " . $con->error); }
$stmtItems->bind_param("i", $orderId);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();

$orderItems = [];
while ($item = $itemsResult->fetch_assoc()) {
    $orderItems[] = $item;
}
$stmtItems->close();

/* -- Initials --------------------------------------------------------------- */
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false) {
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
}
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
    --success:      #16a34a;
    --success-lt:   rgba(22,163,74,0.08);
    --danger:       #dc2626;
    --danger-lt:    rgba(220,38,38,0.08);
    --info:         #2563eb;
    --info-lt:      rgba(37,99,235,0.08);
    --warning:      #d97706;
    --warning-lt:   rgba(217,119,6,0.08);
    --font:        'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --radius-xs:    4px;
    --radius-sm:    8px;
    --radius-md:    12px;
    --radius-lg:    16px;
    --shadow-sm:    0 1px 6px rgba(0,0,0,0.06);
    --shadow:       0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:    0 6px 28px rgba(0,0,0,0.12);
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
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
}

/* ─── SCROLLBAR ──────────────────────────────────────────────────────────── */
::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--gray-300); border-radius:99px; }
::-webkit-scrollbar-thumb:hover { background:var(--orange); }

/* ─── HEADER ─────────────────────────────────────────────────────────────── */
.hdr {
    position:sticky; top:0; z-index:400;
    background: var(--white);
    border-bottom: 2.5px solid var(--orange);
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    height: var(--hdr-h);
}
.hdr-inner {
    max-width: 1400px; margin: 0 auto;
    padding: 0 1.8rem;
    height: 100%;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
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
    cursor:pointer; transition:var(--trans); font-family:var(--font);
}
.hdr-btn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.avatar {
    width:34px; height:34px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:12px;
    box-shadow:0 2px 8px rgba(242,128,24,0.35);
}

/* ─── PAGE BODY ──────────────────────────────────────────────────────────── */
.page-wrap {
    max-width: 1400px; margin: 0 auto;
    padding: 2rem 1.8rem 6rem;
}

/* ─── HERO ───────────────────────────────────────────────────────────────── */
.page-hero {
    background: var(--white);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 1.6rem 2rem;
    margin-bottom: 1.5rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;
    box-shadow: var(--shadow-sm);
    animation: fadeUp .35s ease;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(-10px);} to{opacity:1;transform:translateY(0);} }
.hero-left { display:flex; flex-direction:column; gap:8px; }
.hero-eyebrow {
    font-size:9px; font-weight:800; color:var(--orange);
    letter-spacing:.22em; text-transform:uppercase;
    display:flex; align-items:center; gap:6px;
}
.hero-eyebrow::before { content:''; width:16px; height:2px; background:var(--orange); border-radius:2px; }
.hero-title {
    font-size:clamp(22px,3vw,34px); font-weight:900;
    color:var(--gray-900); letter-spacing:-.02em; line-height:1.1;
}
.hero-title span { color:var(--orange); }
.hero-sub { font-size:12px; font-weight:500; color:var(--gray-400); }
.draft-pill {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--warning-lt); border:1.5px solid rgba(217,119,6,0.25);
    color:var(--warning); border-radius:20px;
    padding:4px 12px; font-size:10.5px; font-weight:800;
    letter-spacing:.06em; text-transform:uppercase;
    animation:pulseWarn 2s infinite;
}
@keyframes pulseWarn { 0%,100%{opacity:1;}50%{opacity:.65;} }
.hero-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

/* ─── STAT ROW ───────────────────────────────────────────────────────────── */
.stat-row {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(170px,1fr)); gap:12px;
    margin-bottom:1.5rem;
}
.stat-card {
    background:var(--white); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md); padding:14px 18px;
    box-shadow:var(--shadow-sm); transition:var(--trans);
    animation:fadeUp .4s ease;
}
.stat-card:hover { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
.stat-card-label {
    font-size:9.5px; font-weight:800; color:var(--gray-400);
    letter-spacing:.12em; text-transform:uppercase; margin-bottom:6px;
    display:flex; align-items:center; gap:5px;
}
.stat-card-label i { color:var(--orange); font-size:8.5px; }
.stat-card-value {
    font-size:1.7rem; font-weight:900; color:var(--gray-900);
    letter-spacing:-.03em; line-height:1;
}
.stat-card-value.preview { color:var(--orange); }

/* Status badge */
.status-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:5px 12px; border-radius:20px;
    font-size:11px; font-weight:700; text-transform:capitalize;
}
.status-pending  { background:var(--warning-lt); color:var(--warning); border:1px solid rgba(217,119,6,.2); }
.status-in-process { background:var(--info-lt); color:var(--info); border:1px solid rgba(37,99,235,.2); }
.status-closed   { background:var(--success-lt); color:var(--success); border:1px solid rgba(22,163,74,.2); }

/* ─── COMPARISON PANEL ────────────────────────────────────────────────────── */
.comparison-panel {
    display:none;
    background:var(--white); border:1.5px solid var(--orange);
    border-radius:var(--radius-md); padding:1.2rem 1.5rem;
    margin-bottom:1.5rem; box-shadow:0 0 0 3px var(--orange-glow);
    animation:fadeUp .3s ease;
}
.comparison-panel.show { display:block; }
.comparison-hd {
    font-size:10px; font-weight:800; color:var(--orange);
    letter-spacing:.12em; text-transform:uppercase;
    margin-bottom:14px; display:flex; align-items:center; gap:6px;
}
.comparison-grid {
    display:grid; grid-template-columns:1fr 1fr; gap:16px;
}
.cmp-col h5 {
    font-size:10px; font-weight:800; color:var(--gray-500);
    letter-spacing:.10em; text-transform:uppercase;
    margin-bottom:8px; display:flex; align-items:center; gap:5px;
}
.cmp-col h5 i { color:var(--orange); font-size:9px; }
.cmp-item {
    background:var(--gray-50); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-sm); padding:10px 14px; margin-bottom:8px;
}
.cmp-item-label { font-size:9.5px; font-weight:700; color:var(--gray-400); text-transform:uppercase; letter-spacing:.09em; }
.cmp-item-value { font-size:1.5rem; font-weight:900; color:var(--gray-900); line-height:1.2; margin-top:2px; }
.cmp-item.changed { border-color:var(--orange); background:var(--orange-lt); }
.cmp-item.changed .cmp-item-value { color:var(--orange); }
.cmp-item.match { border-color:var(--success); background:var(--success-lt); }
.cmp-item.match .cmp-item-value { color:var(--success); }

/* ─── CARD ───────────────────────────────────────────────────────────────── */
.card {
    background:var(--white); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-lg); overflow:hidden;
    box-shadow:var(--shadow-sm); margin-bottom:1.5rem;
    animation:fadeUp .4s ease;
}
.card-hd {
    padding:.9rem 1.4rem;
    border-bottom:1.5px solid var(--gray-100);
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    background:var(--gray-50);
}
.card-title {
    font-size:12px; font-weight:800; color:var(--gray-700);
    letter-spacing:.08em; text-transform:uppercase;
    display:flex; align-items:center; gap:8px;
}
.card-title-icon {
    width:26px; height:26px; border-radius:var(--radius-xs);
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-size:10.5px;
}
.card-body { padding:1.4rem; }

/* ─── ITEM ROWS ──────────────────────────────────────────────────────────── */
.item-row {
    display:grid;
    grid-template-columns: 2fr 2fr 1fr 1.3fr auto;
    gap:12px; padding:14px 16px;
    background:var(--gray-50); border:1.5px solid var(--gray-200);
    border-radius:var(--radius-md); margin-bottom:10px;
    align-items:end; transition:var(--trans);
}
.item-row:hover { background:var(--orange-lt); border-color:rgba(242,128,24,0.3); }
.item-row.modified { border-color:var(--orange); background:var(--orange-lt); }

/* Form groups inside rows */
.fg { display:flex; flex-direction:column; gap:4px; }
.fg label {
    font-size:9.5px; font-weight:800; color:var(--gray-500);
    text-transform:uppercase; letter-spacing:.09em;
    display:flex; align-items:center; gap:4px;
}
.fg label i { color:var(--orange); font-size:8.5px; }
.fg label i.green { color:var(--success); }
.fg input[type="text"],
.fg input[type="number"] {
    padding:7px 10px;
    border:1.5px solid var(--gray-200);
    border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12.5px; font-weight:600;
    color:var(--gray-700); background:var(--white);
    transition:var(--trans); outline:none;
    -moz-appearance:textfield;
}
.fg input::-webkit-outer-spin-button,
.fg input::-webkit-inner-spin-button { -webkit-appearance:none; }
.fg input[readonly] { background:var(--gray-100); color:var(--gray-400); cursor:not-allowed; }
.fg input.editable {
    border-color:var(--success); background:rgba(22,163,74,0.04);
    font-weight:800; color:var(--gray-900);
}
.fg input.editable:focus { border-color:var(--success); box-shadow:0 0 0 3px rgba(22,163,74,0.12); }

/* Row number */
.row-num {
    width:22px; height:22px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:9px; font-weight:900; flex-shrink:0; margin-bottom:2px;
}

/* ─── BUTTONS ─────────────────────────────────────────────────────────────── */
.btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 16px; border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12px; font-weight:800;
    letter-spacing:.04em; text-transform:uppercase;
    border:none; cursor:pointer; text-decoration:none;
    transition:var(--trans);
}
.btn-sm { padding:6px 12px; font-size:11px; }
.btn-orange { background:var(--orange); color:var(--white); }
.btn-orange:hover { background:var(--orange-dk); transform:translateY(-1px); box-shadow:0 5px 18px rgba(242,128,24,0.3); }
.btn-ghost {
    background:var(--white); color:var(--gray-500);
    border:1.5px solid var(--gray-200);
}
.btn-ghost:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.btn-success { background:var(--success); color:var(--white); }
.btn-success:hover { filter:brightness(1.08); transform:translateY(-1px); }
.btn-danger { background:var(--danger); color:var(--white); }
.btn-danger:hover { filter:brightness(1.08); transform:translateY(-1px); }
.btn-warning { background:var(--warning); color:var(--white); }
.btn-warning:hover { filter:brightness(1.08); transform:translateY(-1px); }
.btn-info { background:var(--info); color:var(--white); }
.btn-info:hover { filter:brightness(1.08); transform:translateY(-1px); }

/* Disabled override */
.btn:disabled, .btn[disabled] {
    opacity:1 !important; cursor:pointer !important;
    pointer-events:all !important;
}

/* ─── FORM ACTIONS ────────────────────────────────────────────────────────── */
.form-actions {
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    flex-wrap:wrap;
    padding-top:1.2rem; margin-top:1.2rem;
    border-top:1.5px solid var(--gray-100);
}

/* ─── EMPTY STATE ────────────────────────────────────────────────────────── */
.empty-state {
    text-align:center; padding:3.5rem 2rem; color:var(--gray-400);
}
.empty-state i { font-size:2.8rem; margin-bottom:12px; display:block; opacity:.4; }
.empty-state h3 { font-size:17px; font-weight:800; color:var(--gray-500); margin-bottom:6px; }
.empty-state p { font-size:12.5px; font-weight:500; }

/* ─── FLOATING CHANGES INDICATOR ─────────────────────────────────────────── */
.changes-dock {
    position:fixed; bottom:0; left:0; right:0; z-index:500;
    background:var(--white);
    border-top:3px solid var(--orange);
    box-shadow:0 -4px 32px rgba(0,0,0,0.12);
    padding:10px 1.8rem;
    display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
    transform:translateY(100%);
    transition:transform .3s cubic-bezier(.4,0,.2,1);
}
.changes-dock.open { transform:translateY(0); }
.changes-dock-left {
    display:flex; align-items:center; gap:10px;
}
.changes-dot {
    width:9px; height:9px; border-radius:50%; background:var(--orange);
    animation:pulse 1.2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1);}50%{opacity:.5;transform:scale(1.3);} }
.changes-dock-label {
    font-size:12px; font-weight:800; color:var(--gray-700);
    letter-spacing:.04em;
}
.changes-dock-sub { font-size:10.5px; color:var(--gray-400); font-weight:500; }
.changes-dock-actions { display:flex; gap:8px; }

/* ─── RESPONSIVE ─────────────────────────────────────────────────────────── */
@media(max-width:900px) {
    .item-row { grid-template-columns:1fr 1fr; }
    .comparison-grid { grid-template-columns:1fr; }
}
@media(max-width:600px) {
    .page-wrap { padding:1rem 1rem 6rem; }
    .item-row { grid-template-columns:1fr; }
    .stat-row { grid-template-columns:1fr 1fr; }
    .page-hero { flex-direction:column; align-items:flex-start; }
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
            <a href="view_order_details.php?oid=<?php echo htmlentities($orderId); ?>" class="hdr-btn">
                <i class="fas fa-eye"></i> View Original
            </a>
            <a href="order-history.php" class="hdr-btn">
                <i class="fas fa-arrow-left"></i> Orders
            </a>
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        </div>
    </div>
</header>

<!-- ═══════════════════════════════ PAGE ══════════════════════════════════ -->
<div class="page-wrap">

    <!-- HERO ─────────────────────────────────────────────────────────────── -->
    <div class="page-hero">
        <div class="hero-left">
            <div class="hero-eyebrow">Order Management</div>
            <div class="hero-title">Edit Order <span>#<?php echo htmlentities($order['order_id']); ?></span></div>
            <div class="hero-sub">Modify quantities only &mdash; database remains unchanged until you create a new order.</div>
            <div class="draft-pill"><i class="fas fa-exclamation-triangle"></i> Draft Mode — Not Saved</div>
        </div>
        <div class="hero-actions">
            <button type="button" onclick="createNewOrderWithData()" class="btn btn-orange" id="createNewOrderBtn">
                <i class="fas fa-plus-circle"></i> Create New Order
            </button>
            <button type="button" onclick="resetToOriginal()" class="btn btn-ghost">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </div>

    <!-- STAT ROW ─────────────────────────────────────────────────────────── -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-card-label"><i class="fas fa-calendar-alt"></i>Order Date</div>
            <div class="stat-card-value" style="font-size:1.1rem;">
                <?php echo date('M j, Y', strtotime($order['order_date'])); ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label"><i class="fas fa-layer-group"></i>Total Items</div>
            <div class="stat-card-value" id="originalItemsCount"><?php echo htmlentities($order['total_items']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label"><i class="fas fa-boxes"></i>Total Quantity</div>
            <div class="stat-card-value" id="originalQuantity"><?php echo htmlentities($order['total_quantity']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label"><i class="fas fa-tag"></i>Status</div>
            <div class="stat-card-value" style="font-size:1rem; margin-top:4px;">
                <?php
                $status = $order['status'] ?? 'pending';
                $cls    = 'status-' . str_replace(' ', '-', $status);
                echo '<span class="status-badge ' . htmlspecialchars($cls) . '">'
                   . htmlspecialchars(ucfirst($status)) . '</span>';
                ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label"><i class="fas fa-user"></i>Customer</div>
            <div class="stat-card-value" style="font-size:1rem; margin-top:4px;">
                <?php echo htmlspecialchars($order['name']); ?>
            </div>
        </div>
    </div>

    <!-- LIVE COMPARISON ──────────────────────────────────────────────────── -->
    <div class="comparison-panel" id="comparisonPanel">
        <div class="comparison-hd"><i class="fas fa-chart-bar"></i>Live Preview vs Original</div>
        <div class="comparison-grid">
            <div class="cmp-col">
                <h5><i class="fas fa-layer-group"></i>Total Items</h5>
                <div class="cmp-item">
                    <div class="cmp-item-label">Original</div>
                    <div class="cmp-item-value"><?php echo htmlentities($order['total_items']); ?></div>
                </div>
                <div class="cmp-item" id="cmpPreviewItemsBox">
                    <div class="cmp-item-label">Preview</div>
                    <div class="cmp-item-value" id="cmpPreviewItems">—</div>
                </div>
            </div>
            <div class="cmp-col">
                <h5><i class="fas fa-boxes"></i>Total Quantity</h5>
                <div class="cmp-item">
                    <div class="cmp-item-label">Original</div>
                    <div class="cmp-item-value"><?php echo htmlentities($order['total_quantity']); ?></div>
                </div>
                <div class="cmp-item" id="cmpPreviewQtyBox">
                    <div class="cmp-item-label">Preview</div>
                    <div class="cmp-item-value" id="cmpPreviewQty">—</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ORDER ITEMS CARD ─────────────────────────────────────────────────── -->
    <div class="card">
        <div class="card-hd">
            <div class="card-title">
                <div class="card-title-icon"><i class="fas fa-boxes"></i></div>
                Order Items &mdash; Quantity Editable Only
            </div>
            <button type="button" onclick="createNewOrderWithData()" class="btn btn-orange btn-sm" id="createNewOrderBtn2">
                <i class="fas fa-plus-circle"></i> Create New Order with This Data
            </button>
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
                <div class="item-row"
                     id="item-<?php echo $itemCounter; ?>"
                     data-original-qty="<?php echo htmlentities($item['quantity']); ?>"
                     data-original-price="<?php echo htmlentities($item['unit_price']); ?>">

                    <div class="fg">
                        <label><i class="fas fa-barcode"></i>Item Code</label>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span class="row-num"><?php echo $itemCounter; ?></span>
                            <input type="text" value="<?php echo htmlentities($item['icode']); ?>" readonly style="flex:1;">
                            <input type="hidden" class="item-icode" value="<?php echo htmlentities($item['icode']); ?>">
                        </div>
                    </div>

                    <div class="fg">
                        <label><i class="fas fa-tag"></i>Details</label>
                        <input type="text" value="<?php echo htmlentities($detailsText); ?>" readonly>
                    </div>

                    <div class="fg">
                        <label><i class="fas fa-edit green"></i>Quantity</label>
                        <input type="number" class="item-qty editable" min="1"
                               value="<?php echo htmlentities($item['quantity']); ?>"
                               onchange="updatePreview(<?php echo $itemCounter; ?>)"
                               oninput="updatePreview(<?php echo $itemCounter; ?>)">
                    </div>

                    <div class="fg">
                        <label><i class="fas fa-coins"></i>Unit Price</label>
                        <input type="number" class="item-price" step="0.01" min="0"
                               value="<?php echo htmlentities($item['unit_price']); ?>" readonly>
                    </div>

                    <div class="fg">
                        <label style="opacity:0;">&nbsp;</label>
                        <button type="button" onclick="removeItemFromPreview(<?php echo $itemCounter; ?>)"
                                class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($orderItems)): ?>
                <div class="empty-state" id="emptyState">
                    <i class="fas fa-inbox"></i>
                    <h3>No Items in This Order</h3>
                    <p>You can still use "Create New Order" to open an empty order template.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" onclick="resetToOriginal()" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Reset to Original
                    </button>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="order-history.php" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <button type="button" onclick="createNewOrderWithData()" class="btn btn-orange">
                        <i class="fas fa-paper-plane"></i> Create New Order
                    </button>
                </div>
            </div>
        </div>
    </div>

</div><!-- /page-wrap -->

<!-- ═══════════════════════════ CHANGES DOCK ═══════════════════════════════ -->
<div class="changes-dock" id="changesDock">
    <div class="changes-dock-left">
        <div class="changes-dot"></div>
        <div>
            <div class="changes-dock-label">Unsaved Draft Changes</div>
            <div class="changes-dock-sub">Database is unchanged — create a new order to save.</div>
        </div>
    </div>
    <div class="changes-dock-actions">
        <button type="button" onclick="resetToOriginal()" class="btn btn-ghost btn-sm">
            <i class="fas fa-undo"></i> Reset
        </button>
        <button type="button" onclick="createNewOrderWithData()" class="btn btn-orange btn-sm">
            <i class="fas fa-plus-circle"></i> Create New Order
        </button>
    </div>
</div>

<!-- ═══════════════════════════════ JS ════════════════════════════════════ -->
<script>
const originalData = {
    items:         <?php echo json_encode($orderItems); ?>,
    status:        '<?php echo addslashes($order['status']); ?>',
    totalItems:    <?php echo (int)$order['total_items']; ?>,
    totalQuantity: <?php echo (int)$order['total_quantity']; ?>
};

let itemIdCounter = <?php echo $itemCounter; ?>;
let hasChanges    = false;

/* ── helpers ─────────────────────────────────────────────────────────────── */
function esc(t) {
    return String(t).replace(/[&<>"']/g, m =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])
    );
}

function markAsModified() {
    hasChanges = true;
    document.getElementById('changesDock').classList.add('open');
    document.getElementById('comparisonPanel').classList.add('show');
    updatePreviewSummary();
}

/* ── per-row update ──────────────────────────────────────────────────────── */
function updatePreview(rowId) {
    const row = document.getElementById(`item-${rowId}`);
    if (!row) return;
    const originalQty = parseInt(row.dataset.originalQty) || 0;
    const currentQty  = parseInt(row.querySelector('.item-qty').value) || 0;
    row.classList.toggle('modified', currentQty !== originalQty);
    markAsModified();
}

/* ── remove row ──────────────────────────────────────────────────────────── */
function removeItemFromPreview(rowId) {
    const row = document.getElementById(`item-${rowId}`);
    if (!row) return;
    if (!confirm('Remove this item from the preview?\n(Original order in database is unchanged)')) return;
    row.remove();
    markAsModified();
    if (!document.querySelectorAll('.item-row').length) {
        document.getElementById('itemsContainer').innerHTML = `
            <div class="empty-state" id="emptyState">
                <i class="fas fa-inbox"></i>
                <h3>No Items in Preview</h3>
                <p>You can still use "Create New Order" to open an empty order template.</p>
            </div>`;
    }
}

/* ── live summary ────────────────────────────────────────────────────────── */
function updatePreviewSummary() {
    const rows   = document.querySelectorAll('.item-row');
    let totalQty = 0;
    rows.forEach(r => {
        const q = r.querySelector('.item-qty');
        if (q) totalQty += parseInt(q.value) || 0;
    });
    const totalItems = rows.length;

    const itemsEl = document.getElementById('cmpPreviewItems');
    const qtyEl   = document.getElementById('cmpPreviewQty');
    const itemsBox = document.getElementById('cmpPreviewItemsBox');
    const qtyBox   = document.getElementById('cmpPreviewQtyBox');

    itemsEl.textContent = totalItems;
    qtyEl.textContent   = totalQty;

    itemsBox.className = 'cmp-item ' + (totalItems !== originalData.totalItems ? 'changed' : 'match');
    qtyBox.className   = 'cmp-item ' + (totalQty   !== originalData.totalQuantity ? 'changed' : 'match');
}

/* ── reset ───────────────────────────────────────────────────────────────── */
function resetToOriginal() {
    if (!confirm('Reset all preview changes back to original order data?')) return;
    window.location.reload();
}

/* ── create new order ────────────────────────────────────────────────────── */
function createNewOrderWithData() {
    const rows = document.querySelectorAll('.item-row');

    if (!rows.length) {
        if (confirm('No items to include. Create an empty order template?')) {
            window.location.href = 'create_new_order.php?source=preview&original_oid=<?php echo $orderId; ?>';
        }
        return;
    }

    if (!confirm('Create a new order using this preview data?\n\nYou will be redirected to the order page with items pre-filled.')) return;

    try {
        window.location.href = getPreviewDataAsURL('create_new_order.php');
    } catch (err) {
        console.error(err);
        alert('Error preparing order data. Please try again.');
    }
}

function getPreviewDataAsURL(base) {
    const rows  = document.querySelectorAll('.item-row');
    const items = [];
    rows.forEach(row => {
        const ic  = row.querySelector('.item-icode');
        const qty = row.querySelector('.item-qty');
        if (ic && qty && parseInt(qty.value) > 0) {
            items.push({ icode: ic.value, qty: parseInt(qty.value) });
        }
    });
    if (!items.length) {
        return `${base}?source=preview&original_oid=<?php echo $orderId; ?>`;
    }
    return `${base}?items=${encodeURIComponent(JSON.stringify(items))}&source=preview&original_oid=<?php echo $orderId; ?>`;
}

/* ── load from URL ───────────────────────────────────────────────────────── */
function loadPreviewFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('items')) return;
    try {
        const itemsData = JSON.parse(decodeURIComponent(urlParams.get('items')));
        if (!confirm('This URL contains preview data. Load it?')) return;

        document.getElementById('itemsContainer').innerHTML = '';

        itemsData.forEach(item => {
            itemIdCounter++;
            const id  = itemIdCounter;
            const row = document.createElement('div');
            row.className = 'item-row modified';
            row.id        = `item-${id}`;
            row.dataset.originalQty   = '0';
            row.dataset.originalPrice = '0';
            row.innerHTML = `
                <div class="fg">
                    <label><i class="fas fa-barcode"></i>Item Code</label>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="row-num">${id}</span>
                        <input type="text" value="${esc(item.icode)}" readonly style="flex:1;">
                        <input type="hidden" class="item-icode" value="${esc(item.icode)}">
                    </div>
                </div>
                <div class="fg">
                    <label><i class="fas fa-tag"></i>Details</label>
                    <input type="text" value="Loaded from URL" readonly>
                </div>
                <div class="fg">
                    <label><i class="fas fa-edit green"></i>Quantity</label>
                    <input type="number" class="item-qty editable" min="1" value="${item.qty}"
                           onchange="updatePreview(${id})" oninput="updatePreview(${id})">
                </div>
                <div class="fg">
                    <label><i class="fas fa-coins"></i>Unit Price</label>
                    <input type="number" class="item-price" step="0.01" value="0" readonly>
                </div>
                <div class="fg">
                    <label style="opacity:0;">&nbsp;</label>
                    <button type="button" onclick="removeItemFromPreview(${id})" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>`;
            document.getElementById('itemsContainer').appendChild(row);
        });

        markAsModified();
        alert('Preview data loaded successfully!');

        const cleanUrl = `${window.location.pathname}?oid=<?php echo $orderId; ?>`;
        window.history.replaceState({}, document.title, cleanUrl);
    } catch (e) {
        console.error('Error loading preview from URL:', e);
        alert('Error loading preview data from URL.');
    }
}

/* ── init ────────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    /* Ensure all create-order buttons are enabled */
    ['createNewOrderBtn','createNewOrderBtn2'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) { btn.removeAttribute('disabled'); btn.style.pointerEvents='all'; }
    });

    loadPreviewFromURL();

    /* Show dock immediately so user knows it's draft mode */
    document.getElementById('changesDock').classList.add('open');

    window.addEventListener('beforeunload', function (e) {
        if (hasChanges) {
            const msg = 'You have unsaved preview changes. These are not saved to the database.';
            e.returnValue = msg;
            return msg;
        }
    });

    /* Keyboard shortcuts */
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') { e.preventDefault(); resetToOriginal(); }
    });
});
</script>
</body>
</html>