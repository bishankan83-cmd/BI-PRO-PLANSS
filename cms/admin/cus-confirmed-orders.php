<?php
session_start();
include('include/config.php');

if (empty($_SESSION['id'])) {
    header('location:index.php');
    exit();
}

date_default_timezone_set('Asia/Kolkata');

/* ── Params ── */
$acmRef  = isset($_GET['acm_ref']) ? (int)$_GET['acm_ref'] : 0;
$cusId   = isset($_GET['cus_id'])  ? (int)$_GET['cus_id']  : 0;

if (!$acmRef || !$cusId) {
    header('location:dashboard.php');
    exit();
}

/* ── Session user (ACM) ── */
$sessionId    = $_SESSION['id'];
$queryUser    = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '" . mysqli_real_escape_string($con, $sessionId) . "'");
$userData     = mysqli_fetch_assoc($queryUser);
if (!$userData) {
    header('location:index.php');
    exit;
}

/* ── Customer info ── */
$queryCus   = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '" . mysqli_real_escape_string($con, $cusId) . "'");
$cusData    = mysqli_fetch_assoc($queryCus);
$cusName    = $cusData ? htmlspecialchars($cusData['fullName'])    : 'Customer #' . $cusId;
$cusEmail   = $cusData ? htmlspecialchars($cusData['userEmail'])  : '';

/* ── Avatar initials (session user) ── */
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false)
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));

/* ── Customer avatar initials ── */
$cusInitials = strtoupper(substr($cusData ? $cusData['fullName'] : 'C', 0, 1));
if ($cusData && strpos($cusData['fullName'], ' ') !== false)
    $cusInitials .= strtoupper(substr($cusData['fullName'], strpos($cusData['fullName'], ' ') + 1, 1));

/* ── Fetch cus_confirmed orders for this customer ── */
$rows_data = [];

$sql = "
    SELECT
        tor.order_id,
        tor.invoice_no,
        tor.customer_id,
        tor.status,
        tor.total_items,
        tor.total_quantity,
        tor.created_at AS order_date,
        GROUP_CONCAT(DISTINCT toi.icode ORDER BY toi.icode SEPARATOR ',') AS item_codes
    FROM tire_orders tor
    LEFT JOIN tire_order_items toi ON toi.order_id = tor.order_id
    WHERE tor.customer_id = ?
      AND tor.status != 'revised'
    GROUP BY tor.order_id
    ORDER BY tor.created_at DESC
";

$stmt = $con->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $cusId);
    $stmt->execute();
    $res       = $stmt->get_result();
    $rows_data = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

/* ── Badge helper ── */
function getBadge($status) {
    switch (strtolower(trim($status))) {
        case 'pending':            return ['badge-pending',      'clock',          'Pending'];
        case 'in process':         return ['badge-inprocess',    'rotate',         'In Process'];
        case 'closed':             return ['badge-closed',       'check-circle',   'Closed'];
        case 'completed':          return ['badge-completed',    'check-double',   'Completed'];
        case 'complete':           return ['badge-completed',    'check-double',   'Complete'];
        case 'pi_confirm':         return ['badge-piconfirm',    'file-invoice',   'PI Confirmed'];
        case 'price_pending':      return ['badge-pricepend',    'hourglass-half', 'Price Pending'];
        case 'price pending':      return ['badge-pricepend',    'hourglass-half', 'Price Pending'];
        case 'cus_confirmed':      return ['badge-cusconfirmed', 'user-check',     'Cus. Confirmed'];
        case 'cus_confirm':        return ['badge-cusconfirmed', 'user-check',     'Cus. Confirmed'];
        case 'customer_confirmed': return ['badge-cusconfirmed', 'user-check',     'Cus. Confirmed'];
        case 'manager_confirm_disc_success': return ['badge-confirmed', 'check-double', 'Mgr. Confirmed'];
        case 'confirmed':          return ['badge-confirmed',    'check',          'Confirmed'];
        case 'share_planning':     return ['badge-inprocess',    'calendar-alt',   'In Planning'];
        case 'in progress':        return ['badge-inprocess',    'spinner',        'In Progress'];
        default:                   return ['badge-default',      'circle', ucwords(str_replace('_', ' ', $status))];
    }
}

