<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$dbname     = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// ── FILTERS ───────────────────────────────────────────────────────────────────
$filter      = $_GET['filter']  ?? 'all';
$search      = trim($_GET['search'] ?? '');
$brand_f     = trim($_GET['brand']  ?? '');
$date_from   = trim($_GET['date_from'] ?? '');
$date_to     = trim($_GET['date_to']   ?? '');
$page        = max(1, (int)($_GET['page'] ?? 1));
$per_page    = 20;
$offset      = ($page - 1) * $per_page;

$where_parts = ["1=1"];
$params      = [];
$types       = '';

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
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}
if ($brand_f !== '') { $where_parts[] = "gs.brand = ?"; $params[] = $brand_f; $types .= 's'; }
if ($date_from !== '') { $where_parts[] = "DATE(gs.generated_at) >= ?"; $params[] = $date_from; $types .= 's'; }
if ($date_to !== '')   { $where_parts[] = "DATE(gs.generated_at) <= ?"; $params[] = $date_to;   $types .= 's'; }

$where_sql = implode(' AND ', $where_parts);

$count_sql = "SELECT COUNT(*) as t FROM generated_serials_uk gs WHERE $where_sql";
if (!empty($params)) {
    $cs = $conn->prepare($count_sql);
    $cs->bind_param($types, ...$params);
    $cs->execute();
    $total_rows = $cs->get_result()->fetch_assoc()['t'];
    $cs->close();
} else {
    $total_rows = $conn->query($count_sql)->fetch_assoc()['t'];
}
$total_pages = max(1, ceil($total_rows / $per_page));
$page = min($page, $total_pages);

$data_sql = "
    SELECT gs.id, gs.serial_number, gs.icode, gs.brand, gs.description, gs.maxload, gs.date, gs.generated_at
    FROM generated_serials_uk gs
    WHERE $where_sql
    ORDER BY gs.generated_at DESC
    LIMIT $per_page OFFSET $offset
";
$rows = [];
if (!empty($params)) {
    $ds = $conn->prepare($data_sql);
    $ds->bind_param($types, ...$params);
    $ds->execute();
    $result = $ds->get_result();
    $ds->close();
} else {
    $result = $conn->query($data_sql);
}
if ($result) { while ($r = $result->fetch_assoc()) { $rows[] = $r; } }

$brands_res = $conn->query("SELECT DISTINCT brand FROM generated_serials_uk WHERE brand IS NOT NULL AND brand != '' ORDER BY brand");
$all_brands = [];
if ($brands_res) { while ($b = $brands_res->fetch_assoc()) { $all_brands[] = $b['brand']; } }

$stat_today = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE DATE(generated_at)=CURDATE()")->fetch_assoc()['t'] ?? 0;
$stat_week  = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['t'] ?? 0;
$stat_total = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk")->fetch_assoc()['t'] ?? 0;

$conn->close();

