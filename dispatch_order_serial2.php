<?php
// Database configuration
$DB_HOST = 'localhost';
$DB_USERNAME = 'planatir_task_managemen';
$DB_PASSWORD = 'Bishan@1919';
$DB_NAME = 'planatir_task_managemen';

// Create database connection
$conn = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination setup for the second part
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Search functionality
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare base query
$baseQuery = "FROM dwork_ser_tem WHERE 1=1";
$params = [];

// Add search condition if search term exists
if (!empty($searchTerm)) {
    $baseQuery .= " AND (
        serial_number LIKE :search OR 
        icode LIKE :search OR 
        description LIKE :search OR 
        ref LIKE :search OR 
        erp LIKE :search
    )";
    $params[':search'] = "%{$searchTerm}%";
}

// PDO Connection for the second part
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USERNAME, $DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count total records
    $countQuery = "SELECT COUNT(*) AS total " . $baseQuery;
    $countStmt = $pdo->prepare($countQuery);
    
    // Bind search parameters for count query
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Calculate total pages
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Retrieve data
    $query = "SELECT * " . $baseQuery . " LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);

    // Bind search parameter if exists
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    // Bind pagination parameters
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transfer and Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        h1, h2, h3 {
            font-family: 'Cantarell', sans-serif;
            color: #343a40;
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center;
            color: #343a40;
            border-bottom: 3px solid #F28018;
            padding-bottom: 10px;
        }
        
        h2 {
            font-size: 24px;
            margin: 20px 0 15px;
            color: #343a40;
            padding-bottom: 10px;
            border-bottom: 2px solid #F28018;
        }
        
        /* Card Styles */
        .card {
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            background-color: #fff;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #343a40;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 2px solid #F28018;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Button Styles */
        .btn {
            display: inline-block;
            background-color: #F28018;
            color: #fff;
            font-weight: 600;
            padding: 12px 25px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #e07016;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-danger {
            background-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        /* Result Messages */
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid;
            font-size: 16px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        
        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .data-table th {
            background-color: #F28018;
            color: #fff;
            padding: 15px;
            text-align: left;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .data-table tr:hover {
            background-color: #f1f3f5;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Search Form */
        .search-form {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            gap: 10px;
        }
        
        .search-form input[type="text"] {
            padding: 12px 15px;
            width: 300px;
            border: 1px solid #ced4da;
            border-radius: 40px;
            font-family: 'Cantarell', sans-serif;
            transition: border-color 0.3s;
        }
        
        .search-form input[type="text"]:focus {
            outline: none;
            border-color: #F28018;
        }
        
        .search-form .btn {
            margin: 0;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            margin: 20px 0;
            gap: 5px;
        }
        
        .pagination a, 
        .pagination span {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 40px;
            text-decoration: none;
            color: #343a40;
            background-color: #fff;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
        }
        
        .pagination a:hover {
            background-color: #F28018;
            color: #fff;
            border-color: #F28018;
        }
        
        .pagination .current {
            background-color: #F28018;
            color: #fff;
            border-color: #F28018;
        }
        
        /* Stats */
        .stats-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .stats-container span {
            color: #F28018;
            margin: 0 5px;
        }
        
        /* Empty State */
        .no-records {
            padding: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 18px;
        }
        
        .no-records i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #adb5bd;
        }
        
        /* Utility Section */
        .utility-section {
            text-align: center;
            padding: 30px;
        }
        
        .utility-section p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #6c757d;
        }
        
        /* Navigation Buttons */
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        
        /* Action Buttons in Table */
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 14px;
        }
        
        /* Highlight */
        .highlight-message {
            background-color: #343a40;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            border-left: 5px solid #F28018;
            font-weight: 600;
        }
        
        /* Tabs Navigation */
        .tabs {
            display: flex;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            overflow-x: auto;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            color: #495057;
            background-color: transparent;
            border: none;
            font-weight: 600;
            position: relative;
            transition: color 0.3s;
        }
        
        .tab.active {
            color: #F28018;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #F28018;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: #fff;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalOpen 0.3s;
        }
        
        .modal-header {
            background-color: #343a40;
            color: #fff;
            padding: 15px 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: 2px solid #F28018;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            color: #fff;
            margin: 0;
        }
        
        .close-modal {
            color: #fff;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        @keyframes modalOpen {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form input[type="text"] {
                width: 100%;
            }
            
            .navigation-buttons {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Data Transfer Utility Section -->
    <div class="container">
        <h1><i class="fas fa-sync-alt"></i> Data Transfer and Cleanup Utility</h1>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tools"></i> Utility Operations
            </div>
            <div class="card-body">
                <div class="utility-section">
                    
                    
                    <form method="post">
                        <button type="submit" name="transfer_data" class="btn">
                            <i class="fas fa-exchange-alt"></i> Transfer and Clean Data
                        </button>
                    </form>
                </div>

                <?php
                // Process data transfer when button is clicked
                if (isset($_POST['transfer_data'])) {
                    // Start a database transaction
                    $conn->begin_transaction();

                    try {
                        // Step 1: Identify serial numbers to be deleted
                        $matching_serials_query = "
                            SELECT serial_number 
                            FROM dwork_ser_tem 
                            WHERE serial_number IN (SELECT serial_number FROM stock_erp)
                        ";
                        $matching_serials_result = $conn->query($matching_serials_query);
                        $matching_serials_count = $matching_serials_result->num_rows;

                        // Step 2: Delete matching serial numbers from stock_erp
                        $delete_stock_erp_query = "
                            DELETE FROM stock_erp 
                            WHERE serial_number IN (
                                SELECT serial_number 
                                FROM dwork_ser_tem 
                                WHERE serial_number IN (SELECT serial_number FROM stock_erp)
                            )
                        ";
                        $delete_stock_erp_result = $conn->query($delete_stock_erp_query);
                        $deleted_stock_erp_count = $conn->affected_rows;

                        // Step 3: Delete matching serial numbers from dwork_ser_tem
                        $delete_dwork_ser_tem_query = "
                            DELETE FROM dwork_ser_tem 
                            WHERE serial_number IN (SELECT serial_number FROM stock_erp)
                        ";
                        $delete_dwork_ser_tem_result = $conn->query($delete_dwork_ser_tem_query);
                        $deleted_dwork_ser_tem_count = $conn->affected_rows;

                        // Step 4: Insert remaining data into dwork_ser
                        $insert_query = "
                            INSERT INTO dwork_ser (
                                serial_number, 
                                icode, 
                                description, 
                                ref, 
                                erp
                            )
                            SELECT 
                                serial_number, 
                                icode, 
                                description, 
                                ref, 
                                erp
                            FROM dwork_ser_tem
                        ";
                        $insert_result = $conn->query($insert_query);
                        $inserted_count = $conn->affected_rows;

                        // Step 5: Clear dwork_ser_tem after successful transfer
                        $clear_query = "TRUNCATE TABLE dwork_ser_tem";
                        $conn->query($clear_query);

                        // Commit the transaction
                        $conn->commit();
                        
                        // Redirect to another page after successful transfer
                        echo "<script>
                            alert('Data Transfer Successful! Redirecting...');
                            window.location.href = 'import22b.php'; // Replace with your target page
                        </script>";
                        exit(); // Stop further script execution

                    } catch (Exception $e) {
                        // Rollback the transaction in case of error
                        $conn->rollback();

                        // Display error message
                        echo "
                        <div class='result error'>
                            <p><i class='fas fa-exclamation-circle'></i> Transfer Failed:</p>
                            <p>" . htmlspecialchars($e->getMessage()) . "</p>
                        </div>";
                    }
                }
                ?>
                
                <div class="highlight-message">
                    <i class="fas fa-info-circle"></i> This operation will permanently modify data. Please ensure you have a backup before proceeding.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Display Section -->
    <div class="container">
        <h1><i class="fas fa-table"></i> Temporary Data Overview</h1>
        
        <!-- Search Form -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-search"></i> Search Records
            </div>
            <div class="card-body">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search by any field..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Stats Display -->
        <div class="stats-container">
            <div>Total Records: <span><?php echo $totalRecords; ?></span> | 
                 Page <span><?php echo $page; ?></span> of 
                 <span><?php echo max(1, $totalPages); ?></span>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Data Records
            </div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Serial Number</th>
                            <th>ICode</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>ERP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="6" class="no-records">
                                    <i class="fas fa-folder-open"></i><br>
                                    No records found. 
                                    <?php echo !empty($searchTerm) ? "Try a different search term." : ""; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['id']); ?></td>
                                    <td><?php echo htmlspecialchars($record['serial_number']); ?></td>
                                    <td><?php echo htmlspecialchars($record['icode']); ?></td>
                                    <td><?php echo htmlspecialchars($record['description'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['ref'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['erp']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                // Previous page link
                if ($page > 1) {
                    echo "<a href='?page=" . ($page - 1) . 
                         (!empty($searchTerm) ? "&search=" . urlencode($searchTerm) : "") . 
                         "'><i class='fas fa-chevron-left'></i> Previous</a>";
                }

                // Page numbers with limited display
                $pageRange = 2; // Number of pages to show before and after current page
                
                for ($i = max(1, $page - $pageRange); $i <= min($totalPages, $page + $pageRange); $i++) {
                    if ($i == $page) {
                        echo "<span class='current'>$i</span>";
                    } else {
                        echo "<a href='?page=$i" . 
                             (!empty($searchTerm) ? "&search=" . urlencode($searchTerm) : "") . 
                             "'>$i</a>";
                    }
                }

                // Next page link
                if ($page < $totalPages) {
                    echo "<a href='?page=" . ($page + 1) . 
                         (!empty($searchTerm) ? "&search=" . urlencode($searchTerm) : "") . 
                         "'>Next <i class='fas fa-chevron-right'></i></a>";
                }
                ?>
            </div>
        <?php endif; ?>
        
       
    <script>
        // Add animation effect to the success/error message
        document.addEventListener('DOMContentLoaded', function() {
            // For demonstration, making the highlight message blink
            const highlightMessage = document.querySelector('.highlight-message');
            if (highlightMessage) {
                highlightMessage.classList.add('blink');
            }
        });
    </script>
</body>
</html>