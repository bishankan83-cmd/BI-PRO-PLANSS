<?php
// Delay for a few seconds before redirecting
$redirectURL = "dashboard.php";
$delay = 3; // Delay in seconds

echo "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .message-box {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
        }
        h2 {
            color: #4CAF50;
        }
        p {
            font-size: 16px;
            color: #333;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = '$redirectURL';
        }, " . ($delay * 1000) . ");
    </script>
</head>
<body>
    <div class='message-box'>
        <h2>Success!</h2>
        <p>Data inserted successfully.</p>
        <p>You will be redirected shortly...</p>
    </div>
</body>
</html>";
?>
