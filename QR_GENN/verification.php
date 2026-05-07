<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$dbname     = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$logged_in_user_id = $_SESSION['user'];
$logged_in_name    = $_SESSION['emp_name'] ?? 'Unknown';

// ── AJAX HANDLERS ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'save_scan') {
        $event_type   = trim($_POST['event_type']    ?? '');
        $lot_serial   = trim($_POST['lot_serial_nbr'] ?? '');
        $inventory_id = trim($_POST['inventory_id']   ?? '');
        $description  = trim($_POST['description']    ?? '');
        $user_id      = $_SESSION['user'];

        if (empty($event_type) || empty($lot_serial) || empty($inventory_id)) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
            exit;
        }

        $table = ($event_type === 'GRN') ? 'grn_verification' : 'stock_verification';

        $dup = $conn->prepare("SELECT id FROM $table WHERE lot_serial_nbr=? AND inventory_id=? AND verified_at >= NOW() - INTERVAL 10 SECOND");
        $dup->bind_param("ss", $lot_serial, $inventory_id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Duplicate scan — already recorded in the last 10 seconds.']);
            $dup->close(); exit;
        }
        $dup->close();

        $stmt = $conn->prepare("INSERT INTO $table (lot_serial_nbr, inventory_id, description, verified_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $lot_serial, $inventory_id, $description, $user_id);
        if ($stmt->execute()) {
            $inserted_id = $conn->insert_id;
            $count_today = $conn->query("SELECT COUNT(*) as t FROM $table WHERE DATE(verified_at)=CURDATE()")->fetch_assoc()['t'];
            $verified_at = $conn->query("SELECT verified_at FROM $table WHERE id=$inserted_id")->fetch_assoc()['verified_at'] ?? date('Y-m-d H:i:s');
            echo json_encode(['success'=>true,'count_today'=>$count_today,'inserted_id'=>$inserted_id,'verified_at'=>$verified_at]);
        } else {
            echo json_encode(['success'=>false,'error'=>'DB error: '.$stmt->error]);
        }
        $stmt->close(); exit;
    }

    if ($action === 'delete_scan') {
        $event_type = trim($_POST['event_type'] ?? '');
        $id         = intval($_POST['id'] ?? 0);
        if (!$id || empty($event_type)) { echo json_encode(['success'=>false,'error'=>'Missing params.']); exit; }
        $table = ($event_type === 'GRN') ? 'grn_verification' : 'stock_verification';
        $del = $conn->prepare("DELETE FROM $table WHERE id=?");
        $del->bind_param("i", $id);
        if ($del->execute()) {
            $count_today = $conn->query("SELECT COUNT(*) as t FROM $table WHERE DATE(verified_at)=CURDATE()")->fetch_assoc()['t'];
            echo json_encode(['success'=>true,'count_today'=>$count_today]);
        } else {
            echo json_encode(['success'=>false,'error'=>$del->error]);
        }
        $del->close(); exit;
    }

    echo json_encode(['success'=>false,'error'=>'Unknown action.']); exit;
}

