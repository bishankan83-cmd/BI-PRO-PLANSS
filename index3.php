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
        'tire_build' => 'tire_building_dashboard.php',
        'pro_qr' => '/prodution_barcode/Tire Building3/',
        'tire_fin' => '/prodution_barcode/Tire Finishing 6/',
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* Notice Styles */
        .notice-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 2px solid #d32f2f;
            border-radius: 8px;
            background-color: #fff;
            font-family: 'Iskoola Pota', 'FMAbhaya', sans-serif;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .notice-header {
            text-align: center;
            color: #d32f2f;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .notice-body {
            font-size: 18px;
            line-height: 1.6;
            text-align: justify;
            color: #333;
        }

        .notice-footer {
            margin-top: 20px;
            text-align: center;
            font-style: italic;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .important {
            color: #d32f2f;
            font-weight: bold;
            background-color: rgba(211, 47, 47, 0.1);
            padding: 2px 5px;
            border-radius: 3px;
        }

        /* Login Form Styles */
        body {
            background-image: url('atire2.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .auth-box-w {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .logo-w img {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-addon {
            position: absolute;
            left: 10px;
            color: #F28018;
        }

        .form-control {
            width: 100%;
            padding: 12px 12px 12px 35px;
            border: 2px solid #F28018;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #000;
            box-shadow: 0 0 0 2px rgba(242, 128, 24, 0.2);
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #F28018;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #000;
            transform: translateY(-1px);
        }

        .error-message {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
   

    <!-- Login Form -->
    <div class="auth-box-w">
        <div class="logo-w">
            <a href="#"><img alt="Company Logo" src="atire.png"></a>
        </div>
        <h4 class="auth-header">Login</h4>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="User_nm">Username</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input class="form-control" name="User_nm" id="User_nm" placeholder="Enter your username" type="text" required>
                </div>
            </div>
            <div class="form-group">
                <label for="Paswd">Password</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input class="form-control" name="Paswd" id="Paswd" placeholder="Enter your password" type="password" required>
                </div>
            </div>
            <div class="buttons-w">
                <button type="submit" name="login" class="btn btn-primary">Log me in</button>
            </div>
        </form>
    </div>
</body>
</html>