<?php
session_start();
include('include/config.php');

// Check if user is logged in
if (empty($_SESSION['id'])) {
    header('location:index.php');
    exit();
}

// Validate order ID
if (!isset($_GET['oid']) || !is_numeric($_GET['oid'])) {
    header('location:order-history.php');
    exit();
}

$orderId = intval($_GET['oid']);
$userId = $_SESSION['id'];
$message = '';
$messageType = '';

// Verify order belongs to user and fetch order details
$orderCheck = mysqli_query($con, "
    SELECT order_id, status, order_notes 
    FROM tire_orders 
    WHERE order_id = '$orderId' AND customer_id = '$userId'
");

if (mysqli_num_rows($orderCheck) == 0) {
    header('location:order-history.php');
    exit();
}

$orderData = mysqli_fetch_assoc($orderCheck);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    try {
        $icode = mysqli_real_escape_string($con, $_POST['icode']);
        $quantity = (int)$_POST['quantity'];
        $unitPrice = (float)$_POST['unit_price'];
        
        if (empty($icode) || $quantity <= 0) {
            throw new Exception("Please select an item and enter a valid quantity");
        }
        
        if ($unitPrice < 0) {
            throw new Exception("Unit price cannot be negative");
        }
        
        // Get product ID and verify item exists
        $productQuery = mysqli_query($con, "SELECT id FROM realstock WHERE icode = '$icode' LIMIT 1");
        if (mysqli_num_rows($productQuery) == 0) {
            throw new Exception("Invalid item code selected");
        }
        $productData = mysqli_fetch_assoc($productQuery);
        $productId = $productData['id'];
        
        // Check if item already exists in order
        $existingItem = mysqli_query($con, "
            SELECT item_id, quantity 
            FROM tire_order_items 
            WHERE order_id = '$orderId' AND icode = '$icode'
        ");
        
        if (mysqli_num_rows($existingItem) > 0) {
            // Update existing item - add to existing quantity
            $existingData = mysqli_fetch_assoc($existingItem);
            $newQuantity = $existingData['quantity'] + $quantity;
            
            $updateQuery = "UPDATE tire_order_items 
                           SET quantity = '$newQuantity', 
                               unit_price = '$unitPrice' 
                           WHERE order_id = '$orderId' AND icode = '$icode'";
            
            if (!mysqli_query($con, $updateQuery)) {
                throw new Exception("Failed to update existing item: " . mysqli_error($con));
            }
        } else {
            // Insert new item
            $insertItem = "INSERT INTO tire_order_items (order_id, product_id, icode, quantity, unit_price) 
                          VALUES ('$orderId', '$productId', '$icode', '$quantity', '$unitPrice')";
            
            if (!mysqli_query($con, $insertItem)) {
                throw new Exception("Failed to add new item: " . mysqli_error($con));
            }
        }
        
        // Update order totals
        $totalsQuery = mysqli_query($con, "
            SELECT COUNT(*) as total_items, SUM(quantity) as total_quantity 
            FROM tire_order_items 
            WHERE order_id = '$orderId'
        ");
        
        if ($totalsQuery) {
            $totals = mysqli_fetch_assoc($totalsQuery);
            
            $updateOrder = "UPDATE tire_orders 
                           SET total_items = '{$totals['total_items']}', 
                               total_quantity = '{$totals['total_quantity']}' 
                           WHERE order_id = '$orderId'";
            
            mysqli_query($con, $updateOrder);
        }
        
        // Redirect back to edit order page with success message
        header("Location: edit_order.php?oid=$orderId&item_added=1");
        exit;
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch all inventory for selection
$inventoryQuery = "SELECT r.id, r.icode, r.t_size, r.brand, r.col, r.rim, t.fweight 
                   FROM realstock r 
                   LEFT JOIN tire_details t ON r.icode = t.icode 
                   ORDER BY r.brand ASC, r.t_size ASC";
$inventoryResult = mysqli_query($con, $inventoryQuery);

if (!$inventoryResult) {
    die("Inventory query failed: " . mysqli_error($con));
}

$inventory = [];
while ($row = mysqli_fetch_assoc($inventoryResult)) {
    $inventory[] = $row;
}

// Fetch existing items in this order to show in the info box
$existingItemsQuery = mysqli_query($con, "
    SELECT icode 
    FROM tire_order_items 
    WHERE order_id = '$orderId'
");
$existingIcodes = [];
while ($row = mysqli_fetch_assoc($existingItemsQuery)) {
    $existingIcodes[] = $row['icode'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item to Order #<?php echo htmlentities($orderId); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --border-gray: #e0e0e0;
            --bg-light: #f8fafc;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --info: #3498db;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--dark-gray);
            line-height: 1.6;
            padding: 2rem;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .page-header {
            background: var(--gradient-1);
            color: var(--white);
            padding: 2rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            opacity: 0.9;
            font-size: 1rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
        }

        .btn-light {
            background: rgba(255,255,255,0.2);
            color: var(--white);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-light:hover {
            background: var(--white);
            color: var(--primary-orange);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-danger {
            background: var(--error);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .content {
            padding: 2.5rem;
        }

        .message {
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid var(--success);
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid var(--error);
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid var(--info);
            padding: 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
        }

        .info-box h4 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-box p {
            color: var(--text-gray);
            font-size: 0.95rem;
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            background: var(--bg-light);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-gray);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-orange);
            outline: none;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .item-preview {
            background: var(--orange-light);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-orange);
        }

        .item-preview h4 {
            color: var(--dark-gray);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .preview-item {
            display: flex;
            flex-direction: column;
        }

        .preview-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            font-weight: 500;
        }

        .preview-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-top: 0.25rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-gray);
        }

        .search-box {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .search-box input {
            padding-left: 2.5rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .helper-text {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-top: 0.5rem;
        }

        .warning-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #fff3cd;
            color: #856404;
            padding: 0.5rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .preview-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .animate-in {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid var(--white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .required {
            color: var(--error);
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="container animate-in">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-plus-circle"></i>
                    Add Item to Order #<?php echo htmlentities($orderId); ?>
                </h1>
                <p class="page-subtitle">Select an item and specify quantity to add</p>
            </div>
            <div class="header-actions">
                <a href="edit_order.php?oid=<?php echo htmlentities($orderId); ?>" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i>
                    Back to Order
                </a>
            </div>
        </div>

        <div class="content">
            <?php if ($message): ?>
                <div class="message <?php echo htmlspecialchars($messageType); ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($existingIcodes)): ?>
            <div class="info-box">
                <h4>
                    <i class="fas fa-info-circle"></i>
                    Note
                </h4>
                <p>If you select an item that already exists in this order, its quantity will be added to the existing quantity.</p>
            </div>
            <?php endif; ?>

            <form id="addItemForm" method="POST">
                <input type="hidden" name="action" value="add_item">

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-box"></i>
                            Item Details
                        </h2>
                    </div>
                    <div class="card-body">
                        <!-- Item Selection -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-barcode"></i>
                                Item Code <span class="required">*</span>
                            </label>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" id="itemSearch" placeholder="Search by code, brand, or size..." autocomplete="off">
                            </div>
                            <select name="icode" id="itemSelect" required>
                                <option value="">-- Select an Item --</option>
                                <?php foreach ($inventory as $item): ?>
                                    <option value="<?php echo htmlentities($item['icode']); ?>"
                                            data-brand="<?php echo htmlentities($item['brand'] ?? ''); ?>"
                                            data-size="<?php echo htmlentities($item['t_size'] ?? ''); ?>"
                                            data-color="<?php echo htmlentities($item['col'] ?? ''); ?>"
                                            data-rim="<?php echo htmlentities($item['rim'] ?? ''); ?>"
                                            data-existing="<?php echo in_array($item['icode'], $existingIcodes) ? '1' : '0'; ?>">
                                        <?php echo htmlentities($item['icode']); ?> - 
                                        <?php echo htmlentities($item['brand'] ?? 'N/A'); ?> - 
                                        <?php echo htmlentities($item['t_size'] ?? 'N/A'); ?>
                                        <?php if (in_array($item['icode'], $existingIcodes)): ?>
                                            (Already in order)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="helper-text">
                                <i class="fas fa-info-circle"></i>
                                Type to search or scroll to select an item
                            </p>
                        </div>

                        <!-- Item Preview -->
                        <div id="itemPreview" class="item-preview" style="display: none;">
                            <h4>
                                <i class="fas fa-eye"></i>
                                Selected Item Preview
                            </h4>
                            <div class="preview-grid">
                                <div class="preview-item">
                                    <span class="preview-label">Item Code</span>
                                    <span class="preview-value" id="previewCode">-</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Brand</span>
                                    <span class="preview-value" id="previewBrand">-</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Size</span>
                                    <span class="preview-value" id="previewSize">-</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Color</span>
                                    <span class="preview-value" id="previewColor">-</span>
                                </div>
                            </div>
                            <div id="existingWarning" style="display: none;">
                                <span class="warning-badge">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    This item already exists in the order. Quantity will be added.
                                </span>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-hashtag"></i>
                                Quantity <span class="required">*</span>
                            </label>
                            <input type="number" name="quantity" id="quantity" min="1" value="1" required>
                            <p class="helper-text">
                                <i class="fas fa-info-circle"></i>
                                Enter the number of units to add
                            </p>
                        </div>

                        <!-- Unit Price -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-rupee-sign"></i>
                                Unit Price (Rs.) <span class="required">*</span>
                            </label>
                            <input type="number" name="unit_price" id="unitPrice" step="0.01" min="0" value="0" required>
                            <p class="helper-text">
                                <i class="fas fa-info-circle"></i>
                                Enter the price per unit
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="edit_order.php?oid=<?php echo htmlentities($orderId); ?>" class="btn btn-danger">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Add Item to Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const existingIcodes = <?php echo json_encode($existingIcodes); ?>;
        
        // Item search functionality
        const itemSearch = document.getElementById('itemSearch');
        const itemSelect = document.getElementById('itemSelect');
        const itemPreview = document.getElementById('itemPreview');
        const existingWarning = document.getElementById('existingWarning');

        itemSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = itemSelect.options;
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                const text = option.text.toLowerCase();
                
                if (text.includes(searchTerm)) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            }
        });

        // Update preview when item is selected
        itemSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                document.getElementById('previewCode').textContent = this.value;
                document.getElementById('previewBrand').textContent = selectedOption.dataset.brand || 'N/A';
                document.getElementById('previewSize').textContent = selectedOption.dataset.size || 'N/A';
                document.getElementById('previewColor').textContent = selectedOption.dataset.color || 'N/A';
                
                // Check if item already exists
                if (selectedOption.dataset.existing === '1') {
                    existingWarning.style.display = 'block';
                } else {
                    existingWarning.style.display = 'none';
                }
                
                itemPreview.style.display = 'block';
                
                // Focus on quantity field
                document.getElementById('quantity').focus();
            } else {
                itemPreview.style.display = 'none';
                existingWarning.style.display = 'none';
            }
        });

        // Form submission
        document.getElementById('addItemForm').addEventListener('submit', function(e) {
            const icode = document.getElementById('itemSelect').value;
            const quantity = document.getElementById('quantity').value;
            const unitPrice = document.getElementById('unitPrice').value;
            
            if (!icode) {
                e.preventDefault();
                alert('⚠️ Please select an item');
                return false;
            }
            
            if (!quantity || quantity <= 0) {
                e.preventDefault();
                alert('⚠️ Please enter a valid quantity (greater than 0)');
                return false;
            }
            
            if (!unitPrice || unitPrice < 0) {
                e.preventDefault();
                alert('⚠️ Please enter a valid unit price (cannot be negative)');
                return false;
            }
            
            // Show loading overlay
            document.getElementById('loadingOverlay').classList.add('active');
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape to cancel
            if (e.key === 'Escape') {
                window.location.href = 'edit_order.php?oid=<?php echo $orderId; ?>';
            }
            
            // Ctrl/Cmd + Enter to submit
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                const form = document.getElementById('addItemForm');
                if (form.checkValidity()) {
                    form.submit();
                } else {
                    form.reportValidity();
                }
            }
        });

        // Focus on search box on page load
        document.addEventListener('DOMContentLoaded', function() {
            itemSearch.focus();
        });

        console.log('Add Item Page loaded successfully for Order #<?php echo $orderId; ?>');
    </script>
</body>
</html>