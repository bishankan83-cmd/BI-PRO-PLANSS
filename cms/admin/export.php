<?php
// export.php
session_start();
include('include/config.php');

// Check if user is logged in
if (!isset($_SESSION["aid"])) {
    header("Location: index.php");
    exit();
}

// Sanitize admin ID
$adminId = intval($_SESSION["aid"]);

// Fetch admin details
$stmt = mysqli_prepare($con, "SELECT * FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$adminResult = mysqli_stmt_get_result($stmt);
$adminData = mysqli_fetch_assoc($adminResult);
mysqli_stmt_close($stmt);

if (!$adminData) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Admin initials
$adminName     = $adminData['fullName'] ?? $adminData['name'] ?? 'Admin';
$adminEmail    = $adminData['email'] ?? '';
$adminInitials = strtoupper(substr($adminName, 0, 1));
if (strpos($adminName, ' ') !== false)
    $adminInitials .= strtoupper(substr($adminName, strpos($adminName, ' ') + 1, 1));

// Check if this admin is an account manager
$isAccountManager = false;
$acmRef = '';
$customerIds = [];

$stmt = mysqli_prepare($con, "SELECT acm_ref FROM account_managers WHERE id = ? AND status = 'active'");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $isAccountManager = true;
    $acmRef = $row['acm_ref'];
    
    $stmt2 = mysqli_prepare($con, "SELECT cus_id FROM users WHERE acm_ref = ?");
    mysqli_stmt_bind_param($stmt2, "s", $acmRef);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    while ($customer = mysqli_fetch_assoc($result2)) {
        $customerIds[] = $customer['cus_id'];
    }
    mysqli_stmt_close($stmt2);
}
mysqli_stmt_close($stmt);

// Build customer IDs string for queries
$customerIdsStr = '';
if ($isAccountManager && !empty($customerIds)) {
    $customerIdsStr = "'" . implode("','", array_map(function($id) use ($con) {
        return mysqli_real_escape_string($con, $id);
    }, $customerIds)) . "'";
}

// Fetch statistics
$totalOrders = 0;
$activeOrders = 0;
$completedOrders = 0;

