<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Chart of Total Stock by Brand</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <canvas id="stockChart" width="400" height="200"></canvas>
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
                    backgroundColor: '#F28018', // Custom color
                    borderColor: '#F28018',     // Custom color for the border
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
    </script>
</body>
</html>
