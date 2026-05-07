<div class="button-container">
        <button>
            <a href="dashboard.php" style="text-decoration: none; color: #da0a0aff;">Click To dashboard</a>
        </button>
    </div>


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

// Query to check if any feature is enabled
$sql = "SELECT COUNT(*) as enabled_count 
        FROM system_features 
        WHERE is_enabled = 1";

$result = $conn->query($sql);
$row = $result->fetch_assoc();
$showMaintenanceMessage = ($row['enabled_count'] > 0);

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .maintenance-notice {
            background-color: #fffbea;
            border: 1px solid #ffe58f;
            padding: 20px;
            border-radius: 4px;
        }
        .maintenance-notice p {
            margin: 0;
            color: #8a6d3b;
            font-size: 16px;
            line-height: 1.5;
        }
        h1 {
            color: #333;
            margin-top: 0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>System Status</h1>
        
        <?php if ($showMaintenanceMessage): ?>
        <div class="maintenance-notice">
            <p><strong>Notice:</strong> The Planning Department is currently updating the Work Order Plan. Please check back soon for the latest information. Thank you for your patience.</p>
        </div>
        <?php else: ?>
        <div class="maintenance-notice" style="background-color: #e8f4fd; border-color: #b3dcfd;">
            <p style="color: #0c5460;">All systems are currently operating normally.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>


<?php
// Database connection
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if data exists in the process table
$sql = "SELECT COUNT(*) as count FROM process";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    // If data exists, display the message with improved styling
    if ($count > 0) {
        echo '
        <div style="max-width: 600px; margin: 20px auto; background-color: #f8f9fa; border-left: 5px solid #F28018; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center;">
                <div style="margin-right: 15px;">
                    <i class="fas fa-sync fa-spin" style="font-size: 24px; color:rgb(0, 13, 15);"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: #F28018; font-weight: 600;">System Notice</h4>
                    <p style="margin: 10px 0 0; font-size: 16px;">The Planning Department is currently updating the Work Order Plan. Please check back soon for the latest information. Thank you for your patience</p>
                </div>
            </div>
        </div>';
    }
}

// Close connection
$conn->close();
?>

<!-- Include Font Awesome for the spinning icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">








<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to delete all data from stockr table
$sql = "DELETE FROM stockr";

