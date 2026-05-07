

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

    .logout-button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
        }

    </style>
    <title>Your Dashboard</title>
</head>
<body>

       
          
         
  
    <div class="element-content">
    <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: center; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Repots</h6>
    

            


<div class="row">

<div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="daily_production.php">
                    <div id="myDIV">Daily Tire Production     </div>
                </a>
            </div>

            <div class="col-sm-4 col-xxxl-3">
            <a class="element-box el-tablo" href="mixingdashboard212.php">
                    <div id="myDIV">Daily Compound Input  </div>
                </a>
            </div>

            <div class="col-sm-4 col-xxxl-3">
            <a class="element-box el-tablo" href="mixingdashboard213.php">
                    <div id="myDIV">Daily Compound Output </div>
                </a>
            </div>

            <div class="col-sm-4 col-xxxl-3">
            <a class="element-box el-tablo" href="mixingdash_stock.php">
                    <div id="myDIV">Compound Stock </div>
                </a>
            </div>

            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="view_band_stock.php">
                    <div id="myDIV">Steel Band Stock</div>
                    
                </a>
       
    </div>
    <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="mrn_details_pen.php">
                    <div id="myDIV">Steel Band Mrn Issue</div>
                    
                </a>
       
    </div>

    <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="mrn_details.php">
                    <div id="myDIV">Complete Steel Band Mrn Issue</div>
                    
                </a>
       
    </div>




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
            width: auto;
            max-width: auto; /* Adjusted max-width */
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
        <h2>Daily Tire Production This Month</h2>
        <canvas id="dailyChart"></canvas>
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
            const ctxDaily = document.getElementById('dailyChart').getContext('2d');
            new Chart(ctxDaily, {
                type: 'bar',
                data: {
                    labels: data.days, // Array of days
                    datasets: [{
                        label: 'Daily Tire Production',
                        data: data.totals, // Array of totals
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
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
                                text: 'Total Tire Production'
                            }
                        }
                    }
                }
            });
        });

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

          
          
           
        </div>
    </div>
</body>
</html>
