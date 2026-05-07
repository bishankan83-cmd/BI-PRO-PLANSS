<?php

// Fetch admin details
$adminId = intval($_SESSION["aid"]);
$adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
$adminData = mysqli_fetch_array($adminQuery);

// Fetch notification count for pending and in-process complaints
$query3 = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE status IS NULL");
$pendingcom = mysqli_num_rows($query3);
$query4 = mysqli_query($con, "SELECT id FROM tbl_tire_complaints WHERE status='pending'");
$inprocesscom = mysqli_num_rows($query4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            --gradient-3: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --ring-orange: 0 0 0 3px rgba(242, 128, 24, 0.2);
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
            text-decoration: none;
        }

        .logo-image {
            width: 14000x;
            height: 40px;
            object-fit: contain;
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

        .notification-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            min-width: 1.25rem;
            height: 1.25rem;
            background: var(--error);
            color: var(--white);
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .logout-btn {
            background: var(--gradient-3);
            color: var(--white);
            padding: 0.75rem;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            background: var(--error);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        @media (max-width: 768px) {
            .menu-btn {
                display: block;
            }

            .search-box {
                display: none;
            }

            .user-details {
                display: none;
            }

            .logout-btn {
                padding: 0.5rem;
            }

            .logo-image {
                width: 60px;
                height: 60px;
            }

            .brand-text {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <button class="menu-btn" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="dashboard.php" class="logo-container">
                <img src="/atire.png" alt="Logo" class="logo-image">
                <span class="brand-text">Customer Service</span>
            </a>
        </div>

        <div class="header-actions">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search complaints, users...">
            </div>

            <a href="#" class="notification-btn">
                <i class="fas fa-bell"></i>
                <?php if ($pendingcom + $inprocesscom > 0): ?>
                <span class="notification-badge"><?php echo $pendingcom + $inprocesscom; ?></span>
                <?php endif; ?>
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

            <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>
</body>
</html>