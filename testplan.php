<!DOCTYPE html>
<html>
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .chart-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .chart-toolbar {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .chart-title {
            margin-bottom: 15px;
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="chart-container">
        <h4 class="chart-title"><i class="fas fa-chart-bar mr-2"></i>Stock Visualization</h4>
        
        <div class="chart-toolbar">
            <div class="row">
                <div class="col-md-4">
                    <select id="chartType" class="form-control">
                        <option value="byMonth">Stock by Month</option>
                        <option value="byYear">Stock by Year</option>
                        <option value="byBrand">Stock by Brand</option>
                        <option value="byCode">Stock by Tyre Code</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <button id="generateChart" class="btn btn-primary">
                        <i class="fas fa-sync-alt mr-2"></i>Generate Chart
                    </button>
                </div>
            </div>
        </div>
        
        <div>
            <canvas id="stockChart"></canvas>
        </div>
    </div>

    <script>
        // Chart instance
        let stockChart = null;
        
        // Function to generate the chart
        function generateChart() {
            const chartType = document.getElementById('chartType').value;
            
            // Get data from the PHP table
            const tableData = [];
            const table = document.getElementById('inventoryTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells.length > 0) {
                    tableData.push({
                        serial: cells[0].innerText,
                        month: cells[1].innerText,
                        year: cells[2].innerText,
                        tireNumber: cells[3].innerText,
                        tyreCode: cells[4].innerText,
                        brand: cells[5].innerText,
                        description: cells[6].innerText,
                        date: cells[7].innerText
                    });
                }
            }
            
            // Process data based on chart type
            let labels = [];
            let data = [];
            let backgroundColor = [];
            let title = '';
            
            if (chartType === 'byMonth') {
                title = 'Stock Distribution by Month';
                // Create a map to count items by month
                const monthCounts = {};
                const monthColors = {
                    'January': 'rgba(54, 162, 235, 0.7)',
                    'February': 'rgba(255, 99, 132, 0.7)',
                    'March': 'rgba(255, 206, 86, 0.7)',
                    'April': 'rgba(75, 192, 192, 0.7)',
                    'May': 'rgba(153, 102, 255, 0.7)',
                    'June': 'rgba(255, 159, 64, 0.7)',
                    'July': 'rgba(199, 199, 199, 0.7)',
                    'August': 'rgba(83, 102, 255, 0.7)',
                    'September': 'rgba(255, 99, 255, 0.7)',
                    'October': 'rgba(0, 162, 235, 0.7)',
                    'November': 'rgba(255, 0, 132, 0.7)',
                    'December': 'rgba(0, 206, 86, 0.7)'
                };
                
                tableData.forEach(item => {
                    const monthName = item.month.split(' ')[0]; // Extract month name
                    if (!monthCounts[monthName]) {
                        monthCounts[monthName] = 0;
                    }
                    monthCounts[monthName]++;
                });
                
                // Sort months in calendar order
                const monthOrder = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 
                                   'August', 'September', 'October', 'November', 'December'];
                
                monthOrder.forEach(month => {
                    if (monthCounts[month]) {
                        labels.push(month);
                        data.push(monthCounts[month]);
                        backgroundColor.push(monthColors[month]);
                    }
                });
            } 
            else if (chartType === 'byYear') {
                title = 'Stock Distribution by Year';
                // Create a map to count items by year
                const yearCounts = {};
                
                tableData.forEach(item => {
                    const year = item.year;
                    if (!yearCounts[year]) {
                        yearCounts[year] = 0;
                    }
                    yearCounts[year]++;
                });
                
                // Sort years in chronological order
                const sortedYears = Object.keys(yearCounts).sort();
                
                sortedYears.forEach(year => {
                    labels.push(year);
                    data.push(yearCounts[year]);
                    backgroundColor.push('rgba(54, 162, 235, 0.7)');
                });
            }
            else if (chartType === 'byBrand') {
                title = 'Stock Distribution by Brand';
                // Create a map to count items by brand
                const brandCounts = {};
                
                tableData.forEach(item => {
                    const brand = item.brand || 'Unknown';
                    if (!brandCounts[brand]) {
                        brandCounts[brand] = 0;
                    }
                    brandCounts[brand]++;
                });
                
                // Generate colors
                function generateColor(index) {
                    const hue = (index * 137.5) % 360;
                    return `hsla(${hue}, 70%, 60%, 0.7)`;
                }
                
                // Sort brands by count (descending)
                const sortedBrands = Object.keys(brandCounts).sort((a, b) => brandCounts[b] - brandCounts[a]);
                
                sortedBrands.forEach((brand, index) => {
                    labels.push(brand);
                    data.push(brandCounts[brand]);
                    backgroundColor.push(generateColor(index));
                });
            }
            else if (chartType === 'byCode') {
                title = 'Top Tyre Codes';
                // Create a map to count items by tyre code
                const codeCounts = {};
                
                tableData.forEach(item => {
                    const code = item.tyreCode || 'Unknown';
                    if (!codeCounts[code]) {
                        codeCounts[code] = 0;
                    }
                    codeCounts[code]++;
                });
                
                // Sort tyre codes by count (descending) and take top 10
                const sortedCodes = Object.keys(codeCounts)
                    .sort((a, b) => codeCounts[b] - codeCounts[a])
                    .slice(0, 10);
                
                sortedCodes.forEach((code, index) => {
                    labels.push(code);
                    data.push(codeCounts[code]);
                    const blueShade = Math.max(20, 80 - (index * 5));
                    backgroundColor.push(`rgba(0, 123, 255, 0.${blueShade})`);
                });
            }
            
            // Destroy previous chart if it exists
            if (stockChart) {
                stockChart.destroy();
            }
            
            // Create new chart
            const ctx = document.getElementById('stockChart').getContext('2d');
            stockChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Number of Items',
                        data: data,
                        backgroundColor: backgroundColor,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: title,
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Count: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Items'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: chartType === 'byMonth' ? 'Month' : 
                                      chartType === 'byYear' ? 'Year' : 
                                      chartType === 'byBrand' ? 'Brand' : 'Tyre Code'
                            }
                        }
                    }
                }
            });
        }
        
        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initial chart generation
            generateChart();
            
            // Generate chart when button is clicked
            document.getElementById('generateChart').addEventListener('click', generateChart);
            
            // Generate chart when chart type changes
            document.getElementById('chartType').addEventListener('change', generateChart);
            
            // Add event listener to export button to include chart
            document.getElementById('exportBtn').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Export functionality would include the chart data as well.');
                // Actual implementation would require server-side code
            });
        });
    </script>
</body>
</html>