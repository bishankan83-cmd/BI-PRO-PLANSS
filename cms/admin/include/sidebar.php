<?php
// sidebar.php
$current_page = basename($_SERVER['PHP_SELF']); // Get current page name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --border-gray: #e0e0e0;
            --bg-light: #f8fafc;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --white: #ffffff;
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

        @media (max-width: 768px) {
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
    <nav class="sidebar" id="sidebar">
        <div class="nav-section">
            <h3 class="nav-title">Admin Management</h3>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="account-manager-dashboard.php" class="<?= ($current_page == 'account-manager-dashboard.php') ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage-users.php" class="<?= ($current_page == 'manage-users.php') ? 'active' : '' ?>">
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
                    <a href="usage_type.php" class="<?= ($current_page == 'usage_type.php') ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        Usage Type
                    </a>
                </li>
                <li class="nav-item">
                    <a href="nature_com.php" class="<?= ($current_page == 'nature_com.php') ? 'active' : '' ?>">
                        <i class="fas fa-exclamation-triangle"></i>
                        Nature of Complaint
                    </a>
                </li>
                <li class="nav-item">
                    <a href="opertaion_condition.php" class="<?= ($current_page == 'opertaion_condition.php') ? 'active' : '' ?>">
                        <i class="fas fa-tools"></i>
                        Operation Condition
                    </a>
                </li>
                <li class="nav-item">
                    <a href="impact_p.php" class="<?= ($current_page == 'impact_p.php') ? 'active' : '' ?>">
                        <i class="fas fa-circle-exclamation"></i>
                        Impact of Problem
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <h3 class="nav-title">User Complaints</h3>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="all-complaint.php" class="<?= ($current_page == 'all-complaint.php') ? 'active' : '' ?>">
                        <i class="fas fa-list"></i>
                        All Complaints
                    </a>
                </li>
                <li class="nav-item">
                    <a href="notprocess-complaint.php" class="<?= ($current_page == 'notprocess-complaint.php') ? 'active' : '' ?>">
                        <i class="fas fa-clock"></i>
                        Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a href="inprocess-complaint.php" class="<?= ($current_page == 'inprocess-complaint.php') ? 'active' : '' ?>">
                        <i class="fas fa-spinner"></i>
                        In Process
                    </a>
                </li>
                <li class="nav-item">
                    <a href="closed-complaint.php" class="<?= ($current_page == 'closed-complaint.php') ? 'active' : '' ?>">
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
                    <a href="between-date-userreport.php" class="<?= ($current_page == 'between-date-userreport.php') ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i>
                        User Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="between-date-complaintreport.php" class="<?= ($current_page == 'between-date-complaintreport.php') ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i>
                        Complaint Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="user-search.php" class="<?= ($current_page == 'user-search.php') ? 'active' : '' ?>">
                        <i class="fas fa-search"></i>
                        User Search
                    </a>
                </li>
                <li class="nav-item">
                    <a href="complaint-search.php" class="<?= ($current_page == 'complaint-search.php') ? 'active' : '' ?>">
                        <i class="fas fa-search-plus"></i>
                        Search Complaints
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</body>
</html>
