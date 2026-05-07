<!DOCTYPE html>
<html>
<head>
    <title>Data Visualization</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<canvas id="totalNewVsPositiveToBeChart" width="400" height="400"></canvas>
<canvas id="countryDistributionChart" width="400" height="400"></canvas>
<canvas id="totalKgsOverTimeChart" width="400" height="400"></canvas>
<canvas id="cargoReadyDatesChart" width="400" height="400"></canvas>

<script>
// Sample data
var data = <?php echo json_encode($data); ?>;

// Extracting data for visualization
var erps = Object.keys(data);
var totalNew = erps.map(erp => data[erp]['total_new']);
var totalPositiveToBe = erps.map(erp => data[erp]['total_positive_tobe']);
var countries = erps.map(erp => data[erp]['country']);
var totalKgs = erps.map(erp => data[erp]['total_kgs']);
var cargoReadyDates = erps.map(erp => data[erp]['cargo_ready_date']);

// Chart 1: Bar chart for Total New vs. Total Positive ToBe
var ctx1 = document.getElementById('totalNewVsPositiveToBeChart').getContext('2d');
var chart1 = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: erps,
        datasets: [{
            label: 'Total New',
            data: totalNew,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }, {
            label: 'Total Positive ToBe',
            data: totalPositiveToBe,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
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

// Chart 2: Pie chart for Country Distribution
var ctx2 = document.getElementById('countryDistributionChart').getContext('2d');
var chart2 = new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: countries,
        datasets: [{
            label: 'Country Distribution',
            data: totalNew, // Using total new orders for each country
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                // Add more colors as needed
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                // Add more colors as needed
            ],
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

// Chart 3: Line chart for Total Kgs Over Time
// Assuming you have time series data for total kgs, you can plot it here

// Chart 4: Timeline chart for Cargo Ready Dates
var ctx4 = document.getElementById('cargoReadyDatesChart').getContext('2d');
var chart4 = new Chart(ctx4, {
    type: 'line',
    data: {
        labels: erps,
        datasets: [{
            label: 'Cargo Ready Dates',
            data: cargoReadyDates,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                type: 'time',
                time: {
                    unit: 'day'
                }
            }
        }
    }
});
</script>

</body>
</html>
