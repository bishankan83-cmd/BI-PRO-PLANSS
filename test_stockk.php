<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Status Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" defer>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(180deg, #f0f0f0 0%, #e0e0e0 100%);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(242, 128, 24, 0.3);
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
            gap: 12px;
        }

        .logo i {
            font-size: 28px;
            color: white;
            transition: transform 0.3s ease;
        }

        .logo i:hover {
            transform: rotate(360deg);
        }

        .logo h1 {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .notification-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .notification-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .container {
            max-width: 1600px;
            margin: 80px auto 20px;
            padding: 20px;
        }

        .highlight-message {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: #FF0000;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            animation: pulse 2s infinite;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 2px solid #F28018;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: #343a40;
            color: white;
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            border-bottom: 2px solid #F28018;
        }

        .card-body {
            padding: 15px;
        }

        .card-body p {
            font-size: 16px;
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-value {
            color: #F28018;
            font-weight: 600;
            font-size: 18px;
        }

        .controls-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
        }

        .search-form input[type="text"] {
            flex: 1;
            min-width: 250px;
            padding: 10px 15px;
            border: 1px solid #CCCCCC;
            border-radius: 20px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-form input[type="text"]:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .select-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .select-wrapper {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .select-wrapper label {
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #CCCCCC;
            border-radius: 15px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
        }

        select:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .button-container {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .download-btn, .dashboard-btn {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .dashboard-btn {
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
        }

        .download-btn:hover, .dashboard-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-height: 600px;
            overflow-y: auto;
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
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: #000000;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #e67e22;
        }

        .stockr-table td {
            border: 1px solid #e0e0e0;
            padding: 10px 8px;
            font-family: 'Open Sans', sans-serif;
        }

        .stockr-row:hover {
            background-color: rgba(242, 128, 24, 0.05);
            transform: scale(1.005);
        }

        .stockr-row:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 10px;
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
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(242, 128, 24, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .floating-action:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 20px rgba(242, 128, 24, 0.5);
        }

        .maintenance-notice {
            background: #fffbea;
            border: 1px solid #ffe58f;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .maintenance-notice p {
            margin: 0;
            color: #8a6d3b;
            font-size: 14px;
        }

        .system-notice {
            max-width: 600px;
            margin: 15px auto;
            background: #f8f9fa;
            border-left: 4px solid #F28018;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .system-notice-content {
            display: flex;
            align-items: center;
        }

        .system-notice-content i {
            margin-right: 12px;
            font-size: 20px;
            color: #000d0f;
        }

        .system-notice h4 {
            margin: 0;
            color: #F28018;
            font-weight: 600;
            font-size: 16px;
        }

        .system-notice p {
            margin: 8px 0 0;
            font-size: 14px;
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(15px);
            transition: all 0.5s ease;
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
                gap: 12px;
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
                gap: 12px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
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

    // Initialize PDO connection
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Cache for repetitive queries
        $cache = [];

        // Check for system features
        $sql_features = "SELECT COUNT(*) as enabled_count FROM system_features WHERE is_enabled = 1";
        $stmt_features = $pdo->query($sql_features);
        $row_features = $stmt_features->fetch(PDO::FETCH_ASSOC);
        $showMaintenanceMessage = ($row_features['enabled_count'] > 0);

        // Check if data exists in the process table
        $sql_process = "SELECT COUNT(*) as count FROM process";
        $stmt_process = $pdo->query($sql_process);
        $row_process = $stmt_process->fetch(PDO::FETCH_ASSOC);
        $process_count = $row_process['count'];

        // Delete and copy data in one transaction
        $pdo->beginTransaction();
        $pdo->exec("DELETE FROM stockr");
        $sql_copy = "
            INSERT INTO stockr (id, icode, t_size, brand, col, rim, gweight, cstock)
            SELECT id, icode, t_size, brand, col, rim, gweight, cstock
            FROM stock";
        $pdo->exec($sql_copy);

        // Update cstock in stockr
        $sql_update = "
            UPDATE stockr s
            JOIN tobeplan1 w ON s.icode = w.icode
            SET s.cstock = w.stockonhand
            WHERE w.erp = 1";
        $pdo->exec($sql_update);
        $pdo->commit();

        // Calculate totals with caching
        function calculateTotalStockOnHand($pdo, &$cache) {
            if (isset($cache['total_stock'])) {
                return $cache['total_stock'];
            }
            $stmt = $pdo->query("SELECT SUM(cstock) AS total_stock FROM realstock");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cache['total_stock'] = $row['total_stock'] ?? "N/A";
            return $cache['total_stock'];
        }

        function calculateTotalFreeStock($pdo, &$cache) {
            if (isset($cache['total_stockk'])) {
                return $cache['total_stockk'];
            }
            $stmt = $pdo->query("SELECT SUM(cstock) AS total_stockk FROM stockr");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $cache['total_stockk'] = $row['total_stockk'] ?? "N/A";
            return $cache['total_stockk'];
        }

        function calculateTotalRequirement($pdo, &$cache) {
            $totalStockOnHand = calculateTotalStockOnHand($pdo, $cache);
            $totalFreeStock = calculateTotalFreeStock($pdo, $cache);
            return ($totalStockOnHand !== "N/A" && $totalFreeStock !== "N/A") 
                ? $totalStockOnHand - $totalFreeStock 
                : "N/A";
        }

        // Fetch stock data
        $sql1 = "
            SELECT rs.id, rs.icode, rs.t_size, rs.brand, rs.col, rs.rim, rs.gweight, rs.cstock,
                   td.stgreenweight AS tire_gweight, (td.stgreenweight * rs.cstock) AS calculated_column
            FROM realstock rs
            JOIN tire_details td ON rs.icode = td.icode";
        $stmt1 = $pdo->query($sql1);
        $results1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $totalCalculatedColumn1 = array_sum(array_column($results1, 'calculated_column'));

        $sql2 = "
            SELECT rs.id, rs.icode, rs.t_size, rs.brand, rs.col, rs.rim, rs.gweight, rs.cstock,
                   td.stgreenweight AS tire_gweight, (td.stgreenweight * rs.cstock) AS calculated_column
            FROM stockr rs
            JOIN tire_details td ON rs.icode = td.icode";
        $stmt2 = $pdo->query($sql2);
        $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $totalCalculatedColumn2 = array_sum(array_column($results2, 'calculated_column'));

        $sql3 = "
            SELECT rs.id, rs.icode, rs.new, td.stgreenweight AS tire_gweight,
                   (td.stgreenweight * rs.new) AS calculated_column
            FROM worder rs
            JOIN tire_details td ON rs.icode = td.icode";
        $stmt3 = $pdo->query($sql3);
        $results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        $totalCalculatedColumn3 = array_sum(array_column($results3, 'calculated_column'));

        $difference = $totalCalculatedColumn1 - $totalCalculatedColumn2;

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit;
    }

    // Helper functions for table data
    function getRequirementSum($pdo, $icode) {
        static $cache = [];
        if (isset($cache[$icode])) {
            return $cache[$icode];
        }
        $stmt = $pdo->prepare("SELECT SUM(new) AS requirement_sum FROM worder WHERE icode = ? AND erp != 1");
        $stmt->execute([$icode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$icode] = $row['requirement_sum'] ?? "0";
        return $cache[$icode];
    }

    function getActualStock($pdo, $icode) {
        static $cache = [];
        if (isset($cache[$icode])) {
            return $cache[$icode];
        }
        $stmt = $pdo->prepare("SELECT cstock FROM stockr WHERE icode = ?");
        $stmt->execute([$icode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$icode] = $row['cstock'] ?? 0;
        return $cache[$icode];
    }

    function getTireDetails($pdo, $icode) {
        static $cache = [];
        if (isset($cache[$icode])) {
            return $cache[$icode];
        }
        $stmt = $pdo->prepare("SELECT greenweight, stgreenweight FROM tire_details WHERE icode = ?");
        $stmt->execute([$icode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$icode] = $row ? [
            'greenweight' => is_numeric($row['greenweight']) ? $row['greenweight'] : 0,
            'stgreenweight' => is_numeric($row['stgreenweight']) ? $row['stgreenweight'] : 0
        ] : ['greenweight' => 0, 'stgreenweight' => 0];
        return $cache[$icode];
    }

    function calculateWeightedValues($freeStock, $greenWeight, $stGreenWeight) {
        $freeStockNum = is_numeric($freeStock) ? $freeStock : 0;
        $greenWeightNum = is_numeric($greenWeight) ? $greenWeight : 0;
        $stGreenWeightNum = is_numeric($stGreenWeight) ? $stGreenWeight : 0;
        return [
            'freestock_x_greenweight' => $freeStockNum * $greenWeightNum,
            'freestock_x_stgreenweight' => $freeStockNum * $stGreenWeightNum
        ];
    }

    function getStockStatus($freeStock, $requirement) {
        $freeStockNum = is_numeric($freeStock) ? $freeStock : 0;
        $requirementNum = is_numeric($requirement) ? $requirement : 0;
        if ($freeStockNum <= 0) {
            return 'status-out-of-stock';
        } elseif ($freeStockNum < $requirementNum || $freeStockNum < 50) {
            return 'status-low-stock';
        }
        return 'status-in-stock';
    }
    ?>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-warehouse"></i>
                <h1>Inventory Status Dashboard</h1>
            </div>
            <div class="header-actions">
                <button class="notification-btn"><i class="fas fa-bell"></i></button>
                <button class="notification-btn"><i class="fas fa-user-circle"></i></button>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- System Status Notices -->
        <?php if ($showMaintenanceMessage): ?>
        <div class="maintenance-notice animate-on-scroll">
            <p><strong>Notice:</strong> The Planning Department is currently updating the Work Order Plan. Please check back soon for the latest information. Thank you for your patience.</p>
        </div>
        <?php else: ?>
        <div class="maintenance-notice animate-on-scroll" style="background-color: #e8f4fd; border-color: #b3dcfd;">
            <p style="color: #0c5460;">All systems are currently operating normally.</p>
        </div>
        <?php endif; ?>

        <?php if ($process_count > 0): ?>
        <div class="system-notice animate-on-scroll">
            <div class="system-notice-content">
                <i class="fas fa-sync fa-spin"></i>
                <div>
                    <h4>System Notice</h4>
                    <p>The Planning Department is currently updating the Work Order Plan. Please check back soon for the latest information. Thank you for your patience.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alert Message -->
        <div class="highlight-message animate-on-scroll">
            <i class="fas fa-exclamation-triangle"></i>
            This is set so that the data of the stock order is not included.
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="card animate-on-scroll">
                <div class="card-header"><i class="fas fa-chart-bar"></i> Stock - Qty</div>
                <div class="card-body">
                    <p><strong>Total Stock On Hand:</strong> <span class="stat-value"><?= number_format(calculateTotalStockOnHand($pdo, $cache)); ?></span></p>
                    <p><strong>Total Requirement:</strong> <span class="stat-value"><?= number_format(calculateTotalRequirement($pdo, $cache)); ?></span></p>
                    <p><strong>Total Free Stock:</strong> <span class="stat-value"><?= number_format(calculateTotalFreeStock($pdo, $cache)); ?></span></p>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header"><i class="fas fa-weight-hanging"></i> Weight - Kgs</div>
                <div class="card-body">
                    <p><strong>FG Stock Green Tire Weight:</strong> <span class="stat-value"><?= number_format(floor($totalCalculatedColumn1)); ?></span></p>
                    <p><strong>Total Requirement Weight:</strong> <span class="stat-value"><?= number_format(floor($difference)); ?></span></p>
                    <p><strong>Total Free Stock Weight:</strong> <span class="stat-value"><?= number_format(floor($totalCalculatedColumn2)); ?></span></p>
                </div>
            </div>

            <div class="card animate-on-scroll">
                <div class="card-header"><i class="fas fa-filter"></i> Stock - Qty (Filtered)</div>
                <div id="totalsDisplay" class="card-body">
                    <p id="totalStockOnHand">Total Stock On Hand: <span class="stat-value">0</span></p>
                    <p id="totalFreeStock">Total Free Stock: <span class="stat-value">0</span></p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="controls-section animate-on-scroll">
            <div class="search-form">
                <input type="text" id="icodeInput" placeholder="🔍 Enter Item Code, Description, Brand, Or Colour" oninput="searchStock()">
                <div class="select-container">
                    <div class="select-wrapper">
                        <label for="brandSelect">Select Brand:</label>
                        <select id="brandSelect" onchange="filterByBrandAndColor()">
                            <option value="">All Brands</option>
                            <?php
                            $stmt = $pdo->query("SELECT DISTINCT brand FROM realstock");
                            while ($brand = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($brand['brand']) . '">' . htmlspecialchars($brand['brand']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="select-wrapper">
                        <label for="colorSelect">Select Color:</label>
                        <select id="colorSelect" onchange="filterByBrandAndColor()">
                            <option value="">All Colors</option>
                            <?php
                            $stmt = $pdo->query("SELECT DISTINCT col FROM realstock");
                            while ($color = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($color['col']) . '">' . htmlspecialchars($color['col']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="button-container">
                <form action="download_excel.php" method="post" style="display: inline;">
                    <button type="submit" class="download-btn"><i class="fas fa-download"></i> Download Excel</button>
                </form>
                <a href="dashboard.php" class="dashboard-btn"><i class="fas fa-tachometer-alt"></i> Go to Dashboard</a>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container animate-on-scroll">
            <div class="table-wrapper">
                <table id="stockr-table" class="stockr-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-barcode"></i> Item Code</th>
                            <th><i class="fas fa-info-circle"></i> Description</th>
                            <th><i class="fas fa-tag"></i> Brand</th>
                            <th><i class="fas fa-palette"></i> Colour</th>
                            <th><i class="fas fa-warehouse"></i> Stock On Hand</th>
                            <th><i class="fas fa-clipboard-check"></i> Requirement</th>
                            <th><i class="fas fa-check-circle"></i> Free Stock</th>
                            <th><i class="fas fa-weight"></i> Free Stock Compound Weight</th>
                            <th><i class="fas fa-weight-hanging"></i> Free Stock St Compound Weight</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM realstock");
                        while ($items = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $tireDetails = getTireDetails($pdo, $items['icode']);
                            $freeStock = getActualStock($pdo, $items['icode']);
                            $requirement = getRequirementSum($pdo, $items['icode']);
                            $weightedValues = calculateWeightedValues($freeStock, $tireDetails['greenweight'], $tireDetails['stgreenweight']);
                            $statusClass = getStockStatus($freeStock, $requirement);
                        ?>
                            <tr class="stockr-row">
                                <td><?= htmlspecialchars($items['icode']); ?></td>
                                <td><?= htmlspecialchars($items['t_size']); ?></td>
                                <td><?= htmlspecialchars($items['brand']); ?></td>
                                <td><?= htmlspecialchars($items['col']); ?></td>
                                <td><?= number_format($items['cstock']); ?></td>
                                <td><?= number_format($requirement); ?></td>
                                <td><span class="status-badge <?= $statusClass; ?>"><?= number_format($freeStock); ?></span></td>
                                <td><?= number_format($weightedValues['freestock_x_greenweight'], 2); ?></td>
                                <td><?= number_format($weightedValues['freestock_x_stgreenweight'], 2); ?></td>
                            </tr>
                        <?php } ?>
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
            const input = document.getElementById('icodeInput').value.toLowerCase();
            const table = document.getElementById('stockr-table');
            const rows = table.getElementsByClassName('stockr-row');
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                const icode = cells[0]?.textContent.toLowerCase() || '';
                const desc = cells[1]?.textContent.toLowerCase() || '';
                const brand = cells[2]?.textContent.toLowerCase() || '';
                rows[i].style.display = (icode.includes(input) || desc.includes(input) || brand.includes(input)) ? '' : 'none';
            }
            updateTotalsDisplay();
        }

        function filterByBrandAndColor() {
            const brand = document.getElementById('brandSelect').value.toLowerCase();
            const color = document.getElementById('colorSelect').value.toLowerCase();
            const table = document.getElementById('stockr-table');
            const rows = table.getElementsByClassName('stockr-row');
            for (let i = 0; i < rows.length;  i++) {
                const cells = rows[i].getElementsByTagName('td');
                const brandValue = cells[2]?.textContent.toLowerCase() || '';
                const colorValue = cells[3]?.textContent.toLowerCase() || '';
                const brandMatch = !brand || brandValue.includes(brand);
                const colorMatch = !color || colorValue.includes(color);
                rows[i].style.display = (brandMatch && colorMatch) ? '' : 'none';
            }
            updateTotalsDisplay();
        }

        function updateTotalsDisplay() {
            const table = document.getElementById('stockr-table');
            const rows = table.getElementsByClassName('stockr-row');
            let totalStockOnHand = 0, totalFreeStock = 0;
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].style.display !== 'none') {
                    const cells = rows[i].getElementsByTagName('td');
                    const stock = parseInt(cells[4]?.textContent.replace(/,/g, '') || 0);
                    const free = parseInt(cells[6]?.textContent.replace(/,/g, '') || 0);
                    totalStockOnHand += stock;
                    totalFreeStock += free;
                }
            }
            document.getElementById('totalStockOnHand').innerHTML = 
                `Total Stock On Hand: <span class="stat-value">${totalStockOnHand.toLocaleString()}</span>`;
            document.getElementById('totalFreeStock').innerHTML = 
                `Total Free Stock: <span class="stat-value">${totalFreeStock.toLocaleString()}</span>`;
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            const windowHeight = window.innerHeight;
            elements.forEach(el => {
                const top = el.getBoundingClientRect().top;
                if (top < windowHeight - 100) {
                    el.classList.add('visible');
                }
            });
        }

        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', () => {
            animateOnScroll();
            updateTotalsDisplay();
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.stockr-row').forEach(row => {
                row.addEventListener('mouseenter', () => {
                    row.style.transform = 'scale(1.005)';
                    row.style.zIndex = '10';
                });
                row.addEventListener('mouseleave', () => {
                    row.style.transform = 'scale(1)';
                    row.style.zIndex = '1';
                });
            });
        });

        document.addEventListener('keydown', e => {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('icodeInput').focus();
            }
            if (e.key === 'Escape') {
                document.getElementById('icodeInput').value = '';
                document.getElementById('brandSelect').value = '';
                document.getElementById('colorSelect').value = '';
                searchStock();
                filterByBrandAndColor();
            }
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 16px;
                background: #27ae60;
                color: white;
                border-radius: 5px;
                z-index: 10000;
                animation: slideIn 0.3s ease;
            }
            .notification.error {
                background: #e74c3c;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>