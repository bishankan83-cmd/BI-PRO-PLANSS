


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Button</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard-button {
            background-color: #000000;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .dashboard-button i {
            margin-right: 10px;
        }

        .dashboard-button:hover {
            background-color: #333333;
            transform: scale(1.05);
        }

        .dashboard-button:active {
            background-color: #666666;
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <button class="dashboard-button" onclick="goToDashboard()">
        <i class="fas fa-home"></i>
        Back to Dashboard
    </button>

    <script>
        function goToDashboard() {
            // Redirect to dashboard.php
            window.location.href = 'dashboard.php';
        }
    </script>
</body>
</html>






<?php
// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filter variables
$search_rm_code = isset($_GET['search_rm_code']) ? $_GET['search_rm_code'] : '';
$filter_quantity = isset($_GET['filter_quantity']) ? $_GET['filter_quantity'] : '';
$filter_band_size = isset($_GET['filter_band_size']) ? $_GET['filter_band_size'] : '';

// Fetch distinct RM Codes for the dropdown
$rm_codes_query = "SELECT DISTINCT RM_code FROM steel_band_stock WHERE RM_code IS NOT NULL";
$rm_codes_result = $conn->query($rm_codes_query);

// Fetch distinct Band Sizes for the dropdown
$band_sizes_query = "SELECT DISTINCT band_size FROM tire_steel_data WHERE band_size IS NOT NULL";
$band_sizes_result = $conn->query($band_sizes_query);

// Base query with GROUP BY to ensure unique RM Codes
$sql = "SELECT s.RM_code, 
               (d.band_size) AS band_size, 
               (s.current_quantity) AS current_quantity, 
               MIN(s.minimum_quantity) AS minimum_quantity
        FROM steel_band_stock s 
        LEFT JOIN tire_steel_data d ON s.RM_code = d.RM_code 
        WHERE 1=1";

// Add search conditions
if (!empty($search_rm_code)) {
    $sql .= " AND s.RM_code = '" . $conn->real_escape_string($search_rm_code) . "'";
}

if ($filter_quantity === 'low') {
    $sql .= " AND s.current_quantity <= s.minimum_quantity";
}

if (!empty($filter_band_size)) {
    $sql .= " AND d.band_size = '" . $conn->real_escape_string($filter_band_size) . "'";
}

// Group by RM Code to ensure unique rows
$sql .= " GROUP BY s.RM_code";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steel Band Stock Management</title>
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-box {
            flex-grow: 1;
            max-width: 300px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #d86b0f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: var(--secondary-color);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-low {
            color: #ff4444;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Steel Band Stock Management</h1>
        
        <form class="controls" method="GET">
            <!-- RM Code Dropdown -->
            <select name="search_rm_code">
                <option value="">All RM Codes</option>
                <?php
                if ($rm_codes_result->num_rows > 0) {
                    while ($row = $rm_codes_result->fetch_assoc()) {
                        $selected = $search_rm_code === $row['RM_code'] ? 'selected' : '';
                        echo "<option value='{$row['RM_code']}' {$selected}>{$row['RM_code']}</option>";
                    }
                }
                ?>
            </select>
            
            <!-- Stock Level Filter -->
            <select name="filter_quantity">
                <option value="">All Stock Levels</option>
                <option value="low" <?php echo $filter_quantity === 'low' ? 'selected' : ''; ?>>Low Stock</option>
            </select>
            
            <!-- Band Size Dropdown -->
            <select name="filter_band_size">
                <option value="">All Band Sizes</option>
                <?php
                if ($band_sizes_result->num_rows > 0) {
                    while ($row = $band_sizes_result->fetch_assoc()) {
                        $selected = $filter_band_size === $row['band_size'] ? 'selected' : '';
                        echo "<option value='{$row['band_size']}' {$selected}>{$row['band_size']}</option>";
                    }
                }
                ?>
            </select>
            
            <!-- Form Buttons -->
            <button type="submit">Apply Filters</button>
            <button type="button" onclick="window.location.href='?'">Reset</button>
            <button type="button" onclick="window.location.reload()">Refresh</button>
        </form>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th>RM Code</th>
                    <th>Band Size</th>
                    <th>Current Quantity</th>
                    <th>Minimum Quantity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $is_low_stock = $row['current_quantity'] <= $row['minimum_quantity'];
                        echo "<tr>
                            <td>{$row['RM_code']}</td>
                            <td>" . ($row['band_size'] ?? 'N/A') . "</td>
                            <td>" . number_format($row['current_quantity']) . "</td>
                            <td>" . number_format($row['minimum_quantity']) . "</td>
                            <td class='" . ($is_low_stock ? 'status-low' : '') . "'>" . 
                                ($is_low_stock ? 'Low Stock' : 'Normal') . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
