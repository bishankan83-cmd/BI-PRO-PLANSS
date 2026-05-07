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

// Query to check if there is data in the daily_plan_data1 table
$query = "SELECT COUNT(*) as count, MAX(date) as max_date FROM template";
$result = $mysqli->query($query);

// Fetch the result
$row = $result->fetch_assoc();
$count = $row['count'];
$maxDate = $row['max_date'];

// Check if there is data in the daily_plan_data1 table
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

        .centered {
            display: flex;
            justify-content: center;
            align-items: center;
        }
      </style>';

    echo '<div class="centered">
            <span class="breadcrumb-item blink" style="cursor: pointer; background: #F28018; color: black;">
                <span style="font-weight: bold; color: black;">Please confirm the daily Reject. ' . date('Y-m-d', strtotime($maxDate)) . '</span>
            </span>
          </div>';
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
        /* Styling for the Dashboard elements */
        .element-content {
            background-color: #F28018;
            padding: 0;
            text-align: center;
            box-shadow: 2px 2px 4px rgba(0, 0, 10, 100);
            
            /* Make the element fill the full page */
            position: right;
            top: 0;
            left: 5px;
            width: 100%;
            height: 100%;
        }

        .element-box {
            background-color: #FFFFFF;
            padding: 20px;
            border-radius: 60px;
            margin: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .element-header {
            color: #000000;
            font-size: 24px;
            font-weight: bold;
        }

        .element-box a {
            text-decoration: none;
            color: #F28018;
        }

        /* Styling for the specific elements with id="myDIV" */
        #myDIV {
            color: #000000;
            font-weight: bold;
            font-size: 20px;
            margin-top: 10px;
        }

        body {
            background-color: #FFFFFF;
        }
    </style>
    <title>Your Dashboard</title>
</head>
<body>
    <div class="element-content">
        <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: center; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Reports</h6>

        <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="stock_button.php">
                    <div id="myDIV">Stock report</div>
                </a>
            </div>
            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="dispatch_view.php">
                    <div id="myDIV">Dispatched work order</div>
                </a>
            </div>
            
    <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="all_check_se.php">
                    <div id="myDIV">Check Serial Number</div>
                </a>
            </div>


        <div class="col-sm-4 col-xxxl-3">
            
        </div>
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Chart of Total Stock by Brand</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .chart-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            padding: 20px;
            width: 80%;
            max-width: 1000px; /* Adjusted max-width */
            transition: transform 0.3s ease, width 0.3s ease, height 0.3s ease;
            cursor: pointer;
        }
        .chart-container.large {
            width: 90%;
            max-width: auto;
            height: auto; /* Allow height to adjust automatically */
            transform: scale(1.2); /* Slightly enlarge */
        }
        canvas {
            display: block;
            margin: 0 auto;
            width: 800px !important; /* Adjusted width */
            height: 400px !important; /* Adjusted height */
        }
    </style>
</head>
<body>

    <div class="chart-container">
        <h1>Total Stock by Brand</h1>
        <canvas id="stockChart" width="800" height="400"></canvas>
    </div>
    <script>
        // Fetch data from PHP
        <?php
        // Database connection details
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        // Create a new PDO instance
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // SQL query to get the total stock for each brand
            $sql = "SELECT brand, SUM(cstock) AS total_stock FROM realstock GROUP BY brand ORDER BY total_stock DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            
            // Fetch data
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        // Encode PHP data to JavaScript
        echo "const chartData = " . json_encode($data) . ";";
        ?>
        
        // Prepare data for Chart.js
        const labels = chartData.map(item => item.brand);
        const values = chartData.map(item => item.total_stock);

        const ctx = document.getElementById('stockChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Stock',
                    data: values,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Optional: Add a click event to toggle size
        document.querySelector('.chart-container').addEventListener('click', function() {
            this.classList.toggle('large');
        });
    </script>
</body>
</html>

    </div>
</body>
</html>
