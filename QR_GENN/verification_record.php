<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$dbname     = "planatir_task_managemen";

// ── FILTER PARAMS ─────────────────────────────────────────────────────────────
$event_type = $_GET['event']     ?? 'GRN';
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to']   ?? '';
$search     = trim($_GET['search'] ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 50;
$offset     = ($page - 1) * $per_page;

if (!in_array($event_type, ['GRN', 'STOCK'])) $event_type = 'GRN';
$table = ($event_type === 'GRN') ? 'grn_verification' : 'stock_verification';

// ── WHERE CLAUSE ──────────────────────────────────────────────────────────────
$where  = []; $params = []; $types = '';
if ($date_from !== '') { $where[] = 'DATE(verified_at) >= ?'; $params[] = $date_from; $types .= 's'; }
if ($date_to   !== '') { $where[] = 'DATE(verified_at) <= ?'; $params[] = $date_to;   $types .= 's'; }
if ($search    !== '') {
    $where[]  = '(lot_serial_nbr LIKE ? OR inventory_id LIKE ? OR description LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'sss';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ══ AJAX: export_all — MUST come before any HTML output ══════════════════════
if (isset($_GET['action']) && $_GET['action'] === 'export_all') {
    header('Content-Type: application/json');
    $c = new mysqli($servername, $username, $password, $dbname);
    if ($c->connect_error) { echo json_encode(['success'=>false,'error'=>'DB error']); exit; }
    $sql = "SELECT lot_serial_nbr, inventory_id, description, verified_at FROM $table $where_sql ORDER BY verified_at DESC";
    $all = [];
    if ($params) {
        $s = $c->prepare($sql); $s->bind_param($types, ...$params); $s->execute(); $r = $s->get_result();
        while ($rx = $r->fetch_assoc()) $all[] = ['Lot / Serial Number' => $rx['lot_serial_nbr'], 'Inventory ID' => $rx['inventory_id'], 'Description' => $rx['description'] ?? '', 'Verified At' => $rx['verified_at']];
        $s->close();
    } else {
        $r = $c->query($sql);
        while ($rx = $r->fetch_assoc()) $all[] = ['Lot / Serial Number' => $rx['lot_serial_nbr'], 'Inventory ID' => $rx['inventory_id'], 'Description' => $rx['description'] ?? '', 'Verified At' => $rx['verified_at']];
    }
    $c->close();
    echo json_encode(['success' => true, 'rows' => $all]);
    exit;
}
// ═════════════════════════════════════════════════════════════════════════════

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// ── TOTAL COUNT ───────────────────────────────────────────────────────────────
$count_sql = "SELECT COUNT(*) as t FROM $table $where_sql";
if ($params) {
    $s = $conn->prepare($count_sql); $s->bind_param($types, ...$params); $s->execute();
    $total_rows = $s->get_result()->fetch_assoc()['t'] ?? 0; $s->close();
} else {
    $total_rows = $conn->query($count_sql)->fetch_assoc()['t'] ?? 0;
}
$total_pages = max(1, (int)ceil($total_rows / $per_page));

// ── PAGE ROWS ─────────────────────────────────────────────────────────────────
$rows = [];
$pp = array_merge($params, [$per_page, $offset]);
$pt = $types . 'ii';
$s  = $conn->prepare("SELECT id, lot_serial_nbr, inventory_id, description, verified_at FROM $table $where_sql ORDER BY verified_at DESC LIMIT ? OFFSET ?");
$s->bind_param($pt, ...$pp); $s->execute(); $r = $s->get_result();
while ($row = $r->fetch_assoc()) $rows[] = $row;
$s->close();

// ── SUMMARY STATS ─────────────────────────────────────────────────────────────
$grn_today = 0; $grn_total = 0; $stock_today = 0; $stock_total = 0;
if ($conn->query("SHOW TABLES LIKE 'grn_verification'")->num_rows > 0) {
    $grn_total = $conn->query("SELECT COUNT(*) as t FROM grn_verification")->fetch_assoc()['t'] ?? 0;
    $grn_today = $conn->query("SELECT COUNT(*) as t FROM grn_verification WHERE DATE(verified_at)=CURDATE()")->fetch_assoc()['t'] ?? 0;
}
if ($conn->query("SHOW TABLES LIKE 'stock_verification'")->num_rows > 0) {
    $stock_total = $conn->query("SELECT COUNT(*) as t FROM stock_verification")->fetch_assoc()['t'] ?? 0;
    $stock_today = $conn->query("SELECT COUNT(*) as t FROM stock_verification WHERE DATE(verified_at)=CURDATE()")->fetch_assoc()['t'] ?? 0;
}
$conn->close();

function qp($overrides = []) {
    $p = array_merge($_GET, $overrides);
    return '?' . http_build_query(array_filter($p, fn($v) => $v !== ''));
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verification Records — Tire Label System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
:root,
[data-theme="dark"] {
    --orange:      #F28018;
    --orange-dark: #c8660e;
    --orange-glow: rgba(242,128,24,0.18);
    --orange-soft: rgba(242,128,24,0.08);
    --bg:          #0d0d0d;
    --surface:     #161616;
    --surface2:    #1e1e1e;
    --surface3:    #242424;
    --border:      rgba(255,255,255,0.07);
    --border-hot:  rgba(242,128,24,0.45);
    --off-white:   #f0ede8;
    --text:        #ffffff;
    --muted:       rgba(255,255,255,0.38);
    --dim:         rgba(255,255,255,0.14);
    --blue:        #60a5fa;
    --blue-glow:   rgba(96,165,250,0.18);
    --blue-soft:   rgba(96,165,250,0.08);
    --blue-border: rgba(96,165,250,0.45);
    --topbar-bg:   rgba(13,13,13,0.92);
    --hero-bg:     linear-gradient(100deg, #1a0d00 0%, #0d0d0d 55%);
    --logo-filter: brightness(1.05) drop-shadow(0 0 6px rgba(242,128,24,0.35));
    --logo-filter-hover: brightness(1.15) drop-shadow(0 0 10px rgba(242,128,24,0.6));
    --noise-opacity: 0.6;
    --th-bg:       #1e1e1e;
    --tr-hover:    rgba(242,128,24,0.04);
    --tr-stripe:   rgba(255,255,255,0.015);
}
[data-theme="light"] {
    --bg:          #f5f4f0;
    --surface:     #ffffff;
    --surface2:    #f0ede8;
    --surface3:    #e8e4de;
    --border:      rgba(0,0,0,0.09);
    --border-hot:  rgba(242,128,24,0.5);
    --off-white:   #1a1a1a;
    --text:        #1a1a1a;
    --muted:       rgba(0,0,0,0.45);
    --dim:         rgba(0,0,0,0.25);
    --topbar-bg:   rgba(255,255,255,0.94);
    --hero-bg:     linear-gradient(100deg, #fff0e0 0%, #f5f4f0 55%);
    --logo-filter: brightness(0.9) drop-shadow(0 0 6px rgba(242,128,24,0.2));
    --logo-filter-hover: brightness(0.8) drop-shadow(0 0 10px rgba(242,128,24,0.4));
    --noise-opacity: 0.15;
    --orange-glow: rgba(242,128,24,0.12);
    --orange-soft: rgba(242,128,24,0.07);
    --blue-glow:   rgba(96,165,250,0.12);
    --blue-soft:   rgba(96,165,250,0.07);
    --blue-border: rgba(96,165,250,0.4);
    --th-bg:       #f0ede8;
    --tr-hover:    rgba(242,128,24,0.05);
    --tr-stripe:   rgba(0,0,0,0.018);
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Outfit', sans-serif;
    background: var(--bg); color: var(--text);
    min-height: 100vh; overflow-x: hidden;
    transition: background 0.3s, color 0.3s;
}
body::before {
    content:''; position:fixed; inset:0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events:none; z-index:0; opacity:var(--noise-opacity); transition:opacity 0.3s;
}

/* ── TOPBAR ── */
.topbar {
    position:sticky; top:0; z-index:100;
    background:var(--topbar-bg); backdrop-filter:blur(14px);
    border-bottom:1px solid var(--border);
    padding:0 40px; height:68px;
    display:flex; align-items:center; justify-content:space-between;
    transition:background 0.3s;
}
.brand { display:flex; align-items:center; gap:14px; }
.brand-logo { height:42px; filter:var(--logo-filter); transition:filter 0.2s ease; }
.brand-logo:hover { filter:var(--logo-filter-hover); }
.brand-divider { width:1px; height:28px; background:var(--border); }
.brand-name { font-family:'Bebas Neue',sans-serif; font-size:1.25rem; letter-spacing:0.08em; color:var(--off-white); }
.topbar-right { display:flex; align-items:center; gap:10px; }

/* ── BUTTONS ── */
.btn-t {
    display:inline-flex; align-items:center; gap:8px;
    padding:9px 20px; border-radius:9px;
    font-size:0.82rem; font-weight:600; font-family:'Outfit',sans-serif;
    border:none; cursor:pointer; text-decoration:none; transition:all 0.18s ease;
}
.btn-orange { background:var(--orange); color:#fff; box-shadow:0 2px 14px var(--orange-glow); }
.btn-orange:hover { background:var(--orange-dark); color:#fff; transform:translateY(-1px); }
.btn-ghost  { background:var(--surface2); border:1px solid var(--border); color:var(--off-white); }
.btn-ghost:hover { background:var(--surface3); border-color:var(--border-hot); color:var(--text); text-decoration:none; }
.btn-green  { background:#16a34a; color:#fff; }
.btn-green:hover  { background:#15803d; transform:translateY(-1px); }
.theme-toggle {
    display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border-radius:9px;
    background:var(--surface2); border:1px solid var(--border); color:var(--muted);
    font-size:0.82rem; font-weight:600; font-family:'Outfit',sans-serif;
    cursor:pointer; transition:all 0.2s ease;
}
.theme-toggle:hover { border-color:var(--border-hot); color:var(--orange); }

/* ── HERO STRIP ── */
.hero-strip {
    background:var(--hero-bg); border-bottom:1px solid var(--border);
    padding:28px 40px 22px; position:relative; overflow:hidden; transition:background 0.3s;
}
.hero-strip::after {
    content:''; position:absolute; right:-80px; top:-80px; width:280px; height:280px;
    background:radial-gradient(circle,rgba(242,128,24,0.1) 0%,transparent 70%); pointer-events:none;
}
.hero-title { font-family:'Bebas Neue',sans-serif; font-size:2.2rem; letter-spacing:0.04em; line-height:1; }
.hero-title span { color:var(--orange); }
.hero-sub { margin-top:5px; font-size:0.82rem; color:var(--muted); }

/* ── PAGE ── */
.page { padding:28px 40px; position:relative; z-index:1; max-width:1400px; margin:0 auto; }

/* ── EVENT SWITCHER ── */
.event-switcher { display:flex; gap:10px; margin-bottom:22px; }
.ev-btn {
    flex:1; display:flex; align-items:center; gap:12px;
    padding:16px 20px; border-radius:12px;
    border:2px solid var(--border); background:var(--surface);
    cursor:pointer; text-decoration:none; transition:all 0.2s ease;
    font-family:'Outfit',sans-serif;
}
.ev-btn:hover { border-color:var(--border-hot); text-decoration:none; }
.ev-btn.active-grn {
    border-color:var(--orange); background:linear-gradient(135deg,rgba(28,15,0,0.7),var(--surface));
    box-shadow:0 0 20px var(--orange-glow);
}
.ev-btn.active-stock {
    border-color:var(--blue-border); background:linear-gradient(135deg,rgba(0,15,40,0.7),var(--surface));
    box-shadow:0 0 20px var(--blue-glow);
}
[data-theme="light"] .ev-btn.active-grn   { background:linear-gradient(135deg,rgba(255,220,180,0.5),var(--surface)); }
[data-theme="light"] .ev-btn.active-stock { background:linear-gradient(135deg,rgba(180,210,255,0.4),var(--surface)); }
.ev-icon {
    width:42px; height:42px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:1.15rem;
}
.ev-icon.orange { background:var(--orange-soft); border:1px solid var(--border-hot); color:var(--orange); }
.ev-icon.blue   { background:var(--blue-soft);   border:1px solid var(--blue-border); color:var(--blue); }
.ev-body { flex:1; }
.ev-label { font-family:'Bebas Neue',sans-serif; font-size:1.05rem; letter-spacing:0.07em; color:var(--text); }
.ev-sub   { font-size:0.69rem; color:var(--muted); margin-top:1px; }
.ev-counts { text-align:right; }
.ev-big { font-family:'Bebas Neue',sans-serif; font-size:1.7rem; line-height:1; }
.ev-big.orange { color:var(--orange); }
.ev-big.blue   { color:var(--blue); }
.ev-small { font-size:0.6rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--muted); }

/* ── FILTER BAR ── */
.filter-bar {
    background:var(--surface); border:1px solid var(--border); border-radius:12px;
    padding:18px 22px; margin-bottom:16px;
    display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap;
    transition:background 0.3s;
}
.filter-group { display:flex; flex-direction:column; gap:5px; }
.filter-label { font-size:0.63rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); }
.filter-input, .filter-select {
    padding:9px 13px; border-radius:8px;
    background:var(--surface2); border:1px solid var(--border);
    color:var(--text); font-family:'Outfit',sans-serif; font-size:0.85rem;
    transition:border-color 0.2s; min-width:140px;
}
.filter-input:focus, .filter-select:focus { outline:none; border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-soft); }
.filter-input::placeholder { color:var(--dim); }
.filter-search { min-width:230px; }
.filter-actions { display:flex; gap:8px; margin-left:auto; align-items:flex-end; }

/* ── TOOLBAR ── */
.toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:10px; }
.record-count { font-size:0.78rem; color:var(--muted); font-weight:500; }
.record-count strong { color:var(--text); font-size:0.9rem; }
.export-group { display:flex; gap:8px; }

/* ── TABLE ── */
.table-wrap {
    background:var(--surface); border:1px solid var(--border);
    border-radius:12px; overflow:hidden; transition:background 0.3s;
}
.data-table { width:100%; border-collapse:collapse; font-size:0.84rem; }
.data-table thead tr { background:var(--th-bg); border-bottom:2px solid var(--border-hot); }
.data-table th {
    padding:13px 18px; text-align:left;
    font-size:0.65rem; font-weight:700; letter-spacing:0.12em;
    text-transform:uppercase; color:var(--muted); white-space:nowrap;
}
.data-table tbody tr { border-bottom:1px solid var(--border); transition:background 0.12s; }
.data-table tbody tr:last-child { border-bottom:none; }
.data-table tbody tr:nth-child(even) { background:var(--tr-stripe); }
.data-table tbody tr:hover { background:var(--tr-hover); }
.data-table td { padding:12px 18px; vertical-align:middle; }
.td-id   { font-size:0.72rem; color:var(--dim); font-weight:600; }
.td-lot  { font-weight:600; color:var(--text); font-size:0.88rem; }
.td-inv  {
    display:inline-flex; align-items:center;
    font-size:0.72rem; font-weight:700;
    padding:3px 10px; border-radius:6px;
    background:var(--orange-soft); color:var(--orange);
    border:1px solid var(--border-hot); white-space:nowrap;
}
.td-desc { font-size:0.8rem; color:var(--muted); max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.td-date { font-size:0.78rem; color:var(--muted); white-space:nowrap; }
.td-date .time { font-size:0.68rem; color:var(--dim); display:block; }

/* ── EMPTY STATE ── */
.empty-state { text-align:center; padding:60px 20px; color:var(--dim); }
.empty-state i { font-size:3rem; display:block; margin-bottom:14px; opacity:0.4; }
.empty-state h3 { font-family:'Bebas Neue',sans-serif; font-size:1.3rem; letter-spacing:0.06em; color:var(--muted); margin-bottom:6px; }
.empty-state p { font-size:0.82rem; }

/* ── PAGINATION ── */
.pagination { display:flex; align-items:center; justify-content:space-between; margin-top:18px; flex-wrap:wrap; gap:10px; }
.page-info { font-size:0.76rem; color:var(--muted); }
.page-btns { display:flex; gap:5px; flex-wrap:wrap; }
.pg-btn {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:34px; height:34px; padding:0 10px; border-radius:7px;
    font-size:0.8rem; font-weight:600; font-family:'Outfit',sans-serif;
    background:var(--surface2); border:1px solid var(--border);
    color:var(--muted); text-decoration:none; transition:all 0.15s ease;
}
.pg-btn:hover { border-color:var(--border-hot); color:var(--orange); text-decoration:none; }
.pg-btn.active { background:var(--orange); border-color:var(--orange); color:#fff; box-shadow:0 2px 10px var(--orange-glow); }
.pg-btn.disabled { opacity:0.3; pointer-events:none; }

/* ── SPINNER OVERLAY ── */
.export-spinner {
    display:none; position:fixed; inset:0; z-index:999;
    background:rgba(0,0,0,0.55); backdrop-filter:blur(6px);
    align-items:center; justify-content:center; flex-direction:column; gap:14px;
}
.export-spinner.show { display:flex; }
.spinner-box {
    background:var(--surface); border:1px solid var(--border-hot);
    border-radius:16px; padding:32px 40px; text-align:center;
    box-shadow:0 0 40px var(--orange-glow);
}
.spinner-box i { font-size:2.2rem; color:var(--orange); margin-bottom:12px; display:block; }
.spinner-box p { font-size:0.9rem; color:var(--muted); }

/* ── FOOTER ── */
.footer-bar { margin-top:32px; padding:16px 0 4px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; font-size:0.72rem; color:var(--dim); }
.status { display:flex; align-items:center; gap:7px; }
.status-dot { width:7px; height:7px; border-radius:50%; background:#4ade80; box-shadow:0 0 6px #4ade80; }

/* ── ANIMATIONS ── */
@keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:none} }
.event-switcher { animation:fadeUp 0.35s ease both; }
.filter-bar     { animation:fadeUp 0.35s 0.05s ease both; }
.toolbar        { animation:fadeUp 0.35s 0.08s ease both; }
.table-wrap     { animation:fadeUp 0.35s 0.11s ease both; }

/* ── RESPONSIVE ── */
@media(max-width:900px){
    .page,.topbar,.hero-strip { padding-left:20px; padding-right:20px; }
    .brand-name,.brand-divider { display:none; }
    .event-switcher { flex-direction:column; }
    .data-table td,.data-table th { padding:10px 12px; }
    .td-desc { max-width:130px; }
    .filter-actions { margin-left:0; width:100%; }
}
@media(max-width:600px){
    .theme-toggle span { display:none; }
    .col-desc { display:none; }
    .btn-label { display:none; }
}
</style>
</head>
<body>

<!-- EXPORT SPINNER -->
<div class="export-spinner" id="exportSpinner">
    <div class="spinner-box">
        <i class="fas fa-spinner fa-spin"></i>
        <p id="spinnerMsg">Fetching all records…</p>
    </div>
</div>

<!-- TOPBAR -->
<header class="topbar">
    <div class="brand">
        <img src="atire.png" alt="ATire Logo" class="brand-logo" onerror="this.style.display='none'">
        <div class="brand-divider"></div>
        <div class="brand-name">Tire Label Generate System</div>
    </div>
    <div class="topbar-right">
        <a href="qr_system_dash.php"  class="btn-t btn-ghost"><i class="fas fa-home"></i> Dashboard</a>
        <a href="verification.php"    class="btn-t btn-ghost"><i class="fas fa-qrcode"></i> Scan</a>
        <a href="view_generated_label.php" class="btn-t btn-ghost"><i class="fas fa-history"></i> Generated</a>
        <button class="theme-toggle" id="themeToggle">
            <i class="fas fa-moon" id="themeIcon"></i>
            <span id="themeLabel">Light</span>
        </button>
    </div>
</header>

<!-- HERO STRIP -->
<div class="hero-strip">
    <div class="hero-title">Verification <span>Records</span></div>
    <div class="hero-sub">Filter, review and export GRN &amp; Stock verification data · UK Series</div>
</div>

<!-- PAGE -->
<div class="page">

    <!-- EVENT SWITCHER -->
    <div class="event-switcher">
        <a href="<?php echo qp(['event'=>'GRN','page'=>1]); ?>"
           class="ev-btn <?php echo $event_type==='GRN' ? 'active-grn' : ''; ?>"
           style="text-decoration:none;">
            <div class="ev-icon orange"><i class="fas fa-truck-loading"></i></div>
            <div class="ev-body">
                <div class="ev-label">GRN Verification</div>
                <div class="ev-sub">Goods Receipt Note records</div>
            </div>
            <div class="ev-counts">
                <div class="ev-big orange"><?php echo number_format($grn_today); ?></div>
                <div class="ev-small">Today</div>
                <div style="font-size:0.68rem;color:var(--muted);margin-top:3px;">
                    <?php echo number_format($grn_total); ?> total
                </div>
            </div>
        </a>
        <a href="<?php echo qp(['event'=>'STOCK','page'=>1]); ?>"
           class="ev-btn <?php echo $event_type==='STOCK' ? 'active-stock' : ''; ?>"
           style="text-decoration:none;">
            <div class="ev-icon blue"><i class="fas fa-boxes"></i></div>
            <div class="ev-body">
                <div class="ev-label">Stock Verification</div>
                <div class="ev-sub">Physical stock count records</div>
            </div>
            <div class="ev-counts">
                <div class="ev-big blue"><?php echo number_format($stock_today); ?></div>
                <div class="ev-small">Today</div>
                <div style="font-size:0.68rem;color:var(--muted);margin-top:3px;">
                    <?php echo number_format($stock_total); ?> total
                </div>
            </div>
        </a>
    </div>

    <!-- FILTER BAR -->
    <form method="GET" action="" id="filterForm">
        <input type="hidden" name="event" value="<?php echo htmlspecialchars($event_type); ?>">
        <input type="hidden" name="page"  value="1">
        <div class="filter-bar">
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-search" style="color:var(--orange);margin-right:4px;"></i>Search
                </label>
                <input class="filter-input filter-search" type="text" name="search"
                    placeholder="Lot serial, tire code, description…"
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-calendar" style="color:var(--orange);margin-right:4px;"></i>Date From
                </label>
                <input class="filter-input" type="date" name="date_from"
                    value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-calendar-check" style="color:var(--orange);margin-right:4px;"></i>Date To
                </label>
                <input class="filter-input" type="date" name="date_to"
                    value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-t btn-orange">
                    <i class="fas fa-filter"></i> <span class="btn-label">Apply Filters</span>
                </button>
                <a href="<?php echo qp(['search'=>'','date_from'=>'','date_to'=>'','page'=>1]); ?>"
                   class="btn-t btn-ghost">
                    <i class="fas fa-times"></i> <span class="btn-label">Clear</span>
                </a>
            </div>
        </div>
    </form>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="record-count">
            Showing <strong><?php echo number_format(count($rows)); ?></strong>
            of <strong><?php echo number_format($total_rows); ?></strong>
            <?php echo $event_type === 'GRN' ? 'GRN' : 'Stock'; ?> records
            <?php if ($search || $date_from || $date_to): ?>
            <span style="color:var(--orange);margin-left:8px;font-size:0.72rem;">
                <i class="fas fa-filter"></i> Filtered
            </span>
            <?php endif; ?>
        </div>
        <div class="export-group">
            <button class="btn-t btn-green" onclick="doExport('page')" <?php echo empty($rows)?'disabled':''; ?>>
                <i class="fas fa-file-excel"></i>
                <span class="btn-label">Export This Page</span>
            </button>
            <button class="btn-t btn-orange" id="exportAllBtn" onclick="doExport('all')" <?php echo $total_rows<1?'disabled':''; ?>>
                <i class="fas fa-download"></i>
                <span class="btn-label">Export All <?php echo number_format($total_rows); ?> Rows</span>
            </button>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-wrap">
        <?php if (empty($rows)): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list" style="color:var(--border-hot);"></i>
            <h3>No Records Found</h3>
            <p>Try adjusting your filters, or switch the event type above.</p>
        </div>
        <?php else: ?>
        <table class="data-table" id="dataTable">
            <thead>
                <tr>
                    <th style="width:56px;">#</th>
                    <th>Lot / Serial Number</th>
                    <th>Inventory ID (Tire Code)</th>
                    <th class="col-desc">Description</th>
                    <th>Verified At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $i => $row): ?>
                <tr>
                    <td class="td-id"><?php echo $offset + $i + 1; ?></td>
                    <td class="td-lot"><?php echo htmlspecialchars($row['lot_serial_nbr']); ?></td>
                    <td><span class="td-inv"><?php echo htmlspecialchars($row['inventory_id']); ?></span></td>
                    <td class="td-desc col-desc"
                        title="<?php echo htmlspecialchars($row['description'] ?? ''); ?>">
                        <?php echo htmlspecialchars($row['description'] ?? '—'); ?>
                    </td>
                    <td class="td-date">
                        <?php $ts = strtotime($row['verified_at']); ?>
                        <?php echo date('d M Y', $ts); ?>
                        <span class="time"><?php echo date('H:i:s', $ts); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <div class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></div>
        <div class="page-btns">
            <?php
            echo '<a href="'.qp(['page'=>max(1,$page-1)]).'" class="pg-btn'.($page<=1?' disabled':'').'">'
               . '<i class="fas fa-chevron-left" style="font-size:0.7rem;"></i></a>';
            $start = max(1, min($page-2, $total_pages-4));
            $end   = min($total_pages, $start+4);
            if ($start > 1) {
                echo '<a href="'.qp(['page'=>1]).'" class="pg-btn">1</a>';
                if ($start > 2) echo '<span class="pg-btn disabled">…</span>';
            }
            for ($p = $start; $p <= $end; $p++) {
                echo '<a href="'.qp(['page'=>$p]).'" class="pg-btn'.($p==$page?' active':'').'">'.$p.'</a>';
            }
            if ($end < $total_pages) {
                if ($end < $total_pages-1) echo '<span class="pg-btn disabled">…</span>';
                echo '<a href="'.qp(['page'=>$total_pages]).'" class="pg-btn">'.$total_pages.'</a>';
            }
            echo '<a href="'.qp(['page'=>min($total_pages,$page+1)]).'" class="pg-btn'.($page>=$total_pages?' disabled':'').'">'
               . '<i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></a>';
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="footer-bar">
        <div class="status"><div class="status-dot"></div>System online — <?php echo date('d M Y, H:i:s'); ?></div>
        <div>Tire Label Management &copy; <?php echo date('Y'); ?></div>
    </div>

</div><!-- /page -->

<!-- Page-level data for export -->
<script>
var PAGE_ROWS = <?php
    $pr = [];
    foreach ($rows as $r) {
        $pr[] = [
            'Lot / Serial Number' => $r['lot_serial_nbr'],
            'Inventory ID'        => $r['inventory_id'],
            'Description'         => $r['description'] ?? '',
            'Verified At'         => $r['verified_at'],
        ];
    }
    echo json_encode($pr);
?>;

var EXPORT_META = {
    event:      "<?php echo $event_type; ?>",
    label:      "<?php echo $event_type === 'GRN' ? 'GRN Verification' : 'Stock Verification'; ?>",
    totalRows:  <?php echo (int)$total_rows; ?>,
    dateFrom:   "<?php echo addslashes($date_from); ?>",
    dateTo:     "<?php echo addslashes($date_to); ?>",
    search:     "<?php echo addslashes($search); ?>",
    page:       <?php echo $page; ?>
};
</script>

<script>
/* ══════════════════════════════════════════════
   THEME
══════════════════════════════════════════════ */
(function(){
    var html = document.documentElement;
    var btn  = document.getElementById('themeToggle');
    var icon = document.getElementById('themeIcon');
    var lbl  = document.getElementById('themeLabel');
    function apply(t) {
        html.setAttribute('data-theme', t);
        localStorage.setItem('tlsTheme', t);
        icon.className = t==='dark' ? 'fas fa-sun' : 'fas fa-moon';
        lbl.textContent = t==='dark' ? 'Light' : 'Dark';
    }
    apply(localStorage.getItem('tlsTheme') || 'dark');
    btn.addEventListener('click', function(){
        apply(html.getAttribute('data-theme')==='dark' ? 'light' : 'dark');
    });
})();

/* ══════════════════════════════════════════════
   EXPORT
══════════════════════════════════════════════ */
function pad2(n){ return n<10?'0'+n:''+n; }

function makeFilename(scope) {
    var d   = new Date();
    var ts  = d.getFullYear()+pad2(d.getMonth()+1)+pad2(d.getDate())
            +'_'+pad2(d.getHours())+pad2(d.getMinutes());
    var sfx = scope==='all' ? '_ALL' : '_Page'+EXPORT_META.page;
    var rng = '';
    if (EXPORT_META.dateFrom) rng += '_from'+EXPORT_META.dateFrom;
    if (EXPORT_META.dateTo)   rng += '_to'+EXPORT_META.dateTo;
    return EXPORT_META.event+'_Verification'+sfx+rng+'_'+ts+'.xlsx';
}

function buildWorkbook(rows) {
    var wb = XLSX.utils.book_new();

    /* ── DATA SHEET ── */
    var data = [['#','Lot / Serial Number','Inventory ID (Tire Code)','Description','Verified At']];
    rows.forEach(function(r, i){
        data.push([
            i+1,
            r['Lot / Serial Number'] || '',
            r['Inventory ID']        || '',
            r['Description']         || '',
            r['Verified At']         || ''
        ]);
    });
    var ws = XLSX.utils.aoa_to_sheet(data);
    ws['!cols'] = [{wch:6},{wch:32},{wch:24},{wch:40},{wch:22}];
    XLSX.utils.book_append_sheet(wb, ws, EXPORT_META.label);

    /* ── SUMMARY SHEET ── */
    var now = new Date();
    var fil = [];
    if (EXPORT_META.dateFrom) fil.push('From: '+EXPORT_META.dateFrom);
    if (EXPORT_META.dateTo)   fil.push('To: '+EXPORT_META.dateTo);
    if (EXPORT_META.search)   fil.push('Search: '+EXPORT_META.search);

    var sum = XLSX.utils.aoa_to_sheet([
        ['Export Summary'],
        [],
        ['Event Type',    EXPORT_META.label],
        ['Total Records', rows.length],
        ['Filters',       fil.length ? fil.join('  |  ') : 'None'],
        ['Exported At',   now.toLocaleString()],
        [],
        ['Generated by',  'Tire Label Management System']
    ]);
    sum['!cols'] = [{wch:20},{wch:40}];
    XLSX.utils.book_append_sheet(wb, sum, 'Export Info');

    return wb;
}

function doExport(scope) {
    if (scope === 'page') {
        if (!PAGE_ROWS.length) { alert('No data on this page.'); return; }
        XLSX.writeFile(buildWorkbook(PAGE_ROWS), makeFilename('page'));
        return;
    }

    // scope === 'all': fetch via AJAX
    var spinner = document.getElementById('exportSpinner');
    var msg     = document.getElementById('spinnerMsg');
    spinner.classList.add('show');
    msg.textContent = 'Fetching all '+EXPORT_META.totalRows+' records…';

    var params = new URLSearchParams({
        action:    'export_all',
        event:     EXPORT_META.event,
        date_from: EXPORT_META.dateFrom,
        date_to:   EXPORT_META.dateTo,
        search:    EXPORT_META.search
    });

    fetch(window.location.pathname + '?' + params.toString())
        .then(function(r){
            if (!r.ok) throw new Error('HTTP '+r.status);
            return r.json();
        })
        .then(function(data){
            spinner.classList.remove('show');
            if (data.success && data.rows) {
                msg.textContent = 'Building Excel file…';
                XLSX.writeFile(buildWorkbook(data.rows), makeFilename('all'));
            } else {
                alert('Export error: ' + (data.error || 'Unknown'));
            }
        })
        .catch(function(e){
            spinner.classList.remove('show');
            alert('Export failed: ' + e.message);
        });
}
</script>

</body>
</html>