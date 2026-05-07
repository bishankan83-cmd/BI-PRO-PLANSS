<?php
// Database configuration
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Connect to database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define variables
$errors = [];
$successMsg = "";

// Process the category update form
if (isset($_POST["updateCategories"])) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        $updatedCount = 0;
        
        // Loop through the submitted category selections
        foreach ($_POST['category'] as $id => $category) {
            $updateStmt = $conn->prepare("UPDATE categorized_stock SET category = ? WHERE id = ?");
            $updateStmt->bind_param("si", $category, $id);
            if ($updateStmt->execute()) {
                $updatedCount++;
            }
            $updateStmt->close();
        }
        
        // Commit changes
        $conn->commit();
        
        $successMsg = "Successfully updated categories for $updatedCount items.";
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $errors[] = "Error updating categories: " . $e->getMessage();
    }
}

// Process the process categorized stock form
if (isset($_POST["processCategorized"])) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get selected records to process
        $selectedIds = isset($_POST['selectedItems']) ? $_POST['selectedItems'] : [];
        $processedCount = 0;
        
        foreach ($selectedIds as $id) {
            // First get the record from categorized_stock
            $getStmt = $conn->prepare("SELECT * FROM categorized_stock WHERE id = ?");
            $getStmt->bind_param("i", $id);
            $getStmt->execute();
            $result = $getStmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Insert into final destination based on category
                $category = $row['category'];
                
                if ($category == "AGRADE" || $category == "BGRADE" || $category == "CROSSCUT") {
                    // Insert into a special table based on category
                    $insertStmt = $conn->prepare(
                        "INSERT INTO categorized_stock_processed 
                         (serial_number, original_serial, date, tyre_code, description, qty, category) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $insertStmt->bind_param(
                        "sssssss", 
                        $row['serial_number'], 
                        $row['original_serial'], 
                        $row['date'], 
                        $row['tyre_code'], 
                        $row['description'], 
                        $row['qty'], 
                        $row['category']
                    );
                    
                    if ($insertStmt->execute()) {
                        // Delete from categorized_stock
                        $deleteStmt = $conn->prepare("DELETE FROM categorized_stock WHERE id = ?");
                        $deleteStmt->bind_param("i", $id);
                        $deleteStmt->execute();
                        $deleteStmt->close();
                        $processedCount++;
                    }
                    $insertStmt->close();
                }
            }
            $getStmt->close();
        }
        
        // Commit changes
        $conn->commit();
        
        $successMsg = "Successfully processed $processedCount categorized items.";
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $errors[] = "Error processing categorized items: " . $e->getMessage();
    }
}

// Get count by category
$categoryCounts = [];
$categoriesQuery = "SELECT category, COUNT(*) as count FROM categorized_stock GROUP BY category";
$categoriesResult = $conn->query($categoriesQuery);

if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categoryCounts[$row['category']] = $row['count'];
    }
}

// Get all categorized records for management
$allCategorizedQuery = "SELECT * FROM categorized_stock ORDER BY id DESC";
$allCategorizedResult = $conn->query($allCategorizedQuery);
$allCategorizedRecords = [];

