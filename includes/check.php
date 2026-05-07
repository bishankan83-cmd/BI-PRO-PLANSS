<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Quantity Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php
    // Database connection
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $database = "planatir_task_managemen";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to get brand quantities
    $brandQuery = "SELECT 
        CONCAT(brand, ' x ', quantity) AS increased_brand,
        brand,
        quantity,
        Customer,
        date,
        wono
    FROM dwork2
    ORDER BY increased_brand ASC";
    $result = $conn->query($brandQuery);

    // Prepare data for chart and table
    $brands = [];
    $quantities = [];
    $tableData = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row['increased_brand'];
            $quantities[] = $row['quantity'];
            $tableData[] = $row;
        }
    }
    ?>

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Chart Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="gradient-bg p-6 text-white">
                    <h2 class="text-2xl font-bold">Brand Quantity Distribution</h2>
                </div>
                <div class="p-6">
                    <canvas id="brandChart"></canvas>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="gradient-bg p-6 text-white">
                    <h2 class="text-2xl font-bold">Detailed Brand Quantities</h2>
                </div>
                <div class="p-6 max-h-[500px] overflow-y-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600">
                                <th class="p-3 text-left">Increased Brand</th>
                                <th class="p-3 text-left">Customer</th>
                                <th class="p-3 text-left">Date</th>
                                <th class="p-3 text-left">Work Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableData as $row): ?>
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="p-3"><?php echo htmlspecialchars($row['increased_brand']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['Customer']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['date']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($row['wono']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart.js configuration
        const ctx = document.getElementById('brandChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($brands); ?>,
                datasets: [{
                    label: 'Quantity by Brand',
                    data: <?php echo json_encode($quantities); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Brand x Quantity'
                        },
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>