<?php
session_start();
include("include/config.php");

if(isset($_POST['submit']))
{
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = md5($_POST['password']);
    
    $ret = mysqli_query($con, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
    $num = mysqli_fetch_array($ret);
    
    if($num > 0)
    {
        $_SESSION['alogin'] = $num['username'];
        $_SESSION['aid'] = $num['id'];
        $_SESSION['role'] = $num['role'];
        $_SESSION['fullname'] = $num['fullname'];
        
        // Role-based redirection
        if($num['role'] == 'admin')
        {
            header("location:dashboard.php");
            exit();
        }
        elseif($num['role'] == 'acm')
        {
            header("location:account-manager-dashboard.php");
            exit();
        }
        elseif($num['role'] == 'Marketing')
        {
            header("location:Marketing.php");
            exit();
        }
        elseif($num['role'] == 'export')
        {
            header("location:export.php");
            exit();
        }

         elseif($num['role'] == 'finace')
        {
            header("location:finace.php");
            exit();
        }

         elseif($num['role'] == 'qad_cms')
        {
            header("location:qad_cms.php");
            exit();
        }

        elseif($num['role'] == 'mar_ex')
        {
            header("location:mar_ex.php");
            exit();
        }
        
        
    }
    else
    {
        echo "<script>alert('Invalid username or password');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Admin Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --warning: #443f38ff;
            --error: #e74c3c;
            --text-gray: #555555;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
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
            line-height: 1.6;
            color: var(--dark-gray);
            background: linear-gradient(135deg, var(--bg-light) 0%, rgba(242, 128, 24, 0.05) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .auth-wrapper {
            max-width: 450px;
            width: 100%;
            padding: 2rem;
            margin: 2rem;
            position: relative;
            z-index: 2;
        }

        .auth-content {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--shadow-soft);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .auth-content:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .auth-content h4 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1rem;
        }

        .auth-content h4 span {
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-content hr {
            border: 0;
            height: 1px;
            background: var(--border-gray);
            margin: 1rem 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 1px solid var(--border-gray);
            border-radius: 50px;
            font-size: 1rem;
            color: var(--dark-gray);
            background: var(--bg-light);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px var(--orange-light);
            background: white;
        }

        .form-control::placeholder {
            color: var(--text-gray);
            opacity: 0.7;
        }

        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            position: relative;
            overflow: hidden;
            width: 100%;
            justify-content: center;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-active);
        }

        .text-muted {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin: 1rem 0;
        }

        .text-muted a {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .text-muted a:hover {
            color: var(--secondary-orange);
            text-decoration: underline;
        }

        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .back-home:hover {
            color: var(--secondary-orange);
            transform: translateY(-2px);
        }

        .back-home i {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .auth-wrapper {
                padding: 1rem;
                margin: 1rem;
            }

            .auth-content {
                padding: 2rem;
            }

            .auth-content h4 {
                font-size: 1.5rem;
            }

            .form-control {
                padding: 0.8rem 1.2rem;
                font-size: 0.9rem;
            }

            .btn {
                padding: 0.8rem 2rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-content text-center">
            <h4>Customer Service <hr /><span>Admin Login</span></h4>
            <form method="post">
                <div class="card-body">
                    <div class="form-group mb-3">
                        <input class="form-control" id="username" name="username" type="text" placeholder="Username" required />
                    </div>
                    <div class="form-group mb-4">
                        <input class="form-control" id="password" name="password" type="password" placeholder="Password" required />
                    </div>
                    <button class="btn btn-primary mb-4" type="submit" name="submit">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                    <p class="mb-2 text-muted">Forgot password? <a href="reset-password.php" class="f-w-400">Reset</a></p>
                    <a class="back-home" href="../index.php">
                        <i class="fa fa-home" aria-hidden="true"></i> Back Home
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>