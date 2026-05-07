<button onclick="window.location.href='dashboard.php'" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Go to Dashboard</button>



<?php
// Database configuration
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Connect to database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for success message from import page
session_start();
$successMsg = "";
if (isset($_SESSION['import_success'])) {
    $successMsg = $_SESSION['import_success'];
    unset($_SESSION['import_success']);
}

// Function to get count from any table
function getCount($conn, $table, $where = "") {
    $whereClause = !empty($where) ? "WHERE $where" : "";
    $query = "SELECT COUNT(*) as total FROM $table $whereClause";
    $result = $conn->query($query);
    return ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;
}

// Get total counts
$totalRegularRecords = getCount($conn, "stock_erp");
$totalTempRecords = getCount($conn, "stock_erp_tem");

// Get today's counts
$today = date('Y-m-d');
$todayRegularRecords = getCount($conn, "stock_erp", "DATE(date) = '$today'");
$todayTempRecords = getCount($conn, "stock_erp_tem", "DATE(date) = '$today'");

// Get tyre codes for chart
$tyreCodes = [];
$tyreCodesQuery = "SELECT tyre_code, COUNT(*) as count FROM stock_erp GROUP BY tyre_code ORDER BY count DESC LIMIT 5";
$tyreCodesResult = $conn->query($tyreCodesQuery);
if ($tyreCodesResult) {
    while ($row = $tyreCodesResult->fetch_assoc()) {
        $tyreCodes[$row['tyre_code']] = $row['count'];
    }
}

// Get monthly data for the line chart
$monthlyData = [];
$monthlyQuery = "SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count FROM stock_erp 
                GROUP BY DATE_FORMAT(date, '%Y-%m') 
                ORDER BY month DESC LIMIT 6";
$monthlyResult = $conn->query($monthlyQuery);
if ($monthlyResult) {
    while ($row = $monthlyResult->fetch_assoc()) {
        $monthlyData[$row['month']] = $row['count'];
    }
}
// Reverse to show in chronological order
$monthlyData = array_reverse($monthlyData, true);

// Get recent activity
$recentActivity = [];
$recentActivityQuery = "
    SELECT 'stock_erp' as table_name, id, serial_number, date, tyre_code, description, qty, 'Regular' as type 
    FROM stock_erp
    UNION
    SELECT 'stock_erp_tem' as table_name, id, serial_number, date, tyre_code, description, qty, 'Temporary' as type
    FROM stock_erp_tem
    ORDER BY date DESC, id DESC
    LIMIT 10
";
$recentActivityResult = $conn->query($recentActivityQuery);
if ($recentActivityResult) {
    while ($row = $recentActivityResult->fetch_assoc()) {
        $recentActivity[] = $row;
    }
}

// Get low stock items
$lowStockItems = [];
$lowStockQuery = "SELECT tyre_code, description, SUM(qty) as total_qty 
                 FROM stock_erp 
                 GROUP BY tyre_code, description 
                 HAVING total_qty < 10 
                 ORDER BY total_qty ASC 
                 LIMIT 5";
