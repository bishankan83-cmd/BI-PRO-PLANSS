<?php
// dashboard.php
session_start();
include('include/config.php');

if (!isset($_SESSION["aid"])) {
    header("Location: index.php");
    exit();
}

$adminId = intval($_SESSION["aid"]);

$stmt = mysqli_prepare($con, "SELECT * FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$adminResult = mysqli_stmt_get_result($stmt);
$adminData   = mysqli_fetch_assoc($adminResult);

if (!$adminData) {
    session_destroy();
    header("Location: index.php");
    exit();
}
mysqli_stmt_close($stmt);

$isAccountManager = false;
$acmRef = '';
$customerIds = [];
$userIds = [];

$stmt = mysqli_prepare($con, "SELECT acm_ref FROM account_managers WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $isAccountManager = true;
    $acmRef = $row['acm_ref'];
    $stmt2  = mysqli_prepare($con, "SELECT id, cus_id FROM users WHERE acm_ref = ?");
    mysqli_stmt_bind_param($stmt2, "s", $acmRef);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    while ($customer = mysqli_fetch_assoc($result2)) {
        $customerIds[] = $customer['cus_id'];
        $userIds[]     = intval($customer['id']);
    }
    mysqli_stmt_close($stmt2);
}
mysqli_stmt_close($stmt);

$totusers = $totcom = $pendingcom = $inprocesscom = $closedcom = 0;
$totAccountManagers = $totOrders = $pendingOrders = $confirmedOrders = 0;
$customerConfirmedOrders = $cusPiConfirmOrders = $requestReviseOrders = 0;

$userIdsStr = '';
if ($isAccountManager && !empty($userIds)) {
    $userIdsStr = implode(",", $userIds);
}

function qCount($con, $sql) {
    $r = mysqli_query($con, $sql);
    return (int)mysqli_fetch_assoc($r)['count'];
}
function pCount($con, $sql) {
    $s = mysqli_prepare($con, $sql);
    mysqli_stmt_execute($s);
    $r = mysqli_stmt_get_result($s);
    $c = (int)mysqli_fetch_assoc($r)['count'];
    mysqli_stmt_close($s);
    return $c;
}

if ($isAccountManager && !empty($userIds)) {
    $totusers              = qCount($con, "SELECT COUNT(*) as count FROM users WHERE id IN ($userIdsStr)");
    $totcom                = qCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE userId IN ($userIdsStr)");
    $pendingcom            = qCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status IS NULL AND userId IN ($userIdsStr)");
    $inprocesscom          = qCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='in process' AND userId IN ($userIdsStr)");
    $closedcom             = qCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='closed' AND userId IN ($userIdsStr)");
    $totOrders             = qCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE customer_id IN ($userIdsStr)");
    $pendingOrders         = qCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='pending' AND customer_id IN ($userIdsStr)");
    $confirmedOrders       = qCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='confirmed' AND customer_id IN ($userIdsStr)");
    $customerConfirmedOrders = qCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='cus_confirmed' AND customer_id IN ($userIdsStr)");
    $cusPiConfirmOrders    = qCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='cus_pi_confirm' AND customer_id IN ($userIdsStr)");
    $requestReviseOrders   = qCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE request_status='request_revise' AND customer_id IN ($userIdsStr)");
} else {
    $totusers              = pCount($con, "SELECT COUNT(*) as count FROM users");
    $totcom                = pCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints");
    $pendingcom            = pCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status IS NULL");
    $inprocesscom          = pCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='in process'");
    $closedcom             = pCount($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='closed'");
    $totOrders             = pCount($con, "SELECT COUNT(*) as count FROM tire_orders");
    $pendingOrders         = pCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='pending'");
    $confirmedOrders       = pCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='confirmed'");
    $customerConfirmedOrders = pCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='cus_confirmed'");
    $cusPiConfirmOrders    = pCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE status='cus_pi_confirm'");
    $requestReviseOrders   = pCount($con, "SELECT COUNT(*) as count FROM tire_orders WHERE request_status='request_revise'");
    $totAccountManagers    = pCount($con, "SELECT COUNT(*) as count FROM account_managers WHERE status = 'active'");
}

if ($isAccountManager && !empty($userIds)) {
    $recentComplaintsQuery = "SELECT tcmp.*, u.fullName as userName FROM tbl_tire_complaints tcmp LEFT JOIN users u ON u.id = tcmp.userId WHERE tcmp.userId IN ($userIdsStr) ORDER BY tcmp.created_at DESC LIMIT 5";
    $recentOrdersQuery     = "SELECT tord.*, u.fullName as userName FROM tire_orders tord LEFT JOIN users u ON u.id = tord.customer_id WHERE tord.customer_id IN ($userIdsStr) ORDER BY tord.order_date DESC LIMIT 5";
} else {
    $recentComplaintsQuery = "SELECT tcmp.*, u.fullName as userName FROM tbl_tire_complaints tcmp LEFT JOIN users u ON u.id = tcmp.userId ORDER BY tcmp.created_at DESC LIMIT 5";
    $recentOrdersQuery     = "SELECT tord.*, u.fullName as userName FROM tire_orders tord LEFT JOIN users u ON u.id = tord.customer_id ORDER BY tord.order_date DESC LIMIT 5";
}
$recentComplaints = mysqli_query($con, $recentComplaintsQuery);
$recentOrders     = mysqli_query($con, $recentOrdersQuery);

$notificationCount = (int)($customerConfirmedOrders > 0) + (int)($cusPiConfirmOrders > 0) + (int)($requestReviseOrders > 0);

// Admin initials
$adminName     = $adminData['fullName'] ?? $adminData['name'] ?? 'Admin';
$adminEmail    = $adminData['email'] ?? '';
$adminInitials = strtoupper(substr($adminName, 0, 1));
if (strpos($adminName, ' ') !== false)
    $adminInitials .= strtoupper(substr($adminName, strpos($adminName, ' ') + 1, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ATIRE Customer Service</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ===== SF UI Display — exact customer dashboard font stack ===== */
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-ultralight-58646b19bf205.otf') format('opentype'); font-weight:100; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-thin-58646e9b26e8b.otf')      format('opentype'); font-weight:200; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-medium-58646be638f96.otf')    format('opentype'); font-weight:400; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-semibold-58646eddcae92.otf')  format('opentype'); font-weight:600; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-bold-58646a511e3d9.otf')      format('opentype'); font-weight:700; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-heavy-586470160b9e5.otf')     format('opentype'); font-weight:800; }
        @font-face { font-family:'SF UI Display'; src:url('font/sf-ui-display-black-58646a6b80d5a.otf')     format('opentype'); font-weight:900; }

        /* ===== CSS Variables — 1:1 copy from customer dashboard ===== */
        :root {
            --primary-orange: #F28018;
            --dark-gray:      #333333;
            --light-gray:     #f0f0f0;
            --border-gray:    #D6D6D6;
            --bg-light:       #f9f9f9;
            --success:        #27ae60;
            --warning:        #f39c12;
            --error:          #e74c3c;
            --info:           #3498db;
            --text-gray:      #555555;
            --orange-light:   rgba(242,128,24,0.1);
            --success-light:  rgba(39,174,96,0.1);
            --warning-light:  rgba(241,196,15,0.1);
            --error-light:    rgba(231,76,60,0.1);
            --info-light:     rgba(52,152,219,0.1);
            --white:          #ffffff;
            --shadow-soft:    0 4px 20px rgba(0,0,0,0.08);
            --shadow-hover:   0 8px 30px rgba(0,0,0,0.12);
            --shadow-active:  0 12px 40px rgba(242,128,24,0.3);
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; }

        body {
            font-family:'SF UI Display',-apple-system,BlinkMacSystemFont,sans-serif;
            background-color:var(--bg-light);
            color:var(--dark-gray);
            line-height:1.6;
            overflow-x:hidden;
            font-weight:400;
        }

        /* ===== HEADER — exact customer dashboard header ===== */
        .header {
            position:sticky; top:0; z-index:1000;
            background:rgba(255,255,255,0.95);
            backdrop-filter:blur(20px);
            border-bottom:1px solid var(--border-gray);
            box-shadow:var(--shadow-soft);
        }
        .navbar {
            max-width:1600px; margin:0 auto;
            padding:1.2rem 5%;
            display:flex; justify-content:space-between; align-items:center; gap:1rem;
        }
        .brand { display:flex; align-items:center; gap:0.8rem; text-decoration:none; }
        .brand-logo { max-width:160px; height:auto; }
        .brand-tagline {
            font-size:0.78rem; color:var(--primary-orange);
            font-weight:700; text-transform:uppercase; letter-spacing:0.12em;
        }
        .user-section { display:flex; align-items:center; gap:1.2rem; }
        .user-info { text-align:right; }
        .user-info h4 { font-size:0.97rem; font-weight:700; color:var(--dark-gray); line-height:1.2; }
        .user-info p  { font-size:0.78rem; color:var(--text-gray); font-weight:400; }
        .user-avatar-link {
            position:relative; display:flex; align-items:center;
            justify-content:center; text-decoration:none; border-radius:50%;
        }
        .user-avatar {
            width:42px; height:42px;
            background:var(--primary-orange); border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            color:var(--white); font-weight:800; font-size:0.95rem;
            cursor:pointer; transition:transform 0.25s ease, box-shadow 0.25s ease;
            letter-spacing:0.5px;
        }
        .user-avatar-link:hover .user-avatar {
            transform:scale(1.1);
            box-shadow:0 0 0 3px var(--primary-orange),var(--shadow-active);
        }
        .admin-badge {
            display:inline-flex; align-items:center; gap:5px;
            background:var(--orange-light); color:var(--primary-orange);
            border:1.5px solid rgba(242,128,24,0.25);
            padding:0.28rem 0.9rem; border-radius:50px;
            font-size:0.72rem; font-weight:800; letter-spacing:0.08em; text-transform:uppercase;
        }

        /* ===== NOTIFICATION BANNERS — same pulse/style as customer alert-banner ===== */
        .alert-banner {
            background:var(--primary-orange);
            padding:1.2rem 5%;
            display:flex; align-items:center; gap:1.4rem;
            cursor:pointer; transition:transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow:var(--shadow-active); text-decoration:none;
            position:fixed; top:80px; left:0; right:0; z-index:999;
            animation:slideDown 0.4s ease-out;
        }
        .alert-banner:hover { transform:translateY(-2px); box-shadow:0 16px 50px rgba(242,128,24,0.4); }
        .alert-banner.alert-error   { background:var(--error);   box-shadow:0 10px 35px rgba(231,76,60,0.35); }
        .alert-banner.alert-success { background:var(--success); box-shadow:0 10px 35px rgba(39,174,96,0.35); }
        .alert-banner.alert-warning { background:var(--warning); box-shadow:0 10px 35px rgba(243,156,18,0.35); }
        .alert-banner.second { top:152px; }
        .alert-banner.third  { top:224px; }

        @keyframes slideDown { from { transform:translateY(-100%); opacity:0; } to { transform:translateY(0); opacity:1; } }

        .alert-icon {
            width:50px; height:50px;
            background:rgba(255,255,255,0.22); border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem; color:white;
            animation:pulse 2.2s ease-in-out infinite; flex-shrink:0;
        }
        @keyframes pulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.12); } }

        .alert-content { flex:1; color:white; }
        .alert-title   { font-size:1rem; font-weight:800; margin-bottom:2px; letter-spacing:-0.1px; }
        .alert-msg     { font-size:0.88rem; opacity:0.94; font-weight:400; }

        .alert-count {
            background:white; border-radius:50px;
            padding:0.3rem 0.85rem; font-size:1rem; font-weight:900;
            animation:bounce 1s ease-in-out infinite;
        }
        .alert-error   .alert-count { color:var(--error);   }
        .alert-success .alert-count { color:var(--success); }
        .alert-warning .alert-count { color:var(--warning); }
        @keyframes bounce { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-5px); } }

        .alert-action {
            background:rgba(255,255,255,0.22); color:white;
            border:1.5px solid rgba(255,255,255,0.4);
            padding:0.5rem 1.1rem; border-radius:50px;
            font-size:0.85rem; font-weight:700;
            font-family:'SF UI Display',sans-serif;
            text-decoration:none; transition:all 0.3s; white-space:nowrap;
            display:inline-flex; align-items:center; gap:6px;
        }
        .alert-action:hover { background:rgba(255,255,255,0.38); transform:translateY(-2px); }
        .alert-close {
            width:32px; height:32px;
            background:rgba(255,255,255,0.2); border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition:all 0.3s; color:white; flex-shrink:0;
        }
        .alert-close:hover { background:rgba(255,255,255,0.35); transform:rotate(90deg); }

        /* body offset classes */
        body.notify-1 .main-scroll { margin-top:72px; }
        body.notify-2 .main-scroll { margin-top:144px; }
        body.notify-3 .main-scroll { margin-top:216px; }

        /* ===== LAYOUT ===== */
        .page-wrapper { display:flex; min-height:calc(100vh - 80px); }
        .main-scroll  { flex:1; overflow-x:hidden; transition:margin-top 0.3s ease; }
        .container    { max-width:1600px; margin:0 auto; padding:2.5rem 5% 4rem; }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:2rem; flex-wrap:wrap; gap:1rem;
        }
        .page-header-left h1 {
            font-size:clamp(1.8rem,3vw,2.4rem); font-weight:800;
            color:var(--dark-gray); letter-spacing:-0.02em; line-height:1.2;
        }
        .page-header-left h1 span { color:var(--primary-orange); }
        .page-header-left p  { font-size:1rem; color:var(--text-gray); margin-top:4px; }
        .acm-tag {
            display:inline-flex; align-items:center; gap:6px;
            background:var(--orange-light); color:var(--primary-orange);
            border:1.5px solid rgba(242,128,24,0.25);
            padding:0.4rem 1.1rem; border-radius:50px;
            font-size:0.82rem; font-weight:700; margin-top:8px;
        }
        .page-header-badge {
            display:inline-flex; align-items:center; gap:8px;
            background:var(--orange-light); border:1.5px solid rgba(242,128,24,0.2);
            color:var(--primary-orange); font-weight:700; font-size:0.9rem;
            padding:0.6rem 1.5rem; border-radius:50px;
        }

        /* ===== SECTION TITLE — 1:1 match customer dashboard ===== */
        .section-title {
            font-size:1.4rem; font-weight:800;
            margin:2.5rem 0 1.2rem;
            display:flex; align-items:center; gap:10px;
            color:var(--dark-gray); letter-spacing:-0.02em;
        }
        .title-icon {
            width:38px; height:38px;
            background:var(--orange-light); border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            color:var(--primary-orange); font-size:1rem; flex-shrink:0;
        }
        .title-line {
            flex:1; height:1.5px;
            background:linear-gradient(90deg,var(--border-gray) 0%,transparent 100%);
            margin-left:8px;
        }

        /* ===== STATS GRID — exact customer dashboard ===== */
        .stats-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
            gap:1.2rem; margin-bottom:1rem;
        }

        .stat-card {
            background:var(--white); border:1px solid var(--border-gray);
            border-radius:20px; padding:1.8rem;
            transition:all 0.3s ease; position:relative; overflow:hidden; cursor:pointer;
        }
        .stat-card::after {
            content:''; position:absolute;
            bottom:0; left:0; right:0; height:4px;
            background:var(--primary-orange);
            transform:scaleX(0); transform-origin:left; transition:transform 0.3s ease;
        }
        .stat-card:hover::after  { transform:scaleX(1); }
        .stat-card:hover {
            border-color:var(--primary-orange);
            transform:translateY(-6px); box-shadow:var(--shadow-hover);
        }
        /* colour variants */
        .stat-card.success::after { background:var(--success); }
        .stat-card.warning::after { background:var(--warning); }
        .stat-card.danger::after  { background:var(--error);   }
        .stat-card.info::after    { background:var(--primary-orange); } /* keep orange */
        .stat-card.success:hover  { border-color:var(--success); }
        .stat-card.warning:hover  { border-color:var(--warning); }
        .stat-card.danger:hover   { border-color:var(--error);   }

        .stat-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.2rem; }

        .stat-icon {
            width:56px; height:56px; border-radius:14px;
            background:var(--orange-light); color:var(--primary-orange);
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem; transition:all 0.3s ease;
        }
        .stat-card.success .stat-icon { background:var(--success-light); color:var(--success); }
        .stat-card.warning .stat-icon { background:var(--warning-light); color:var(--warning); }
        .stat-card.danger  .stat-icon { background:var(--error-light);   color:var(--error);   }
        .stat-card:hover .stat-icon   { background:var(--primary-orange); color:white; transform:scale(1.08) rotate(-5deg); }
        .stat-card.success:hover .stat-icon { background:var(--success); }
        .stat-card.warning:hover .stat-icon { background:var(--warning); }
        .stat-card.danger:hover  .stat-icon { background:var(--error);   }

        .stat-trend {
            padding:5px 14px; border-radius:50px;
            font-size:0.78rem; font-weight:700;
            display:flex; align-items:center; gap:4px;
        }
        .trend-up      { background:var(--success-light); color:var(--success); }
        .trend-down    { background:var(--error-light);   color:var(--error);   }
        .trend-neutral { background:var(--orange-light);  color:var(--primary-orange); }
        .trend-warn    { background:var(--warning-light); color:var(--warning); }

        .stat-value {
            font-size:2.8rem; font-weight:900; color:var(--primary-orange);
            line-height:1; margin-bottom:4px; letter-spacing:-1px;
        }
        .stat-card.success .stat-value { color:var(--success); }
        .stat-card.warning .stat-value { color:var(--warning); }
        .stat-card.danger  .stat-value { color:var(--error);   }
        .stat-label       { font-size:0.97rem; font-weight:700; color:var(--dark-gray); margin-bottom:2px; }
        .stat-description { font-size:0.84rem; color:var(--text-gray); font-weight:400; }
        .stat-link {
            display:inline-flex; align-items:center; gap:4px;
            margin-top:0.6rem; color:var(--primary-orange);
            font-size:0.82rem; font-weight:700; text-decoration:none; transition:gap 0.2s;
        }
        .stat-card.success .stat-link { color:var(--success); }
        .stat-card.warning .stat-link { color:var(--warning); }
        .stat-card.danger  .stat-link { color:var(--error);   }
        .stat-link:hover { gap:8px; }

        /* ===== QUICK ACTIONS GRID — same as customer dashboard ===== */
        .quick-actions {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
            gap:1.2rem; margin-bottom:1rem;
        }
        .quick-action-card {
            background:var(--white); border:1px solid var(--border-gray);
            border-radius:20px; padding:1.8rem 1.2rem 1.5rem;
            text-align:center; cursor:pointer; transition:all 0.3s ease;
            text-decoration:none; color:inherit; position:relative; overflow:hidden;
        }
        .quick-action-card::after {
            content:''; position:absolute;
            bottom:0; left:0; right:0; height:4px;
            background:var(--primary-orange);
            transform:scaleX(0); transition:transform 0.3s ease;
        }
        .quick-action-card:hover::after { transform:scaleX(1); }
        .quick-action-card:hover {
            border-color:var(--primary-orange);
            transform:translateY(-8px); box-shadow:var(--shadow-hover);
        }
        .quick-action-icon {
            width:64px; height:64px; margin:0 auto 1rem;
            background:var(--orange-light); border-radius:18px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.6rem; color:var(--primary-orange); transition:all 0.3s ease;
        }
        .quick-action-card:hover .quick-action-icon {
            background:var(--primary-orange); color:white;
            transform:scale(1.1) rotate(5deg);
        }
        .quick-action-title    { font-size:0.97rem; font-weight:700; color:var(--dark-gray); margin-bottom:4px; }
        .quick-action-subtitle { font-size:0.82rem; color:var(--text-gray); font-weight:400; }

        /* ===== CARDS ===== */
        .card {
            background:var(--white); border:1px solid var(--border-gray);
            border-radius:20px; overflow:hidden;
            transition:all 0.3s ease; box-shadow:var(--shadow-soft);
            margin-bottom:1.5rem;
        }
        .card:hover { border-color:rgba(242,128,24,0.35); box-shadow:var(--shadow-hover); }
        .card-header {
            padding:1.4rem 2rem;
            background:var(--bg-light); border-bottom:1px solid var(--border-gray);
            display:flex; justify-content:space-between; align-items:center;
        }
        .card-title {
            font-size:1.1rem; font-weight:700;
            display:flex; align-items:center; gap:10px; color:var(--dark-gray);
        }
        .card-title i { color:var(--primary-orange); }
        .card-body { padding:1.8rem 2rem; }

        /* ===== CONTENT GRID ===== */
        .content-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem; }

        /* ===== BUTTONS ===== */
        .btn {
            padding:0.8rem 2rem; border:none; border-radius:50px;
            font-size:0.97rem; font-weight:700;
            font-family:'SF UI Display',-apple-system,sans-serif;
            text-decoration:none; transition:all 0.3s ease; cursor:pointer;
            display:inline-flex; align-items:center; gap:0.7rem;
            position:relative; overflow:hidden;
        }
        .btn::before {
            content:''; position:absolute; top:0; left:-100%;
            width:100%; height:100%;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent);
            transition:left 0.5s;
        }
        .btn:hover::before { left:100%; }
        .btn-primary { background:var(--primary-orange); color:white; box-shadow:var(--shadow-soft); }
        .btn-primary:hover { background:#d4700f; transform:translateY(-2px); box-shadow:var(--shadow-active); }
        .btn-outline { background:transparent; color:var(--primary-orange); border:2px solid var(--primary-orange); }
        .btn-outline:hover { background:var(--primary-orange); color:white; transform:translateY(-2px); box-shadow:var(--shadow-hover); }
        .btn-sm { padding:0.5rem 1.2rem; font-size:0.82rem; }

        /* ===== LIST ITEMS ===== */
        .list-container { display:flex; flex-direction:column; gap:0.9rem; }
        .list-item {
            background:var(--bg-light); border:1px solid var(--border-gray);
            border-radius:14px; padding:1.2rem 1.5rem;
            display:flex; align-items:center; gap:1.2rem;
            transition:all 0.3s ease; text-decoration:none; color:inherit;
        }
        .list-item:hover {
            background:var(--white); border-color:var(--primary-orange);
            transform:translateX(5px); box-shadow:var(--shadow-soft);
        }
        .item-avatar {
            width:50px; height:50px; background:var(--primary-orange);
            border-radius:13px; display:flex; align-items:center; justify-content:center;
            font-size:1.2rem; color:white; flex-shrink:0;
        }
        .item-avatar.warn    { background:var(--warning); }
        .item-avatar.danger  { background:var(--error);   }
        .item-avatar.success { background:var(--success); }
        .item-details { flex:1; min-width:0; }
        .item-title {
            font-weight:700; margin-bottom:3px; color:var(--dark-gray);
            font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        }
        .item-meta { font-size:0.83rem; color:var(--text-gray); line-height:1.5; font-weight:400; }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            padding:0.35rem 1rem; border-radius:50px;
            font-size:0.78rem; font-weight:700; text-transform:capitalize; white-space:nowrap;
        }
        .status-pending       { background:var(--warning-light); color:var(--warning); border:1px solid rgba(243,156,18,0.25); }
        .status-in-process    { background:var(--orange-light);  color:var(--primary-orange); border:1px solid rgba(242,128,24,0.25); }
        .status-closed        { background:var(--success-light); color:var(--success); border:1px solid rgba(39,174,96,0.3); }
        .status-confirmed     { background:var(--orange-light);  color:var(--primary-orange); border:1px solid rgba(242,128,24,0.25); }
        .status-cus-confirmed { background:var(--success-light); color:var(--success); border:1px solid rgba(39,174,96,0.3); }
        .status-pi-confirmed  { background:var(--orange-light);  color:var(--primary-orange); border:1px solid rgba(242,128,24,0.25); }

        /* ===== EMPTY STATE ===== */
        .empty-state { text-align:center; padding:3.5rem 2rem; }
        .empty-icon  { font-size:4rem; color:var(--border-gray); margin-bottom:1.2rem; display:block; }
        .empty-state h3 { font-size:1.2rem; font-weight:700; color:var(--dark-gray); margin-bottom:6px; }
        .empty-state p  { color:var(--text-gray); font-size:0.92rem; }

        /* ===== SCROLL REVEAL ===== */
        .reveal { opacity:0; transform:translateY(24px); transition:opacity 0.6s ease,transform 0.6s ease; }
        .reveal.visible { opacity:1; transform:translateY(0); }
        @keyframes slideUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { animation:slideUp 0.7s ease-out both; }

        /* ===== RESPONSIVE ===== */
        @media (max-width:1200px) { .content-grid { grid-template-columns:1fr; } }
        @media (max-width:1024px) { .container { padding:2rem 4%; } .navbar { padding:1rem 4%; } }
        @media (max-width:768px) {
            .user-info { display:none; }
            .stats-grid { grid-template-columns:repeat(2,1fr); }
            .quick-actions { grid-template-columns:repeat(3,1fr); }
            .alert-banner { padding:0.9rem 4%; }
            .card-body { padding:1.2rem; } .card-header { padding:1.1rem 1.2rem; }
        }
        @media (max-width:480px) {
            .stats-grid { grid-template-columns:1fr; }
            .quick-actions { grid-template-columns:repeat(2,1fr); }
            .list-item { flex-wrap:wrap; }
        }
    </style>
