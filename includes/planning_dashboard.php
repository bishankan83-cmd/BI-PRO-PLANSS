



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
            --secondary-color: #2c3e50;
            --background-color: #f5f5f5;
            --card-background: #FFFFFF;
            --text-dark: #000000;
            --text-light: #FFFFFF;
            --sidebar-background: #34495e;
            --sidebar-text: #ecf0f1;
            --sidebar-hover: black;
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
            display: flex;
            background-color: var(--background-color);
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background-color: var(--sidebar-background);
            color: var(--sidebar-text);
            transition: var(--transition);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background-color: var(--secondary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            margin-right: 15px;
            border-radius: 10px;
        }

        .sidebar-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--text-light);
        }

        .sidebar-user-profile {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background-color: rgba(0,0,0,0.2);
            gap: 15px;
        }

        .sidebar-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--primary-color);
        }

        .sidebar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-user-details {
            flex-grow: 1;
        }

        .sidebar-username {
            font-weight: bold;
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .sidebar-userrole {
            color: var(--sidebar-text);
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .sidebar-logout {
            background: var(--primary-color);
            color: var(--text-light);
            padding: 0.75rem;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 15px;
            transition: var(--transition);
        }

        .sidebar-logout:hover {
            background: #ff9a3c;
            transform: scale(1.05);
        }

        .sidebar-logout i {
            margin-right: 10px;
        }

        .sidebar-menu {
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-menu-item {
            list-style: none;
            position: relative;
        }

        .sidebar-menu-item > a {
            display: flex;
            align-items: center;
            color: var(--sidebar-text);
            text-decoration: none;
            padding: 12px 20px;
            transition: var(--transition);
            gap: 10px;
        }

        .sidebar-menu-item > a:hover {
            background-color: var(--sidebar-hover);
            color: var(--text-light);
        }

        .sidebar-menu-item > a i {
            width: 25px;
            text-align: center;
        }

        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: var(--transition);
            background-color: rgba(0, 0, 0, 0.1);
        }

        .sidebar-menu-item:hover .sidebar-submenu {
            max-height: 500px;
        }

        .sidebar-submenu a {
            padding: 10px 40px;
            color: var(--sidebar-text);
            text-decoration: none;
            display: block;
            transition: var(--transition);
        }

        .sidebar-submenu a:hover {
            background-color: var(--primary-color);
        }

        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 20px;
        }

        .status-bar {
            background: linear-gradient(135deg, var(--primary-color), #ff9a3c);
            color: var(--text-light);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 12px;
            white-space: nowrap;
            overflow: hidden;
        }

        .dashboard-title {
            background: linear-gradient(135deg, var(--secondary-color), #333);
            color: var(--text-light);
          
            border-radius: 25px;
            text-align: center;
            margin: 1rem 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .card {
            background: var(--card-background);
            border-radius: 15px;
            padding: 1.5rem;
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .card-title {
            font-weight: bold;
            text-align: center;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
           
    
        
        <!-- User Profile Section -->
        <div class="sidebar-user-profile">
            <div class="sidebar-avatar">
                <img src="user_profile/<?php echo $_SESSION['emp_pro']; ?>" alt="User Avatar">
            </div>
            <div class="sidebar-user-details">
                <div class="sidebar-username"><?php echo $_SESSION['emp_name']; ?></div>
                <div class="sidebar-userrole"><?php echo $_SESSION['User_type']; ?></div>
            </div>
        </div>
        </div>
        <!-- Logout Button -->
        <a href="logout.php" class="sidebar-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
        
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="#"><i class="fas fa-clipboard-list"></i>Work Order</a>
                <ul class="sidebar-submenu">
                    <li><a href="add_workorder.php">Work order - New</a></li>
                    <li><a href="comparee.php">Work order - Verify</a></li>
                    <li><a href="workdelete.php">Work order - Remove</a></li>
                    <li><a href="import22bnew32.php">Pause Stock Orders</a></li>
                    <li><a href="stock_order_rep.php">Resume Stock Orders</a></li>
                    <li><a href="worder_rev_button.php">Work order - Revise</a></li>
                    <li><a href="add_work_order_hold.php">Work order - Hold</a></li>
                    <li><a href="dispatchR.php">Work order - Reverse</a></li>
                </ul>
            </li>
            
            <li class="sidebar-menu-item">
                <a href="#"><i class="fas fa-chart-line"></i>Production Plan</a>
                <ul class="sidebar-submenu">
                    <li><a href="convertstock.php">Plan - Work order</a></li>
                    <li><a href="deleteplan.php">Plan - Remove</a></li>
                     <li><a href="select_cav_prev.php">Plan - Get Auto Cavity</a></li>
                    <li><a href="date_update12.php">Plan - Update</a></li>
                    <li><a href="updatedate.php">Plan - Date Update</a></li>
                    <li><a href="time_range2.php">Plan - Shift Wise</a></li>
                    <li><a href="date_update.php">Plan - Date Change</a></li>
                    <li><a href="stock_add.php">Plan - Stock</a></li>
                    <li><a href="plan_import.php">Plan - Daily</a></li>
                </ul>
            </li>
            
            <li class="sidebar-menu-item">
                <a href="#"><i class="fas fa-tire"></i>Tires Input</a>
                <ul class="sidebar-submenu">
                    <li><a href="add_daily_production.php">Daily Production</a></li>
                </ul>
            </li>
            
            <li class="sidebar-menu-item">
                <a href="#"><i class="fas fa-times-circle"></i>Tires Output - QA</a>
                <ul class="sidebar-submenu">
                    <li><a href="add_reject.php">Daily Reject</a></li>
                    <li><a href="add_rejectb.php">Daily B Grade</a></li>
                    <li><a href="#">Daily Hold</a></li>
                </ul>
            </li>
            
            <li class="sidebar-menu-item">
                <a href="#"><i class="fas fa-truck-loading"></i>Tire Output - Sales</a>
                <ul class="sidebar-submenu">
                    <li><a href="dispatch.php">Order Dispatch</a></li>
                </ul>
            </li>
            
            <li class="sidebar-menu-item">
                <a href="#"><i class="fas fa-cog"></i>System</a>
                <ul class="sidebar-submenu">
                    <li><a href="get_z.php">Refresh System</a></li>
                    <li><a href="edit_data.php">Edit Data</a></li>
                    <li><a href="notice_mangement.php">edit notice</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-item">
                <a href="#"><i class="fas fa-cog"></i>System Update</a>
                <ul class="sidebar-submenu">
                    <li><a href="switch.php">Update System</a></li>
                  
                </ul>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Status Bar -->
        <div class="status-bar">
            <marquee direction="right" onmouseover="this.stop();" onmouseout="this.start();">
                <span>FG Stock: <?php echo $totalCStock; ?></span> |
                <span>Total Requirement: <?php echo $totalnew; ?></span> |
                <span>Free Stock: <?php echo $totalCStockk; ?></span> |
                <span>To be produced: <?php echo $totaltobe; ?></span> |
                <span>On Hand Work Orders: <?php echo $totalcount; ?></span> |
                <span>Production Complete: <?php echo $result; ?></span> |
                <span>To be Produced: <?php echo $erpCount; ?></span> |
                <span>Cavity Utilization: 59</span> |
                <span>Current Month Dispatched: <?php echo $totalcountt; ?></span>
            </marquee>
        </div>

        <!-- Dashboard Title -->
        <h1 class="dashboard-title">Production Dashboard</h1>

        <!-- Cards Grid -->
        <div class="cards-container">
            <!-- Work Order -->
            <a href="work_order_show.php" class="card">
                <i class="fas fa-tasks card-icon"></i>
                <div class="card-title">Work Order</div>
            </a>

            <!-- Stock Report -->
            <a href="stock_button.php" class="card">
                <i class="fas fa-boxes card-icon"></i>
                <div class="card-title">Stock Report</div>
            </a>

            <!-- Dispatch View -->
            <a href="dispatch_view.php" class="card">
                <i class="fas fa-truck card-icon"></i>
                <div class="card-title">Dispatched Work Order</div>
            </a>

            <!-- Mold Changing -->
            <a href="mold_change.php" class="card">
                <i class="fas fa-cogs card-icon"></i>
                <div class="card-title">Mold Changing</div>
            </a>

            <!-- Item Wise Orders -->
            <a href="order_quantity.php" class="card">
                <i class="fas fa-clipboard-list card-icon"></i>
                <div class="card-title">On Hand Orders - Item Wise</div>
            </a>

            <!-- Daily Production -->
            <a href="daily_production.php" class="card">
                <i class="fas fa-industry card-icon"></i>
                <div class="card-title">Daily Production</div>
            </a>

            <!-- Daily Reject -->
            <a href="rejectbutton.php" class="card">
                <i class="fas fa-ban card-icon"></i>
                <div class="card-title">Daily Reject</div>
            </a>

            <!-- Green Tire Weight -->
            <a href="bom_all.php" class="card">
                <i class="fas fa-weight card-icon"></i>
                <div class="card-title">Green Tire Weight</div>
            </a>

            <!-- Planning Reports -->
            <a href="planbuttoon.php" class="card">
                <i class="fas fa-calendar-alt card-icon"></i>
                <div class="card-title">Planning Reports</div>
            </a>

         
         
         <!-- Compound Production -->
         <a href="show_mixing.php" class="card">
                <i class="fas fa-blender card-icon"></i>
                <div class="card-title">Compound Production</div>
            </a>

            <a href="mixingdash_stock.php" class="card">
    
    <i class="fas fa-flask card-icon"></i>
    <div class="card-title">Compound Stock</div>
</a>

            <!-- QR Code Details -->
            <a href="lab_qr_details.php" class="card">
                <i class="fas fa-qrcode card-icon"></i>
                <div class="card-title">QR Code Details</div>
            </a>
            
            <a href="band_summery.php" class="card">
    <i class="fas fa-cubes card-icon"></i> <!-- Updated icon for Steel Band Stock -->
    <div class="card-title">Steel Band Summery</div>
</a>

            
            <!-- Mold Changing -->
            <!--a href="mold_change.php" class="card" -->

             <a href="dis_mold.php" class="card">
            
                <i class="fas fa-cogs card-icon"></i>
                <div class="card-title">Mold Capacity</div>
            </a>

            
          <!-- Check Serial Number -->
<a href="all_check_se.php" class="card" title="Verify serial numbers across stock">
    <i class="fas fa-barcode card-icon"></i>
    <div class="card-title">Check Serial Number</div>
</a>


        </div>


        

       

    <script>
        // Optional JavaScript for enhanced interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar menu items
            const menuItems = document.querySelectorAll('.sidebar-menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    this.classList.toggle('active');
                });
            });

            // Smooth scroll to top
            const scrollToTopBtn = document.createElement('button');
            scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            scrollToTopBtn.classList.add('scroll-to-top');
            document.body.appendChild(scrollToTopBtn);

            scrollToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Show/hide scroll to top button
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.style.display = 'block';
                } else {
                    scrollToTopBtn.style.display = 'none';
                }
            });
        });
    </script>

    <style>
        /* Footer Styles */
        .dashboard-footer {
            background-color: var(--secondary-color);
            color: var(--text-light);
            padding: 1rem;
            margin-top: 2rem;
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            gap: 1rem;
        }

        .footer-links a {
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--primary-color);
            color: var(--text-light);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .scroll-to-top:hover {
            background-color: #ff9a3c;
            transform: scale(1.1);
        }
    </style>
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



