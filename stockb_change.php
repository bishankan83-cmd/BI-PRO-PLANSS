

<?php
// config.php
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

// Set charset
$conn->set_charset("utf8mb4");

// Create stock_changes table if not exists
$sql_create_table = "CREATE TABLE IF NOT EXISTS stock_changes (
    change_id INT AUTO_INCREMENT PRIMARY KEY,
    icode VARCHAR(8),
    t_size VARCHAR(75),
    old_value INT,
    new_value INT,
    change_reason TEXT,
    change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by VARCHAR(50),
    INDEX idx_icode (icode),
    INDEX idx_t_size (t_size)
)";

if (!$conn->query($sql_create_table)) {
    die("Error creating table: " . $conn->error);
}

// Function to sanitize input
function sanitizeInput($conn, $input) {
    return $conn->real_escape_string(trim($input));
}

// Function to get stock items
function getStockItems($conn) {
    $sql = "SELECT DISTINCT icode, t_size, brand, col, rim, cstock 
            FROM stockrb 
            ORDER BY icode";
    $result = $conn->query($sql);
    $items = [];
    
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    return $items;
}

// Function to update stock
function updateStock($conn, $icode, $t_size, $new_value, $reason, $changed_by) {
    if (!is_numeric($new_value) || $new_value < 0) {
        return ["success" => false, "message" => "Invalid stock value"];
    }
    
    $conn->begin_transaction();
    
    try {
        // Get current stock
        $stmt = $conn->prepare("SELECT cstock FROM stockrb WHERE icode = ? AND t_size = ?");
        $stmt->bind_param("ss", $icode, $t_size);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Item not found");
        }
        
        $old_value = $result->fetch_assoc()['cstock'];
        
        // Update stock
        $update_stmt = $conn->prepare("UPDATE stockrb SET cstock = ? WHERE icode = ? AND t_size = ?");
        $update_stmt->bind_param("iss", $new_value, $icode, $t_size);
        $update_stmt->execute();
        
        // Log change
        $log_stmt = $conn->prepare("INSERT INTO stock_changes 
            (icode, t_size, old_value, new_value, change_reason, changed_by) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $log_stmt->bind_param("ssiiss", $icode, $t_size, $old_value, $new_value, $reason, $changed_by);
        $log_stmt->execute();
        
        $conn->commit();
        return ["success" => true, "message" => "Stock updated successfully"];
    } catch (Exception $e) {
        $conn->rollback();
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// Function to get stock changes
function getStockChanges($conn, $page = 1, $limit = 10, $filter = []) {
    $offset = ($page - 1) * $limit;
    
    $where_conditions = [];
    $params = [];
    $types = "";
    
    if (!empty($filter['icode'])) {
        $where_conditions[] = "sc.icode LIKE ?";
        $params[] = "%" . $filter['icode'] . "%";
        $types .= "s";
    }
    
    if (!empty($filter['t_size'])) {
        $where_conditions[] = "sc.t_size LIKE ?";
        $params[] = "%" . $filter['t_size'] . "%";
        $types .= "s";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM stock_changes sc $where_clause";
    $stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Get data
    $sql = "SELECT 
                sc.change_id,
                sc.icode,
                sc.t_size,
                sb.brand,
                sb.col,
                sb.rim,
                sc.old_value,
                sc.new_value,
                sc.change_reason,
                sc.change_date,
                sc.changed_by
            FROM stock_changes sc
            JOIN stockrb sb ON sc.icode = sb.icode AND sc.t_size = sb.t_size
            $where_clause
            ORDER BY sc.change_date DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $changes = [];
    while ($row = $result->fetch_assoc()) {
        $changes[] = $row;
    }
    
    return [
        "total" => $total,
        "pages" => ceil($total / $limit),
        "current_page" => $page,
        "data" => $changes
    ];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_stock'])) {
        $result = updateStock(
            $conn,
            sanitizeInput($conn, $_POST['icode']),
            sanitizeInput($conn, $_POST['t_size']),
            (int)$_POST['new_value'],
            sanitizeInput($conn, $_POST['reason']),
            sanitizeInput($conn, $_POST['changed_by'])
        );
        
        $message = $result['message'];
        $success = $result['success'];
    }
}

// Get current page and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'icode' => isset($_GET['filter_icode']) ? sanitizeInput($conn, $_GET['filter_icode']) : '',
    't_size' => isset($_GET['filter_t_size']) ? sanitizeInput($conn, $_GET['filter_t_size']) : ''
];

// Get data for display
$stock_items = getStockItems($conn);
$changes = getStockChanges($conn, $page, 10, $filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color:  #F28018;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #F28018;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color:#F28018;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #F28018;
        }
        .error {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stock Management System</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Update Stock Form -->
        <h2>Update Stock</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="item">Select Item:</label>
                <select name="item" id="item" required onchange="updateFields(this.value)">
                    <option value="">Select an item</option>
                    <?php foreach ($stock_items as $item): ?>
                        <option value="<?php echo htmlspecialchars($item['icode'].'|'.$item['t_size'].'|'.$item['cstock']); ?>">
                            <?php echo htmlspecialchars($item['icode'] . ' - ' . $item['t_size'] . ' (' . $item['brand'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="hidden" name="icode" id="icode" required>
            <input type="hidden" name="t_size" id="t_size" required>
            
            <div class="form-group">
                <label for="current_stock">Current Stock:</label>
                <input type="number" id="current_stock" readonly>
            </div>
            
            <div class="form-group">
                <label for="new_value">New Stock Value:</label>
                <input type="number" name="new_value" id="new_value" required min="0">
            </div>
            
            <div class="form-group">
                <label for="reason">Reason for Change:</label>
                <textarea name="reason" id="reason" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="changed_by">Changed By:</label>
                <input type="text" name="changed_by" id="changed_by" required>
            </div>
            
            <button type="submit" name="update_stock">Update Stock</button>
        </form>
        
        <!-- Stock Changes Table -->
        <h2>Stock Change History</h2>
        
        <!-- Filters -->
        <form method="GET" action="">
            <div class="form-group">
                <label for="filter_icode">Filter by Item Code:</label>
                <input type="text" name="filter_icode" id="filter_icode" value="<?php echo htmlspecialchars($filters['icode']); ?>">
            </div>
            
            <div class="form-group">
                <label for="filter_t_size">Filter by Size:</label>
                <input type="text" name="filter_t_size" id="filter_t_size" value="<?php echo htmlspecialchars($filters['t_size']); ?>">
            </div>
            
            <button type="submit">Apply Filters</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>Change ID</th>
                    <th>Item Code</th>
                    <th>Size</th>
                    <th>Brand</th>
                    <th>Color</th>
                    <th>Rim</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Changed By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($changes['data'] as $change): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($change['change_id']); ?></td>
                        <td><?php echo htmlspecialchars($change['icode']); ?></td>
                        <td><?php echo htmlspecialchars($change['t_size']); ?></td>
                        <td><?php echo htmlspecialchars($change['brand']); ?></td>
                        <td><?php echo htmlspecialchars($change['col']); ?></td>
                        <td><?php echo htmlspecialchars($change['rim']); ?></td>
                        <td><?php echo htmlspecialchars($change['old_value']); ?></td>
                        <td><?php echo htmlspecialchars($change['new_value']); ?></td>
                        <td><?php echo htmlspecialchars($change['change_reason']); ?></td>
                        <td><?php echo htmlspecialchars($change['change_date']); ?></td>
                        <td><?php echo htmlspecialchars($change['changed_by']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $changes['pages']; $i++): ?>
                <a href="?page=<?php echo $i; ?>&filter_icode=<?php echo urlencode($filters['icode']); ?>&filter_t_size=<?php echo urlencode($filters['t_size']);?>" class="<?php echo $i === $changes['current_page'] ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        </div>
    </div>

    <script>
        function updateFields(value) {
            if (value) {
                const [icode, t_size, current_stock] = value.split('|');
                document.getElementById('icode').value = icode;
                document.getElementById('t_size').value = t_size;
                document.getElementById('current_stock').value = current_stock;
                document.getElementById('new_value').value = current_stock;
            } else {
                document.getElementById('icode').value = '';
                document.getElementById('t_size').value = '';
                document.getElementById('current_stock').value = '';
                document.getElementById('new_value').value = '';
            }
        }

        // Add event listener for form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const newValue = parseInt(document.getElementById('new_value').value);
            const reason = document.getElementById('reason').value.trim();
            const changedBy = document.getElementById('changed_by').value.trim();

            if (newValue < 0) {
                e.preventDefault();
                alert('Stock value cannot be negative');
            } else if (!reason) {
                e.preventDefault();
                alert('Please provide a reason for the change');
            } else if (!changedBy) {
                e.preventDefault();
                alert('Please provide who is making this change');
            }
        });

        // Add filtering functionality
        document.getElementById('filter_icode').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>