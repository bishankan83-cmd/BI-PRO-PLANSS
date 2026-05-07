<?php
// dashboard.php
session_start();
include('include/config.php');

// Check if user is logged in
if (!isset($_SESSION["aid"])) {
    header("Location: index.php");
    exit();
}

// Sanitize and validate admin ID from session
$adminId = intval($_SESSION["aid"]);

// Verify admin exists and is active
$stmt = mysqli_prepare($con, "SELECT * FROM admin WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$adminResult = mysqli_stmt_get_result($stmt);
$adminData = mysqli_fetch_assoc($adminResult);

if (!$adminData) {
    session_destroy();
    header("Location: index.php");
    exit();
}
mysqli_stmt_close($stmt);

// Check if this admin is an account manager
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

    $stmt2 = mysqli_prepare($con, "SELECT id, cus_id FROM users WHERE acm_ref = ?");
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

// Initialize statistics
$totcom                 = 0;
$pendingcom             = 0;
$inprocesscom           = 0;
$closedcom              = 0;
$newPendingComplaints   = 0;  // status = 'in_process'  (system-wide notification)
$newInProcessComplaints = 0;  // status = 'in process'  (system-wide notification)

// Build IN-clause string for account manager filtering
$userIdsStr = '';
if ($isAccountManager && !empty($userIds)) {
    $userIdsStr = implode(",", $userIds);
}

// ======================================================================
// in_process NOTIFICATION — NO ACM FILTER (visible to ALL admins)
// ======================================================================
$stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status = 'in_process'");
mysqli_stmt_execute($stmt);
$newPendingComplaints = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
mysqli_stmt_close($stmt);

// ======================================================================
// in process NOTIFICATION — NO ACM FILTER (visible to ALL admins)
// ======================================================================
$stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status = 'in process'");
mysqli_stmt_execute($stmt);
$newInProcessComplaints = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
mysqli_stmt_close($stmt);

// ==================== TOTAL COMPLAINTS ====================
if ($isAccountManager && !empty($userIds)) {
    $result = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE userId IN ($userIdsStr)");
    $totcom = mysqli_fetch_assoc($result)['count'];
} else {
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints");
    mysqli_stmt_execute($stmt);
    $totcom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
    mysqli_stmt_close($stmt);
}

// ==================== in_process COMPLAINTS ====================
if ($isAccountManager && !empty($userIds)) {
    $result     = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status = 'in_process' AND userId IN ($userIdsStr)");
    $pendingcom = mysqli_fetch_assoc($result)['count'];
} else {
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status = 'in_process'");
    mysqli_stmt_execute($stmt);
    $pendingcom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
    mysqli_stmt_close($stmt);
}

// ==================== IN PROCESS COMPLAINTS ====================
if ($isAccountManager && !empty($userIds)) {
    $result       = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='in process' AND userId IN ($userIdsStr)");
    $inprocesscom = mysqli_fetch_assoc($result)['count'];
} else {
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='in process'");
    mysqli_stmt_execute($stmt);
    $inprocesscom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
    mysqli_stmt_close($stmt);
}

// ==================== CLOSED COMPLAINTS ====================
if ($isAccountManager && !empty($userIds)) {
    $result    = mysqli_query($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='closed' AND userId IN ($userIdsStr)");
    $closedcom = mysqli_fetch_assoc($result)['count'];
} else {
    $stmt = mysqli_prepare($con, "SELECT COUNT(*) as count FROM tbl_tire_complaints WHERE status='closed'");
    mysqli_stmt_execute($stmt);
    $closedcom = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['count'];
    mysqli_stmt_close($stmt);
}

// ==================== RECENT COMPLAINTS (limit 5) ====================
if ($isAccountManager && !empty($userIds)) {
    $recentComplaintsQuery = "SELECT tcmp.*, u.fullName as userName 
                              FROM tbl_tire_complaints tcmp 
                              LEFT JOIN users u ON u.id = tcmp.userId 
                              WHERE tcmp.userId IN ($userIdsStr) 
                              ORDER BY tcmp.created_at DESC LIMIT 5";
} else {
    $recentComplaintsQuery = "SELECT tcmp.*, u.fullName as userName 
                              FROM tbl_tire_complaints tcmp 
                              LEFT JOIN users u ON u.id = tcmp.userId 
                              ORDER BY tcmp.created_at DESC LIMIT 5";
}
$recentComplaints = mysqli_query($con, $recentComplaintsQuery);

// How many banners will be shown? (used to offset main content)
$activeBanners = ($newPendingComplaints > 0 ? 1 : 0) + ($newInProcessComplaints > 0 ? 1 : 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Customer Service Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --bg-light: #f8fafc;
            --success: #27ae60;
            --warning: #f39c12;
            --info: #3498db;
            --teal: #1abc9c;
            --text-gray: #64748b;
            --border-gray: #e0e0e0;
            --orange-light: rgba(242,128,24,0.1);
            --success-light: rgba(39,174,96,0.1);
            --warning-light: rgba(241,196,15,0.1);
            --info-light: rgba(52,152,219,0.1);
            --teal-light: rgba(26,188,156,0.1);
            --purple-light: rgba(155,89,182,0.1);
            --purple: #9b59b6;
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg,#F28018,#e67e22);
            --gradient-2: linear-gradient(135deg,#27ae60,#2ecc71);
            --gradient-4: linear-gradient(135deg,#f39c12,#e67e22);
            --gradient-5: linear-gradient(135deg,#3498db,#2980b9);
            --gradient-7: linear-gradient(135deg,#1abc9c,#16a085);
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04);
            --banner-h: 68px; /* approx height of one banner */
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── NOTIFICATION BANNERS ── */
        .top-notification-alert {
            position: fixed; left: 0; right: 0;
            color: var(--white); padding: .85rem 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
            z-index: 9999;
            animation: slideDown .4s ease-out;
        }

        /* Banner 1 — in_process (teal) sits just below the site header */
        .alert-inprocess-new {
            top: 80px;
            background: linear-gradient(135deg,#1abc9c,#16a085);
            border-bottom: 3px solid #0e6655;
        }

        /* Banner 2 — in process (orange/warning) sits below banner 1 */
        .alert-inprocess {
            top: calc(80px + var(--banner-h));   /* pushed down by banner 1 height */
            background: linear-gradient(135deg,#f39c12,#e67e22);
            border-bottom: 3px solid #ca7d08;
        }

        /* When only the in process banner is shown (no in_process banner above it) */
        .alert-inprocess.solo {
            top: 80px;
        }

        /* Push main content below however many banners are active */
        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            transition: padding-top .3s ease;
        }
        .main-content.banners-1 { padding-top: calc(var(--banner-h) + 2rem); }
        .main-content.banners-2 { padding-top: calc(var(--banner-h) * 2 + 2rem); }

        @keyframes slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes slideUp   { from { transform: translateY(0); opacity: 1; } to { transform: translateY(-100%); opacity: 0; } }

        .top-notification-content {
            max-width: 1400px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        }
        .top-notification-icon {
            width: 44px; height: 44px; background: rgba(255,255,255,.2); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
            animation: pulse 2s ease-in-out infinite; flex-shrink: 0;
        }
        @keyframes pulse {
            0%,100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,.7); }
            50%      { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255,255,255,0); }
        }
        .top-notification-message {
            flex: 1; display: flex; align-items: center; gap: 1rem;
            font-size: 1.05rem; font-weight: 700; letter-spacing: .5px;
        }
        .notification-count-badge {
            background: var(--white);
            padding: .35rem .9rem; border-radius: 2rem;
            font-size: 1.1rem; font-weight: 900; min-width: 46px; text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.2); animation: bounce 1s ease-in-out infinite;
        }
        /* Badge text colour matches each banner */
        .alert-inprocess-new .notification-count-badge { color: #16a085; }
        .alert-inprocess     .notification-count-badge { color: #e67e22; }

        @keyframes bounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }

        .top-notification-close {
            width: 34px; height: 34px; background: rgba(255,255,255,.2); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .3s; flex-shrink: 0;
        }
        .top-notification-close:hover { background: rgba(255,255,255,.3); transform: rotate(90deg); }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .75rem 1.5rem; border: none; border-radius: .75rem;
            font-weight: 600; text-decoration: none; cursor: pointer;
            transition: all .2s; font-size: .9rem; white-space: nowrap;
            background: var(--gradient-1); color: var(--white);
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-secondary {
            background: var(--white); color: var(--text-gray);
            border: 1px solid var(--border-gray); box-shadow: none;
        }
        .btn-secondary:hover { background: var(--bg-light); border-color: var(--primary-orange); color: var(--primary-orange); transform: none; }
        .btn-info { background: var(--gradient-5); color: var(--white); }
        .btn-warning { background: var(--gradient-4); color: var(--white); }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
        }
        .page-title-section { flex: 1; min-width: 300px; }
        .page-title { font-size: 2rem; font-weight: 800; color: var(--dark-gray); margin-bottom: .5rem; }
        .page-subtitle { color: var(--text-gray); font-size: 1rem; line-height: 1.5; }
        .acm-badge {
            display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem;
            background: var(--info-light); color: var(--info); border-radius: .5rem;
            font-size: .85rem; font-weight: 600; margin-top: .5rem;
        }
        .admin-info {
            display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem;
            background: var(--purple-light); color: var(--purple); border-radius: .5rem;
            font-size: .75rem; font-weight: 600; margin-top: .5rem; margin-left: .5rem;
        }
        .header-actions-right { display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-start; }

        /* ── STAT CARDS ── */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem; margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--white); border-radius: 1rem; padding: 2rem;
            border: 1px solid var(--border-gray); position: relative; overflow: hidden;
            transition: all .3s; box-shadow: var(--shadow-sm);
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--gradient-1); }
        .stat-card.success::before { background: var(--gradient-2); }
        .stat-card.warning::before { background: var(--gradient-4); }
        .stat-card.info::before    { background: var(--gradient-5); }
        .stat-card.teal::before    { background: var(--gradient-7); }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-xl); }

        .stat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .stat-icon {
            width: 3.5rem; height: 3.5rem; border-radius: 1rem;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            background: var(--orange-light); color: var(--primary-orange);
        }
        .stat-card.success .stat-icon { background: var(--success-light); color: var(--success); }
        .stat-card.warning .stat-icon { background: var(--warning-light); color: var(--warning); }
        .stat-card.info    .stat-icon { background: var(--info-light);    color: var(--info); }
        .stat-card.teal    .stat-icon { background: var(--teal-light);    color: var(--teal); }

        .stat-value       { font-size: 2.5rem; font-weight: 900; color: var(--dark-gray); line-height: 1; margin-bottom: .5rem; }
        .stat-label       { font-weight: 600; color: var(--text-gray); margin-bottom: .25rem; }
        .stat-description { font-size: .85rem; color: var(--text-gray); opacity: .8; }
        .stat-link {
            display: inline-flex; align-items: center; gap: .25rem; margin-top: .75rem;
            color: var(--primary-orange); font-size: .85rem; font-weight: 600;
            text-decoration: none; transition: gap .2s;
        }
        .stat-link:hover { gap: .5rem; }

        /* ── CONTENT GRID ── */
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }

        .card {
            background: var(--white); border-radius: 1rem;
            border: 1px solid var(--border-gray); overflow: hidden; box-shadow: var(--shadow-sm);
        }
        .card-header {
            padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-gray);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-title { font-size: 1.25rem; font-weight: 700; color: var(--dark-gray); display: flex; align-items: center; gap: .75rem; }
        .card-body  { padding: 2rem; }

        /* ── Claim LIST ── */
        .complaints-list { display: flex; flex-direction: column; gap: 1rem; }
        .Claim-item {
            display: flex; align-items: center; gap: 1rem; padding: 1.5rem;
            background: var(--bg-light); border-radius: .75rem; border: 1px solid var(--border-gray);
            transition: all .2s; text-decoration: none; color: inherit;
        }
        .Claim-item:hover {
            background: var(--white); border-color: var(--primary-orange);
            transform: translateX(.25rem); box-shadow: var(--shadow-md);
        }
        .Claim-avatar {
            width: 3rem; height: 3rem; border-radius: .75rem;
            background: var(--orange-light); display: flex; align-items: center; justify-content: center;
            color: var(--primary-orange); font-size: 1.25rem; flex-shrink: 0;
        }
        .Claim-details { flex: 1; min-width: 0; }
        .Claim-title {
            font-weight: 600; color: var(--dark-gray); margin-bottom: .25rem;
            font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .Claim-meta { font-size: .8rem; color: var(--text-gray); }

        .status-badge { padding: .375rem .875rem; border-radius: 1rem; font-size: .75rem; font-weight: 600; text-transform: capitalize; white-space: nowrap; }
        .status-in_process  { background: var(--teal-light);    color: var(--teal); }
        .status-in-process  { background: var(--warning-light); color: var(--warning); }
        .status-closed      { background: var(--success-light); color: var(--success); }

        /* ── QUICK ACTIONS ── */
        .quick-actions { display: flex; flex-direction: column; gap: 1rem; }
        .action-card {
            padding: 1.5rem; background: var(--bg-light); border-radius: .75rem;
            border: 1px solid var(--border-gray); text-decoration: none; color: inherit;
            transition: all .2s; display: flex; align-items: center; gap: 1rem;
        }
        .action-card:hover { background: var(--white); border-color: var(--primary-orange); transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .action-icon {
            width: 3rem; height: 3rem; border-radius: .75rem;
            background: var(--orange-light); display: flex; align-items: center; justify-content: center;
            color: var(--primary-orange); font-size: 1.25rem; flex-shrink: 0;
        }
        .action-content h3 { font-size: 1rem; font-weight: 600; color: var(--dark-gray); margin-bottom: .25rem; }
        .action-content p  { font-size: .85rem; color: var(--text-gray); }

        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-gray); }
        .empty-icon  { font-size: 3rem; margin-bottom: 1rem; opacity: .5; }

        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: slideIn .6s ease-out forwards; }

        /* ── RESPONSIVE ── */
        @media(max-width:1200px) { .stats-container { grid-template-columns: repeat(2, 1fr); } }
        @media(max-width:1024px) { .content-grid { grid-template-columns: 1fr; gap: 1.5rem; } }
        @media(max-width:768px) {
            :root { --banner-h: 110px; }
            .top-notification-alert { padding: 1rem; }
            .top-notification-content { flex-direction: column; text-align: center; }
            .top-notification-message { flex-direction: column; font-size: 1rem; }
            .main-content { padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; align-items: stretch; }
            .page-title-section { min-width: 100%; }
            .header-actions-right { flex-direction: column; }
            .stats-container { grid-template-columns: 1fr; }
            .btn { justify-content: center; width: 100%; }
            .admin-info { margin-left: 0; margin-top: .25rem; }
        }
    </style>
