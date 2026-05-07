<?php
include('include/config.php');
error_reporting(0);
if(isset($_POST['submit']))
{
    $fullname=$_POST['fullname'];
    $email=$_POST['email'];
    $password=md5($_POST['password']);
    $contactno=$_POST['contactno'];
    $status=1;
    $query=mysqli_query($con,"insert into users(fullName,userEmail,password,contactNo,status) values('$fullname','$email','$password','$contactno','$status')");
    
    echo "<script>alert('Registration successful. Now you can login'); document.location ='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | User Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #F28018 0%, #e67e22 50%, #f39c12 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: flex;
            min-height: 600px;
        }

        .left-section {
            background: linear-gradient(45deg, #343a40, #555555);
            padding: 50px 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #ffffff;
            text-align: center;
        }

        .logo-img {
            max-width: 350px;
            height: auto;
            margin-bottom: 0.2rem;
        }

        .left-section h2 {
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .left-section p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .right-section {
            padding: 50px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h3 {
            color: #343a40;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #555555;
            font-size: 14px;
        }

        .error-message {
            background: #ff4757;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #F28018;
            background: #ffffff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #CCCCCC;
            font-size: 16px;
        }

        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #F28018, #e67e22);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #e67e22, #F28018);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(242, 128, 24, 0.3);
        }

        .form-links {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-links a {
            color: #F28018;
            text-decoration: none;
            font-size: 14px;
        }

        .form-links a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
        }

        .back-home a {
            color: #555555;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 15px;
            background: #f0f0f0;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            background: #e0e0e0;
            color: #333333;
            text-decoration: none;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                margin: 10px;
            }
            
            .left-section {
                padding: 30px 20px;
            }
            
            .right-section {
                padding: 30px 25px;
            }
            
            .logo-img {
                max-width: 70px;
                max-height: 70px;
            }
            
            .left-section h2 {
                font-size: 20px;
            }
            
            .form-header h3 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Section -->
        <div class="left-section">
            <div class="logo-container">
                <img src="atire.png" alt="ATIRE Logo" class="logo-img">
            </div>
            <h2>Complaint Management System</h2>
            <p>Professional customer service solution for efficient complaint handling and enhanced customer satisfaction.</p>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="form-header">
                <h3>Create Account</h3>
                <p>Join our platform today</p>
            </div>

            <form method="post">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Full Name" name="fullname" required>
                    <span class="icon">👤</span>
                </div>

                <div class="form-group">
                    <input type="email" class="form-control" placeholder="Email ID" id="email" onBlur="userAvailability()" name="email" required>
                    <span class="icon">📧</span>
                    <span id="user-availability-status1" style="font-size:12px; display: block; margin-top: 5px;"></span>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" maxlength="10" name="contactno" placeholder="Contact No" required>
                    <span class="icon">📞</span>
                </div>

                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                    <span class="icon">🔒</span>
                </div>

                <button type="submit" name="submit" class="btn-register">Register</button>
            </form>

            <div class="form-links">
                <a href="index.php">Already have an account? Sign in</a>
            </div>

            <div class="back-home">
                <a href="../index.php">🏠 Back to Homepage</a>
            </div>
        </div>
    </div>

    <!-- Required Js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function userAvailability() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "check_availability.php",
                data: 'email='+$("#email").val(),
                type: "POST",
                success: function(data) {
                    $("#user-availability-status1").html(data);
                    $("#loaderIcon").hide();
                },
                error: function() {}
            });
        }
    </script>
</body>
</html>