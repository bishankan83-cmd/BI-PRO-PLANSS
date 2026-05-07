<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
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

// Set default values for filters
$where_clause = "";
$serial_filter = "";
$serial_select = ""; 
$date_filter = "";
$tyre_code_filter = "";
$tyre_code_select = ""; 
$description_filter = "";
$description_select = ""; 
$brand_filter = ""; 
$brand_select = "";
$month_filter = "all";
$year_filter = "all";

// Handle filters if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter'])) {
    // For serial number, check both the dropdown and text input
    if (!empty($_POST['serial_select']) && $_POST['serial_select'] != "all") {
        $serial_select = $_POST['serial_select'];
        $where_clause .= " AND s.serial_number = '" . $conn->real_escape_string($serial_select) . "'";
    } elseif (!empty($_POST['serial_filter'])) {
        $serial_filter = $_POST['serial_filter'];
        $where_clause .= " AND s.serial_number LIKE '%" . $conn->real_escape_string($serial_filter) . "%'";
    }
    
    // For tyre code, check both the dropdown and text input
    if (!empty($_POST['tyre_code_select']) && $_POST['tyre_code_select'] != "all") {
        $tyre_code_select = $_POST['tyre_code_select'];
        $where_clause .= " AND s.tyre_code = '" . $conn->real_escape_string($tyre_code_select) . "'";
    } elseif (!empty($_POST['tyre_code_filter'])) {
        $tyre_code_filter = $_POST['tyre_code_filter'];
        $where_clause .= " AND s.tyre_code LIKE '%" . $conn->real_escape_string($tyre_code_filter) . "%'";
    }
    
    // For brand, check both the dropdown and text input
    if (!empty($_POST['brand_select']) && $_POST['brand_select'] != "all") {
        $brand_select = $_POST['brand_select'];
        $where_clause .= " AND td.brand = '" . $conn->real_escape_string($brand_select) . "'";
    } elseif (!empty($_POST['brand_filter'])) {
        $brand_filter = $_POST['brand_filter'];
        $where_clause .= " AND td.brand LIKE '%" . $conn->real_escape_string($brand_filter) . "%'";
    }
    
    if (!empty($_POST['date_filter'])) {
        $date_filter = $_POST['date_filter'];
        $where_clause .= " AND s.date = '" . $conn->real_escape_string($date_filter) . "'";
    }
    
    // Updated description filter handling to check both dropdown and text input
    if (!empty($_POST['description_select']) && $_POST['description_select'] != "all") {
        $description_select = $_POST['description_select'];
        $where_clause .= " AND s.description = '" . $conn->real_escape_string($description_select) . "'";
    } elseif (!empty($_POST['description_filter'])) {
        $description_filter = $_POST['description_filter'];
        $where_clause .= " AND s.description LIKE '%" . $conn->real_escape_string($description_filter) . "%'";
    }
    
    // Handle month filter
    if (!empty($_POST['month_filter']) && $_POST['month_filter'] != "all") {
        $month_filter = $_POST['month_filter'];
        // Add month filter using SUBSTRING_INDEX and SUBSTRING functions to extract month part
        // This handles both formats: MMYYYYNNNNN and MMYY-NNNNN
        $where_clause .= " AND (
            (SUBSTRING(s.serial_number, 1, 2) = '" . $conn->real_escape_string($month_filter) . "')
            OR
            (SUBSTRING(SUBSTRING_INDEX(s.serial_number, '-', 1), 1, 2) = '" . $conn->real_escape_string($month_filter) . "')
        )";
    }
    
    // Handle year filter
    if (!empty($_POST['year_filter']) && $_POST['year_filter'] != "all") {
        $year_filter = $_POST['year_filter'];
        $short_year = substr($year_filter, -2); // Get last 2 digits of year
        
        // This complex condition handles different formats:
        // 1. MMYYYYNNNNN - Extract position 3-6 for year
        // 2. MMYY-NNNNN - Extract position 3-4 for YY and compare with short year
        // 3. Handle various permutations
        $where_clause .= " AND (
            (SUBSTRING(s.serial_number, 3, 4) = '" . $conn->real_escape_string($year_filter) . "')
            OR
            (SUBSTRING(s.serial_number, 3, 2) = '" . $conn->real_escape_string($short_year) . "')
            OR
            (SUBSTRING(SUBSTRING_INDEX(s.serial_number, '-', 1), 3, 4) = '" . $conn->real_escape_string($year_filter) . "')
            OR
            (SUBSTRING(SUBSTRING_INDEX(s.serial_number, '-', 1), 3, 2) = '" . $conn->real_escape_string($short_year) . "')
        )";
    }
    
} elseif (isset($_POST['reset'])) {
    $serial_filter = "";
    $serial_select = "";
    $date_filter = "";
    $tyre_code_filter = "";
    $tyre_code_select = "";
    $description_filter = "";
    $description_select = "";
    $brand_filter = "";
    $brand_select = "";
    $month_filter = "all";
    $year_filter = "all";
    $where_clause = "";
}