</head>
<body class="<?php
    if ($notificationCount == 1) echo 'notify-1';
    elseif ($notificationCount == 2) echo 'notify-2';
    elseif ($notificationCount == 3) echo 'notify-3';
?>">

<!-- ===== Header ===== -->
<header class="header">
    <nav class="navbar">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE Logo" class="brand-logo">
            <div>
                <div class="brand-tagline">Customer Service</div>
            </div>
        </a>
        <div class="user-section">
            <div class="user-info">
                <h4><?php echo htmlspecialchars($adminName); ?></h4>
                <p><?php echo htmlspecialchars($adminEmail); ?></p>
            </div>
            <div class="admin-badge"><i class="fas fa-shield-alt"></i> Admin</div>
            <a href="admin-profile.php" class="user-avatar-link" title="View Profile">
                <div class="user-avatar"><?php echo $adminInitials; ?></div>
            </a>
        </div>
    </nav>
</header>

<!-- ===== Notification Banners (fixed, layered) ===== -->
<?php if ($customerConfirmedOrders > 0): ?>
<div class="alert-banner alert-error fade-in" id="notif1" onclick="window.location.href='tire-orders.php?status=cus_confirmed'">
    <div class="alert-icon"><i class="fas fa-bell"></i></div>
    <div class="alert-content">
        <div class="alert-title">⚡ New Customer Confirmed Order<?php echo $customerConfirmedOrders > 1 ? 's' : ''; ?>!</div>
        <div class="alert-msg">Require your immediate review and processing.</div>
    </div>
    <span class="alert-count"><?php echo $customerConfirmedOrders; ?></span>
    <a href="tire-orders.php?status=cus_confirmed" class="alert-action" onclick="event.stopPropagation()"><i class="fas fa-eye"></i> View Orders</a>
    <div class="alert-close" onclick="event.stopPropagation(); closeAlert('notif1')"><i class="fas fa-times"></i></div>
