<?php
// Configuration and Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_task_managemen');

// Initialize session
session_start();

// Database connection function
function getDbConnection() {
    try {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($connection->connect_error) {
            throw new Exception("Connection failed: " . $connection->connect_error);
        }
        return $connection;
    } catch (Exception $e) {
        error_log($e->getMessage());
        die("A database error occurred. Please try again later.");
    }
}

// Login handling
function handleLogin($connection) {
    if (!isset($_POST['login'])) return null;

    $uemail = $connection->real_escape_string($_POST['User_nm'] ?? '');
    $pass = $_POST['Paswd'] ?? '';

    if (empty($uemail) || empty($pass)) {
        return "Please fill in all fields";
    }

    $stmt = $connection->prepare("SELECT * FROM emp_login WHERE user_id = ? AND status = '1'");
    $stmt->bind_param("s", $uemail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($pass, $user['pswd'])) {
        return "Invalid credentials";
    }

    // Set session variables
    $_SESSION['user'] = $user['id'];
    $_SESSION['emp_name'] = $user['emp_name'];
    $_SESSION['emp_pro'] = $user['emp_pro'];
    $_SESSION['User_type'] = $user['user_role'];

    // Redirect based on user role
    $redirects = [
        'qmanager' => 'qad_manager.php',
        'qad' => 'qad.php',
        'QR_GEN' => 'QR_GENN/qr_system_dash.php',
        'tire_build' => 'tire_building_dashboard.php',
        'pro_qr' => '/prodution_barcode/Tire Building3/',
        'tire_fin' => '/prodution_barcode/Tire Finishing 6/',
        'qad_ent' => '/qad/dashboard.php',
        'default' => 'dashboard.php'
    ];

    $redirect = $redirects[$_SESSION['User_type']] ?? $redirects['default'];
    header("Location: $redirect");
    exit();
}

