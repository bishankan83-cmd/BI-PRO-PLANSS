<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    if (isset($_GET['uid']) && $_GET['action'] == 'del') {
        $userid = $_GET['uid'];
        $query = mysqli_query($con, "DELETE FROM users WHERE id='$userid'");
        header('location:manage-users.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Between Dates Users Report</title>
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
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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

        /* Header */
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

        .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
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
            text-decoration: none;
            color: inherit;
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

        /* Layout */
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

        /* Main Content */
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

        .breadcrumb {
            display: flex;
            list-style: none;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .breadcrumb-item a {
            color: var(--text-gray);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-orange);
        }

        .breadcrumb-item.active {
            color: var(--dark-gray);
            font-weight: 600;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            color: var(--text-gray);
            margin-right: 0.5rem;
        }

        /* Card */
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
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
        }

        .card-body {
            padding: 2rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
            width: 100px;
        }

        .form-control {
            width: 300px;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-gray);
            border-radius: 0.75rem;
            font-size: 0.9rem;
            color: var(--dark-gray);
            background: var(--white);
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
            outline: none;
        }

        /* Table */
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
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-gray);
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

        /* Buttons */
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

        .btn-danger {
            background: var(--error);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-danger:hover {
            background: var(--red-accent);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        /* Responsive Design */
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

            .user-details {
                display: none;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .form-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-group label {
                width: auto;
            }

            .form-control {
                width: 100%;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Animations */
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

        /* Custom Scrollbar */
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
            <button class="menu-btn" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="dashboard.php" class="brand-text">Complaint Management System</a>
        </div>

        <div class="header-actions">
            <?php
            $adminId = intval($_SESSION["aid"]);
            $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
            $adminData = mysqli_fetch_array($adminQuery);
            ?>
            <div class="user-menu">
                <a href="admin-profile.php" class="user-btn">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($adminData['fullname'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlentities($adminData['fullname']); ?></h4>
                        <span>Administrator</span>
                    </div>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="nav-section">
                <h3 class="nav-title">Admin Management</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-users.php">
                            <i class="fas fa-users"></i>
                            Manage Users
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h3 class="nav-title">Form Management</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="usage_type.php">
                            <i class="fas fa-cog"></i>
                            Usage Type
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="nature_com.php">
                            <i class="fas fa-exclamation-triangle"></i>
                            Nature of Complaint
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="opertaion_condition.php">
                            <i class="fas fa-tools"></i>
                            Operation Condition
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="impact_p.php">
                            <i class="fas fa-impact"></i>
                            Impact of Problem
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h3 class="nav-title">User Complaints</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="all-complaint.php">
                            <i class="fas fa-list"></i>
                            All Complaints
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="notprocess-complaint.php">
                            <i class="fas fa-clock"></i>
                            Pending
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="inprocess-complaint.php">
                            <i class="fas fa-spinner"></i>
                            In Process
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="closed-complaint.php">
                            <i class="fas fa-check-circle"></i>
                            Closed
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <h3 class="nav-title">Reports & Search</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="between-date-userreport.php" class="active">
                            <i class="fas fa-chart-bar"></i>
                            User Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="between-date-complaintreport.php">
                            <i class="fas fa-chart-line"></i>
                            Complaint Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="user-search.php">
                            <i class="fas fa-search"></i>
                            User Search
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="complaint-search.php">
                            <i class="fas fa-search-plus"></i>
                            Search Complaints
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Between Dates Users Report</h1>
                    <p class="page-subtitle">Generate a report of users registered between selected dates</p>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Between Dates Users Report</li>
                </ul>
            </div>

            <!-- Date Filter Form -->
            <div class="card animate-in" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Filter Users by Registration Date</h2>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group">
                            <label for="fromdate">From Date</label>
                            <input type="date" name="fromdate" id="fromdate" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="todate">To Date</label>
                            <input type="date" name="todate" id="todate" class="form-control" required>
                        </div>
                        <div style="text-align: center;">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <?php if (isset($_POST['submit'])) {
                $fdate = $_POST['fromdate'];
                $tdate = $_POST['todate'];
            ?>
                <div class="card animate-in">
                    <div class="card-header">
                        <h2 class="card-title">Users Report: <?php echo htmlentities($fdate); ?> to <?php echo htmlentities($tdate); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact No</th>
                                        <th>Reg. Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = mysqli_query($con, "SELECT * FROM users WHERE regDate BETWEEN '$fdate' AND '$tdate'");
                                    $cnt = 1;
                                    while ($row = mysqli_fetch_array($query)) {
                                    ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td><?php echo htmlentities($row['fullName']); ?></td>
                                            <td><?php echo htmlentities($row['userEmail']); ?></td>
                                            <td><?php echo htmlentities($row['contactNo']); ?></td>
                                            <td><?php echo htmlentities($row['regDate']); ?></td>
                                            <td>
                                                <a href="javascript:void(0);" 
                                                   onClick="popUpWindow('userprofile.php?uid=<?php echo htmlentities($row['id']); ?>', 200, 200, 600, 600);" 
                                                   class="btn btn-primary">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                <a href="manage-users.php?uid=<?php echo htmlentities($row['id']); ?>&action=del" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Do you really want to delete?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                        $cnt++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Popup window function
        var popUpWin = 0;
        function popUpWindow(URLStr, left, top, width, height) {
            if (popUpWin) {
                if (!popUpWin.closed) popUpWin.close();
            }
            popUpWin = open(URLStr, 'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',screenX=' + left + ',screenY=' + top);
        }
    </script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>