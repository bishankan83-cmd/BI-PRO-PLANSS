<?php
// config.php - Database configuration
class Config {
    const DB_HOST = "localhost";
    const DB_USER = "planatir_task_managemen";
    const DB_PASS = "Bishan@1919";
    const DB_NAME = "planatir_task_managemen";
    const ITEMS_PER_PAGE = 1000;
}

// models/Database.php - Database connection handling
class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = new mysqli(
            Config::DB_HOST, 
            Config::DB_USER, 
            Config::DB_PASS, 
            Config::DB_NAME
        );
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->setupErrorReporting();
    }
    
    private function setupErrorReporting() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn->close();
    }
    
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
}

// models/StockModel.php - Stock data operations
class StockModel {
    private $db;
    private $conn;
    
    public function __construct(Database $db) {
        $this->db = $db;
        $this->conn = $db->getConnection();
    }
    
    public function getFilteredStock($filters, $pagination) {
        $where_clause = $this->buildWhereClause($filters);
        
        $offset = ($pagination['page'] - 1) * Config::ITEMS_PER_PAGE;
        
        $sql = "SELECT s.*, td.brand FROM stock_erp s 
                LEFT JOIN tire_details td ON s.tyre_code = td.icode
                WHERE 1=1" . $where_clause . " 
                ORDER BY s.id DESC LIMIT " . $offset . ", " . Config::ITEMS_PER_PAGE;
                
        return $this->conn->query($sql);
    }
    
    public function countFilteredStock($filters) {
        $where_clause = $this->buildWhereClause($filters);
        
        $count_sql = "SELECT COUNT(*) as total FROM stock_erp s 
                      LEFT JOIN tire_details td ON s.tyre_code = td.icode
                      WHERE 1=1" . $where_clause;
                      
        $count_result = $this->conn->query($count_sql);
        $count_row = $count_result->fetch_assoc();
        
        return $count_row['total'];
    }
    
    public function getDistinctValues($field, $table = 'stock_erp', $orderBy = null) {
        if ($orderBy === null) {
            $orderBy = $field;
        }
        
        $sql = "SELECT DISTINCT $field FROM $table ORDER BY $orderBy ASC";
        return $this->conn->query($sql);
    }
    
    public function getDistinctMonths() {
        $sql = "SELECT DISTINCT LEFT(serial_number, 2) as month FROM stock_erp ORDER BY month ASC";
        return $this->conn->query($sql);
    }
    
    public function getDistinctYears() {
        $sql = "SELECT DISTINCT SUBSTRING(serial_number, 3, 2) as year FROM stock_erp ORDER BY year ASC";
        return $this->conn->query($sql);
    }
    
    private function buildWhereClause($filters) {
        $where_clause = "";
        
        // Serial number filtering
        if (!empty($filters['serial_select']) && $filters['serial_select'] != "all") {
            $where_clause .= " AND s.serial_number = '" . $this->db->escapeString($filters['serial_select']) . "'";
        } elseif (!empty($filters['serial_filter'])) {
            $where_clause .= " AND s.serial_number LIKE '%" . $this->db->escapeString($filters['serial_filter']) . "%'";
        }
        
        // Month filtering
        if (!empty($filters['month_filter'])) {
            $month_formatted = str_pad($filters['month_filter'], 2, '0', STR_PAD_LEFT);
            $where_clause .= " AND s.serial_number LIKE '" . $this->db->escapeString($month_formatted) . "%'";
        }
        
        // Year filtering
        if (!empty($filters['year_filter'])) {
            $year_filter = $filters['year_filter'];
            
            // Take last 2 digits of year if 4 digits were entered
            if (strlen($year_filter) == 4) {
                $year_filter = substr($year_filter, 2, 2);
            }
            
            // If month is also filtered, we add it to the pattern
            if (!empty($filters['month_filter'])) {
                $month_formatted = str_pad($filters['month_filter'], 2, '0', STR_PAD_LEFT);
                $where_clause .= " AND s.serial_number LIKE '" . $this->db->escapeString($month_formatted . $year_filter) . "%'";
            } else {
                // If only year is filtered, we need to match the pattern with any month
                $where_clause .= " AND s.serial_number LIKE '__" . $this->db->escapeString($year_filter) . "%'";
            }
        }
        
        // Tyre code filtering
        if (!empty($filters['tyre_code_select']) && $filters['tyre_code_select'] != "all") {
            $where_clause .= " AND s.tyre_code = '" . $this->db->escapeString($filters['tyre_code_select']) . "'";
        } elseif (!empty($filters['tyre_code_filter'])) {
            $where_clause .= " AND s.tyre_code LIKE '%" . $this->db->escapeString($filters['tyre_code_filter']) . "%'";
        }
        
        // Brand filtering
        if (!empty($filters['brand_select']) && $filters['brand_select'] != "all") {
            $where_clause .= " AND td.brand = '" . $this->db->escapeString($filters['brand_select']) . "'";
        } elseif (!empty($filters['brand_filter'])) {
            $where_clause .= " AND td.brand LIKE '%" . $this->db->escapeString($filters['brand_filter']) . "%'";
        }
        
        // Date filtering
        if (!empty($filters['date_filter'])) {
            $where_clause .= " AND s.date = '" . $this->db->escapeString($filters['date_filter']) . "'";
        }
        
        // Description filtering
        if (!empty($filters['description_select']) && $filters['description_select'] != "all") {
            $where_clause .= " AND s.description = '" . $this->db->escapeString($filters['description_select']) . "'";
        } elseif (!empty($filters['description_filter'])) {
            $where_clause .= " AND s.description LIKE '%" . $this->db->escapeString($filters['description_filter']) . "%'";
        }
        
        return $where_clause;
    }
}

