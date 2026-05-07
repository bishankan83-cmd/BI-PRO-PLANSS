<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reject Reasons Pie Chart</title>
  <!-- Include Chart.js library -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php
// Your database connection code goes here
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$connection = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch data from the 'reject123' table
$query = "SELECT reason, COUNT(*) AS count FROM reject123 GROUP BY reason";
$result = mysqli_query($connection, $query);

// Prepare data for Chart.js
$reasons = [];
$counts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reasons[] = $row['reason'];
    $counts[] = $row['count'];
}

?>

<!-- Create a canvas element for the pie chart -->
<canvas id="rejectPieChart" width="400" height="200"></canvas>

<script>
// Create a pie chart using Chart.js
var ctx = document.getElementById('rejectPieChart').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            data: <?php echo json_encode($data); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
    }
});
</script>

</body>
</html>