$lowStockResult = $conn->query($lowStockQuery);
if ($lowStockResult) {
    while ($row = $lowStockResult->fetch_assoc()) {
        $lowStockItems[] = $row;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock ERP Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 24px;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background: linear-gradient(135deg, #8a6d3b 0%, #6d5022 100%);
            color: white;
            font-weight: bold;
            padding: 18px 20px;
            border-bottom: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3a3a3a 0%, #000000 100%);
            border: none;
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #8a6d3b 0%, #6d5022 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .alert {
            margin-top: 20px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 16px 20px;
        }
        .feature-icon {
            font-size: 36px;
            margin-bottom: 15px;
            color: #8a6d3b;
            transition: transform 0.3s ease;
        }
        .dash-card:hover .feature-icon {
            transform: scale(1.2);
        }
        .dash-card {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .dash-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .dash-card-title {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .dash-card-value {
            font-size: 38px;
            font-weight: 700;
            color: #8a6d3b;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background: linear-gradient(135deg, #f3f3f3 0%, #e6e6e6 100%);
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #8a6d3b;
            padding: 12px 15px;
        }
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(138, 109, 59, 0.05);
        }
        .section-title {
            position: relative;
            margin-bottom: 25px;
            padding-bottom: 15px;
            color: #333;
            font-weight: 600;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #8a6d3b;
        }
        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        .badge-temp {
            background-color: #8a6d3b;
            color: white;
        }
        .badge-regular {
            background-color: #28a745;
            color: white;
        }
        .page-header {
            margin-bottom: 30px;
            text-align: center;
            padding: 20px 0;
            position: relative;
        }
        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .page-subtitle {
            color: #6c757d;
            font-size: A18px;
        }
        .quick-action-cards {
            margin-bottom: 30px;
        }
        .quick-action-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        .quick-action-icon {
            font-size: 28px;
            margin-bottom: 15px;
            color: #8a6d3b;
        }
        .quick-action-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .quick-action-desc {
            font-size: 14px;
            color: #6c757d;
        }
        .nav-pills .nav-link {
            color: #6c757d;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-right: 10px;
        }
        .nav-pills .nav-link.active {
            background-color: #8a6d3b;
            color: white;
        }
        .nav-pills .nav-link:hover:not(.active) {
            background-color: rgba(138, 109, 59, 0.1);
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #d1d1d1;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-green {
            background-color: #28a745;
        }
        .status-yellow {
            background-color: #ffc107;
        }
        .status-red {
            background-color: #dc3545;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 250px;
        }
        @media (max-width: 768px) {
            .dash-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Stock ERP Dashboard</h1>
            <p class="page-subtitle">Complete overview of your inventory management system</p>
        </div>
        
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMsg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        
            
           
        
        <!-- Summary Cards Row -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="dash-card-title">Total FG Stock</div>
                    <div class="dash-card-value"><?php echo number_format($totalRegularRecords); ?></div>
                    <div class="mt-2">
                        <span class="badge badge-regular">Regular Stock</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="dash-card-title">Pending Item</div>
                    <div class="dash-card-value"><?php echo number_format($totalTempRecords); ?></div>
                    <div class="mt-2">
                        <span class="badge badge-temp">Temporary</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="dash-card-title">Today's Records</div>
                    <div class="dash-card-value"><?php echo number_format($todayRegularRecords); ?></div>
                    <div class="mt-2">
                        <span class="badge bg-info">Today's Data</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="dash-card">
                    <div class="feature-icon position-relative">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?php if (count($lowStockItems) > 0): ?>
                        <span class="notification-badge"><?php echo count($lowStockItems); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="dash-card-title">Low Stock Items</div>
                    <div class="dash-card-value"><?php echo count($lowStockItems); ?></div>
                    <div class="mt-2">
                        <span class="badge bg-warning text-dark">Needs Attention</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Row -->
        <div class="row">
            <!-- Left Column - Charts -->
            <div class="col-lg-8">
                <!-- Inventory Trends Chart -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Inventory Trends</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-light" id="monthViewBtn">Month</button>
                            <button type="button" class="btn btn-sm btn-light" id="weekViewBtn">Week</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="inventoryTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Tyre Distribution Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="m-0">Tyre Code Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="tyreDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Quantity</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentActivity)): ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state">
                                                    <i class="bi bi-clock-history"></i>
                                                    <p>No recent activity found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentActivity as $activity): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($activity['serial_number']); ?></td>
                                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($activity['date']))); ?></td>
                                                <td><?php echo htmlspecialchars($activity['tyre_code']); ?></td>
                                                <td><?php echo htmlspecialchars($activity['qty']); ?></td>
                                                <td>
                                                    <?php if ($activity['type'] == 'Regular'): ?>
                                                        <span class="badge badge-regular">Regular</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-temp">Temporary</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Additional Info -->
            <div class="col-lg-4">
                <!-- System Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="m-0">System Status</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="status-indicator status-green"></span>
                                    Database Connection
                                </span>
                                <span class="badge bg-success">Active</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="status-indicator status-green"></span>
                                    Regular Stock Table
                                </span>
                                <span class="badge bg-success">Online</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="status-indicator status-green"></span>
                                    Temporary Stock Table
                                </span>
                                <span class="badge bg-success">Online</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="status-indicator status-green"></span>
                                    Excel Import/Export
                                </span>
                                <span class="badge bg-success">Working</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <span class="status-indicator status-green"></span>
                                    Last System Update
                                </span>
                                <span><?php echo date('d M Y'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Low Stock Alerts -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="m-0">Low Stock Alerts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lowStockItems)): ?>
                            <div class="empty-state">
                                <i class="bi bi-check-circle"></i>
                                <p>No low stock items found</p>
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($lowStockItems as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['tyre_code']); ?></strong>
                                            <p class="mb-0 text-muted small"><?php echo htmlspecialchars($item['description']); ?></p>
                                        </div>
                                        <span class="badge <?php echo $item['total_qty'] < 5 ? 'bg-danger' : 'bg-warning text-dark'; ?>">
                                            <?php echo htmlspecialchars($item['total_qty']); ?> left
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                          
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="m-0">Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="erp_stock_excel.php" class="btn btn-outline-secondary">
                                <i class="bi bi-file-earmark-excel me-2"></i> Excel Manager
                            </a>
                            <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#reportModal">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i> Generate Reports
                            </a>
                            <a href="#" class="btn btn-outline-secondary">
                                <i class="bi bi-gear me-2"></i> System Settings
                            </a>
                            <a href="#" class="btn btn-outline-secondary">
                                <i class="bi bi-people me-2"></i> User Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Record Modal -->
    <div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRecordModalLabel">Add New Stock Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addRecordForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="serialNumber" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serialNumber" required>
                                <div class="form-text">Will be automatically formatted (e.g. 32505031 → 032025-05031)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tyreCode" class="form-label">Tyre Code</label>
                                <input type="text" class="form-control" id="tyreCode" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stockType" id="regularStock" value="regular" checked>
                                <label class="form-check-label" for="regularStock">
                                    Add to Regular Stock
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="stockType" id="tempStock" value="temporary">
                                <label class="form-check-label" for="tempStock">
                                    Add to Temporary Stock
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Add Record</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm">
                        <div class="mb-3">
                            <label class="form-label">Select Data to Export</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exportType" id="exportRegular" value="regular" checked>
                                <label class="form-check-label" for="exportRegular">
                                    Regular Stock
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exportType" id="exportTemp" value="temporary">
                                <label class="form-check-label" for="exportTemp">
                                    Temporary Stock
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exportType" id="exportAll" value="all">
                                <label class="form-check-label" for="exportAll">
                                    All Data
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="thisweek">This Week</option>
                                <option value="thismonth">This Month</option>
                                <option value="lastmonth">Last Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="row" id="customDateRange" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="exportFormat" class="form-label">Export Format</label>
                            <select class="form-select" id="exportFormat">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Export</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">Search Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="searchForm">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="searchQuery" placeholder="Search by serial number, tyre code, or description...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="searchType">
                                    <option value="all">All Fields</option>
                                    <option value="serial">Serial Number</option>
                                    <option value="tyre">Tyre Code</option>
                                    <option value="description">Description</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="searchRegular" value="regular" checked>
                                <label class="form-check-label" for="searchRegular">Regular Stock</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="searchTemp" value="temporary" checked>
                                <label class="form-check-label" for="searchTemp">Temporary Stock</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" id="searchButton">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="advancedSearchToggle">
                                Advanced Search
                            </button>
                        </div>
                        <div id="advancedSearch" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="searchDateFrom" class="form-label">Date From</label>
                                    <input type="date" class="form-control" id="searchDateFrom">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="searchDateTo" class="form-label">Date To</label>
                                    <input type="date" class="form-control" id="searchDateTo">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="searchQtyMin" class="form-label">Min Quantity</label>
                                    <input type="number" class="form-control" id="searchQtyMin" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="searchQtyMax" class="form-label">Max Quantity</label>
                                    <input type="number" class="form-control" id="searchQtyMax" min="0">
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="search-results mt-4" style="display: none;">
                        <h6>Search Results</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="searchResults">
                                    <!-- Search results will be populated here -->
                                </tbody>
                            </table>
                        </div>
                        <div id="noResults" class="text-center py-3" style="display: none;">
                            <i class="bi bi-search me-2"></i> No matching records found
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="search-stats">
                                Showing <span id="resultCount">0</span> results
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-download me-1"></i> Export Results
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reorder Modal -->
    <div class="modal fade" id="reorderModal" tabindex="-1" aria-labelledby="reorderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reorderModalLabel">Reorder Low Stock Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>The following items are running low and need to be reordered:</p>
                    <ul class="list-group mb-3">
                        <?php foreach ($lowStockItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['tyre_code']); ?></strong>
                                    <small class="d-block text-muted"><?php echo htmlspecialchars($item['description']); ?></small>
                                </div>
                                <div>
                                    <span class="badge <?php echo $item['total_qty'] < 5 ? 'bg-danger' : 'bg-warning text-dark'; ?> me-2">
                                        <?php echo htmlspecialchars($item['total_qty']); ?> left
                                    </span>
                                    <input type="number" class="form-control form-control-sm d-inline-block" style="width: 70px;" value="10">
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mb-3">
                        <label for="supplier" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier">
                            <option value="">Select Supplier</option>
                            <option value="1">PlanaTir Tyre Company</option>
                            <option value="2">Continental Supplies Ltd</option>
                            <option value="3">AutoParts Global</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deliveryDate" class="form-label">Expected Delivery Date</label>
                        <input type="date" class="form-control" id="deliveryDate">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Submit Order</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Generate Reports</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Monthly Inventory Summary</h6>
                                <small><i class="bi bi-file-earmark-bar-graph"></i></small>
                            </div>
                            <p class="mb-1 small">Comprehensive overview of stock levels, additions, and removals by month</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Tyre Code Analysis</h6>
                                <small><i class="bi bi-pie-chart"></i></small>
                            </div>
                            <p class="mb-1 small">Breakdown of inventory by tyre code with trends and predictions</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Low Stock Report</h6>
                                <small><i class="bi bi-exclamation-triangle"></i></small>
                            </div>
                            <p class="mb-1 small">Detailed list of items below threshold with reorder recommendations</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Activity Log</h6>
                                <small><i class="bi bi-clock-history"></i></small>
                            </div>
                            <p class="mb-1 small">Chronological record of all system activities and changes</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Custom Report</h6>
                                <small><i class="bi bi-gear"></i></small>
                            </div>
                            <p class="mb-1 small">Create a customized report with specific parameters and data points</p>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Bootstrap components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Show/hide custom date range in export modal
            document.getElementById('dateRange').addEventListener('change', function() {
                if (this.value === 'custom') {
                    document.getElementById('customDateRange').style.display = 'flex';
                } else {
                    document.getElementById('customDateRange').style.display = 'none';
                }
            });
            
            // Toggle advanced search options
            document.getElementById('advancedSearchToggle').addEventListener('click', function() {
                var advancedSearch = document.getElementById('advancedSearch');
                if (advancedSearch.style.display === 'none') {
                    advancedSearch.style.display = 'block';
                    this.textContent = 'Hide Advanced Search';
                } else {
                    advancedSearch.style.display = 'none';
                    this.textContent = 'Advanced Search';
                }
            });
            
            // Initialize Monthly Trend Chart
            const trendsCtx = document.getElementById('inventoryTrendsChart').getContext('2d');
            const monthlyData = <?php echo json_encode(array_values($monthlyData)); ?>;
            const monthlyLabels = <?php echo json_encode(array_map(function($month) { 
                return date('M Y', strtotime($month . '-01')); 
            }, array_keys($monthlyData))); ?>;
            
            const trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Inventory Count',
                        data: monthlyData,
                        backgroundColor: 'rgba(138, 109, 59, 0.2)',
                        borderColor: 'rgba(138, 109, 59, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(138, 109, 59, 1)',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 10,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(138, 109, 59, 1)',
                            borderWidth: 1
                        }
                    }
                }
            });
            
            // Initialize Tyre Distribution Chart
            const tyreCtx = document.getElementById('tyreDistributionChart').getContext('2d');
            const tyreCodeLabels = <?php echo json_encode(array_keys($tyreCodes)); ?>;
            const tyreCodeData = <?php echo json_encode(array_values($tyreCodes)); ?>;
            
            const colorPalette = [
                'rgba(138, 109, 59, 0.8)',
                'rgba(100, 80, 43, 0.8)',
                'rgba(175, 138, 75, 0.8)',
                'rgba(80, 64, 34, 0.8)',
                'rgba(210, 166, 90, 0.8)'
            ];
            
            const tyreChart = new Chart(tyreCtx, {
                type: 'bar',
                data: {
                    labels: tyreCodeLabels,
                    datasets: [{
                        label: 'Quantity',
                        data: tyreCodeData,
                        backgroundColor: colorPalette,
                        borderColor: colorPalette.map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Month vs Week view toggle for chart
            document.getElementById('monthViewBtn').addEventListener('click', function() {
                trendsChart.data.labels = monthlyLabels;
                trendsChart.data.datasets[0].data = monthlyData;
                trendsChart.update();
                
                this.classList.add('btn-primary');
                this.classList.remove('btn-light');
                document.getElementById('weekViewBtn').classList.add('btn-light');
                document.getElementById('weekViewBtn').classList.remove('btn-primary');
            });
            
            document.getElementById('weekViewBtn').addEventListener('click', function() {
                // This would typically fetch weekly data from the server
                // For demo purposes, we'll just use a modified version of the monthly data
                const weeklyLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                const weeklyData = [42, 38, 55, 50]; // Demo data
                
                trendsChart.data.labels = weeklyLabels;
                trendsChart.data.datasets[0].data = weeklyData;
                trendsChart.update();
                
                this.classList.add('btn-primary');
                this.classList.remove('btn-light');
                document.getElementById('monthViewBtn').classList.add('btn-light');
                document.getElementById('monthViewBtn').classList.remove('btn-primary');
            });
            
            // Search form handling
            document.getElementById('searchButton').addEventListener('click', function() {
                // In a real implementation, this would send an AJAX request to the server
                // For demo purposes, just show the results section with dummy data
                document.querySelector('.search-results').style.display = 'block';
                
                const searchQuery = document.getElementById('searchQuery').value.trim();
                if (searchQuery === '') {
                    document.getElementById('noResults').style.display = 'block';
                    document.getElementById('searchResults').innerHTML = '';
                    document.getElementById('resultCount').textContent = '0';
                } else {
                    document.getElementById('noResults').style.display = 'none';
                    // Populate with dummy results
                    let resultsHTML = '';
                    for (let i = 0; i < 3; i++) {
                        resultsHTML += `
                            <tr>
                                <td>032023-${Math.floor(10000 + Math.random() * 90000)}</td>
                                <td>${new Date().toLocaleDateString()}</td>
                                <td>${searchQuery.toUpperCase()}</td>
                                <td>Sample description for ${searchQuery}</td>
                                <td>${Math.floor(1 + Math.random() * 10)}</td>
                                <td><span class="badge badge-regular">Regular</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary">Edit</button>
                                </td>
                            </tr>
                        `;
                    }
                    document.getElementById('searchResults').innerHTML = resultsHTML;
                    document.getElementById('resultCount').textContent = '3';
                }
            });
        });
    </script>
</body>
</html>