</div>
<?php endif; ?>

<?php if ($cusPiConfirmOrders > 0): ?>
<div class="alert-banner alert-success fade-in <?php echo $customerConfirmedOrders > 0 ? 'second' : ''; ?>" id="notif2" onclick="window.location.href='tire-orders-marketing.php?status=cus_pi_confirm'">
    <div class="alert-icon"><i class="fas fa-file-invoice"></i></div>
    <div class="alert-content">
        <div class="alert-title">✅ PI Confirmed Order<?php echo $cusPiConfirmOrders > 1 ? 's' : ''; ?> Ready</div>
        <div class="alert-msg">Proforma invoice confirmed — ready for processing.</div>
    </div>
    <span class="alert-count"><?php echo $cusPiConfirmOrders; ?></span>
    <a href="tire-orders-marketing.php?status=cus_pi_confirm" class="alert-action" onclick="event.stopPropagation()"><i class="fas fa-eye"></i> View PI Orders</a>
    <div class="alert-close" onclick="event.stopPropagation(); closeAlert('notif2')"><i class="fas fa-times"></i></div>
</div>
<?php endif; ?>

<?php if ($requestReviseOrders > 0): ?>
<div class="alert-banner alert-warning fade-in <?php
    if ($customerConfirmedOrders > 0 && $cusPiConfirmOrders > 0) echo 'third';
    elseif ($customerConfirmedOrders > 0 || $cusPiConfirmOrders > 0) echo 'second';
