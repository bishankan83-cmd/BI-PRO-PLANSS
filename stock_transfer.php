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
    error_log("Connection failed: " . $conn->connect_error);
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
    if (!empty($_POST['serial_select']) && $_POST['serial_select'] != "all") {
        $serial_select = $_POST['serial_select'];
        $where_clause .= " AND serial_number = '" . $conn->real_escape_string($serial_select) . "'";
    } elseif (!empty($_POST['serial_filter'])) {
        $serial_filter = $_POST['serial_filter'];
        $where_clause .= " AND serial_number LIKE '%" . $conn->real_escape_string($serial_filter) . "%'";
    }
    
    if (!empty($_POST['tyre_code_select']) && $_POST['tyre_code_select'] != "all") {
        $tyre_code_select = $_POST['tyre_code_select'];
        $where_clause .= " AND tyre_code = '" . $conn->real_escape_string($tyre_code_select) . "'";
    } elseif (!empty($_POST['tyre_code_filter'])) {
        $tyre_code_filter = $_POST['tyre_code_filter'];
        $where_clause .= " AND tyre_code LIKE '%" . $conn->real_escape_string($tyre_code_filter) . "%'";
    }
    
    if (!empty($_POST['brand_select']) && $_POST['brand_select'] != "all") {
        $brand_select = $_POST['brand_select'];
        $where_clause .= " AND brand = '" . $conn->real_escape_string($brand_select) . "'";
    } elseif (!empty($_POST['brand_filter'])) {
        $brand_filter = $_POST['brand_filter'];
        $where_clause .= " AND brand LIKE '%" . $conn->real_escape_string($brand_filter) . "%'";
    }
    
    if (!empty($_POST['date_filter'])) {
        $date_filter = $_POST['date_filter'];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_filter)) {
            $where_clause .= " AND date = '" . $conn->real_escape_string($date_filter) . "'";
        }
    }
    
    if (!empty($_POST['description_select']) && $_POST['description_select'] != "all") {
        $description_select = $_POST['description_select'];
        $where_clause .= " AND description = '" . $conn->real_escape_string($description_select) . "'";
    } elseif (!empty($_POST['description_filter'])) {
        $description_filter = $_POST['description_filter'];
        $where_clause .= " AND description LIKE '%" . $conn->real_escape_string($description_filter) . "%'";
    }
    
    if (!empty($_POST['month_filter']) && $_POST['month_filter'] != "all") {
        $month_filter = $_POST['month_filter'];
        if (preg_match('/^\d{2}$/', $month_filter)) {
            $where_clause .= " AND (
                (SUBSTRING(serial_number, 1, 2) = '" . $conn->real_escape_string($month_filter) . "' AND table_name = 'stock_erp')
                OR
                (SUBSTRING(SUBSTRING_INDEX(serial_number, '-', 1), 1, 2) = '" . $conn->real_escape_string($month_filter) . "' AND table_name = 'stock_erp')
                OR
                (month = '" . $conn->real_escape_string($month_filter) . "' AND table_name IN ('over_age', 'non_moveing_tire', 'stocks'))
            )";
        }
    }
    
    if (!empty($_POST['year_filter']) && $_POST['year_filter'] != "all") {
        $year_filter = $_POST['year_filter'];
        if (preg_match('/^\d{4}$/', $year_filter)) {
            $short_year = substr($year_filter, -2);
            $where_clause .= " AND (
                (SUBSTRING(serial_number, 3, 4) = '" . $conn->real_escape_string($year_filter) . "' AND table_name = 'stock_erp')
                OR
                (SUBSTRING(serial_number, 3, 2) = '" . $conn->real_escape_string($short_year) . "' AND table_name = 'stock_erp')
                OR
                (SUBSTRING(SUBSTRING_INDEX(serial_number, '-', 1), 3, 4) = '" . $conn->real_escape_string($year_filter) . "' AND table_name = 'stock_erp')
                OR
                (SUBSTRING(SUBSTRING_INDEX(serial_number, '-', 1), 3, 2) = '" . $conn->real_escape_string($short_year) . "' AND table_name = 'stock_erp')
                OR
                (year IN ('" . $conn->real_escape_string($year_filter) . "', '" . $conn->real_escape_string($short_year) . "') AND table_name IN ('over_age', 'non_moveing_tire', 'stocks'))
            )";
        }
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

