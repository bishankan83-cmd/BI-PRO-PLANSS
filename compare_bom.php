<?php
// Configuration
define('ENVIRONMENT', 'development'); // 'development' or 'production'

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'planatir_task_managemen');
define('DB_PASSWORD', 'Bishan@1919');
define('DB_NAME', 'planatir_task_managemen');

// Additional security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

class BOMComparator {
    private $conn;
    private $columns;

    public function __construct(mysqli $connection) {
        $this->conn = $connection;
        $this->columns = [
            't_size', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 
            'm', 'n', 'o', 'p', 'q', 'r', 'Grand Totalcompound weight', 
            'Color', 'Brand', 'Green Tire weight', 'PBweight'
        ];
    }

    public function compareBoMTables() {
        try {
            $sql_bom_comparison = $this->buildComparisonQuery();
            $stmt = $this->conn->prepare($sql_bom_comparison);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result;
        } catch (Exception $e) {
            error_log("Comparison Error: " . $e->getMessage());
            return false;
        }
    }

    private function buildComparisonQuery() {
        $columnSql = $this->generateComparisonColumns();
        $conditionSql = $this->generateComparisonConditions();

        return "
            SELECT 
                t.Item AS bom_tem_Item,
                t.icode AS bom_tem_icode,
                $columnSql
            FROM bom_tem t
            LEFT JOIN bom_new n ON t.icode = n.icode
            WHERE $conditionSql
        ";
    }

    private function generateComparisonColumns() {
        $columnMapping = [
            't_size' => 'Size',
            'a' => 'ATPRS',
            'b' => 'B-ATS 15',
            'c' => 'B-BNS 24',
            'd' => 'BG-BLS 12',
            'e' => 'CG - BS 901',
            'f' => 'C - SMS 501',
            'g' => 'C-ATS 20',
            'h' => 'C-SMS 702',
            'i' => 'C-ATNMS 20',
            'j' => 'T - TRS 102',
            'k' => 'T-ATNM S',
            'l' => 'T-ATS 30',
            'm' => 'T-ATS 35',
            'n' => 'T-KS 40',
            'o' => 'T-TRNMS 402',
            'p' => 'T-TRNMS 402G',
            'q' => 'T-TRS 202',
            'r' => 'WC0001',
            'Grand Totalcompound weight' => 'Grand Total Compound Weight',
            'Color' => 'Color',
            'Brand' => 'Brand',
            'Green Tire weight' => 'Green Tire Weight',
            'PBweight' => 'Profile/Bead Weight'
        ];

        $columnSql = implode(", ", array_map(function($col) use ($columnMapping) {
            $friendlyName = $columnMapping[$col] ?? $col;
            return "t.`$col` AS bom_tem_" . str_replace(' ', '_', $col) . ", 
                    n.`$col` AS bom_new_" . str_replace(' ', '_', $col) . ", 
                    '$friendlyName' AS friendly_name_" . str_replace(' ', '_', $col);
        }, $this->columns));

        return $columnSql;
    }

    private function generateComparisonConditions() {
        $conditions = array_map(function($col) {
            return "n.`$col` IS NULL OR n.`$col` != t.`$col`";
        }, $this->columns);

        return implode(" OR ", $conditions);
    }

    public function renderComparisonTable($result) {
        if (!$result || $result->num_rows === 0) {
            echo "<div class='alert alert-info'>No differences found between tables.</div>";
            return;
        }

        $columnMapping = [
            't_size' => 'Size',
            'a' => 'ATPRS',
            'b' => 'B-ATS 15',
            'c' => 'B-BNS 24',
            'd' => 'BG-BLS 12',
            'e' => 'CG - BS 901',
            'f' => 'C - SMS 501',
            'g' => 'C-ATS 20',
            'h' => 'C-SMS 702',
            'i' => 'C-ATNMS 20',
            'j' => 'T - TRS 102',
            'k' => 'T-ATNM S',
            'l' => 'T-ATS 30',
            'm' => 'T-ATS 35',
            'n' => 'T-KS 40',
            'o' => 'T-TRNMS 402',
            'p' => 'T-TRNMS 402G',
            'q' => 'T-TRS 202',
            'r' => 'WC0001',
            'Grand Totalcompound weight' => 'Grand Total Compound Weight',
            'Color' => 'Color',
            'Brand' => 'Brand',
            'Green Tire weight' => 'Green Tire Weight',
            'PBweight' => 'Profile/Bead Weight'
        ];

        echo "<div class='table-responsive'>";
        echo "<table id='comparison-table' class='table table-striped table-hover'>";
        echo "<thead class='thead-dark'><tr>";
        echo "<th>Item</th><th>iCode</th>";
        
        foreach ($this->columns as $col) {
            echo "<th>" . htmlspecialchars($columnMapping[$col] ?? $col) . "</th>";
        }
        echo "</tr></thead><tbody>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['bom_tem_Item']) . "</td>";
            echo "<td>" . htmlspecialchars($row['bom_tem_icode']) . "</td>";

            foreach ($this->columns as $col) {
                $tem_key = 'bom_tem_' . str_replace(' ', '_', $col);
                $new_key = 'bom_new_' . str_replace(' ', '_', $col);
                
                $tem_value = $row[$tem_key] ?? 'N/A';
                $new_value = $row[$new_key] ?? 'N/A';
                
                $class = ($tem_value != $new_value) ? 'table-danger' : '';
                echo "<td class='$class'>" . 
                     htmlspecialchars($tem_value) . 
                     " <i class='fas fa-arrow-right text-muted mx-2'></i> " . 
                     htmlspecialchars($new_value) . 
                     "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>";
    }
    
    // New function to insert data from bom_tem to bom_new
    public function insertAllDataToBomNew() {
        try {
            // Start transaction
            $this->conn->begin_transaction();
            
            // First, delete any existing data in bom_new that might conflict
            $delete_sql = "DELETE FROM bom_new WHERE icode IN (SELECT icode FROM bom_tem)";
            $this->conn->query($delete_sql);
            
            // Build column list for INSERT
            $columns = ['Item', 'icode'];
            $columns = array_merge($columns, $this->columns);
            $column_list = '`' . implode('`, `', $columns) . '`';
            
            // Insert data from bom_tem to bom_new
            $insert_sql = "INSERT INTO bom_new ($column_list) SELECT $column_list FROM bom_tem";
            $result = $this->conn->query($insert_sql);
            
            if (!$result) {
                throw new Exception("Insert failed: " . $this->conn->error);
            }
            
            $rows_affected = $this->conn->affected_rows;
            
            // Commit transaction
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => "$rows_affected rows inserted successfully from bom_tem to bom_new"
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Insert Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to insert data: " . $e->getMessage()
            ];
        }
    }
    
