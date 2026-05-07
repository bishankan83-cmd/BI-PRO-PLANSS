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

// ── LABEL STATS ───────────────────────────────────────────────────────────────
$queue_total      = $conn->query("SELECT COUNT(*) as t FROM get_serial_uk")->fetch_assoc()['t'] ?? 0;
$generated_today  = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE DATE(generated_at) = CURDATE()")->fetch_assoc()['t'] ?? 0;
$generated_week   = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['t'] ?? 0;
$generated_month  = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk WHERE MONTH(generated_at)=MONTH(NOW()) AND YEAR(generated_at)=YEAR(NOW())")->fetch_assoc()['t'] ?? 0;
$generated_total  = $conn->query("SELECT COUNT(*) as t FROM generated_serials_uk")->fetch_assoc()['t'] ?? 0;

// ── VERIFICATION STATS ────────────────────────────────────────────────────────
$grn_today   = 0; $grn_total   = 0;
$stock_today = 0; $stock_total = 0;

$tbl_check = $conn->query("SHOW TABLES LIKE 'grn_verification'");
if ($tbl_check && $tbl_check->num_rows > 0) {
    $grn_today = $conn->query("SELECT COUNT(*) as t FROM grn_verification WHERE DATE(verified_at) = CURDATE()")->fetch_assoc()['t'] ?? 0;
    $grn_total = $conn->query("SELECT COUNT(*) as t FROM grn_verification")->fetch_assoc()['t'] ?? 0;
}
$tbl_check2 = $conn->query("SHOW TABLES LIKE 'stock_verification'");
if ($tbl_check2 && $tbl_check2->num_rows > 0) {
    $stock_today = $conn->query("SELECT COUNT(*) as t FROM stock_verification WHERE DATE(verified_at) = CURDATE()")->fetch_assoc()['t'] ?? 0;
    $stock_total = $conn->query("SELECT COUNT(*) as t FROM stock_verification")->fetch_assoc()['t'] ?? 0;
}

// ── CHART DATA ────────────────────────────────────────────────────────────────
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

