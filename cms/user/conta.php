<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Container</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
            animation: fadeIn 0.5s ease-in;
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
        
        .container h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2em;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        
        .container p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .content-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Your PHP Page</h1>
        
        <p>This is a modern, responsive container that you can use in your PHP application. It features a clean design with gradient backgrounds and smooth animations.</p>
        
        <?php
        // PHP code example
        $currentDate = date('F j, Y');
        $currentTime = date('g:i A');
        ?>
        
        <div class="content-section">
            <h2 style="color: #667eea; margin-bottom: 10px;">Server Information</h2>
            <p><strong>Current Date:</strong> <?php echo $currentDate; ?></p>
            <p><strong>Current Time:</strong> <?php echo $currentTime; ?></p>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        </div>
        
        <a href="#" class="button">Learn More</a>
    </div>
</body>
</html>