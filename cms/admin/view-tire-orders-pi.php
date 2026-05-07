<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // Fetch admin details
    $adminId = intval($_SESSION["aid"]);
    $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
    $adminData = mysqli_fetch_array($adminQuery);
    
    // Check if this admin has an acm_ref that matches any customer
    $adminAcmRef = $adminId;
    $customerQuery = mysqli_query($con, "SELECT cus_id FROM users WHERE acm_ref='$adminAcmRef'");
    $allowedCustomerIds = [];
    while ($customer = mysqli_fetch_array($customerQuery)) {
        if (!empty($customer['cus_id'])) {
            $allowedCustomerIds[] = $customer['cus_id'];
        }
    }

    // Get order ID - handle both numeric and alphanumeric IDs
    $orderId = mysqli_real_escape_string($con, $_GET['id']);
    
    // Handle status update BEFORE fetching order details
    if (isset($_POST['update_status'])) {
        if (!empty($allowedCustomerIds)) {
            $customerIdList = implode(',', array_map('intval', $allowedCustomerIds));
            $newStatus = mysqli_real_escape_string($con, $_POST['status']);

            $updateQuery = mysqli_query($con, "
                UPDATE tire_orders 
                SET status = '$newStatus' 
                WHERE order_id = '$orderId' AND customer_id IN ($customerIdList)
            ");

            if ($updateQuery) {
                $_SESSION['success_msg'] = "Order status updated successfully!";

                // Redirect to sent_mail6.php after status update
                header('Location: sent_mail_planning.php?id=' . urlencode($orderId));
                exit();
            } else {
                $errorMsg = "Failed to update order status.";
            }
        } else {
            $errorMsg = "You don't have permission to update this order.";
        }
    }
    
    // Fetch order details
    if (!empty($allowedCustomerIds)) {
        $customerIdList = implode(',', array_map('intval', $allowedCustomerIds));
        $orderQuery = mysqli_query($con, "
            SELECT o.* 
            FROM tire_orders o
            WHERE o.order_id = '$orderId' AND o.customer_id IN ($customerIdList)
        ");
        $orderData = mysqli_fetch_array($orderQuery);
        
        // Fetch order items
        $itemsQuery = mysqli_query($con, "
            SELECT * FROM tire_order_items 
            WHERE order_id = '$orderId' 
            ORDER BY item_id
        ");
    } else {
        $orderData = null;
    }
    
    // Helper function to parse comma-separated values
    function parseCommaSeparatedValue($value) {
        if (empty($value)) {
            return [];
        }
        // Split by comma and trim whitespace
        $items = array_map('trim', explode(',', $value));
        return array_filter($items); // Remove empty values
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planatir CMS | View Tire Order</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --medium-gray: #343a40;
            --border-gray: #e0e0e0;
            --bg-light: #f8fafc;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --info-light: rgba(52, 152, 219, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .main-wrapper {
            min-height: 100vh;
            padding: 2rem;
        }

        .page-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 2rem; 
        }
        .page-title { 
            font-size: 2rem; 
            font-weight: 800; 
            color: var(--dark-gray); 
            margin-bottom: 0.5rem; 
        }
        .page-subtitle { 
            color: var(--text-gray); 
            font-size: 1rem; 
        }

        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem;
            border: none; border-radius: 0.75rem; font-weight: 600; font-size: 0.9rem;
            cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .btn-back { 
            background: var(--light-gray); 
            color: var(--dark-gray); 
            margin-bottom: 1.5rem;
        }
        .btn-back:hover { background: var(--border-gray); }
        .btn-primary { background: var(--gradient-1); color: white; box-shadow: var(--shadow); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-success { background: var(--gradient-2); color: white; box-shadow: var(--shadow); }
        .btn-success:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-success:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }

        .card {
            background: var(--white); 
            border-radius: 1rem; 
            border: 1px solid var(--border-gray);
            overflow: hidden; 
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }
        .card-header {
            padding: 1.5rem 2rem; 
            border-bottom: 1px solid var(--border-gray);
            background: #fdfdfd;
        }
        .card-title {
            font-size: 1.3rem; 
            font-weight: 700; 
            color: var(--dark-gray);
            display: flex; 
            align-items: center; 
            gap: 0.75rem;
        }
        .card-body { padding: 2rem; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .info-item {
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 0.75rem;
            border: 1px solid var(--border-gray);
        }
        .info-label {
            font-size: 0.85rem;
            color: var(--text-gray);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .status-badge {
            padding: 0.5rem 1rem; 
            border-radius: 0.6rem; 
            font-size: 0.85rem;
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .status-pending { background: #fff3cd; color: #f39c12; border: 1px solid #ffeaa7; }
        .status-confirmed { background: #d4edda; color: #27ae60; border: 1px solid #a3e4b7; }
        .status-cus_confirmed { background: #d1ecf1; color: #0d6efd; border: 1px solid #a0d8e8; }

        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-gray); }
        .table th { font-weight: 600; color: var(--text-gray); text-transform: uppercase; font-size: 0.85rem; background: var(--bg-light); }
        .table td { font-size: 0.95rem; }
        
        .multi-value-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .multi-value-item {
            padding: 0.25rem 0;
        }
        .multi-value-item:not(:last-child) {
            border-bottom: 1px dashed var(--border-gray);
            margin-bottom: 0.25rem;
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-gray);
            border-radius: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px var(--orange-light);
        }
        .form-control:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .form-control option:disabled {
            color: #999;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-success {
            background: var(--success-light);
            color: #27ae60;
            border: 1px solid #a3e4b7;
        }
        .alert-danger {
            background: #f8d7da;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: var(--warning-light);
            color: #f39c12;
            border: 1px solid #ffeaa7;
        }
        .alert-info {
            background: var(--info-light);
            color: #2980b9;
            border: 1px solid #a0d8e8;
        }

        .no-data {
            text-align: center; 
            padding: 3rem 2rem; 
            color: var(--text-gray);
        }
        .no-data i { 
            font-size: 3rem; 
            opacity: 0.3; 
            margin-bottom: 1rem; 
            display: block; 
        }

        @media (max-width: 768px) {
            .main-wrapper { padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <!-- Back Button -->
        <a href="tire-orders.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>

        <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $errorMsg; ?></span>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($orderData): ?>
        <div class="page-header">
            <div>
                <h1 class="page-title">Order #<?php echo htmlentities($orderData['order_id']); ?></h1>
                <p class="page-subtitle">Complete order details and items</p>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i> Order Summary
                </h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Order ID</div>
                        <div class="info-value">#<?php echo htmlentities($orderData['order_id']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Customer ID</div>
                        <div class="info-value"><?php echo htmlentities($orderData['customer_id']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Order Date</div>
                        <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($orderData['order_date'])); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Quantity</div>
                        <div class="info-value"><?php echo htmlentities($orderData['total_quantity']); ?> Units</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <?php 
                            $status = $orderData['status'];
                            $badgeClass = $status == 'confirmed' ? 'status-confirmed' : 
                                         ($status == 'cus_confirmed' ? 'status-cus_confirmed' : 'status-pending');
                            $statusText = ucwords(str_replace('_', ' ', $status));
                            ?>
                            <span class="status-badge <?php echo $badgeClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($orderData['order_notes']): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Order Notes</div>
                        <div class="info-value" style="font-weight: 400; line-height: 1.6;">
                            <?php echo nl2br(htmlentities($orderData['order_notes'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-boxes"></i> Order Items
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cnt = 1;
                            if (mysqli_num_rows($itemsQuery) > 0) {
                                while ($item = mysqli_fetch_array($itemsQuery)) {
                                    // Parse comma-separated values
                                    $icodes = parseCommaSeparatedValue($item['icode']);
                                    $quantities = parseCommaSeparatedValue($item['quantity']);
                                    
                                    // Ensure we have at least one item
                                    if (empty($icodes)) {
                                        $icodes = [$item['icode']];
                                        $quantities = [$item['quantity']];
                                    }
                                    
                                    // Match quantities to icodes (pad with empty if needed)
                                    $maxCount = max(count($icodes), count($quantities));
                                    $icodes = array_pad($icodes, $maxCount, '');
                                    $quantities = array_pad($quantities, $maxCount, '');
                            ?>
                            <tr>
                                <td><?php echo $cnt++; ?></td>
                                <td>
                                    <?php if (count($icodes) > 1): ?>
                                        <ul class="multi-value-list">
                                            <?php foreach ($icodes as $icode): ?>
                                                <?php if (!empty($icode)): ?>
                                                    <li class="multi-value-item"><strong><?php echo htmlentities($icode); ?></strong></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <strong><?php echo htmlentities($icodes[0]); ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (count($icodes) > 1): ?>
                                        <ul class="multi-value-list">
                                            <?php foreach ($icodes as $icode): ?>
                                                <?php 
                                                if (!empty($icode)) {
                                                    $icode_clean = mysqli_real_escape_string($con, $icode);
                                                    $tireDetailQuery = mysqli_query($con, "SELECT description FROM tire_details WHERE icode = '$icode_clean'");
                                                    $tireDetail = mysqli_fetch_array($tireDetailQuery);
                                                    $description = $tireDetail ? htmlentities($tireDetail['description']) : 'N/A';
                                                ?>
                                                    <li class="multi-value-item"><?php echo $description; ?></li>
                                                <?php } ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <?php 
                                        $icode_clean = mysqli_real_escape_string($con, $icodes[0]);
                                        $tireDetailQuery = mysqli_query($con, "SELECT description FROM tire_details WHERE icode = '$icode_clean'");
                                        $tireDetail = mysqli_fetch_array($tireDetailQuery);
                                        $description = $tireDetail ? htmlentities($tireDetail['description']) : 'N/A';
                                        echo $description;
                                        ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (count($quantities) > 1): ?>
                                        <ul class="multi-value-list">
                                            <?php foreach ($quantities as $qty): ?>
                                                <?php if (!empty($qty)): ?>
                                                    <li class="multi-value-item"><?php echo htmlentities($qty); ?></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <?php echo htmlentities($quantities[0]); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="4">
                                    <div class="no-data">
                                        <i class="fas fa-box-open"></i>
                                        <p>No items found for this order.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Update Status Card - Only Share Planning Option -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-edit"></i> Update Order Status
                </h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>This order can only be updated to "Share Planning" status.</span>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="status" value="Share_planning">
                    <div class="form-group">
                        <label class="form-label" for="status_display">Status</label>
                        <select id="status_display" class="form-control" disabled>
                            <option value="Share_planning" selected>Share Planning</option>
                        </select>
                        <p style="margin-top: 0.5rem; color: var(--text-gray); font-size: 0.9rem;">
                            <i class="fas fa-lock"></i> Only "Share Planning" status is available for this order.
                        </p>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-success">
                        <i class="fas fa-check"></i> Update to Share Planning
                    </button>
                </form>
            </div>
        </div>

        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Order not found or you don't have permission to view this order.</span>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
<?php } ?>