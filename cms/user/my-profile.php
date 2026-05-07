<?php
session_start();
include('include/config.php');

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

// Get user ID from URL parameter or session
$userId = isset($_GET['uid']) ? intval($_GET['uid']) : intval($_SESSION["id"]);

// Security check: Make sure the logged-in user can only view their own profile
if ($userId != intval($_SESSION["id"])) {
    header("Location: dashboard.php");
    exit();
}

// Fetch user data
$userQuery = mysqli_query($con, "SELECT * FROM users WHERE id='$userId'");
$userData = mysqli_fetch_array($userQuery);

if (!$userData) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get user initials
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false) {
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
}

// Get user statistics
$statsQuery = mysqli_query($con, "
    SELECT 
        (SELECT COUNT(*) FROM tire_orders WHERE customer_id='$userId') as total_orders,
        (SELECT COUNT(*) FROM tbl_tire_complaints WHERE userId='$userId') as total_complaints,
        (SELECT COUNT(*) FROM tbl_customer_feedback WHERE userId='$userId') as total_feedback
");
$stats = mysqli_fetch_assoc($statsQuery);

// Get account creation date
$memberSince = date('F Y', strtotime($userData['regDate']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Customer Dashboard</title>
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
            --bg-light: #f9f9f9;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #555555;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(45deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-active: 0 12px 40px rgba(242, 128, 24, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--white);
            color: var(--dark-gray);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Decorative Background Elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -20%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(242, 128, 24, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -30%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(39, 174, 96, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
            animation: float 25s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(50px, -50px) rotate(5deg); }
            50% { transform: translate(0, -100px) rotate(-5deg); }
            75% { transform: translate(-50px, -50px) rotate(3deg); }
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }

        .navbar {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon img {
            height: 45px;
            width: auto;
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 2px solid;
            font-size: 0.9rem;
        }

        .btn-back {
            background: white;
            border-color: var(--primary-orange);
            color: var(--primary-orange);
        }

        .btn-back:hover {
            background: var(--primary-orange);
            color: white;
            transform: translateY(-2px);
        }

        .btn-logout {
            background: var(--gradient-3);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Profile Header */
        .profile-header {
            background: var(--gradient-1);
            border-radius: 24px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-active);
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .profile-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border: 4px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
            color: white;
            flex-shrink: 0;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .profile-meta {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border: 2px solid var(--border-gray);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-orange);
            box-shadow: var(--shadow-hover);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--orange-light);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-orange);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-orange);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.95rem;
            color: var(--text-gray);
            margin-top: 0.3rem;
        }

        /* Action Button */
        .action-button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn-action {
            background: var(--gradient-1);
            color: white;
            padding: 1.25rem 3rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-active);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 40px rgba(242, 128, 24, 0.4);
        }

        .btn-action:active {
            transform: translateY(-1px);
        }

        .btn-action i:last-child {
            transition: transform 0.3s ease;
        }

        .btn-action:hover i:last-child {
            transform: translateX(5px);
        }

        /* Card */
        .card {
            background: white;
            border: 2px solid var(--border-gray);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
        }

        .card:hover {
            border-color: var(--primary-orange);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            padding: 1.5rem 2rem;
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-gray);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--dark-gray);
        }

        .card-title i {
            color: var(--primary-orange);
        }

        .card-body {
            padding: 2rem;
        }

        /* Info Display Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            padding: 1.25rem;
            background: var(--bg-light);
            border-radius: 12px;
            border: 2px solid var(--border-gray);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            border-color: var(--primary-orange);
            background: var(--orange-light);
        }

        .info-label {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-gray);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-label i {
            color: var(--primary-orange);
            font-size: 1rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: var(--dark-gray);
            font-weight: 600;
            word-break: break-word;
        }

        .info-value.empty {
            color: var(--text-gray);
            font-style: italic;
            font-weight: 400;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .navbar {
                padding: 0 1rem;
                flex-wrap: wrap;
            }

            .nav-actions {
                width: 100%;
                justify-content: space-between;
                margin-top: 1rem;
            }

            .profile-content {
                flex-direction: column;
                text-align: center;
            }

            .profile-name {
                font-size: 2rem;
            }

            .profile-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .btn-action {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <div class="logo-icon">
                    <img src="atire.png" alt="Atire Logo">
                </div>
                <span class="logo-text">My Profile</span>
            </div>
            <div class="nav-actions">
                <a href="dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header fade-in">
            <div class="profile-content">
                <div class="profile-avatar"><?php echo $initials; ?></div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($userData['fullName']); ?></h1>
                    <p class="profile-email">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userData['userEmail']); ?>
                    </p>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Member since <?php echo $memberSince; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-id-card"></i>
                            <span>Customer Code: <?php echo htmlspecialchars($userData['customer_code']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-user-tag"></i>
                            <span>Customer ID: <?php echo htmlspecialchars($userData['cus_id']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="stats-grid fade-in">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['total_complaints']; ?></div>
                <div class="stat-label">Total Claims</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['total_feedback']; ?></div>
                <div class="stat-label">Feedback Given</div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="action-button-container fade-in">
            <a href="add_signature.php" class="btn-action">
                <i class="fas fa-user-edit"></i>
                <span>Add Signature</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Personal Information Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-user"></i>
                    Personal Information
                </h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user"></i>
                            Full Name
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($userData['fullName']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($userData['userEmail']); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user-tie"></i>
                            Contact Person
                        </div>
                        <div class="info-value <?php echo empty($userData['contact_person1_name']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['contact_person1_name']) ? htmlspecialchars($userData['contact_person1_name']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-phone-office"></i>
                            Office Number
                        </div>
                        <div class="info-value <?php echo empty($userData['contact_number1_office']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['contact_number1_office']) ? htmlspecialchars($userData['contact_number1_office']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-mobile-alt"></i>
                            Mobile Number
                        </div>
                        <div class="info-value <?php echo empty($userData['contact_number1_mobile']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['contact_number1_mobile']) ? htmlspecialchars($userData['contact_number1_mobile']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-at"></i>
                            Contact Email
                        </div>
                        <div class="info-value <?php echo empty($userData['email_address1']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['email_address1']) ? htmlspecialchars($userData['email_address1']) : 'Not provided'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Information Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-building"></i>
                    Company Information
                </h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-file-signature"></i>
                            Company Registration Number
                        </div>
                        <div class="info-value <?php echo empty($userData['company_rn']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['company_rn']) ? htmlspecialchars($userData['company_rn']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-receipt"></i>
                            Business Registration Number
                        </div>
                        <div class="info-value <?php echo empty($userData['business_registration_number']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['business_registration_number']) ? htmlspecialchars($userData['business_registration_number']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-hashtag"></i>
                            TIN Number
                        </div>
                        <div class="info-value <?php echo empty($userData['tin_number']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['tin_number']) ? htmlspecialchars($userData['tin_number']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-globe"></i>
                            Country
                        </div>
                        <div class="info-value <?php echo empty($userData['Country']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['Country']) ? htmlspecialchars($userData['Country']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item full-width">
                        <div class="info-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Registered Address
                        </div>
                        <div class="info-value <?php echo empty($userData['registerd_Address']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['registerd_Address']) ? htmlspecialchars($userData['registerd_Address']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item full-width">
                        <div class="info-label">
                            <i class="fas fa-shipping-fast"></i>
                            Delivery Address
                        </div>
                        <div class="info-value <?php echo empty($userData['delivery_address']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['delivery_address']) ? htmlspecialchars($userData['delivery_address']) : 'Not provided'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Terms Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-file-contract"></i>
                    Business Terms & Conditions
                </h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-check"></i>
                            Payment Terms
                        </div>
                        <div class="info-value <?php echo empty($userData['standard_payment_term']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['standard_payment_term']) ? htmlspecialchars($userData['standard_payment_term']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-dollar-sign"></i>
                            Functional Currency
                        </div>
                        <div class="info-value <?php echo empty($userData['functional_currency']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['functional_currency']) ? htmlspecialchars($userData['functional_currency']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-percent"></i>
                            Payment Rate
                        </div>
                        <div class="info-value <?php echo empty($userData['payment_rate']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['payment_rate']) ? htmlspecialchars($userData['payment_rate']) . '%' : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-truck"></i>
                            Incoterm
                        </div>
                        <div class="info-value <?php echo empty($userData['incoterm']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['incoterm']) ? htmlspecialchars($userData['incoterm']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-anchor"></i>
                            Port of Discharge
                        </div>
                        <div class="info-value <?php echo empty($userData['port_of_discharge']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['port_of_discharge']) ? htmlspecialchars($userData['port_of_discharge']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-item full-width">
                        <div class="info-label">
                            <i class="fas fa-certificate"></i>
                            Required Certificates
                        </div>
                        <div class="info-value <?php echo empty($userData['required_certificate']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['required_certificate']) ? nl2br(htmlspecialchars($userData['required_certificate'])) : 'Not provided'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Manager Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-user-shield"></i>
                    Account Management
                </h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user-tie"></i>
                            Account Manager Name
                        </div>
                        <div class="info-value <?php echo empty($userData['acm_name']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['acm_name']) ? htmlspecialchars($userData['acm_name']) : 'Not assigned'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-id-badge"></i>
                            Account Manager Code
                        </div>
                        <div class="info-value <?php echo empty($userData['account_manager_code']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['account_manager_code']) ? htmlspecialchars($userData['account_manager_code']) : 'Not assigned'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-link"></i>
                            Account Manager Reference
                        </div>
                        <div class="info-value <?php echo empty($userData['acm_ref']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['acm_ref']) ? htmlspecialchars($userData['acm_ref']) : 'Not assigned'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user-check"></i>
                            Account Status
                        </div>
                        <div class="info-value">
                            <?php echo $userData['status'] == 1 ? 'Active' : 'Inactive'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Account Information
                </h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar-plus"></i>
                            Registration Date
                        </div>
                        <div class="info-value">
                            <?php echo date('F j, Y - g:i A', strtotime($userData['regDate'])); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-clock"></i>
                            Last Updated
                        </div>
                        <div class="info-value">
                            <?php echo !empty($userData['updationDate']) ? date('F j, Y - g:i A', strtotime($userData['updationDate'])) : 'Never updated'; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-fingerprint"></i>
                            User ID
                        </div>
                        <div class="info-value"><?php echo $userId; ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-barcode"></i>
                            Customer Code
                        </div>
                        <div class="info-value <?php echo empty($userData['customer_code']) ? 'empty' : ''; ?>">
                            <?php echo !empty($userData['customer_code']) ? htmlspecialchars($userData['customer_code']) : 'Not assigned'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fade in animations
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.fade-in').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease-out';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($con); ?>