// Initialize connection and handle login
$connection = getDbConnection();
$error = handleLogin($connection);
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BI PRO PLAN S LOGIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary-color: #F28018;
            --primary-dark: #D16000;
            --primary-light: #FFB067;
            --black: #000000;
            --white: #FFFFFF;
            --gray-dark: #333333;
            --gray-medium: #666666;
            --gray-light: #EEEEEE;
            --error-color: #d32f2f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
            color: var(--gray-dark);
            background-color: #f5f5f5;
        }

        .background-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -2;
        }

        .tire-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('atire89.png') no-repeat center center;
            background-size: cover;
            filter: brightness(0.7) contrast(1.1);
            z-index: -2;
        }

        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.7) 50%, rgba(0,0,0,0.85) 100%);
            z-index: -1;
        }

        .page-container {
            display: flex;
            height: 100vh;
            padding: 0;
            position: relative;
        }

        .branding-column {
            flex: 0 0 55%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .circular-accent {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            border: 2px solid rgba(242, 128, 24, 0.3);
            top: -150px;
            right: -150px;
        }

        .circular-accent-2 {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            border: 2px solid rgba(242, 128, 24, 0.2);
            bottom: -100px;
            left: -100px;
        }

        .tire-icon {
            width: 120px;
            height: 120px;
            background: rgba(242, 128, 24, 0.9);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(242, 128, 24, 0.3);
        }

        .tire-icon i {
            font-size: 60px;
            color: var(--white);
        }

        .branding-title {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .branding-subtitle {
            font-size: 18px;
            font-weight: 300;
            margin-bottom: 40px;
            text-align: center;
            max-width: 80%;
            line-height: 1.6;
            color: var(--gray-light);
        }

        .feature-list {
            list-style-type: none;
            width: 100%;
            max-width: 500px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-left: 15px;
        }

        .feature-icon {
            background: rgba(242, 128, 24, 0.9);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
            box-shadow: 0 5px 15px rgba(242, 128, 24, 0.3);
            flex-shrink: 0;
        }

        .feature-icon i {
            color: var(--white);
            font-size: 24px;
        }

        .feature-text {
            font-size: 16px;
            line-height: 1.5;
        }

        .feature-text strong {
            display: block;
            margin-bottom: 5px;
            color: var(--primary-light);
            font-size: 18px;
        }

        .login-column {
            flex: 0 0 45%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: var(--white);
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            padding: 40px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-text h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--gray-dark);
        }

        .welcome-text p {
            font-size: 16px;
            color: var(--gray-medium);
        }

        .error-message {
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--error-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .error-message i {
            margin-right: 10px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray-dark);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: var(--primary-color);
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 16px 16px 16px 45px;
            border: 2px solid var(--gray-light);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.2);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            user-select: none;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-light);
            border-radius: 4px;
            margin-right: 10px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .custom-checkbox i {
            color: var(--white);
            font-size: 12px;
            visibility: hidden;
        }

        #remember:checked + .custom-checkbox {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        #remember:checked + .custom-checkbox i {
            visibility: visible;
        }

        #remember {
            position: absolute;
            opacity: 0;
        }

        .checkbox-label {
            cursor: pointer;
            font-size: 14px;
            color: var(--gray-medium);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            color: var(--white);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(242, 128, 24, 0.3);
        }

        .btn-login i {
            margin-right: 10px;
            font-size: 18px;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(242, 128, 24, 0.4);
        }

        .footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 14px;
            color: var(--gray-medium);
        }

        .accent-circle {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(242, 128, 24, 0.05) 0%, rgba(242, 128, 24, 0.01) 100%);
            z-index: -1;
        }

        .accent-circle-1 { top: 20%; right: -100px; }
        .accent-circle-2 { bottom: 10%; left: -100px; }

        .login-decoration {
            position: absolute;
            bottom: 60px;
            right: 40px;
            width: 80px;
            height: 80px;
            background: url('atire.png') no-repeat center center;
            background-size: contain;
            opacity: 0.1;
        }

        @media (max-width: 1200px) {
            .branding-column { flex: 0 0 50%; }
            .login-column { flex: 0 0 50%; }
        }

        @media (max-width: 992px) {
            .page-container { flex-direction: column; overflow-y: auto; height: auto; min-height: 100vh; }
            .branding-column { flex: 0 0 auto; min-height: 50vh; padding: 40px 20px; }
            .login-column { flex: 0 0 auto; min-height: 50vh; box-shadow: 0 -10px 30px rgba(0,0,0,0.1); }
            .tire-icon { width: 100px; height: 100px; }
            .tire-icon i { font-size: 50px; }
            .branding-title { font-size: 36px; }
            .feature-list { max-width: 100%; }
        }

        @media (max-width: 576px) {
            .branding-column { display: none; }
            .login-column {
                flex: 1;
                min-height: 100vh;
                background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('atire89.png') no-repeat center center;
                background-size: cover;
            }
            .login-container {
                background: rgba(255,255,255,0.95);
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            .welcome-text h2 { font-size: 24px; }
        }

        /* Loading spinner */
        .loading {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            position: relative;
        }

        .loading-spinner:before,
        .loading-spinner:after {
            content: '';
            position: absolute;
            border-radius: 50%;
        }

        .loading-spinner:before {
            width: 100%; height: 100%;
            background-image: linear-gradient(90deg, var(--primary-color) 0%, #fff 100%);
            animation: spin 0.5s infinite linear;
        }

        .loading-spinner:after {
            width: 90%; height: 90%;
            background-color: #000;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Background Container -->
    <div class="background-container">
        <div class="tire-background"></div>
        <div class="background-overlay"></div>
    </div>

    <!-- Main Page Container -->
    <div class="page-container">

        <!-- Branding Column -->
        <div class="branding-column">
            <div class="circular-accent"></div>
            <div class="circular-accent-2"></div>

            <div class="tire-icon">
                <i class="fas fa-tire"></i>
            </div>

            <h1 class="branding-title">BI PRO PLAN S</h1>
            <p class="branding-subtitle">Advanced Production Management System for Tire Manufacturing Excellence</p>

            <ul class="feature-list">
                <li class="feature-item">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="feature-text">
                        <strong>Production Analytics</strong>
                        Real-time monitoring and analysis of tire manufacturing metrics
                    </div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="feature-text">
                        <strong>Quality Assurance</strong>
                        Comprehensive quality control and defect tracking system
                    </div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon"><i class="fas fa-cogs"></i></div>
                    <div class="feature-text">
                        <strong>Process Automation</strong>
                        Streamlined workflows and automated production scheduling
                    </div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon"><i class="fas fa-database"></i></div>
                    <div class="feature-text">
                        <strong>Inventory Management</strong>
                        Precise tracking of materials and finished tire inventory
                    </div>
                </li>
            </ul>
        </div>

        <!-- Login Column -->
        <div class="login-column">
            <div class="accent-circle accent-circle-1"></div>
            <div class="accent-circle accent-circle-2"></div>
            <div class="login-decoration"></div>

            <div class="login-container">
                <div class="logo-container">
                    <img src="atire.png" alt="Company Logo">
                </div>

                <div class="welcome-text">
                    <h2>Welcome Back</h2>
                    <p>Sign in to access your dashboard</p>
                </div>

                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>

                <form action="" method="post" id="loginForm">
                    <div class="form-group">
                        <label for="User_nm">Username</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="User_nm" name="User_nm" class="form-control" placeholder="Enter your username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Paswd">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="Paswd" name="Paswd" class="form-control" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" class="custom-checkbox">
                            <i class="fas fa-check"></i>
                        </label>
                        <span class="checkbox-label">Remember me</span>
                    </div>

                    <button type="submit" name="login" class="btn-login" id="loginButton">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
            </div>

            <div class="footer">
                &copy; <?php echo date('Y'); ?> BI PRO PLAN S. All rights reserved.
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading" id="loadingSpinner">
        <div class="loading-spinner"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var loginForm = document.getElementById('loginForm');
        var loadingSpinner = document.getElementById('loadingSpinner');

        loginForm.addEventListener('submit', function() {
            loadingSpinner.style.display = 'flex';
        });

        var passwordInput = document.getElementById('Paswd');
        passwordInput.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === ' ') {
                e.preventDefault();
                this.type = this.type === 'password' ? 'text' : 'password';
            }
        });
    });
    </script>
</body>
</html>