<?php
// Database connection settings
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current year
$currentYear = date("Y");

// Get all unique cavity names
$cavityQuery = "SELECT DISTINCT CavityName FROM daily_plan_data 
                WHERE YEAR(Date) = $currentYear 
                AND CavityName IS NOT NULL 
                AND CavityName != '' 
                ORDER BY CavityName";
$cavityResult = $conn->query($cavityQuery);
$cavities = [];

if ($cavityResult->num_rows > 0) {
    while($row = $cavityResult->fetch_assoc()) {
        $cavities[] = $row["CavityName"];
    }
}

// Query to get cavity usage by month for current year
$sql = "SELECT 
            MONTH(Date) as month_num, 
            MONTHNAME(Date) as month_name,
            CavityName,
            COUNT(*) as usage_count
        FROM 
            daily_plan_data
        WHERE 
            YEAR(Date) = $currentYear
            AND CavityName IS NOT NULL
            AND CavityName != ''
        GROUP BY 
            MONTH(Date), CavityName
        ORDER BY 
            MONTH(Date), CavityName";

$result = $conn->query($sql);

// Prepare data for chart
$monthLabels = array(
    1 => 'January', 2 => 'February', 3 => 'March', 
    4 => 'April', 5 => 'May', 6 => 'June',
    7 => 'July', 8 => 'August', 9 => 'September', 
    10 => 'October', 11 => 'November', 12 => 'December'
);

