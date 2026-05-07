<?php
// Replace these variables with your actual database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a database connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if there is data in the daily_plan_data1 table
$count = 1; // Example variable, replace with actual count check
if ($count > 0) {
    echo '<style>
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0; }
            100% { opacity: 1; }
        }

        .blink {
            animation: blink 1s infinite;
        }
      </style>';
}

// Close the database connection
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Base styling for the body */
        body {
            background-color: #FFFFFF;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Styling for the Dashboard container */
        .dashboard-container {
            background-color: #F28018;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 1200px;
        }

        .dashboard-header {
            background-color: #000000;
            color: #FFFFFF;
            border-radius: 25px;
            padding: 10px;
            font-weight: bold;
            font-family: 'Cantarell', sans-serif;
            margin-bottom: 20px;
        }

        /* Styling for the individual elements in the dashboard */
        .element-box {
            background-color: #FFFFFF;
            padding: 20px;
            border-radius: 20px;
            margin: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: inline-block;
            width: 45%;
            vertical-align: top;
        }

        .element-header {
            color: #000000;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .element-box a {
            text-decoration: none;
            color: #F28018;
            font-size: 18px;
            font-weight: bold;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .element-box a:hover {
            background-color: #F2A85C;
        }

        .element-content {
            font-size: 16px;
            color: #333333;
            margin-top: 10px;
        }

        .logout-button {
            background-color: #FFFFFF;
            color: #F28018;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid #F28018;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #F28018;
            color: #FFFFFF;
        }
    </style>
    <title>Your Dashboard</title>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h6>Dashboard - Reports</h6>
            <button class="logout-button" onclick="window.location.href='logout.php'">Logout</button>
        </div>

        <div class="element-box">
            <a href="chck_stock.php">
                <div class="element-header">Free Stock</div>
            </a>
            <div class="element-content">Details about free stock</div>
        </div>

        <div class="element-box">
            <a href="stockrb.php">
                <div class="element-header">B Grade Stock</div>
            </a>
            <div class="element-content">Details about B Grade stock</div>
        </div>

        <div class="element-box">
            <a href="stockb.php">
                <div class="element-header">Hold Stock</div>
            </a>
            <div class="element-content">Details about hold stock</div>
        </div>
    </div>
</body>
</html>