function esc($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function ago($ts) {
    $diff = time() - strtotime($ts);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('d M Y', strtotime($ts));
}
function hlSearch($str, $q) {
    if (!$q) return htmlspecialchars($str ?? '');
    return preg_replace(
        '/(' . preg_quote(htmlspecialchars($q), '/') . ')/i',
        '<span class="hl">$1</span>',
        htmlspecialchars($str ?? '')
    );
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generated Labels — Tire Label System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
/* ══════════════════════════════════════════════
   SHARED DESIGN TOKENS — DARK (default)
══════════════════════════════════════════════ */
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
    --white:       #ffffff;
    --off-white:   #f0ede8;
    --text:        #ffffff;
    --muted:       rgba(255,255,255,0.38);
    --dim:         rgba(255,255,255,0.14);
    --green:       #4ade80;
    --topbar-bg:   rgba(13,13,13,0.92);
    --hero-bg:     linear-gradient(100deg, #1a0d00 0%, #0d0d0d 55%);
    --logo-filter: brightness(1.05) drop-shadow(0 0 6px rgba(242,128,24,0.35));
    --logo-filter-hover: brightness(1.15) drop-shadow(0 0 10px rgba(242,128,24,0.6));
    --noise-opacity: 0.6;
    --input-bg:    #1e1e1e;
}

[data-theme="light"] {
    --bg:          #f5f4f0;
    --surface:     #ffffff;
    --surface2:    #f0ede8;
    --surface3:    #e8e4de;
    --border:      rgba(0,0,0,0.09);
    --border-hot:  rgba(242,128,24,0.5);
    --white:       #ffffff;
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
    --input-bg:    #f0ede8;
    --green:       #16a34a;
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
    background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events:none; z-index:0; opacity:var(--noise-opacity); transition: opacity 0.3s;
}

/* ── THEME TOGGLE ── */
.theme-toggle {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 9px;
    background: var(--surface2); border: 1px solid var(--border);
    color: var(--muted); font-size: 0.82rem; font-weight: 600;
    font-family: 'Outfit', sans-serif;
    cursor: pointer; transition: all 0.2s ease; white-space: nowrap;
}
.theme-toggle:hover { border-color: var(--border-hot); color: var(--orange); background: var(--orange-soft); }
.theme-toggle .t-icon { font-size: 0.9rem; transition: transform 0.4s ease; }
.theme-toggle:hover .t-icon { transform: rotate(20deg); }

/* ── TOPBAR ── */
.topbar {
    position:sticky; top:0; z-index:100;
    background:var(--topbar-bg); backdrop-filter:blur(14px);
    border-bottom:1px solid var(--border);
    padding:0 40px; height:68px;
    display:flex; align-items:center; justify-content:space-between;
    transition: background 0.3s, border-color 0.3s;
}
.brand { display:flex; align-items:center; gap:14px; }
.brand-logo {
    height:42px; width:auto; object-fit:contain;
    filter:var(--logo-filter); transition:filter 0.2s ease;
}
.brand-logo:hover { filter:var(--logo-filter-hover); }
.brand-divider { width:1px; height:28px; background:var(--border); flex-shrink:0; }
.brand-name { font-family:'Bebas Neue',sans-serif; font-size:1.25rem; letter-spacing:0.08em; color:var(--off-white); transition: color 0.3s; }
.topbar-right { display:flex; align-items:center; gap:10px; }
.topbar-date { font-size:0.75rem; font-weight:500; color:var(--muted); background:var(--surface2); border:1px solid var(--border); padding:7px 14px; border-radius:8px; transition: background 0.3s; }
.topbar-date i { color:var(--orange); margin-right:6px; }
.btn-t { display:inline-flex; align-items:center; gap:8px; padding:9px 20px; border-radius:9px; font-size:0.82rem; font-weight:600; font-family:'Outfit',sans-serif; border:none; cursor:pointer; text-decoration:none; transition:all 0.18s ease; }
.btn-orange { background:var(--orange); color:#fff; box-shadow:0 2px 14px var(--orange-glow); }
.btn-orange:hover { background:var(--orange-dark); color:#fff; transform:translateY(-1px); text-decoration:none; }
.btn-ghost { background:var(--surface2); border:1px solid var(--border); color:var(--off-white); }
.btn-ghost:hover { background:var(--surface3); color:var(--text); border-color:var(--border-hot); text-decoration:none; }
.btn-green { background:rgba(74,222,128,0.12); border:1px solid rgba(74,222,128,0.3); color:var(--green); }
.btn-green:hover { background:rgba(74,222,128,0.2); color:var(--green); text-decoration:none; }
[data-theme="light"] .btn-green { background:rgba(22,163,74,0.08); border-color:rgba(22,163,74,0.3); }

/* ── HERO ── */
.hero-strip {
    background:var(--hero-bg);
    border-bottom:1px solid var(--border);
    padding:28px 40px 24px; position:relative; overflow:hidden;
    transition: background 0.3s;
}
.hero-strip::after {
    content:''; position:absolute; right:-80px; top:-80px;
    width:280px; height:280px;
    background:radial-gradient(circle,rgba(242,128,24,0.1) 0%,transparent 70%);
    pointer-events:none;
}
.hero-breadcrumb { font-size:0.7rem; color:var(--muted); margin-bottom:8px; }
.hero-breadcrumb a { color:var(--muted); text-decoration:none; transition:color 0.2s; }
.hero-breadcrumb a:hover { color:var(--orange); }
.hero-breadcrumb i { margin:0 6px; font-size:0.55rem; }
.hero-title { font-family:'Bebas Neue',sans-serif; font-size:2.2rem; letter-spacing:0.04em; line-height:1; color:var(--text); }
.hero-title span { color:var(--orange); }
.hero-sub { font-size:0.8rem; color:var(--muted); margin-top:6px; }

/* ── PAGE ── */
.page { padding:28px 40px; position:relative; z-index:1; max-width:1400px; margin:0 auto; }

/* ── QUICK STATS ── */
.qs-row { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px; }
.qs-card {
    background:var(--surface); border:1px solid var(--border); border-radius:12px;
    padding:18px 22px; display:flex; align-items:center; gap:16px;
    text-decoration:none; transition:border-color 0.2s, box-shadow 0.2s, background 0.3s;
}
.qs-card:hover { border-color:var(--border-hot); box-shadow:0 0 20px var(--orange-glow); }
.qs-card.active { border-color:var(--border-hot); background:linear-gradient(135deg,rgba(28,15,0,0.6),var(--surface)); box-shadow:0 0 20px var(--orange-glow); }
[data-theme="light"] .qs-card.active { background:linear-gradient(135deg,rgba(255,220,180,0.4),var(--surface)); }
.qs-icon { width:42px; height:42px; border-radius:10px; background:var(--orange-soft); display:flex; align-items:center; justify-content:center; color:var(--orange); font-size:1.05rem; flex-shrink:0; }
.qs-card.active .qs-icon { background:var(--orange); color:#fff; }
.qs-val { font-family:'Bebas Neue',sans-serif; font-size:2rem; line-height:1; color:var(--text); }
.qs-card.active .qs-val { color:var(--orange); }
.qs-lbl { font-size:0.67rem; font-weight:700; letter-spacing:0.1em; color:var(--muted); text-transform:uppercase; margin-top:2px; }

/* ── FILTER BAR ── */
.filter-wrap {
    background:var(--surface); border:1px solid var(--border); border-radius:14px;
    padding:20px 24px; margin-bottom:20px;
    transition: background 0.3s;
}
.filter-title { font-size:0.68rem; font-weight:700; letter-spacing:0.12em; color:var(--muted); text-transform:uppercase; margin-bottom:14px; }
.filter-grid { display:grid; grid-template-columns:2fr 1fr 1fr 1fr auto; gap:12px; align-items:end; }
.f-group label { font-size:0.68rem; font-weight:700; letter-spacing:0.08em; color:var(--muted); text-transform:uppercase; display:block; margin-bottom:6px; }
.f-control {
    width:100%; background:var(--input-bg); border:1px solid var(--border);
    border-radius:8px; padding:10px 14px; font-family:'Outfit',sans-serif;
    font-size:0.82rem; color:var(--text); outline:none; transition:border-color 0.18s, background 0.3s, color 0.3s;
}
.f-control:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-soft); }
.f-control option { background:var(--surface2); color:var(--text); }
.f-control[type="date"]::-webkit-calendar-picker-indicator { filter:invert(0.5); cursor:pointer; }
[data-theme="light"] .f-control[type="date"]::-webkit-calendar-picker-indicator { filter:invert(0.3); }
.btn-apply {
    padding:10px 22px; border-radius:8px; background:var(--orange); color:#fff;
    border:none; font-family:'Outfit',sans-serif; font-size:0.82rem; font-weight:600;
    cursor:pointer; transition:all 0.18s; white-space:nowrap;
    display:inline-flex; align-items:center; gap:8px; height:42px;
}
.btn-apply:hover { background:var(--orange-dark); transform:translateY(-1px); }
.filter-tags { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
.f-tag {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--orange-soft); border:1px solid var(--border-hot);
    color:var(--orange); padding:4px 12px; border-radius:20px;
    font-size:0.7rem; font-weight:700;
}
.f-tag a { color:var(--muted); text-decoration:none; margin-left:4px; }
.f-tag a:hover { color:var(--orange); }

/* ── TOOLBAR ── */
.toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
.result-info { font-size:0.8rem; color:var(--muted); }
.result-info strong { color:var(--text); }
.toolbar-right { display:flex; align-items:center; gap:10px; }

/* ── TABLE ── */
.tbl-wrap {
    background:var(--surface); border:1px solid var(--border); border-radius:14px;
    overflow:hidden; transition: background 0.3s;
}
.tbl-wrap table { width:100%; border-collapse:collapse; }
.tbl-wrap thead th {
    background:var(--surface2); padding:13px 18px;
    font-size:0.65rem; font-weight:700; letter-spacing:0.1em; color:var(--muted);
    text-transform:uppercase; border-bottom:1px solid var(--border);
    white-space:nowrap; text-align:left;
    transition: background 0.3s;
}
.tbl-wrap tbody tr { border-bottom:1px solid var(--border); transition:background 0.12s; }
.tbl-wrap tbody tr:last-child { border-bottom:none; }
.tbl-wrap tbody tr:hover { background:rgba(242,128,24,0.04); }
.tbl-wrap tbody td { padding:13px 18px; font-size:0.83rem; vertical-align:middle; }

.td-num { font-size:0.7rem; color:var(--dim); font-weight:600; }
.td-serial { font-weight:700; color:var(--text); font-size:0.88rem; letter-spacing:0.02em; }
.td-icode {
    display:inline-flex; align-items:center;
    background:var(--orange-soft); border:1px solid var(--border-hot);
    color:var(--orange); padding:3px 10px; border-radius:6px;
    font-size:0.72rem; font-weight:700; letter-spacing:0.04em;
}
.td-brand { font-size:0.78rem; color:var(--off-white); font-weight:600; }
.td-desc { font-size:0.75rem; color:var(--muted); max-width:240px; }
.td-desc-inner { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:230px; display:block; }
.td-maxload { font-size:0.75rem; font-weight:700; color:var(--green); }
.td-date { font-size:0.72rem; color:var(--muted); line-height:1.5; white-space:nowrap; }
.td-date-main { color:var(--text); font-weight:600; font-size:0.78rem; }
.td-ago { font-size:0.65rem; color:var(--dim); }

/* ── EMPTY STATE ── */
.empty-state { text-align:center; padding:80px 24px; }
.empty-state-icon { font-size:3.5rem; color:var(--dim); margin-bottom:16px; }
.empty-state h3 { font-family:'Bebas Neue',sans-serif; font-size:1.5rem; letter-spacing:0.06em; color:var(--muted); margin-bottom:8px; }
.empty-state p { font-size:0.82rem; color:var(--dim); }

/* ── PAGINATION ── */
.pagination-wrap { display:flex; align-items:center; justify-content:space-between; margin-top:20px; flex-wrap:wrap; gap:12px; }
.pagination { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.page-btn {
    min-width:36px; height:36px; border-radius:8px; display:inline-flex;
    align-items:center; justify-content:center;
    background:var(--surface); border:1px solid var(--border);
    color:var(--muted); font-size:0.8rem; font-weight:600;
    text-decoration:none; transition:all 0.15s; padding:0 10px;
}
.page-btn:hover { border-color:var(--border-hot); color:var(--orange); background:var(--orange-soft); text-decoration:none; }
.page-btn.active { background:var(--orange); border-color:var(--orange); color:#fff; box-shadow:0 2px 10px var(--orange-glow); }
.page-btn.disabled { opacity:0.3; pointer-events:none; }
.page-info { font-size:0.75rem; color:var(--muted); }

/* ── FOOTER ── */
.footer-bar { margin-top:32px; padding:16px 0 4px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; font-size:0.72rem; color:var(--dim); }
.status { display:flex; align-items:center; gap:7px; }
.status-dot { width:7px; height:7px; border-radius:50%; background:var(--green); box-shadow:0 0 6px var(--green); }

/* ── ANIMATIONS ── */
@keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }
.qs-row      { animation:fadeUp 0.35s ease both; }
.filter-wrap { animation:fadeUp 0.35s 0.06s ease both; }
.tbl-wrap    { animation:fadeUp 0.35s 0.12s ease both; }

/* ── SEARCH HIGHLIGHT ── */
.hl { background:rgba(242,128,24,0.25); color:var(--orange); border-radius:2px; padding:0 2px; }

/* ── RESPONSIVE ── */
@media(max-width:1000px){
    .qs-row { grid-template-columns:1fr 1fr; }
    .filter-grid { grid-template-columns:1fr 1fr; }
    .page, .topbar, .hero-strip { padding-left:20px; padding-right:20px; }
    .tbl-wrap thead th:nth-child(5),
    .tbl-wrap tbody td:nth-child(5) { display:none; }
}
@media(max-width:640px){
    .qs-row { grid-template-columns:1fr; }
    .filter-grid { grid-template-columns:1fr; }
    .topbar { padding:0 16px; }
    .hero-strip, .page { padding-left:16px; padding-right:16px; }
    .brand-name, .brand-divider { display:none; }
    .topbar-date { display:none; }
    .theme-toggle span { display:none; }
}
</style>
</head>
<body>

<!-- ── TOPBAR ── -->
<header class="topbar">
    <div class="brand">
        <img src="atire.png" alt="ATire Logo" class="brand-logo" onerror="this.style.display='none'">
        <div class="brand-divider"></div>
        <div class="brand-name">Tire Label Generate System</div>
    </div>
    <div class="topbar-right">
        <div class="topbar-date"><i class="fas fa-calendar-alt"></i><?php echo date('D, d M Y · H:i'); ?></div>
        <a href="qr_system_dash.php" class="btn-t btn-ghost"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="sticker_uk.php" class="btn-t btn-orange"><i class="fas fa-plus"></i> New Labels</a>
        <!-- THEME TOGGLE -->
        <button class="theme-toggle" id="themeToggle" title="Toggle dark/light mode">
            <i class="fas fa-moon t-icon" id="themeIcon"></i>
            <span id="themeLabel">Light</span>
        </button>
    </div>
</header>

<!-- ── HERO ── -->
<div class="hero-strip">
    <div class="hero-breadcrumb">
        <a href="qr_system_dash.php"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span style="color:var(--off-white);">Generated Labels</span>
    </div>
    <div class="hero-title"><span>Generated</span> Labels</div>
    <div class="hero-sub">Complete history of all printed tire labels</div>
</div>

<!-- ── PAGE ── -->
<div class="page">

    <!-- QUICK STATS -->
    <div class="qs-row">
        <a href="?filter=all<?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="qs-card <?php echo ($filter==='all'||$filter==='')?'active':''; ?>">
            <div class="qs-icon"><i class="fas fa-archive"></i></div>
            <div class="qs-info">
                <div class="qs-val"><?php echo number_format($stat_total); ?></div>
                <div class="qs-lbl">All Time</div>
            </div>
        </a>
        <a href="?filter=week<?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="qs-card <?php echo $filter==='week'?'active':''; ?>">
            <div class="qs-icon"><i class="fas fa-calendar-week"></i></div>
            <div class="qs-info">
                <div class="qs-val"><?php echo number_format($stat_week); ?></div>
                <div class="qs-lbl">This Week</div>
            </div>
        </a>
        <a href="?filter=today<?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="qs-card <?php echo $filter==='today'?'active':''; ?>">
            <div class="qs-icon"><i class="fas fa-sun"></i></div>
            <div class="qs-info">
                <div class="qs-val"><?php echo number_format($stat_today); ?></div>
                <div class="qs-lbl">Today</div>
            </div>
        </a>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-wrap">
        <div class="filter-title"><i class="fas fa-filter" style="color:var(--orange);margin-right:6px;"></i>Filter &amp; Search</div>
        <form method="GET" action="">
            <input type="hidden" name="filter" value="<?php echo esc($filter); ?>">
            <div class="filter-grid">
                <div class="f-group">
                    <label>Search</label>
                    <input type="text" name="search" class="f-control" placeholder="Serial number, item code, description…" value="<?php echo esc($search); ?>">
                </div>
                <div class="f-group">
                    <label>Brand</label>
                    <select name="brand" class="f-control">
                        <option value="">All Brands</option>
                        <?php foreach ($all_brands as $b): ?>
                        <option value="<?php echo esc($b); ?>" <?php echo ($brand_f===$b)?'selected':''; ?>><?php echo esc($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="f-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="f-control" value="<?php echo esc($date_from); ?>">
                </div>
                <div class="f-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="f-control" value="<?php echo esc($date_to); ?>">
                </div>
                <div class="f-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-apply"><i class="fas fa-search"></i> Apply</button>
                </div>
            </div>
        </form>

        <?php $has_filters = ($search || $brand_f || $date_from || $date_to || ($filter && $filter !== 'all')); ?>
        <?php if ($has_filters): ?>
        <div class="filter-tags">
            <?php if ($filter && $filter !== 'all'): ?>
            <span class="f-tag"><i class="fas fa-clock"></i> <?php echo ucfirst($filter); ?> <a href="?filter=all&search=<?php echo urlencode($search); ?>&brand=<?php echo urlencode($brand_f); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"><i class="fas fa-times"></i></a></span>
            <?php endif; ?>
            <?php if ($search): ?>
            <span class="f-tag"><i class="fas fa-search"></i> "<?php echo esc($search); ?>" <a href="?filter=<?php echo esc($filter); ?>&brand=<?php echo urlencode($brand_f); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"><i class="fas fa-times"></i></a></span>
            <?php endif; ?>
            <?php if ($brand_f): ?>
            <span class="f-tag"><i class="fas fa-tag"></i> <?php echo esc($brand_f); ?> <a href="?filter=<?php echo esc($filter); ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"><i class="fas fa-times"></i></a></span>
            <?php endif; ?>
            <?php if ($date_from || $date_to): ?>
            <span class="f-tag"><i class="fas fa-calendar"></i> <?php echo ($date_from ?: '…') . ' → ' . ($date_to ?: '…'); ?> <a href="?filter=<?php echo esc($filter); ?>&search=<?php echo urlencode($search); ?>&brand=<?php echo urlencode($brand_f); ?>"><i class="fas fa-times"></i></a></span>
            <?php endif; ?>
            <a href="?" class="f-tag" style="background:rgba(128,128,128,0.08);border-color:var(--border);color:var(--muted);text-decoration:none;"><i class="fas fa-times-circle"></i> Clear all</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="result-info">
            Showing <strong><?php echo number_format(count($rows)); ?></strong> of <strong><?php echo number_format($total_rows); ?></strong> records
            <?php if ($search): ?> for <strong>"<?php echo esc($search); ?>"</strong><?php endif; ?>
        </div>
        <div class="toolbar-right">
            <a href="export_generated.php?<?php echo http_build_query(array_filter(['filter'=>$filter,'search'=>$search,'brand'=>$brand_f,'date_from'=>$date_from,'date_to'=>$date_to])); ?>" class="btn-t btn-green" style="font-size:0.78rem; padding:8px 16px;">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- TABLE -->
    <div class="tbl-wrap">
        <?php if (empty($rows)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-history"></i></div>
            <h3>No Labels Found</h3>
            <p>No generated labels match your current filters.</p>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width:48px;">#</th>
                    <th>Serial Number</th>
                    <th>Item Code</th>
                    <th>Brand</th>
                    <th>Description</th>
                    <th>Max Load</th>
                    <th>Generated At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $i => $row):
                    $row_num = $offset + $i + 1;
                ?>
                <tr>
                    <td class="td-num"><?php echo $row_num; ?></td>
                    <td><div class="td-serial"><?php echo hlSearch($row['serial_number'], $search); ?></div></td>
                    <td><span class="td-icode"><?php echo hlSearch($row['icode'], $search); ?></span></td>
                    <td class="td-brand"><?php echo esc($row['brand'] ?? '—'); ?></td>
                    <td class="td-desc">
                        <span class="td-desc-inner" title="<?php echo esc($row['description'] ?? ''); ?>">
                            <?php echo hlSearch($row['description'], $search); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['maxload']): ?>
                        <span class="td-maxload"><?php echo esc($row['maxload']); ?> kg</span>
                        <?php else: ?><span style="color:var(--dim);">—</span><?php endif; ?>
                    </td>
                    <td class="td-date">
                        <div class="td-date-main"><?php echo date('d M Y', strtotime($row['generated_at'])); ?></div>
                        <div><?php echo date('H:i', strtotime($row['generated_at'])); ?></div>
                        <div class="td-ago"><?php echo ago($row['generated_at']); ?></div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <?php $base_q = array_filter(['filter'=>$filter,'search'=>$search,'brand'=>$brand_f,'date_from'=>$date_from,'date_to'=>$date_to]); ?>
    <div class="pagination-wrap">
        <div class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?> &mdash; <?php echo number_format($total_rows); ?> total records</div>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($base_q, ['page'=>1])); ?>" class="page-btn"><i class="fas fa-angle-double-left"></i></a>
            <a href="?<?php echo http_build_query(array_merge($base_q, ['page'=>$page-1])); ?>" class="page-btn"><i class="fas fa-angle-left"></i></a>
            <?php else: ?>
            <span class="page-btn disabled"><i class="fas fa-angle-double-left"></i></span>
            <span class="page-btn disabled"><i class="fas fa-angle-left"></i></span>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            if ($start > 1) echo '<span class="page-btn disabled">…</span>';
            for ($p = $start; $p <= $end; $p++):
            ?>
            <a href="?<?php echo http_build_query(array_merge($base_q, ['page'=>$p])); ?>" class="page-btn <?php echo $p===$page?'active':''; ?>"><?php echo $p; ?></a>
            <?php endfor;
            if ($end < $total_pages) echo '<span class="page-btn disabled">…</span>';
            ?>

            <?php if ($page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($base_q, ['page'=>$page+1])); ?>" class="page-btn"><i class="fas fa-angle-right"></i></a>
            <a href="?<?php echo http_build_query(array_merge($base_q, ['page'=>$total_pages])); ?>" class="page-btn"><i class="fas fa-angle-double-right"></i></a>
            <?php else: ?>
            <span class="page-btn disabled"><i class="fas fa-angle-right"></i></span>
            <span class="page-btn disabled"><i class="fas fa-angle-double-right"></i></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="footer-bar">
        <div class="status"><div class="status-dot"></div>System online — <?php echo date('d M Y, H:i:s'); ?></div>
        <div>Tire Label Management &copy; <?php echo date('Y'); ?></div>
    </div>

</div>

<script>
/* ── THEME SYSTEM ── */
(function(){
    var html  = document.documentElement;
    var btn   = document.getElementById('themeToggle');
    var icon  = document.getElementById('themeIcon');
    var label = document.getElementById('themeLabel');

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('tlsTheme', theme);
        if (theme === 'dark') {
            icon.className  = 'fas fa-sun t-icon';
            label.textContent = 'Light';
        } else {
            icon.className  = 'fas fa-moon t-icon';
            label.textContent = 'Dark';
        }
    }

    var saved = localStorage.getItem('tlsTheme') || 'dark';
    applyTheme(saved);

    btn.addEventListener('click', function(){
        var current = html.getAttribute('data-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });
})();

document.querySelector('input[name="search"]').addEventListener('keydown', function(e){
    if (e.key === 'Enter') this.closest('form').submit();
});
</script>
</body>
</html>