// Get distinct serial numbers for dropdown (limit to most recent 1000 for better performance)
$serial_sql = "SELECT DISTINCT serial_number FROM stock_erp ORDER BY id DESC LIMIT 1000";
$serial_result = $conn->query($serial_sql);

// Get distinct tyre codes for dropdown
$tyre_code_sql = "SELECT DISTINCT tyre_code FROM stock_erp ORDER BY tyre_code ASC";
$tyre_code_result = $conn->query($tyre_code_sql);

// Get distinct descriptions for dropdown
$description_sql = "SELECT DISTINCT description FROM stock_erp ORDER BY description ASC";
$description_result = $conn->query($description_sql);

// Get distinct brands from tire_details for dropdown
$brand_sql = "SELECT DISTINCT brand FROM tire_details ORDER BY brand ASC";
$brand_result = $conn->query($brand_sql);

// Get distinct years and months from serial numbers - for new filters
$years = [];
$current_year = intval(date('Y'));
// Generate years from 5 years back to current year
for ($i = $current_year - 5; $i <= $current_year; $i++) {
    $years[] = $i;
}

// Define month names for display
$month_names = array(
    '01' => 'January', '02' => 'February', '03' => 'March',
    '04' => 'April', '05' => 'May', '06' => 'June',
    '07' => 'July', '08' => 'August', '09' => 'September',
    '10' => 'October', '11' => 'November', '12' => 'December'
);

// Count total items for pagination and display
// Join with tire_details table to include brand information
$count_sql = "SELECT COUNT(*) as total FROM stock_erp s 
               LEFT JOIN tire_details td ON s.tyre_code = td.icode
               WHERE 1=1" . $where_clause;
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_items = $count_row['total'];

// Pagination
$items_per_page = 1000;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$total_pages = ceil($total_items / $items_per_page);