/* ── Stats ── */
$totalOrders = count($rows_data);
$totalItems  = array_sum(array_column($rows_data, 'total_items'));
$totalQty    = array_sum(array_column($rows_data, 'total_quantity'));
$pendingCnt  = count(array_filter($rows_data, function($r) {
    return strtolower(trim($r['status'])) === 'pending';
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order History — ATIRE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@font-face { font-family:'SF UI Display'; font-weight:500; src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:600; src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:700; src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:800; src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:900; src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); }

:root {
    --orange:    #f28018;
    --orange-dk: #d06e10;
    --orange-lt: rgba(242,128,24,0.10);
    --orange-gl: rgba(242,128,24,0.22);
    --teal:      #5bc0be;
    --teal-dk:   #47a8a6;
    --teal-lt:   rgba(91,192,190,0.12);
    --amber:     #f59e0b;
    --amber-dk:  #d97706;
    --amber-lt:  rgba(245,158,11,0.10);
    --green:     #10b981;
    --green-lt:  rgba(16,185,129,0.10);
    --green-gl:  rgba(16,185,129,0.22);
    --bg:        #f0f2f5;
    --white:     #ffffff;
    --g50:       #f9fafb;
    --g100:      #f3f4f6;
    --g200:      #e5e7eb;
    --g300:      #d1d5db;
    --g400:      #9ca3af;
    --g500:      #6b7280;
    --g700:      #374151;
    --g900:      #111827;
    --font:     'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --r-sm:      8px;
    --r-md:      12px;
    --r-lg:      16px;
    --shadow:    0 1px 3px rgba(0,0,0,0.07), 0 4px 16px rgba(0,0,0,0.06);
    --tr:        0.16s ease;
    --hh:        62px;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family:var(--font);
    background:var(--bg);
    color:var(--g700);
    min-height:100vh;
    font-size:13px;
    line-height:1.5;
    -webkit-font-smoothing:antialiased;
}
::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--g300); border-radius:99px; }

/* ── HEADER ── */
.hdr {
    position:sticky; top:0; z-index:500;
    background:var(--white);
    border-bottom:2px solid var(--orange);
    box-shadow:0 2px 16px rgba(0,0,0,0.07);
    height:var(--hh);
}
.hdr-inner {
    max-width:1400px; margin:0 auto;
    padding:0 2rem; height:100%;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
.brand-logo { height:28px; }
.hdr-r { display:flex; align-items:center; gap:8px; }
.hbtn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 16px; border-radius:var(--r-sm);
    font-family:var(--font); font-weight:700; font-size:12px;
    text-decoration:none; border:1.5px solid var(--g200);
    background:var(--white); color:var(--g500);
    cursor:pointer; transition:var(--tr);
}
.hbtn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }
.hbtn.pri { background:var(--orange); color:var(--white); border-color:var(--orange); }
.hbtn.pri:hover { background:var(--orange-dk); }
.avatar {
    width:34px; height:34px; border-radius:50%;
    background:var(--orange); color:var(--white);
    display:flex; align-items:center; justify-content:center;
    font-weight:900; font-size:12px;
    box-shadow:0 2px 8px var(--orange-gl);
}

/* ── PAGE ── */
.wrap { max-width:1400px; margin:0 auto; padding:2rem 2rem 5rem; }

