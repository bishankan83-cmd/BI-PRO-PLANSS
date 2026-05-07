<?php
// dashboard.php
session_start();
include('include/config.php');

// Check if user is logged in
if (!isset($_SESSION["aid"])) {
    header("Location: index.php");
    exit();
}

// Fetch dashboard statistics
$query1 = mysqli_query($con, "SELECT id FROM users");
$totusers = mysqli_num_rows($query1);

$query2 = mysqli_query($con, "SELECT id FROM tbl_tire_complaints");
$totcom = mysqli_num_rows($query2);

$query3 = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE status IS NULL");
$pendingcom = mysqli_num_rows($query3);

$query4 = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE status='in process'");
$inprocesscom = mysqli_num_rows($query4);

$query5 = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE status='closed'");
$closedcom = mysqli_num_rows($query5);

$query6 = mysqli_query($con, "SELECT id FROM subcategory");
$totsubcategory = mysqli_num_rows($query6);

$query7 = mysqli_query($con, "SELECT id FROM state");
$totstate = mysqli_num_rows($query7);

// Fetch recent complaints for the activity feed
$recentComplaints = mysqli_query($con, "SELECT tc.*, u.fullName as userName FROM tbl_tire_complaints tc 
                                       JOIN users u ON u.id = tc.userId 
                                       ORDER BY tc.created_at DESC LIMIT 5");

// Fetch admin details
$adminId = intval($_SESSION["aid"]);
$adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
$adminData = mysqli_fetch_array($adminQuery);

// Calculate resolution rate
$resolutionRate = $totcom > 0 ? round(($closedcom / $totcom) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service Portal - Dashboard</title>
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

        .container {
            display: flex;
            min-height: calc(100vh - 80px);
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

        .btn-danger {
            background: var(--gradient-3);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid var(--border-gray);
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: var(--shadow-sm);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
        }

        .stat-card.success::before {
            background: var(--gradient-2);
        }

        .stat-card.warning::before {
            background: var(--gradient-4);
        }

        .stat-card.danger::before {
            background: var(--gradient-3);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .stat-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: var(--orange-light);
            color: var(--primary-orange);
        }

        .stat-card.success .stat-icon {
            background: var(--success-light);
            color: var(--success);
        }

        .stat-card.warning .stat-icon {
            background: var(--warning-light);
            color: var(--warning);
        }

        .stat-card.danger .stat-icon {
            background: var(--error-light);
            color: var(--error);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--dark-gray);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-weight: 600;
            color: var(--text-gray);
            margin-bottom: 0.25rem;
        }

        .stat-description {
            font-size: 0.85rem;
            color: var(--text-gray);
            opacity: 0.8;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
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

        .complaints-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .complaint-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--bg-light);
            border-radius: 0.75rem;
            border: 1px solid var(--border-gray);
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .complaint-item:hover {
            background: var(--white);
            border-color: var(--primary-orange);
            transform: translateX(0.25rem);
            box-shadow: var(--shadow-md);
        }

        .complaint-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            background: var(--orange-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-orange);
            font-size: 1.25rem;
        }

        .complaint-details {
            flex: 1;
        }

        .complaint-title {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .complaint-meta {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: var(--warning-light);
            color: var(--warning);
        }

        .status-in-process {
            background: var(--orange-light);
            color: var(--primary-orange);
        }

        .status-closed {
            background: var(--success-light);
            color: var(--success);
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .action-card {
            padding: 1.5rem;
            background: var(--bg-light);
            border-radius: 0.75rem;
            border: 1px solid var(--border-gray);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .action-card:hover {
            background: var(--white);
            border-color: var(--primary-orange);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .action-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            background: var(--orange-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-orange);
            font-size: 1.25rem;
        }

        .action-content h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.25rem;
        }

        .action-content p {
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        .progress-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .progress-ring {
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
        }

        .progress-ring-circle {
            stroke: var(--border-gray);
            stroke-width: 8;
            fill: transparent;
            r: 52;
            cx: 60;
            cy: 60;
        }

        .progress-ring-fill {
            stroke: var(--primary-orange);
            stroke-width: 8;
            fill: transparent;
            r: 52;
            cx: 60;
            cy: 60;
            stroke-dasharray: 327;
            stroke-dashoffset: calc(327 - (327 * <?php echo $resolutionRate; ?>) / 100);
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease-in-out;
        }

        .progress-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-gray);
        }

        .progress-label {
            font-size: 0.9rem;
            color: var(--text-gray);
            margin-bottom: 1rem;
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

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
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
    </style>
</head>
<body>
    <?php include('include/header.php'); ?>
    <div class="container">
        <?php include('include/sidebar.php'); ?>
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Dashboard Analytics</h1>
                    <p class="page-subtitle">Welcome back, <?php echo htmlentities($adminData['fullname']); ?>. Here's what's happening with your complaint management system today.</p>
                </div>
                <div class="header-actions-right">
                    <a href="all-complaint.php" class="btn btn-secondary">
                        <i class="fas fa-eye"></i>
                        View All
                    </a>
                    <a href="notprocess-complaint.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        New Complaint
                    </a>
             
                </div>
            </div>

            <div class="stats-container animate-in">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $totusers; ?></div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-description">Registered system users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $totcom; ?></div>
                    <div class="stat-label">Total Complaints</div>
                    <div class="stat-description">All complaints received</div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $pendingcom; ?></div>
                    <div class="stat-label">Pending Complaints</div>
                    <div class="stat-description">Require immediate attention</div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $inprocesscom; ?></div>
                    <div class="stat-label">In Process</div>
                    <div class="stat-description">Currently being handled</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $closedcom; ?></div>
                    <div class="stat-label">Resolved</div>
                    <div class="stat-description">Successfully closed cases</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $totsubcategory; ?></div>
                    <div class="stat-label">Subcategories</div>
                    <div class="stat-description">Available complaint types</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $totstate; ?></div>
                    <div class="stat-label">States</div>
                    <div class="stat-description">Coverage areas</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $resolutionRate; ?>%</div>
                    <div class="stat-label">Resolution Rate</div>
                    <div class="stat-description">Success rate metric</div>
                </div>
            </div>

            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-clock"></i>
                            Recent Complaints
                        </h2>
                        <a href="all-complaint.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($recentComplaints) > 0): ?>
                            <div class="complaints-list">
                                <?php while ($complaint = mysqli_fetch_array($recentComplaints)): ?>
                                    <a href="complaint-details.php?cid=<?php echo $complaint['id']; ?>" class="complaint-item">
                                        <div class="complaint-avatar">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="complaint-details">
                                            <div class="complaint-title">
                                                Complaint #<?php echo $complaint['id']; ?> - <?php echo htmlentities($complaint['userName']); ?>
                                            </div>
                                            <div class="complaint-meta">
                                                <?php echo date('M d, Y - h:i A', strtotime($complaint['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="status-badge <?php 
                                            if ($complaint['status'] == null) echo 'status-pending';
                                            elseif ($complaint['status'] == 'in process') echo 'status-in-process';
                                            elseif ($complaint['status'] == 'closed') echo 'status-closed';
                                        ?>">
                                            <?php echo $complaint['status'] ?? 'Pending'; ?>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h3>No recent complaints</h3>
                                <p>All caught up! No new complaints to review.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-chart-pie"></i>
                                Resolution Rate
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="progress-container">
                                <svg class="progress-ring" viewBox="0 0 120 120">
                                    <circle class="progress-ring-circle"></circle>
                                    <circle class="progress-ring-fill"></circle>
                                </svg>
                                <div class="progress-text"><?php echo $resolutionRate; ?>%</div>
                                <div class="progress-label">Complaints Resolved</div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                                <div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);"><?php echo $closedcom; ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-gray);">Resolved</div>
                                </div>
                                <div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--error);"><?php echo $pendingcom + $inprocesscom; ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-gray);">Active</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-bolt"></i>
                                Quick Actions
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions">
                                <a href="notprocess-complaint.php" class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="action-content">
                                        <h3>Review Pending</h3>
                                        <p><?php echo $pendingcom; ?> complaints need attention</p>
                                    </div>
                                </a>

                                <a href="manage-users.php" class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="action-content">
                                        <h3>Manage Users</h3>
                                        <p>View and manage user accounts</p>
                                    </div>
                                </a>

                                <a href="between-date-complaintreport.php" class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div class="action-content">
                                        <h3>Generate Reports</h3>
                                        <p>Create detailed analytics reports</p>
                                    </div>
                                </a>

                                <a href="complaint-search.php" class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="action-content">
                                        <h3>Search Complaints</h3>
                                        <p>Find specific complaints quickly</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
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

        // Add animation delays for cards
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Update notification badge periodically
        setInterval(() => {
            fetch('get-notification-count.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.notification-badge');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                        } else {
                            const notificationBtn = document.querySelector('.notification-btn');
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.count;
                            notificationBtn.appendChild(newBadge);
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                })
                .catch(error => console.error('Error updating notifications:', error));
        }, 30000);

        // Auto-refresh stats every 5 minutes
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
<?php mysqli_close($con); ?>