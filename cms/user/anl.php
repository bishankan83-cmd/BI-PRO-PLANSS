```php
<?php
session_start();
include('include/config.php');

// Check if user is logged in
if (empty($_SESSION['id'])) {
    header('location:index.php');
    exit();
}

$userId = $_SESSION['id'];

// Fetch user data
$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
if (!$queryUser) {
    die("User query failed: " . mysqli_error($con));
}
$userData = mysqli_fetch_assoc($queryUser);
if (!$userData) {
    header('location:index.php');
    exit;
}

// Calculate initials for avatar
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false) {
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
}

// Fetch analytics data
// 1. Total Orders
$stmtTotalOrders = $con->prepare("SELECT COUNT(*) as total_orders FROM tire_orders WHERE customer_id = ?");
if (!$stmtTotalOrders) {
    die("Total orders prepare failed: " . $con->error);
}
$stmtTotalOrders->bind_param("i", $userId);
$stmtTotalOrders->execute();
$totalOrdersResult = $stmtTotalOrders->get_result();
$totalOrders = $totalOrdersResult->fetch_assoc()['total_orders'] ?? 0;
$stmtTotalOrders->close();

// 2. Orders by Status
$stmtStatus = $con->prepare("
    SELECT status, COUNT(*) as count 
    FROM tire_orders 
    WHERE customer_id = ? 
    GROUP BY status
");
if (!$stmtStatus) {
    die("Status prepare failed: " . $con->error);
}
$stmtStatus->bind_param("i", $userId);
$stmtStatus->execute();
$statusResult = $stmtStatus->get_result();
$ordersByStatus = [];
while ($row = $statusResult->fetch_assoc()) {
    $ordersByStatus[$row['status']] = $row['count'];
}
$stmtStatus->close();

// 3. Total Order Value
$stmtTotalValue = $con->prepare("
    SELECT SUM(ti.quantity * ti.unit_price) as total_value
    FROM tire_orders tor
    JOIN tire_order_items ti ON ti.order_id = tor.order_id
    WHERE tor.customer_id = ?
");
if (!$stmtTotalValue) {
    die("Total value prepare failed: " . $con->error);
}
$stmtTotalValue->bind_param("i", $userId);
$stmtTotalValue->execute();
$totalValueResult = $stmtTotalValue->get_result();
$totalOrderValue = $totalValueResult->fetch_assoc()['total_value'] ?? 0;
$stmtTotalValue->close();

// 4. Top 5 Item Codes by Quantity
$stmtTopItems = $con->prepare("
    SELECT ti.icode, SUM(ti.quantity) as total_quantity
    FROM tire_order_items ti
    JOIN tire_orders tor ON ti.order_id = tor.order_id
    WHERE tor.customer_id = ?
    GROUP BY ti.icode
    ORDER BY total_quantity DESC
    LIMIT 5
");
if (!$stmtTopItems) {
    die("Top items prepare failed: " . $con->error);
}
$stmtTopItems->bind_param("i", $userId);
$stmtTopItems->execute();
$topItemsResult = $stmtTopItems->get_result();
$topItems = [];
while ($row = $topItemsResult->fetch_assoc()) {
    $topItems[] = $row;
}
$stmtTopItems->close();

// 5. Orders by Month (for line chart)
$stmtOrdersByMonth = $con->prepare("
    SELECT DATE_FORMAT(order_date, '%Y-%m') as month, COUNT(*) as order_count
    FROM tire_orders
    WHERE customer_id = ?
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month
");
if (!$stmtOrdersByMonth) {
    die("Orders by month prepare failed: " . $con->error);
}
$stmtOrdersByMonth->bind_param("i", $userId);
$stmtOrdersByMonth->execute();
$ordersByMonthResult = $stmtOrdersByMonth->get_result();
$ordersByMonth = [];
$months = [];
while ($row = $ordersByMonthResult->fetch_assoc()) {
    $months[] = $row['month'];
    $ordersByMonth[] = $row['order_count'];
}
$stmtOrdersByMonth->close();

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service Portal - Analytics</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --medium-gray: #343a40;
            --black: #000000;
            --red: #FF0000;
            --red-accent: #ff4757;
            --border-gray: #e0e0e0;
            --light-border: #CCCCCC;
            --bg-light: #f8fafc;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --gradient-4: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --ring-orange: 0 0 0 3px rgba(242, 128, 24, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-btn {
            display: none;
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 0.5rem;
            color: var(--text-gray);
            transition: all 0.2s;
        }

        .menu-btn:hover {
            background: var(--orange-light);
            color: var(--primary-orange);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-img {
            height: 40px;
            width: auto;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 300px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border-gray);
            border-radius: 2rem;
            background: var(--white);
            font-size: 0.9rem;
            transition: all 0.3s;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            color: var(--text-gray);
            pointer-events: none;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            padding: 0.75rem;
            cursor: pointer;
            border-radius: 0.75rem;
            color: var(--text-gray);
            transition: all 0.2s;
        }

        .notification-btn:hover {
            background: var(--orange-light);
            color: var(--primary-orange);
        }

        .notification-badge {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            width: 0.5rem;
            height: 0.5rem;
            background: var(--error);
            border-radius: 50%;
        }

        .user-menu {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 2rem;
            transition: all 0.2s;
        }

        .user-btn:hover {
            background: var(--orange-light);
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--gradient-1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-details h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .user-details span {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 280px;
            background: var(--white);
            border-right: 1px solid var(--border-gray);
            padding: 2rem 0;
            overflow-y: auto;
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
        }

        .nav-section {
            margin-bottom: 2rem;
            padding: 0 1.5rem;
        }

        .nav-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
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
            padding: 0.75rem 1rem;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }

        .nav-item a:hover,
        .nav-item a.active {
            background: var(--orange-light);
            color: var(--primary-orange);
            transform: translateX(0.25rem);
        }

        .nav-item a.active::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 1.5rem;
            background: var(--primary-orange);
            border-radius: 2px;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow: hidden;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 1rem;
        }

        .header-actions-right {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-secondary:hover {
            background: var(--bg-light);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-orange);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-gray);
            text-transform: uppercase;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background: var(--bg-light);
            color: var(--text-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border-gray);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody tr {
            transition: all 0.2s;
        }

        .table tbody tr:hover {
            background: var(--orange-light);
            transform: translateX(0.25rem);
        }

        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-gray);
            color: var(--dark-gray);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-gray);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .chart-container {
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .menu-btn {
                display: block;
            }

            .sidebar {
                position: fixed;
                top: 80px;
                left: 0;
                z-index: 40;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
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

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .table thead th {
                font-size: 0.75rem;
                padding: 0.75rem;
            }

            .table tbody td {
                padding: 0.75rem;
            }

            .chart-container {
                max-width: 100%;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: slideIn 0.6s ease-out forwards;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-orange);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <button class="menu-btn" id="menuBtn">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <div class="logo-container">
                <img src="atire.png" alt="Logo" class="logo-img">
                <div class="brand-text">Customer Portal</div>
            </div>
        </div>

        <div class="header-actions">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search...">
            </div>
            
            <button class="notification-btn">
                <i class="fas fa-bell fa-lg"></i>
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
        </div>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <nav class="nav-section">
                <h3 class="nav-title">Dashboard</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard2.php">
                            <i class="fas fa-home"></i>
                            Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="add_order.php">
                            <i class="fas fa-plus-circle"></i>
                            New Order
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="view_order.php">
                            <i class="fas fa-list"></i>
                            My Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics.php" class="active">
                            <i class="fas fa-chart-bar"></i>
                            Analytics
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
                        <a href="#">
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
                    <h1 class="page-title">Analytics Dashboard</h1>
                    <p class="page-subtitle">Insights into your order history and trends</p>
                </div>
                <div class="header-actions-right">
                    <a href="order-history.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i>
                        View Orders
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card animate-in">
                    <div class="stat-value"><?php echo htmlentities($totalOrders); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card animate-in">
                    <div class="stat-value"><?php echo htmlentities($ordersByStatus['pending'] ?? 0); ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card animate-in">
                    <div class="stat-value"><?php echo htmlentities($ordersByStatus['in process'] ?? 0); ?></div>
                    <div class="stat-label">In Process Orders</div>
                </div>
                <div class="stat-card animate-in">
                    <div class="stat-value"><?php echo htmlentities($ordersByStatus['closed'] ?? 0); ?></div>
                    <div class="stat-label">Closed Orders</div>
                </div>
                <div class="stat-card animate-in">
                    <div class="stat-value"><?php echo number_format($totalOrderValue, 2); ?></div>
                    <div class="stat-label">Total Order Value</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Orders Over Time
                    </h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ordersByMonthChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Orders by Status
                    </h2>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ordersByStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Items Table -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list"></i>
                        Top 5 Most Ordered Items
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Code</th>
                                    <th>Total Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $cnt = 1;
                                if (count($topItems) > 0) {
                                    foreach ($topItems as $item) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td><?php echo htmlentities($item['icode']); ?></td>
                                            <td><?php echo htmlentities($item['total_quantity']); ?></td>
                                        </tr>
                                        <?php
                                        $cnt++;
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="3">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox empty-icon"></i>
                                                <h3 style="margin-bottom: 0.5rem; color: var(--dark-gray);">No items found</h3>
                                                <p>You haven't ordered any items yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Menu Toggle for Mobile
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        
        menuBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });

        // User dropdown (placeholder)
        const userBtn = document.getElementById('userBtn');
        userBtn?.addEventListener('click', () => {
            console.log('User menu clicked');
        });

        // Search functionality (for top items table)
        const searchInput = document.querySelector('.search-input');
        searchInput?.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const text = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });

        // Animate elements on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.card, .stat-card').forEach(el => {
            observer.observe(el);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput?.focus();
            }
            
            if (e.key === 'Escape' && window.innerWidth <= 768) {
                sidebar.classList.remove('show');
            }
        });

        // Chart.js: Orders by Month (Line Chart)
        const ordersByMonthChart = new Chart(document.getElementById('ordersByMonthChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Orders per Month',
                    data: <?php echo json_encode($ordersByMonth); ?>,
                    borderColor: 'rgba(242, 128, 24, 1)',
                    backgroundColor: 'rgba(242, 128, 24, 0.2)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(242, 128, 24, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(242, 128, 24, 1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Order Trends by Month'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Orders'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        // Chart.js: Orders by Status (Pie Chart)
        const ordersByStatusChart = new Chart(document.getElementById('ordersByStatusChart'), {
            type: 'pie',
            data: {
                labels: ['Pending', 'In Process', 'Closed'],
                datasets: [{
                    data: [
                        <?php echo json_encode($ordersByStatus['pending'] ?? 0); ?>,
                        <?php echo json_encode($ordersByStatus['in process'] ?? 0); ?>,
                        <?php echo json_encode($ordersByStatus['closed'] ?? 0); ?>
                    ],
                    backgroundColor: [
                        'rgba(231, 76, 60, 0.8)',  // error (pending)
                        'rgba(241, 196, 15, 0.8)', // warning (in process)
                        'rgba(39, 174, 96, 0.8)'   // success (closed)
                    ],
                    borderColor: ['#fff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Orders by Status'
                    }
                }
            }
        });

        console.log('Analytics Page loaded successfully!');
    </script>
</body>
</html>
```