if ($allCategorizedResult) {
    while ($row = $allCategorizedResult->fetch_assoc()) {
        $allCategorizedRecords[] = $row;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categorized Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .dash-card {
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: 100%;
        }
        .dash-card-title {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .dash-card-value {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }
        .empty-state {
            text-align: center;
            padding: 30px 0;
        }
        .empty-state i {
            font-size: 48px;
            color: #adb5bd;
            margin-bottom: 10px;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .filter-label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }
        .select-all-checkbox-container {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .highlight-row {
            background-color: #f0f7ff !important;
        }
        .serial-format {
            font-family: monospace;
        }
        .category-dropdown {
            width: 100%;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="page-header mb-4">
            <h2 class="page-title">Manage Categorized Stock</h2>
        </div>
        
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successMsg); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Category Distribution -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Category Distribution</h5>
                        <div>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($categoryCounts as $category => $count): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="dash-card">
                                        <h5 class="dash-card-title"><?php echo htmlspecialchars($category); ?></h5>
                                        <div class="dash-card-value"><?php echo number_format($count); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($categoryCounts)): ?>
                                <div class="col-12">
                                    <div class="empty-state">
                                        <i class="bi bi-clipboard-x"></i>
                                        <p>No categorized items yet</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Manage Categorized Items -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Categorized Items</h5>
                <div>
                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-light btn-sm refresh-categorized">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($allCategorizedRecords)): ?>
                    <div class="empty-state">
                        <i class="bi bi-folder-x"></i>
                        <p>No categorized items found</p>
                    </div>
                <?php else: ?>
                    <div class="filter-section mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="filter-label">Filter by Category:</label>
                                <div class="category-filter">
                                    <select id="categoryFilter" class="form-select">
                                        <option value="">All Categories</option>
                                        <?php foreach (array_keys($categoryCounts) as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category); ?>">
                                                <?php echo htmlspecialchars($category); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" id="searchInput" class="form-control" placeholder="Search by serial number or tyre code...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form action="" method="post" id="categoryManagementForm">
                        <div class="select-all-checkbox-container">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllItems">
                                <label class="form-check-label" for="selectAllItems">
                                    <strong>Select All Items</strong>
                                </label>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40px"></th>
                                        <th>Serial Number</th>
                                        <th>Original Serial</th>
                                        <th>Date</th>
                                        <th>Tyre Code</th>
                                        <th>Qty</th>
                                        <th>Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allCategorizedRecords as $record): ?>
                                        <tr class="category-row" data-category="<?php echo htmlspecialchars($record['category']); ?>">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox" type="checkbox" name="selectedItems[]" value="<?php echo $record['id']; ?>">
                                                </div>
                                            </td>
                                            <td class="serial-format"><?php echo htmlspecialchars($record['serial_number']); ?></td>
                                            <td><?php echo htmlspecialchars($record['original_serial']); ?></td>
                                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                                            <td><?php echo htmlspecialchars($record['tyre_code']); ?></td>
                                            <td><?php echo htmlspecialchars($record['qty']); ?></td>
                                            <td>
                                                <select name="category[<?php echo $record['id']; ?>]" class="category-dropdown">
                                                    <option value="HOLD" <?php echo ($record['category'] == 'HOLD') ? 'selected' : ''; ?>>HOLD</option>
                                                    <option value="AGRADE" <?php echo ($record['category'] == 'AGRADE') ? 'selected' : ''; ?>>A GRADE</option>
                                                    <option value="BGRADE" <?php echo ($record['category'] == 'BGRADE') ? 'selected' : ''; ?>>B GRADE</option>
                                                    <option value="CROSSCUT" <?php echo ($record['category'] == 'CROSSCUT') ? 'selected' : ''; ?>>CROSSCUT</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="bulk-actions-section mt-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <button type="submit" name="updateCategories" class="btn btn-primary">
                                        <i class="bi bi-tags me-2"></i> Update Categories
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3 text-md-end">
                                    <button type="submit" name="processCategorized" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i> Process Selected Items
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle category filtering
            const categoryFilterSelect = document.getElementById('categoryFilter');
            if (categoryFilterSelect) {
                categoryFilterSelect.addEventListener('change', function() {
                    const selectedCategory = this.value;
                    const rows = document.querySelectorAll('.category-row');
                    
                    rows.forEach(row => {
                        if (!selectedCategory || row.dataset.category === selectedCategory) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
            
            // Handle search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.category-row');
                    
                    rows.forEach(row => {
                        const serialNumber = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const originalSerial = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        const tyreCode = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
                        
                        if (serialNumber.includes(searchTerm) || 
                            originalSerial.includes(searchTerm) || 
                            tyreCode.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
            
            // Handle select all checkbox
            const selectAllCheckbox = document.getElementById('selectAllItems');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
                    itemCheckboxes.forEach(checkbox => {
                        if (checkbox.closest('tr').style.display !== 'none') {
                            checkbox.checked = this.checked;
                        }
                    });
                });
            }
            
            // Auto-check "Select All" if all visible checkboxes are checked
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const selectAllCheckbox = document.getElementById('selectAllItems');
                    const visibleCheckboxes = Array.from(document.querySelectorAll('.item-checkbox')).filter(
                        cb => cb.closest('tr').style.display !== 'none'
                    );
                    const allChecked = visibleCheckboxes.every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked && visibleCheckboxes.length > 0;
                });
            });
            
            // Highlight row when checkbox is checked
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const row = this.closest('tr');
                    if (this.checked) {
                        row.classList.add('highlight-row');
                    } else {
                        row.classList.remove('highlight-row');
                    }
                });
            });
            
            // Show confirmation before processing items
            const categoryManagementForm = document.getElementById('categoryManagementForm');
            if (categoryManagementForm) {
                categoryManagementForm.addEventListener('submit', function(e) {
                    if (e.submitter && e.submitter.name === 'processCategorized') {
                        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
                        if (selectedItems.length === 0) {
                            e.preventDefault();
                            alert('Please select at least one item to process.');
                            return false;
                        }
                        
                        if (!confirm(`Are you sure you want to process ${selectedItems.length} selected item(s)? This action cannot be undone.`)) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>