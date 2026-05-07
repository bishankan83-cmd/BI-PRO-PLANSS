<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
            --background-color: #f5f5f5;
            --card-background: #FFFFFF;
            --text-dark: #000000;
            --text-light: #FFFFFF;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
        }

        /* User Profile Section */
        .profile-section {
            background-color: var(--card-background);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary-color);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: bold;
            color: var(--text-dark);
        }

        .user-role {
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .logout-button {
            background-color: var(--secondary-color);
            color: var(--text-light);
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .logout-button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Status Bar */
        .status-bar {
            background: var(--primary-color);
            color: var(--text-dark);
            padding: 0.8rem;
            margin: 1rem 0;
            border-radius: 8px;
            white-space: nowrap;
            overflow: hidden;
        }

        .status-item {
            display: inline-block;
            margin: 0 1.5rem;
            font-weight: 600;
        }

        /* Dashboard Title */
        .dashboard-title {
            background-color: var(--secondary-color);
            color: var(--text-light);
           
            border-radius: 25px;
            text-align: center;
            margin: 1rem 2rem;
            font-weight: bold;
        }

        /* Cards Grid */
        .cards-container {
            padding: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: var(--card-background);
            border-radius: 15px;
            padding: 1.5rem;
            text-decoration: none;
            color: var(--text-dark);
            transition: var(--transition);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            border: 2px solid var(--primary-color);
        }

        .card-icon {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .card-title {
            font-weight: bold;
            font-size: 1.1rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .profile-section {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 1rem;
            }

            .status-bar {
                font-size: 0.9rem;
            }

            .cards-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- User Profile Section -->
    <div class="profile-section">
        <div class="user-profile">
            <div class="avatar">
                <img alt="Profile" src="user_profile/<?php echo $_SESSION['emp_pro'];?>">
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['emp_name'];?></div>
                <div class="user-role"><?php echo $_SESSION['User_type'];?></div>
            </div>
        </div>
        <a href="logout.php" class="logout-button">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Status Bar -->
    <div class="status-bar">
        <marquee direction="right" onmouseover="this.stop();" onmouseout="this.start();">
            <span class="status-item">FG Stock: <?php echo $totalCStock; ?></span> |
            <span class="status-item">Total Requirement: <?php echo $totalnew; ?></span> |
            <span class="status-item">Free Stock: <?php echo $totalCStockk; ?></span> |
            <span class="status-item">To be produced: <?php echo $totaltobe; ?></span> |
            <span class="status-item">On Hand Work Orders: <?php echo $totalcount; ?></span> |
            <span class="status-item">Production complete work orders: <?php echo $result; ?></span> |
            <span class="status-item">To be Produce Work Orders: <?php echo ($erpCount); ?></span> |
            <span class="status-item">Cavity Utilization: 59</span> |
            <span class="status-item">Current Month Dispatched Order: <?php echo ($totalcountt);?></span>
        </marquee>
    </div>

    <!-- Dashboard Title -->
    <h1 class="dashboard-title">Dashboard - Reports</h1>

    <!-- Cards Grid -->
    <div class="cards-container">
        <a href="work_order_show.php" class="card">
            <i class="fas fa-tasks card-icon"></i>
            <div class="card-title">Work Order</div>
        </a>

        <a href="stock_button_gm.php" class="card">
            <i class="fas fa-boxes card-icon"></i>
            <div class="card-title">Stock Report</div>
        </a>

        <a href="dispatch_view.php" class="card">
            <i class="fas fa-truck card-icon"></i>
            <div class="card-title">Dispatched Work Order</div>
        </a>

        <a href="match.php" class="card">
            <i class="fas fa-cogs card-icon"></i>
            <div class="card-title">Mold Changing</div>
        </a>

        <a href="order_quantity.php" class="card">
            <i class="fas fa-clipboard-list card-icon"></i>
            <div class="card-title">On Hand Orders - Item Wise</div>
        </a>

        <a href="daily_production.php" class="card">
            <i class="fas fa-industry card-icon"></i>
            <div class="card-title">Daily Production</div>
        </a>

        <a href="rejectbutton.php" class="card">
            <i class="fas fa-ban card-icon"></i>
            <div class="card-title">Daily Reject</div>
        </a>

        <a href="bom_all.php" class="card">
            <i class="fas fa-weight card-icon"></i>
            <div class="card-title">Green Tire Weight</div>
        </a>

        <a href="planbuttoon.php" class="card">
            <i class="fas fa-calendar-alt card-icon"></i>
            <div class="card-title">Planning Reports</div>
        </a>

        <a href="show_mixing.php" class="card">
            <i class="fas fa-blender card-icon"></i>
            <div class="card-title">Compound Production</div>
        </a>

        <a href="lab_qr_details.php" class="card">
    <i class="fas fa-qrcode card-icon"></i>
    <div class="card-title">QR Code Details</div>
</a>

<a href="band_summery.php" class="card">
    <i class="fas fa-cubes card-icon"></i> <!-- Updated icon for Steel Band Stock -->
    <div class="card-title">Steel Band Summery</div>
</a>


   <!-- Check Serial Number -->
<a href="all_check_se.php" class="card" title="Verify serial numbers across stock">
    <i class="fas fa-barcode card-icon"></i>
    <div class="card-title">Check Serial Number</div>
</a>


    <!-- Mold Changing -->
    <a href="dis_mold.php" class="card">
                <i class="fas fa-cogs card-icon"></i>
                <div class="card-title">Mold Capacity</div>
            </a>

    </div>
</body>
</html>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
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
    <div class="chart-container" id="dailyContainer">
        <h1>Daily Tire Production This Month</h2>
        <div>
        <h2>Tire Production</h2>
        <canvas id="productionChart"></canvas>
        <div>
        <h2>Tire Weight</h2>
        <canvas id="weightChart"></canvas>
    </div>
    </div>
    </div>
    <div class="chart-container" id="monthlyContainer">
        <h2>Monthly Tire Production This Year</h2>
        <canvas id="monthlyChart"></canvas>
    </div>

    <script>
    function fetchDataAndRenderCharts() {
       
        // Fetch daily data
fetch('get_daily_data.php') // Path to your PHP file
    .then(response => response.json())
    .then(data => {
        // Daily Tire Production Chart
        const ctxProduction = document.getElementById('productionChart').getContext('2d');
        new Chart(ctxProduction, {
            type: 'bar',
            data: {
                labels: data.days, // Array of days
                datasets: [
                    {
                        label: 'Daily Tire Production',
                        data: data.totals, // Array of totals
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Tire Production'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Production'
                        }
                    }
                }
            }
        });

        // Daily Tire Weight Chart
        const ctxWeight = document.getElementById('weightChart').getContext('2d');
        new Chart(ctxWeight, {
            type: 'bar',
            data: {
                labels: data.days, // Array of days
                datasets: [
                    {
                        label: 'Daily Tire Weight',
                        data: data.stgreenweights, // Array of weights
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Tire Weight'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Weight'
                        }
                    }
                }
            }
        });
    })
    .catch(error => console.error('Error fetching data:', error));




        // Fetch monthly data
        fetch('get_monthly_data.php') // Path to your PHP file
        .then(response => response.json())
        .then(data => {
            const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
            new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: data.months, // Array of months
                    datasets: [{
                        label: 'Monthly Tire Production',
                        data: data.totals, // Array of totals
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Tire Production'
                            }
                        }
                    }
                }
            });
        });
    }

    // Call the function to fetch data and render charts
    fetchDataAndRenderCharts();

    // Add click event listeners to toggle chart size
    document.getElementById('dailyContainer').addEventListener('click', function() {
        this.classList.toggle('large');
    });

    document.getElementById('monthlyContainer').addEventListener('click', function() {
        this.classList.toggle('large');
    });
    </script>
</body>
</html>








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
    </div>
</body>
</html>



