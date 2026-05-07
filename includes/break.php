


        <!DOCTYPE html>
<html>
<head>
    <style>
        .blinking {
            font-weight: bold;
            animation: blinkingText 1.5s infinite;
        }

        @keyframes blinkingText {
            0% { color: red; }
            50% { color: transparent; }
            100% { color: red; }
        }
    </style>
</head>
<body>
    <?php
    $message = "Production temporarily halted ( 7th Sunday to 15th Sunday)!";
    echo "<p class='blinking'>$message</p>";
    ?>
</body>
</html>



<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .notification {
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .notification h1 {
            font-size: 28px;
            color: #343a40;
            margin-bottom: 15px;
        }
        .notification p {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .notification .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .notification .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="notification">
        <h1>System Update</h1>
        <p>The system is currently undergoing maintenance. We apologize for the inconvenience. Please check back later.</p>
        <a href="#" class="btn">Learn More</a>
    </div>
</body>
</html>