$cavityData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $monthNum = $row["month_num"];
        $cavityName = $row["CavityName"];
        $count = $row["usage_count"];
        
        // Add cavity data
        if (!isset($cavityData[$cavityName])) {
            $cavityData[$cavityName] = array_fill(1, 12, 0);
        }
        
        $cavityData[$cavityName][$monthNum] = $count;
    }
} else {
    echo "No data found for the current year.";
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cavity Usage Trends</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chart-container {
            width: 100%;
            height: 500px;
            margin: 20px auto;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .toggle-buttons {
            display: flex;
            gap: 10px;
        }
        button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            background-color: #4a90e2;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #357abD;
        }
        button.active {
            background-color: #2c6bac;
        }
        .checkbox-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
        }
        .checkbox-item {
            width: 25%;
            margin-bottom: 8px;
        }
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            text-align: center;
        }
        .stat-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
            min-width: 150px;
        }
        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #4a90e2;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Cavity Usage Trends (<?php echo $currentYear; ?>)</h1>
        
        <div class="controls">
            <div class="filter-group">
                <label for="cavityFilter">Filter by Cavity:</label>
                <select id="cavityFilter" multiple size="5">
                    <?php foreach ($cavities as $cavity): ?>
                        <option value="<?php echo htmlspecialchars($cavity); ?>" selected>
                            <?php echo htmlspecialchars($cavity); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button id="selectAll">Select All</button>
                <button id="clearAll">Clear All</button>
            </div>
            
            <div class="toggle-buttons">
                <button id="lineChartBtn" class="active">Line Chart</button>
                <button id="barChartBtn">Bar Chart</button>
                <button id="stackedBarBtn">Stacked Bar</button>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="cavityChart"></canvas>
        </div>
        
        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-value" id="totalUsage">0</div>
                <div class="stat-label">Total Usage</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" id="mostUsedCavity">-</div>
                <div class="stat-label">Most Used Cavity</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" id="peakMonth">-</div>
                <div class="stat-label">Peak Month</div>
            </div>
        </div>
    </div>

    <script>
        // Chart data
        const monthLabels = <?php echo json_encode(array_values($monthLabels)); ?>;
        const cavityData = <?php echo json_encode($cavityData); ?>;
        const cavities = <?php echo json_encode($cavities); ?>;
        
        // Color palette for consistent colors
        const colorPalette = [
            '#4e79a7', '#f28e2c', '#e15759', '#76b7b2', '#59a14f',
            '#edc949', '#af7aa1', '#ff9da7', '#9c755f', '#bab0ab',
            '#6b9ac4', '#e48d5a', '#d4767b', '#94c8c4', '#7eb875',
            '#f0d675', '#c295b8', '#ffb7bf', '#b48f7d', '#d2ccc9'
        ];
        
        // Initialize chart
        let chartType = 'line';
        let cavityChart;
        
        // Function to update chart
        function updateChart() {
            // Get selected cavities
            const selectedCavities = Array.from(document.getElementById('cavityFilter').selectedOptions)
                .map(option => option.value);
            
            // Prepare datasets based on selected cavities
            const datasets = [];
            let totalUsage = 0;
            const cavityTotals = {};
            const monthTotals = Array(12).fill(0);
            
            selectedCavities.forEach((cavity, index) => {
                const colorIndex = index % colorPalette.length;
                const data = Object.values(cavityData[cavity] || Array(12).fill(0));
                
                // Calculate totals
                const cavityTotal = data.reduce((sum, val) => sum + val, 0);
                totalUsage += cavityTotal;
                cavityTotals[cavity] = cavityTotal;
                
                // Update month totals
                data.forEach((value, i) => {
                    monthTotals[i] += value;
                });
                
                datasets.push({
                    label: cavity,
                    data: data,
                    backgroundColor: colorPalette[colorIndex],
                    borderColor: colorPalette[colorIndex],
                    borderWidth: 2,
                    tension: 0.3,
                    fill: chartType === 'line' ? false : true
                });
            });
            
            // Find most used cavity and peak month
            const mostUsedCavity = Object.entries(cavityTotals)
                .sort((a, b) => b[1] - a[1])[0] || ['-', 0];
            
            const peakMonthIndex = monthTotals.indexOf(Math.max(...monthTotals));
            const peakMonth = monthLabels[peakMonthIndex] || '-';
            
            // Update stats
            document.getElementById('totalUsage').textContent = totalUsage;
            document.getElementById('mostUsedCavity').textContent = mostUsedCavity[0];
            document.getElementById('peakMonth').textContent = peakMonth;
            
            // Destroy existing chart if it exists
            if (cavityChart) {
                cavityChart.destroy();
            }
            
            // Create new chart
            const ctx = document.getElementById('cavityChart').getContext('2d');
            cavityChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: monthLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Cavity Usage Trends by Month',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + ' uses';
                                }
                            }
                        }
                    },
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
                                text: 'Usage Count'
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
        
        // Initialize chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateChart();
            
            // Chart type toggle buttons
            document.getElementById('lineChartBtn').addEventListener('click', function() {
                document.getElementById('lineChartBtn').classList.add('active');
                document.getElementById('barChartBtn').classList.remove('active');
                document.getElementById('stackedBarBtn').classList.remove('active');
                chartType = 'line';
                updateChart();
            });
            
            document.getElementById('barChartBtn').addEventListener('click', function() {
                document.getElementById('lineChartBtn').classList.remove('active');
                document.getElementById('barChartBtn').classList.add('active');
                document.getElementById('stackedBarBtn').classList.remove('active');
                chartType = 'bar';
                updateChart();
            });
            
            document.getElementById('stackedBarBtn').addEventListener('click', function() {
                document.getElementById('lineChartBtn').classList.remove('active');
                document.getElementById('barChartBtn').classList.remove('active');
                document.getElementById('stackedBarBtn').classList.add('active');
                chartType = 'bar';
                
                // Destroy existing chart if it exists
                if (cavityChart) {
                    cavityChart.destroy();
                }
                
                // Get selected cavities
                const selectedCavities = Array.from(document.getElementById('cavityFilter').selectedOptions)
                    .map(option => option.value);
                
                // Prepare datasets based on selected cavities
                const datasets = [];
                
                selectedCavities.forEach((cavity, index) => {
                    const colorIndex = index % colorPalette.length;
                    datasets.push({
                        label: cavity,
                        data: Object.values(cavityData[cavity] || Array(12).fill(0)),
                        backgroundColor: colorPalette[colorIndex],
                        borderColor: colorPalette[colorIndex],
                        borderWidth: 1
                    });
                });
                
                // Create stacked bar chart
                const ctx = document.getElementById('cavityChart').getContext('2d');
                cavityChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: monthLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Stacked Cavity Usage by Month',
                                font: {
                                    size: 16
                                }
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw + ' uses';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Usage Count'
                                }
                            }
                        }
                    }
                });
            });
            
            // Select all/clear all buttons
            document.getElementById('selectAll').addEventListener('click', function() {
                const options = document.getElementById('cavityFilter').options;
                for (let i = 0; i < options.length; i++) {
                    options[i].selected = true;
                }
                updateChart();
            });
            
            document.getElementById('clearAll').addEventListener('click', function() {
                const options = document.getElementById('cavityFilter').options;
                for (let i = 0; i < options.length; i++) {
                    options[i].selected = false;
                }
                updateChart();
            });
            
            // Cavity filter change event
            document.getElementById('cavityFilter').addEventListener('change', updateChart);
        });
    </script>
</body>
</html>