/* ── Customer Info Banner ── */
.cus-banner {
    display:flex; align-items:center; gap:14px;
    background:linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius:var(--r-md);
    padding:1rem 1.4rem;
    margin-bottom:1.4rem;
    box-shadow:0 4px 20px rgba(16,185,129,0.3);
    color:#fff;
}
.cus-banner-avatar {
    width:46px; height:46px; border-radius:50%;
    background:rgba(255,255,255,0.25);
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; font-weight:900; flex-shrink:0;
    border:2px solid rgba(255,255,255,0.4);
}
.cus-banner-text { flex:1; }
.cus-banner-label {
    font-size:9.5px; font-weight:800; letter-spacing:.18em; text-transform:uppercase;
    opacity:.8; margin-bottom:2px;
}
.cus-banner-name  { font-size:1.05rem; font-weight:900; margin-bottom:2px; }
.cus-banner-email { font-size:0.8rem; opacity:.85; }
.cus-banner-badge {
    display:inline-flex; align-items:center; gap:5px;
    background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.35);
    border-radius:20px; padding:4px 12px;
    font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase;
    flex-shrink:0;
}

/* ── HERO ── */
.hero { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.6rem; }
.eyebrow {
    font-size:10px; font-weight:800; color:var(--green);
    letter-spacing:.22em; text-transform:uppercase;
    margin-bottom:5px; display:flex; align-items:center; gap:6px;
}
.eyebrow::before { content:''; width:14px; height:2px; background:var(--green); border-radius:2px; }
.page-title {
    font-size:clamp(24px,3vw,36px); font-weight:900;
    color:var(--g900); letter-spacing:-.02em; line-height:1.1;
    display:flex; align-items:center; gap:10px; flex-wrap:wrap;
}
.page-title span.hl { color:var(--green); }
.fchip {
    display:inline-flex; align-items:center; gap:5px;
    background:var(--green); color:var(--white);
    padding:3px 12px; border-radius:20px;
    font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}
.sub { font-size:12px; color:var(--g400); font-weight:500; margin-top:5px; }

/* ── STATS ── */
.stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px; margin-bottom:1.4rem;
}
.scard {
    background:var(--white); border-radius:var(--r-md);
    border:1.5px solid var(--g200); padding:1rem 1.2rem;
    display:flex; align-items:center; gap:12px;
    box-shadow:var(--shadow); transition:var(--tr);
}
.scard:hover { border-color:var(--green); }
.sico {
    width:38px; height:38px; border-radius:var(--r-sm);
    background:var(--green-lt);
    display:flex; align-items:center; justify-content:center;
    font-size:15px; color:var(--green); flex-shrink:0;
}
.sico.or { background:var(--orange-lt); color:var(--orange); }
.sico.am { background:var(--amber-lt);  color:var(--amber);  }
.slabel { font-size:10px; font-weight:800; color:var(--g400); letter-spacing:.07em; text-transform:uppercase; margin-bottom:2px; }
.sval   { font-size:22px; font-weight:900; color:var(--g900); line-height:1; }

/* ── PANEL ── */
.panel {
    background:var(--white);
    border:1.5px solid var(--g200);
    border-radius:var(--r-lg);
    box-shadow:var(--shadow);
}

/* ── TOOLBAR ── */
.toolbar {
    padding:.9rem 1.4rem;
    border-bottom:1.5px solid var(--g100);
    display:flex; align-items:center; justify-content:space-between;
    gap:1rem; flex-wrap:wrap;
    background:var(--white);
    position:sticky; top:var(--hh); z-index:40;
}
.tb-l { display:flex; align-items:center; gap:10px; }
.tb-r { display:flex; align-items:center; gap:8px; }
.tb-ico {
    width:30px; height:30px; border-radius:6px;
    background:var(--green); color:var(--white);
    display:flex; align-items:center; justify-content:center; font-size:12px;
}
.tb-title { font-size:11px; font-weight:800; color:var(--g700); letter-spacing:.08em; text-transform:uppercase; }
.cpill {
    padding:3px 10px; border-radius:20px;
    font-size:10px; font-weight:700;
    background:var(--green-lt); color:var(--green);
    border:1px solid rgba(16,185,129,0.22);
}
.srch { position:relative; }
.srch input {
    padding:7px 32px 7px 12px;
    border:1.5px solid var(--g200); border-radius:var(--r-sm);
    font-family:var(--font); font-size:12.5px; font-weight:600;
    color:var(--g700); background:var(--white);
    outline:none; transition:var(--tr); width:220px;
}
.srch input:focus { border-color:var(--green); box-shadow:0 0 0 3px var(--green-gl); }
.srch i { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:var(--g400); font-size:11px; pointer-events:none; }

