<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // Fetch admin details
    $adminId = intval($_SESSION["aid"]);
    $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
    $adminData = mysqli_fetch_array($adminQuery);
    $adminAcmRef = mysqli_real_escape_string($con, $adminData['acm_ref']);

    if (isset($_GET['uid']) && $_GET['action'] == 'del') {
        $userid = intval($_GET['uid']);
        $query = mysqli_query($con, "DELETE FROM users WHERE id='$userid'");
        if ($query) {
            echo '<script>alert("User Deleted Successfully")</script>';
        } else {
            echo '<script>alert("Error deleting user")</script>';
        }
        echo "<script>window.location.href='manage-users.php'</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Manage Users</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --blue: #3b82f6;
            --blue-light: rgba(59, 130, 246, 0.1);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper { display: flex; min-height: 100vh; }

        .main-content { flex: 1; padding: 2rem; max-width: 100%; overflow-x: hidden; }

        .page-header {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            color: var(--white);
            box-shadow: var(--shadow-lg);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .header-left h1 { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; }
        .header-left p { opacity: 0.9; font-size: 1rem; }

        .acm-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .header-actions { display: flex; gap: 1rem; flex-wrap: wrap; }

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
            transition: all 0.3s;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .btn-white { background: var(--white); color: var(--primary-orange); box-shadow: var(--shadow); }
        .btn-white:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-outline { background: transparent; color: var(--white); border: 2px solid var(--white); }
        .btn-outline:hover { background: var(--white); color: var(--primary-orange); }
        .btn-success { background: linear-gradient(135deg, var(--success), #2ecc71); color: var(--white); box-shadow: var(--shadow); }
        .btn-success:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-gray);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-orange), var(--secondary-orange));
        }

        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-xl); }

        .stat-content { display: flex; align-items: center; gap: 1.5rem; }

        .stat-icon {
            width: 4rem; height: 4rem;
            border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; flex-shrink: 0;
        }

        .stat-icon.orange { background: var(--orange-light); color: var(--primary-orange); }
        .stat-icon.green { background: var(--success-light); color: var(--success); }
        .stat-icon.blue { background: var(--blue-light); color: var(--blue); }

        .stat-info h3 { font-size: 2.5rem; font-weight: 800; color: var(--dark-gray); margin-bottom: 0.25rem; }
        .stat-info p { color: var(--text-gray); font-size: 0.95rem; font-weight: 500; }

        .card {
            background: var(--white);
            border-radius: 1.25rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 2px solid var(--border-gray);
            background: var(--bg-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body { padding: 2rem; }

        .filter-wrapper {
            background: var(--bg-light);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-gray);
        }

        .filter-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .filter-header h3 { font-size: 1rem; font-weight: 600; color: var(--dark-gray); display: flex; align-items: center; gap: 0.5rem; }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .filter-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-gray); display: flex; align-items: center; gap: 0.5rem; }

        .filter-input, .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-gray);
            border-radius: 0.75rem;
            background: var(--white);
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .filter-input:focus, .filter-select:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px var(--orange-light);
        }

        .filter-actions { display: flex; gap: 1rem; flex-wrap: wrap; }

        .btn-primary { background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)); color: var(--white); box-shadow: var(--shadow); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-secondary { background: var(--white); color: var(--text-gray); border: 2px solid var(--border-gray); }
        .btn-secondary:hover { background: var(--bg-light); border-color: var(--primary-orange); color: var(--primary-orange); }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-info { font-size: 0.9rem; color: var(--text-gray); font-weight: 500; }
        .table-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; }

        .table-container { background: var(--white); border-radius: 1rem; overflow: hidden; border: 1px solid var(--border-gray); }
        .table-responsive { overflow-x: auto; overflow-y: visible; }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1400px;
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            position: sticky; top: 0; z-index: 10;
        }

        .table th {
            padding: 1rem 0.5rem;
            text-align: left;
            font-weight: 700;
            color: var(--white);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            border-bottom: 3px solid var(--secondary-orange);
        }

        .table tbody tr { border-bottom: 1px solid var(--border-gray); transition: all 0.3s; }
        .table tbody tr:hover { background: var(--orange-light); transform: scale(1.01); box-shadow: var(--shadow-md); }
        .table td { padding: 1rem 0.5rem; color: var(--dark-gray); font-size: 0.9rem; vertical-align: middle; }

        .user-cell { display: flex; align-items: center; gap: 0.75rem; }
        .user-avatar {
            width: 48px; height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            display: flex; align-items: center; justify-content: center;
            color: var(--white); font-weight: 700; font-size: 1.1rem;
            flex-shrink: 0; box-shadow: var(--shadow);
        }
        .user-info { flex: 1; }
        .user-name { font-weight: 700; color: var(--dark-gray); margin-bottom: 0.25rem; font-size: 0.95rem; }
        .user-company { color: var(--text-gray); font-size: 0.8rem; display: flex; align-items: center; gap: 0.5rem; }

        .customer-id-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: var(--orange-light); color: var(--primary-orange);
            border-radius: 0.5rem; font-weight: 700; font-size: 0.85rem;
        }

        .customer-code { display: block; color: var(--text-gray); font-size: 0.75rem; margin-top: 0.25rem; }

        .email-cell { display: flex; align-items: center; gap: 0.5rem; color: var(--text-gray); }
        .email-cell i { color: var(--primary-orange); }

        .country-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: var(--bg-light); border: 1px solid var(--border-gray);
            border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;
        }

        .manager-cell { display: flex; flex-direction: column; gap: 0.25rem; }
        .manager-name { font-weight: 600; color: var(--dark-gray); }
        .manager-ref { color: var(--text-gray); font-size: 0.75rem; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 1rem; border-radius: 2rem;
            font-size: 0.8rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .status-active { background: var(--success-light); color: var(--success); border: 2px solid var(--success); }
        .status-inactive { background: var(--error-light); color: var(--error); border: 2px solid var(--error); }

        .date-cell { display: flex; flex-direction: column; gap: 0.25rem; }
        .date-primary { font-weight: 600; color: var(--dark-gray); }
        .date-secondary { color: var(--text-gray); font-size: 0.75rem; }

        .action-buttons { display: flex; gap: 0.5rem; flex-wrap: wrap; }

        .btn-action { padding: 0.5rem 1rem; font-size: 0.8rem; border-radius: 0.5rem; font-weight: 600; }
        .btn-edit { background: linear-gradient(135deg, var(--warning), #e67e22); color: var(--white); }
        .btn-delete { background: linear-gradient(135deg, var(--error), #c0392b); color: var(--white); }
        .btn-action:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }

        .no-data { text-align: center; padding: 4rem 2rem; color: var(--text-gray); }
        .no-data-icon {
            width: 120px; height: 120px;
            margin: 0 auto 1.5rem;
            border-radius: 50%; background: var(--orange-light);
            display: flex; align-items: center; justify-content: center;
        }
        .no-data-icon i { font-size: 4rem; color: var(--primary-orange); }
        .no-data h3 { font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--dark-gray); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.6s ease-out; }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: var(--bg-light); border-radius: 5px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)); border-radius: 5px; }

        @media (max-width: 768px) {
            .main-content { padding: 1rem; }
            .page-header { padding: 1.5rem; }
            .header-content { flex-direction: column; align-items: stretch; }
            .header-left h1 { font-size: 1.5rem; }
            .header-actions { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr; }
            .filter-grid { grid-template-columns: 1fr; }
            .card-body { padding: 1rem; }
            .table-controls { flex-direction: column; align-items: stretch; }
            .btn { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-content">

            <!-- Page Header -->
            <div class="page-header animate-in">
                <div class="header-content">
                    <div class="header-left">
                        <h1><i class="fas fa-users"></i> User Management</h1>
                        <p>All users across the system</p>
                        <?php if ($adminAcmRef) { ?>
                            <div class="acm-badge">
                                <i class="fas fa-user-shield"></i>
                                ACM Reference: <?php echo htmlentities($adminAcmRef); ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="header-actions">
                        <a href="Marketing.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                        <a href="add-user.php" class="btn btn-success">
                            <i class="fas fa-user-plus"></i>
                            Add New User
                        </a>
                        <button class="btn btn-white" onclick="window.print()">
                            <i class="fas fa-print"></i>
                            Export Report
                        </button>
                    </div>
                </div>
            </div>

            <?php
            // ✅ SHOW ALL USERS — no ACM reference filter
            $totalUsersQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users");
            $totalUsersData = mysqli_fetch_array($totalUsersQuery);
            $totalUsers = $totalUsersData['total'];

            $activeUsersQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users WHERE status = 1");
            $activeUsersData = mysqli_fetch_array($activeUsersQuery);
            $activeUsers = $activeUsersData['total'];

            $recentUsersQuery = mysqli_query($con, "SELECT COUNT(*) as total FROM users WHERE regDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $recentUsersData = mysqli_fetch_array($recentUsersQuery);
            $recentUsers = $recentUsersData['total'];
            ?>

            <!-- Statistics -->
            <div class="stats-grid animate-in">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon orange"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $activeUsers; ?></h3>
                            <p>Active Users</p>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-icon blue"><i class="fas fa-user-plus"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $recentUsers; ?></h3>
                            <p>New This Month</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Table Card -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-table"></i>
                        All Users
                    </h2>
                    <a href="add-user.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i>
                        Add User
                    </a>
                </div>
                <div class="card-body">

                    <!-- Filter Section -->
                    <div class="filter-wrapper">
                        <div class="filter-header">
                            <h3><i class="fas fa-filter"></i> Filter Users</h3>
                        </div>
                        <div class="filter-grid">
                            <div class="filter-group">
                                <label><i class="fas fa-search"></i> Search</label>
                                <input type="text" id="searchInput" class="filter-input" placeholder="Search by name, email, or customer ID...">
                            </div>
                            <div class="filter-group">
                                <label><i class="fas fa-globe"></i> Country</label>
                                <select id="countryFilter" class="filter-select">
                                    <option value="">All Countries</option>
                                    <?php
                                    // ✅ All countries — no ACM filter
                                    $countryQuery = mysqli_query($con, "SELECT DISTINCT Country FROM users WHERE Country != '' ORDER BY Country");
                                    while ($country = mysqli_fetch_array($countryQuery)) {
                                        echo '<option value="' . htmlentities($country['Country']) . '">' . htmlentities($country['Country']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label><i class="fas fa-toggle-on"></i> Status</label>
                                <select id="statusFilter" class="filter-select">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label><i class="fas fa-user-tie"></i> Account Manager</label>
                                <select id="acmFilter" class="filter-select">
                                    <option value="">All Managers</option>
                                    <?php
                                    // ✅ All ACM names — no ACM filter
                                    $acmQuery = mysqli_query($con, "SELECT DISTINCT acm_name FROM users WHERE acm_name != '' ORDER BY acm_name");
                                    while ($acm = mysqli_fetch_array($acmQuery)) {
                                        echo '<option value="' . htmlentities($acm['acm_name']) . '">' . htmlentities($acm['acm_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="filter-actions">
                            <button class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <button class="btn btn-secondary" onclick="resetFilters()">
                                <i class="fas fa-redo"></i> Reset All
                            </button>
                        </div>
                    </div>

                    <!-- Table Controls -->
                    <div class="table-controls">
                        <div class="table-info">
                            <i class="fas fa-info-circle"></i>
                            Showing <strong id="visibleCount"><?php echo $totalUsers; ?></strong> of <strong><?php echo $totalUsers; ?></strong> users
                        </div>
                        <div class="table-actions">
                            <button class="btn btn-secondary btn-sm" onclick="exportToCSV()">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table" id="userTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Customer ID</th>
                                        <th>Email</th>
                                        <th>Country</th>
                                        <th>Account Manager</th>
                                        <th>ACM Ref</th>
                                        <th>Status</th>
                                        <th>Registration Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // ✅ Fetch ALL users — no acm_ref WHERE clause
                                    $query = mysqli_query($con, "SELECT * FROM users ORDER BY regDate DESC");
                                    $cnt = 1;
                                    $rowCount = mysqli_num_rows($query);

                                    if ($rowCount > 0) {
                                        while ($row = mysqli_fetch_array($query)) {
                                            $statusClass = $row['status'] == 1 ? 'status-active' : 'status-inactive';
                                            $statusText  = $row['status'] == 1 ? 'Active' : 'Inactive';
                                            $statusIcon  = $row['status'] == 1 ? 'fa-check-circle' : 'fa-times-circle';

                                            // Get initials for avatar
                                            $nameParts = explode(' ', $row['fullName']);
                                            $initials = '';
                                            foreach ($nameParts as $part) {
                                                if (!empty($part)) {
                                                    $initials .= strtoupper($part[0]);
                                                    if (strlen($initials) >= 2) break;
                                                }
                                            }
                                    ?>
                                    <tr data-country="<?php echo htmlentities($row['Country']); ?>"
                                        data-status="<?php echo htmlentities($row['status']); ?>"
                                        data-acm="<?php echo htmlentities($row['acm_name']); ?>">

                                        <td><strong><?php echo $cnt; ?></strong></td>

                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar"><?php echo $initials; ?></div>
                                                <div class="user-info">
                                                    <div class="user-name"><?php echo htmlentities($row['fullName']); ?></div>
                                                    <?php if ($row['company_rn']) { ?>
                                                        <div class="user-company">
                                                            <i class="fas fa-building"></i>
                                                            <?php echo htmlentities($row['company_rn']); ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="customer-id-badge">
                                                <i class="fas fa-id-card"></i>
                                                <?php echo htmlentities($row['cus_id'] ? $row['cus_id'] : 'N/A'); ?>
                                            </div>
                                            <?php if ($row['customer_code']) { ?>
                                                <span class="customer-code">Code: <?php echo htmlentities($row['customer_code']); ?></span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <div class="email-cell">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlentities($row['userEmail']); ?>
                                            </div>
                                        </td>

                                        <td>
                                            <?php if ($row['Country']) { ?>
                                                <span class="country-badge">
                                                    <i class="fas fa-flag"></i>
                                                    <?php echo htmlentities($row['Country']); ?>
                                                </span>
                                            <?php } else { ?>
                                                <span style="color:var(--text-gray);">N/A</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <?php if ($row['acm_name']) { ?>
                                                <div class="manager-cell">
                                                    <span class="manager-name">
                                                        <i class="fas fa-user-tie"></i>
                                                        <?php echo htmlentities($row['acm_name']); ?>
                                                    </span>
                                                </div>
                                            <?php } else { ?>
                                                <span style="color:var(--text-gray);">Not Assigned</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <?php if ($row['acm_ref']) { ?>
                                                <span class="customer-id-badge" style="background:var(--blue-light);color:var(--blue);">
                                                    <i class="fas fa-tag"></i>
                                                    <?php echo htmlentities($row['acm_ref']); ?>
                                                </span>
                                            <?php } else { ?>
                                                <span style="color:var(--text-gray);">N/A</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <i class="fas <?php echo $statusIcon; ?>"></i>
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="date-cell">
                                                <span class="date-primary"><?php echo date('M d, Y', strtotime($row['regDate'])); ?></span>
                                                <span class="date-secondary"><?php echo date('h:i A', strtotime($row['regDate'])); ?></span>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit-user.php?uid=<?php echo htmlentities($row['id']); ?>" class="btn btn-action btn-edit" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="manage-users.php?uid=<?php echo htmlentities($row['id']); ?>&action=del"
                                                   class="btn btn-action btn-delete"
                                                   title="Delete User"
                                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                            $cnt++;
                                        }
                                    } else {
                                    ?>
                                    <tr id="noDataRow">
                                        <td colspan="10">
                                            <div class="no-data">
                                                <div class="no-data-icon"><i class="fas fa-users"></i></div>
                                                <h3>No Users Found</h3>
                                                <p>There are no users in the system yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        function filterTable() {
            const searchValue  = document.getElementById('searchInput').value.toLowerCase();
            const countryValue = document.getElementById('countryFilter').value.toLowerCase();
            const statusValue  = document.getElementById('statusFilter').value;
            const acmValue     = document.getElementById('acmFilter').value.toLowerCase();

            const rows = document.querySelectorAll('#userTable tbody tr:not(#noDataRow)');
            let visibleCount = 0;

            rows.forEach(row => {
                let show = true;

                if (searchValue && !row.textContent.toLowerCase().includes(searchValue)) show = false;
                if (countryValue && row.getAttribute('data-country').toLowerCase() !== countryValue) show = false;
                if (statusValue  && row.getAttribute('data-status') !== statusValue) show = false;
                if (acmValue     && row.getAttribute('data-acm').toLowerCase() !== acmValue) show = false;

                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            document.getElementById('visibleCount').textContent = visibleCount;

            const noDataRow = document.getElementById('noDataRow');
            if (noDataRow) noDataRow.style.display = visibleCount === 0 ? '' : 'none';
        }

        function applyFilters() { filterTable(); }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('countryFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('acmFilter').value = '';
            filterTable();
        }

        document.getElementById('searchInput').addEventListener('keyup', filterTable);

        function exportToCSV() {
            const rows = document.querySelectorAll('#userTable tr:not(#noDataRow)');
            let csv = [];

            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                cols.forEach((col, i) => {
                    if (i < cols.length - 1) { // skip Actions column
                        let data = col.innerText.replace(/[\r\n]+/g, ' ').replace(/,/g, ' ').trim();
                        rowData.push('"' + data + '"');
                    }
                });
                csv.push(rowData.join(','));
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
            const url  = window.URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url;
            a.download = 'all_users_' + new Date().getTime() + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        window.addEventListener('load', function () {
            document.querySelectorAll('.stat-card, .card').forEach((card, i) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50 + i * 100);
            });
        });
    </script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>