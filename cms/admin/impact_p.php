<?php
session_start();
include('include/config.php');

if (empty($_SESSION['aid'])) {
    header('location:index.php');
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

// Fetch admin details
$adminId = intval($_SESSION["aid"]);
$adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
$adminData = mysqli_fetch_array($adminQuery);

// Handle Add Operation
if (isset($_POST['add_impact_type'])) {
    $impact_name = mysqli_real_escape_string($con, trim($_POST['impact_name']));
    
    if (empty($impact_name)) {
        $_SESSION['error'] = "Impact Type cannot be empty!";
    } elseif (strlen($impact_name) > 100) {
        $_SESSION['error'] = "Impact Type must not exceed 100 characters!";
    } elseif (strlen($impact_name) < 2) {
        $_SESSION['error'] = "Impact Type must be at least 2 characters long!";
    } else {
        // Check if impact type already exists
        $check_query = mysqli_query($con, "SELECT * FROM impact_types WHERE impact_name = '$impact_name'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Impact Type already exists!";
        } else {
            $sql = mysqli_query($con, "INSERT INTO impact_types(impact_name, is_active, created_at) VALUES('$impact_name', 1, CURRENT_TIMESTAMP)");
            if ($sql) {
                $_SESSION['msg'] = "Impact Type created successfully!";
            } else {
                $_SESSION['error'] = "Error creating impact type: " . mysqli_error($con);
            }
        }
    }
}

// Handle Update Operation
if (isset($_POST['update_impact_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $impact_name = mysqli_real_escape_string($con, trim($_POST['edit_impact_name']));
    
    if (empty($impact_name)) {
        $_SESSION['error'] = "Impact Type cannot be empty!";
    } elseif (strlen($impact_name) > 100) {
        $_SESSION['error'] = "Impact Type must not exceed 100 characters!";
    } elseif (strlen($impact_name) < 2) {
        $_SESSION['error'] = "Impact Type must be at least 2 characters long!";
    } else {
        // Check if impact type already exists (excluding current record)
        $check_query = mysqli_query($con, "SELECT * FROM impact_types WHERE impact_name = '$impact_name' AND id != '$id'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Impact Type already exists!";
        } else {
            $sql = mysqli_query($con, "UPDATE impact_types SET impact_name = '$impact_name' WHERE id = '$id'");
            if ($sql) {
                $_SESSION['msg'] = "Impact Type updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating impact type: " . mysqli_error($con);
            }
        }
    }
}

// Handle Deactivate Operation
if (isset($_POST['deactivate_impact_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['deactivate_id']);
    
    $sql = mysqli_query($con, "UPDATE impact_types SET is_active = 0 WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Impact Type deactivated successfully!";
    } else {
        $_SESSION['error'] = "Error deactivating impact type: " . mysqli_error($con);
    }
}

// Handle Delete Operation
if (isset($_POST['delete_impact_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['delete_id']);
    
    $sql = mysqli_query($con, "DELETE FROM impact_types WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Impact Type deleted permanently!";
    } else {
        $_SESSION['error'] = "Error deleting impact type: " . mysqli_error($con);
    }
}

// Get edit data if edit button was clicked
$edit_data = null;
if (isset($_POST['edit_btn'])) {
    $edit_id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $edit_query = mysqli_query($con, "SELECT * FROM impact_types WHERE id = '$edit_id' AND is_active = 1");
    if (mysqli_num_rows($edit_query) > 0) {
        $edit_data = mysqli_fetch_array($edit_query);
    }
}

// Cancel edit
if (isset($_POST['cancel_edit'])) {
    $edit_data = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Impact Types</title>
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
            text-decoration: none;
        }

        .notification-btn:hover {
            background: var(--orange-light);
            color: var(--primary-orange);
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

        .header-actions-right {
            display: flex;
            gap: 1rem;
        }

        .breadcrumb {
            list-style: none;
            display: flex;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-gray);
        }

        .breadcrumb-item a {
            color: var(--text-gray);
            text-decoration: none;
            transition: all 0.2s;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-orange);
        }

        .breadcrumb-item i {
            margin-right: 0.5rem;
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-gray);
            border-radius: 0.75rem;
            font-size: 0.9rem;
            transition: all 0.3s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
        }

        .edit-form {
            background: var(--success-light);
            border: 1px solid var(--success);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-danger {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }

        .alert-warning {
            background: var(--warning-light);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .alert-dismissible .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1rem;
            color: inherit;
            cursor: pointer;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            vertical-align: middle;
        }

        .table th {
            background: var(--medium-gray);
            color: var(--white);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table td {
            border-bottom: 1px solid var(--border-gray);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background: var(--bg-light);
        }

        .table-hover tbody tr:hover {
            background: var(--light-gray);
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-active {
            background: var(--success-light);
            color: var(--success);
        }

        .status-inactive {
            background: var(--error-light);
            color: var(--error);
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

        .btn-success {
            background: var(--gradient-2);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-warning {
            background: var(--gradient-4);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-warning:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-danger {
            background: var(--gradient-3);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-danger:hover {
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .action-buttons form {
            display: inline-block;
            margin: 0 0.25rem;
        }

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

            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: center;
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
            <button class="menu-btn" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="dashboard.php" class="brand-text">Complaint Management System</a>
        </div>
        <div class="header-actions">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search complaints...">
            </div>
            <a href="#" class="notification-btn">
                <i class="fas fa-bell"></i>
            </a>
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
                        <a href="operation-condition.php">
                            <i class="fas fa-tools"></i>
                            Operation Condition
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="impact_p.php" class="active">
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
                        <a href="between-date-userreport.php">
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
                    <h1 class="page-title">Impact Types Management</h1>
                    <p class="page-subtitle">Manage impact types for tire complaints.</p>
                </div>
                <div class="header-actions-right">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])): ?>
                <div class="alert alert-success alert-dismissible animate-in">
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Success!</strong> <?php echo htmlentities($_SESSION['msg']); ?></span>
                    <button type="button" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?php $_SESSION['msg'] = ''; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible animate-in">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); ?></span>
                    <button type="button" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?php $_SESSION['error'] = ''; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['delmsg']) && !empty($_SESSION['delmsg'])): ?>
                <div class="alert alert-warning alert-dismissible animate-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><strong>Action Completed!</strong> <?php echo htmlentities($_SESSION['delmsg']); ?></span>
                    <button type="button" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?php $_SESSION['delmsg'] = ''; ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Form Card -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-<?php echo $edit_data ? 'edit' : 'plus-circle'; ?>"></i>
                        <?php echo $edit_data ? 'Edit Impact Type' : 'Add New Impact Type'; ?>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($edit_data): ?>
                        <div class="edit-form">
                            <h6><i class="fas fa-edit"></i> Editing: <strong><?php echo htmlentities($edit_data['impact_name']); ?></strong></h6>
                            <form method="post" class="mt-3">
                                <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="form-label">Impact Type <span class="text-danger">*</span></label>
                                            <input type="text" name="edit_impact_name" class="form-control"
                                                   value="<?php echo htmlentities($edit_data['impact_name']); ?>"
                                                   required maxlength="100" placeholder="Enter impact type">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mt-md-4">
                                            <button type="submit" name="update_impact_type" class="btn btn-success">
                                                <i class="fas fa-check"></i> Update
                                            </button>
                                            <button type="submit" name="cancel_edit" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="post">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label">Impact Type <span class="text-danger">*</span></label>
                                        <input type="text" name="impact_name" id="impact_name" class="form-control"
                                               required maxlength="100" placeholder="Enter impact type">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mt-md-4">
                                        <button type="submit" name="add_impact_type" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Impact Type
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Manage Impact Types Table Card -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list"></i>
                        Manage Impact Types
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="40%">Impact Type</th>
                                    <th width="20%">Status</th>
                                    <th width="30%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = mysqli_query($con, "SELECT * FROM impact_types ORDER BY impact_name ASC");
                                $cnt = 1;
                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><strong><?php echo $cnt; ?></strong></td>
                                        <td><?php echo htmlentities($row['impact_name']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="text-center action-buttons">
                                            <?php if ($row['is_active']): ?>
                                                <form method="post" style="display: inline-block;">
                                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="edit_btn" class="btn btn-primary btn-sm"
                                                            title="Edit Impact Type">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </form>
                                                <form method="post" style="display: inline-block;"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this impact type: <?php echo htmlentities($row['impact_name']); ?>?');">
                                                    <input type="hidden" name="deactivate_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="deactivate_impact_type" class="btn btn-warning btn-sm"
                                                            title="Deactivate Impact Type">
                                                        <i class="fas fa-pause-circle"></i> Deactivate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" style="display: inline-block;"
                                                  onsubmit="return confirm('Are you sure you want to permanently delete this impact type: <?php echo htmlentities($row['impact_name']); ?>? This action cannot be undone.');">
                                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete_impact_type" class="btn btn-danger btn-sm"
                                                        title="Delete Impact Type">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php
                                        $cnt++;
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h3>No impact types found.</h3>
                                            <p>Add your first impact type using the form above.</p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Total Impact Types: <strong><?php echo mysqli_num_rows($query); ?></strong>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        if (menuToggle && sidebar) {
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
        }

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    const searchTerm = e.target.value.trim();
                    if (searchTerm) {
                        window.location.href = `complaint-search.php?search=${encodeURIComponent(searchTerm)}`;
                    }
                }
            });
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert) => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });

        // Add animation delays for cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    </script>
</body>
</html>
<?php mysqli_close($con); ?>