?>" id="notif3" onclick="window.location.href='tire-orders-revise.php?request_status=request_revise'">
    <div class="alert-icon"><i class="fas fa-edit"></i></div>
    <div class="alert-content">
        <div class="alert-title">📝 Order<?php echo $requestReviseOrders > 1 ? 's' : ''; ?> Require<?php echo $requestReviseOrders == 1 ? 's' : ''; ?> Revision</div>
        <div class="alert-msg">Customer<?php echo $requestReviseOrders > 1 ? 's have' : ' has'; ?> requested changes.</div>
    </div>
    <span class="alert-count"><?php echo $requestReviseOrders; ?></span>
    <a href="tire-orders-revise.php?request_status=request_revise" class="alert-action" onclick="event.stopPropagation()"><i class="fas fa-eye"></i> View Revisions</a>
    <div class="alert-close" onclick="event.stopPropagation(); closeAlert('notif3')"><i class="fas fa-times"></i></div>
</div>
<?php endif; ?>

<!-- ===== Page Wrapper ===== -->
<div class="page-wrapper">
   

    <div class="main-scroll">
    <div class="container">

        <!-- Page Header -->
        <div class="page-header reveal">
            <div class="page-header-left">
                <h1>Admin <span>Dashboard</span></h1>
                <p>Welcome back — here's your full operational overview.</p>
                <?php if ($isAccountManager): ?>
                <div class="acm-tag"><i class="fas fa-user-tie"></i> Account Manager — <?php echo htmlspecialchars($acmRef); ?></div>
                <?php endif; ?>
            </div>
           
        </div>

        <!-- ============================================================ -->
        <!--  OVERVIEW                                                     -->
        <!-- ============================================================ -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-chart-pie"></i></span>
            Overview
            <span class="title-line"></span>
        </h2>

        <div class="stats-grid reveal">
            <div class="stat-card" onclick="location.href='manage-user2.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-trend trend-neutral"><i class="fas fa-layer-group"></i> Total</div>
                </div>
                <div class="stat-value"><?php echo $totusers; ?></div>
                <div class="stat-label"><?php echo $isAccountManager ? 'My Customers' : 'Total Users'; ?></div>
                <div class="stat-description"><?php echo $isAccountManager ? 'Assigned customer accounts' : 'Registered system users'; ?></div>
                <a href="manage-user2.php" class="stat-link">View Details <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php if (!$isAccountManager): ?>
            <div class="stat-card" onclick="location.href='manage-account-managers.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                    <div class="stat-trend trend-neutral"><i class="fas fa-users-cog"></i> Team</div>
                </div>
                <div class="stat-value"><?php echo $totAccountManagers; ?></div>
                <div class="stat-label">Account Managers</div>
                <div class="stat-description">Active team members</div>
                <a href="manage-account-managers.php" class="stat-link">Manage Team <i class="fas fa-arrow-right"></i></a>
            </div>
            <?php endif; ?>
        </div>

        <!-- ============================================================ -->
        <!--  CLAIMS OVERVIEW                                              -->
        <!-- ============================================================ -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-exclamation-triangle"></i></span>
            Claims Overview
            <span class="title-line"></span>
        </h2>

        <div class="stats-grid reveal">
            <div class="stat-card" onclick="location.href='all-complaint2.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="stat-trend trend-neutral"><i class="fas fa-chart-bar"></i> Total</div>
                </div>
                <div class="stat-value"><?php echo $totcom; ?></div>
                <div class="stat-label">Total Claims</div>
                <div class="stat-description"><?php echo $isAccountManager ? 'From your customers' : 'All complaints received'; ?></div>
                <a href="all-complaint2.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card danger" onclick="location.href='notprocess-complaint.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-trend trend-down"><i class="fas fa-exclamation"></i> Urgent</div>
                </div>
                <div class="stat-value"><?php echo $pendingcom; ?></div>
                <div class="stat-label">Pending Review</div>
                <div class="stat-description">Require immediate attention</div>
                <a href="notprocess-complaint.php" class="stat-link">Take Action <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card warning" onclick="location.href='inprocess-complaint.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-cogs"></i></div>
                    <div class="stat-trend trend-warn"><i class="fas fa-wrench"></i> Active</div>
                </div>
                <div class="stat-value"><?php echo $inprocesscom; ?></div>
                <div class="stat-label">In Process</div>
                <div class="stat-description">Currently being handled</div>
                <a href="inprocess-complaint.php" class="stat-link">View Progress <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card success" onclick="location.href='closed-complaint.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-trend trend-up"><i class="fas fa-check"></i> Done</div>
                </div>
                <div class="stat-value"><?php echo $closedcom; ?></div>
                <div class="stat-label">Resolved</div>
                <div class="stat-description">Successfully closed cases</div>
                <a href="closed-complaint.php" class="stat-link">View Resolved <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Claims Quick Actions -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-bolt"></i></span>
            Claims Quick Actions
            <span class="title-line"></span>
        </h2>
        <div class="quick-actions reveal">
            <a href="notprocess-complaint.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="quick-action-title">Review Pending</div>
                <div class="quick-action-subtitle"><?php echo $pendingcom; ?> need attention</div>
            </a>
            <a href="inprocess-complaint.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-spinner"></i></div>
                <div class="quick-action-title">In-Process</div>
                <div class="quick-action-subtitle"><?php echo $inprocesscom; ?> being handled</div>
            </a>
            <a href="closed-complaint.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-check-circle"></i></div>
                <div class="quick-action-title">Resolved Cases</div>
                <div class="quick-action-subtitle"><?php echo $closedcom; ?> closed</div>
            </a>
            <a href="complaint-search.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-search"></i></div>
                <div class="quick-action-title">Search Claims</div>
                <div class="quick-action-subtitle">Find any complaint</div>
            </a>
            <a href="between-date-complaintreport.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="quick-action-title">Reports</div>
                <div class="quick-action-subtitle">Analytics & exports</div>
            </a>
        </div>

        <!-- Recent Claims -->
        <div class="card reveal">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Recent Claims</h2>
                <a href="all-complaint2.php" class="btn btn-primary btn-sm"><i class="fas fa-external-link-alt"></i> View All</a>
            </div>
            <div class="card-body">
                <?php if ($recentComplaints && mysqli_num_rows($recentComplaints) > 0): ?>
                <div class="list-container">
                    <?php while ($complaint = mysqli_fetch_assoc($recentComplaints)): ?>
                    <a href="complaint-details.php?cid=<?php echo $complaint['complaintNumber']; ?>" class="list-item">
                        <div class="item-avatar <?php echo ($complaint['status'] == null ? 'warn' : ($complaint['status'] == 'closed' ? 'success' : '')); ?>">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">#<?php echo htmlspecialchars($complaint['complaintNumber']); ?> — <?php echo htmlspecialchars($complaint['userName'] ?? 'Anonymous'); ?></div>
                            <div class="item-meta"><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y · h:i A', strtotime($complaint['created_at'])); ?></div>
                        </div>
                        <div class="status-badge <?php
                            if ($complaint['status'] == null)             echo 'status-pending';
                            elseif ($complaint['status'] == 'in process') echo 'status-in-process';
                            elseif ($complaint['status'] == 'closed')     echo 'status-closed';
                            else                                           echo 'status-in-process';
                        ?>"><?php echo ucfirst($complaint['status'] ?? 'Pending'); ?></div>
                    </a>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox empty-icon"></i>
                    <h3>No Recent Claims</h3>
                    <p><?php echo $isAccountManager ? 'No complaints from your customers yet.' : 'All caught up! No new complaints.'; ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================ -->
        <!--  ORDERS OVERVIEW                                              -->
        <!-- ============================================================ -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-shopping-bag"></i></span>
            Orders Overview
            <span class="title-line"></span>
        </h2>

        <div class="stats-grid reveal">
            <div class="stat-card" onclick="location.href='tire-orders.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-trend trend-neutral"><i class="fas fa-layer-group"></i> Total</div>
                </div>
                <div class="stat-value"><?php echo $totOrders; ?></div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-description"><?php echo $isAccountManager ? 'From your customers' : 'All tire orders'; ?></div>
                <a href="tire-orders.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card warning" onclick="location.href='tire-orders.php?status=pending'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-trend trend-warn"><i class="fas fa-clock"></i> Pending</div>
                </div>
                <div class="stat-value"><?php echo $pendingOrders; ?></div>
                <div class="stat-label">Pending Orders</div>
                <div class="stat-description">Awaiting confirmation</div>
                <a href="tire-orders.php?status=pending" class="stat-link">Process <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card success" onclick="location.href='tire-orders.php?status=confirmed'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                    <div class="stat-trend trend-up"><i class="fas fa-check"></i> Confirmed</div>
                </div>
                <div class="stat-value"><?php echo $confirmedOrders + $customerConfirmedOrders; ?></div>
                <div class="stat-label">Confirmed Orders</div>
                <div class="stat-description">Admin + customer confirmed</div>
                <a href="tire-orders.php?status=confirmed" class="stat-link">View <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card success" onclick="location.href='tire-orders.php?status=cus_confirmed'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                    <div class="stat-trend trend-up"><i class="fas fa-user"></i> Customer</div>
                </div>
                <div class="stat-value"><?php echo $customerConfirmedOrders; ?></div>
                <div class="stat-label">Customer Confirmed</div>
                <div class="stat-description">Confirmed by customer</div>
                <a href="tire-orders.php?status=cus_confirmed" class="stat-link">View <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card" onclick="location.href='tire-orders.php?status=cus_pi_confirm'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="stat-trend trend-neutral"><i class="fas fa-stamp"></i> PI</div>
                </div>
                <div class="stat-value"><?php echo $cusPiConfirmOrders; ?></div>
                <div class="stat-label">PI Confirmed</div>
                <div class="stat-description">Proforma invoice confirmed</div>
                <a href="tire-orders.php?status=cus_pi_confirm" class="stat-link">View <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-card danger" onclick="location.href='tire-orders-revise.php?request_status=request_revise'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-edit"></i></div>
                    <div class="stat-trend trend-down"><i class="fas fa-redo"></i> Revision</div>
                </div>
                <div class="stat-value"><?php echo $requestReviseOrders; ?></div>
                <div class="stat-label">Revision Requests</div>
                <div class="stat-description">Orders requiring revision</div>
                <a href="tire-orders-revise.php?request_status=request_revise" class="stat-link">Review <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Orders Quick Actions -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-bolt"></i></span>
            Orders Quick Actions
            <span class="title-line"></span>
        </h2>
        <div class="quick-actions reveal">
            <a href="tire-orders.php?status=pending" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-cart-arrow-down"></i></div>
                <div class="quick-action-title">Process Pending</div>
                <div class="quick-action-subtitle"><?php echo $pendingOrders; ?> awaiting</div>
            </a>
            <a href="tire-orders.php?status=cus_confirmed" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-user-check"></i></div>
                <div class="quick-action-title">Cus. Confirmed</div>
                <div class="quick-action-subtitle"><?php echo $customerConfirmedOrders; ?> confirmed</div>
            </a>
            <a href="tire-orders-marketing.php?status=cus_pi_confirm" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="quick-action-title">PI Confirmed</div>
                <div class="quick-action-subtitle"><?php echo $cusPiConfirmOrders; ?> ready</div>
            </a>
            <a href="tire-orders-revise.php?request_status=request_revise" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-edit"></i></div>
                <div class="quick-action-title">Revisions</div>
                <div class="quick-action-subtitle"><?php echo $requestReviseOrders; ?> need changes</div>
            </a>
            <a href="tire-orders.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-list-alt"></i></div>
                <div class="quick-action-title">All Orders</div>
                <div class="quick-action-subtitle">Full order history</div>
            </a>
            <a href="manage-user2.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-users"></i></div>
                <div class="quick-action-title">Manage Users</div>
                <div class="quick-action-subtitle"><?php echo $isAccountManager ? 'Your customers' : 'All accounts'; ?></div>
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="card reveal" style="margin-bottom:3rem;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-shopping-bag"></i> Recent Orders</h2>
                <a href="tire-orders.php" class="btn btn-primary btn-sm"><i class="fas fa-external-link-alt"></i> View All</a>
            </div>
            <div class="card-body">
                <?php if ($recentOrders && mysqli_num_rows($recentOrders) > 0): ?>
                <div class="list-container">
                    <?php while ($order = mysqli_fetch_assoc($recentOrders)): ?>
                    <a href="order-details.php?oid=<?php echo $order['order_id']; ?>" class="list-item">
                        <div class="item-avatar <?php echo ($order['status']=='pending' ? 'warn' : ($order['status']=='confirmed'||$order['status']=='cus_confirmed' ? 'success' : '')); ?>">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">#<?php echo $order['order_id']; ?> — <?php echo htmlspecialchars($order['userName'] ?? 'Anonymous'); ?></div>
                            <div class="item-meta">
                                <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y · h:i A', strtotime($order['order_date'])); ?>
                                &bull; <?php echo $order['total_items']; ?> item<?php echo $order['total_items'] != 1 ? 's' : ''; ?> (<?php echo $order['total_quantity']; ?> qty)
                            </div>
                        </div>
                        <div class="status-badge <?php
                            if ($order['status'] == 'pending')            echo 'status-pending';
                            elseif ($order['status'] == 'confirmed')      echo 'status-confirmed';
                            elseif ($order['status'] == 'cus_confirmed')  echo 'status-cus-confirmed';
                            elseif ($order['status'] == 'cus_pi_confirm') echo 'status-pi-confirmed';
                            else                                           echo 'status-pending';
                        ?>">
                            <?php
                            if ($order['status'] == 'cus_confirmed')      echo 'Cus. Confirmed';
                            elseif ($order['status'] == 'cus_pi_confirm') echo 'PI Confirmed';
                            else echo ucfirst($order['status']);
                            ?>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox empty-icon"></i>
                    <h3>No Recent Orders</h3>
                    <p><?php echo $isAccountManager ? 'No orders from your customers yet.' : 'No new tire orders to review.'; ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /container -->
    </div><!-- /main-scroll -->