/* ── TABLE ── */
.tbl-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }

table.t {
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
    min-width:1160px;
}

table.t col.c0  { width:52px;  }
table.t col.c1  { width:115px; }
table.t col.c1b { width:130px; }
table.t col.c2  { width:150px; }
table.t col.c3  { width:auto;  }
table.t col.c4  { width:70px;  }
table.t col.c5  { width:70px;  }
table.t col.c6  { width:168px; }
table.t col.c7  { width:110px; }

table.t thead { background:var(--g50); }
table.t th {
    padding:10px 14px;
    text-align:left; font-size:10px; font-weight:800;
    color:var(--g400); letter-spacing:.12em; text-transform:uppercase;
    white-space:nowrap;
    border-bottom:2px solid var(--g200);
    border-right:1px solid var(--g200);
    overflow:hidden; text-overflow:ellipsis;
}
table.t th:last-child { border-right:none; }
table.t th.ctr { text-align:center; }
table.t th i.hi { color:var(--green); margin-right:5px; font-size:9px; }

table.t tbody tr { border-bottom:1px solid var(--g100); transition:background var(--tr); }
table.t tbody tr:last-child { border-bottom:none; }
table.t tbody tr:hover { background:rgba(16,185,129,0.04); }

table.t td {
    padding:13px 14px;
    font-size:13px; font-weight:500; color:var(--g700);
    vertical-align:middle;
    overflow:hidden;
}

table.t td.c0  { text-align:center; }
table.t td.c1  { white-space:nowrap; }
table.t td.c1b { white-space:nowrap; }
table.t td.c4  { text-align:center; white-space:nowrap; }
table.t td.c5  { text-align:center; white-space:nowrap; }
table.t td.c6  { white-space:nowrap; }
table.t td.c7  { text-align:center; white-space:nowrap; }

.rno {
    width:24px; height:24px; border-radius:50%;
    background:var(--g100);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:10px; font-weight:800; color:var(--g400);
}

.oid-pill {
    display:inline-block;
    background:var(--orange-lt);
    color:var(--orange);
    border:1px solid rgba(242,128,24,0.25);
    border-radius:6px;
    padding:3px 10px;
    font-size:13px; font-weight:900; letter-spacing:.02em;
    white-space:nowrap;
    max-width:100%; overflow:hidden; text-overflow:ellipsis;
}

.inv-pill {
    display:inline-block;
    background:#ede9fe;
    color:#5b21b6;
    border:1px solid rgba(91,33,182,0.2);
    border-radius:6px;
    padding:3px 10px;
    font-size:13px; font-weight:700; letter-spacing:.01em;
    white-space:nowrap;
    max-width:100%; overflow:hidden; text-overflow:ellipsis;
}

.d1 { font-weight:700; color:var(--g700); font-size:12.5px; white-space:nowrap; }
.d2 { font-size:11px; color:var(--g400); font-weight:500; margin-top:2px; white-space:nowrap; }

.codes { display:flex; flex-wrap:wrap; gap:4px; align-items:flex-start; }
.ctag {
    display:inline-block;
    background:var(--g100); color:var(--g700);
    border:1px solid var(--g200); border-radius:4px;
    padding:2px 7px;
    font-family:'Courier New', Courier, monospace;
    font-size:11px; font-weight:600; white-space:nowrap;
}

