<?php
// Database connection configuration
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection using mysqli
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper encoding
$conn->set_charset("utf8mb4");

// Create new table for tracking changes if it doesn't exist
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

// Function to update stock and log changes with input validation
function updateStockWithReason($conn, $icode, $t_size, $new_value, $reason, $changed_by) {
    // Validate inputs
    if (!is_numeric($new_value) || $new_value < 0) {
        return ["success" => false, "message" => "Invalid stock value"];
    }
    
    // Sanitize inputs
    $icode = sanitizeInput($conn, $icode);
    $t_size = sanitizeInput($conn, $t_size);
    $reason = sanitizeInput($conn, $reason);
    $changed_by = sanitizeInput($conn, $changed_by);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if item exists
        $stmt = $conn->prepare("SELECT cstock FROM stockr WHERE icode = ? AND t_size = ?");
        $stmt->bind_param("ss", $icode, $t_size);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Item not found");
        }
        
        $row = $result->fetch_assoc();
        $old_value = $row['cstock'];
        
        // Update stock
        $update_stmt = $conn->prepare("UPDATE stockr SET cstock = ? WHERE icode = ? AND t_size = ?");
        $update_stmt->bind_param("iss", $new_value, $icode, $t_size);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update stock");
        }
        
        // Log change
        $log_stmt = $conn->prepare("INSERT INTO stock_changes 
            (icode, t_size, old_value, new_value, change_reason, changed_by) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $log_stmt->bind_param("ssiiss", $icode, $t_size, $old_value, $new_value, $reason, $changed_by);
        
        if (!$log_stmt->execute()) {
            throw new Exception("Failed to log change");
        }
        
        // Commit transaction
        $conn->commit();
        
        return ["success" => true, "message" => "Stock updated successfully"];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// Function to display stock changes with filtering and pagination
function displayStockChanges($conn, $page = 1, $limit = 50, $filter = []) {
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause based on filters
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
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM stock_changes sc $where_clause";
    $stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Main query
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
            JOIN stockr sb ON sc.icode = sb.icode AND sc.t_size = sb.t_size
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
    
    $output = [
        "total" => $total,
        "pages" => ceil($total / $limit),
        "current_page" => $page,
        "data" => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $output["data"][] = $row;
    }
    
    return $output;
}

// Example usage with error handling:
try {
    // Example update
    $update_result = updateStockWithReason(
        $conn,
        "ABC123",
        "245/40R18",
        50,
        "Stock adjustment after inventory check",
        "System Admin"
    );
    
    if ($update_result["success"]) {
        echo "Update successful: " . $update_result["message"] . "\n";
        
        // Display changes with filtering
        $filter = [
            "icode" => "ABC123",
            "t_size" => "245/40R18"
        ];
        
        $changes = displayStockChanges($conn, 1, 50, $filter);
        
        // Output as JSON for API use or format as HTML for display
        header('Content-Type: application/json');
        echo json_encode($changes, JSON_PRETTY_PRINT);
    } else {
        echo "Update failed: " . $update_result["message"] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    // Close connection
    $conn->close();
}
?>