</div><!-- /page-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Animated counters ── */
    document.querySelectorAll('.stat-value').forEach((el, i) => {
        const text  = el.textContent.trim();
        const match = text.match(/[\d.]+/);
        if (!match) return;
        const final   = parseFloat(match[0]);
        const isFloat = text.includes('.');
        let   current = 0;
        const inc     = final / 55;
        const timer   = setInterval(() => {
            current += inc;
            if (current >= final) { current = final; clearInterval(timer); }
            el.textContent = isFloat ? current.toFixed(1) : Math.floor(current);
        }, 18 + i * 3);
    });

    /* ── Scroll reveal ── */
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

    /* ── Header shadow on scroll ── */
    window.addEventListener('scroll', () => {
        document.querySelector('.header').style.boxShadow =
            window.scrollY > 10 ? '0 4px 20px rgba(0,0,0,0.08)' : 'none';
    });
});

/* ── Close notification banners ── */
function closeAlert(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
    el.style.opacity    = '0';
    el.style.transform  = 'translateY(-100%)';
    setTimeout(() => {
        el.remove();
        reStackAlerts();
    }, 370);
}

function reStackAlerts() {
    const alerts = document.querySelectorAll('.alert-banner');
    const tops   = [80, 152, 224];
    const count  = alerts.length;
    document.body.className = count ? 'notify-' + count : '';
    alerts.forEach((a, i) => {
        a.classList.remove('second', 'third');
        if (i === 1) a.classList.add('second');
        if (i === 2) a.classList.add('third');
    });
}
</script>
</body>
</html>
<?php mysqli_close($con); ?>