// Get data with pagination - now includes brand information from tire_details
$sql = "SELECT s.*, td.brand FROM stock_erp s 
         LEFT JOIN tire_details td ON s.tyre_code = td.icode
         WHERE 1=1" . $where_clause . " 
         ORDER BY s.id DESC LIMIT " . $offset . ", " . $items_per_page;
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock - Qty</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Open Sans', sans-serif;
        }
        
        .container-fluid {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #F28018;
        }
        
        .card {
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 18px;
            border-bottom: 2px solid #F28018;
        }
        
        .form-label {
            font-weight: 600;
            color: #343a40;
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #CCCCCC;
            transition: all 0.3s;
            font-family: 'Cantarell', sans-serif;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #F28018;
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
        }
        
        .btn {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 40px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #000000;
            border-color: #000000;
            color: #FFFFFF;
        }
        
        .btn-primary:hover {
            background-color: #333333;
            border-color: #333333;
        }
        
        .btn-warning {
            background-color: #343a40;
            border-color: #343a40;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #23272b;
            border-color: #23272b;
            color: white;
        }
        
        .filter-section {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #CCCCCC;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .table thead {
            background-color: #F28018;
            color: #000000;
        }
        
        .table thead th {
            font-weight: bold;
            padding: 15px;
            border-bottom: none;
            font-family: 'Cantarell', sans-serif;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: rgba(240, 240, 240, 0.5);
        }
        
        .table tbody tr:hover {
            background-color: rgba(242, 128, 24, 0.1);
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border: 1px solid #000000;
            font-family: 'Open Sans', sans-serif;
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 15px;
            font-weight: 600;
        }
        
        .pagination .page-link {
            color: #000000;
            border-color: #F28018;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #F28018;
            border-color: #F28018;
            color: #000000;
        }
        
        .pagination .page-link:hover {
            background-color: #343a40;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border-left: 5px solid;
        }
        
        .alert-success {
            border-left-color: #28a745;
        }
        
        .alert-danger {
            border-left-color: #dc3545;
        }
        
        .filter-group {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        
        .results-count {
            background-color: #343a40;
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #F28018;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .count-badge {
            background-color: #F28018;
            color: #000000;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .serial-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 10px 15px;
            margin: 10px 0;
            border-left: 4px solid #F28018;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
            
            .card-header {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header">
            <h1 class="mb-0">Stock With Serial Number</h1>
            <p class="mb-0">Manage and view your stock inventory</p>
        </div>
        
        <div id="alertArea">
            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success'] ?? ''); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error'] ?? ''); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Stock Inventory</h5>
                        <div>
                            <button id="exportBtn" class="btn btn-success">
                                <i class="fas fa-file-excel mr-2"></i>Export to Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="filter-section">
                            <h5 class="mb-3">Filter Options</h5>
                            
                            <!-- Serial Number Format Information -->
                            <div class="serial-info">
                                <h6><i class="fas fa-info-circle mr-2"></i>Serial Number Format:</h6>
                                <p class="mb-0">Format: <strong>MMYYYYNNNNN</strong> or <strong>MMYY-NNNNN</strong> where:</p>
                                <ul class="mb-0">
                                    <li><strong>MM</strong> = Month (e.g., 03)</li>
                                    <li><strong>YYYY</strong> = Year (e.g., 2025 or just YY = 25)</li>
                                    <li><strong>NNNNN</strong> = Tire Number (e.g., 07753)</li>
                                </ul>
                                <p class="mb-0">Example: <strong>032025-07753</strong> is March 2025, tire #07753</p>
                            </div>
                            
                            <form method="post" action="" id="filterForm">
                                <div class="row">
                                    <!-- Month Filter (New) -->
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Month (From Serial)</label>
                                            <select class="form-control" id="month_filter" name="month_filter">
                                                <option value="all" <?php echo ($month_filter == "all") ? 'selected' : ''; ?>>All Months</option>
                                                <?php foreach ($month_names as $num => $name): ?>
                                                <option value="<?php echo $num; ?>" <?php echo ($month_filter == $num) ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?> (<?php echo $num; ?>)
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Year (From Serial)</label>
                                            <select class="form-control" id="year_filter" name="year_filter">
                                                <option value="all" <?php echo ($year_filter == "all") ? 'selected' : ''; ?>>All Years</option>
                                                <?php foreach ($years as $year): ?>
                                                <option value="<?php echo $year; ?>" <?php echo ($year_filter == $year) ? 'selected' : ''; ?>>
                                                    <?php echo $year; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                  
                                    
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Serial Number</label>
                                            <div class="mb-2">
                                                <label class="form-label">Select from list:</label>
                                                <select class="form-control" id="serial_select" name="serial_select">
                                                    <option value="all">All Serial Numbers</option>
                                                    <?php if ($serial_result->num_rows > 0): ?>
                                                        <?php while($serial_row = $serial_result->fetch_assoc()): ?>
                                                        <option value="<?php echo htmlspecialchars($serial_row['serial_number'] ?? ''); ?>" 
                                                                <?php echo ($serial_select == ($serial_row['serial_number'] ?? '')) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($serial_row['serial_number'] ?? ''); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Or search by full serial:</label>
                                                <input type="text" class="form-control" id="serial_filter" name="serial_filter" 
                                                       placeholder="Enter serial number..." 
                                                       value="<?php echo htmlspecialchars($serial_filter ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Tyre Code</label>
                                            <div class="mb-2">
                                                <label class="form-label">Select from list:</label>
                                                <select class="form-control" id="tyre_code_select" name="tyre_code_select">
                                                    <option value="all">All Tyre Codes</option>
                                                    <?php if ($tyre_code_result->num_rows > 0): ?>
                                                        <?php while($tyre_code_row = $tyre_code_result->fetch_assoc()): ?>
                                                        <option value="<?php echo htmlspecialchars($tyre_code_row['tyre_code'] ?? ''); ?>" 
                                                                <?php echo ($tyre_code_select == ($tyre_code_row['tyre_code'] ?? '')) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($tyre_code_row['tyre_code'] ?? ''); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Or search:</label>
                                                <input type="text" class="form-control" id="tyre_code_filter" name="tyre_code_filter" 
                                                       placeholder="Enter tyre code..." 
                                                       value="<?php echo htmlspecialchars($tyre_code_filter ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Brand Filter Section -->
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Tire Brand</label>
                                            <div class="mb-2">
                                                <label class="form-label">Select from list:</label>
                                                <select class="form-control" id="brand_select" name="brand_select">
                                                    <option value="all">All Brands</option>
                                                    <?php if ($brand_result->num_rows > 0): ?>
                                                        <?php while($brand_row = $brand_result->fetch_assoc()): ?>
                                                        <option value="<?php echo htmlspecialchars($brand_row['brand'] ?? ''); ?>" 
                                                                <?php echo ($brand_select == ($brand_row['brand'] ?? '')) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($brand_row['brand'] ?? ''); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Or search:</label>
                                                <input type="text" class="form-control" id="brand_filter" name="brand_filter" 
                                                       placeholder="Enter brand name..." 
                                                       value="<?php echo htmlspecialchars($brand_filter ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Description</label>
                                            <div class="mb-2">
                                                <label class="form-label">Select from list:</label>
                                                <select class="form-control" id="description_select" name="description_select">
                                                    <option value="all">All Descriptions</option>
                                                    <?php if ($description_result->num_rows > 0): ?>
                                                        <?php while($description_row = $description_result->fetch_assoc()): ?>
                                                        <option value="<?php echo htmlspecialchars($description_row['description'] ?? ''); ?>" 
                                                                <?php echo ($description_select == ($description_row['description'] ?? '')) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($description_row['description'] ?? ''); ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label">Or search:</label>
                                                <input type="text" class="form-control" id="description_filter" name="description_filter" 
                                                       placeholder="Search by description" 
                                                       value="<?php echo htmlspecialchars($description_filter ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Date</label>
                                            <div>
                                                <input type="date" class="form-control" id="date_filter" name="date_filter" 
                                                       value="<?php echo htmlspecialchars($date_filter ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" name="filter" class="btn btn-primary">
                                            <i class="fas fa-filter mr-2"></i>Apply Filters
                                        </button>
                                        <button type="submit" name="reset" class="btn btn-secondary ml-2">
                                            <i class="fas fa-undo mr-2"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        

                        

                        <!-- Results Count Display -->
                        <div class="results-count">
                            <span>Filtered Results</span>
                            <span class="count-badge">
                                <i class="fas fa-tags mr-2"></i> <?php echo number_format($total_items); ?> Items
                            </span>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped mt-4" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>Tire Number</th>
                                        <th>Tyre Code</th>
                                        <th>Brand</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): 
                                            // Parse the serial number
                                            $serial = $row['serial_number'] ?? '';
                                            
                                            // Initialize variables
                                            $month = '';
                                            $year = '';
                                            $tire_number = '';
                                            
                                            // Check if serial number contains a hyphen to determine format
                                            if (strpos($serial, '-') !== false) {
                                                // Format like "032025-07753"
                                                $parts = explode('-', $serial);
                                                $first_part = $parts[0];
                                                $tire_number = $parts[1] ?? '';
                                                
                                                // Extract month and year
                                                $month = substr($first_part, 0, 2);
                                                
                                                // Check length of first part to determine if it has 2 or 4 digit year
                                                if (strlen($first_part) >= 6) {
                                                    $year = substr($first_part, 2, 4); // MMYYYY format
                                                } else {
                                                    $year = "20" . substr($first_part, 2, 2); // MMYY format, assuming 21st century
                                                }
                                            } else {
                                                // Format without hyphen
                                                $month = substr($serial, 0, 2);
                                                
                                                // Try to determine format based on length
                                                if (strlen($serial) >= 8) { // Assuming it could be MMYYYYNNNNN
                                                    $year_part = substr($serial, 2, 4);
                                                    // Check if year part is likely a 4-digit year (starts with 19 or 20)
                                                    if (substr($year_part, 0, 2) == '19' || substr($year_part, 0, 2) == '20') {
                                                        $year = $year_part;
                                                        $tire_number = substr($serial, 6);
                                                    } else {
                                                        // Assume it's MMYYNNNNN
                                                        $year = "20" . substr($serial, 2, 2);
                                                        $tire_number = substr($serial, 4);
                                                    }
                                                } else { // Must be MMYYNNNNN
                                                    $year = "20" . substr($serial, 2, 2);
                                                    $tire_number = substr($serial, 4);
                                                }
                                            }
                                            
                                            // Get month name
                                            $month_name = isset($month_names[$month]) ? $month_names[$month] : $month;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($serial); ?></td>
                                            <td><?php echo htmlspecialchars($month_name); ?> (<?php echo htmlspecialchars($month); ?>)</td>
                                            <td><?php echo htmlspecialchars($year); ?></td>
                                            <td><?php echo htmlspecialchars($tire_number); ?></td>
                                            <td><?php echo htmlspecialchars($row['tyre_code'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['brand'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['date'] ?? ''); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        
                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);
            
            // Make each row clickable for details view
            $('table tbody tr').click(function() {
                var serialNumber = $(this).find('td:first').text();
                // You can redirect to a details page or show a modal
                // window.location.href = 'view_details.php?serial=' + serialNumber;
            });
            
            // Clear the other input when one is used
            $('#serial_select').change(function() {
                if($(this).val() !== 'all') {
                    $('#serial_filter').val('');
                }
            });
            
            $('#serial_filter').keyup(function() {
                if($(this).val() !== '') {
                    $('#serial_select').val('all');
                }
            });
            
            $('#tyre_code_select').change(function() {
                if($(this).val() !== 'all') {
                    $('#tyre_code_filter').val('');
                }
            });
            
            $('#tyre_code_filter').keyup(function() {
                if($(this).val() !== '') {
                    $('#tyre_code_select').val('all');
                }
            });
            
            $('#description_select').change(function() {
                if($(this).val() !== 'all') {
                    $('#description_filter').val('');
                }
            });
            
            $('#description_filter').keyup(function() {
                if($(this).val() !== '') {
                    $('#description_select').val('all');
                }
            });
            
            $('#brand_select').change(function() {
                if($(this).val() !== 'all') {
                    $('#brand_filter').val('');
                }
            });
            
            $('#brand_filter').keyup(function() {
                if($(this).val() !== '') {
                    $('#brand_select').val('all');
                }
            });
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>