if ($isAccountManager && !empty($customerIds)) {
    // Count total orders
    $query = "SELECT COUNT(*) as total FROM tire_orders tord 
              LEFT JOIN users u ON u.id = tord.customer_id 
              WHERE u.cus_id IN ($customerIdsStr)";
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totalOrders = $row['total'];
    }
    
    // Count active orders
    $query = "SELECT COUNT(*) as total FROM tire_orders tord 
              LEFT JOIN users u ON u.id = tord.customer_id 
              WHERE u.cus_id IN ($customerIdsStr) 
              AND tord.status IN ('pending', 'processing', 'cus_pi_confirm')";
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $activeOrders = $row['total'];
    }
    
    // Count completed orders
    $query = "SELECT COUNT(*) as total FROM tire_orders tord 
              LEFT JOIN users u ON u.id = tord.customer_id 
              WHERE u.cus_id IN ($customerIdsStr) 
              AND tord.status = 'completed'";
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $completedOrders = $row['total'];
    }
} else {
    // Count total orders
    $query = "SELECT COUNT(*) as total FROM tire_orders";
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totalOrders = $row['total'];
    }
    
    // Count active orders
    $query = "SELECT COUNT(*) as total FROM tire_orders 
              WHERE status IN ('pending', 'processing', 'cus_pi_confirm')";
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $activeOrders = $row['total'];
    }
    
    // Count completed orders
    $query = "SELECT COUNT(*) as total FROM tire_orders 
              WHERE status = 'completed'";
    $result = mysqli_query($con, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $completedOrders = $row['total'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Export - ATIRE Customer Service</title>
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

        /* ===== LAYOUT ===== */
        .page-wrapper { display:flex; min-height:calc(100vh - 80px); }
        .main-scroll  { flex:1; overflow-x:hidden; }
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

        /* ===== SECTION TITLE ===== */
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

        /* ===== STATS GRID ===== */
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
        .stat-card.info::after    { background:var(--primary-orange); }
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

        /* ===== QUICK ACTIONS GRID ===== */
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
        .status-active        { background:var(--orange-light);  color:var(--primary-orange); border:1px solid rgba(242,128,24,0.25); }
        .status-completed     { background:var(--success-light); color:var(--success); border:1px solid rgba(39,174,96,0.3); }
        .status-in-process    { background:var(--orange-light);  color:var(--primary-orange); border:1px solid rgba(242,128,24,0.25); }

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
            .card-body { padding:1.2rem; } .card-header { padding:1.1rem 1.2rem; }
        }
        @media (max-width:480px) {
            .stats-grid { grid-template-columns:1fr; }
            .quick-actions { grid-template-columns:repeat(2,1fr); }
            .list-item { flex-wrap:wrap; }
        }
    </style>
</head>
<body>

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

<!-- ===== Page Wrapper ===== -->
<div class="page-wrapper">
    <div class="main-scroll">
    <div class="container">

        <!-- Page Header -->
        <div class="page-header reveal">
            <div class="page-header-left">
                <h1>Dashboard <span>Export</span></h1>
                <p>Orders overview and analytics — manage your shipping data.</p>
                <?php if ($isAccountManager): ?>
                <div class="acm-tag"><i class="fas fa-user-tie"></i> Account Manager — <?php echo htmlspecialchars($acmRef); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================ -->
        <!--  STATISTICS OVERVIEW                                         -->
        <!-- ============================================================ -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-chart-pie"></i></span>
            Orders Overview
            <span class="title-line"></span>
        </h2>

        <div class="stats-grid reveal">
            <div class="stat-card" onclick="location.href='orders-list.php'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-trend trend-neutral"><i class="fas fa-layer-group"></i> Total</div>
                </div>
                <div class="stat-value"><?php echo $totalOrders; ?></div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-description"><?php echo $isAccountManager ? 'From your customers' : 'All time orders'; ?></div>
                <a href="orders-list.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="stat-card warning" onclick="location.href='orders-list.php?status=active'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-trend trend-warn"><i class="fas fa-spinner"></i> Active</div>
                </div>
                <div class="stat-value"><?php echo $activeOrders; ?></div>
                <div class="stat-label">Active Orders</div>
                <div class="stat-description">In progress</div>
                <a href="orders-list.php?status=active" class="stat-link">Process <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="stat-card success" onclick="location.href='orders-list.php?status=completed'">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-trend trend-up"><i class="fas fa-check"></i> Done</div>
                </div>
                <div class="stat-value"><?php echo $completedOrders; ?></div>
                <div class="stat-label">Completed Orders</div>
                <div class="stat-description">Successfully completed</div>
                <a href="orders-list.php?status=completed" class="stat-link">View <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 class="section-title reveal">
            <span class="title-icon"><i class="fas fa-bolt"></i></span>
            Quick Actions
            <span class="title-line"></span>
        </h2>
        <div class="quick-actions reveal">
            <a href="orders-list.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-list-alt"></i></div>
                <div class="quick-action-title">All Orders</div>
                <div class="quick-action-subtitle"><?php echo $totalOrders; ?> total</div>
            </a>
            <a href="orders-list.php?status=active" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-spinner"></i></div>
                <div class="quick-action-title">In Progress</div>
                <div class="quick-action-subtitle"><?php echo $activeOrders; ?> active</div>
            </a>
            <a href="data_enter_mar.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-boxes"></i></div>
                <div class="quick-action-title">Add Shipping</div>
                <div class="quick-action-subtitle">Update tracking data</div>
            </a>
            <a href="orders-list.php?status=completed" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-check-circle"></i></div>
                <div class="quick-action-title">Completed</div>
                <div class="quick-action-subtitle"><?php echo $completedOrders; ?> closed</div>
            </a>
            <a href="reports.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="quick-action-title">Reports</div>
                <div class="quick-action-subtitle">Analytics & exports</div>
            </a>
            <a href="customers.php" class="quick-action-card">
                <div class="quick-action-icon"><i class="fas fa-users"></i></div>
                <div class="quick-action-title">Customers</div>
                <div class="quick-action-subtitle">Manage accounts</div>
            </a>
        </div>

        <!-- Main Actions Card -->
        <div class="card reveal">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-cogs"></i> Primary Actions</h2>
            </div>
            <div class="card-body">
                <div class="list-container">
                    <a href="orders-list.php" class="list-item">
                        <div class="item-avatar">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">View All Orders</div>
                            <div class="item-meta">Access and manage all customer orders in one place</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:var(--primary-orange); font-size:1.2rem;"></i>
                    </a>
                    <a href="inventory-management.php" class="list-item">
                        <div class="item-avatar" style="background:var(--success);">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">Inventory Management</div>
                            <div class="item-meta">Manage stock levels, track items, and update inventory information</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:var(--success); font-size:1.2rem;"></i>
                    </a>
                    <a href="customers.php" class="list-item">
                        <div class="item-avatar" style="background:var(--primary-orange);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">Customer Management</div>
                            <div class="item-meta">View and manage customer accounts, contacts, and relationship details</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:var(--primary-orange); font-size:1.2rem;"></i>
                    </a>
                    <a href="data_enter_mar.php" class="list-item">
                        <div class="item-avatar" style="background:var(--warning);">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">Add Shipping Data</div>
                            <div class="item-meta">Enter and update shipping information for orders</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:var(--warning); font-size:1.2rem;"></i>
                    </a>
                    <a href="reports.php" class="list-item">
                        <div class="item-avatar" style="background:var(--info);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">Reports & Analytics</div>
                            <div class="item-meta">View detailed reports and export data for analysis</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:var(--info); font-size:1.2rem;"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="card reveal" style="margin-bottom:3rem;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-cog"></i> System Settings</h2>
            </div>
            <div class="card-body">
                <div class="list-container">
                    <a href="settings.php" class="list-item">
                        <div class="item-avatar" style="background:var(--primary-orange);">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">Application Settings</div>
                            <div class="item-meta">Configure system preferences and general settings</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:var(--primary-orange); font-size:1.2rem;"></i>
                    </a>
                    <a href="admin-profile.php" class="list-item">
                        <div class="item-avatar" style="background:var(--success);">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="item-details">
                            <div class="item-title">Admin Profile</div>
                            <div class="item-meta">Update your profile information and security settings</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color:var(--success); font-size:1.2rem;"></i>
                    </a>
                </div>
            </div>
        </div>

    </div><!-- /container -->
    </div><!-- /main-scroll -->
</div><!-- /page-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', () => {
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
</script>
</body>
</html>
<?php mysqli_close($con); ?>