// ── RECENT GENERATED ─────────────────────────────────────────────────────────
$recent_result = $conn->query("
    SELECT gs.serial_number, gs.icode, td.brand, td.description, gs.generated_at
    FROM generated_serials_uk gs
    LEFT JOIN tire_details td ON gs.icode = td.icode
    ORDER BY gs.generated_at DESC LIMIT 8
");
$recent = [];
if ($recent_result) { while ($r = $recent_result->fetch_assoc()) { $recent[] = $r; } }

// ── TOP BRANDS ────────────────────────────────────────────────────────────────
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
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>Dashboard — Tire Label System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ══════════════════════════════════════════════
   DESIGN TOKENS
══════════════════════════════════════════════ */
:root,
[data-theme="dark"] {
    --orange:        #F28018;
    --orange-dark:   #c8660e;
    --orange-glow:   rgba(242,128,24,0.18);
    --orange-soft:   rgba(242,128,24,0.08);
    --bg:            #0d0d0d;
    --surface:       #161616;
    --surface2:      #1e1e1e;
    --surface3:      #242424;
    --border:        rgba(255,255,255,0.07);
    --border-hot:    rgba(242,128,24,0.45);
    --text:          #ffffff;
    --off-white:     #f0ede8;
    --muted:         rgba(255,255,255,0.38);
    --dim:           rgba(255,255,255,0.14);
    --green:         #4ade80;
    --blue:          #60a5fa;
    --blue-glow:     rgba(96,165,250,0.18);
    --blue-soft:     rgba(96,165,250,0.08);
    --blue-border:   rgba(96,165,250,0.45);
    --purple:        #a78bfa;
    --purple-glow:   rgba(167,139,250,0.18);
    --purple-soft:   rgba(167,139,250,0.08);
    --purple-border: rgba(167,139,250,0.45);
    --topbar-bg:     rgba(13,13,13,0.95);
    --hero-bg:       linear-gradient(100deg,#1a0d00 0%,#0d0d0d 55%);
    --logo-filter:   brightness(1.05) drop-shadow(0 0 6px rgba(242,128,24,0.35));
    --noise-opacity: 0.6;
    --chart-text:    rgba(255,255,255,0.38);
    --chart-grid:    rgba(255,255,255,0.04);
    --chart-border:  rgba(255,255,255,0.07);
    --drawer-bg:     #121212;
    --safe-bottom:   env(safe-area-inset-bottom, 0px);
    --safe-top:      env(safe-area-inset-top, 0px);
}

[data-theme="light"] {
    --bg:            #f5f4f0;
    --surface:       #ffffff;
    --surface2:      #f0ede8;
    --surface3:      #e8e4de;
    --border:        rgba(0,0,0,0.09);
    --border-hot:    rgba(242,128,24,0.5);
    --text:          #1a1a1a;
    --off-white:     #1a1a1a;
    --muted:         rgba(0,0,0,0.45);
    --dim:           rgba(0,0,0,0.25);
    --topbar-bg:     rgba(255,255,255,0.96);
    --hero-bg:       linear-gradient(100deg,#fff0e0 0%,#f5f4f0 55%);
    --logo-filter:   brightness(0.9) drop-shadow(0 0 6px rgba(242,128,24,0.2));
    --noise-opacity: 0.15;
    --chart-text:    rgba(0,0,0,0.45);
    --chart-grid:    rgba(0,0,0,0.05);
    --chart-border:  rgba(0,0,0,0.09);
    --orange-glow:   rgba(242,128,24,0.12);
    --orange-soft:   rgba(242,128,24,0.07);
    --blue-glow:     rgba(96,165,250,0.12);
    --blue-soft:     rgba(96,165,250,0.07);
    --blue-border:   rgba(96,165,250,0.4);
    --purple:        #7c3aed;
    --purple-glow:   rgba(124,58,237,0.12);
    --purple-soft:   rgba(124,58,237,0.07);
    --purple-border: rgba(124,58,237,0.4);
    --drawer-bg:     #ffffff;
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

html { scroll-behavior: smooth; }

body {
    font-family: 'Outfit', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    overflow-x: hidden;
    transition: background 0.3s, color 0.3s;
    /* Account for iOS bottom safe area */
    padding-bottom: var(--safe-bottom);
}
body::before {
    content: '';
    position: fixed; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events: none; z-index: 0; opacity: var(--noise-opacity);
}

/* ══ TOPBAR ══════════════════════════════════════════════════════════════ */
.topbar {
    position: sticky; top: 0; z-index: 200;
    background: var(--topbar-bg);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
    padding: 0 20px;
    padding-top: var(--safe-top);
    height: calc(64px + var(--safe-top));
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px;
    transition: background 0.3s, border-color 0.3s;
}

.brand { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.brand-logo {
    height: 36px; width: auto; object-fit: contain; display: block;
    filter: var(--logo-filter);
}
.brand-divider { width: 1px; height: 24px; background: var(--border); }
.brand-name {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.1rem; letter-spacing: 0.08em;
    color: var(--off-white);
}

/* Desktop nav buttons */
.topbar-nav {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: nowrap; overflow: hidden;
}

/* Hamburger — mobile only */
.hamburger {
    display: none;
    flex-direction: column; justify-content: center; align-items: center;
    gap: 5px; width: 40px; height: 40px; border-radius: 10px;
    background: var(--surface2); border: 1px solid var(--border);
    cursor: pointer; flex-shrink: 0;
    transition: border-color 0.2s;
}
.hamburger:hover { border-color: var(--border-hot); }
.hamburger span {
    display: block; width: 18px; height: 2px;
    background: var(--text); border-radius: 2px;
    transition: transform 0.3s, opacity 0.3s;
}
.hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity: 0; }
.hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ══ MOBILE DRAWER ═══════════════════════════════════════════════════════ */
.drawer-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.6); z-index: 300;
    opacity: 0; transition: opacity 0.3s;
}
.drawer-overlay.open { display: block; opacity: 1; }

.drawer {
    position: fixed; top: 0; right: -300px; bottom: 0;
    width: 280px; max-width: 85vw;
    background: var(--drawer-bg);
    border-left: 1px solid var(--border);
    z-index: 400;
    padding: calc(20px + var(--safe-top)) 20px 20px;
    display: flex; flex-direction: column; gap: 8px;
    transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
}
.drawer.open { right: 0; }
.drawer-header {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1rem; letter-spacing: 0.1em; color: var(--muted);
    margin-bottom: 8px; padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}
.drawer-item {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 16px; border-radius: 10px;
    background: var(--surface2); border: 1px solid var(--border);
    color: var(--text); font-size: 0.88rem; font-weight: 600;
    text-decoration: none; transition: all 0.18s ease;
    cursor: pointer; font-family: 'Outfit', sans-serif;
}
.drawer-item:hover, .drawer-item:active { border-color: var(--border-hot); color: var(--orange); background: var(--orange-soft); }
.drawer-item.blue   { border-color: var(--blue-border); color: var(--blue); background: var(--blue-soft); }
.drawer-item.blue:hover { background: var(--blue-soft); }
.drawer-item.purple { border-color: var(--purple-border); color: var(--purple); background: var(--purple-soft); }
.drawer-item.purple:hover { background: var(--purple-soft); }
.drawer-item.orange { background: var(--orange); color: #fff; border-color: transparent; }
.drawer-item.orange:hover { background: var(--orange-dark); color: #fff; }
.drawer-item i { width: 18px; text-align: center; flex-shrink: 0; }
.drawer-badge {
    margin-left: auto; background: rgba(255,255,255,0.2); padding: 1px 8px;
    border-radius: 12px; font-size: 0.72rem;
}
.drawer-item.orange .drawer-badge { background: rgba(255,255,255,0.22); }
.drawer-sep { height: 1px; background: var(--border); margin: 4px 0; }

/* ══ BUTTONS ═════════════════════════════════════════════════════════════ */
.btn-t {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 18px; border-radius: 9px;
    font-size: 0.8rem; font-weight: 600; font-family: 'Outfit', sans-serif;
    border: none; cursor: pointer; text-decoration: none;
    transition: all 0.18s ease; white-space: nowrap; flex-shrink: 0;
}
.btn-orange { background: var(--orange); color: #fff; box-shadow: 0 2px 14px var(--orange-glow); }
.btn-orange:hover { background: var(--orange-dark); color: #fff; transform: translateY(-1px); }
.btn-ghost  { background: var(--surface2); border: 1px solid var(--border); color: var(--off-white); }
.btn-ghost:hover { background: var(--surface3); border-color: var(--border-hot); text-decoration: none; color: var(--text); }
.btn-blue   { background: var(--blue); color: #fff; box-shadow: 0 2px 14px var(--blue-glow); }
.btn-blue:hover { background: #3b82f6; color: #fff; transform: translateY(-1px); }
.btn-purple { background: var(--surface2); border: 1px solid var(--purple-border); color: var(--purple); }
.btn-purple:hover { background: var(--purple-soft); transform: translateY(-1px); text-decoration: none; color: var(--purple); }

/* Theme toggle */
.theme-toggle {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 14px; border-radius: 9px;
    background: var(--surface2); border: 1px solid var(--border);
    color: var(--muted); font-size: 0.8rem; font-weight: 600;
    font-family: 'Outfit', sans-serif;
    cursor: pointer; transition: all 0.2s ease; flex-shrink: 0;
}
.theme-toggle:hover { border-color: var(--border-hot); color: var(--orange); }
.theme-toggle .t-icon { transition: transform 0.4s ease; }
.theme-toggle:hover .t-icon { transform: rotate(20deg); }

/* Date badge */
.topbar-date {
    font-size: 0.72rem; font-weight: 500; color: var(--muted);
    background: var(--surface2); border: 1px solid var(--border);
    padding: 7px 12px; border-radius: 8px; white-space: nowrap;
    flex-shrink: 0;
}
.topbar-date i { color: var(--orange); margin-right: 5px; }

/* ══ HERO STRIP ══════════════════════════════════════════════════════════ */
.hero-strip {
    background: var(--hero-bg);
    border-bottom: 1px solid var(--border);
    padding: 28px 20px 22px;
    position: relative; overflow: hidden;
}
.hero-strip::after {
    content: ''; position: absolute; right: -60px; top: -60px;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(242,128,24,0.12) 0%, transparent 70%);
    pointer-events: none;
}
.hero-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(1.8rem, 5vw, 2.6rem);
    letter-spacing: 0.04em; line-height: 1; color: var(--text);
}
.hero-title span { color: var(--orange); }
.hero-sub { font-size: 0.78rem; color: var(--muted); margin-top: 4px; }

/* ══ PAGE WRAPPER ════════════════════════════════════════════════════════ */
.page {
    padding: 20px;
    position: relative; z-index: 1;
    max-width: 1360px; margin: 0 auto;
}

/* ══ HERO STAT CARDS ═════════════════════════════════════════════════════ */
.hero-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px; margin-bottom: 16px;
}
.hero-stat {
    border-radius: 14px; padding: 24px 22px;
    position: relative; overflow: hidden;
    border: 1px solid var(--border);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.hero-stat:hover { border-color: var(--border-hot); box-shadow: 0 0 28px var(--orange-glow); }
.hero-stat.pending {
    background: linear-gradient(135deg, rgba(28,15,0,0.6) 0%, var(--surface) 60%);
    border-color: var(--border-hot); box-shadow: 0 0 22px var(--orange-glow);
}
[data-theme="light"] .hero-stat.pending { background: linear-gradient(135deg, rgba(255,220,180,0.6) 0%, var(--surface) 60%); }
.hero-stat.today { background: var(--surface); }

.hs-label {
    font-size: 0.65rem; font-weight: 700; letter-spacing: 0.13em;
    text-transform: uppercase; color: var(--muted);
    margin-bottom: 10px; display: flex; align-items: center; gap: 7px;
}
.hs-label i { color: var(--orange); }
.hs-value {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(3rem, 8vw, 5rem);
    line-height: 1; letter-spacing: 0.02em; color: var(--orange);
}
.hero-stat.today .hs-value { color: var(--text); }
.hs-footer { margin-top: 12px; display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }
.pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 700; }
.pill-orange { background: var(--orange-glow); color: var(--orange); border: 1px solid var(--border-hot); }
.pill-white  { background: rgba(128,128,128,0.12); color: var(--muted); border: 1px solid var(--border); }
.pill-blue   { background: var(--blue-glow); color: var(--blue); border: 1px solid var(--blue-border); }
.hs-bg-icon { position: absolute; right: 14px; bottom: 10px; font-size: 4rem; opacity: 0.05; pointer-events: none; color: var(--orange); }

/* ══ QUICK ACTIONS (mobile fab row) ══════════════════════════════════════ */
.quick-actions {
    display: none; /* shown on mobile */
    gap: 10px; margin-bottom: 16px;
    overflow-x: auto; padding-bottom: 4px;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.quick-actions::-webkit-scrollbar { display: none; }
.qa-btn {
    display: flex; flex-direction: column; align-items: center; gap: 5px;
    padding: 12px 14px; border-radius: 12px; min-width: 72px;
    background: var(--surface); border: 1px solid var(--border);
    color: var(--text); font-size: 0.68rem; font-weight: 600;
    text-decoration: none; transition: all 0.18s ease; flex-shrink: 0;
    font-family: 'Outfit', sans-serif;
}
.qa-btn i { font-size: 1.2rem; }
.qa-btn.orange { border-color: var(--border-hot); color: var(--orange); background: var(--orange-soft); }
.qa-btn.blue   { border-color: var(--blue-border); color: var(--blue); background: var(--blue-soft); }
.qa-btn.purple { border-color: var(--purple-border); color: var(--purple); background: var(--purple-soft); }
.qa-btn:active { transform: scale(0.95); }

/* ══ VERIFICATION BANNER ═════════════════════════════════════════════════ */
.verify-banner {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 12px; margin-bottom: 16px;
}
.vb-card {
    border-radius: 12px; padding: 18px 16px;
    border: 1px solid var(--border); background: var(--surface);
    display: flex; align-items: center; gap: 14px;
    text-decoration: none; transition: all 0.2s ease;
    position: relative; overflow: hidden;
}
.vb-card:hover { border-color: var(--border-hot); box-shadow: 0 0 24px var(--orange-glow); transform: translateY(-2px); }
.vb-card.blue:hover { border-color: var(--blue-border); box-shadow: 0 0 24px var(--blue-glow); }
.vb-card::after {
    content: ''; position: absolute; right: -30px; top: -30px;
    width: 110px; height: 110px;
    background: radial-gradient(circle, var(--orange-glow) 0%, transparent 70%);
    pointer-events: none;
}
.vb-card.blue::after { background: radial-gradient(circle, var(--blue-glow) 0%, transparent 70%); }
.vb-icon {
    width: 42px; height: 42px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    background: var(--orange-soft); border: 1px solid var(--border-hot); color: var(--orange);
}
.vb-card.blue .vb-icon { background: var(--blue-soft); border-color: var(--blue-border); color: var(--blue); }
.vb-body { flex: 1; min-width: 0; }
.vb-label { font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: 0.07em; color: var(--text); }
.vb-sub   { font-size: 0.65rem; color: var(--muted); margin-top: 2px; line-height: 1.3; }
.vb-stats { text-align: right; flex-shrink: 0; }
.vb-count { font-family: 'Bebas Neue', sans-serif; font-size: 1.8rem; line-height: 1; color: var(--orange); }
.vb-card.blue .vb-count { color: var(--blue); }
.vb-count-label { font-size: 0.58rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted); }
.vb-arrow { color: var(--muted); font-size: 0.8rem; margin-left: 6px; transition: transform 0.2s; }
.vb-card:hover .vb-arrow { transform: translateX(3px); color: var(--orange); }
.vb-card.blue:hover .vb-arrow { color: var(--blue); }

/* ══ INFO ROW ════════════════════════════════════════════════════════════ */
.info-row {
    display: grid; grid-template-columns: 1.7fr 1fr;
    gap: 14px; margin-bottom: 16px;
}

/* ══ CARD ════════════════════════════════════════════════════════════════ */
.card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden;
    transition: border-color 0.2s;
}
.card:hover { border-color: rgba(242,128,24,0.22); }
.card-head {
    padding: 15px 18px 12px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.card-head-title { font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: 0.08em; color: var(--text); }
.card-head-title i { color: var(--orange); margin-right: 7px; }
.card-tag { font-size: 0.62rem; font-weight: 600; letter-spacing: 0.07em; color: var(--muted); background: var(--surface2); border: 1px solid var(--border); padding: 3px 9px; border-radius: 6px; white-space: nowrap; }
.card-body { padding: 16px 18px; }
.chart-wrap { position: relative; width: 100%; height: 190px; }

/* ══ BRAND BARS ══════════════════════════════════════════════════════════ */
.brand-rows { display: flex; flex-direction: column; gap: 12px; }
.b-row { display: flex; align-items: center; gap: 10px; }
.b-name { font-size: 0.76rem; font-weight: 600; color: var(--off-white); width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.b-track { flex: 1; height: 5px; background: var(--border); border-radius: 3px; overflow: hidden; }
.b-fill { height: 100%; background: linear-gradient(90deg, var(--orange), #ffb060); border-radius: 3px; transition: width 0.7s cubic-bezier(.25,.8,.25,1); }
.b-num { font-size: 0.7rem; font-weight: 600; color: var(--muted); min-width: 26px; text-align: right; }

/* ══ BOTTOM ROW ══════════════════════════════════════════════════════════ */
.bottom-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* ══ SUMMARY MINI CARDS ══════════════════════════════════════════════════ */
.mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.mini-card {
    background: var(--surface2); border: 1px solid var(--border); border-radius: 10px;
    padding: 15px 16px; position: relative; overflow: hidden;
    text-decoration: none; display: block;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.mini-card:hover { border-color: var(--border-hot); box-shadow: 0 0 16px var(--orange-glow); }
.mini-card.hot {
    background: linear-gradient(135deg, rgba(28,15,0,0.5), var(--surface2));
    border-color: var(--border-hot);
}
[data-theme="light"] .mini-card.hot { background: linear-gradient(135deg, rgba(255,220,180,0.4), var(--surface2)); }
.mc-label { font-size: 0.61rem; font-weight: 700; letter-spacing: .11em; color: var(--muted); margin-bottom: 7px; text-transform: uppercase; }
.mc-value { font-family: 'Bebas Neue', sans-serif; font-size: 2.1rem; color: var(--text); line-height: 1; }
.mini-card.hot .mc-value { color: var(--orange); }
.mc-sub { font-size: 0.65rem; color: var(--muted); margin-top: 5px; }
.mc-icon { position: absolute; right: 10px; bottom: 7px; font-size: 2.2rem; opacity: 0.05; color: var(--text); }
.mini-card.hot .mc-icon { opacity: 0.09; color: var(--orange); }

/* ══ VIEW ALL LINK ═══════════════════════════════════════════════════════ */
.view-all-link {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    margin-top: 12px; padding: 11px 16px; border-radius: 9px;
    background: var(--surface2); border: 1px solid var(--border);
    color: var(--off-white); font-size: 0.8rem; font-weight: 600;
    text-decoration: none; transition: all 0.18s ease;
}
.view-all-link:hover { background: var(--orange-soft); border-color: var(--border-hot); color: var(--orange); }
.view-all-link i { color: var(--orange); }

/* ══ ACTIVITY LIST ═══════════════════════════════════════════════════════ */
.act-list { display: flex; flex-direction: column; }
.act-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }
.act-item:last-child { border-bottom: none; }
.act-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--orange); flex-shrink: 0; }
.act-info { flex: 1; min-width: 0; }
.act-serial { font-size: 0.78rem; font-weight: 600; color: var(--text); }
.act-desc { font-size: 0.68rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.act-badge { font-size: 0.62rem; font-weight: 700; padding: 2px 7px; border-radius: 5px; background: var(--orange-soft); color: var(--orange); border: 1px solid var(--border-hot); white-space: nowrap; flex-shrink: 0; }
.act-time { font-size: 0.63rem; color: var(--dim); white-space: nowrap; text-align: right; line-height: 1.5; flex-shrink: 0; }

/* ══ EMPTY STATE ═════════════════════════════════════════════════════════ */
.empty { text-align: center; padding: 32px 20px; color: var(--dim); }
.empty i { font-size: 1.8rem; display: block; margin-bottom: 8px; }

/* ══ FOOTER ══════════════════════════════════════════════════════════════ */
.footer-bar {
    margin-top: 28px; padding: 16px 0 4px;
    border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    font-size: 0.7rem; color: var(--dim);
    flex-wrap: wrap; gap: 8px;
}
.status { display: flex; align-items: center; gap: 7px; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 6px #4ade80; }

/* ══ ANIMATIONS ══════════════════════════════════════════════════════════ */
@keyframes fadeUp {
    from { opacity:0; transform: translateY(12px); }
    to   { opacity:1; transform: none; }
}
.hero-cards    { animation: fadeUp 0.35s ease both; }
.quick-actions { animation: fadeUp 0.35s 0.04s ease both; }
.verify-banner { animation: fadeUp 0.35s 0.07s ease both; }
.info-row      { animation: fadeUp 0.35s 0.10s ease both; }
.bottom-row    { animation: fadeUp 0.35s 0.14s ease both; }

/* ══ TABLET (768px – 1024px) ══════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .info-row { grid-template-columns: 1fr; }
    .bottom-row { grid-template-columns: 1fr; }
    .brand-name { font-size: 0.95rem; }
}

/* ══ MOBILE (≤ 768px) ════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    /* Topbar: show hamburger, hide desktop nav */
    .topbar-nav  { display: none; }
    .topbar-date { display: none; }
    .hamburger   { display: flex; }

    /* Brand text optional truncation */
    .brand-name { max-width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Quick actions row visible */
    .quick-actions { display: flex; }

    /* Hero cards: stack on very small screens, side-by-side on medium */
    .hero-cards { grid-template-columns: 1fr 1fr; }

    /* Verify banner: stack */
    .verify-banner { grid-template-columns: 1fr; }

    /* vb-card: simplify layout */
    .vb-card { padding: 14px 14px; gap: 12px; }
    .vb-sub { display: none; }

    /* Chart smaller */
    .chart-wrap { height: 160px; }

    /* Bottom row: already 1 col from tablet query */
}

/* ══ SMALL MOBILE (≤ 480px) ══════════════════════════════════════════════ */
@media (max-width: 480px) {
    .page { padding: 14px; }
    .hero-strip { padding: 20px 14px 16px; }
    .topbar { padding: 0 14px; padding-top: var(--safe-top); }

    /* Stack hero cards vertically on tiny screens */
    .hero-cards { grid-template-columns: 1fr; }

    .hs-value { font-size: 3.8rem; }
    .hs-bg-icon { font-size: 3rem; }

    /* Mini grid stays 2col */
    .mini-grid { grid-template-columns: 1fr 1fr; }

    /* Hide brand name on tiny phones */
    .brand-name { display: none; }
    .brand-divider { display: none; }

    /* Verification pills hidden on tiny */
    .vb-stats { display: none; }
    .vb-arrow { display: none; }
}
</style>
</head>
<body>

<!-- ══ DRAWER OVERLAY ══════════════════════════════════════════════════════ -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<!-- ══ MOBILE DRAWER ═══════════════════════════════════════════════════════ -->
<div class="drawer" id="mobileDrawer">
    <div class="drawer-header">Navigation</div>
    <a href="view_generated_label.php" class="drawer-item">
        <i class="fas fa-history"></i> View Generated
    </a>
    <a href="verification.php" class="drawer-item blue">
        <i class="fas fa-qrcode"></i> Verification
    </a>
    <a href="verification_record.php" class="drawer-item purple">
        <i class="fas fa-clipboard-list"></i> Verification Record
    </a>
    <div class="drawer-sep"></div>
    <?php if ($queue_total > 0): ?>
    <a href="sticker_uk.php" class="drawer-item orange">
        <i class="fas fa-file-pdf"></i> Generate Batch
        <span class="drawer-badge"><?php echo $queue_total; ?></span>
    </a>
    <?php else: ?>
    <a href="sticker_uk.php" class="drawer-item orange">
        <i class="fas fa-plus"></i> New Label
    </a>
    <?php endif; ?>
    <a href="sticker_uk.php" class="drawer-item">
        <i class="fas fa-tags"></i> Labels
    </a>
    <div class="drawer-sep"></div>
    <button class="drawer-item" id="drawerThemeBtn" onclick="toggleThemeFromDrawer()" style="width:100%;text-align:left;border:1px solid var(--border);">
        <i class="fas fa-moon" id="drawerThemeIcon"></i>
        <span id="drawerThemeLabel">Switch to Light</span>
    </button>
</div>

<!-- ══ TOPBAR ══════════════════════════════════════════════════════════════ -->
<header class="topbar">
    <div class="brand">
        <img src="atire.png" alt="ATire Logo" class="brand-logo" onerror="this.style.display='none'">
        <div class="brand-divider"></div>
        <div class="brand-name">Tire Label System</div>
    </div>

    <!-- Desktop nav -->
    <div class="topbar-nav">
        <div class="topbar-date">
            <i class="fas fa-calendar-alt"></i><?php echo date('D, d M Y · H:i'); ?>
        </div>
        <a href="view_generated_label.php" class="btn-t btn-ghost">
            <i class="fas fa-history"></i> Generated
        </a>
        <a href="verification.php" class="btn-t btn-blue">
            <i class="fas fa-qrcode"></i> Verification
        </a>
        <a href="verification_record.php" class="btn-t btn-purple">
            <i class="fas fa-clipboard-list"></i> Record
        </a>
        <?php if ($queue_total > 0): ?>
        <a href="sticker_uk.php" class="btn-t btn-orange">
            <i class="fas fa-file-pdf"></i> Generate
            <span style="background:rgba(255,255,255,0.22);padding:1px 8px;border-radius:12px;font-size:0.68rem;"><?php echo $queue_total; ?></span>
        </a>
        <?php else: ?>
        <a href="sticker_uk.php" class="btn-t btn-ghost"><i class="fas fa-plus"></i> New Label</a>
        <?php endif; ?>
        <a href="sticker_uk.php" class="btn-t btn-ghost"><i class="fas fa-tags"></i> Labels</a>
        <button class="theme-toggle" id="themeToggle">
            <i class="fas fa-moon t-icon" id="themeIcon"></i>
            <span id="themeLabel">Light</span>
        </button>
    </div>

    <!-- Mobile hamburger -->
    <button class="hamburger" id="hamburger" onclick="toggleDrawer()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</header>

<!-- ══ HERO STRIP ══════════════════════════════════════════════════════════ -->
<div class="hero-strip">
    <div class="hero-title"><span>Dashboard</span></div>
    <div class="hero-sub"><?php echo date('l, d F Y · H:i'); ?> &nbsp;·&nbsp; System Online</div>
</div>

<!-- ══ PAGE ════════════════════════════════════════════════════════════════ -->
<div class="page">

    <!-- HERO STAT CARDS -->
    <div class="hero-cards">
        <div class="hero-stat pending">
            <div class="hs-label"><i class="fas fa-layer-group"></i> Pending Queue</div>
            <div class="hs-value"><?php echo number_format($queue_total); ?></div>
            <div class="hs-footer">
                <span class="pill pill-orange"><i class="fas fa-circle" style="font-size:.4rem;"></i> Awaiting print</span>
            </div>
            <i class="fas fa-layer-group hs-bg-icon"></i>
        </div>
        <div class="hero-stat today">
            <div class="hs-label"><i class="fas fa-print"></i> Generated Today</div>
            <div class="hs-value"><?php echo number_format($generated_today); ?></div>
            <div class="hs-footer">
                <span class="pill pill-orange"><i class="fas fa-arrow-up" style="font-size:.55rem;"></i> <?php echo $generated_week; ?> week</span>
                <span class="pill pill-white"><?php echo $generated_month; ?> month</span>
            </div>
            <i class="fas fa-print hs-bg-icon"></i>
        </div>
    </div>

    <!-- QUICK ACTIONS (mobile only) -->
    <div class="quick-actions" id="quickActions">
        <a href="sticker_uk.php" class="qa-btn orange">
            <i class="fas fa-file-pdf"></i>
            Generate<?php if($queue_total>0): ?> (<?php echo $queue_total; ?>)<?php endif; ?>
        </a>
        <a href="verification.php" class="qa-btn blue">
            <i class="fas fa-qrcode"></i>
            Verify
        </a>
        <a href="verification_record.php" class="qa-btn purple">
            <i class="fas fa-clipboard-list"></i>
            Records
        </a>
        <a href="view_generated_label.php" class="qa-btn">
            <i class="fas fa-history"></i>
            History
        </a>
        <a href="sticker_uk.php" class="qa-btn">
            <i class="fas fa-tags"></i>
            Labels
        </a>
    </div>

    <!-- VERIFICATION BANNER -->
    <div class="verify-banner">
        <a href="verification.php?event=GRN" class="vb-card" style="text-decoration:none;">
            <div class="vb-icon"><i class="fas fa-truck-loading"></i></div>
            <div class="vb-body">
                <div class="vb-label">GRN Verification</div>
                <div class="vb-sub">Scan QR to verify goods receipt</div>
                <div style="margin-top:7px;display:flex;gap:6px;flex-wrap:wrap;">
                    <span class="pill pill-orange"><i class="fas fa-circle" style="font-size:.35rem;"></i> <?php echo $grn_today; ?> today</span>
                    <span class="pill pill-white"><?php echo $grn_total; ?> total</span>
                </div>
            </div>
            <div class="vb-stats">
                <div class="vb-count"><?php echo number_format($grn_today); ?></div>
                <div class="vb-count-label">Today</div>
            </div>
            <i class="fas fa-arrow-right vb-arrow"></i>
        </a>
        <a href="verification.php?event=STOCK" class="vb-card blue" style="text-decoration:none;">
            <div class="vb-icon"><i class="fas fa-boxes"></i></div>
            <div class="vb-body">
                <div class="vb-label">Stock Verification</div>
                <div class="vb-sub">Scan QR to confirm on-hand stock</div>
                <div style="margin-top:7px;display:flex;gap:6px;flex-wrap:wrap;">
                    <span class="pill pill-blue"><i class="fas fa-circle" style="font-size:.35rem;"></i> <?php echo $stock_today; ?> today</span>
                    <span class="pill pill-white"><?php echo $stock_total; ?> total</span>
                </div>
            </div>
            <div class="vb-stats">
                <div class="vb-count"><?php echo number_format($stock_today); ?></div>
                <div class="vb-count-label">Today</div>
            </div>
            <i class="fas fa-arrow-right vb-arrow"></i>
        </a>
    </div>

    <!-- CHART + TOP BRANDS -->
    <div class="info-row">
        <div class="card">
            <div class="card-head">
                <div class="card-head-title"><i class="fas fa-chart-bar"></i>Monthly Output</div>
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

    <!-- SUMMARY + ACTIVITY -->
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
                        <div class="mc-sub">All-time</div>
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
                        <div class="mc-sub"><?php echo date('F'); ?></div>
                        <i class="fas fa-calendar-alt mc-icon"></i>
                    </a>
                    <a href="sticker_uk.php" class="mini-card hot" style="text-decoration:none;">
                        <div class="mc-label">In Queue</div>
                        <div class="mc-value"><?php echo number_format($queue_total); ?></div>
                        <div class="mc-sub">Ready to print</div>
                        <i class="fas fa-layer-group mc-icon"></i>
                    </a>
                </div>
                <a href="view_generated_label.php" class="view-all-link">
                    <i class="fas fa-history"></i> View All Generated Labels
                    <i class="fas fa-arrow-right" style="margin-left:4px;font-size:0.72rem;"></i>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div class="card-head-title"><i class="fas fa-bolt"></i>Recent Activity</div>
                <a href="view_generated_label.php" style="font-size:0.7rem;color:var(--orange);text-decoration:none;font-weight:600;">View all <i class="fas fa-arrow-right" style="font-size:0.62rem;"></i></a>
            </div>
            <div class="card-body" style="padding:0 18px;">
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
        <div>Tire Label System &copy; <?php echo date('Y'); ?></div>
    </div>

</div>

<script>
/* ══ DRAWER ══════════════════════════════════════════════════════════════ */
var drawerOpen = false;

function toggleDrawer() {
    drawerOpen ? closeDrawer() : openDrawer();
}
function openDrawer() {
    drawerOpen = true;
    document.getElementById('mobileDrawer').classList.add('open');
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('hamburger').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDrawer() {
    drawerOpen = false;
    document.getElementById('mobileDrawer').classList.remove('open');
    document.getElementById('drawerOverlay').classList.remove('open');
    document.getElementById('hamburger').classList.remove('open');
    document.body.style.overflow = '';
}

/* Close drawer on resize to desktop */
window.addEventListener('resize', function() {
    if (window.innerWidth > 768 && drawerOpen) closeDrawer();
});

/* ══ THEME SYSTEM ════════════════════════════════════════════════════════ */
(function() {
    var html  = document.documentElement;
    var btn   = document.getElementById('themeToggle');
    var icon  = document.getElementById('themeIcon');
    var label = document.getElementById('themeLabel');

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('tlsTheme', theme);

        /* Desktop button */
        if (theme === 'dark') {
            if (icon)  icon.className   = 'fas fa-sun t-icon';
            if (label) label.textContent = 'Light';
        } else {
            if (icon)  icon.className   = 'fas fa-moon t-icon';
            if (label) label.textContent = 'Dark';
        }

        /* Drawer button */
        var di = document.getElementById('drawerThemeIcon');
        var dl = document.getElementById('drawerThemeLabel');
        if (theme === 'dark') {
            if (di) di.className   = 'fas fa-sun';
            if (dl) dl.textContent = 'Switch to Light';
        } else {
            if (di) di.className   = 'fas fa-moon';
            if (dl) dl.textContent = 'Switch to Dark';
        }

        if (window._dashChart) { window._dashChart.destroy(); buildChart(); }
    }

    window.toggleThemeFromDrawer = function() {
        applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    };

    if (btn) {
        btn.addEventListener('click', function() {
            applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });
    }

    var saved = localStorage.getItem('tlsTheme') || 'dark';
    applyTheme(saved);

    /* ── CHART ── */
    function buildChart() {
        var ctx = document.getElementById('monthlyChart');
        if (!ctx) return;
        var isDark     = html.getAttribute('data-theme') === 'dark';
        var chartText  = isDark ? 'rgba(255,255,255,0.38)' : 'rgba(0,0,0,0.45)';
        var chartGrid  = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.05)';
        var chartBorder= isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.09)';

        Chart.defaults.color       = chartText;
        Chart.defaults.font.family = "'Outfit', sans-serif";
        Chart.defaults.font.size   = 11;

        window._dashChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthly_labels); ?>,
                datasets: [{
                    label: 'Labels',
                    data: <?php echo json_encode($monthly_counts); ?>,
                    backgroundColor: function(c) {
                        var ch = c.chart, ca = ch.chartArea;
                        if (!ca) return 'rgba(242,128,24,0.75)';
                        var g = ch.ctx.createLinearGradient(0, ca.top, 0, ca.bottom);
                        g.addColorStop(0, 'rgba(242,128,24,0.95)');
                        g.addColorStop(1, 'rgba(242,128,24,0.15)');
                        return g;
                    },
                    borderColor: '#F28018', borderWidth: 1,
                    borderRadius: 5, borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#1e1e1e' : '#fff',
                        borderColor: '#F28018', borderWidth: 1, padding: 10,
                        titleColor: isDark ? '#fff' : '#1a1a1a',
                        bodyColor: isDark ? 'rgba(255,255,255,0.6)' : 'rgba(0,0,0,0.6)',
                        callbacks: { label: c => '  ' + c.raw + ' labels' }
                    }
                },
                scales: {
                    x: { grid: { color: chartGrid }, border: { color: chartBorder }, ticks: { color: chartText, maxRotation: 0 } },
                    y: { grid: { color: chartGrid }, border: { color: chartBorder }, ticks: { color: chartText }, beginAtZero: true, stepSize: 1 }
                }
            }
        });
    }

    window.buildChart = buildChart;
    buildChart();
})();
</script>
</body>
</html>