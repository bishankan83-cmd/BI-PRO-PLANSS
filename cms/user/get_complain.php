<?php
session_start();
include('include/config.php');
error_reporting(0);

if (strlen($_SESSION['id']) == 0) {
    header('location:index.php');
    exit;
}

date_default_timezone_set('Asia/Kolkata');

// Fetch user data
$userId = $_SESSION['id'];
$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
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

// Fetch complaints for the logged-in user
$query = "SELECT * FROM tbl_tire_complaints WHERE userId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service Portal - View Tire Complaints</title>
    <!-- Bootstrap CSS for layout -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* Complaint Card Styles */
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

        .complaint-card {
            background: var(--white);
            border-radius: 0.75rem;
            border-left: 4px solid var(--primary-orange);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .complaint-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .complaint-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .complaint-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-orange);
            margin: 0;
        }

        .complaint-header span {
            font-size: 0.9rem;
            color: var(--text-gray);
        }

        .complaint-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .detail-item {
            font-size: 0.9rem;
            color: var(--dark-gray);
        }

        .detail-item strong {
            font-weight: 600;
            color: var(--dark-gray);
        }

        .file-link {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .file-link:hover {
            color: var(--secondary-orange);
            text-decoration: underline;
        }

        .no-complaints {
            text-align: center;
            color: var(--text-gray);
            font-size: 1rem;
            padding: 2rem;
            background: var(--orange-light);
            border-radius: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
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

            .complaint-details {
                grid-template-columns: 1fr;
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
                <input type="text" class="search-input" placeholder="Search complaints...">
            </div>
            
            <button class="notification-btn">
                <i class="fas fa-bell fa-lg"></i>
                <span class="notification-badge"></span>
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
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="register-complaint.php">
                            <i class="fas fa-plus-circle"></i>
                            New Complaint
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="complaint-history.php" class="active">
                            <i class="fas fa-list"></i>
                            My Complaints
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
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
                    <h1 class="page-title">View Tire Complaints</h1>
                    <p class="page-subtitle">Review all your submitted tire complaints</p>
                </div>
                <div class="header-actions-right">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                    <a href="register-complaint.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        New Complaint
                    </a>
                </div>
            </div>

            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list"></i>
                        Your Tire Complaints
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($result->num_rows > 0) { ?>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <div class="complaint-card animate-in">
                                <div class="complaint-header">
                                    <h5>Complaint ID: <?php echo htmlspecialchars($row['id']); ?></h5>
                                    <span>Submitted: <?php echo htmlspecialchars($row['created_at']); ?></span>
                                </div>
                                <div class="complaint-details">
                                    <div class="detail-item">
                                        <strong>Serial Number:</strong> <?php echo htmlspecialchars($row['serial_number']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Tire Size:</strong> <?php echo htmlspecialchars($row['tire_size']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Purchase Date:</strong> <?php echo htmlspecialchars($row['purchase_date']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Purchase Location:</strong> <?php echo htmlspecialchars($row['purchase_location']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Invoice Number:</strong> <?php echo htmlspecialchars($row['invoice_number']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Warranty Period:</strong> <?php echo htmlspecialchars($row['warranty_period']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Vehicle Make/Model:</strong> <?php echo htmlspecialchars($row['vehicle_make_model']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Vehicle Year:</strong> <?php echo htmlspecialchars($row['vehicle_year']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Usage Type:</strong> <?php echo htmlspecialchars($row['usage_type']); ?>
                                        <?php if (!empty($row['usage_type_other'])) { ?>
                                            (<?php echo htmlspecialchars($row['usage_type_other']); ?>)
                                        <?php } ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Nature of Complaint:</strong> <?php echo htmlspecialchars($row['nature_complaint']); ?>
                                        <?php if (!empty($row['nature_other'])) { ?>
                                            (Other: <?php echo htmlspecialchars($row['nature_other']); ?>)
                                        <?php } ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Detailed Description:</strong> <?php echo nl2br(htmlspecialchars($row['detailed_description'])); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Mileage/Hours:</strong> <?php echo htmlspecialchars($row['mileage_hours']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Duration Before Problem:</strong> <?php echo htmlspecialchars($row['duration_before_problem']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Operating Conditions:</strong> <?php echo htmlspecialchars($row['operating_conditions']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Impact:</strong> <?php echo htmlspecialchars($row['impact']); ?>
                                        <?php if (!empty($row['impact_other'])) { ?>
                                            (Other: <?php echo htmlspecialchars($row['impact_other']); ?>)
                                        <?php } ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Daily Usage:</strong> <?php echo htmlspecialchars($row['daily_usage']); ?> hours
                                    </div>
                                    <div class="detail-item">
                                        <strong>Load Capacity:</strong> <?php echo htmlspecialchars($row['load_capacity']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Surface Conditions:</strong> <?php echo htmlspecialchars($row['surface_conditions']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Temperature Conditions:</strong> <?php echo htmlspecialchars($row['temperature_conditions']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Speed of Operation:</strong> <?php echo htmlspecialchars($row['speed_operation']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Documentation:</strong> <?php echo htmlspecialchars($row['documentation']); ?>
                                        <?php if (!empty($row['other_documentation'])) { ?>
                                            (Other: <?php echo htmlspecialchars($row['other_documentation']); ?>)
                                        <?php } ?>
                                    </div>
                                    <?php if (!empty($row['complaint_file'])) { ?>
                                        <div class="detail-item">
                                            <strong>Attached File:</strong> 
                                            <a href="complaintdocs/<?php echo htmlspecialchars($row['complaint_file']); ?>" class="file-link" target="_blank">
                                                <i class="fas fa-file"></i> View File
                                            </a>
                                        </div>
                                    <?php } ?>
                                    <div class="detail-item">
                                        <strong>Previous Actions:</strong> <?php echo nl2br(htmlspecialchars($row['previous_actions'])); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Resolution Requested:</strong> <?php echo htmlspecialchars($row['resolution_requested']); ?>
                                        <?php if (!empty($row['resolution_other'])) { ?>
                                            (Other: <?php echo htmlspecialchars($row['resolution_other']); ?>)
                                        <?php } ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Additional Comments:</strong> <?php echo nl2br(htmlspecialchars($row['additional_comments'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="no-complaints">No complaints found.</p>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        searchInput?.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            console.log('Searching for:', query);
            // Add client-side filtering for complaints if needed
            document.querySelectorAll('.complaint-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
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

        document.querySelectorAll('.card, .complaint-card').forEach(el => {
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

        console.log('View Tire Complaints page loaded successfully!');
    </script>
</body>
</html>
<?php $stmt->close(); ?>