// Get distinct serial numbers for dropdown
$serial_sql = "
    SELECT DISTINCT serial_number FROM stock_erp
    UNION
    SELECT DISTINCT serial_number FROM over_age
    UNION
    SELECT DISTINCT serial_number FROM non_moveing_tire
    UNION
    SELECT DISTINCT SerialNumber AS serial_number FROM stocks
    ORDER BY serial_number DESC LIMIT 1000";
$serial_result = $conn->query($serial_sql);
if (!$serial_result) {
    error_log("Serial query failed: " . $conn->error);
    die("Serial query failed: " . $conn->error);
}

// Get distinct tyre codes for dropdown
$tyre_code_sql = "
    SELECT DISTINCT tyre_code FROM stock_erp
    UNION
    SELECT DISTINCT tyre_code FROM over_age
    UNION
    SELECT DISTINCT tyre_code FROM non_moveing_tire
    UNION
    SELECT DISTINCT icode AS tyre_code FROM stocks
    ORDER BY tyre_code ASC";
$tyre_code_result = $conn->query($tyre_code_sql);
if (!$tyre_code_result) {
    error_log("Tyre code query failed: " . $conn->error);
    die("Tyre code query failed: " . $conn->error);
}

// Get distinct descriptions for dropdown
$description_sql = "
    SELECT DISTINCT description FROM stock_erp
    UNION
    SELECT DISTINCT description FROM over_age
    UNION
    SELECT DISTINCT description FROM non_moveing_tire
    UNION
    SELECT DISTINCT Description AS description FROM stocks
    ORDER BY description ASC";
$description_result = $conn->query($description_sql);
if (!$description_result) {
    error_log("Description query failed: " . $conn->error);
    die("Description query failed: " . $conn->error);
}

// Get distinct brands for dropdown
$brand_sql = "
    SELECT DISTINCT brand FROM tire_details
    UNION
    SELECT DISTINCT brand FROM over_age
    UNION
    SELECT DISTINCT brand FROM non_moveing_tire
    UNION
    SELECT DISTINCT Brand AS brand FROM stocks
    ORDER BY brand ASC";
$brand_result = $conn->query($brand_sql);
if (!$brand_result) {
    error_log("Brand query failed: " . $conn->error);
    die("Brand query failed: " . $conn->error);
}

// Get distinct years and months from serial numbers and direct columns
$years = [];
$current_year = intval(date('Y'));
for ($i = $current_year - 5; $i <= $current_year; $i++) {
    $years[] = $i;
}

$month_names = array(
    '01' => 'January', '02' => 'February', '03' => 'March',
    '04' => 'April', '05' => 'May', '06' => 'June',
    '07' => 'July', '08' => 'August', '09' => 'September',
    '10' => 'October', '11' => 'November', '12' => 'December'
);

// Count total items for pagination
$count_sql = "
    SELECT COUNT(*) as total FROM (
        SELECT s.serial_number, s.tyre_code, s.description, s.date, td.brand, NULL AS number_of_tires, NULL AS year, NULL AS month, NULL AS color, NULL AS sq, NULL AS location_number, 'stock_erp' AS table_name 
        FROM stock_erp s 
        LEFT JOIN tire_details td ON s.tyre_code = td.icode
        WHERE 1=1 $where_clause
        UNION
        SELECT serial_number, tyre_code, description, date, brand, number_of_tires, year, month, color, NULL AS sq, NULL AS location_number, 'over_age' AS table_name 
        FROM over_age 
        WHERE 1=1 $where_clause
        UNION
        SELECT serial_number, tyre_code, description, date, brand, number_of_tires, year, month, color, NULL AS sq, NULL AS location_number, 'non_moveing_tire' AS table_name 
        FROM non_moveing_tire 
        WHERE 1=1 $where_clause
        UNION
        SELECT SerialNumber AS serial_number, icode AS tyre_code, Description AS description, NULL AS date, Brand AS brand, NULL AS number_of_tires, Year AS year, Month AS month, Color AS color, SQ AS sq, LocationNumber AS location_number, 'stocks' AS table_name 
        FROM stocks 
        WHERE 1=1 $where_clause
    ) AS combined";
$count_result = $conn->query($count_sql);
if (!$count_result) {
    error_log("Count query failed: " . $conn->error);
    die("Count query failed: " . $conn->error);
}
$count_row = $count_result->fetch_assoc();
$total_items = $count_row['total'];

// Pagination
$items_per_page = 1000;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$total_pages = ceil($total_items / $items_per_page);