.nchip {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:32px; height:26px;
    background:var(--g100); border-radius:6px;
    font-size:13px; font-weight:800; color:var(--g900);
    padding:0 8px;
}

/* Status badges */
.badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 11px; border-radius:20px;
    font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase;
    white-space:nowrap; border:1px solid transparent;
}
.badge i { font-size:8px; }
.badge-pending      { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
.badge-inprocess    { background:#fef3c7; color:#92400e; border-color:#fcd34d; }
.badge-closed       { background:#dcfce7; color:#166534; border-color:#86efac; }
.badge-completed    { background:#ccfbf1; color:#0f766e; border-color:#5eead4; }
.badge-piconfirm    { background:#dbeafe; color:#1e40af; border-color:#93c5fd; }
.badge-pricepend    { background:#fef3c7; color:#92400e; border-color:#fcd34d; }
.badge-cusconfirmed { background:#ede9fe; color:#5b21b6; border-color:#c4b5fd; }
.badge-confirmed    { background:#dcfce7; color:#166534; border-color:#86efac; }
.badge-default      { background:var(--g100); color:var(--g500); border-color:var(--g200); }

/* ── Action button group ── */
.action-group {
    display:inline-flex;
    align-items:center;
    gap:6px;
    justify-content:center;
    flex-wrap:wrap;
}

.vbtn {
    display:inline-flex; align-items:center; gap:5px;
    padding:6px 14px; border-radius:var(--r-sm);
    background:var(--orange); color:var(--white);
    font-family:var(--font); font-size:11px; font-weight:800;
    letter-spacing:.04em; text-transform:uppercase;
    text-decoration:none; border:none; cursor:pointer;
    transition:var(--tr); box-shadow:0 2px 8px rgba(242,128,24,0.25);
    white-space:nowrap;
}
.vbtn:hover { background:var(--orange-dk); transform:translateY(-1px); }
.vbtn i { font-size:10px; }

/* Empty state */
.empty { text-align:center; padding:4rem 2rem; }
.empty-ico {
    width:58px; height:58px; border-radius:50%;
    background:var(--g100);
    display:inline-flex; align-items:center; justify-content:center;
    font-size:22px; color:var(--g300); margin-bottom:14px;
}
.empty h3 { font-size:16px; font-weight:800; color:var(--g700); margin-bottom:6px; }
.empty p  { font-size:12.5px; color:var(--g400); font-weight:500; margin-bottom:1.4rem; }
.empty-acts { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; }

.nores { display:none; text-align:center; padding:2rem; border-top:1px solid var(--g100); }
.nores i { font-size:1.8rem; color:var(--g300); display:block; margin-bottom:8px; }
.nores p  { font-size:13px; font-weight:600; color:var(--g400); }

/* Responsive */
@media(max-width:1000px) { .toolbar { position:static; } }
@media(max-width:768px)  { .stats { grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px) {
    .wrap { padding:1rem 1rem 5rem; }
    .hdr-inner { padding:0 1rem; }
    .toolbar { flex-direction:column; align-items:stretch; }
    .srch input { width:100%; }
    .hero { flex-direction:column; align-items:flex-start; }
    .stats { grid-template-columns:1fr 1fr; }
    .action-group { flex-direction:column; gap:4px; }
    .cus-banner { flex-wrap:wrap; }
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-r">
            <a href="javascript:history.back()" class="hbtn"><i class="fas fa-arrow-left"></i> Back</a>
            <a href="dashboard.php" class="hbtn"><i class="fas fa-home"></i> Dashboard</a>
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        </div>
    </div>
</header>

<div class="wrap">

    <!-- Customer Banner -->
    <div class="cus-banner">
        <div class="cus-banner-avatar"><?php echo htmlspecialchars($cusInitials); ?></div>
        <div class="cus-banner-text">
            <div class="cus-banner-label">Customer Account</div>
            <div class="cus-banner-name"><?php echo $cusName; ?></div>
            <?php if ($cusEmail): ?>
            <div class="cus-banner-email"><i class="fas fa-envelope" style="font-size:10px;margin-right:4px;"></i><?php echo $cusEmail; ?></div>
            <?php endif; ?>
        </div>
        <div class="cus-banner-badge">
            <i class="fas fa-receipt"></i> Order History
        </div>
    </div>

    <!-- HERO -->
    <div class="hero">
        <div>
            <div class="eyebrow">Order Management</div>
            <div class="page-title">
                Order <span class="hl">History</span>
            </div>
            <div class="sub">
                Showing all <strong><?php echo $totalOrders; ?></strong> order<?php echo $totalOrders !== 1 ? 's' : ''; ?> for <strong><?php echo $cusName; ?></strong>
            </div>
        </div>
        <a href="dashboard.php" class="hbtn"><i class="fas fa-th-large"></i> Dashboard</a>
    </div>

    <!-- STATS -->
    <div class="stats">
        <div class="scard">
            <div class="sico"><i class="fas fa-receipt"></i></div>
            <div>
                <div class="slabel">Total Orders</div>
                <div class="sval"><?php echo $totalOrders; ?></div>
            </div>
        </div>
        <div class="scard">
            <div class="sico or"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="slabel">Total Items</div>
                <div class="sval"><?php echo $totalItems ?: '—'; ?></div>
            </div>
        </div>
        <div class="scard">
            <div class="sico or"><i class="fas fa-boxes"></i></div>
            <div>
                <div class="slabel">Total Qty</div>
                <div class="sval"><?php echo $totalQty ?: '—'; ?></div>
            </div>
        </div>
        <div class="scard">
            <div class="sico am"><i class="fas fa-clock"></i></div>
            <div>
                <div class="slabel">Pending</div>
                <div class="sval"><?php echo $pendingCnt; ?></div>
            </div>
        </div>
    </div>

    <!-- PANEL -->
    <div class="panel">

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="tb-l">
                <div class="tb-ico"><i class="fas fa-history"></i></div>
                <div class="tb-title">All Orders</div>
                <span class="cpill" id="cPill">
                    <?php echo $totalOrders . ' Order' . ($totalOrders !== 1 ? 's' : ''); ?>
                </span>
            </div>
            <div class="tb-r">
                <div class="srch">
                    <input type="text" id="searchInput" placeholder="Search by Order ID or Invoice No.…" autocomplete="off">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="tbl-wrap">
            <table class="t">
                <colgroup>
                    <col class="c0">
                    <col class="c1">
                    <col class="c1b">
                    <col class="c2">
                    <col class="c3">
                    <col class="c4">
                    <col class="c5">
                    <col class="c6">
                    <col class="c7">
                </colgroup>
                <thead>
                    <tr>
                        <th class="ctr">#</th>
                        <th><i class="hi fas fa-hashtag"></i>Order ID</th>
                        <th><i class="hi fas fa-file-invoice"></i>Invoice No.</th>
                        <th><i class="hi fas fa-calendar-alt"></i>Date</th>
                        <th><i class="hi fas fa-barcode"></i>Item Codes</th>
                        <th class="ctr"><i class="hi fas fa-layer-group"></i>Items</th>
                        <th class="ctr"><i class="hi fas fa-boxes"></i>Qty</th>
                        <th><i class="hi fas fa-circle"></i>Status</th>
                        <th class="ctr"><i class="hi fas fa-bolt"></i>Action</th>
                    </tr>
                </thead>
                <tbody id="tbody">

                <?php if (!empty($rows_data)): ?>
                <?php $cnt = 1; foreach ($rows_data as $row): ?>
                <?php
                    $rawCodes = trim($row['item_codes'] ?? '');
                    $codeHtml = '<span style="color:var(--g300)">—</span>';
                    if ($rawCodes !== '') {
                        $arr  = array_filter(array_map('trim', explode(',', $rawCodes)));
                        $tags = [];
                        foreach ($arr as $c) $tags[] = '<span class="ctag">' . htmlspecialchars($c) . '</span>';
                        $codeHtml = implode('', $tags);
                    }

                    [$bCls, $bIco, $bLbl] = getBadge($row['status'] ?? 'cus_confirmed');

                    $dPrimary = '—'; $dTime = '';
                    if (!empty($row['order_date'])) {
                        $ts = strtotime($row['order_date']);
                        if ($ts) {
                            $dPrimary = date('M j, Y', $ts);
                            $dTime    = date('g:i A', $ts);
                        } else {
                            $dPrimary = htmlspecialchars($row['order_date']);
                        }
                    }

                    $invoiceNo  = !empty($row['invoice_no']) ? htmlspecialchars($row['invoice_no']) : '—';
                ?>
                <tr data-id="<?php echo htmlspecialchars($row['order_id']); ?>"
                    data-invoice="<?php echo htmlspecialchars($row['invoice_no'] ?? ''); ?>">
                    <td class="c0"><span class="rno"><?php echo $cnt; ?></span></td>
                    <td class="c1"><span class="oid-pill"><?php echo htmlspecialchars($row['order_id']); ?></span></td>
                    <td class="c1b"><span class="inv-pill"><?php echo $invoiceNo; ?></span></td>
                    <td class="c2">
                        <div class="d1"><?php echo $dPrimary; ?></div>
                        <?php if ($dTime): ?>
                        <div class="d2"><i class="fas fa-clock" style="font-size:9px;margin-right:2px;"></i><?php echo $dTime; ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="c3"><div class="codes"><?php echo $codeHtml; ?></div></td>
                    <td class="c4"><span class="nchip"><?php echo htmlspecialchars($row['total_items'] ?? '—'); ?></span></td>
                    <td class="c5"><span class="nchip"><?php echo htmlspecialchars($row['total_quantity'] ?? '—'); ?></span></td>
                    <td class="c6">
                        <span class="badge <?php echo $bCls; ?>">
                            <i class="fas fa-<?php echo $bIco; ?>"></i> <?php echo htmlspecialchars($bLbl); ?>
                        </span>
                    </td>
                    <td class="c7">
                        <div class="action-group">
                            <a href="order-details.php?oid=<?php echo urlencode($row['order_id']); ?>" class="vbtn">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </td>
                </tr>
                <?php $cnt++; endforeach; ?>

                <?php else: ?>
                <tr><td colspan="9">
                    <div class="empty">
                        <div class="empty-ico">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h3>No Orders Found</h3>
                        <p><?php echo htmlspecialchars($cusName); ?> has no orders at this time.</p>
                        <div class="empty-acts">
                            <a href="dashboard.php" class="hbtn"><i class="fas fa-home"></i> Dashboard</a>
                        </div>
                    </div>
                </td></tr>
                <?php endif; ?>

                </tbody>
            </table>

            <div class="nores" id="noRes">
                <i class="fas fa-search"></i>
                <p>No orders match your search.</p>
            </div>
        </div>

    </div><!-- /panel -->
</div><!-- /wrap -->

<script>
(function () {
    var inp  = document.getElementById('searchInput');
    var pill = document.getElementById('cPill');
    var noR  = document.getElementById('noRes');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#tbody tr[data-id]'));
    if (!inp || !rows.length) return;

    inp.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var n = 0;
        rows.forEach(function (r) {
            var orderId   = (r.getAttribute('data-id')      || '').toLowerCase();
            var invoiceNo = (r.getAttribute('data-invoice') || '').toLowerCase();
            var show = !q || orderId.includes(q) || invoiceNo.includes(q);
            r.style.display = show ? '' : 'none';
            if (show) n++;
        });
        noR.style.display = (n === 0) ? 'block' : 'none';
        pill.textContent  = n + ' Order' + (n !== 1 ? 's' : '');
    });
}());
</script>
</body>
</html>