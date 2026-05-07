<?php
session_start();
include('include/config.php');

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$userId = intval($_SESSION["id"]);
$userQuery = mysqli_query($con, "SELECT * FROM users WHERE id='$userId'");
$userData = mysqli_fetch_array($userQuery);

if (!$userData) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Total Complaints
$queryUserComplaints = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId='$userId'");
$totalUserComplaints = mysqli_num_rows($queryUserComplaints);

// Pending Complaints
$queryUserPending = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId='$userId' AND (status='Pending' OR status IS NULL OR status='')");
$pendingUserComplaints = mysqli_num_rows($queryUserPending);

// In Process Complaints
$queryUserInProcess = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId='$userId' AND status='In process'");
$inProcessUserComplaints = mysqli_num_rows($queryUserInProcess);

// Closed Complaints
$queryUserClosed = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId='$userId' AND (status='closed' OR status='Closed' OR status='Resolved')");
$closedUserComplaints = mysqli_num_rows($queryUserClosed);

// ============== ORDERS QUERIES ==============

// Exclude 'revised' from Order Summery
$queryUserOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND status != 'revised'");
$totalUserOrders = mysqli_num_rows($queryUserOrders);

// Pending Orders
$queryUserPendingOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND (status='pending' OR status IS NULL OR status='')");
$pendingUserOrders = mysqli_num_rows($queryUserPendingOrders);

// Confirmed Orders
$queryUserConfirmedOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND (status='confirmed' OR status LIKE '%confirmed%')");
$confirmedUserOrders = mysqli_num_rows($queryUserConfirmedOrders);

// In Progress Orders
$queryUserInProgressOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND (status='in progress' OR status='processing' OR status LIKE '%planning%' OR status='Share_planning')");
$inProgressUserOrders = mysqli_num_rows($queryUserInProgressOrders);

// Complete Orders
$queryUsercompleteOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND status='complete'");
$completeUserOrders = mysqli_num_rows($queryUsercompleteOrders);

// Revised Orders
$queryUserRevisedOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND status='revised'");
$revisedUserOrders = mysqli_num_rows($queryUserRevisedOrders);

// PI Confirmed Orders
$queryUserPIConfirmedOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND (status='PI Confirmed' OR status='pi_confirm' OR status LIKE '%PI%Confirmed%')");
$piConfirmedUserOrders = mysqli_num_rows($queryUserPIConfirmedOrders);

// ============== TO BE PRODUCTION ORDERS (PRICE PENDING OR CUS CONFIRMED) ==============
$queryUserToBeProduction = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id='$userId' AND (status='price_pending' OR status='price pending' OR status='cus_confirmed' OR status='cus_confirm' OR status='customer_confirmed')");
$toBeProductionOrders = mysqli_num_rows($queryUserToBeProduction);

// Feedback & Rating
$queryUserFeedback = mysqli_query($con, "SELECT id FROM tbl_customer_feedback WHERE userId='$userId'");
$totalUserFeedback = mysqli_num_rows($queryUserFeedback);

$queryUserAvgRating = mysqli_query($con, "SELECT AVG(rating) as avg_rating FROM tbl_customer_feedback WHERE userId='$userId'");
$avgRatingData = mysqli_fetch_assoc($queryUserAvgRating);
$userAvgRating = $avgRatingData && $avgRatingData['avg_rating'] ? round($avgRatingData['avg_rating'], 1) : 0;

// Recent Activity
$recentComplaints = mysqli_query($con, "SELECT tc.*, tc.complaintNumber, tc.serial_number, tc.tire_size, tc.nature_complaint FROM tbl_tire_complaints tc WHERE tc.userId='$userId' ORDER BY tc.created_at DESC LIMIT 5");

