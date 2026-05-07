<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
include 'includes/App_Code.php';

// Fetch data for this month from the 'reject123' table
$thisMonthQuery = "SELECT DATE(date) as date, SUM(cstock) AS total_cstock 
                   FROM reject123 
                   WHERE MONTH(date) = MONTH(CURRENT_DATE()) 
                   AND YEAR(date) = YEAR(CURRENT_DATE()) 
                   GROUP BY DATE(date)";
$thisMonthResult = mysqli_query($connection, $thisMonthQuery);

// Fetch data for this year from the 'reject123' table
$thisYearQuery = "SELECT MONTH(date) as month, SUM(cstock) AS total_cstock 
                  FROM reject123 
                  WHERE YEAR(date) = YEAR(CURRENT_DATE()) 
                  GROUP BY MONTH(date)";
$thisYearResult = mysqli_query($connection, $thisYearQuery);

// Prepare data for Chart.js for this month
$thisMonthDates = [];
$thisMonthCstocks = [];
while ($row = mysqli_fetch_assoc($thisMonthResult)) {
    $thisMonthDates[] = $row['date'];
    $thisMonthCstocks[] = $row['total_cstock'];
}

// Prepare data for Chart.js for this year
$thisYearMonths = [];
$thisYearCstocks = [];
while ($row = mysqli_fetch_assoc($thisYearResult)) {
    $monthName = date("F", mktime(0, 0, 0, $row['month'], 10));
    $thisYearMonths[] = $monthName;
    $thisYearCstocks[] = $row['total_cstock'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.0.0/chartjs-plugin-datalabels.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #F28018, #ff9642);
            min-height: 100vh;
            padding: 20px;
        }

        .header-marquee {
            background: #000000;
            padding: 10px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .marquee-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-img {
            height: 50px;
            object-fit: contain;
        }

        .news-link {
            color: #F28018;
            font-size: 18px;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .news-link:hover {
            color: #ffffff;
        }

        .second-marquee {
            background: #F28018;
            color: #000000;
            padding: 10px;
            border-radius: 15px;
            margin: 10px 0;
            font-weight: bold;
        }

        .button-container {
            display: flex;
            gap: 20px;
            margin: 30px 0;
            justify-content: center;
        }

        .dashboard-button {
            background: #000000;
            color: #F28018;
            padding: 15px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 2px solid #F28018;
            min-width: 200px;
            text-align: center;
        }

        .dashboard-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            background: #F28018;
            color: #000000;
        }

        .container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .chart-container {
            flex: 1;
            min-width: 300px;
            max-width: 600px;
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        canvas {
            width: 100% !important;
            height: 400px !important;
        }

        @media (max-width: 768px) {
            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .chart-container {
                min-width: 100%;
            }

            .dashboard-button {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="header-marquee">
        <marquee direction="left">
            <div class="marquee-content">
                <img src="atire.png" alt="Logo" class="logo-img">
                <?php
                $qry = mysqli_query($connection, "SELECT * FROM news_and_update WHERE news_type='alert' ORDER BY created DESC") or die("select query fail" . mysqli_error());
                while ($row = mysqli_fetch_assoc($qry)) {
                    $news_title = $row['news_title'];
                    echo "<a href='#' class='news-link'>$news_title &nbsp;<strong></strong></a>";
                }
                ?>
            </div>
        </marquee>
    </div>

    <div class="second-marquee">
        <marquee direction="right" onmouseover="this.stop();" onmouseout="this.start();">
            <span class="breadcrumb-item" style="cursor: pointer;">
                Welcome to the Quality Department - Track Your Performance and Analytics
            </span>
        </marquee>
    </div>

    <div class="button-container">
    <a href="p_summery_filter.php" class="dashboard-button">Daily Production Summery</a>
        <a href="rejectbutton.php" class="dashboard-button">Daily Reject</a>
        <a href="bom_all.php" class="dashboard-button">BOM</a>
    </div>

    <div class="container">
        <div class="chart-container">
            <canvas id="thisMonthPieChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="thisYearPieChart"></canvas>
        </div>
    </div>

    <script>
        // Create Monthly Chart
        var thisMonthCtx = document.getElementById('thisMonthPieChart').getContext('2d');
        var thisMonthPieChart = new Chart(thisMonthCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($thisMonthDates); ?>,
                datasets: [{
                    label: 'CStock (This Month)',
                    data: <?php echo json_encode($thisMonthCstocks); ?>,
                    backgroundColor: 'rgba(242, 128, 24, 0.7)',
                    borderColor: 'rgba(242, 128, 24, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: true,
                        color: '#000000',
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value.toLocaleString();
                        },
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        padding: { bottom: 4 }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Create Yearly Chart
        var thisYearCtx = document.getElementById('thisYearPieChart').getContext('2d');
        var thisYearPieChart = new Chart(thisYearCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($thisYearMonths); ?>,
                datasets: [{
                    label: 'CStock (This Year)',
                    data: <?php echo json_encode($thisYearCstocks); ?>,
                    backgroundColor: 'rgba(242, 128, 24, 0.7)',
                    borderColor: 'rgba(242, 128, 24, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: true,
                        color: '#000000',
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value.toLocaleString();
                        },
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        padding: { bottom: 4 }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>
</body>
</html>