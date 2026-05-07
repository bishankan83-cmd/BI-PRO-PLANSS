<?php
// Database connection parameters
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

// Function to parse serial number
function parseSerialNumber($serialNumber) {
    // Remove any hyphens that might be in the serial number
    $cleanSerial = str_replace('-', '', $serialNumber);
    
    // Extract components based on MMYYYYNNNNN format
    if (strlen($cleanSerial) >= 9) {
        // The first 2 digits represent the month
        $month = substr($cleanSerial, 0, 2);
        
        // The next 4 digits represent the full year (not just last 2 digits)
        $year = substr($cleanSerial, 2, 4);
        
        // The remaining digits represent the tire number
        $tyreNumber = substr($cleanSerial, 6);
        
        return [
            'month' => intval($month),
            'year' => intval($year),
            'tyreNumber' => intval($tyreNumber),
            'monthName' => getMonthName(intval($month))
        ];
    }
    
    return [
        'month' => 0,
        'year' => 0,
        'tyreNumber' => 0,
        'monthName' => 'Unknown'
    ];
}

// Helper function to get month name
function getMonthName($month) {
    $monthNames = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    
    if ($month >= 1 && $month <= 12) {
        return $monthNames[$month];
    }
    return 'Unknown';
}

// Query to get data from stock_erp table
$sql = "SELECT * FROM stock_erp ORDER BY date DESC";
$result = $conn->query($sql);

// Process the data
$processedData = [];
$inventoryByMonth = [];
$inventoryByTyreCode = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Parse serial numbers
        $parsedSerial = parseSerialNumber($row["serial_number"]);
        $parsedPrevSerial = parseSerialNumber($row["prev_serial"]);
        
        // Add parsed data to the row
        $row["serialParsed"] = $parsedSerial;
        $row["prevSerialParsed"] = $parsedPrevSerial;
        
        // Add to processed data array
        $processedData[] = $row;
        
        // Build inventory by month data
        $monthYear = $parsedSerial['monthName'] . " " . $parsedSerial['year'];
        if (!isset($inventoryByMonth[$monthYear])) {
            $inventoryByMonth[$monthYear] = 0;
        }
        $inventoryByMonth[$monthYear] += $row["qty"];
        
        // Build inventory by tyre code data
        if (!isset($inventoryByTyreCode[$row["tyre_code"]])) {
            $inventoryByTyreCode[$row["tyre_code"]] = [
                'code' => $row["tyre_code"],
                'description' => $row["description"],
                'quantity' => 0
            ];
        }
        $inventoryByTyreCode[$row["tyre_code"]]['quantity'] += $row["qty"];
    }
} 

// Convert inventory by month to JSON for charts
$inventoryByMonthJson = json_encode(array_map(
    function($key, $value) {
        return ['month' => $key, 'quantity' => $value];
    },
    array_keys($inventoryByMonth),
    array_values($inventoryByMonth)
));

// Convert inventory by tyre code to JSON for charts
$inventoryByTyreCodeJson = json_encode(array_values($inventoryByTyreCode));

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tire Inventory Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            border-radius: 10px 10px 0 0 !important;
        }
        .table-responsive {
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="text-center">Tire Inventory Dashboard</h1>
        </div>
    </div>
    
    <div class="row">
        <!-- Monthly Inventory Chart -->
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inventory by Month</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Tire Type Distribution Chart -->
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Inventory by Tire Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="tyreCodeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Detailed Inventory Table -->
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detailed Inventory</h5>
                    <div class="form-group mb-0">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Serial Number</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Tire Number</th>
                                <th>Date</th>
                                <th>Tire Code</th>
                                <th>Description</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processedData as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><?php echo $item['serial_number']; ?></td>
                                <td><?php echo $item['serialParsed']['monthName']; ?></td>
                                <td><?php echo $item['serialParsed']['year']; ?></td>
                                <td><?php echo $item['serialParsed']['tyreNumber']; ?></td>
                                <td><?php echo $item['date']; ?></td>
                                <td><?php echo $item['tyre_code']; ?></td>
                                <td><?php echo $item['description']; ?></td>
                                <td><?php echo $item['qty']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Serial Number Analysis -->
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Serial Number Analysis</h5>
                </div>
                <div class="card-body">
                    <p><strong>Format:</strong> MMYYYYNNNNN where:</p>
                    <ul>
                        <li>MM = Month (e.g., 03)</li>
                        <li>YYYY = Full Year (e.g., 2025)</li>
                        <li>NNNNN = Tire Number (e.g., 07753)</li>
                    </ul>
                    <p><strong>Example:</strong> 032025-07753 is March 2025, tire #07753</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Sort function for month names
function sortByMonth(data) {
    const monthOrder = {
        'January': 1, 'February': 2, 'March': 3, 'April': 4, 
        'May': 5, 'June': 6, 'July': 7, 'August': 8, 
        'September': 9, 'October': 10, 'November': 11, 'December': 12
    };
    
    return data.sort((a, b) => {
        // Split month and year
        const [aMonth, aYear] = a.month.split(' ');
        const [bMonth, bYear] = b.month.split(' ');
        
        // Compare years first
        if (aYear !== bYear) {
            return parseInt(aYear) - parseInt(bYear);
        }
        
        // If years are the same, compare months
        return monthOrder[aMonth] - monthOrder[bMonth];
    });
}

// Monthly Inventory Chart
let monthlyChartData = <?php echo $inventoryByMonthJson; ?>;
monthlyChartData = sortByMonth(monthlyChartData);

const monthlyChart = new Chart(
    document.getElementById('monthlyChart'),
    {
        type: 'bar',
        data: {
            labels: monthlyChartData.map(row => row.month),
            datasets: [
                {
                    label: 'Quantity',
                    data: monthlyChartData.map(row => row.quantity),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Inventory'
                }
            }
        }
    }
);

// Tire Type Chart
const tyreCodeChartData = <?php echo $inventoryByTyreCodeJson; ?>;
const tyreCodeChart = new Chart(
    document.getElementById('tyreCodeChart'),
    {
        type: 'pie',
        data: {
            labels: tyreCodeChartData.map(row => row.code),
            datasets: [
                {
                    label: 'Quantity',
                    data: tyreCodeChartData.map(row => row.quantity),
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ]
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Distribution by Tire Type'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const data = context.dataset.data;
                            const total = data.reduce((acc, val) => acc + val, 0);
                            const value = data[context.dataIndex];
                            const percentage = ((value / total) * 100).toFixed(1) + '%';
                            return `${context.label}: ${value} (${percentage})`;
                        }
                    }
                }
            }
        }
    }
);

// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const table = document.getElementById('inventoryTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
            const cellValue = cells[j].textContent.toLowerCase();
            
            if (cellValue.indexOf(searchValue) > -1) {
                found = true;
                break;
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
});
</script>

</body>
</html>