$recentOrders = mysqli_query($con, "SELECT o.*, 
                                    COUNT(DISTINCT oi.item_id) as total_items,
                                    GROUP_CONCAT(DISTINCT oi.icode ORDER BY oi.item_id SEPARATOR ', ') as product_codes, 
                                    COALESCE(SUM(oi.quantity), 0) as total_qty 
                                    FROM tire_orders o 
                                    LEFT JOIN tire_order_items oi ON o.order_id = oi.order_id 
                                    WHERE o.customer_id='$userId' 
                                    GROUP BY o.order_id 
                                    ORDER BY o.order_date DESC LIMIT 5");

$recentFeedback = mysqli_query($con, "SELECT f.* FROM tbl_customer_feedback f WHERE f.userId='$userId' ORDER BY f.created_at DESC LIMIT 5");

// Metrics
$resolutionRate = $totalUserComplaints > 0 ? round(($closedUserComplaints / $totalUserComplaints) * 100, 1) : 0;
$orderCompletionRate = $totalUserOrders > 0 ? round(($completeUserOrders / $totalUserOrders) * 100, 1) : 0;

// User Info
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false) {
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
}
$firstName = explode(' ', $userData['fullName'])[0];
$hasPendingOrders = $pendingUserOrders > 0;
$hasPIConfirmedOrders = $piConfirmedUserOrders > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - ATIRE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ===== SF UI Display — matching landing page font paths ===== */
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-ultralight-58646b19bf205.otf') format('opentype'); font-weight: 100; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-thin-58646e9b26e8b.otf') format('opentype');      font-weight: 200; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype');     font-weight: 400; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype');   font-weight: 600; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype');       font-weight: 700; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype');      font-weight: 800; }
        @font-face { font-family: 'SF UI Display'; src: url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype');      font-weight: 900; }

        /* ===== CSS Variables — exact match with landing page ===== */
        :root {
            --primary-orange:  #F28018;
            --dark-gray:       #333333;
            --light-gray:      #f0f0f0;
            --border-gray:     #D6D6D6;
            --bg-light:        #f9f9f9;
            --success:         #27ae60;
            --warning:         #f39c12;
            --error:           #e74c3c;
            --info:            #3498db;
            --text-gray:       #555555;
            --orange-light:    rgba(242, 128, 24, 0.1);
            --success-light:   rgba(39, 174, 96, 0.1);
            --warning-light:   rgba(241, 196, 15, 0.1);
            --error-light:     rgba(231, 76, 60, 0.1);
            --info-light:      rgba(52, 152, 219, 0.1);
            --white:           #ffffff;
            --shadow-soft:     0 4px 20px rgba(0,0,0,0.08);
            --shadow-hover:    0 8px 30px rgba(0,0,0,0.12);
            --shadow-active:   0 12px 40px rgba(242,128,24,0.3);
        }

        /* ===== Reset & Base ===== */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            overflow-x: hidden;
            font-weight: 400;
        }

        /* ===== Header ===== */
        .header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            box-shadow: var(--shadow-soft);
        }

        .navbar {
            max-width: 1600px;
            margin: 0 auto;
            padding: 1.2rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            text-decoration: none;
        }
        .brand-logo { max-width: 160px; height: auto; }
        .brand-tagline {
            font-size: 0.78rem;
            color: var(--primary-orange);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .user-info {
            text-align: right;
        }
        .user-info h4 {
            font-size: 0.97rem;
            font-weight: 700;
            color: var(--dark-gray);
            line-height: 1.2;
        }
        .user-info p {
            font-size: 0.78rem;
            color: var(--text-gray);
            font-weight: 400;
        }

        .user-avatar-link {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 50%;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background: var(--primary-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            letter-spacing: 0.5px;
        }
        .user-avatar-link:hover .user-avatar {
            transform: scale(1.1);
            box-shadow: 0 0 0 3px var(--primary-orange), var(--shadow-active);
        }

        /* ===== Page Container ===== */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2.5rem 5% 4rem;
        }

        /* ===== Page Header ===== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header-left h1 {
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 800;
            color: var(--dark-gray);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .page-header-left h1 span { color: var(--primary-orange); }
        .page-header-left p {
            font-size: 1rem;
            color: var(--text-gray);
            font-weight: 400;
            margin-top: 4px;
        }

        /* ===== Alert Banners ===== */
        .alert-banner {
            background: var(--primary-orange);
            border-radius: 20px;
            padding: 1.3rem 2rem;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1.4rem;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: var(--shadow-active);
            text-decoration: none;
        }
        .alert-banner:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 50px rgba(242,128,24,0.4);
        }
        .alert-banner.pi-confirmed {
            background: var(--success);
            box-shadow: 0 10px 35px rgba(39,174,96,0.3);
        }
        .alert-banner.pi-confirmed:hover {
            box-shadow: 0 16px 50px rgba(39,174,96,0.4);
        }
        .alert-icon {
            width: 54px;
            height: 54px;
            background: rgba(255,255,255,0.22);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            animation: pulse 2.2s ease-in-out infinite;
            flex-shrink: 0;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50%       { transform: scale(1.12); }
        }
        .alert-content { flex: 1; color: white; }
        .alert-title { font-size: 1.05rem; font-weight: 800; margin-bottom: 2px; letter-spacing: -0.2px; }
        .alert-message { font-size: 0.92rem; opacity: 0.94; font-weight: 400; }
        .alert-arrow { color: rgba(255,255,255,0.7); font-size: 1.2rem; flex-shrink: 0; }

        /* ===== Section Titles ===== */
        .section-title {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 2.5rem 0 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark-gray);
            letter-spacing: -0.02em;
        }
        .title-icon {
            width: 38px;
            height: 38px;
            background: var(--orange-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-orange);
            font-size: 1rem;
            flex-shrink: 0;
        }
        .title-line {
            flex: 1;
            height: 1.5px;
            background: linear-gradient(90deg, var(--border-gray) 0%, transparent 100%);
            margin-left: 8px;
        }

        /* ===== Stats Grid ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1rem;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 20px;
            padding: 1.8rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: var(--primary-orange);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        .stat-card:hover::after { transform: scaleX(1); }
        .stat-card:hover {
            border-color: var(--primary-orange);
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
        }

        .stat-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.2rem;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            background: var(--orange-light);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--primary-orange);
            transition: all 0.3s ease;
        }
        .stat-card:hover .stat-icon {
            background: var(--primary-orange);
            color: white;
            transform: scale(1.08) rotate(-5deg);
        }

        .stat-trend {
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .trend-up      { background: var(--success-light); color: var(--success); }
        .trend-down    { background: var(--error-light);   color: var(--error);   }
        .trend-neutral { background: var(--orange-light);  color: var(--primary-orange); }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--primary-orange);
            line-height: 1;
            margin-bottom: 4px;
            letter-spacing: -1px;
        }
        .stat-label {
            font-size: 0.97rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 2px;
        }
        .stat-description {
            font-size: 0.84rem;
            color: var(--text-gray);
            font-weight: 400;
        }

        /* ===== Quick Actions ===== */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1rem;
        }

        .quick-action-card {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 20px;
            padding: 1.8rem 1.2rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            position: relative;
            overflow: hidden;
        }
        .quick-action-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: var(--primary-orange);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .quick-action-card:hover::after { transform: scaleX(1); }
        .quick-action-card:hover {
            border-color: var(--primary-orange);
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .quick-action-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            background: var(--orange-light);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: var(--primary-orange);
            transition: all 0.3s ease;
        }
        .quick-action-card:hover .quick-action-icon {
            background: var(--primary-orange);
            color: white;
            transform: scale(1.1) rotate(5deg);
        }
        .quick-action-title {
            font-size: 0.97rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 4px;
        }
        .quick-action-subtitle {
            font-size: 0.82rem;
            color: var(--text-gray);
            font-weight: 400;
        }

        /* ===== Cards ===== */
        .card {
            background: var(--white);
            border: 1px solid var(--border-gray);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
        }
        .card:hover {
            border-color: rgba(242,128,24,0.35);
            box-shadow: var(--shadow-hover);
        }
        .card-header {
            padding: 1.4rem 2rem;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark-gray);
        }
        .card-title i { color: var(--primary-orange); }
        .card-body { padding: 1.8rem 2rem; }

        /* ===== Buttons ===== */
        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 0.97rem;
            font-weight: 700;
            font-family: 'SF UI Display', -apple-system, sans-serif;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            position: relative;
            overflow: hidden;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn:hover::before { left: 100%; }

        .btn-primary { background: var(--primary-orange); color: white; box-shadow: var(--shadow-soft); }
        .btn-primary:hover { background: #d4700f; transform: translateY(-2px); box-shadow: var(--shadow-active); }

        .btn-outline { background: transparent; color: var(--primary-orange); border: 2px solid var(--primary-orange); }
        .btn-outline:hover { background: var(--primary-orange); color: white; transform: translateY(-2px); box-shadow: var(--shadow-hover); }

        .btn-sm { padding: 0.5rem 1.2rem; font-size: 0.82rem; }

        /* ===== List Items ===== */
        .list-container { display: flex; flex-direction: column; gap: 0.9rem; }

        .list-item {
            background: var(--bg-light);
            border: 1px solid var(--border-gray);
            border-radius: 14px;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        .list-item:hover {
            background: var(--white);
            border-color: var(--primary-orange);
            transform: translateX(5px);
            box-shadow: var(--shadow-soft);
        }

        .item-avatar {
            width: 50px;
            height: 50px;
            background: var(--primary-orange);
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .item-details { flex: 1; min-width: 0; }
        .item-title {
            font-weight: 700;
            margin-bottom: 3px;
            color: var(--dark-gray);
            font-size: 0.97rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-meta {
            font-size: 0.84rem;
            color: var(--text-gray);
            line-height: 1.55;
            font-weight: 400;
        }
        .item-meta small { font-size: 0.78rem; }

        /* ===== Status Badges ===== */
        .status-badge {
            padding: 0.35rem 1rem;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .status-pending    { background: var(--warning-light); color: var(--warning); border: 1px solid rgba(243,156,18,0.25); }
        .status-cus-confirmed,
        .status-cus_confirmed { background: rgba(91,192,190,0.12); color: #5bc0be; border: 1px solid rgba(91,192,190,0.3); }
        .status-price-pending,
        .status-price_pending { background: rgba(243,156,18,0.12); color: var(--warning); border: 1px solid rgba(243,156,18,0.25); }
        .status-pi-confirmed  { background: var(--success-light); color: var(--success); border: 1px solid rgba(39,174,96,0.3); }
        .status-in-process,
        .status-in-progress   { background: var(--info-light); color: var(--info); border: 1px solid rgba(52,152,219,0.25); }
        .status-closed,
        .status-complete     { background: var(--success-light); color: var(--success); border: 1px solid rgba(39,174,96,0.3); }

        /* ===== Rating Stars ===== */
        .rating-stars { display: flex; gap: 3px; margin: 4px 0; }
        .star       { color: var(--primary-orange); font-size: 0.9rem; }
        .star.empty { color: var(--border-gray); }

        /* ===== Empty State ===== */
        .empty-state { text-align: center; padding: 3.5rem 2rem; }
        .empty-icon { font-size: 4rem; color: var(--border-gray); margin-bottom: 1.2rem; display: block; }
        .empty-state h3 { font-size: 1.2rem; font-weight: 700; color: var(--dark-gray); margin-bottom: 6px; }
        .empty-state p  { color: var(--text-gray); font-size: 0.92rem; }

        /* ===== Help Grid ===== */
        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
        }
        .help-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            font-size: 0.97rem;
            font-weight: 700;
            font-family: 'SF UI Display', sans-serif;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .help-btn-primary {
            background: var(--primary-orange);
            color: white;
            box-shadow: var(--shadow-soft);
        }
        .help-btn-primary:hover { background: #d4700f; transform: translateY(-3px); box-shadow: var(--shadow-active); }
        .help-btn-outline {
            background: transparent;
            border-color: var(--primary-orange);
            color: var(--primary-orange);
        }
        .help-btn-outline:hover { background: var(--primary-orange); color: white; transform: translateY(-3px); box-shadow: var(--shadow-hover); }

        /* ===== Scroll Reveal ===== */
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: slideUp 0.7s ease-out both; }

        /* ===== Responsive ===== */
        @media (max-width: 1024px) {
            .container { padding: 2rem 4%; }
            .navbar    { padding: 1rem 4%; }
        }
        @media (max-width: 768px) {
            .user-info     { display: none; }
            .quick-actions { grid-template-columns: repeat(3, 1fr); }
            .stats-grid    { grid-template-columns: repeat(2, 1fr); }
            .page-header-left h1 { font-size: 1.5rem; }
            .card-body { padding: 1.2rem 1.2rem; }
            .card-header { padding: 1.1rem 1.2rem; }
        }
        @media (max-width: 480px) {
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
            .stats-grid    { grid-template-columns: 1fr; }
            .list-item     { flex-wrap: wrap; }
            .alert-banner  { padding: 1rem 1.2rem; gap: 1rem; }
            .section-title { font-size: 1.15rem; }
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
                <div class="brand-tagline">CUSTOMER SERVICE</div>
            </div>
        </a>

        <div class="user-section">
            <div class="user-info">
                <h4><?php echo htmlspecialchars($userData['fullName']); ?></h4>
                <p><?php echo htmlspecialchars($userData['userEmail']); ?></p>
            </div>
            <a href="my-profile.php" class="user-avatar-link" title="View My Profile">
                <div class="user-avatar"><?php echo $initials; ?></div>
            </a>
        </div>
    </nav>
</header>

<!-- ===== Main ===== -->
<div class="container">

    <!-- Alert Banners -->
    <?php if ($hasPendingOrders): ?>
    <div class="alert-banner fade-in" onclick="window.location.href='view_order.php?status=pending'" role="button" tabindex="0">
        <div class="alert-icon"><i class="fas fa-bell"></i></div>
        <div class="alert-content">
            <div class="alert-title">⚡ Action Required!</div>
            <div class="alert-message">You have <?php echo $pendingUserOrders; ?> pending order<?php echo $pendingUserOrders > 1 ? 's' : ''; ?> waiting for confirmation.</div>
        </div>
        <div class="alert-arrow"><i class="fas fa-chevron-right"></i></div>
    </div>
    <?php endif; ?>

    <?php if ($hasPIConfirmedOrders): ?>
    <div class="alert-banner pi-confirmed fade-in" onclick="window.location.href='view_order.php?status=pi_confirm'" role="button" tabindex="0">
        <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
        <div class="alert-content">
            <div class="alert-title">✅ PI Confirmed Orders</div>
            <div class="alert-message">You have <?php echo $piConfirmedUserOrders; ?> order<?php echo $piConfirmedUserOrders > 1 ? 's' : ''; ?> with PI confirmed status — ready for processing.</div>
        </div>
        <div class="alert-arrow"><i class="fas fa-chevron-right"></i></div>
    </div>
    <?php endif; ?>

    <!-- Orders Overview -->
    <h2 class="section-title reveal">
        <span class="title-icon"><i class="fas fa-shopping-bag"></i></span>
        My Orders Overview
        <span class="title-line"></span>
    </h2>

    <div class="stats-grid reveal">
        <div class="stat-card" data-href="view_order.php">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-trend trend-neutral"><i class="fas fa-layer-group"></i> Total</div>
            </div>
            <div class="stat-value"><?php echo $totalUserOrders; ?></div>
            <div class="stat-label">Order Summery</div>
            <div class="stat-description">All orders you've placed</div>
        </div>

        <div class="stat-card" data-href="view_order.php?status=complete">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-trend trend-up"><i class="fas fa-check"></i> Complete</div>
            </div>
            <div class="stat-value"><?php echo $completeUserOrders; ?></div>
            <div class="stat-label">Deliverd Orders</div>
            <div class="stat-description">Complete Deliverd Order</div>
        </div>

        <div class="stat-card" data-href="view_order.php?status=Share_planning">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-cogs"></i></div>
                <div class="stat-trend trend-up"><i class="fas fa-spinner"></i> Active</div>
            </div>
            <div class="stat-value"><?php echo $inProgressUserOrders; ?></div>
            <div class="stat-label">In Production</div>
            <div class="stat-description">Currently being prepared</div>
        </div>

        <!-- ===== TO BE PRODUCTION CARD - MULTI STATUS ===== -->
        <div class="stat-card" onclick="goToProduction()">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-shipping-fast"></i></div>
                <div class="stat-trend trend-neutral"><i class="fas fa-truck"></i> Pending</div>
            </div>
            <div class="stat-value"><?php echo $toBeProductionOrders; ?></div>
            <div class="stat-label">In processing</div>
            <div class="stat-description">Orders pending to production</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="section-title reveal">
        <span class="title-icon"><i class="fas fa-bolt"></i></span>
        Quick Actions
        <span class="title-line"></span>
    </h2>

    <div class="quick-actions reveal">
        <a href="add_order.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="quick-action-title">Place Order</div>
            <div class="quick-action-subtitle">Order new tires</div>
        </a>
        <a href="view_order.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-box"></i></div>
            <div class="quick-action-title">My Orders</div>
            <div class="quick-action-subtitle">Track your orders</div>
        </a>
        <a href="register-complaint.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-plus-circle"></i></div>
            <div class="quick-action-title">File Claim</div>
            <div class="quick-action-subtitle">Submit a new issue</div>
        </a>
        <a href="complaint-history.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-list-alt"></i></div>
            <div class="quick-action-title">My Claims</div>
            <div class="quick-action-subtitle">View all claims</div>
        </a>
        <a href="submit-feedback.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-star"></i></div>
            <div class="quick-action-title">Give Feedback</div>
            <div class="quick-action-subtitle">Share your experience</div>
        </a>
    </div>

    <!-- Claims Overview -->
    <h2 class="section-title reveal">
        <span class="title-icon"><i class="fas fa-exclamation-triangle"></i></span>
        My Claims Overview
        <span class="title-line"></span>
    </h2>

    <div class="stats-grid reveal">
        <div class="stat-card" data-href="complaint-history.php">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                <div class="stat-trend trend-neutral"><i class="fas fa-chart-bar"></i> Total</div>
            </div>
            <div class="stat-value"><?php echo $totalUserComplaints; ?></div>
            <div class="stat-label">Total Claims</div>
            <div class="stat-description">All claims you've filed</div>
        </div>
        <div class="stat-card" data-href="complaint-history.php?status=pending">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-trend trend-down"><i class="fas fa-exclamation"></i> Pending</div>
            </div>
            <div class="stat-value"><?php echo $pendingUserComplaints; ?></div>
            <div class="stat-label">Pending Review</div>
            <div class="stat-description">Awaiting admin response</div>
        </div>
        <div class="stat-card" data-href="complaint-history.php?status=in+process">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-cogs"></i></div>
                <div class="stat-trend trend-up"><i class="fas fa-wrench"></i> Active</div>
            </div>
            <div class="stat-value"><?php echo $inProcessUserComplaints; ?></div>
            <div class="stat-label">In Process</div>
            <div class="stat-description">Currently being resolved</div>
        </div>
        <div class="stat-card" data-href="complaint-history.php?status=closed">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-trend trend-up"><i class="fas fa-check"></i> Done</div>
            </div>
            <div class="stat-value"><?php echo $closedUserComplaints; ?></div>
            <div class="stat-label">Closed</div>
            <div class="stat-description">Successfully resolved</div>
        </div>
    </div>

    <!-- Feedback Summary -->
    <?php if ($totalUserFeedback > 0): ?>
    <h2 class="section-title reveal">
        <span class="title-icon"><i class="fas fa-star"></i></span>
        My Feedback Summary
        <span class="title-line"></span>
    </h2>
    <div class="stats-grid reveal" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); max-width: 640px;">
        <div class="stat-card" data-href="my-feedback.php">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-comments"></i></div>
                <div class="stat-trend trend-neutral"><i class="fas fa-chart-bar"></i> Total</div>
            </div>
            <div class="stat-value"><?php echo $totalUserFeedback; ?></div>
            <div class="stat-label">Feedback Submitted</div>
            <div class="stat-description">Total feedback you've provided</div>
        </div>
        <div class="stat-card" data-href="my-feedback.php">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-trend trend-up"><i class="fas fa-trophy"></i> Rating</div>
            </div>
            <div class="stat-value"><?php echo $userAvgRating; ?> ★</div>
            <div class="stat-label">Average Rating Given</div>
            <div class="stat-description">Your average score across all feedback</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Activity Metrics -->
    <?php if ($totalUserComplaints > 0 || $totalUserOrders > 0): ?>
    <h2 class="section-title reveal">
        <span class="title-icon"><i class="fas fa-chart-line"></i></span>
        Your Activity Metrics
        <span class="title-line"></span>
    </h2>
    <div class="stats-grid reveal" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); max-width: 640px;">
        <?php if ($totalUserComplaints > 0): ?>
        <div class="stat-card" data-href="complaint-history.php?status=closed">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                <div class="stat-trend trend-up"><i class="fas fa-arrow-up"></i> Resolution</div>
            </div>
            <div class="stat-value"><?php echo $resolutionRate; ?>%</div>
            <div class="stat-label">Resolution Rate</div>
            <div class="stat-description">Your claims resolved</div>
        </div>
        <?php endif; ?>
        <?php if ($totalUserOrders > 0): ?>
        <div class="stat-card" data-href="view_order.php?status=complete">
            <div class="stat-header">
                <div class="stat-icon"><i class="fas fa-shipping-fast"></i></div>
                <div class="stat-trend trend-up"><i class="fas fa-check"></i> Completion</div>
            </div>
            <div class="stat-value"><?php echo $orderCompletionRate; ?>%</div>
            <div class="stat-label">Order Completion</div>
            <div class="stat-description">Your orders complete</div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Activity -->
    <h2 class="section-title reveal">
        <span class="title-icon"><i class="fas fa-history"></i></span>
        Recent Activity
        <span class="title-line"></span>
    </h2>

    <!-- Recent Orders -->
    <div class="card reveal">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-shopping-bag"></i> Recent Orders</h2>
            <a href="view_order.php" class="btn btn-primary">
                <i class="fas fa-external-link-alt"></i> View All
            </a>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($recentOrders) > 0): ?>
            <div class="list-container">
                <?php while ($order = mysqli_fetch_assoc($recentOrders)): ?>
                <div class="list-item">
                    <div class="item-avatar"><i class="fas fa-box"></i></div>
                    <div class="item-details">
                        <div class="item-title">Order #<?php echo htmlspecialchars($order['order_id']); ?></div>
                        <div class="item-meta">
                            <?php
                            $itemText = $order['total_items'] . ' item' . ($order['total_items'] > 1 ? 's' : '');
                            $qtyText  = ($order['total_qty'] ? $order['total_qty'] : $order['total_quantity']) . ' units';
                            echo htmlspecialchars($itemText . ' · ' . $qtyText);
                            ?>
                            <br><small><?php echo date('M j, Y', strtotime($order['order_date'])); ?></small>
                        </div>
                    </div>
                    <?php
                    $orderStatus = strtolower($order['status'] ?? 'pending');
                    if ($orderStatus == 'complete' || $orderStatus == 'delivered')                              $badgeClass = 'status-complete';
                    elseif ($orderStatus == 'price_pending' || $orderStatus == 'price pending')                 $badgeClass = 'status-price-pending';
                    elseif ($orderStatus == 'cus_confirmed' || $orderStatus == 'cus_confirm')                   $badgeClass = 'status-cus-confirmed';
                    elseif (strpos($orderStatus, 'confirmed') !== false)                                        $badgeClass = 'status-cus-confirmed';
                    elseif (in_array($orderStatus, ['in progress','processing','share_planning']) || strpos($orderStatus,'planning') !== false) $badgeClass = 'status-in-progress';
                    else                                                                                        $badgeClass = 'status-pending';

                    $displayStatus = $order['status'] ?? 'pending';
                    if ($displayStatus == 'Share_planning')  $displayStatus = 'In Planning';
                    elseif ($displayStatus == 'price_pending') $displayStatus = 'Price Pending';
                    elseif ($displayStatus == 'cus_confirmed') $displayStatus = 'Customer Confirmed';
                    elseif ($displayStatus == 'cus_confirm')   $displayStatus = 'Customer Confirmed';
                    else $displayStatus = ucfirst(str_replace('_', ' ', $displayStatus));
                    ?>
                    <div class="status-badge <?php echo $badgeClass; ?>"><?php echo $displayStatus; ?></div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart empty-icon"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders. Click "Place Order" to get started.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Claims -->
    <div class="card reveal">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-exclamation-circle"></i> Recent Claims</h2>
            <a href="complaint-history.php" class="btn btn-primary">
                <i class="fas fa-external-link-alt"></i> View All
            </a>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($recentComplaints) > 0): ?>
            <div class="list-container">
                <?php while ($complaint = mysqli_fetch_array($recentComplaints)): ?>
                <a href="complaint-details.php?cid=<?php echo $complaint['id']; ?>" class="list-item">
                    <div class="item-avatar"><i class="fas fa-tire"></i></div>
                    <div class="item-details">
                        <div class="item-title"><?php echo htmlspecialchars($complaint['nature_complaint'] ?? 'Tire Claim'); ?></div>
                        <div class="item-meta">
                            <?php echo htmlspecialchars($complaint['complaintNumber']); ?> · <?php echo htmlspecialchars($complaint['tire_size']); ?>
                            <br><small><?php echo date('M j, Y — h:i A', strtotime($complaint['created_at'])); ?></small>
                        </div>
                    </div>
                    <?php
                    $statusLower = strtolower($complaint['status'] ?? 'pending');
                    if ($statusLower == 'pending' || $statusLower == '')           $cBadge = 'status-pending';
                    elseif ($statusLower == 'in process')                           $cBadge = 'status-in-process';
                    elseif ($statusLower == 'closed' || $statusLower == 'resolved') $cBadge = 'status-closed';
                    else                                                             $cBadge = 'status-in-process';
                    ?>
                    <div class="status-badge <?php echo $cBadge; ?>"><?php echo ucfirst($complaint['status'] ?? 'Pending'); ?></div>
                </a>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox empty-icon"></i>
                <h3>No Claims Yet</h3>
                <p>You haven't filed any claims. Click "File Claim" to get started.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Feedback -->
    <div class="card reveal">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-star"></i> My Recent Feedback</h2>
            <a href="my-feedback.php" class="btn btn-primary">
                <i class="fas fa-external-link-alt"></i> View All
            </a>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($recentFeedback) > 0): ?>
            <div class="list-container">
                <?php while ($feedback = mysqli_fetch_assoc($recentFeedback)): ?>
                <div class="list-item">
                    <div class="item-avatar"><i class="fas fa-comment-dots"></i></div>
                    <div class="item-details">
                        <div class="item-title">Your Feedback</div>
                        <div class="item-meta">
                            <div class="rating-stars">
                                <?php
                                $rating = isset($feedback['rating']) ? intval($feedback['rating']) : 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating
                                        ? '<i class="fas fa-star star"></i>'
                                        : '<i class="fas fa-star star empty"></i>';
                                }
                                ?>
                            </div>
                            <?php
                            $feedbackText = isset($feedback['feedback']) ? htmlspecialchars($feedback['feedback']) : 'No feedback text';
                            echo substr($feedbackText, 0, 60) . (strlen($feedbackText) > 60 ? '…' : '');
                            ?>
                            <br><small><?php echo isset($feedback['created_at']) ? date('M j, Y', strtotime($feedback['created_at'])) : 'Recent'; ?></small>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comment-slash empty-icon"></i>
                <h3>No Feedback Yet</h3>
                <p>You haven't submitted any feedback. Share your experience with us!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Need Help -->
    <div class="card reveal" style="margin-bottom:3rem;">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-question-circle"></i> Need Help?</h2>
        </div>
        <div class="card-body">
            <div class="help-grid">
                <a href="contact-support.php" class="help-btn help-btn-primary">
                    <i class="fas fa-headset"></i> Contact Support
                </a>
                <a href="faq.php" class="help-btn help-btn-outline">
                    <i class="fas fa-book-open"></i> View FAQ
                </a>
                <a href="track-complaint.php" class="help-btn help-btn-outline">
                    <i class="fas fa-search"></i> Track Status
                </a>
            </div>
        </div>
    </div>

