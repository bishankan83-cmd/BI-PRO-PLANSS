<?php
session_start();
include('include/config.php');

// Check if user is logged in
if (strlen($_SESSION['id']) == 0) {
    header('location:index.php');
    exit;
}

// Handle language selection
$languages = ['en', 'es', 'fr', 'de', 'it', 'pt'];
$defaultLang = 'en';
$selectedLang = isset($_SESSION['lang']) && in_array($_SESSION['lang'], $languages) ? $_SESSION['lang'] : $defaultLang;

if (isset($_POST['lang']) && in_array($_POST['lang'], $languages)) {
    $_SESSION['lang'] = $_POST['lang'];
    $selectedLang = $_POST['lang'];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Load translations
$translationFile = "languages/{$selectedLang}.json";
if (!file_exists($translationFile)) {
    $translationFile = "languages/{$defaultLang}.json";
}
$translations = json_decode(file_get_contents($translationFile), true);

// Fetch user data
$userId = $_SESSION['id'];
$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
$userData = mysqli_fetch_assoc($queryUser);
if (!$userData) {
    header('location:index.php');
    exit;
}

// Fetch dashboard statistics - COMPLAINTS
$queryTotal = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId = '$userId'");
$totalComplaints = mysqli_num_rows($queryTotal);

$queryPending = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId = '$userId' AND (status IS NULL OR status = '')");
$pendingComplaints = mysqli_num_rows($queryPending);

$queryInProcess = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId = '$userId' AND status = 'in process'");
$inprocessComplaints = mysqli_num_rows($queryInProcess);

$queryClosed = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE userId = '$userId' AND status = 'closed'");
$closedComplaints = mysqli_num_rows($queryClosed);

// Fetch dashboard statistics - ORDERS
$queryTotalOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id = '$userId'");
$totalOrders = mysqli_num_rows($queryTotalOrders);

$queryPendingOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id = '$userId' AND (status IS NULL OR status = '' OR status = 'pending')");
$pendingOrders = mysqli_num_rows($queryPendingOrders);

$queryInProgressOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id = '$userId' AND status = 'in progress'");
$inProgressOrders = mysqli_num_rows($queryInProgressOrders);

$queryCompletedOrders = mysqli_query($con, "SELECT order_id FROM tire_orders WHERE customer_id = '$userId' AND status = 'completed'");
$completedOrders = mysqli_num_rows($queryCompletedOrders);

// Fetch feedback statistics
$queryTotalFeedback = mysqli_query($con, "SELECT id FROM tbl_customer_feedback WHERE userId = '$userId'");
$totalFeedback = mysqli_num_rows($queryTotalFeedback);

$queryAvgRating = mysqli_query($con, "SELECT AVG(rating) as avg_rating FROM tbl_customer_feedback WHERE userId = '$userId'");
$avgRatingData = mysqli_fetch_assoc($queryAvgRating);
$avgRating = $avgRatingData ? round($avgRatingData['avg_rating'], 1) : 0;

// Fetch recent complaints
$queryRecent = mysqli_query($con, "SELECT id, nature_complaint, created_at, status FROM tbl_tire_complaints WHERE userId = '$userId' ORDER BY created_at DESC LIMIT 5");

// Fetch recent orders
$queryRecentOrders = mysqli_query($con, "SELECT * FROM tire_orders WHERE customer_id = '$userId' ORDER BY order_id DESC LIMIT 5");

// Fetch recent feedback
$queryRecentFeedback = mysqli_query($con, "SELECT * FROM tbl_customer_feedback WHERE userId = '$userId' ORDER BY created_at DESC LIMIT 5");

// Calculate initials
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false) {
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
}

$resolutionRate = $totalComplaints > 0 ? round(($closedComplaints / $totalComplaints) * 100) : 0;
$orderCompletionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100) : 0;

