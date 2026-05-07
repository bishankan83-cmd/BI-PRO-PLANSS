<?php
// Database connection
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

// Process form submission for transferring selected data
if (isset($_POST['transfer'])) {
    if (isset($_POST['selected_records']) && !empty($_POST['selected_records'])) {
        $selected_ids = $_POST['selected_records'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            foreach ($selected_ids as $id) {
                // Get the record from temporary table
                $stmt = $conn->prepare("SELECT * FROM stock_erp_tem WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    // Insert into main table
                    $insert_stmt = $conn->prepare("INSERT INTO stock_erp (prev_serial, serial_number, date, tyre_code, description, qty) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_stmt->bind_param("sssssi", 
                        $row['prev_serial'], 
                        $row['serial_number'], 
                        $row['date'], 
                        $row['tyre_code'], 
                        $row['description'], 
                        $row['qty']
                    );
                    $insert_stmt->execute();
                    $insert_stmt->close();
                    
                    // Delete from temporary table after successful transfer
                    $delete_stmt = $conn->prepare("DELETE FROM stock_erp_tem WHERE id = ?");
                    $delete_stmt->bind_param("i", $id);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }
                $stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            $success_message = count($selected_ids) . " record(s) transferred successfully and removed from temporary table!";
        } catch (Exception $e) {
            // Rollback in case of error
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        }
    } else {
        $error_message = "No records selected for transfer.";
    }
}

// Search functionality
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Pagination settings
$records_per_page = 600;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Prepare SQL for fetching records with search and pagination
$sql = "SELECT * FROM stock_erp_tem";
if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";
    $sql .= " WHERE prev_serial LIKE ? OR serial_number LIKE ? OR tyre_code LIKE ? OR description LIKE ?";
}
$sql .= " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if (!empty($search_query)) {
    $stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $records_per_page, $offset);
} else {
    $stmt->bind_param("ii", $records_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM stock_erp_tem";
if (!empty($search_query)) {
    $count_sql .= " WHERE prev_serial LIKE ? OR serial_number LIKE ? OR tyre_code LIKE ? OR description LIKE ?";
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($search_query)) {
    $count_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock ERP Data Transfer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f28018;
            --secondary-color: #000000;
            --background-color: #f5f5f5;
            --white: #ffffff;
            --gray-200: #e5e7eb;
            --gray-700: #374151;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--gray-700);
            line-height: 1.5;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h2 {
            margin: 0;
            font-weight: 600;
        }

        .header p {
            margin-top: 10px;
            opacity: 0.9;
        }

        .search-section {
            background-color: var(--white);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
        }

        .search-container {
            display: flex;
            gap: 10px;
        }

        .search-container input {
            flex-grow: 1;
            padding: 10px 15px;
            border: 2px solid var(--gray-200);
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }

        .search-container input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .left-buttons, .right-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--gray-200);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-success {
            background-color: #10B981;
            color: white;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .badge-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .table-container {
            background-color: var(--white);
            border-radius: 8px;
            overflow: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            font-weight: bold;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: rgba(242, 128, 24, 0.05);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
            cursor: pointer;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination {
            display: flex;
            list-style: none;
            gap: 5px;
        }

        .page-item .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            border: 1px solid var(--gray-200);
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .page-item .page-link:hover {
            background-color: var(--gray-200);
        }

        .page-item.active .page-link:hover {
            background-color: var(--primary-color);
        }

        .records-info {
            text-align: center;
            color: var(--gray-700);
            margin-top: 10px;
            font-size: 14px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #ECFDF5;
            border-left: 4px solid #10B981;
            color: #065F46;
        }

        .alert-danger {
            background-color: #FEF2F2;
            border-left: 4px solid #EF4444;
            color: #991B1B;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .left-buttons, .right-buttons {
                width: 100%;
                justify-content: space-between;
            }

            th, td {
                padding: 10px;
            }

            .search-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-exchange-alt"></i> Stock ERP Data Transfer System</h2>
            <p>Select records from the temporary table to transfer to the main stock table. Selected records will be automatically removed from the temporary table after successful transfer.</p>
        </div>

        <?php if(isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="search-section">
            <form method="GET" class="search-container">
                <input type="text" name="search" placeholder="Search by serial, tyre code, or description" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if(!empty($search_query)): ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <form method="POST" id="transfer-form">
            <div class="action-buttons">
                <div class="left-buttons">
                    <button type="submit" name="transfer" class="btn btn-success" id="transfer-btn" disabled>
                        <i class="fas fa-share"></i> Transfer Selected
                    </button>
                    <button type="button" class="btn btn-outline" id="select-all-btn">
                        <i class="fas fa-check-square"></i> Select All
                    </button>
                    <button type="button" class="btn btn-outline" id="deselect-all-btn">
                        <i class="fas fa-square"></i> Deselect All
                    </button>
                </div>
                <div class="right-buttons">
                    <span class="badge badge-primary" id="selected-count">
                        <i class="fas fa-tags"></i> 0 selected
                    </span>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="check-all" class="checkbox-custom"></th>
                            <th width="60">ID</th>
                            <th width="120">Prev Serial</th>
                            <th width="120">Serial Number</th>
                            <th width="100">Date</th>
                            <th width="100">Tyre Code</th>
                            <th>Description</th>
                            <th width="60">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_records[]" value="<?php echo $row['id']; ?>" class="record-checkbox checkbox-custom">
                                    </td>
                                    <td><?php echo $row['id']; ?></td>
                                    <td title="<?php echo htmlspecialchars($row['prev_serial']); ?>"><?php echo htmlspecialchars($row['prev_serial']); ?></td>
                                    <td title="<?php echo htmlspecialchars($row['serial_number']); ?>"><?php echo htmlspecialchars($row['serial_number']); ?></td>
                                    <td><?php echo $row['date']; ?></td>
                                    <td><?php echo htmlspecialchars($row['tyre_code']); ?></td>
                                    <td title="<?php echo htmlspecialchars($row['description']); ?>"><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo $row['qty']; ?></td>
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
        </form>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Previous">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search_query) ? '&search=' . urlencode($search_query) : '') . '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
                              <a class="page-link" href="?page=' . $i . (!empty($search_query) ? '&search=' . urlencode($search_query) : '') . '">' . $i . '</a>
                              </li>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($search_query) ? '&search=' . urlencode($search_query) : '') . '">' . $total_pages . '</a></li>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" aria-label="Next">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="records-info">
            Showing <?php echo min(($offset + 1), $total_records); ?> to <?php echo min(($offset + $records_per_page), $total_records); ?> of <?php echo $total_records; ?> records
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('check-all');
            const recordCheckboxes = document.querySelectorAll('.record-checkbox');
            const transferBtn = document.getElementById('transfer-btn');
            const selectAllBtn = document.getElementById('select-all-btn');
            const deselectAllBtn = document.getElementById('deselect-all-btn');
            const selectedCountBadge = document.getElementById('selected-count');

            // Function to update selected count and transfer button state
            function updateSelectionStatus() {
                const selectedCount = document.querySelectorAll('.record-checkbox:checked').length;
                selectedCountBadge.innerHTML = `<i class="fas fa-tags"></i> ${selectedCount} selected`;
                transferBtn.disabled = selectedCount === 0;
                
                // Update the transfer button appearance based on state
                if (selectedCount === 0) {
                    transferBtn.classList.add('btn-secondary');
                    transferBtn.classList.remove('btn-success');
                } else {
                    transferBtn.classList.add('btn-success');
                    transferBtn.classList.remove('btn-secondary');
                }
            }

            // Check all checkbox
            checkAll.addEventListener('change', function() {
                recordCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectionStatus();
            });

            // Individual checkboxes
            recordCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = [...recordCheckboxes].every(c => c.checked);
                    const anyChecked = [...recordCheckboxes].some(c => c.checked);
                    
                    checkAll.checked = allChecked;
                    checkAll.indeterminate = anyChecked && !allChecked;
                    
                    updateSelectionStatus();
                });
            });

            // Select all button
            selectAllBtn.addEventListener('click', function() {
                recordCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
                checkAll.checked = true;
                checkAll.indeterminate = false;
                updateSelectionStatus();
            });

            // Deselect all button
            deselectAllBtn.addEventListener('click', function() {
                recordCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                checkAll.checked = false;
                checkAll.indeterminate = false;
                updateSelectionStatus();
            });

            // Initialize selection status
            updateSelectionStatus();
            
            // Add row click handler to toggle checkbox
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('click', function(event) {
                    // Don't toggle if clicking on the checkbox itself
                    if (event.target.type !== 'checkbox') {
                        const checkbox = this.querySelector('.record-checkbox');
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>