</head>
<body>

<?php include('include/header.php'); ?>

<?php
// Determine if "in process" banner is solo (no in_process banner above it)
$inProcessBannerClass = ($newPendingComplaints > 0) ? 'alert-inprocess' : 'alert-inprocess solo';
?>

<!-- ── BANNER 1: in_process (teal) ── -->
<?php if ($newPendingComplaints > 0): ?>
<div class="top-notification-alert alert-inprocess-new" id="alertInProcessNew">
    <div class="top-notification-content">
        <div class="top-notification-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="top-notification-message">
            <strong><?php echo $newPendingComplaints > 1 ? 'NEW COMPLAINTS' : 'NEW Claim'; ?>!</strong>
            <span class="notification-count-badge"><?php echo $newPendingComplaints; ?></span>
        </div>
        <a href="notprocess-Claim.php" class="btn" style="margin:0; background:rgba(255,255,255,.25); color:#fff; border:2px solid rgba(255,255,255,.5);">
            <i class="fas fa-eye"></i> View
        </a>
        <div class="top-notification-close" onclick="closeBanner('alertInProcessNew', 1)">
            <i class="fas fa-times"></i>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── BANNER 2: in process (orange/warning) ── -->
<?php if ($newInProcessComplaints > 0): ?>
<div class="top-notification-alert <?php echo $inProcessBannerClass; ?>" id="alertInProcess">
    <div class="top-notification-content">
        <div class="top-notification-icon"><i class="fas fa-spinner fa-spin"></i></div>
        <div class="top-notification-message">
            <strong><?php echo $newInProcessComplaints > 1 ? 'IN PROCESS COMPLAINTS' : 'IN PROCESS Claim'; ?>!</strong>
            <span class="notification-count-badge"><?php echo $newInProcessComplaints; ?></span>
        </div>
        <a href="inprocess-Claim.php" class="btn" style="margin:0; background:rgba(255,255,255,.25); color:#fff; border:2px solid rgba(255,255,255,.5);">
            <i class="fas fa-eye"></i> View
        </a>
        <div class="top-notification-close" onclick="closeBanner('alertInProcess', 2)">
            <i class="fas fa-times"></i>
        </div>
    </div>