function getTranslation($key, $placeholders = []) {
    global $translations;
    $text = isset($translations[$key]) ? $translations[$key] : $key;
    foreach ($placeholders as $placeholder => $value) {
        $text = str_replace('{{' . $placeholder . '}}', $value, $text);
    }
    return $text;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $selectedLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Tire Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #1a1d29;
            --medium-gray: #2d3142;
            --light-gray: #f5f7fa;
            --border-color: #e8ecf1;
            --text-primary: #1a1d29;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --info: #3b82f6;
            --purple: #8b5cf6;
            --white: #ffffff;
            
            --gradient-orange: linear-gradient(135deg, #F28018 0%, #ff9a44 100%);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --shadow-xl: 0 16px 32px rgba(0, 0, 0, 0.16);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fafbfc;
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Header Styles */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .menu-btn {
            display: none;
            background: transparent;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 1.5rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-img {
            height: 36px;
            width: auto;
        }

        .brand-text {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-orange);
            letter-spacing: -0.02em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 300px;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            background: var(--light-gray);
            font-size: 0.875rem;
            transition: all 0.2s;
            outline: none;
        }

        .search-input:focus {
            background: var(--white);
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .notification-btn {
            position: relative;
            background: transparent;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 1.25rem;
            transition: color 0.2s;
        }

        .notification-btn:hover {
            color: var(--primary-orange);
        }

        .notification-badge {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            width: 8px;
            height: 8px;
            background: var(--error);
            border-radius: 50%;
            border: 2px solid var(--white);
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .user-btn:hover {
            background: var(--light-gray);
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--gradient-orange);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 700;
            font-size: 0.875rem;
        }

        .user-details h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            text-align: left;
        }

        .user-details span {
            font-size: 0.75rem;
            color: var(--text-secondary);
            display: block;
            text-align: left;
        }

        .language-selector {
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            background: var(--white);
            outline: none;
            transition: all 0.2s;
        }

        .language-selector:hover,
        .language-selector:focus {
            border-color: var(--primary-orange);
        }

        /* Container & Sidebar */
        .container {
            display: flex;
            min-height: calc(100vh - 73px);
        }

        .sidebar {
            width: 260px;
            background: var(--white);
            border-right: 1px solid var(--border-color);
            padding: 1.5rem 0;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 2rem;
            padding: 0 1rem;
        }

        .nav-title {
            font-size: 0.625rem;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.75rem;
            padding-left: 0.75rem;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .nav-item a i {
            font-size: 1rem;
            width: 1.25rem;
            text-align: center;
        }

        .nav-item a:hover {
            background: var(--light-gray);
            color: var(--text-primary);
        }

        .nav-item a.active {
            background: linear-gradient(135deg, rgba(242, 128, 24, 0.1) 0%, rgba(242, 128, 24, 0.05) 100%);
            color: var(--primary-orange);
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .header-actions-right {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--gradient-orange);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(242, 128, 24, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(242, 128, 24, 0.4);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--light-gray);
            border-color: var(--text-secondary);
        }

        /* Section Styles */
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            margin-top: 2.5rem;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .section-title i {
            color: var(--primary-orange);
            font-size: 1.125rem;
        }

        /* Stats Grid */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card.primary .stat-icon,
        .stat-card .stat-icon {
            background: linear-gradient(135deg, rgba(242, 128, 24, 0.1) 0%, rgba(242, 128, 24, 0.05) 100%);
            color: var(--primary-orange);
        }

        .stat-card.success .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--success);
        }

        .stat-card.warning .stat-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            color: var(--warning);
        }

        .stat-card.danger .stat-icon {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            color: var(--error);
        }

        .stat-card.info .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            color: var(--info);
        }

        .stat-card.purple .stat-icon {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
            color: var(--purple);
        }

        .stat-card.gradient-card .stat-icon {
            background: var(--gradient-orange);
            color: var(--white);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .trend-up {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .trend-down {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .trend-neutral {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .stat-description {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--light-gray);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .card-title i {
            color: var(--primary-orange);
            font-size: 0.875rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* List Items */
        .list-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .list-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--light-gray);
            border-radius: 10px;
            transition: all 0.2s;
        }

        .list-item:hover {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            transform: translateX(4px);
        }

        .item-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(242, 128, 24, 0.1) 0%, rgba(242, 128, 24, 0.05) 100%);
            color: var(--primary-orange);
        }

        .item-details {
            flex: 1;
            min-width: 0;
        }

        .item-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .item-meta {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-in-process,
        .status-in-progress {
            background: rgba(242, 128, 24, 0.1);
            color: var(--primary-orange);
        }

        .status-closed,
        .status-completed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        /* Rating Stars */
        .rating-stars {
            display: flex;
            gap: 0.125rem;
            margin: 0.25rem 0;
        }

        .star {
            color: #fbbf24;
            font-size: 0.875rem;
        }

        .star.empty {
            color: #e5e7eb;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.2;
            color: var(--text-light);
        }

        .empty-state h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 80;
        }

        .overlay.show {
            display: block;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .search-input {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .menu-btn {
                display: block;
            }

            .sidebar {
                position: fixed;
                top: 73px;
                left: -260px;
                height: calc(100vh - 73px);
                z-index: 90;
                transition: left 0.3s;
                box-shadow: var(--shadow-xl);
            }

            .sidebar.show {
                left: 0;
            }

            .main-content {
                padding: 1rem;
            }

            .search-box {
                display: none;
            }

            .user-details {
                display: none;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <button class="menu-btn" id="menuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo-container">
                <img src="atire.png" alt="Logo" class="logo-img">
                <div class="brand-text">Customer Service</div>
            </div>
        </div>

        <div class="header-actions">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search orders, complaints & feedback...">
            </div>
            
            <button class="notification-btn">
                <i class="fas fa-bell"></i>
                <?php if ($pendingComplaints > 0 || $pendingOrders > 0): ?>
                <span class="notification-badge"></span>
                <?php endif; ?>
            </button>

            <div class="user-menu">
                <button class="user-btn" id="userBtn">
                    <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($userData['fullName']); ?></h4>
                        <span><?php echo htmlspecialchars($userData['userEmail']); ?></span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <form method="post" style="display: inline;">
                <select name="lang" class="language-selector" onchange="this.form.submit()">
                    <option value="en" <?php echo $selectedLang == 'en' ? 'selected' : ''; ?>>🇬🇧 EN</option>
                    <option value="es" <?php echo $selectedLang == 'es' ? 'selected' : ''; ?>>🇪🇸 ES</option>
                    <option value="fr" <?php echo $selectedLang == 'fr' ? 'selected' : ''; ?>>🇫🇷 FR</option>
                    <option value="de" <?php echo $selectedLang == 'de' ? 'selected' : ''; ?>>🇩🇪 DE</option>
                    <option value="it" <?php echo $selectedLang == 'it' ? 'selected' : ''; ?>>🇮🇹 IT</option>
                    <option value="pt" <?php echo $selectedLang == 'pt' ? 'selected' : ''; ?>>🇵🇹 PT</option>
                </select>
            </form>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar"><nav class="nav-section">
                <h3 class="nav-title">Dashboard</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard.php" class="active">
                            <i class="fas fa-home"></i>
                            Overview
                        </a>
                    </li>
                </ul>
            </nav>

            <nav class="nav-section">
                <h3 class="nav-title">Orders</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="add_order.php">
                            <i class="fas fa-shopping-cart"></i>
                            New Order
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="view_order.php">
                            <i class="fas fa-box"></i>
                            My Orders
                        </a>
                    </li>
                </ul>
            </nav>

            <nav class="nav-section">
                <h3 class="nav-title">Complaints</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="register-complaint.php">
                            <i class="fas fa-plus-circle"></i>
                            New Complaint
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="complaint-history.php">
                            <i class="fas fa-list"></i>
                            My Complaints
                        </a>
                    </li>
                </ul>
            </nav>

            <nav class="nav-section">
                <h3 class="nav-title">Feedback</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="submit-feedback.php">
                            <i class="fas fa-star"></i>
                            Submit Feedback
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="feedback-history.php">
                            <i class="fas fa-comments"></i>
                            My Feedback
                        </a>
                    </li>
                </ul>
            </nav>

            <nav class="nav-section">
                <h3 class="nav-title">Account</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="setting.php">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="support.php">
                            <i class="fas fa-headset"></i>
                            Support
                        </a>
                    </li>
                </ul>
            </nav>

            <nav class="nav-section">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="logout.php" style="color: var(--error);">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Welcome back, <?php echo htmlspecialchars(explode(' ', $userData['fullName'])[0]); ?>! 👋</h1>
                    <p class="page-subtitle">Manage your orders, track complaints, and share your valuable feedback</p>
                </div>
                <div class="header-actions-right">
                    <a href="add_order.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i>
                        New Order
                    </a>
                    <a href="register-complaint.php" class="btn btn-secondary">
                        <i class="fas fa-plus"></i>
                        New Complaint
                    </a>
                </div>
            </div>

            <!-- Orders Stats Section -->
            <h2 class="section-title">
                <i class="fas fa-shopping-bag"></i>
                Order Statistics
            </h2>
            <div class="stats-container">
                <div class="stat-card info">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-trend trend-neutral">
                            <i class="fas fa-chart-line"></i>
                            Total
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $totalOrders; ?></div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-description">All orders placed</div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="stat-trend trend-down">
                            <i class="fas fa-clock"></i>
                            Waiting
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $pendingOrders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-description">Awaiting processing</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            Active
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $inProgressOrders; ?></div>
                    <div class="stat-label">In Progress</div>
                    <div class="stat-description">Being processed</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-check"></i>
                            Done
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $completedOrders; ?></div>
                    <div class="stat-label">Completed Orders</div>
                    <div class="stat-description">Successfully delivered</div>
                </div>
            </div>

            <!-- Complaints Stats Section -->
            <h2 class="section-title">
                <i class="fas fa-exclamation-triangle"></i>
                Complaint Statistics
            </h2>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-trend trend-neutral">
                            <i class="fas fa-chart-line"></i>
                            Total
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $totalComplaints; ?></div>
                    <div class="stat-label">Total Complaints</div>
                    <div class="stat-description">All complaints submitted</div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-trend trend-down">
                            <i class="fas fa-exclamation-triangle"></i>
                            Pending
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $pendingComplaints; ?></div>
                    <div class="stat-label">Pending Review</div>
                    <div class="stat-description">Awaiting response</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            Active
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $inprocessComplaints; ?></div>
                    <div class="stat-label">In Progress</div>
                    <div class="stat-description">Being resolved</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-check"></i>
                            Resolved
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $closedComplaints; ?></div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-description">Successfully resolved</div>
                </div>
            </div>

            <!-- Feedback Stats Section -->
            <h2 class="section-title">
                <i class="fas fa-star"></i>
                Feedback Overview
            </h2>
            <div class="stats-container" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); max-width: 800px;">
                <div class="stat-card purple">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-trend trend-neutral">
                            <i class="fas fa-chart-bar"></i>
                            Feedback
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $totalFeedback; ?></div>
                    <div class="stat-label">Total Feedback</div>
                    <div class="stat-description">Your valuable feedback</div>
                </div>

                <div class="stat-card gradient-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-trophy"></i>
                            Rating
                        </div>
                    </div>
                    <div class="stat-value">
                        <?php echo $avgRating; ?> <span style="font-size: 1.75rem;">★</span>
                    </div>
                    <div class="stat-label">Average Rating</div>
                    <div class="stat-description">Your satisfaction score</div>
                </div>
            </div>

            <!-- Recent Activity Grid -->
            <h2 class="section-title">
                <i class="fas fa-history"></i>
                Recent Activity
            </h2>
            <div class="content-grid">
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-shopping-bag"></i>
                            Recent Orders
                        </h2>
                        <a href="view_order.php" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.625rem 1.125rem;">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="list-container">
                            <?php if (mysqli_num_rows($queryRecentOrders) > 0): ?>
                                <?php while ($order = mysqli_fetch_assoc($queryRecentOrders)): ?>
                                    <div class="list-item">
                                        <div class="item-avatar" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%); color: var(--info);">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div class="item-details">
                                            <div class="item-title">
                                                Order #<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?>
                                            </div>
                                            <div class="item-meta">
                                                <?php echo isset($order['product_name']) ? htmlspecialchars(substr($order['product_name'], 0, 40)) : 'Order Details'; ?>
                                            </div>
                                        </div>
                                        <div class="status-badge 
                                            <?php 
                                            $orderStatus = strtolower($order['status'] ?? 'pending');
                                            if ($orderStatus == 'completed') {
                                                echo 'status-completed';
                                            } elseif ($orderStatus == 'in progress') {
                                                echo 'status-in-progress';
                                            } else {
                                                echo 'status-pending';
                                            }
                                            ?>">
                                            <?php 
                                            echo htmlspecialchars(ucwords(str_replace('-', ' ', $order['status'] ?? 'Pending')));
                                            ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart empty-icon"></i>
                                    <h3>No Orders Yet</h3>
                                    <p>You haven't placed any orders</p>
                                    <a href="add_order.php" class="btn btn-primary" style="margin-top: 1.25rem;">
                                        <i class="fas fa-plus"></i>
                                        Place Your First Order
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Complaints -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-exclamation-circle"></i>
                            Recent Complaints
                        </h2>
                        <a href="complaint-history.php" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.625rem 1.125rem;">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="list-container">
                            <?php if (mysqli_num_rows($queryRecent) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($queryRecent)): ?>
                                    <div class="list-item">
                                        <div class="item-avatar">
                                            <i class="fas fa-tire"></i>
                                        </div>
                                        <div class="item-details">
                                            <div class="item-title">
                                                Complaint #<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?>
                                            </div>
                                            <div class="item-meta">
                                                <?php echo htmlspecialchars(substr($row['nature_complaint'], 0, 40)) . (strlen($row['nature_complaint']) > 40 ? '...' : ''); ?>
                                                <br>
                                                <small><?php echo date('M j, Y', strtotime($row['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="status-badge 
                                            <?php 
                                            if ($row['status'] == 'closed') {
                                                echo 'status-closed';
                                            } elseif ($row['status'] == 'in process') {
                                                echo 'status-in-process';
                                            } else {
                                                echo 'status-pending';
                                            }
                                            ?>">
                                            <?php 
                                            echo $row['status'] ? htmlspecialchars(ucwords(str_replace('-', ' ', $row['status']))) : 'Pending';
                                            ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox empty-icon"></i>
                                    <h3>No Complaints Yet</h3>
                                    <p>You haven't submitted any complaints</p>
                                    <a href="register-complaint.php" class="btn btn-primary" style="margin-top: 1.25rem;">
                                        <i class="fas fa-plus"></i>
                                        File Your First Complaint
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Feedback -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-star"></i>
                            Recent Feedback
                        </h2>
                        <a href="feedback-history.php" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.625rem 1.125rem;">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="list-container">
                            <?php if (mysqli_num_rows($queryRecentFeedback) > 0): ?>
                                <?php while ($feedback = mysqli_fetch_assoc($queryRecentFeedback)): ?>
                                    <div class="list-item">
                                        <div class="item-avatar" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); color: var(--purple);">
                                            <i class="fas fa-comment-dots"></i>
                                        </div>
                                        <div class="item-details">
                                            <div class="item-title">
                                                Feedback #<?php echo str_pad($feedback['id'], 4, '0', STR_PAD_LEFT); ?>
                                            </div>
                                            <div class="item-meta">
                                                <div class="rating-stars">
                                                    <?php 
                                                    $rating = isset($feedback['rating']) ? intval($feedback['rating']) : 0;
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<i class="fas fa-star star"></i>';
                                                        } else {
                                                            echo '<i class="fas fa-star star empty"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php 
                                                $feedbackText = isset($feedback['feedback']) ? htmlspecialchars($feedback['feedback']) : 'No feedback text';
                                                echo substr($feedbackText, 0, 50) . (strlen($feedbackText) > 50 ? '...' : ''); 
                                                ?>
                                                <br>
                                                <small><?php echo date('M j, Y', strtotime($feedback['created_at'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-comment-slash empty-icon"></i>
                                    <h3>No Feedback Yet</h3>
                                    <p>Share your experience with us</p>
                                    <a href="submit-feedback.php" class="btn btn-primary" style="margin-top: 1.25rem;">
                                        <i class="fas fa-plus"></i>
                                        Submit Your First Feedback
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Performance Metrics
            </h2>
            <div class="stats-container" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); max-width: 800px;">
                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            Success
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $resolutionRate; ?>%</div>
                    <div class="stat-label">Resolution Rate</div>
                    <div class="stat-description">Complaints resolved successfully</div>
                </div>

                <div class="stat-card info">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-check"></i>
                            Complete
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $orderCompletionRate; ?>%</div>
                    <div class="stat-label">Order Completion</div>
                    <div class="stat-description">Orders successfully delivered</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile Menu Toggle
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                console.log('Searching for:', searchTerm);
            });
        }

        // Animate stats on load
        document.addEventListener('DOMContentLoaded', () => {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach((stat, index) => {
                const text = stat.textContent;
                const hasPercent = text.includes('%');
                const hasStar = text.includes('★');
                const numMatch = text.match(/[\d.]+/);
                
                if (numMatch) {
                    const finalValue = parseFloat(numMatch[0]);
                    let currentValue = 0;
                    const increment = finalValue / 50;
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= finalValue) {
                            let displayValue = hasPercent || !hasStar ? Math.round(finalValue) : finalValue.toFixed(1);
                            stat.textContent = displayValue + (hasPercent ? '%' : '') + (hasStar ? ' ★' : '');
                            clearInterval(timer);
                        } else {
                            let displayValue = hasPercent || !hasStar ? Math.floor(currentValue) : currentValue.toFixed(1);
                            stat.textContent = displayValue + (hasPercent ? '%' : '') + (hasStar ? ' ★' : '');
                        }
                    }, 20 + (index * 5));
                }
            });
        });

        // Notification bell
        const notificationBtn = document.querySelector('.notification-btn');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                const pendingCount = <?php echo $pendingComplaints + $pendingOrders; ?>;
                if (pendingCount > 0) {
                    alert('You have ' + pendingCount + ' pending items');
                } else {
                    alert('No pending notifications');
                }
            });
        }

        // User menu (placeholder)
        const userBtn = document.getElementById('userBtn');
        if (userBtn) {
            userBtn.addEventListener('click', () => {
                console.log('User menu clicked');
            });
        }
    </script>
</body>
</html>