// ── PAGE STATS ────────────────────────────────────────────────────────────────
$grn_today   = $conn->query("SELECT COUNT(*) as t FROM grn_verification   WHERE DATE(verified_at)=CURDATE()")->fetch_assoc()['t'] ?? 0;
$stock_today = $conn->query("SELECT COUNT(*) as t FROM stock_verification WHERE DATE(verified_at)=CURDATE()")->fetch_assoc()['t'] ?? 0;
$grn_total   = $conn->query("SELECT COUNT(*) as t FROM grn_verification")->fetch_assoc()['t'] ?? 0;
$stock_total = $conn->query("SELECT COUNT(*) as t FROM stock_verification")->fetch_assoc()['t'] ?? 0;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="TireVerify">
<meta name="theme-color" content="#0d0d0d" id="themeColorMeta">
<title>Verification — Tire Label System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<style>
/* ══════════════════════════════════════════
   DESIGN TOKENS
══════════════════════════════════════════ */
:root,
[data-theme="dark"] {
    --orange:        #F28018;
    --orange-dark:   #c8660e;
    --orange-glow:   rgba(242,128,24,0.22);
    --orange-soft:   rgba(242,128,24,0.10);
    --bg:            #0d0d0d;
    --surface:       #161616;
    --surface2:      #1e1e1e;
    --surface3:      #2a2a2a;
    --border:        rgba(255,255,255,0.08);
    --border-hot:    rgba(242,128,24,0.45);
    --text:          #ffffff;
    --off-white:     #f0ede8;
    --muted:         rgba(255,255,255,0.42);
    --dim:           rgba(255,255,255,0.16);
    --green:         #4ade80;
    --danger:        #f87171;
    --topbar-bg:     rgba(13,13,13,0.96);
    --hero-bg:       linear-gradient(135deg,#1a0d00 0%,#0d0d0d 60%);
    --noise-op:      0.55;
    --safe-top:      env(safe-area-inset-top, 0px);
    --safe-bottom:   env(safe-area-inset-bottom, 0px);
    --safe-left:     env(safe-area-inset-left, 0px);
    --safe-right:    env(safe-area-inset-right, 0px);
}
[data-theme="light"] {
    --bg:            #f2f1ed;
    --surface:       #ffffff;
    --surface2:      #edebe6;
    --surface3:      #e3e0d9;
    --border:        rgba(0,0,0,0.09);
    --border-hot:    rgba(242,128,24,0.45);
    --text:          #1a1a1a;
    --off-white:     #1a1a1a;
    --muted:         rgba(0,0,0,0.45);
    --dim:           rgba(0,0,0,0.22);
    --topbar-bg:     rgba(242,241,237,0.97);
    --hero-bg:       linear-gradient(135deg,#fff0e0 0%,#f2f1ed 60%);
    --orange-glow:   rgba(242,128,24,0.14);
    --orange-soft:   rgba(242,128,24,0.08);
    --noise-op:      0.12;
}

/* ── RESET ─────────────────────────────── */
*,*::before,*::after{
    margin:0;padding:0;box-sizing:border-box;
    -webkit-tap-highlight-color:transparent;
}
html{height:100%;scroll-behavior:smooth;}
body{
    font-family:'Outfit',sans-serif;
    background:var(--bg);color:var(--text);
    min-height:100%;overflow-x:hidden;
    transition:background .3s,color .3s;
    padding-bottom:calc(72px + var(--safe-bottom));
}
body::before{
    content:'';position:fixed;inset:0;
    background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events:none;z-index:0;opacity:var(--noise-op);transition:opacity .3s;
}

/* ── TOPBAR ─────────────────────────────── */
.topbar{
    position:sticky;top:0;z-index:200;
    background:var(--topbar-bg);
    backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
    border-bottom:1px solid var(--border);
    padding:0 14px;
    padding-top:var(--safe-top);
    height:calc(56px + var(--safe-top));
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    transition:background .3s;
}
.brand{display:flex;align-items:center;gap:10px;flex-shrink:0;}
.brand-logo{
    height:32px;object-fit:contain;
    filter:brightness(1.05) drop-shadow(0 0 5px rgba(242,128,24,.3));
}
.brand-divider{width:1px;height:22px;background:var(--border);}
.brand-name{
    font-family:'Bebas Neue',sans-serif;
    font-size:1.05rem;letter-spacing:.07em;color:var(--off-white);
    white-space:nowrap;
}
.topbar-right{display:flex;align-items:center;gap:8px;}

/* User pill */
.user-pill{
    display:inline-flex;align-items:center;gap:6px;
    padding:0 11px;height:36px;border-radius:20px;
    background:var(--orange-soft);border:1px solid var(--border-hot);
    color:var(--orange);font-size:.72rem;font-weight:600;
    white-space:nowrap;flex-shrink:0;max-width:130px;
    overflow:hidden;text-overflow:ellipsis;
}
.user-pill i{font-size:.78rem;flex-shrink:0;}

/* ── ICON BUTTON (44×44 tap target) ────── */
.icon-btn{
    display:inline-flex;align-items:center;justify-content:center;
    min-width:44px;min-height:44px;border-radius:11px;
    background:var(--surface2);border:1px solid var(--border);
    color:var(--muted);cursor:pointer;font-size:1rem;
    transition:all .18s ease;
    -webkit-user-select:none;user-select:none;
    flex-shrink:0;
}
.icon-btn:active{transform:scale(.91);background:var(--surface3);}
.icon-btn:hover{border-color:var(--border-hot);color:var(--orange);}
.icon-btn.lit{background:var(--orange);color:#fff;border-color:var(--orange);}

/* Theme button */
.theme-pill{
    display:inline-flex;align-items:center;gap:6px;
    padding:0 13px;height:44px;border-radius:11px;
    background:var(--surface2);border:1px solid var(--border);
    color:var(--muted);font-size:.78rem;font-weight:600;
    font-family:'Outfit',sans-serif;cursor:pointer;
    transition:all .2s;-webkit-user-select:none;user-select:none;flex-shrink:0;
}
.theme-pill:active{transform:scale(.95);}
.theme-pill:hover{border-color:var(--border-hot);color:var(--orange);}

/* ── HERO STRIP ─────────────────────────── */
.hero{
    background:var(--hero-bg);
    border-bottom:1px solid var(--border);
    padding:18px 14px 14px;
    position:relative;overflow:hidden;
}
.hero::after{
    content:'';position:absolute;right:-50px;top:-50px;
    width:200px;height:200px;
    background:radial-gradient(circle,rgba(242,128,24,.14) 0%,transparent 70%);
    pointer-events:none;
}
.hero-title{
    font-family:'Bebas Neue',sans-serif;
    font-size:clamp(1.6rem,5vw,2rem);
    letter-spacing:.04em;line-height:1;
}
.hero-title span{color:var(--orange);}
.hero-sub{font-size:.7rem;color:var(--muted);margin-top:4px;}

/* ── NAV LINKS ROW ──────────────────────── */
.back-nav{
    display:flex;gap:8px;padding:10px 14px 0;
    overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;
}
.back-nav::-webkit-scrollbar{display:none;}
.nav-link{
    display:inline-flex;align-items:center;gap:6px;
    padding:9px 14px;border-radius:10px;
    background:var(--surface);border:1px solid var(--border);
    color:var(--muted);font-size:.74rem;font-weight:600;
    font-family:'Outfit',sans-serif;text-decoration:none;
    white-space:nowrap;transition:all .18s;flex-shrink:0;
    -webkit-user-select:none;user-select:none;
}
.nav-link:active{transform:scale(.95);border-color:var(--border-hot);color:var(--orange);}
.nav-link i{font-size:.8rem;}

/* ── EVENT TYPE TABS ────────────────────── */
.event-tabs{
    display:grid;grid-template-columns:1fr 1fr;
    gap:10px;padding:12px 14px 0;
}
.event-tab{
    padding:14px 12px;border-radius:14px;
    border:2px solid var(--border);background:var(--surface);
    cursor:pointer;transition:all .22s ease;
    display:flex;flex-direction:column;gap:8px;
    -webkit-user-select:none;user-select:none;
    min-height:88px;position:relative;overflow:hidden;
}
.event-tab::after{
    content:'';position:absolute;right:-20px;top:-20px;
    width:80px;height:80px;
    background:radial-gradient(circle,var(--orange-glow) 0%,transparent 70%);
    pointer-events:none;opacity:0;transition:opacity .22s;
}
.event-tab:active{transform:scale(.97);}
.event-tab.active{
    border-color:var(--orange);
    background:linear-gradient(135deg,rgba(28,15,0,.7),var(--surface));
    box-shadow:0 0 22px var(--orange-glow);
}
.event-tab.active::after{opacity:1;}
[data-theme="light"] .event-tab.active{background:linear-gradient(135deg,rgba(255,220,180,.5),var(--surface));}

.tab-top{display:flex;align-items:center;justify-content:space-between;}
.tab-icon{
    width:36px;height:36px;border-radius:9px;
    background:var(--orange-soft);border:1px solid var(--border-hot);
    display:flex;align-items:center;justify-content:center;
    font-size:.95rem;color:var(--orange);flex-shrink:0;
}
.tab-count{font-family:'Bebas Neue',sans-serif;font-size:1.7rem;color:var(--orange);line-height:1;}
.tab-label{font-family:'Bebas Neue',sans-serif;font-size:1rem;letter-spacing:.06em;color:var(--text);}
.tab-desc{font-size:.6rem;color:var(--muted);}

/* ── PAGE WRAPPER ───────────────────────── */
.page{padding:12px 14px;position:relative;z-index:1;}

/* ── CARD ───────────────────────────────── */
.card{
    background:var(--surface);border:1px solid var(--border);
    border-radius:16px;overflow:hidden;margin-bottom:12px;
    transition:border-color .2s;
}
.card:hover{border-color:rgba(242,128,24,.2);}
.card-head{
    padding:13px 14px 11px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;gap:8px;
}
.card-head-title{
    font-family:'Bebas Neue',sans-serif;font-size:.98rem;
    letter-spacing:.07em;color:var(--text);
}
.card-head-title i{color:var(--orange);margin-right:6px;}
.card-body{padding:12px 14px;}

/* ── SCANNER VIEWPORT ───────────────────── */
.scanner-wrap{
    width:100%;border-radius:12px;overflow:hidden;
    border:2px solid var(--border);
    background:#000;position:relative;
    min-height:240px;
    display:flex;align-items:center;justify-content:center;
    transition:border-color .3s, box-shadow .3s;
}
.scanner-wrap.active{
    border-color:var(--orange);
    box-shadow:0 0 30px var(--orange-glow);
}

/* Corner brackets */
.sc-br{
    position:absolute;width:32px;height:32px;
    z-index:10;pointer-events:none;display:none;
}
.scanner-wrap.active .sc-br{display:block;}
.sc-br.tl{top:10px;left:10px;border-top:3px solid var(--orange);border-left:3px solid var(--orange);border-radius:4px 0 0 0;}
.sc-br.tr{top:10px;right:10px;border-top:3px solid var(--orange);border-right:3px solid var(--orange);border-radius:0 4px 0 0;}
.sc-br.bl{bottom:10px;left:10px;border-bottom:3px solid var(--orange);border-left:3px solid var(--orange);border-radius:0 0 0 4px;}
.sc-br.br{bottom:10px;right:10px;border-bottom:3px solid var(--orange);border-right:3px solid var(--orange);border-radius:0 0 4px 0;}

/* Scan line */
@keyframes scanLine{0%{top:12%;}100%{top:88%;}}
.scan-line{
    display:none;position:absolute;left:8%;right:8%;height:2px;
    background:linear-gradient(90deg,transparent,var(--orange),transparent);
    border-radius:2px;z-index:11;pointer-events:none;
    animation:scanLine 2s ease-in-out infinite alternate;
    box-shadow:0 0 10px var(--orange);
}
.scanner-wrap.active .scan-line{display:block;}

.scanner-placeholder{
    text-align:center;color:var(--muted);
    padding:44px 20px;
}
.scanner-placeholder i{
    font-size:3.2rem;color:var(--dim);
    display:block;margin-bottom:14px;
}
.scanner-placeholder p{font-size:.82rem;line-height:1.6;}
.scanner-placeholder strong{color:var(--orange);}

/* Suppress html5-qrcode chrome */
#reader__scan_region{padding:0!important;}
#reader__scan_region img{display:none!important;}
#reader__dashboard{display:none!important;}
#reader__header_message{display:none!important;}
#reader__status_span{display:none!important;}
#reader__camera_selection{display:none!important;}
#reader__camera_permission_button{display:none!important;}
#reader{width:100%;}
#reader video{width:100%!important;height:auto!important;max-height:60vw;object-fit:cover;display:block;}
#reader canvas{display:none!important;}

/* ── ZOOM CONTROL ───────────────────────── */
.scanner-controls{
    display:none;gap:10px;align-items:center;
    padding:10px 0 2px;flex-wrap:wrap;
}
.scanner-controls.visible{display:flex;}
.zoom-wrap{display:flex;align-items:center;gap:10px;flex:1;min-width:0;}
.zoom-label{
    font-size:.65rem;font-weight:700;color:var(--muted);
    letter-spacing:.09em;text-transform:uppercase;white-space:nowrap;
}
.zoom-slider{
    -webkit-appearance:none;appearance:none;
    flex:1;height:6px;border-radius:3px;
    background:var(--surface3);outline:none;cursor:pointer;
    padding:10px 0;background-clip:content-box;
}
.zoom-slider::-webkit-slider-thumb{
    -webkit-appearance:none;appearance:none;
    width:26px;height:26px;border-radius:50%;
    background:var(--orange);cursor:pointer;
    box-shadow:0 0 8px var(--orange-glow);
}
.zoom-slider::-moz-range-thumb{
    width:26px;height:26px;border-radius:50%;
    background:var(--orange);cursor:pointer;border:none;
}
.zoom-val{font-size:.78rem;font-weight:700;color:var(--orange);min-width:32px;text-align:right;}

/* Scan hint */
.scan-hint{
    display:none;align-items:center;gap:6px;
    padding:8px 12px;border-radius:9px;
    background:var(--orange-soft);border:1px solid var(--border-hot);
    font-size:.68rem;color:var(--orange);margin-top:8px;line-height:1.5;
}
.scan-hint.visible{display:flex;}

/* ── FIXED BOTTOM ACTION BAR ────────────── */
.bottom-bar{
    position:fixed;bottom:0;left:0;right:0;z-index:300;
    background:var(--topbar-bg);
    backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
    border-top:1px solid var(--border);
    padding:10px 14px;
    padding-bottom:calc(10px + var(--safe-bottom));
    padding-left:calc(14px + var(--safe-left));
    padding-right:calc(14px + var(--safe-right));
    display:flex;gap:10px;align-items:stretch;
}
.btn-start,.btn-stop,.btn-manual-toggle{
    display:inline-flex;align-items:center;justify-content:center;gap:8px;
    border:none;cursor:pointer;border-radius:13px;
    font-family:'Outfit',sans-serif;font-weight:700;font-size:.9rem;
    min-height:52px;transition:all .18s ease;
    -webkit-user-select:none;user-select:none;
    padding:0 20px;text-decoration:none;
}
.btn-start:active,.btn-stop:active,.btn-manual-toggle:active{transform:scale(.95);}
.btn-start{
    flex:1;background:var(--orange);color:#fff;
    box-shadow:0 4px 22px var(--orange-glow);
}
.btn-start:hover{background:var(--orange-dark);}
.btn-stop{
    flex:1;background:#dc2626;color:#fff;
    box-shadow:0 4px 18px rgba(220,38,38,.3);
}
.btn-stop:hover{background:#b91c1c;}
.btn-manual-toggle{
    min-width:52px;width:52px;padding:0;flex-shrink:0;
    background:var(--surface2);color:var(--muted);
    border:1px solid var(--border);font-size:1rem;
}
.btn-manual-toggle:hover{border-color:var(--border-hot);color:var(--orange);}
.btn-manual-toggle.active{
    background:var(--orange-soft);
    border-color:var(--border-hot);color:var(--orange);
}

/* ── RESULT BOX ─────────────────────────── */
.result-box{
    padding:12px 14px;border-radius:12px;
    border:1px solid var(--border);background:var(--surface2);
    display:none;animation:slideIn .25s ease;
}
.result-box.show{display:block;}
.result-box.success{border-color:#16a34a;background:rgba(22,163,74,.08);}
.result-box.error{border-color:#dc2626;background:rgba(220,38,38,.08);}
@keyframes slideIn{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:none;}}

.result-header{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.result-icon{font-size:1.4rem;}
.result-icon.ok{color:#4ade80;}
.result-icon.err{color:#f87171;}
.result-title{font-family:'Bebas Neue',sans-serif;font-size:1rem;letter-spacing:.06em;}

.field-row{display:flex;gap:8px;margin-bottom:7px;align-items:flex-start;}
.field-label{
    font-size:.58rem;font-weight:700;letter-spacing:.12em;
    text-transform:uppercase;color:var(--muted);
    min-width:94px;padding-top:3px;flex-shrink:0;
}
.field-value{font-size:.88rem;font-weight:600;color:var(--text);word-break:break-all;}
.field-value.hi{color:var(--orange);}

/* ── MANUAL ENTRY PANEL ─────────────────── */
.manual-panel{
    display:none;
    padding:14px;
    background:var(--surface2);
    border-top:1px solid var(--border);
    animation:slideIn .25s ease;
}
.manual-panel.open{display:block;}
.form-grid{display:flex;flex-direction:column;gap:11px;}
.form-field{display:flex;flex-direction:column;gap:5px;}
.form-label{
    font-size:.62rem;font-weight:700;letter-spacing:.1em;
    text-transform:uppercase;color:var(--muted);
}
.form-input{
    padding:14px 13px;border-radius:10px;
    background:var(--surface);border:1.5px solid var(--border);
    color:var(--text);font-family:'Outfit',sans-serif;font-size:16px;
    transition:border-color .2s;-webkit-appearance:none;appearance:none;
    min-height:52px;
}
.form-input:focus{outline:none;border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-soft);}
.form-input::placeholder{color:var(--dim);}
.btn-save-manual{
    display:flex;align-items:center;justify-content:center;gap:8px;
    padding:0;height:52px;border-radius:13px;
    background:#16a34a;color:#fff;
    border:none;cursor:pointer;
    font-family:'Outfit',sans-serif;font-weight:700;font-size:.9rem;
    width:100%;transition:all .18s;
    -webkit-user-select:none;user-select:none;
}
.btn-save-manual:active{transform:scale(.97);background:#15803d;}

/* ── SESSION LIST ───────────────────────── */
.session-meta{
    display:flex;align-items:center;justify-content:space-between;
    padding:9px 14px 7px;gap:8px;
    font-size:.66rem;color:var(--muted);
}
.session-badge{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 10px;border-radius:20px;
    background:var(--orange-soft);border:1px solid var(--border-hot);
    color:var(--orange);font-size:.62rem;font-weight:700;flex-shrink:0;
}
.btn-clear-all{
    display:inline-flex;align-items:center;gap:5px;
    padding:6px 12px;border-radius:8px;
    background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.28);
    color:#f87171;font-size:.65rem;font-weight:700;
    font-family:'Outfit',sans-serif;cursor:pointer;
    -webkit-user-select:none;user-select:none;transition:all .18s;
}
.btn-clear-all:active{transform:scale(.94);background:rgba(220,38,38,.22);}

.recent-list{display:flex;flex-direction:column;}

/* Swipe-to-delete item */
.rec-item{
    display:flex;align-items:center;gap:10px;
    padding:11px 14px;border-bottom:1px solid var(--border);
    background:var(--surface);
    transition:transform .25s ease, opacity .25s ease;
    position:relative;overflow:hidden;
    touch-action:pan-y;
    animation:recIn .28s ease both;
}
@keyframes recIn{from{opacity:0;transform:translateX(14px);}to{opacity:1;transform:none;}}
.rec-item:last-child{border-bottom:none;}
.rec-item.removing{opacity:0;transform:translateX(22px);}
.rec-item.swiping{transition:none;}

/* Swipe background reveal */
.rec-swipe-bg{
    position:absolute;right:0;top:0;bottom:0;
    width:80px;
    background:linear-gradient(90deg,transparent,rgba(220,38,38,.85));
    display:flex;align-items:center;justify-content:flex-end;
    padding-right:16px;pointer-events:none;
    opacity:0;transition:opacity .15s;
}
.rec-swipe-bg i{color:#fff;font-size:1rem;}
.rec-item.swipe-reveal .rec-swipe-bg{opacity:1;}

.rec-dot{width:7px;height:7px;border-radius:50%;background:var(--orange);flex-shrink:0;}
.rec-info{flex:1;min-width:0;}
.rec-serial{font-size:.83rem;font-weight:600;color:var(--text);}
.rec-desc{font-size:.66rem;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.rec-icode{
    font-size:.58rem;font-weight:700;padding:2px 7px;border-radius:6px;
    background:var(--orange-soft);color:var(--orange);border:1px solid var(--border-hot);
    white-space:nowrap;flex-shrink:0;
}
.rec-time{font-size:.58rem;color:var(--dim);text-align:right;line-height:1.6;white-space:nowrap;flex-shrink:0;}
.rec-del{
    display:inline-flex;align-items:center;justify-content:center;
    min-width:40px;min-height:40px;border-radius:9px;flex-shrink:0;
    background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.22);
    color:#f87171;cursor:pointer;font-size:.82rem;
    transition:all .16s;-webkit-user-select:none;user-select:none;
}
.rec-del:active{transform:scale(.88);background:rgba(220,38,38,.25);}

.empty-state{text-align:center;padding:36px 20px;color:var(--dim);}
.empty-state i{font-size:2.4rem;display:block;margin-bottom:12px;opacity:.5;}
.empty-state p{font-size:.78rem;line-height:1.7;}

/* ── COUNTERS BAR ───────────────────────── */
.counters-bar{
    display:flex;gap:0;border-top:1px solid var(--border);
}
.counter-item{
    flex:1;padding:13px 10px;text-align:center;
    border-right:1px solid var(--border);
}
.counter-item:last-child{border-right:none;}
.counter-val{font-family:'Bebas Neue',sans-serif;font-size:1.7rem;line-height:1;}
.counter-val.orange{color:var(--orange);}
.counter-val.green{color:#4ade80;}
.counter-val.white{color:var(--text);}
.counter-label{font-size:.52rem;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-top:2px;}

/* ── SUCCESS POPUP (Bottom Sheet) ───────── */
.popup-overlay{
    position:fixed;inset:0;z-index:9999;
    background:rgba(0,0,0,.78);
    backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
    display:flex;align-items:flex-end;justify-content:center;
    opacity:0;pointer-events:none;transition:opacity .25s ease;
    padding-bottom:var(--safe-bottom);
}
.popup-overlay.show{opacity:1;pointer-events:all;}
.popup-sheet{
    background:var(--surface);
    border:1px solid rgba(74,222,128,.3);
    border-radius:22px 22px 0 0;
    padding:24px 20px 20px;
    width:100%;max-width:500px;
    text-align:center;
    box-shadow:0 -8px 60px rgba(74,222,128,.18);
    transform:translateY(100%);
    transition:transform .38s cubic-bezier(.34,1.4,.64,1);
    position:relative;overflow:hidden;
}
.popup-overlay.show .popup-sheet{transform:translateY(0);}
.popup-sheet::before{
    content:'';position:absolute;top:-50px;left:50%;transform:translateX(-50%);
    width:200px;height:200px;
    background:radial-gradient(circle,rgba(74,222,128,.18) 0%,transparent 70%);
    pointer-events:none;
}
.popup-handle{width:36px;height:4px;border-radius:2px;background:var(--border);margin:0 auto 18px;}

.tick-wrap{
    width:68px;height:68px;border-radius:50%;
    background:rgba(74,222,128,.12);border:2px solid rgba(74,222,128,.4);
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 14px;position:relative;
}
.tick-wrap svg{width:34px;height:34px;}
.tick-circle{fill:none;stroke:#4ade80;stroke-width:3;stroke-dasharray:251;stroke-dashoffset:251;stroke-linecap:round;transform-origin:center;transition:stroke-dashoffset .5s cubic-bezier(.4,0,.2,1);}
.tick-check{fill:none;stroke:#4ade80;stroke-width:3.5;stroke-dasharray:60;stroke-dashoffset:60;stroke-linecap:round;stroke-linejoin:round;transition:stroke-dashoffset .35s cubic-bezier(.4,0,.2,1) .4s;}
.popup-overlay.show .tick-circle{stroke-dashoffset:0;}
.popup-overlay.show .tick-check{stroke-dashoffset:0;}
@keyframes pulseRing{0%{transform:scale(1);opacity:.5;}100%{transform:scale(1.55);opacity:0;}}
.tick-pulse{position:absolute;inset:-2px;border-radius:50%;border:2px solid rgba(74,222,128,.45);animation:pulseRing 1.2s ease-out .6s infinite;}

.popup-badge{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 12px;border-radius:20px;
    font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
    background:var(--orange-soft);color:var(--orange);border:1px solid var(--border-hot);
    margin-bottom:8px;
}
.popup-title{font-family:'Bebas Neue',sans-serif;font-size:1.7rem;letter-spacing:.06em;color:#4ade80;line-height:1;margin-bottom:4px;}
.popup-sub{font-size:.72rem;color:var(--muted);margin-bottom:15px;line-height:1.6;}
.popup-fields{
    background:var(--surface2);border:1px solid var(--border);
    border-radius:10px;padding:10px 14px;text-align:left;margin-bottom:14px;
}
.popup-field{display:flex;justify-content:space-between;align-items:center;padding:5px 0;}
.popup-field:not(:last-child){border-bottom:1px solid var(--border);}
.popup-field-label{font-size:.58rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);}
.popup-field-value{font-size:.8rem;font-weight:600;color:var(--orange);max-width:180px;text-align:right;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.popup-progress-wrap{height:3px;background:var(--surface3);border-radius:2px;overflow:hidden;margin-bottom:10px;}
.popup-progress-bar{height:100%;background:#4ade80;border-radius:2px;width:100%;transition:width linear;}
.popup-hint{font-size:.62rem;color:var(--dim);}

/* ── DELETE CONFIRM SHEET ───────────────── */
.del-overlay{
    position:fixed;inset:0;z-index:9998;
    background:rgba(0,0,0,.7);backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px);
    display:flex;align-items:flex-end;justify-content:center;
    opacity:0;pointer-events:none;transition:opacity .22s;
    padding-bottom:var(--safe-bottom);
}
.del-overlay.show{opacity:1;pointer-events:all;}
.del-sheet{
    background:var(--surface);border:1px solid rgba(220,38,38,.3);
    border-radius:22px 22px 0 0;padding:22px 20px 18px;
    width:100%;max-width:500px;text-align:center;
    box-shadow:0 -4px 40px rgba(220,38,38,.14);
    transform:translateY(100%);
    transition:transform .32s cubic-bezier(.34,1.4,.64,1);
}
.del-overlay.show .del-sheet{transform:translateY(0);}
.del-handle{width:36px;height:4px;border-radius:2px;background:var(--border);margin:0 auto 16px;}
.del-icon{font-size:1.8rem;color:#f87171;margin-bottom:10px;}
.del-title{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:#f87171;margin-bottom:7px;}
.del-msg{font-size:.78rem;color:var(--muted);margin-bottom:18px;line-height:1.7;}
.del-serial{font-weight:700;color:var(--text);}
.del-actions{display:flex;gap:10px;}
.btn-cancel,.btn-confirm-del{
    flex:1;display:flex;align-items:center;justify-content:center;gap:7px;
    height:52px;border-radius:13px;border:none;cursor:pointer;
    font-family:'Outfit',sans-serif;font-weight:700;font-size:.88rem;
    transition:all .18s;-webkit-user-select:none;user-select:none;
}
.btn-cancel:active,.btn-confirm-del:active{transform:scale(.96);}
.btn-cancel{background:var(--surface2);color:var(--muted);border:1px solid var(--border);}
.btn-confirm-del{background:#dc2626;color:#fff;box-shadow:0 4px 16px rgba(220,38,38,.3);}

/* ── TOAST NOTIFICATION ─────────────────── */
.toast{
    position:fixed;top:calc(58px + var(--safe-top));left:50%;
    transform:translateX(-50%) translateY(-14px);
    z-index:9997;
    padding:11px 18px;border-radius:12px;
    min-width:240px;max-width:90vw;
    font-size:.83rem;font-weight:600;text-align:center;
    border:1px solid var(--border);
    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
    pointer-events:none;opacity:0;
    transition:opacity .25s,transform .25s;
    white-space:nowrap;
}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
.toast.success{background:rgba(22,163,74,.92);color:#fff;border-color:#16a34a;}
.toast.error{background:rgba(220,38,38,.92);color:#fff;border-color:#dc2626;}
.toast.warn{background:rgba(242,128,24,.9);color:#fff;border-color:var(--orange);}

/* ── ACTIVE MODE PILL ───────────────────── */
.mode-pill{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 10px;border-radius:20px;
    background:var(--orange-glow);color:var(--orange);
    border:1px solid var(--border-hot);
    font-size:.62rem;font-weight:700;letter-spacing:.06em;
}

/* ── FOOTER ─────────────────────────────── */
.footer{
    padding:14px 14px 8px;border-top:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
    font-size:.62rem;color:var(--dim);flex-wrap:wrap;gap:6px;
}
.status-dot-row{display:flex;align-items:center;gap:6px;}
.status-dot{width:6px;height:6px;border-radius:50%;background:#4ade80;box-shadow:0 0 5px #4ade80;}

/* ── ANIMATIONS ─────────────────────────── */
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:none;}}
.event-tabs  {animation:fadeUp .3s ease both;}
.page        {animation:fadeUp .3s .06s ease both;}
.bottom-bar  {animation:fadeUp .3s .08s ease both;}

/* ══ RESPONSIVE ══════════════════════════ */
@media (min-width:601px) {
    .topbar{padding:0 24px;padding-top:var(--safe-top);}
    .hero{padding:22px 24px 18px;}
    .back-nav{padding:12px 24px 0;}
    .event-tabs{padding:14px 24px 0;gap:14px;}
    .page{padding:14px 24px;}
    .event-tab{min-height:96px;}
    #reader video{max-height:50vw;}
    .bottom-bar{padding:12px 24px;padding-bottom:calc(12px + var(--safe-bottom));}
}
@media (min-width:900px) {
    .topbar{padding:0 36px;padding-top:var(--safe-top);}
    .hero{padding:28px 36px 22px;}
    .back-nav{padding:14px 36px 0;}
    .event-tabs{padding:16px 36px 0;max-width:900px;margin:0 auto;}
    .page{padding:16px 36px;max-width:900px;margin:0 auto;}
    .scanner-wrap{min-height:320px;}
    #reader video{max-height:380px;}
    .bottom-bar{padding:12px 36px;padding-bottom:calc(12px + var(--safe-bottom));}
    .popup-sheet,.del-sheet{border-radius:22px 22px 0 0;}
}
@media (max-width:380px) {
    .brand-name{display:none;}
    .brand-divider{display:none;}
    .theme-pill span{display:none;}
    .theme-pill{padding:0;width:44px;justify-content:center;}
    .tab-desc{display:none;}
    .scanner-placeholder{padding:30px 14px;}
    .scanner-placeholder i{font-size:2.5rem;}
    .user-pill span{display:none;}
    .user-pill{padding:0;width:36px;justify-content:center;border-radius:50%;}
}
</style>
</head>
<body>

<!-- ══ TOAST ══════════════════════════════ -->
<div class="toast" id="toast"></div>

<!-- ══ SUCCESS POPUP ══════════════════════ -->
<div class="popup-overlay" id="popupOverlay" onclick="closePopupOverlay(event)">
    <div class="popup-sheet">
        <div class="popup-handle"></div>
        <div class="tick-wrap">
            <div class="tick-pulse"></div>
            <svg viewBox="0 0 80 80" fill="none">
                <circle class="tick-circle" cx="40" cy="40" r="36" transform="rotate(-90 40 40)"/>
                <polyline class="tick-check" points="24,41 35,52 56,30"/>
            </svg>
        </div>
        <div class="popup-badge"><i class="fas fa-check"></i> <span id="popEvName">GRN</span> Verified</div>
        <div class="popup-title">SCAN SAVED!</div>
        <div class="popup-sub" id="popSub">Successfully recorded to GRN Verification</div>
        <div class="popup-fields" id="popFields"></div>
        <div class="popup-progress-wrap"><div class="popup-progress-bar" id="popBar"></div></div>
        <div class="popup-hint">Auto-closing in <span id="popCd">3</span>s &middot; tap anywhere to dismiss</div>
    </div>
</div>

<!-- ══ DELETE CONFIRM ══════════════════════ -->
<div class="del-overlay" id="delOverlay" onclick="cancelDelOverlay(event)">
    <div class="del-sheet">
        <div class="del-handle"></div>
        <div class="del-icon"><i class="fas fa-trash-alt"></i></div>
        <div class="del-title">Delete Scan?</div>
        <div class="del-msg">This will permanently remove<br><span class="del-serial" id="delSerial"></span><br>from the database.</div>
        <div class="del-actions">
            <button class="btn-cancel" onclick="cancelDelete()"><i class="fas fa-times"></i> Cancel</button>
            <button class="btn-confirm-del" onclick="confirmDelete()"><i class="fas fa-trash-alt"></i> Delete</button>
        </div>
    </div>
</div>

<!-- ══ TOPBAR ══════════════════════════════ -->
<header class="topbar">
    <div class="brand">
        <img src="atire.png" alt="ATire" class="brand-logo" onerror="this.style.display='none'">
        <div class="brand-divider"></div>
        <div class="brand-name">TireVerify</div>
    </div>
    <div class="topbar-right">
        <!-- Logged-in user pill -->
        <div class="user-pill" title="Logged in as <?php echo htmlspecialchars($logged_in_name); ?>">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($logged_in_name); ?></span>
        </div>

        <button class="icon-btn" id="torchBtn" onclick="toggleTorch()" title="Torch" style="display:none;">
            <i class="fas fa-bolt"></i>
        </button>
        <a href="qr_system_dash.php" class="icon-btn" title="Dashboard">
            <i class="fas fa-home"></i>
        </a>
        <button class="theme-pill" id="themeToggle">
            <i class="fas fa-moon" id="themeIcon"></i>
            <span id="themeLabel">Light</span>
        </button>
    </div>
</header>

<!-- ══ HERO ════════════════════════════════ -->
<div class="hero">
    <div class="hero-title"><span>Verification</span> Console</div>
    <div class="hero-sub">Scan tire QR codes &middot; GRN &amp; Stock &middot; UK Series</div>
</div>

<!-- ══ NAV LINKS ═══════════════════════════ -->
<div class="back-nav">
    <a href="qr_system_dash.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
    <a href="view_generated_label.php" class="nav-link"><i class="fas fa-history"></i> Generated</a>
    <a href="sticker_uk.php" class="nav-link"><i class="fas fa-tags"></i> Labels</a>
    <a href="verification_record.php" class="nav-link"><i class="fas fa-clipboard-list"></i> Records</a>
</div>

<!-- ══ EVENT TYPE TABS ══════════════════════ -->
<div class="event-tabs">
    <div class="event-tab active" id="tab-GRN" onclick="setEventType('GRN')">
        <div class="tab-top">
            <div class="tab-icon"><i class="fas fa-truck-loading"></i></div>
            <div class="tab-count" id="grn-today"><?php echo $grn_today; ?></div>
        </div>
        <div class="tab-label">GRN</div>
        <div class="tab-desc">Goods Receipt Note</div>
    </div>
    <div class="event-tab" id="tab-STOCK" onclick="setEventType('STOCK')">
        <div class="tab-top">
            <div class="tab-icon"><i class="fas fa-boxes"></i></div>
            <div class="tab-count" id="stock-today"><?php echo $stock_today; ?></div>
        </div>
        <div class="tab-label">STOCK</div>
        <div class="tab-desc">Physical stock count</div>
    </div>
</div>

<!-- ══ PAGE CONTENT ═════════════════════════ -->
<div class="page">

    <!-- SCANNER CARD -->
    <div class="card">
        <div class="card-head">
            <div class="card-head-title"><i class="fas fa-qrcode"></i>QR Scanner</div>
            <span class="mode-pill" id="modePill">
                <i class="fas fa-circle" style="font-size:.35rem;"></i>
                <span id="activeModeLabel">GRN</span>
            </span>
        </div>
        <div class="card-body" style="padding-bottom:8px;">
            <div class="scanner-wrap" id="scannerWrap">
                <div class="sc-br tl"></div>
                <div class="sc-br tr"></div>
                <div class="sc-br bl"></div>
                <div class="sc-br br"></div>
                <div class="scan-line"></div>
                <div class="scanner-placeholder" id="scannerPlaceholder">
                    <i class="fas fa-camera"></i>
                    <p>Tap <strong>Start Scanner</strong><br>below to activate camera</p>
                </div>
                <div id="reader"></div>
            </div>

            <!-- Zoom control -->
            <div class="scanner-controls" id="scannerControls">
                <div class="zoom-wrap">
                    <span class="zoom-label"><i class="fas fa-search-plus"></i> Zoom</span>
                    <input type="range" class="zoom-slider" id="zoomSlider"
                        min="1" max="8" step="0.1" value="2.5"
                        oninput="applyZoom(this.value)">
                    <span class="zoom-val" id="zoomVal">2.5×</span>
                </div>
            </div>

            <div class="scan-hint" id="scanHint">
                <i class="fas fa-info-circle"></i>
                <strong>Optimal distance: 5–8 cm</strong> for 3cm×3cm QR codes · Preset zoom: 2.5× for best focus
            </div>
        </div>
    </div>

    <!-- LAST SCAN RESULT CARD -->
    <div class="card">
        <div class="card-head">
            <div class="card-head-title"><i class="fas fa-clipboard-check"></i>Last Scan Result</div>
        </div>
        <div class="card-body">
            <div class="result-box" id="resultBox">
                <div class="result-header">
                    <i class="result-icon" id="resultIcon"></i>
                    <span class="result-title" id="resultTitle"></span>
                </div>
                <div id="resultFields"></div>
            </div>
            <div id="resultEmpty" style="color:var(--muted);font-size:.8rem;text-align:center;padding:18px 0;">
                <i class="fas fa-qrcode" style="font-size:2rem;color:var(--dim);display:block;margin-bottom:8px;"></i>
                No scan yet — start scanner or enter manually
            </div>
        </div>

        <!-- Manual entry panel -->
        <div class="manual-panel" id="manualPanel">
            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label">Lot / Serial Number *</label>
                    <input class="form-input" id="manualLot" type="text"
                        placeholder="e.g. SN-2024-0001" autocomplete="off"
                        inputmode="text" enterkeyhint="next">
                </div>
                <div class="form-field">
                    <label class="form-label">Inventory ID (Tire Code) *</label>
                    <input class="form-input" id="manualInv" type="text"
                        placeholder="e.g. TY-205/55R16" autocomplete="off"
                        inputmode="text" enterkeyhint="next">
                </div>
                <div class="form-field">
                    <label class="form-label">Description (optional)</label>
                    <input class="form-input" id="manualDesc" type="text"
                        placeholder="e.g. Bridgestone Turanza" autocomplete="off"
                        inputmode="text" enterkeyhint="done"
                        onkeydown="if(event.key==='Enter')submitManual()">
                </div>
                <button class="btn-save-manual" onclick="submitManual()">
                    <i class="fas fa-save"></i> Save Entry
                </button>
            </div>
        </div>
    </div>

    <!-- SESSION SCANS CARD -->
    <div class="card">
        <div class="card-head">
            <div class="card-head-title"><i class="fas fa-list-check"></i>Session Scans</div>
            <span class="session-badge" id="sessionBadge">
                <i class="fas fa-video"></i> Live
            </span>
        </div>

        <div class="session-meta">
            <span id="sessionMeta" style="color:var(--dim);font-size:.63rem;">Start scanner to begin</span>
            <button class="btn-clear-all" id="clearAllBtn" style="display:none;" onclick="clearAllSession()">
                <i class="fas fa-trash"></i> Clear All
            </button>
        </div>

        <div class="recent-list" id="recentList">
            <div class="empty-state">
                <i class="fas fa-camera-slash"></i>
                <p>Session scans appear here<br>after you start scanning</p>
            </div>
        </div>

        <!-- Counters -->
        <div class="counters-bar">
            <div class="counter-item">
                <div class="counter-val orange" id="allTimeCount"><?php echo $grn_total; ?></div>
                <div class="counter-label">All-time</div>
            </div>
            <div class="counter-item">
                <div class="counter-val white" id="todayCount"><?php echo $grn_today; ?></div>
                <div class="counter-label">Today</div>
            </div>
            <div class="counter-item">
                <div class="counter-val green" id="sessionCount">0</div>
                <div class="counter-label">Session</div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="status-dot-row">
            <div class="status-dot"></div>
            System online &middot; <strong style="color:var(--orange);"><?php echo htmlspecialchars($logged_in_name); ?></strong>
        </div>
        <div><?php echo date('d M Y'); ?> &copy; Tire Label Mgmt</div>
    </div>

</div><!-- /page -->

<!-- ══ FIXED BOTTOM ACTION BAR ══════════════ -->
<div class="bottom-bar" id="bottomBar">
    <button class="btn-start" id="startBtn" onclick="startScanner()">
        <i class="fas fa-camera"></i> Start Scanner
    </button>
    <button class="btn-stop" id="stopBtn" onclick="stopScanner()" style="display:none;">
        <i class="fas fa-stop-circle"></i> Stop
    </button>
    <button class="btn-manual-toggle" id="manualToggleBtn" onclick="toggleManual()" title="Manual entry">
        <i class="fas fa-keyboard"></i>
    </button>
</div>

<script>
/* ══════════════════════════════════════════
   STATE
══════════════════════════════════════════ */
var currentEventType = '<?php echo isset($_GET['event']) && $_GET['event']==='STOCK' ? 'STOCK' : 'GRN'; ?>';
var scanner          = null;
var scannerRunning   = false;
var lastScanned      = null;
var torchOn          = false;
var torchSupported   = false;
var activeTrack      = null;
var zoomSupported    = false;
var manualOpen       = false;
var sessionScans     = [];
var pendingDelId        = null;
var pendingDelEventType = null;
var wakeLock         = null;

var allTimeTotals = { GRN: <?php echo $grn_total; ?>, STOCK: <?php echo $stock_total; ?> };
var todayTotals   = { GRN: <?php echo $grn_today; ?>, STOCK: <?php echo $stock_today; ?> };

/* ══ THEME ══════════════════════════════ */
(function(){
    var html=document.documentElement;
    var btn=document.getElementById('themeToggle');
    var icon=document.getElementById('themeIcon');
    var label=document.getElementById('themeLabel');

    function applyTheme(t){
        html.setAttribute('data-theme',t);
        localStorage.setItem('tlsTheme',t);
        icon.className = t==='dark' ? 'fas fa-sun' : 'fas fa-moon';
        label.textContent = t==='dark' ? 'Light' : 'Dark';
        document.getElementById('themeColorMeta').content = t==='dark' ? '#0d0d0d' : '#f2f1ed';
    }
    applyTheme(localStorage.getItem('tlsTheme') || 'dark');
    btn.addEventListener('click', function(){
        applyTheme(html.getAttribute('data-theme')==='dark' ? 'light' : 'dark');
    });
})();

/* ══ INIT EVENT TYPE FROM URL ══════════ */
(function(){
    if(currentEventType === 'STOCK') setEventType('STOCK');
})();

/* ══ EVENT TYPE ════════════════════════ */
function setEventType(type){
    currentEventType = type;
    ['GRN','STOCK'].forEach(function(t){
        document.getElementById('tab-'+t).classList.toggle('active', t===type);
    });
    document.getElementById('activeModeLabel').textContent = type;
    document.getElementById('allTimeCount').textContent = allTimeTotals[type];
    document.getElementById('todayCount').textContent = todayTotals[type];
    clearResult();
    renderSessionList();
    if(scannerRunning) stopScanner();
}

/* ══ MANUAL PANEL ══════════════════════ */
function toggleManual(){
    manualOpen = !manualOpen;
    document.getElementById('manualPanel').classList.toggle('open', manualOpen);
    document.getElementById('manualToggleBtn').classList.toggle('active', manualOpen);
    if(manualOpen) setTimeout(function(){ document.getElementById('manualLot').focus(); }, 350);
}

/* ══ SESSION LIST ══════════════════════ */
function renderSessionList(){
    var list   = document.getElementById('recentList');
    var meta   = document.getElementById('sessionMeta');
    var clrBtn = document.getElementById('clearAllBtn');
    var filtered = sessionScans.filter(function(s){ return s.event_type === currentEventType; });

    document.getElementById('sessionCount').textContent = filtered.length;

    if(!filtered.length){
        list.innerHTML = '<div class="empty-state"><i class="fas fa-'+(scannerRunning?'qrcode':'camera-slash')+'"></i><p>'+(scannerRunning?'Waiting for scans…':'Start the scanner to begin scanning')+'</p></div>';
        meta.textContent = 'Start scanner to begin';
        clrBtn.style.display = 'none';
        return;
    }
    meta.textContent = filtered.length + ' scan' + (filtered.length!==1?'s':'') + ' this session';
    clrBtn.style.display = '';
    list.innerHTML = filtered.map(function(r){
        var dt = new Date(r.verified_at.replace(' ','T'));
        var ds = dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short'});
        var ts = dt.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
        return '<div class="rec-item" id="ri-'+r.id+'" data-id="'+r.id+'" data-ev="'+escH(r.event_type)+'" data-serial="'+escH(r.lot_serial_nbr)+'">'
            +'<div class="rec-swipe-bg"><i class="fas fa-trash-alt"></i></div>'
            +'<div class="rec-dot"></div>'
            +'<div class="rec-info">'
              +'<div class="rec-serial">'+escH(r.lot_serial_nbr)+'</div>'
              +'<div class="rec-desc">'+escH(r.description||'—')+'</div>'
            +'</div>'
            +'<span class="rec-icode">'+escH(r.inventory_id)+'</span>'
            +'<div class="rec-time">'+ds+'<br>'+ts+'</div>'
            +'<button class="rec-del" onclick="requestDelete('+r.id+',\''+escH(r.event_type)+'\',\''+escH(r.lot_serial_nbr)+'\')">'
              +'<i class="fas fa-trash-alt"></i>'
            +'</button>'
            +'</div>';
    }).join('');
    list.querySelectorAll('.rec-item').forEach(function(el){ attachSwipe(el); });
}

function addToSession(scan){
    sessionScans.unshift(scan);
    renderSessionList();
}

function clearAllSession(){
    if(!confirm('Remove all session scans from list?\n(DB records will NOT be deleted)')) return;
    sessionScans = sessionScans.filter(function(s){ return s.event_type !== currentEventType; });
    renderSessionList();
}

/* ══ SWIPE TO DELETE ═══════════════════ */
function attachSwipe(el){
    var startX=0, curX=0, swiping=false, threshold=80;
    el.addEventListener('touchstart', function(e){
        startX = e.touches[0].clientX;
        swiping = true; el.classList.add('swiping');
    }, {passive:true});
    el.addEventListener('touchmove', function(e){
        if(!swiping) return;
        curX = e.touches[0].clientX - startX;
        if(curX < 0){
            el.style.transform = 'translateX('+Math.max(curX,-90)+'px)';
            el.classList.toggle('swipe-reveal', curX < -50);
        }
    }, {passive:true});
    el.addEventListener('touchend', function(){
        if(!swiping) return;
        swiping = false; el.classList.remove('swiping');
        if(curX < -threshold){
            requestDelete(parseInt(el.dataset.id), el.dataset.ev, el.dataset.serial);
        }
        el.style.transform = ''; el.classList.remove('swipe-reveal'); curX = 0;
    });
}

/* ══ DELETE FLOW ═══════════════════════ */
function requestDelete(id, eventType, serial){
    pendingDelId = id; pendingDelEventType = eventType;
    document.getElementById('delSerial').textContent = serial;
    document.getElementById('delOverlay').classList.add('show');
}
function cancelDelOverlay(e){
    if(e.target === document.getElementById('delOverlay')) cancelDelete();
}
function cancelDelete(){
    document.getElementById('delOverlay').classList.remove('show');
    pendingDelId = null; pendingDelEventType = null;
}
function confirmDelete(){
    if(!pendingDelId) return;
    document.getElementById('delOverlay').classList.remove('show');
    var id = pendingDelId, ev = pendingDelEventType;
    pendingDelId = null; pendingDelEventType = null;

    var el = document.getElementById('ri-'+id);
    if(el) el.classList.add('removing');

    var fd = new FormData();
    fd.append('action','delete_scan');
    fd.append('event_type',ev);
    fd.append('id',id);

    fetch('', {method:'POST',body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res){
            if(res.success){
                sessionScans = sessionScans.filter(function(s){ return s.id !== id; });
                todayTotals[ev] = res.count_today;
                document.getElementById('todayCount').textContent = res.count_today;
                document.getElementById(ev==='GRN'?'grn-today':'stock-today').textContent = res.count_today;
                if(allTimeTotals[ev]>0) allTimeTotals[ev]--;
                document.getElementById('allTimeCount').textContent = allTimeTotals[ev];
                setTimeout(function(){ renderSessionList(); }, 300);
                showToast('Scan deleted.','success');
            } else {
                if(el) el.classList.remove('removing');
                showToast(res.error || 'Delete failed.','error');
            }
        })
        .catch(function(){
            if(el) el.classList.remove('removing');
            showToast('Network error.','error');
        });
}

/* ══ SUCCESS POPUP ══════════════════════ */
var popTimer = null, popCdTimer = null;
function showSuccessPopup(data){
    var overlay = document.getElementById('popupOverlay');
    var bar     = document.getElementById('popBar');
    document.getElementById('popEvName').textContent  = currentEventType;
    document.getElementById('popSub').textContent      = 'Saved to '+currentEventType+' Verification';
    document.getElementById('popFields').innerHTML     =
        pf('Lot / Serial', data.lot_serial_nbr) +
        pf('Inventory ID', data.inventory_id) +
        (data.description ? pf('Description', data.description) : '');
    bar.style.transition = 'none'; bar.style.width = '100%';
    overlay.classList.add('show');
    var secs = 3; document.getElementById('popCd').textContent = secs;
    clearInterval(popCdTimer);
    popCdTimer = setInterval(function(){
        secs--;
        document.getElementById('popCd').textContent = secs;
        if(secs <= 0) clearInterval(popCdTimer);
    }, 1000);
    requestAnimationFrame(function(){ requestAnimationFrame(function(){
        bar.style.transition = 'width 3s linear'; bar.style.width = '0%';
    });});
    clearTimeout(popTimer);
    popTimer = setTimeout(closePopup, 3000);
}
function pf(l,v){ return '<div class="popup-field"><span class="popup-field-label">'+escH(l)+'</span><span class="popup-field-value">'+escH(v)+'</span></div>'; }
function closePopup(){ clearTimeout(popTimer); clearInterval(popCdTimer); document.getElementById('popupOverlay').classList.remove('show'); }
function closePopupOverlay(e){ if(e.target === document.getElementById('popupOverlay')) closePopup(); }

/* ══ SCANNER ════════════════════════════ */
function startScanner(){
    if(scannerRunning) return;
    document.getElementById('scannerPlaceholder').style.display = 'none';
    document.getElementById('scannerWrap').classList.add('active');
    document.getElementById('startBtn').style.display  = 'none';
    document.getElementById('stopBtn').style.display   = '';

    scanner = new Html5Qrcode("reader");
    var cfg = {
        fps: 30,
        aspectRatio: 1.0,
        disableFlip: false,
        videoConstraints: {
            facingMode: {ideal: "environment"},
            width: {ideal: 1920, min: 1280},
            height: {ideal: 1920, min: 1280},
            advanced: [
                {focusMode: "continuous"},
                {focusDistance: 0.1},
                {zoom: 2.0}
            ]
        }
    };

    scanner.start(
        {facingMode: {ideal: "environment"}}, cfg,
        function(text){ onQrScanned(text); },
        function(){}
    ).then(function(){
        scannerRunning = true;
        initCaps();
        document.getElementById('scannerControls').classList.add('visible');
        document.getElementById('scanHint').classList.add('visible');
        renderSessionList();
        if(navigator.wakeLock){
            navigator.wakeLock.request('screen').then(function(wl){ wakeLock = wl; }).catch(function(){});
        }
    }).catch(function(err){
        showToast('Camera error: ' + (err.message || err), 'error');
        resetScannerUI();
    });
}

function initCaps(){
    try{
        var caps = scanner.getRunningTrackCameraCapabilities();
        var zc = caps.zoomFeature();
        if(zc.isSupported()){
            zoomSupported = true;
            var sl = document.getElementById('zoomSlider');
            sl.min = zc.min()||1; sl.max = zc.max()||8; sl.step = zc.step()||0.1; sl.value = 2.5;
            document.getElementById('zoomVal').textContent = '2.5×';
        } else {
            document.getElementById('zoomSlider').parentElement.style.display = 'none';
        }
        var tc = caps.torchFeature();
        if(tc.isSupported()){ torchSupported = true; document.getElementById('torchBtn').style.display = ''; }
    } catch(e){
        try{
            var v = document.querySelector('#reader video');
            if(v && v.srcObject){
                var tracks = v.srcObject.getVideoTracks();
                if(tracks.length){
                    activeTrack = tracks[0];
                    var sc = activeTrack.getCapabilities ? activeTrack.getCapabilities() : {};
                    if(sc.zoom){
                        zoomSupported = true;
                        var sl = document.getElementById('zoomSlider');
                        sl.min = sc.zoom.min||1; sl.max = sc.zoom.max||8;
                        sl.step = sc.zoom.step||0.1; sl.value = 2.5;
                        document.getElementById('zoomVal').textContent = '2.5×';
                    } else { document.getElementById('zoomSlider').parentElement.style.display = 'none'; }
                    if(sc.torch){ torchSupported = true; document.getElementById('torchBtn').style.display = ''; }
                }
            }
        } catch(e2){}
    }
}

function applyZoom(v){
    v = parseFloat(v);
    document.getElementById('zoomVal').textContent = v.toFixed(1) + '×';
    try{ var z = scanner.getRunningTrackCameraCapabilities().zoomFeature(); if(z.isSupported()){ z.apply(v); return; } } catch(e){}
    if(activeTrack){ try{ activeTrack.applyConstraints({advanced:[{zoom:v}]}); } catch(e){} return; }
    try{ var vid = document.querySelector('#reader video'); if(vid && vid.srcObject){ var t = vid.srcObject.getVideoTracks(); if(t.length){ activeTrack=t[0]; activeTrack.applyConstraints({advanced:[{zoom:v}]}); } } } catch(e){}
}

function toggleTorch(){
    if(!scannerRunning) return;
    torchOn = !torchOn;
    document.getElementById('torchBtn').classList.toggle('lit', torchOn);
    function applyT(track){ try{ track.applyConstraints({advanced:[{torch:torchOn}]}); } catch(e){} }
    try{ var tc = scanner.getRunningTrackCameraCapabilities().torchFeature(); if(tc.isSupported()){ tc.apply(torchOn); return; } } catch(e){}
    if(activeTrack){ applyT(activeTrack); return; }
    try{ var v = document.querySelector('#reader video'); if(v && v.srcObject){ var t = v.srcObject.getVideoTracks(); if(t.length){ activeTrack=t[0]; applyT(activeTrack); } } } catch(e){}
}

function stopScanner(){
    if(!scannerRunning || !scanner) return;
    scanner.stop().then(function(){
        scanner.clear(); scanner = null; scannerRunning = false; activeTrack = null;
        torchOn = false; zoomSupported = false; torchSupported = false;
        if(wakeLock){ try{ wakeLock.release(); } catch(e){} wakeLock = null; }
        resetScannerUI();
        document.getElementById('zoomSlider').value = 2.5;
        document.getElementById('zoomVal').textContent = '2.5×';
        document.getElementById('zoomSlider').parentElement.style.display = '';
        document.getElementById('torchBtn').style.display = 'none';
        document.getElementById('torchBtn').classList.remove('lit');
        renderSessionList();
    }).catch(function(){ scanner = null; scannerRunning = false; resetScannerUI(); });
}

function resetScannerUI(){
    document.getElementById('scannerPlaceholder').style.display = '';
    document.getElementById('scannerWrap').classList.remove('active');
    document.getElementById('startBtn').style.display = '';
    document.getElementById('stopBtn').style.display  = 'none';
    document.getElementById('scannerControls').classList.remove('visible');
    document.getElementById('scanHint').classList.remove('visible');
}

/* ══ QR DECODE ══════════════════════════ */
function onQrScanned(raw){
    if(raw === lastScanned) return;
    lastScanned = raw;
    setTimeout(function(){ lastScanned = null; }, 3000);

    try{
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var o = ctx.createOscillator(); var g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.type = 'sine'; o.frequency.value = 880;
        g.gain.setValueAtTime(.2, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + .2);
        o.start(ctx.currentTime); o.stop(ctx.currentTime + .2);
    } catch(e){}
    if(navigator.vibrate) navigator.vibrate([55,30,55]);

    var data = null;
    try{ data = JSON.parse(raw); } catch(e){
        showResult(false, 'Invalid QR', 'QR does not contain valid JSON.'); return;
    }
    var lot  = (data.LotSerialNbr || '').trim();
    var inv  = (data.InventoryID  || '').trim();
    var desc = (data.TB || '').trim();
    if(!lot || !inv){ showResult(false, 'Missing QR Data', 'QR missing LotSerialNbr or InventoryID.'); return; }
    autoSaveScan({lot_serial_nbr:lot, inventory_id:inv, description:desc});
}

/* ══ AUTO SAVE ══════════════════════════ */
function autoSaveScan(data){
    var fd = new FormData();
    fd.append('action','save_scan');
    fd.append('event_type', currentEventType);
    fd.append('lot_serial_nbr', data.lot_serial_nbr);
    fd.append('inventory_id',   data.inventory_id);
    fd.append('description',    data.description || '');

    fetch('', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res){
            if(res.success){
                todayTotals[currentEventType] = res.count_today;
                document.getElementById('todayCount').textContent = res.count_today;
                document.getElementById(currentEventType==='GRN'?'grn-today':'stock-today').textContent = res.count_today;
                allTimeTotals[currentEventType]++;
                document.getElementById('allTimeCount').textContent = allTimeTotals[currentEventType];
                addToSession({
                    id: res.inserted_id,
                    lot_serial_nbr: data.lot_serial_nbr,
                    inventory_id:   data.inventory_id,
                    description:    data.description || '',
                    verified_at:    res.verified_at,
                    event_type:     currentEventType
                });
                showResult(true, 'Saved — '+currentEventType+' Verified', null, data);
                showSuccessPopup(data);
            } else {
                showResult(false, 'Not Saved', res.error || 'Save failed.');
                showToast(res.error || 'Save failed.', 'error');
            }
        })
        .catch(function(){ showToast('Network error.', 'error'); });
}

/* ══ RESULT BOX ═════════════════════════ */
function showResult(ok, title, errMsg, data){
    var box = document.getElementById('resultBox');
    var icon = document.getElementById('resultIcon');
    document.getElementById('resultEmpty').style.display = 'none';
    box.className = 'result-box show ' + (ok ? 'success' : 'error');
    icon.className = 'result-icon ' + (ok ? 'ok fas fa-check-circle' : 'err fas fa-times-circle');
    document.getElementById('resultTitle').textContent = title;
    if(ok && data){
        document.getElementById('resultFields').innerHTML =
            fr('Lot / Serial', data.lot_serial_nbr, true) +
            fr('Inventory ID', data.inventory_id, true) +
            (data.description ? fr('Description', data.description, false) : '');
    } else {
        document.getElementById('resultFields').innerHTML =
            '<div style="color:var(--danger);font-size:.82rem;margin-top:4px;">'+escH(errMsg||'')+'</div>';
    }
    box.scrollIntoView({behavior:'smooth', block:'nearest'});
}
function fr(l,v,hi){ return '<div class="field-row"><span class="field-label">'+escH(l)+'</span><span class="field-value'+(hi?' hi':'')+'">'+escH(v)+'</span></div>'; }
function clearResult(){
    document.getElementById('resultBox').className = 'result-box';
    document.getElementById('resultEmpty').style.display = '';
    document.getElementById('resultFields').innerHTML = '';
}

/* ══ MANUAL SUBMIT ══════════════════════ */
function submitManual(){
    var lot  = document.getElementById('manualLot').value.trim();
    var inv  = document.getElementById('manualInv').value.trim();
    var desc = document.getElementById('manualDesc').value.trim();
    if(!lot || !inv){
        showToast('Lot/Serial and Inventory ID are required.', 'warn'); return;
    }
    autoSaveScan({lot_serial_nbr:lot, inventory_id:inv, description:desc});
    document.getElementById('manualLot').value  = '';
    document.getElementById('manualInv').value  = '';
    document.getElementById('manualDesc').value = '';
}

/* ══ TOAST ══════════════════════════════ */
var toastTimer = null;
function showToast(msg, type){
    var el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast show ' + (type||'success');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function(){ el.classList.remove('show'); }, 3200);
}

/* ══ UTILS ══════════════════════════════ */
function escH(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

/* ══ WAKE LOCK RE-ACQUIRE ═══════════════ */
document.addEventListener('visibilitychange', function(){
    if(document.visibilityState==='visible' && scannerRunning && navigator.wakeLock && !wakeLock){
        navigator.wakeLock.request('screen').then(function(wl){ wakeLock=wl; }).catch(function(){});
    }
});

/* ══ INIT ═══════════════════════════════ */
renderSessionList();
</script>
</body>
</html>