</div>
<?php endif; ?>

<main class="main-content banners-<?php echo $activeBanners; ?>" id="mainContent">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-title-section">
            <h1 class="page-title">Dashboard Analytics</h1>
            <p class="page-subtitle">
                Welcome back, <?php echo htmlspecialchars($adminData['fullname']); ?>.
                Here's what's happening with your Customer Service today.
            </p>
            <?php if ($isAccountManager): ?>
                <div class="acm-badge">
                    <i class="fas fa-user-shield"></i>
                    Account Manager View — Showing data for <?php echo count($customerIds); ?> customer<?php echo count($customerIds) != 1 ? 's' : ''; ?>
                </div>
                <div class="admin-info">
                    <i class="fas fa-id-badge"></i>
                    Admin ID: <?php echo $adminId; ?> | ACM Ref: <?php echo htmlspecialchars($acmRef); ?>
                </div>
            <?php else: ?>
                <div class="admin-info">
                    <i class="fas fa-user-cog"></i>
                    Admin ID: <?php echo $adminId; ?> | Role: Full Access Admin
                </div>
            <?php endif; ?>
        </div>
        
    </div>

    <!-- STAT CARDS -->
    <div class="stats-container animate-in">

        <div class="stat-card">
            <div class="stat-header"><div class="stat-icon"><i class="fas fa-file-alt"></i></div></div>
            <div class="stat-value"><?php echo $totcom; ?></div>
            <div class="stat-label">Total Complaints</div>
            <div class="stat-description"><?php echo $isAccountManager ? 'From your customers' : 'All complaints received'; ?></div>
            <a href="all-complaint2_qad.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="stat-card teal">
            <div class="stat-header"><div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div></div>
            <div class="stat-value"><?php echo $pendingcom; ?></div>
            <div class="stat-label">New / in_process Complaints</div>
            <div class="stat-description">
                <?php if ($isAccountManager && $newPendingComplaints > $pendingcom): ?>
                    From your customers
                    <br><small style="color:var(--teal);">(<?php echo $newPendingComplaints; ?> total system-wide)</small>
                <?php else: ?>
                    Require immediate attention
                <?php endif; ?>
            </div>
            <a href="notprocess-Claim.php" class="stat-link">Take Action <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="stat-card warning">
            <div class="stat-header"><div class="stat-icon"><i class="fas fa-spinner"></i></div></div>
            <div class="stat-value"><?php echo $inprocesscom; ?></div>
            <div class="stat-label">In Process</div>
            <div class="stat-description">
                <?php if ($isAccountManager && $newInProcessComplaints > $inprocesscom): ?>
                    From your customers
                    <br><small style="color:var(--warning);">(<?php echo $newInProcessComplaints; ?> total system-wide)</small>
                <?php else: ?>
                    Currently being handled
                <?php endif; ?>
            </div>
            <a href="inprocess-Claim.php" class="stat-link">View Progress <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="stat-card success">
            <div class="stat-header"><div class="stat-icon"><i class="fas fa-check-circle"></i></div></div>
            <div class="stat-value"><?php echo $closedcom; ?></div>
            <div class="stat-label">Resolved Complaints</div>
            <div class="stat-description">Successfully closed cases</div>
            <a href="closed-Claim.php" class="stat-link">View Resolved <i class="fas fa-arrow-right"></i></a>
        </div>

    </div><!-- /stats-container -->

    <!-- RECENT COMPLAINTS & QUICK ACTIONS -->
    <div class="content-grid">

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-clock"></i> Recent Complaints</h2>
                <a href="all-complaint2_qad.php" class="btn btn-secondary" style="padding:.5rem 1rem;font-size:.8rem;">View All</a>
            </div>
            <div class="card-body">
                <?php if ($recentComplaints && mysqli_num_rows($recentComplaints) > 0): ?>
                    <div class="complaints-list">
                        <?php while ($Claim = mysqli_fetch_assoc($recentComplaints)): ?>
                            <a href="Claim-details.php?cid=<?php echo $Claim['complaintNumber']; ?>" class="Claim-item">
                                <div class="Claim-avatar"><i class="fas fa-file-alt"></i></div>
                                <div class="Claim-details">
                                    <div class="Claim-title">
                                        Claim #<?php echo $Claim['complaintNumber']; ?> — <?php echo htmlspecialchars($Claim['userName'] ?? 'Anonymous'); ?>
                                    </div>
                                    <div class="Claim-meta">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('M d, Y - h:i A', strtotime($Claim['created_at'])); ?>
                                    </div>
                                </div>
                                <?php
                                $cs = strtolower($Claim['status']);
                                if ($cs === 'in_process')     { $bc = 'status-in_process';  $bl = 'New / in_process'; }
                                elseif ($cs === 'in process') { $bc = 'status-in-process';  $bl = 'In Process'; }
                                elseif ($cs === 'closed')     { $bc = 'status-closed';      $bl = 'Closed'; }
                                else                          { $bc = 'status-in_process';  $bl = ucfirst($Claim['status']); }
                                ?>
                                <div class="status-badge <?php echo $bc; ?>"><?php echo $bl; ?></div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                        <h3>No recent complaints</h3>
                        <p><?php echo $isAccountManager ? 'No complaints from your customers yet.' : 'All caught up! No new complaints to review.'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
            </div>
            <div class="card-body">
                <div class="quick-actions">

                    <a href="notprocess-Claim.php" class="action-card">
                        <div class="action-icon" style="background:var(--teal-light);color:var(--teal);">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="action-content">
                            <h3>Review New / in_process Complaints</h3>
                            <p><?php echo $newPendingComplaints; ?> Claim<?php echo $newPendingComplaints != 1 ? 's' : ''; ?> need attention (system-wide)</p>
                        </div>
                    </a>

                    <a href="inprocess-Claim.php" class="action-card">
                        <div class="action-icon" style="background:var(--warning-light);color:var(--warning);">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <div class="action-content">
                            <h3>In Process Complaints</h3>
                            <p><?php echo $newInProcessComplaints; ?> Claim<?php echo $newInProcessComplaints != 1 ? 's' : ''; ?> currently being handled (system-wide)</p>
                        </div>
                    </a>

                    <a href="closed-Claim.php" class="action-card">
                        <div class="action-icon" style="background:var(--success-light);color:var(--success);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="action-content">
                            <h3>Resolved Complaints</h3>
                            <p><?php echo $closedcom; ?> Claim<?php echo $closedcom != 1 ? 's' : ''; ?> successfully closed</p>
                        </div>
                    </a>

                    <a href="between-date-complaintreport.php" class="action-card">
                        <div class="action-icon"><i class="fas fa-chart-bar"></i></div>
                        <div class="action-content">
                            <h3>Generate Reports</h3>
                            <p>Create detailed analytics reports</p>
                        </div>
                    </a>

                    <a href="Claim-search.php" class="action-card">
                        <div class="action-icon"><i class="fas fa-search"></i></div>
                        <div class="action-content">
                            <h3>Search Complaints</h3>
                            <p>Find specific complaints quickly</p>
                        </div>
                    </a>

                </div>
            </div>
        </div>

    </div><!-- /content-grid -->

</main>

<script>
    // Track how many banners are currently visible
    let activeBanners = <?php echo $activeBanners; ?>;

    function closeBanner(id, bannerIndex) {
        const el = document.getElementById(id);
        if (!el) return;

        el.style.animation = 'slideUp .4s ease-out forwards';

        setTimeout(() => {
            el.remove();
            activeBanners--;

            // Update main content padding
            const main = document.getElementById('mainContent');
            main.className = 'main-content' + (activeBanners > 0 ? ' banners-' + activeBanners : '');

            // If banner 1 (in_process / teal) is closed but banner 2 (in process / orange) is still visible,
            // move banner 2 up so it no longer has a gap at the top.
            if (bannerIndex === 1) {
                const banner2 = document.getElementById('alertInProcess');
                if (banner2) {
                    banner2.classList.add('solo');
                    banner2.classList.remove('alert-inprocess');
                    // Re-trigger animation so it slides smoothly
                    banner2.style.animation = 'none';
                    banner2.offsetHeight; // reflow
                    banner2.style.animation = 'slideDown .4s ease-out';
                }
            }
        }, 400);
    }
</script>
</body>
</html>