// Get combined data with pagination
$sql_combined = "
    SELECT serial_number, tyre_code, description, date, brand, number_of_tires, year, month, color, sq, location_number, table_name FROM (
        SELECT s.serial_number, s.tyre_code, s.description, s.date, td.brand, NULL AS number_of_tires, NULL AS year, NULL AS month, NULL AS color, NULL AS sq, NULL AS location_number, 'stock_erp' AS table_name 
        FROM stock_erp s 
        LEFT JOIN tire_details td ON s.tyre_code = td.icode
        WHERE 1=1 $where_clause
        UNION
        SELECT serial_number, tyre_code, description, date, brand, number_of_tires, year, month, color, NULL AS sq, NULL AS location_number, 'over_age' AS table_name 
        FROM over_age 
        WHERE 1=1 $where_clause
        UNION
        SELECT serial_number, tyre_code, description, date, brand, number_of_tires, year, month, color, NULL AS sq, NULL AS location_number, 'non_moveing_tire' AS table_name 
        FROM non_moveing_tire 
       ਸ
        WHERE 1=1 $where_clause
        UNION
        SELECT SerialNumber AS serial_number, icode AS tyre_code, Description AS description, NULL AS date, Brand AS brand, NULL AS number_of_tires, Year AS year, Month AS month, Color AS color, SQ AS sq, LocationNumber AS location_number, 'stocks' AS table_name 
        FROM stocks 
        WHERE 1=1 $where_clause
    ) AS combined
    ORDER BY serial_number DESC LIMIT $offset, $items_per_page";
$result_combined = $conn->query($sql_combined);
if (!$result_combined) {
    error_log("Combined query failed: " . $conn->error);
    die("Combined query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FULL STOCK REPORT</title>
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
            <h1 class="mb-0">FULL STOCK REPORT</h1>
            <p class="mb-0">Manage and view your stock inventory</p>
        </div>
        
        <div id="alertArea">
            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success'] ?? ''); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error'] ?? ''); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
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
                                    <div class="col-md-3 mb-3">
                                        <div class="filter-group">
                                            <label class="form-label font-weight-bold">Month</label>
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
                                            <label class="form-label font-weight-bold">Year</label>
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
                        
                        <!-- Combined Results Table -->
                        <div class="results-count">
                            <span>Combined Inventory Results</span>
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
                                        <th>Number of Tires</th>
                                        <th>Color</th>
                                        <th>SQ</th>
                                        <th>Location Number</th>
                                        <th>Source Table</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_combined->num_rows > 0): ?>
                                        <?php while($row = $result_combined->fetch_assoc()): 
                                            $serial = $row['serial_number'] ?? '';
                                            $month = $row['month'] ?? '';
                                            $year = $row['year'] ?? '';
                                            $tire_number = '';
                                            
                                            if (strpos($serial, '-') !== false) {
                                                $parts = explode('-', $serial);
                                                $first_part = $parts[0];
                                                $tire_number = $parts[1] ?? '';
                                                if (empty($month) && strlen($first_part) >= 2) {
                                                    $month = substr($first_part, 0, 2);
                                                }
                                                if (empty($year)) {
                                                    $year_part = strlen($first_part) >= 6 ? substr($first_part, 2, 4) : substr($first_part, 2, 2);
                                                    $year = (strlen($year_part) == 2 && $year_part >= '00' && $year_part <= '99') ? '20' . $year_part : $year_part;
                                                }
                                            } else {
                                                if (empty($month) && strlen($serial) >= 2) {
                                                    $month = substr($serial, 0, 2);
                                                }
                                                if (empty($year) && strlen($serial) >= 4) {
                                                    $year_part = substr($serial, 2, (strlen($serial) >= 6 ? 4 : 2));
                                                    $year = (strlen($year_part) == 2 && $year_part >= '00' && $year_part <= '99') ? '20' . $year_part : $year_part;
                                                    $tire_number = substr($serial, strlen($year_part) + 2);
                                                }
                                            }
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
                                            <td><?php echo htmlspecialchars($row['number_of_tires'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['color'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['sq'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['location_number'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['table_name'] ?? ''); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="13" class="text-center">No records found</td>
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
        $(document).ready(function() {
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);
            
            $('table tbody tr').click(function() {
                var serialNumber = $(this).find('td:first').text();
                // window.location.href = 'view_details.php?serial=' + serialNumber;
            });
            
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
$conn->close();
?>