// controllers/StockController.php - Handle requests and responses
class StockController {
    private $stockModel;
    private $filters = [
        'serial_filter' => '',
        'serial_select' => '',
        'month_filter' => '',
        'year_filter' => '',
        'date_filter' => '',
        'tyre_code_filter' => '',
        'tyre_code_select' => '',
        'description_filter' => '',
        'description_select' => '',
        'brand_filter' => '',
        'brand_select' => ''
    ];
    
    public function __construct(StockModel $stockModel) {
        $this->stockModel = $stockModel;
    }
    
    public function handleRequest() {
        // Process form submissions
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['filter'])) {
                $this->processFilterForm();
            } elseif (isset($_POST['reset'])) {
                $this->resetFilters();
            }
        }
        
        // Get current page for pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Retrieve filtered data
        $data = $this->fetchData($page);
        
        // Load view
        $this->renderView($data);
    }
    
    private function processFilterForm() {
        foreach ($this->filters as $key => $value) {
            if (isset($_POST[$key])) {
                $this->filters[$key] = $_POST[$key];
            }
        }
    }
    
    private function resetFilters() {
        foreach ($this->filters as $key => $value) {
            $this->filters[$key] = '';
        }
    }
    
    private function fetchData($page) {
        // Count total filtered items
        $totalItems = $this->stockModel->countFilteredStock($this->filters);
        $totalPages = ceil($totalItems / Config::ITEMS_PER_PAGE);
        
        // Get data with pagination
        $stockData = $this->stockModel->getFilteredStock($this->filters, ['page' => $page]);
        
        // Get dropdowns data
        $serialNumbers = $this->stockModel->getDistinctValues('serial_number');
        $months = $this->stockModel->getDistinctMonths();
        $years = $this->stockModel->getDistinctYears();
        $tyreCodes = $this->stockModel->getDistinctValues('tyre_code');
        $descriptions = $this->stockModel->getDistinctValues('description');
        $brands = $this->stockModel->getDistinctValues('brand', 'tire_details');
        
        return [
            'stock' => $stockData,
            'serialNumbers' => $serialNumbers,
            'months' => $months,
            'years' => $years,
            'tyreCodes' => $tyreCodes,
            'descriptions' => $descriptions,
            'brands' => $brands,
            'filters' => $this->filters,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems
            ]
        ];
    }
    
    private function renderView($data) {
        // We'll create this view separately
        include 'views/stock_view.php';
    }
}

// views/stock_view.php - Presentation layer
// See below for the view content

// index.php - Entry point
$db = new Database();
$stockModel = new StockModel($db);
$controller = new StockController($stockModel);

// Handle the request
$controller->handleRequest();

// Close the database connection
$db->closeConnection();
?>