// Execute the query
if ($conn->query($sql) === TRUE) {
   // echo "All data successfully deleted from the stockr table.";
} else {
    //echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();
?>






<?php
// Database connection parameters for both stock and stockr (assuming both are in the same database)
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection to the stock database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to copy data from stock to stockr table
$sql = "
INSERT INTO stockr (id, icode, t_size, brand, col, rim, gweight, cstock)
SELECT id, icode, t_size, brand, col, rim, gweight, cstock
FROM stock;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
   // echo "Data successfully copied from stock to stockr.";
} else {
    //cho "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>





<?php
// Database connection details
$servername = "localhost"; // Database host and port
$username = "planatir_task_managemen"; // Database username
$password = "Bishan@1919"; // Database password
$dbname = "planatir_task_managemen"; // Database name

// Create a new PDO instance
try {
    // Establish the database connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update cstock in stockr to match stockhand in tobeplan1 where erp = 1
    $updateQuery = "
        UPDATE stockr s
        JOIN tobeplan1 w ON s.icode = w.icode
        SET s.cstock = w.stockonhand
        WHERE w.erp = 1
    ";
    
    // Prepare and execute the update query
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute();

    // Display the updated cstock values
    $selectQuery = "
        SELECT s.icode, s.cstock
        FROM stockr s
        JOIN tobeplan1 w ON s.icode = w.icode
        WHERE w.erp = 1
    ";
    
    // Prepare and execute the select query
    $stmt = $pdo->prepare($selectQuery);
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the results
    foreach ($results as $row) {
        //echo "Icode: " . $row['icode'] . " - Updated cstock: " . $row['cstock'] . "<br>";
    }

} catch (PDOException $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage();
}
?>






















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            color: #333;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(242, 128, 24, 0.3);
        }

        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo i {
            font-size: 32px;
            color: white;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .notification-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .notification-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .highlight-message {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: #FF0000;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            animation: blink 2s infinite;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #343a40;
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #F28018;
            padding: 20px;
            border-radius: 13px 13px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        .card-body p {
            font-size: 18px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body strong {
            color: #343a40;
        }

        .stat-value {
            color: #F28018;
            font-weight: bold;
            font-size: 20px;
        }

        .controls-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-form input[type="text"] {
            flex: 1;
            min-width: 300px;
            padding: 12px 20px;
            border: 1px solid #CCCCCC;
            border-radius: 25px;
            font-family: 'Cantarell', sans-serif;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-form input[type="text"]:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .select-container {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .select-wrapper {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .select-wrapper label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        select {
            padding: 10px 15px;
            border: 1px solid #CCCCCC;
            border-radius: 20px;
            font-family: 'Cantarell', sans-serif;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        select:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .button-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .download-btn {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: #FFFFFF;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .download-btn:hover {
            background: linear-gradient(135deg, #333333 0%, #555555 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .stockr-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .stockr-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 15px 10px;
            text-align: left;
            border-bottom: 2px solid #e67e22;
        }

        .stockr-table td {
            border: 1px solid #e0e0e0;
            padding: 12px 10px;
            text-align: left;
            font-family: 'Open Sans', sans-serif;
            transition: all 0.3s ease;
        }

        .stockr-row:hover {
            background-color: rgba(242, 128, 24, 0.1);
            transform: scale(1.01);
        }

        .stockr-row:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-in-stock {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .status-low-stock {
            background: rgba(241, 196, 15, 0.1);
            color: #f39c12;
        }

        .status-out-of-stock {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(242, 128, 24, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .floating-action:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 30px rgba(242, 128, 24, 0.6);
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 1200px) {
            .stockr-table {
                font-size: 11px;
            }
            .stockr-table td, .stockr-table th {
                padding: 8px 6px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form input[type="text"] {
                min-width: auto;
            }

            .select-container {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stockr-table {
                font-size: 10px;
            }

            .stockr-table th,
            .stockr-table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <?php
    // Database connection parameters
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create a database connection
    $con = mysqli_connect($servername, $username, $password, $dbname);

    // Check the connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Function to calculate the total stock On Hand
    function calculateTotalStockOnHand()
    {
        global $con;
        $query = "SELECT SUM(cstock) AS total_stock FROM realstock";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['total_stock'] ?? 0;
        }

        return 0;
    }

    // Function to calculate the total Free Stock
    function calculateTotalFreeStock()
    {
        global $con;
        $query = "SELECT SUM(cstock) AS total_stockk FROM stockr";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['total_stockk'] ?? 0;
        }

        return 0;
    }

    // Function to calculate the total Stock Requirement
    function calculateTotalStockRequirement()
    {
        $totalStockOnHand = calculateTotalStockOnHand();
        $totalFreeStock = calculateTotalFreeStock();
        return max(0, $totalStockOnHand - $totalFreeStock);
    }

    // Function to calculate the total Order Requirement
    function calculateTotalRequirement()
    {
        global $con;
        $query = "SELECT SUM(new) AS total_requirement FROM worder WHERE erp != 1";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['total_requirement'] ?? 0;
        }

        return 0;
    }

    // Function to calculate the total Requirement Weight
    function calculateTotalRequirementWeight()
    {
        global $con;
        $query = "SELECT SUM(w.new * td.stgreenweight) AS total_requirement_weight
                  FROM worder w
                  JOIN tire_details td ON w.icode = td.icode
                  WHERE w.erp != 1";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['total_requirement_weight'] ?? 0;
        }

        return 0;
    }

    // Function to calculate the total Stock Requirement Weight
    function calculateTotalStockRequirementWeight($totalStockRequirement)
    {
        global $con;
        $query = "SELECT SUM(rs.cstock * td.stgreenweight) AS total_requirement_weight
                  FROM realstock rs
                  JOIN tire_details td ON rs.icode = td.icode";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $totalRealStockWeight = $row['total_requirement_weight'] ?? 0;
            $totalFreeStockWeight = calculateTotalFreeStockWeight();
            return max(0, $totalRealStockWeight - $totalFreeStockWeight);
        }

        return 0;
    }

    // Function to calculate the total Free Stock Weight
    function calculateTotalFreeStockWeight()
    {
        global $con;
        $query = "SELECT SUM(rs.cstock * td.stgreenweight) AS total_free_stock_weight
                  FROM stockr rs
                  JOIN tire_details td ON rs.icode = td.icode";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['total_free_stock_weight'] ?? 0;
        }

        return 0;
    }

    // Database connection parameters for PDO
    try {
        // Create a new PDO instance
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query 1: Real stock
        $sql1 = "SELECT
                    rs.id,   
                    rs.icode,
                    rs.t_size,
                    rs.brand,
                    rs.col,
                    rs.rim,
                    rs.gweight,
                    rs.cstock,
                    td.stgreenweight AS tire_gweight,
                    (td.stgreenweight * rs.cstock) AS calculated_column
                FROM
                    realstock rs
                JOIN
                    tire_details td ON rs.icode = td.icode";

        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute();
        $results1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        $totalCalculatedColumn1 = 0;
        foreach ($results1 as $row) {
            $totalCalculatedColumn1 += $row['calculated_column'];
        }

        // Query 2: Free stock
        $sql2 = "SELECT
                    rs.id,
                    rs.icode,
                    rs.t_size,
                    rs.brand,
                    rs.col,
                    rs.rim,
                    rs.gweight,
                    rs.cstock,
                    td.stgreenweight AS tire_gweight,
                    (td.stgreenweight * rs.cstock) AS calculated_column
                FROM
                    stockr rs
                JOIN
                    tire_details td ON rs.icode = td.icode";

        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $totalCalculatedColumn2 = 0;
        foreach ($results2 as $row) {
            $totalCalculatedColumn2 += $row['calculated_column'];
        }

        // Query 3: Work Order
        $sql3 = "SELECT
                    rs.id,
                    rs.icode,
                    rs.new,
                    td.stgreenweight AS tire_gweight,
                    (td.stgreenweight * rs.new) AS calculated_column
                FROM
                    worder rs
                JOIN
                    tire_details td ON rs.icode = td.icode";

        $stmt3 = $conn->prepare($sql3);
        $stmt3->execute();
        $results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        $totalCalculatedColumn3 = 0;
        foreach ($results3 as $row) {
            $totalCalculatedColumn3 += $row['calculated_column'];
        }

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    // Close the database connection
    $conn = null;
    ?>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-warehouse"></i>
                <h1>Stock Management Dashboard</h1>
            </div>
            <div class="header-actions">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                </button>
                <button class="notification-btn">
                    <i class="fas fa-user-circle"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Message -->
        <div class="highlight-message animate-on-scroll">
            <i class="fas fa-exclamation-triangle"></i>
            This is set so that the data of the stock order is not included.
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="card animate-on-scroll">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i> Stock - Qty
                </div>
                <div class="card-body">
                    <p><strong>Total Stock On Hand:</strong> <span class="stat-value"><?= number_format(calculateTotalStockOnHand()); ?></span></p>
                    
                    <p><strong>Total Free Stock:</strong> <span class="stat-value"><?= number_format(calculateTotalFreeStock()); ?></span></p>
                    <p><strong>Total Stock Requirement:</strong> <span class="stat-value"><?= number_format(calculateTotalStockRequirement()); ?></span></p>
                    <p><strong>Total Order Requirement:</strong> <span class="stat-value"><?= number_format(calculateTotalRequirement()); ?></span></p>
                </div>
            </div>

            
            <div class="card animate-on-scroll">
                <div class="card-header">
                    <i class="fas fa-weight-hanging"></i> Weight - Kgs
                </div>
                <div class="card-body">
                    <p><strong>FG Stock Green Tire Weight:</strong> <span class="stat-value"><?= number_format(floor($totalCalculatedColumn1)); ?></span></p>
                    
                    <p><strong>Total Free Stock Weight:</strong> <span class="stat-value"><?= number_format(floor($totalCalculatedColumn2)); ?></span></p>
                    <p><strong>Total Stock Requirement Weight:</strong> <span class="stat-value"><?= number_format(floor(calculateTotalStockRequirementWeight(calculateTotalStockRequirement()))); ?></span></p>
                    <p><strong>Total Requirement Weight:</strong> <span class="stat-value"><?= number_format(floor(calculateTotalRequirementWeight())); ?></span></p>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Stock - Qty (Filtered)
                </div>
                <div id="totalsDisplay" class="card-body">
                    <p id="totalStockOnHand">Total Stock On Hand: <span class="stat-value">0</span></p>
                    <p id="totalFreeStock">Total Free Stock: <span class="stat-value">0</span></p>
                    <p id="totalStockRequirement">Total Stock Requirement: <span class="stat-value">0</span></p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="controls-section animate-on-scroll">
            <div class="search-form">
                <input type="text" 
                       id="icodeInput" 
                       placeholder="🔍 Enter Item Code, Description, Brand, Colour, or Rim" 
                       oninput="searchStock()">
                
                <div class="select-container">
                    <div class="select-wrapper">
                        <label for="brandSelect">Select Brand:</label>
                        <select id="brandSelect" onchange="filterByBrandAndColorAndRim()">
                            <option value="">All Brands</option>
                            <?php
                            $brand_query = "SELECT DISTINCT brand FROM realstock";
                            $brand_query_run = mysqli_query($con, $brand_query);

                            if ($brand_query_run) {
                                while ($brand = mysqli_fetch_assoc($brand_query_run)) {
                                    echo '<option value="' . $brand['brand'] . '">' . $brand['brand'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="select-wrapper">
                        <label for="colorSelect">Select Color:</label>
                        <select id="colorSelect" onchange="filterByBrandAndColorAndRim()">
                            <option value="">All Colors</option>
                            <?php
                            $color_query = "SELECT DISTINCT col FROM realstock";
                            $color_query_run = mysqli_query($con, $color_query);

                            if ($color_query_run) {
                                while ($color = mysqli_fetch_assoc($color_query_run)) {
                                    echo '<option value="' . $color['col'] . '">' . $color['col'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="select-wrapper">
                        <label for="rimSelect">Select Type:</label>
                        <select id="rimSelect" onchange="filterByBrandAndColorAndRim()">
                            <option value="">All TYPE</option>
                            <?php
                            $rim_query = "SELECT DISTINCT rim FROM realstock";
                            $rim_query_run = mysqli_query($con, $rim_query);

                            if ($rim_query_run) {
                                while ($rim = mysqli_fetch_assoc($rim_query_run)) {
                                    echo '<option value="' . $rim['rim'] . '">' . $rim['rim'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="button-container">
                <form action="download_excel.php" method="post" style="display: inline;">
                    <button type="submit" class="download-btn">
                        <i class="fas fa-download"></i>
                        Download Excel
                    </button>
                </form>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container animate-on-scroll">
            <div class="table-wrapper">
                <table id="stockr-table" class="stockr-table">
                    <thead>
                        <tr class="header">
                            <th><i class="fas fa-barcode"></i> Item Code</th>
                            <th><i class="fas fa-info-circle"></i> Description</th>
                            <th><i class="fas fa-tag"></i> Brand</th>
                            <th><i class="fas fa-palette"></i> Colour</th>
                            <th><i class="fas fa-circle"></i> Type</th>
                            <th><i class="fas fa-warehouse"></i> Stock On Hand</th>
                            <th><i class="fas fa-clipboard-check"></i> Order Requirement</th>
                            <th><i class="fas fa-check-circle"></i> Free Stock</th>
                            <th><i class="fas fa-weight"></i> Free Stock Compound Weight</th>
                            <th><i class="fas fa-weight-hanging"></i> Free Stock St Compound Weight</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        function getRequirementSum($icode)
                        {
                            global $con;
                            $query = "SELECT SUM(new) AS requirement_sum FROM worder WHERE icode = ? AND erp != 1";
                            $stmt = mysqli_prepare($con, $query);
                            mysqli_stmt_bind_param($stmt, "s", $icode);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);

                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                return $row['requirement_sum'] ?? 0;
                            }

                            return 0;
                        }

                        function getActualStock($icode)
                        {
                            global $con;
                            $query = "SELECT cstock FROM stockr WHERE icode = '$icode'";
                            $result = mysqli_query($con, $query);

                            if ($result && mysqli_num_rows($result)) {
                                $row = mysqli_fetch_assoc($result);
                                return $row['cstock'];
                            }

                            return 0;
                        }

                        function getTireDetails($icode)
                        {
                            global $con;
                            $query = "SELECT greenweight, stgreenweight FROM tire_details WHERE icode = '$icode'";
                            $result = mysqli_query($con, $query);

                            if ($result && mysqli_num_rows($result)) {
                                $row = mysqli_fetch_assoc($result);
                                return array(
                                    'greenweight' => is_numeric($row['greenweight']) ? $row['greenweight'] : 0,
                                    'stgreenweight' => is_numeric($row['stgreenweight']) ? $row['stgreenweight'] : 0
                                );
                            }

                            return array('greenweight' => 0, 'stgreenweight' => 0);
                        }

                        function calculateWeightedValues($freeStock, $greenWeight, $stGreenWeight)
                        {
                            $freeStockNum = is_numeric($freeStock) ? $freeStock : 0;
                            $greenWeightNum = is_numeric($greenWeight) ? $greenWeight : 0;
                            $stGreenWeightNum = is_numeric($stGreenWeight) ? $stGreenWeight : 0;
                            
                            return array(
                                'freestock_x_greenweight' => $freeStockNum * $greenWeightNum,
                                'freestock_x_stgreenweight' => $freeStockNum * $stGreenWeightNum
                            );
                        }

                        function getStockStatus($freeStock, $requirement) {
                            $freeStockNum = is_numeric($freeStock) ? $freeStock : 0;
                            $requirementNum = is_numeric($requirement) ? $requirement : 0;
                            
                            if ($freeStockNum <= 0) {
                                return 'status-out-of-stock';
                            } elseif ($freeStockNum < $requirementNum || $freeStockNum < 50) {
                                return 'status-low-stock';
                            } else {
                                return 'status-in-stock';
                            }
                        }

                        $query = "SELECT * FROM realstock";
                        $query_run = mysqli_query($con, $query);

                        if (!$query_run) {
                            echo "Error in stock query: " . mysqli_error($con);
                        } else {
                            while ($items = mysqli_fetch_assoc($query_run)) {
                                $tireDetails = getTireDetails($items['icode']);
                                $freeStock = getActualStock($items['icode']);
                                $requirement = getRequirementSum($items['icode']);
                                $weightedValues = calculateWeightedValues($freeStock, $tireDetails['greenweight'], $tireDetails['stgreenweight']);
                                $statusClass = getStockStatus($freeStock, $requirement);
                        ?>
                                <tr class="stockr-row">
                                    <td><?= $items['icode']; ?></td>
                                    <td><?= $items['t_size']; ?></td>
                                    <td><?= $items['brand']; ?></td>
                                    <td><?= $items['col']; ?></td>
                                    <td><?= $items['rim']; ?></td>
                                    <td><?= number_format($items['cstock']); ?></td>
                                    <td><?= number_format($requirement); ?></td>
                                    <td><span class="status-badge <?= $statusClass; ?>"><?= number_format($freeStock); ?></span></td>
                                    <td><?= number_format($weightedValues['freestock_x_greenweight'], 2); ?></td>
                                    <td><?= number_format($weightedValues['freestock_x_stgreenweight'], 2); ?></td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="floating-action" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        function searchStock() {
            var input = document.getElementById('icodeInput').value.toLowerCase();
            var table = document.getElementById('stockr-table');
            var rows = table.getElementsByClassName('stockr-row');

            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var icodeCell = cells[0];
                var descCell = cells[1];
                var brandCell = cells[2];
                var colorCell = cells[3];
                var rimCell = cells[4];

                if (icodeCell && descCell && brandCell && colorCell && rimCell) {
                    var icodeValue = icodeCell.textContent || icodeCell.innerText;
                    var descValue = descCell.textContent || descCell.innerText;
                    var brandValue = brandCell.textContent || brandCell.innerText;
                    var colorValue = colorCell.textContent || colorCell.innerText;
                    var rimValue = rimCell.textContent || rimCell.innerText;

                    icodeValue = icodeValue.toLowerCase();
                    descValue = descValue.toLowerCase();
                    brandValue = brandValue.toLowerCase();
                    colorValue = colorValue.toLowerCase();
                    rimValue = rimValue.toLowerCase();

                    if (icodeValue.includes(input) || descValue.includes(input) || brandValue.includes(input) || colorValue.includes(input) || rimValue.includes(input)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
            updateTotalsDisplay();
        }

        function filterByBrandAndColorAndRim() {
            var brandSelect = document.getElementById('brandSelect');
            var colorSelect = document.getElementById('colorSelect');
            var rimSelect = document.getElementById('rimSelect');
            var selectedBrand = brandSelect.value.toLowerCase();
            var selectedColor = colorSelect.value.toLowerCase();
            var selectedRim = rimSelect.value.toLowerCase();
            var table = document.getElementById('stockr-table');
            var rows = table.getElementsByClassName('stockr-row');

            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var brandCell = cells[2];
                var colorCell = cells[3];
                var rimCell = cells[4];

                if (brandCell && colorCell && rimCell) {
                    var brandValue = brandCell.textContent || brandCell.innerText;
                    var colorValue = colorCell.textContent || colorCell.innerText;
                    var rimValue = rimCell.textContent || rimCell.innerText;

                    brandValue = brandValue.toLowerCase();
                    colorValue = colorValue.toLowerCase();
                    rimValue = rimValue.toLowerCase();

                    var brandMatch = selectedBrand === "" || brandValue.includes(selectedBrand);
                    var colorMatch = selectedColor === "" || colorValue.includes(selectedColor);
                    var rimMatch = selectedRim === "" || rimValue.includes(selectedRim);

                    if (brandMatch && colorMatch && rimMatch) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
            updateTotalsDisplay();
        }

        function updateTotalsDisplay() {
            var table = document.getElementById('stockr-table');
            var rows = table.getElementsByClassName('stockr-row');
            var totalStockOnHand = 0;
            var totalFreeStock = 0;

            for (var i = 0; i < rows.length; i++) {
                if (rows[i].style.display !== "none") {
                    var cells = rows[i].getElementsByTagName('td');
                    var stockOnHandCell = cells[5];
                    var freeStockCell = cells[7];

                    if (stockOnHandCell && freeStockCell) {
                        var stockOnHandValue = stockOnHandCell.textContent || stockOnHandCell.innerText;
                        var freeStockValue = freeStockCell.textContent || freeStockCell.innerText;

                        // Remove commas and parse as numbers
                        stockOnHandValue = stockOnHandValue.replace(/,/g, '');
                        freeStockValue = freeStockValue.replace(/,/g, '');

                        var stockOnHandNum = parseInt(stockOnHandValue) || 0;
                        var freeStockNum = parseInt(freeStockValue) || 0;

                        totalStockOnHand += stockOnHandNum;
                        totalFreeStock += freeStockNum;
                    }
                }
            }

            var totalStockRequirement = Math.max(0, totalStockOnHand - totalFreeStock);

            // Update the display
            document.getElementById('totalStockOnHand').innerHTML = 
                'Total Stock On Hand: <span class="stat-value">' + totalStockOnHand.toLocaleString() + '</span>';
            document.getElementById('totalFreeStock').innerHTML = 
                'Total Free Stock: <span class="stat-value">' + totalFreeStock.toLocaleString() + '</span>';
            document.getElementById('totalStockRequirement').innerHTML = 
                'Total Stock Requirement: <span class="stat-value">' + totalStockRequirement.toLocaleString() + '</span>';
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Animation on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            const windowHeight = window.innerHeight;

            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('visible');
                }
            });
        }

        // Initialize animations
        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);

        // Initialize totals display on page load
        window.addEventListener('load', function() {
            updateTotalsDisplay();
        });

        // Add smooth hover effects for table rows
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('.stockr-row');
            
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.zIndex = '10';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.zIndex = '1';
                });
            });
        });

        // Add notification functionality
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                background: ${type === 'error' ? '#e74c3c' : '#27ae60'};
                color: white;
                border-radius: 5px;
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + F to focus search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('icodeInput').focus();
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                document.getElementById('icodeInput').value = '';
                document.getElementById('brandSelect').value = '';
                document.getElementById('colorSelect').value = '';
                document.getElementById('rimSelect').value = '';
                searchStock();
                filterByBrandAndColorAndRim();
            }
        });

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            .notification {
                animation: slideIn 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>