    // New function to delete bom_tem table
    public function deleteBomTemTable() {
        try {
            $drop_sql = "DROP TABLE bom_tem";
            $result = $this->conn->query($drop_sql);
            
            if (!$result) {
                throw new Exception("Delete failed: " . $this->conn->error);
            }
            
            return [
                'success' => true,
                'message' => "Table bom_tem has been deleted successfully"
            ];
        } catch (Exception $e) {
            error_log("Delete Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to delete table: " . $e->getMessage()
            ];
        }
    }
}

// Database Connection Function
function createDatabaseConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Sorry, there was a problem connecting to the database.");
    }
}

// Main execution
try {
    // Disable error reporting in production
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    }

    $conn = createDatabaseConnection();
    $comparator = new BOMComparator($conn);
    
    // Process form submissions
    $message = "";
    $alertClass = "";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['insert_data'])) {
            $result = $comparator->insertAllDataToBomNew();
            $message = $result['message'];
            $alertClass = $result['success'] ? 'alert-success' : 'alert-danger';
        } elseif (isset($_POST['delete_table'])) {
            $result = $comparator->deleteBomTemTable();
            $message = $result['message'];
            $alertClass = $result['success'] ? 'alert-success' : 'alert-danger';
        }
    }
    
    $comparisonResult = $comparator->compareBoMTables();
} catch (Exception $e) {
    error_log("Execution Error: " . $e->getMessage());
    $comparisonResult = false;
    $message = "An error occurred: " . $e->getMessage();
    $alertClass = "alert-danger";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOM Comparison Tool</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color:rgb(241, 180, 9);
            --secondary-color:rgb(0, 0, 0);
            --background-color: #f4f6f9;
            --text-color: #2c3e50;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .header h1 {
            color: var(--primary-color);
            margin: 0;
            font-weight: 600;
        }

        .header .logo, .header .fa-sync {
            color: var(--secondary-color);
            font-size: 2rem;
        }

        .table-responsive {
            margin-top: 1rem;
        }

        #comparison-table .table-danger {
            background-color: rgba(220, 53, 69, 0.1);
            font-weight: bold;
        }

        #comparison-table .table-danger td {
            position: relative;
        }

        #comparison-table .table-danger td::after {
            content: '⚠️';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .metadata {
            margin-top: 2rem;
            text-align: right;
            color: var(--text-color);
            opacity: 0.7;
            font-size: 0.9rem;
        }

        .action-buttons {
            margin: 1.5rem 0;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }

        .action-buttons .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .action-buttons .btn-danger {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .action-buttons .btn {
            margin-right: 0.5rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .header .logo, .header .fa-sync {
                margin: 0.5rem 0;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
            }
            
            .action-buttons .btn {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="header">
            <div class="d-flex align-items-center">
                <i class="fas fa-table logo me-3"></i>
                <h1>BOM Comparison Tool</h1>
            </div>
            <i class="fas fa-sync"></i>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <h4>Data Management</h4>
            <p>Manage BOM data tables - use with caution!</p>
            
            <form method="POST" action="" class="d-flex flex-wrap">
                <button type="submit" name="insert_data" class="btn btn-primary me-2" onclick="return confirm('Are you sure you want to insert all data from bom_tem to bom_new? This will overwrite existing data in bom_new.');">
                    <i class="fas fa-database me-2"></i>Insert All Data to bom_new
                </button>
                
               
            </form>
        </div>

        <?php 
        // Render the comparison table
        if ($comparisonResult !== false) {
            $comparator->renderComparisonTable($comparisonResult); 
        } else {
            echo "<div class='alert alert-danger'>An error occurred during comparison.</div>";
        }
        ?>

        <div class="metadata">
            <p><i class="far fa-clock me-2"></i>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>

    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const table = document.getElementById('comparison-table');
            if (table) {
                // Table header sorting
                const headers = table.querySelectorAll('thead th');
                headers.forEach((header, index) => {
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', () => {
                        sortTable(table, index);
                    });
                });
            }
        });

        function sortTable(table, column) {
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const isNumeric = (value) => !isNaN(parseFloat(value)) && isFinite(value);

            rows.sort((a, b) => {
                const cellA = a.querySelectorAll('td')[column].textContent;
                const cellB = b.querySelectorAll('td')[column].textContent;

                if (isNumeric(cellA) && isNumeric(cellB)) {
                    return parseFloat(cellA) - parseFloat(cellB);
                }
                return cellA.localeCompare(cellB);
            });

            const tbody = table.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</body>
</html>