</div><!-- /container -->

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Animated counters ── */
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach((el, i) => {
        const text       = el.textContent.trim();
        const hasPercent = text.includes('%');
        const hasStar    = text.includes('★');
        const match      = text.match(/[\d.]+/);
        if (!match) return;

        const final   = parseFloat(match[0]);
        const isFloat = text.includes('.');
        let   current = 0;
        const steps   = 55;
        const inc     = final / steps;

        const timer = setInterval(() => {
            current += inc;
            if (current >= final) { current = final; clearInterval(timer); }
            const display = isFloat ? current.toFixed(1) : Math.floor(current);
            el.textContent = display + (hasPercent ? '%' : '') + (hasStar ? ' ★' : '');
        }, 18 + i * 4);
    });

    /* ── Scroll reveal ── */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    /* ── Clickable stat cards via data-href ── */
    document.querySelectorAll('.stat-card[data-href]').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            location.href = card.getAttribute('data-href');
        });
    });

    /* ── Header shadow on scroll ── */
    window.addEventListener('scroll', () => {
        document.querySelector('.header').style.boxShadow =
            window.scrollY > 10 ? '0 4px 20px rgba(0,0,0,0.08)' : 'none';
    });
});

/**
 * Navigate to "To Be Production" orders
 * Shows orders with EITHER price_pending OR cus_confirmed status
 */
function goToProduction() {
    window.location.href = 'view_order.php?filter=to_production';
}
</script>
</body>
</html>
<?php mysqli_close($con); ?>