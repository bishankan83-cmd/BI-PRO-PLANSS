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

// ── STATS ─────────────────────────────────────────────────────────────────────
$queue_total      = $conn->query("SELECT COUNT(*) as t FROM get_serial_uk")->fetch_assoc()['t'] ?? 0;
$generated_today  = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE DATE(generated_at) = CURDATE()")->fetch_assoc()['t'] ?? 0;
$generated_week   = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['t'] ?? 0;
$generated_month  = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE MONTH(generated_at)=MONTH(NOW()) AND YEAR(generated_at)=YEAR(NOW())")->fetch_assoc()['t'] ?? 0;
$generated_total  = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk")->fetch_assoc()['t'] ?? 0;

// Monthly trend (last 6 months)
$monthly_result = $conn->query("
    SELECT DATE_FORMAT(generated_at,'%b') AS lbl,
           DATE_FORMAT(generated_at,'%Y-%m') AS mk,
           COUNT(*) AS cnt
    FROM generated_serials_uk
    WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mk, lbl ORDER BY mk ASC
");
$monthly_labels = []; $monthly_counts = [];
if ($monthly_result) { while ($r = $monthly_result->fetch_assoc()) { $monthly_labels[] = $r['lbl']; $monthly_counts[] = (int)$r['cnt']; } }
if (empty($monthly_labels)) { $monthly_labels = ['No Data']; $monthly_counts = [0]; }

// Recent activity (last 8)
$recent_result = $conn->query("
    SELECT gs.serial_number, gs.icode, td.brand, td.description, gs.generated_at
    FROM generated_serials_uk gs
    LEFT JOIN tire_details td ON gs.icode = td.icode
    ORDER BY gs.generated_at DESC LIMIT 8
");
$recent = [];
if ($recent_result) { while ($r = $recent_result->fetch_assoc()) { $recent[] = $r; } }

// Top brands (30 days)
$brands_result = $conn->query("
    SELECT td.brand, COUNT(*) AS cnt
    FROM generated_serials_uk gs
    LEFT JOIN tire_details td ON gs.icode = td.icode
    WHERE gs.generated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
      AND td.brand IS NOT NULL AND td.brand != ''
    GROUP BY td.brand ORDER BY cnt DESC LIMIT 5
");
$top_brands = [];
if ($brands_result) { while ($r = $brands_result->fetch_assoc()) { $top_brands[] = $r; } }

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Tire Label System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root {
    --orange:      #F28018;
    --orange-dark: #c8660e;
    --orange-glow: rgba(242,128,24,0.18);
    --orange-soft: rgba(242,128,24,0.08);
    --bg:          #0d0d0d;
    --surface:     #161616;
    --surface2:    #1e1e1e;
    --border:      rgba(255,255,255,0.07);
    --border-hot:  rgba(242,128,24,0.45);
    --white:       #ffffff;
    --off-white:   #f0ede8;
    --muted:       rgba(255,255,255,0.38);
    --dim:         rgba(255,255,255,0.14);
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Outfit', sans-serif;
    background: var(--bg);
    color: var(--white);
    min-height: 100vh;
    overflow-x: hidden;
}
body::before {
    content: '';
    position: fixed; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events: none; z-index: 0; opacity: 0.6;
}

/* ── TOPBAR ── */
.topbar {
    position: sticky; top: 0; z-index: 100;
    background: rgba(13,13,13,0.92);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
    padding: 0 40px; height: 68px;
    display: flex; align-items: center; justify-content: space-between;
}
.brand { display: flex; align-items: center; gap: 14px; }
.brand-name { font-family: 'Bebas Neue', sans-serif; font-size: 1.25rem; letter-spacing: 0.08em; line-height: 1; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-date {
    font-size: 0.75rem; font-weight: 500; color: var(--muted);
    background: var(--surface2); border: 1px solid var(--border);
    padding: 7px 14px; border-radius: 8px;
}
.topbar-date i { color: var(--orange); margin-right: 6px; }
.btn-t {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 20px; border-radius: 9px;
    font-size: 0.82rem; font-weight: 600; font-family: 'Outfit', sans-serif;
    border: none; cursor: pointer; text-decoration: none; transition: all 0.18s ease;
}
.btn-orange { background: var(--orange); color: #fff; box-shadow: 0 2px 14px var(--orange-glow); }
.btn-orange:hover { background: var(--orange-dark); color: #fff; box-shadow: 0 4px 22px rgba(242,128,24,0.45); transform: translateY(-1px); }
.btn-ghost  { background: var(--surface2); border: 1px solid var(--border); color: var(--off-white); }
.btn-ghost:hover { background: var(--surface); color: #fff; border-color: var(--border-hot); }

/* ── HERO STRIP ── */
.hero-strip {
    background: linear-gradient(100deg, #1a0d00 0%, #0d0d0d 55%);
    border-bottom: 1px solid var(--border);
    padding: 36px 40px 30px;
    position: relative; overflow: hidden;
}
.hero-strip::after {
    content: ''; position: absolute; right: -80px; top: -80px;
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(242,128,24,0.12) 0%, transparent 70%);
    pointer-events: none;
}
.hero-title { font-family: 'Bebas Neue', sans-serif; font-size: 2.6rem; letter-spacing: 0.04em; line-height: 1; }
.hero-title span { color: var(--orange); }

/* ── PAGE ── */
.page { padding: 36px 40px; position: relative; z-index: 1; max-width: 1360px; margin: 0 auto; }

/* ── HERO STAT CARDS ── */
.hero-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px; }
.hero-stat {
    border-radius: 16px; padding: 32px 34px;
    position: relative; overflow: hidden;
    border: 1px solid var(--border);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.hero-stat:hover { border-color: var(--border-hot); box-shadow: 0 0 32px var(--orange-glow); }
.hero-stat.pending {
    background: linear-gradient(135deg, #1c0f00 0%, #161616 60%);
    border-color: var(--border-hot);
    box-shadow: 0 0 28px var(--orange-glow);
}
.hero-stat.today { background: var(--surface); }
.hs-label {
    font-size: 0.68rem; font-weight: 700; letter-spacing: 0.14em;
    text-transform: uppercase; color: var(--muted);
    margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
}
.hs-label i { color: var(--orange); font-size: 0.85rem; }
.hs-value {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 5.5rem; line-height: 1; letter-spacing: 0.02em; color: var(--orange);
}
.hero-stat.today .hs-value { color: var(--white); }
.hs-footer { margin-top: 16px; display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: var(--muted); flex-wrap: wrap; }
.pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; }
.pill-orange { background: var(--orange-glow); color: var(--orange); border: 1px solid var(--border-hot); }
.pill-white  { background: rgba(255,255,255,0.08); color: var(--off-white); border: 1px solid var(--border); }
.hs-bg-icon { position: absolute; right: 24px; bottom: 16px; font-size: 5.5rem; opacity: 0.05; pointer-events: none; }

/* ── INFO ROW ── */
.info-row { display: grid; grid-template-columns: 1.7fr 1fr; gap: 20px; margin-bottom: 28px; }

/* ── CARD ── */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: border-color 0.2s; }
.card:hover { border-color: rgba(242,128,24,0.25); }
.card-head {
    padding: 18px 24px 14px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.card-head-title { font-family: 'Bebas Neue', sans-serif; font-size: 1.05rem; letter-spacing: 0.08em; }
.card-head-title i { color: var(--orange); margin-right: 8px; }
.card-tag { font-size: 0.65rem; font-weight: 600; letter-spacing: 0.07em; color: var(--muted); background: var(--surface2); border: 1px solid var(--border); padding: 3px 10px; border-radius: 6px; }
.card-body { padding: 20px 24px; }
.chart-wrap { position: relative; width: 100%; height: 200px; }

/* ── BRAND BARS ── */
.brand-rows { display: flex; flex-direction: column; gap: 14px; }
.b-row { display: flex; align-items: center; gap: 12px; }
.b-name { font-size: 0.8rem; font-weight: 600; color: var(--off-white); width: 90px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.b-track { flex: 1; height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
.b-fill { height: 100%; background: linear-gradient(90deg, var(--orange), #ffb060); border-radius: 3px; transition: width 0.7s cubic-bezier(.25,.8,.25,1); }
.b-num { font-size: 0.72rem; font-weight: 600; color: var(--muted); min-width: 28px; text-align: right; }

/* ── BOTTOM ROW ── */
.bottom-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

/* ── SUMMARY MINI CARDS ── */
.mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mini-card {
    background: var(--surface2); border: 1px solid var(--border); border-radius: 10px;
    padding: 18px 20px; position: relative; overflow: hidden;
    text-decoration: none; display: block; transition: border-color 0.2s, box-shadow 0.2s;
}
.mini-card:hover { border-color: var(--border-hot); box-shadow: 0 0 18px var(--orange-glow); }
.mini-card.hot { background: linear-gradient(135deg,#1c0f00,var(--surface2)); border-color: var(--border-hot); }
.mc-label { font-size: 0.63rem; font-weight: 700; letter-spacing: .12em; color: var(--muted); margin-bottom: 8px; text-transform: uppercase; }
.mc-value { font-family: 'Bebas Neue', sans-serif; font-size: 2.4rem; color: var(--white); line-height: 1; }
.mini-card.hot .mc-value { color: var(--orange); }
.mc-sub { font-size: 0.68rem; color: var(--muted); margin-top: 6px; }
.mc-icon { position: absolute; right: 12px; bottom: 8px; font-size: 2.5rem; opacity: 0.05; }
.mini-card.hot .mc-icon { opacity: 0.09; color: var(--orange); }

/* ── VIEW ALL LINK ── */
.view-all-link {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    margin-top: 16px; padding: 11px 18px; border-radius: 9px;
    background: var(--surface2); border: 1px solid var(--border);
    color: var(--off-white); font-size: 0.82rem; font-weight: 600;
    text-decoration: none; transition: all 0.18s ease;
}
.view-all-link:hover { background: var(--orange-soft); border-color: var(--border-hot); color: var(--orange); }
.view-all-link i { color: var(--orange); }

/* ── ACTIVITY ── */
.act-list { display: flex; flex-direction: column; }
.act-item { display: flex; align-items: center; gap: 14px; padding: 11px 0; border-bottom: 1px solid var(--border); }
.act-item:last-child { border-bottom: none; }
.act-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--orange); flex-shrink: 0; }
.act-info { flex: 1; min-width: 0; }
.act-serial { font-size: 0.8rem; font-weight: 600; color: var(--white); }
.act-desc { font-size: 0.7rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.act-badge { font-size: 0.63rem; font-weight: 700; padding: 2px 8px; border-radius: 5px; background: var(--orange-soft); color: var(--orange); border: 1px solid var(--border-hot); white-space: nowrap; }
.act-time { font-size: 0.65rem; color: var(--dim); white-space: nowrap; text-align: right; line-height: 1.5; }

/* ── EMPTY ── */
.empty { text-align: center; padding: 40px 20px; color: var(--dim); }
.empty i { font-size: 2rem; display: block; margin-bottom: 8px; }

/* ── FOOTER ── */
.footer-bar { margin-top: 36px; padding: 18px 0 4px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: var(--dim); }
.status { display: flex; align-items: center; gap: 7px; }
.status-dot { width: 7px; height: 7px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 6px #4ade80; }

/* ── ANIMATIONS ── */
@keyframes fadeUp { from { opacity:0; transform: translateY(14px); } to { opacity:1; transform: none; } }
.hero-cards  { animation: fadeUp 0.4s ease both; }
.info-row    { animation: fadeUp 0.4s 0.08s ease both; }
.bottom-row  { animation: fadeUp 0.4s 0.16s ease both; }

/* ── RESPONSIVE ── */
@media(max-width:900px){
    .hero-cards, .info-row, .bottom-row, .mini-grid { grid-template-columns: 1fr; }
    .page, .topbar, .hero-strip { padding-left: 20px; padding-right: 20px; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
    <div class="brand">
        <div>
            <div class="brand-name">Tire Label Generate System</div>
        </div>
    </div>
    <div class="topbar-right">
        <div class="topbar-date">
            <i class="fas fa-calendar-alt"></i><?php echo date('D, d M Y · H:i'); ?>
        </div>
        <a href="view_generated_label.php" class="btn-t btn-ghost">
            <i class="fas fa-history"></i> View Generated
        </a>
        <?php if ($queue_total > 0): ?>
        <a href="sticker_uk.php" class="btn-t btn-orange">
            <i class="fas fa-file-pdf"></i> Generate Batch
            <span style="background:rgba(255,255,255,0.22);padding:1px 8px;border-radius:12px;font-size:0.7rem;"><?php echo $queue_total; ?></span>
        </a>
        <?php else: ?>
        <a href="sticker_uk.php" class="btn-t btn-ghost"><i class="fas fa-plus"></i> New Label</a>
        <?php endif; ?>
        <a href="sticker_uk.php" class="btn-t btn-ghost"><i class="fas fa-tags"></i> Labels</a>
    </div>
</header>

<!-- HERO STRIP -->
<div class="hero-strip">
    <div class="hero-title"><span>Dashboard</span></div>
</div>

<!-- PAGE -->
<div class="page">

    <!-- HERO CARDS -->
    <div class="hero-cards">
        <div class="hero-stat pending">
            <div class="hs-label"><i class="fas fa-layer-group"></i> Pending Queue</div>
            <div class="hs-value"><?php echo number_format($queue_total); ?></div>
            <div class="hs-footer">
                <span class="pill pill-orange"><i class="fas fa-circle" style="font-size:.4rem;"></i> Awaiting PDF generation</span>
            </div>
            <i class="fas fa-layer-group hs-bg-icon"></i>
        </div>
        <div class="hero-stat today">
            <div class="hs-label"><i class="fas fa-print"></i> Generated Today</div>
            <div class="hs-value"><?php echo number_format($generated_today); ?></div>
            <div class="hs-footer">
                <span class="pill pill-orange"><i class="fas fa-arrow-up" style="font-size:.6rem;"></i> <?php echo $generated_week; ?> this week</span>
                <span class="pill pill-white"><?php echo $generated_month; ?> this month</span>
            </div>
            <i class="fas fa-print hs-bg-icon"></i>
        </div>
    </div>

    <!-- CHART + TOP BRANDS -->
    <div class="info-row">
        <div class="card">
            <div class="card-head">
                <div class="card-head-title"><i class="fas fa-chart-bar"></i>Monthly Label Output</div>
                <span class="card-tag">Last 6 months</span>
            </div>
            <div class="card-body">
                <div class="chart-wrap"><canvas id="monthlyChart"></canvas></div>
            </div>
        </div>
        <div class="card">
            <div class="card-head">
                <div class="card-head-title"><i class="fas fa-trophy"></i>Top Brands</div>
                <span class="card-tag">30 days</span>
            </div>
            <div class="card-body">
                <?php if (!empty($top_brands)): $max = $top_brands[0]['cnt']; ?>
                <div class="brand-rows">
                    <?php foreach ($top_brands as $b): ?>
                    <div class="b-row">
                        <span class="b-name"><?php echo htmlspecialchars($b['brand']); ?></span>
                        <div class="b-track"><div class="b-fill" style="width:<?php echo round(($b['cnt']/$max)*100); ?>%"></div></div>
                        <span class="b-num"><?php echo $b['cnt']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty"><i class="fas fa-chart-bar"></i>No data yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SUMMARY + RECENT ACTIVITY -->
    <div class="bottom-row">

        <div class="card">
            <div class="card-head">
                <div class="card-head-title"><i class="fas fa-database"></i>Summary</div>
                <span class="card-tag">All time</span>
            </div>
            <div class="card-body">
                <div class="mini-grid">
                    <a href="view_generated_label.php" class="mini-card" style="text-decoration:none;">
                        <div class="mc-label">Total Generated</div>
                        <div class="mc-value" style="color:var(--orange);"><?php echo number_format($generated_total); ?></div>
                        <div class="mc-sub">All-time labels</div>
                        <i class="fas fa-archive mc-icon"></i>
                    </a>
                    <a href="view_generated_label.php?filter=week" class="mini-card" style="text-decoration:none;">
                        <div class="mc-label">This Week</div>
                        <div class="mc-value"><?php echo number_format($generated_week); ?></div>
                        <div class="mc-sub">Last 7 days</div>
                        <i class="fas fa-calendar-week mc-icon"></i>
                    </a>
                    <a href="view_generated_label.php?filter=month" class="mini-card" style="text-decoration:none;">
                        <div class="mc-label">This Month</div>
                        <div class="mc-value"><?php echo number_format($generated_month); ?></div>
                        <div class="mc-sub"><?php echo date('F Y'); ?></div>
                        <i class="fas fa-calendar-alt mc-icon"></i>
                    </a>
                    <a href="sticker_uk.php" class="mini-card hot" style="text-decoration:none;">
                        <div class="mc-label">In Queue</div>
                        <div class="mc-value"><?php echo number_format($queue_total); ?></div>
                        <div class="mc-sub">Ready to print</div>
                        <i class="fas fa-layer-group mc-icon"></i>
                    </a>
                </div>
                <a href="view_generated_label.php" class="view-all-link mt-3">
                    <i class="fas fa-history"></i> View All Generated Labels
                    <i class="fas fa-arrow-right" style="margin-left:4px; font-size:0.75rem;"></i>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div class="card-head-title"><i class="fas fa-bolt"></i>Recent Activity</div>
                <a href="view_generated_label.php" style="font-size:0.72rem;color:var(--orange);text-decoration:none;font-weight:600;">View all <i class="fas fa-arrow-right" style="font-size:0.65rem;"></i></a>
            </div>
            <div class="card-body" style="padding: 0 24px;">
                <?php if (!empty($recent)): ?>
                <div class="act-list">
                    <?php foreach ($recent as $item): ?>
                    <div class="act-item">
                        <div class="act-dot"></div>
                        <div class="act-info">
                            <div class="act-serial"><?php echo htmlspecialchars($item['serial_number']); ?></div>
                            <div class="act-desc"><?php echo htmlspecialchars($item['description'] ?? '—'); ?></div>
                        </div>
                        <span class="act-badge"><?php echo htmlspecialchars($item['icode']); ?></span>
                        <div class="act-time"><?php echo date('d M', strtotime($item['generated_at'])); ?><br><?php echo date('H:i', strtotime($item['generated_at'])); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty"><i class="fas fa-history"></i>No labels generated yet</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer-bar">
        <div class="status"><div class="status-dot"></div>System online — <?php echo date('d M Y, H:i:s'); ?></div>
        <div>Tire Label Management &copy; <?php echo date('Y'); ?></div>
    </div>

</div>

<script>
Chart.defaults.color = 'rgba(255,255,255,0.38)';
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.font.size = 11;
(function(){
    var ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($monthly_labels); ?>,
            datasets: [{
                label: 'Labels',
                data: <?php echo json_encode($monthly_counts); ?>,
                backgroundColor: function(c){
                    var ch = c.chart, ca = ch.chartArea;
                    if (!ca) return 'rgba(242,128,24,0.75)';
                    var g = ch.ctx.createLinearGradient(0, ca.top, 0, ca.bottom);
                    g.addColorStop(0,'rgba(242,128,24,0.95)');
                    g.addColorStop(1,'rgba(242,128,24,0.15)');
                    return g;
                },
                borderColor: '#F28018', borderWidth: 1,
                borderRadius: 6, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e1e1e', borderColor: '#F28018', borderWidth: 1, padding: 10,
                    callbacks: { label: c => '  ' + c.raw + ' labels' }
                }
            },
            scales: {
                x: { grid: { color:'rgba(255,255,255,0.04)' }, border: { color:'rgba(255,255,255,0.07)' } },
                y: { grid: { color:'rgba(255,255,255,0.04)' }, border: { color:'rgba(255,255,255,0.07)' }, beginAtZero:true, ticks:{ stepSize:1 } }
            }
        }
    });
})();
</script>
</body>
</html>