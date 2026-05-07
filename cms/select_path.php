<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Navigation Hub</title>
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
            --ring-orange: 0 0 0 3px rgba(242, 128, 24, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: var(--white);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px;
            box-shadow: var(--shadow-md);
            text-align: center;
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--border-gray);
        }

        h1 {
            color: var(--dark-gray);
            margin-bottom: 30px;
            font-size: 2.5em;
            font-weight: 600;
        }

        p {
            color: var(--text-gray);
            margin-bottom: 40px;
            font-size: 1.1em;
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }

        .nav-button {
            display: inline-block;
            padding: 15px 40px;
            background: var(--gradient-1);
            color: var(--white);
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
            min-width: 200px;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .nav-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .nav-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(242, 128, 24, 0.4);
        }

        .nav-button:hover::before {
            left: 100%;
        }

        .nav-button:active {
            transform: translateY(-1px);
        }

        .nav-button:focus {
            outline: none;
            box-shadow: var(--ring-orange), var(--shadow-md);
        }

        .button-1 {
            background: linear-gradient(135deg, var(--red-accent) 0%, var(--error) 100%);
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3);
        }

        .button-1:hover {
            box-shadow: 0 8px 25px rgba(255, 71, 87, 0.4);
        }

        .button-2 {
            background: linear-gradient(135deg, var(--success) 0%, var(--success) 100%);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }

        .button-2:hover {
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
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
            margin: 0;
        }

        .user-details span {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }

        .message.success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .message.error {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }

        @media (max-width: 768px) {
            .user-details {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .nav-button {
                padding: 12px 30px;
                font-size: 1.1em;
                min-width: 180px;
            }
        }

        .footer {
            margin-top: 30px;
            color: var(--text-gray);
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Navigation Hub</h1>
        <p>Choose your destination to navigate to different PHP pages</p>
        
        <div class="button-group">
            <a href="user/index.php" class="nav-button button-1">
                Customer Complain
            </a>
            
            <a href="user/index2.php" class="nav-button button-2">
                Placed Order
            </a>
        </div>
        
        <div class="footer">
            <p>Click any button to navigate to the corresponding PHP page</p>
        </div>
    </div>
</body>
</html>