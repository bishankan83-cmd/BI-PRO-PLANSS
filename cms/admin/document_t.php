<?php
session_start();
include('include/config.php');

if (empty($_SESSION['aid'])) {
    header('location:index.php');
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

// Handle Add Operation
if (isset($_POST['add_documentation_type'])) {
    $doc_name = mysqli_real_escape_string($con, trim($_POST['doc_name']));
    
    if (empty($doc_name)) {
        $_SESSION['error'] = "Documentation Type cannot be empty!";
    } elseif (strlen($doc_name) > 255) {
        $_SESSION['error'] = "Documentation Type must not exceed 255 characters!";
    } elseif (strlen($doc_name) < 2) {
        $_SESSION['error'] = "Documentation Type must be at least 2 characters long!";
    } else {
        // Check if documentation type already exists
        $check_query = mysqli_query($con, "SELECT * FROM documentation_types WHERE doc_name = '$doc_name'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Documentation Type already exists!";
        } else {
            $sql = mysqli_query($con, "INSERT INTO documentation_types(doc_name, is_active) VALUES('$doc_name', 1)");
            if ($sql) {
                $_SESSION['msg'] = "Documentation Type created successfully!";
            } else {
                $_SESSION['error'] = "Error creating documentation type: " . mysqli_error($con);
            }
        }
    }
}

// Handle Update Operation
if (isset($_POST['update_documentation_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $doc_name = mysqli_real_escape_string($con, trim($_POST['edit_doc_name']));
    
    if (empty($doc_name)) {
        $_SESSION['error'] = "Documentation Type cannot be empty!";
    } elseif (strlen($doc_name) > 255) {
        $_SESSION['error'] = "Documentation Type must not exceed 255 characters!";
    } elseif (strlen($doc_name) < 2) {
        $_SESSION['error'] = "Documentation Type must be at least 2 characters long!";
    } else {
        // Check if documentation type already exists (excluding current record)
        $check_query = mysqli_query($con, "SELECT * FROM documentation_types WHERE doc_name = '$doc_name' AND id != '$id'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Documentation Type already exists!";
        } else {
            $sql = mysqli_query($con, "UPDATE documentation_types SET doc_name = '$doc_name' WHERE id = '$id'");
            if ($sql) {
                $_SESSION['msg'] = "Documentation Type updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating documentation type: " . mysqli_error($con);
            }
        }
    }
}

// Handle Deactivate Operation
if (isset($_POST['deactivate_documentation_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['deactivate_id']);
    
    $sql = mysqli_query($con, "UPDATE documentation_types SET is_active = 0 WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Documentation Type deactivated successfully!";
    } else {
        $_SESSION['error'] = "Error deactivating documentation type: " . mysqli_error($con);
    }
}

// Handle Delete Operation
if (isset($_POST['delete_documentation_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['delete_id']);
    
    $sql = mysqli_query($con, "DELETE FROM documentation_types WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Documentation Type deleted permanently!";
    } else {
        $_SESSION['error'] = "Error deleting documentation type: " . mysqli_error($con);
    }
}

// Get edit data if edit button was clicked
$edit_data = null;
if (isset($_POST['edit_btn'])) {
    $edit_id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $edit_query = mysqli_query($con, "SELECT * FROM documentation_types WHERE id = '$edit_id' AND is_active = 1");
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
    <title>CMS | Documentation Types</title>
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
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            max-width: 500px;
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
            vertical-align: middle;
        }

        .no-results {
            color: var(--text-gray);
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            padding: 2rem;
        }

        .no-results i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-gray);
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
        }

        .status-active {
            background: var(--success-light);
            color: var(--success);
        }

        .status-inactive {
            background: var(--error-light);
            color: var(--error);
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
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
            background: var(--success);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-success:hover {
            background: #219653;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-warning {
            background: var(--warning);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-warning:hover {
            background: #e08e0b;
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

        .btn-secondary {
            background: var(--text-gray);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-secondary:hover {
            background: #5a6b82;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            transition: opacity 0.3s ease;
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

        .alert .close {
            background: none;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            color: inherit;
            position: absolute;
            right: 1rem;
            top: 1rem;
        }

        /* Edit Form Styling */
        .edit-form {
            background: var(--orange-light);
            border: 1px solid var(--primary-orange);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .edit-form h6 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-orange);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
                margin-bottom: 1rem;
            }

            .form-control {
                max-width: 100%;
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

            .btn-sm {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }

            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: center;
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
                    <li class="nav-item">
                        <a href="documentation-types.php" class="active">
                            <i class="fas fa-file-alt"></i>
                            Documentation Types
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
                    <h1 class="page-title">Documentation Types Management</h1>
                    <p class="page-subtitle">Manage documentation types for complaint submissions</p>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Documentation Types</li>
                </ul>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])): ?>
                <div class="alert alert-success animate-in">
                    <i class="fas fa-check-circle"></i>
                    <strong>Success!</strong> <?php echo htmlentities($_SESSION['msg']); ?>
                    <button type="button" class="close" onclick="this.parentElement.style.opacity='0';setTimeout(() => this.parentElement.style.display='none', 300);">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php $_SESSION['msg'] = ''; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger animate-in">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); ?>
                    <button type="button" class="close" onclick="this.parentElement.style.opacity='0';setTimeout(() => this.parentElement.style.display='none', 300);">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php $_SESSION['error'] = ''; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['delmsg']) && !empty($_SESSION['delmsg'])): ?>
                <div class="alert alert-warning animate-in">
                    <i class="fas fa-info-circle"></i>
                    <strong>Action Completed!</strong> <?php echo htmlentities($_SESSION['delmsg']); ?>
                    <button type="button" class="close" onclick="this.parentElement.style.opacity='0';setTimeout(() => this.parentElement.style.display='none', 300);">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php $_SESSION['delmsg'] = ''; ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div class="card animate-in" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title"><?php echo $edit_data ? 'Edit Documentation Type' : 'Add New Documentation Type'; ?></h2>
                </div>
                <div class="card-body">
                    <?php if ($edit_data): ?>
                        <div class="edit-form">
                            <h6><i class="fas fa-edit"></i> Editing: <strong><?php echo htmlentities($edit_data['doc_name']); ?></strong></h6>
                            <form method="post">
                                <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                                <div class="form-group">
                                    <label for="edit_doc_name">Documentation Type <span class="text-danger">*</span></label>
                                    <input type="text" name="edit_doc_name" id="edit_doc_name" class="form-control"
                                           value="<?php echo htmlentities($edit_data['doc_name']); ?>"
                                           required maxlength="255" placeholder="Enter documentation type">
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="update_documentation_type" class="btn btn-success">
                                        <i class="fas fa-check"></i> Update
                                    </button>
                                    <button type="submit" name="cancel_edit" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="post">
                            <div class="form-group">
                                <label for="doc_name">Documentation Type <span class="text-danger">*</span></label>
                                <input type="text" name="doc_name" id="doc_name" class="form-control"
                                       required maxlength="255" placeholder="Enter documentation type">
                            </div>
                            <div class="form-group">
                                <button type="submit" name="add_documentation_type" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Documentation Type
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Documentation Types Table -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">Manage Documentation Types</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Documentation Type</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = mysqli_query($con, "SELECT * FROM documentation_types ORDER BY doc_name ASC");
                                $cnt = 1;

                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><strong><?php echo $cnt; ?></strong></td>
                                        <td><?php echo htmlentities($row['doc_name']); ?></td>
                                        <td>
                                            <?php echo $row['is_active'] ? '<span class="status-badge status-active">Active</span>' : '<span class="status-badge status-inactive">Inactive</span>'; ?>
                                        </td>
                                        <td class="text-center action-buttons">
                                            <?php if ($row['is_active']): ?>
                                                <form method="post" style="display: inline-block;">
                                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="edit_btn" class="btn btn-primary btn-sm"
                                                            title="Edit Documentation Type">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </form>
                                                <form method="post" style="display: inline-block;"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this documentation type: <?php echo htmlentities($row['doc_name']); ?>?');">
                                                    <input type="hidden" name="deactivate_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="deactivate_documentation_type" class="btn btn-warning btn-sm"
                                                            title="Deactivate Documentation Type">
                                                        <i class="fas fa-pause-circle"></i> Deactivate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" style="display: inline-block;"
                                                  onsubmit="return confirm('Are you sure you want to permanently delete this documentation type: <?php echo htmlentities($row['doc_name']); ?>? This action cannot be undone.');">
                                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete_documentation_type" class="btn btn-danger btn-sm"
                                                        title="Delete Documentation Type">
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
                                        <td colspan="4" class="no-results">
                                            <i class="fas fa-inbox"></i><br>
                                            <strong>No documentation types found.</strong><br>
                                            Add your first documentation type using the form above.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Total Documentation Types: <strong><?php echo mysqli_num_rows($query); ?></strong>
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

        // Client-side form validation
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const input = form.querySelector('input[name="doc_name"], input[name="edit_doc_name"]');
                if (input) {
                    const value = input.value.trim();
                    if (value.length < 2) {
                        alert('Documentation type must be at least 2 characters long.');
                        e.preventDefault();
                        return false;
                    }
                    if (value.length > 255) {
                        alert('Documentation type must not exceed 255 characters.');
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php
mysqli_close($con);
?>