<!-- views/stock_view.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Include modern charting libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3@7.8.5/dist/d3.min.js"></script>
    <style>
        :root {
            --primary-color: #343a40;
            --accent-color: #F28018;
            --light-bg: #f0f0f0;
            --card-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Roboto', sans-serif;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px;
            border: 2px solid var(--accent-color);
        }
        
        .card {
            border-radius: 15px;
            border: 2px solid var(--accent-color);
            box-shadow: var(--card-shadow);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: 2px solid var(--accent-color);
        }
        
        .filter-group {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        
        .table thead {
            background-color: var(--accent-color);
            color: #000000;
            position: sticky;
            top: 0;
        }
        
        .count-badge {
            background-color: var(--accent-color);
            color: #000000;
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        /* Chart containers */
        .chart-container {
            position: relative;
            margin: 20px 0;
            height: 300px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            background-color: white;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-left: 5px solid var(--accent-color);
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: var(--primary-color);
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="header p-4 mb-4">
            <h1 class="mb-0">Stock Management Dashboard</h1>
            <p class="mb-0">Modern visualization and management system</p>
        </div>
        
        <!-- Alerts -->
        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['success'] ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['error'] ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Dashboard Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Dashboard Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="dashboard-stats">
                            <div class="stat-card">
                                <h3>Total Items</h3>
                                <div class="stat-value"><?php echo number_format($data['pagination']['totalItems']); ?></div>
                                <p>Items in inventory</p>
                            </div>
                            
                            <div class="stat-card">
                                <h3>Unique Brands</h3>
                                <div class="stat-value"><?php echo $data['brands']->num_rows; ?></div>
                                <p>Different tire brands</p>
                            </div>
                            
                            <div class="stat-card">
                                <h3>Tire Codes</h3>
                                <div class="stat-value"><?php echo $data['tyreCodes']->num_rows; ?></div>
                                <p>Different tire codes</p>
                            </div>
                            
                            <div class="stat-card">
                                <h3>Production Periods</h3>
                                <div class="stat-value"><?php echo $data['months']->num_rows; ?></div>
                                <p>Months with production</p>
                            </div>
                        </div>
                        
                        <!-- Charts Row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="monthlyProductionChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="brandDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="chart-container">
                                    <canvas id="yearlyTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Filter Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Advanced Filters</h5>
                        <span class="count-badge"><?php echo number_format($data['pagination']['totalItems']); ?> items found</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row g-3">
                                <!-- Serial Number Filters -->
                                <div class="col-md-4">
                                    <div class="filter-group">
                                        <h6><i class="fas fa-barcode me-2"></i>Serial Number</h6>
                                        <div class="mb-2">
                                            <label for="serial_select" class="form-label">Select Serial:</label>
                                            <select name="serial_select" id="serial_select" class="form-select">
                                                <option value="all">All Serial Numbers</option>
                                                <?php while($row = $data['serialNumbers']->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($row['serial_number']); ?>" 
                                                        <?php echo ($data['filters']['serial_select'] == $row['serial_number']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($row['serial_number']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="serial_filter" class="form-label">Search Serial:</label>
                                            <input type="text" class="form-control" id="serial_filter" name="serial_filter" 
                                                value="<?php echo htmlspecialchars($data['filters']['serial_filter']); ?>" 
                                                placeholder="Type to search...">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Date and Production Filters -->
                                <div class="col-md-4">
                                    <div class="filter-group">
                                        <h6><i class="fas fa-calendar me-2"></i>Date & Production</h6>
                                        <div class="mb-2">
                                            <label for="month_filter" class="form-label">Production Month:</label>
                                            <select name="month_filter" id="month_filter" class="form-select">
                                                <option value="">All Months</option>
                                                <?php while($row = $data['months']->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($row['month']); ?>" 
                                                        <?php echo ($data['filters']['month_filter'] == $row['month']) ? 'selected' : ''; ?>>
                                                        <?php 
                                                            $month_name = date('F', mktime(0, 0, 0, (int)$row['month'], 1));
                                                            echo htmlspecialchars($month_name . ' (' . $row['month'] . ')'); 
                                                        ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="year_filter" class="form-label">Production Year:</label>
                                            <select name="year_filter" id="year_filter" class="form-select">
                                                <option value="">All Years</option>
                                                <?php while($row = $data['years']->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($row['year']); ?>" 
                                                        <?php echo ($data['filters']['year_filter'] == $row['year']) ? 'selected' : ''; ?>>
                                                        <?php 
                                                            $full_year = '20' . $row['year']; // Assuming years are in 'YY' format and belong to 2000s
                                                            echo htmlspecialchars($full_year); 
                                                        ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="date_filter" class="form-label">System Date:</label>
                                            <input type="date" class="form-control" id="date_filter" name="date_filter" 
                                                value="<?php echo htmlspecialchars($data['filters']['date_filter']); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tyre Details Filters -->
                                <div class="col-md-4">
                                    <div class="filter-group">
                                        <h6><i class="fas fa-circle me-2"></i>Tyre Details</h6>
                                        <div class="mb-2">
                                            <label for="tyre_code_select" class="form-label">Tyre Code:</label>
                                            <select name="tyre_code_select" id="tyre_code_select" class="form-select">
                                                <option value="all">All Tyre Codes</option>
                                                <?php while($row = $data['tyreCodes']->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($row['tyre_code']); ?>" 
                                                        <?php echo ($data['filters']['tyre_code_select'] == $row['tyre_code']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($row['tyre_code']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="brand_select" class="form-label">Brand:</label>
                                            <select name="brand_select" id="brand_select" class="form-select">
                                                <option value="all">All Brands</option>
                                                <?php while($row = $data['brands']->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($row['brand']); ?>" 
                                                        <?php echo ($data['filters']['brand_select'] == $row['brand']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($row['brand']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label for="description_select" class="form-label">Description:</label>
                                            <select name="description_select" id="description_select" class="form-select">
                                                <option value="all">All Descriptions</option>
                                                <?php while($row = $data['descriptions']->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($row['description']); ?>" 
                                                        <?php echo ($data['filters']['description_select'] == $row['description']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($row['description']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" name="filter" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                                <button type="submit" name="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo me-2"></i>Reset Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Stock Results</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 600px;">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Serial Number</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Brand</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($data['stock']->num_rows > 0) {
                                        while($row = $data['stock']->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tyre_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['brand'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td>
                                            <?php if (isset($row['status'])): ?>
                                                <span class="badge <?php echo $row['status'] == 'Active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Unknown</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary view-item" data-id="<?php echo $row['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary edit-item" data-id="<?php echo $row['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>No stock items found. Try adjusting your filters.
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($data['pagination']['totalPages'] > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <!-- Previous Button -->
                                <li class="page-item <?php echo ($data['pagination']['currentPage'] <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $data['pagination']['currentPage'] - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <!-- Page Numbers -->
                                <?php
                                $startPage = max(1, $data['pagination']['currentPage'] - 2);
                                $endPage = min($data['pagination']['totalPages'], $data['pagination']['currentPage'] + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                <li class="page-item <?php echo ($i == $data['pagination']['currentPage']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <!-- Next Button -->
                                <li class="page-item <?php echo ($data['pagination']['currentPage'] >= $data['pagination']['totalPages']) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $data['pagination']['currentPage'] + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart Initialization Scripts -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Monthly Production Chart
                const monthlyCtx = document.getElementById('monthlyProductionChart').getContext('2d');
                const monthlyChart = new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: [
                            'January', 'February', 'March', 'April', 'May', 'June', 
                            'July', 'August', 'September', 'October', 'November', 'December'
                        ],
                        datasets: [{
                            label: 'Monthly Production',
                            data: [
                                <?php
                                // Initialize an array with 12 months set to 0
                                $monthlyData = array_fill(0, 12, 0);
                                
                                // Reset pointer to beginning of result set
                                $data['months']->data_seek(0);
                                
                                // Count items per month (this is a simplified example)
                                while($row = $data['months']->fetch_assoc()) {
                                    // Month is 1-indexed in the data but 0-indexed in our array
                                    $monthIdx = (int)$row['month'] - 1;
                                    if ($monthIdx >= 0 && $monthIdx < 12) {
                                        // In a real implementation, you'd want to count actual items
                                        // This is just a placeholder that increments by 10
                                        $monthlyData[$monthIdx] += 10;
                                    }
                                }
                                
                                // Output the data for the chart
                                echo implode(', ', $monthlyData);
                                ?>
                            ],
                            backgroundColor: 'rgba(242, 128, 24, 0.6)',
                            borderColor: 'rgba(242, 128, 24, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Monthly Production Distribution',
                                font: {
                                    size: 16
                                }
                            },
                            legend: {
                                position: 'top',
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
                                    text: 'Month'
                                }
                            }
                        }
                    }
                });
                
                // Brand Distribution Chart
                const brandCtx = document.getElementById('brandDistributionChart').getContext('2d');
                const brandChart = new Chart(brandCtx, {
                    type: 'pie',
                    data: {
                        labels: [
                            <?php
                            // Reset pointer to beginning of result set
                            $data['brands']->data_seek(0);
                            
                            $brandLabels = [];
                            while($row = $data['brands']->fetch_assoc()) {
                                if (!empty($row['brand'])) {
                                    $brandLabels[] = "'" . addslashes($row['brand']) . "'";
                                }
                            }
                            echo implode(', ', array_slice($brandLabels, 0, 5)); // Limit to top 5 for readability
                            ?>
                        ],
                        datasets: [{
                            data: [35, 25, 20, 15, 5], // Placeholder data
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Brand Distribution',
                                font: {
                                    size: 16
                                }
                            },
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
                
                // Yearly Trend Chart
                const yearlyCtx = document.getElementById('yearlyTrendChart').getContext('2d');
                const yearlyChart = new Chart(yearlyCtx, {
                    type: 'line',
                    data: {
                        labels: [
                            <?php
                            // Reset pointer to beginning of result set
                            $data['years']->data_seek(0);
                            
                            $yearLabels = [];
                            while($row = $data['years']->fetch_assoc()) {
                                $yearLabels[] = "'20" . $row['year'] . "'"; // Assuming 2-digit years from 2000s
                            }
                            echo implode(', ', $yearLabels);
                            ?>
                        ],
                        datasets: [{
                            label: 'Total Items',
                            data: [120, 190, 300, 500, 250, 400], // Placeholder data
                            fill: false,
                            borderColor: 'rgb(242, 128, 24)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Yearly Production Trend',
                                font: {
                                    size: 16
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
                                    text: 'Year'
                                }
                            }
                        }
                    }
                });
            });
        </script>
        
        <!-- Modal for Item Details -->
        <div class="modal fade" id="itemDetailModal" tabindex="-1" aria-labelledby="itemDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="itemDetailModalLabel">Item Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="itemDetails">
                            <!-- Details will be loaded dynamically via JavaScript -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading item details...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="editItemBtn">Edit Item</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- JavaScript for Modal Functionality -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // View Item Modal
                const viewButtons = document.querySelectorAll('.view-item');
                const editButtons = document.querySelectorAll('.edit-item');
                const itemDetailModal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
                const itemDetails = document.getElementById('itemDetails');
                const editItemBtn = document.getElementById('editItemBtn');
                
                // Event listener for view buttons
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const itemId = this.dataset.id;
                        
                        // For demo purposes, we'll just show some placeholder content
                        // In a real app, you would fetch this data from the server with AJAX
                        itemDetails.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold">General Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">ID</th>
                                            <td>${itemId}</td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number</th>
                                            <td>SN${Math.floor(Math.random() * 10000).toString().padStart(5, '0')}</td>
                                        </tr>
                                        <tr>
                                            <th>Date Added</th>
                                            <td>${new Date().toLocaleDateString()}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><span class="badge bg-success">Active</span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Tyre Details</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Tyre Code</th>
                                            <td>TC${Math.floor(Math.random() * 1000)}</td>
                                        </tr>
                                        <tr>
                                            <th>Brand</th>
                                            <td>Premium Brand</td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>High-quality performance tyre</td>
                                        </tr>
                                        <tr>
                                            <th>Production Date</th>
                                            <td>${Math.floor(Math.random() * 12) + 1}/${Math.floor(Math.random() * 10) + 10}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="fw-bold">Additional Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="20%">Warehouse Location</th>
                                            <td>Section A, Row ${Math.floor(Math.random() * 10) + 1}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated</th>
                                            <td>${new Date().toLocaleString()}</td>
                                        </tr>
                                        <tr>
                                            <th>Notes</th>
                                            <td>This is a sample note for item #${itemId}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        `;
                        
                        itemDetailModal.show();
                    });
                });
                
                // Event listener for edit buttons
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const itemId = this.dataset.id;
                        // Redirect to edit page or show edit modal
                        alert('Edit functionality would open for item #' + itemId);
                    });
                });
                
                // Event listener for edit button in detail modal
                editItemBtn.addEventListener('click', function() {
                    itemDetailModal.hide();
                    alert('Edit functionality would open from modal');
                });
            });
        </script>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>