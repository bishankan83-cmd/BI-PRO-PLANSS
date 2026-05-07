<?php
session_start();
include('include/config.php');

if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Get order ID and discount data
$orderId      = isset($_GET['id'])   ? mysqli_real_escape_string($con, $_GET['id']) : '';
$discountType = isset($_GET['type']) ? $_GET['type'] : '';

if (empty($orderId)) {
    $_SESSION['error_msg'] = "Invalid order ID.";
    header('location:tire-orders.php');
    exit();
}

// Fetch admin details
$adminId     = intval($_SESSION["aid"]);
$adminQuery  = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
$adminData   = mysqli_fetch_array($adminQuery);

// Check admin permission
$adminAcmRef      = $adminId;
$customerQuery    = mysqli_query($con, "SELECT cus_id FROM users WHERE acm_ref='$adminAcmRef'");
$allowedCustomerIds = [];
while ($customer = mysqli_fetch_array($customerQuery)) {
    if (!empty($customer['cus_id'])) {
        $allowedCustomerIds[] = $customer['cus_id'];
    }
}

// Fetch order details with customer code
if (!empty($allowedCustomerIds)) {
    $customerIdList = implode(',', array_map('intval', $allowedCustomerIds));
    $orderQuery = mysqli_query($con, "
        SELECT o.*, u.customer_code AS customer_code,
               u.payment_rate AS payment_rate
        FROM tire_orders o
        LEFT JOIN users u ON o.customer_id = u.id
        WHERE o.order_id = '$orderId' AND o.customer_id IN ($customerIdList)
    ");
    $orderData = mysqli_fetch_array($orderQuery);

    // Fetch order items (includes saved rate_value if column exists)
    $itemsQuery = mysqli_query($con, "
        SELECT * FROM tire_order_items 
        WHERE order_id = '$orderId' 
        ORDER BY item_id
    ");
} else {
    $orderData = null;
}

if (!$orderData) {
    $_SESSION['error_msg'] = "Order not found or you don't have permission to access this order.";
    header('location:tire-orders.php');
    exit();
}

$paymentRate = floatval($orderData['payment_rate'] ?? 0);

// ─────────────────────────────────────────────────────────────────────────────
// Handle status update — saves discount AND rate_value to tire_order_items
// ─────────────────────────────────────────────────────────────────────────────
if (isset($_POST['update_status'])) {
    if (!empty($allowedCustomerIds)) {
        $customerIdList = implode(',', array_map('intval', $allowedCustomerIds));
        $newStatus      = mysqli_real_escape_string($con, $_POST['status']);

        mysqli_begin_transaction($con);

        try {
            // 1. Update order status
            $updateStatusQuery = mysqli_query($con, "
                UPDATE tire_orders 
                SET status = '$newStatus' 
                WHERE order_id = '$orderId' AND customer_id IN ($customerIdList)
            ");
            if (!$updateStatusQuery) {
                throw new Exception("Failed to update order status.");
            }

            $updatedCount = 0;

            // 2a. Uniform discount
            if ($discountType === 'uniform') {
                $uniformDiscount = isset($_GET['discount']) ? floatval($_GET['discount']) : 0;

                $itemsUpdateQuery = mysqli_query($con, "
                    SELECT item_id FROM tire_order_items WHERE order_id = '$orderId'
                ");
                while ($item = mysqli_fetch_array($itemsUpdateQuery)) {
                    $itemId        = intval($item['item_id']);
                    $discountValue = number_format($uniformDiscount, 2, '.', '');

                    $ok = mysqli_query($con, "
                        UPDATE tire_order_items 
                        SET discount = '$discountValue'
                        WHERE item_id = '$itemId' AND order_id = '$orderId'
                    ");
                    if (!$ok) throw new Exception("Failed to update discount for item ID: $itemId");
                    $updatedCount++;
                }

            // 2b. Individual discounts + rate_values
            } elseif ($discountType === 'individual') {
                $itemDiscounts  = isset($_SESSION['item_discounts'])   ? $_SESSION['item_discounts']   : [];
                $itemRateValues = isset($_SESSION['item_rate_values']) ? $_SESSION['item_rate_values'] : [];

                // Merge all item IDs that need updating
                $allItemIds = array_unique(array_merge(
                    array_keys($itemDiscounts),
                    array_keys($itemRateValues)
                ));

                foreach ($allItemIds as $itemId) {
                    $itemId        = intval($itemId);
                    $discountValue = isset($itemDiscounts[$itemId])  ? number_format(floatval($itemDiscounts[$itemId]),  2, '.', '') : null;
                    $rateValue     = isset($itemRateValues[$itemId]) ? number_format(floatval($itemRateValues[$itemId]), 2, '.', '') : null;

                    // Build SET clause dynamically
                    $setParts = [];
                    if ($discountValue !== null) $setParts[] = "discount = '$discountValue'";
                    if ($rateValue     !== null) $setParts[] = "rate_value = '$rateValue'";

                    if (!empty($setParts)) {
                        $setClause = implode(', ', $setParts);
                        $ok = mysqli_query($con, "
                            UPDATE tire_order_items 
                            SET $setClause
                            WHERE item_id = '$itemId' AND order_id = '$orderId'
                        ");
                        if (!$ok) throw new Exception("Failed to update item ID: $itemId");
                        $updatedCount++;
                    }
                }
            }

            mysqli_commit($con);

            // Clear session data
            unset($_SESSION['item_discounts'], $_SESSION['item_rate_values']);

            $_SESSION['success_msg'] = "Order status updated and $updatedCount item(s) saved successfully!";

            if ($newStatus === 'Share_planning' || $newStatus === 'cus_pi_confirm') {
                header('Location: account-manager-dashboard.php?id=' . urlencode($orderId));
            } else {
                header('Location: processing_discount.php?id=' . urlencode($orderId));
            }
            exit();

        } catch (Exception $e) {
            mysqli_rollback($con);
            $statusErrorMsg = $e->getMessage();
        }
    } else {
        $statusErrorMsg = "You don't have permission to update this order.";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Build $discountData for display
// ─────────────────────────────────────────────────────────────────────────────
$discountData     = [];
$hasInvalidWeight = false;

// Pull session rate values (set by dashboard.php)
$sessionRateValues = isset($_SESSION['item_rate_values']) ? $_SESSION['item_rate_values'] : [];

if ($discountType === 'uniform') {
    $uniformDiscount = isset($_GET['discount']) ? floatval($_GET['discount']) : 0;

    mysqli_data_seek($itemsQuery, 0);
    while ($item = mysqli_fetch_array($itemsQuery)) {
        // Rate value priority: session → saved DB value → empty
        $rateVal = isset($sessionRateValues[$item['item_id']])
                   ? floatval($sessionRateValues[$item['item_id']])
                   : (isset($item['rate_value']) && $item['rate_value'] != '' ? floatval($item['rate_value']) : null);

        $discountData[] = [
            'item_id'    => $item['item_id'],
            'icode'      => $item['icode'],
            'quantity'   => $item['quantity'],
            'discount'   => $uniformDiscount,
            'rate_value' => $rateVal,
        ];
    }

} elseif ($discountType === 'individual') {
    $itemDiscounts = isset($_SESSION['item_discounts']) ? $_SESSION['item_discounts'] : [];

    mysqli_data_seek($itemsQuery, 0);
    while ($item = mysqli_fetch_array($itemsQuery)) {
        $discount = isset($itemDiscounts[$item['item_id']]) ? floatval($itemDiscounts[$item['item_id']]) : 0;

        $rateVal = isset($sessionRateValues[$item['item_id']])
                   ? floatval($sessionRateValues[$item['item_id']])
                   : (isset($item['rate_value']) && $item['rate_value'] != '' ? floatval($item['rate_value']) : null);

        $discountData[] = [
            'item_id'    => $item['item_id'],
            'icode'      => $item['icode'],
            'quantity'   => $item['quantity'],
            'discount'   => $discount,
            'rate_value' => $rateVal,
        ];
    }
}

// Helper function to parse comma-separated values
function parseCommaSeparatedValue($value) {
    if (empty($value)) return [];
    $items = array_map('trim', explode(',', $value));
    return array_filter($items);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planatir CMS | Process Discount</title>
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
            --text-gray: #64748b;
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --danger-light: rgba(231, 76, 60, 0.1);
            --info-light: rgba(52, 152, 219, 0.1);
            --orange-light: rgba(242, 128, 24, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
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

        .main-wrapper { min-height: 100vh; padding: 2rem; max-width: 1400px; margin: 0 auto; }

        .page-header  { margin-bottom: 2rem; }
        .page-title   { font-size: 2rem; font-weight: 800; color: var(--dark-gray); margin-bottom: 0.5rem; }
        .page-subtitle { color: var(--text-gray); font-size: 1rem; }

        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem;
            border: none; border-radius: 0.75rem; font-weight: 600; font-size: 0.9rem;
            cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .btn-back    { background: var(--light-gray); color: var(--dark-gray); margin-bottom: 1.5rem; }
        .btn-back:hover { background: var(--border-gray); }
        .btn-primary { background: var(--gradient-1); color: white; box-shadow: var(--shadow); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-success { background: var(--gradient-2); color: white; box-shadow: var(--shadow); }
        .btn-success:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-success:disabled, .btn-primary:disabled { background: #cccccc; cursor: not-allowed; transform: none; opacity: 0.6; }

        .card          { background: var(--white); border-radius: 1rem; border: 1px solid var(--border-gray); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
        .card-header   { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-gray); background: #fdfdfd; }
        .card-title    { font-size: 1.3rem; font-weight: 700; color: var(--dark-gray); display: flex; align-items: center; gap: 0.75rem; }
        .card-body     { padding: 2rem; }

        .alert         { padding: 1rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .alert-success { background: var(--success-light); color: #27ae60; border: 1px solid #a3e4b7; }
        .alert-danger  { background: var(--danger-light);  color: #e74c3c; border: 1px solid #f5c6cb; }
        .alert-warning { background: var(--warning-light); color: #f39c12; border: 1px solid #ffeaa7; }
        .alert-info    { background: var(--info-light);    color: #2980b9; border: 1px solid #a0d8e8; }

        .info-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem; margin-bottom: 2rem; padding: 1.5rem;
            background: var(--bg-light); border-radius: 0.75rem;
        }
        .info-item     { display: flex; flex-direction: column; gap: 0.25rem; }
        .info-label    { font-size: 0.85rem; color: var(--text-gray); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .info-value    { font-size: 1.1rem; font-weight: 600; color: var(--dark-gray); }

        .table-responsive { overflow-x: auto; }
        .table         { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-gray); }
        .table th      { font-weight: 600; color: var(--text-gray); text-transform: uppercase; font-size: 0.85rem; background: var(--bg-light); }
        .table td      { font-size: 0.95rem; }

        .discount-badge { padding: 0.4rem 0.8rem; background: var(--primary-orange); color: white; border-radius: 0.5rem; font-weight: 700; font-size: 0.9rem; }

        .rate-badge {
            display: inline-block; padding: 0.3rem 0.7rem;
            background: var(--info-light); color: #2980b9;
            border-radius: 0.5rem; font-size: 0.88rem; font-weight: 700;
            border: 1px solid rgba(52, 152, 219, 0.25);
        }
        .rate-badge.rate-missing {
            background: var(--danger-light); color: #e74c3c;
            border-color: rgba(231, 76, 60, 0.25);
        }

        .weight-badge { display: inline-block; padding: 0.25rem 0.75rem; background: var(--orange-light); color: var(--primary-orange); border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; border: 1px solid rgba(242, 128, 24, 0.2); }
        .weight-badge.weight-missing { background: var(--danger-light); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.2); }

        .status-badge         { padding: 0.5rem 1rem; border-radius: 0.6rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
        .status-pending       { background: #fff3cd; color: #f39c12; border: 1px solid #ffeaa7; }
        .status-confirmed     { background: #d4edda; color: #27ae60; border: 1px solid #a3e4b7; }
        .status-cus_confirmed { background: #d1ecf1; color: #0d6efd; border: 1px solid #a0d8e8; }

        .multi-value-list     { list-style: none; padding: 0; margin: 0; }
        .multi-value-item     { padding: 0.25rem 0; }
        .multi-value-item:not(:last-child) { border-bottom: 1px dashed var(--border-gray); margin-bottom: 0.25rem; padding-bottom: 0.5rem; }

        .action-buttons { display: flex; gap: 1rem; margin-top: 2rem; }

        .form-group   { margin-bottom: 1.5rem; }
        .form-label   { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--dark-gray); }
        .form-control {
            width: 100%; padding: 0.75rem 1rem;
            border: 2px solid var(--border-gray); border-radius: 0.75rem;
            font-size: 0.95rem; transition: all 0.2s; font-family: 'Inter', sans-serif;
        }
        .form-control:focus   { outline: none; border-color: var(--primary-orange); box-shadow: 0 0 0 3px rgba(242,128,24,0.1); }
        .form-control:disabled { background-color: #f5f5f5; cursor: not-allowed; opacity: 0.6; }
        .form-control option:disabled { color: #999; }

        @media (max-width: 768px) {
            .main-wrapper   { padding: 1rem; }
            .info-grid      { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">

    <!-- Back Button -->
    <a href="account-manager-dashboard.php?id=<?php echo urlencode($orderId); ?>" class="btn btn-back">
        <i class="fas fa-arrow-left"></i> Back to Order
    </a>

    <?php if (isset($statusErrorMsg)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlentities($statusErrorMsg); ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo htmlentities($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlentities($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?></span>
    </div>
    <?php endif; ?>

    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-percent"></i> Discount Applied Successfully</h1>
        <p class="page-subtitle">Review and manage discounts for Order #<?php echo htmlentities($orderId); ?></p>
    </div>

    <!-- Preparation notice -->
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>
            <?php
            echo $discountType === 'uniform'
                ? "Uniform discount has been prepared for all items."
                : "Individual discounts have been prepared for each item.";
            ?>
        </span>
    </div>

    <!-- ── Order Summary ──────────────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-info-circle"></i> Order Information</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Order ID</div>
                    <div class="info-value">#<?php echo htmlentities($orderId); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Customer Code</div>
                    <div class="info-value"><?php echo htmlentities($orderData['customer_code'] ?: 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Discount Type</div>
                    <div class="info-value"><?php echo ucfirst($discountType); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Items</div>
                    <div class="info-value"><?php echo count($discountData); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Rate</div>
                    <div class="info-value"><?php echo $paymentRate > 0 ? number_format($paymentRate, 2) : 'N/A'; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Discount Details Table ─────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-list-alt"></i> Discount Details by Item</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item ID</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Weight (kg)</th>
                            <th>Rate Value</th>
                            <th>Quantity</th>
                            <th>Discount %</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($discountData)):
                        $cnt = 1;
                        foreach ($discountData as $item):
                            $icodes     = parseCommaSeparatedValue($item['icode']);
                            $quantities = parseCommaSeparatedValue($item['quantity']);

                            if (empty($icodes)) {
                                $icodes     = [$item['icode']];
                                $quantities = [$item['quantity']];
                            }

                            $maxCount   = max(count($icodes), count($quantities));
                            $icodes     = array_pad($icodes,     $maxCount, '');
                            $quantities = array_pad($quantities, $maxCount, '');
                    ?>
                    <tr>
                        <td><?php echo $cnt++; ?></td>
                        <td><strong><?php echo htmlentities($item['item_id']); ?></strong></td>

                        <!-- Item Code -->
                        <td>
                            <?php if (count($icodes) > 1): ?>
                                <ul class="multi-value-list">
                                    <?php foreach ($icodes as $icode): if (!empty($icode)): ?>
                                        <li class="multi-value-item"><strong><?php echo htmlentities($icode); ?></strong></li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <strong><?php echo htmlentities($icodes[0]); ?></strong>
                            <?php endif; ?>
                        </td>

                        <!-- Description -->
                        <td>
                            <?php if (count($icodes) > 1): ?>
                                <ul class="multi-value-list">
                                    <?php foreach ($icodes as $icode): if (!empty($icode)):
                                        $ic  = mysqli_real_escape_string($con, $icode);
                                        $dq  = mysqli_query($con, "SELECT description FROM tire_details WHERE icode='$ic'");
                                        $dr  = mysqli_fetch_array($dq);
                                    ?>
                                        <li class="multi-value-item"><?php echo $dr ? htmlentities($dr['description']) : 'N/A'; ?></li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            <?php else:
                                $ic = mysqli_real_escape_string($con, $icodes[0]);
                                $dq = mysqli_query($con, "SELECT description FROM tire_details WHERE icode='$ic'");
                                $dr = mysqli_fetch_array($dq);
                                echo $dr ? htmlentities($dr['description']) : 'N/A';
                            endif; ?>
                        </td>

                        <!-- Weight -->
                        <td>
                            <?php if (count($icodes) > 1): ?>
                                <ul class="multi-value-list">
                                    <?php foreach ($icodes as $icode): if (!empty($icode)):
                                        $ic  = mysqli_real_escape_string($con, $icode);
                                        $wq  = mysqli_query($con, "SELECT fweight FROM tire_details WHERE icode='$ic'");
                                        $wr  = mysqli_fetch_array($wq);
                                        $fw  = $wr ? $wr['fweight'] : null;
                                        $inv = (empty($fw) || $fw == '0' || $fw == 0);
                                        if ($inv) $hasInvalidWeight = true;
                                    ?>
                                        <li class="multi-value-item">
                                            <span class="weight-badge <?php echo $inv ? 'weight-missing' : ''; ?>">
                                                <i class="fas fa-weight-hanging"></i>
                                                <?php echo $inv ? 'Missing' : htmlentities($fw) . ' kg'; ?>
                                            </span>
                                        </li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            <?php else:
                                $ic  = mysqli_real_escape_string($con, $icodes[0]);
                                $wq  = mysqli_query($con, "SELECT fweight FROM tire_details WHERE icode='$ic'");
                                $wr  = mysqli_fetch_array($wq);
                                $fw  = $wr ? $wr['fweight'] : null;
                                $inv = (empty($fw) || $fw == '0' || $fw == 0);
                                if ($inv) $hasInvalidWeight = true;
                            ?>
                                <span class="weight-badge <?php echo $inv ? 'weight-missing' : ''; ?>">
                                    <i class="fas fa-weight-hanging"></i>
                                    <?php echo $inv ? 'Missing' : htmlentities($fw) . ' kg'; ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Rate Value (from session / saved DB value) -->
                        <td>
                            <?php if ($item['rate_value'] !== null): ?>
                                <span class="rate-badge">
                                    <i class="fas fa-dollar-sign"></i>
                                    <?php echo number_format($item['rate_value'], 2); ?>
                                </span>
                            <?php else: ?>
                                <span class="rate-badge rate-missing">
                                    <i class="fas fa-exclamation-triangle"></i> N/A
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Quantity -->
                        <td>
                            <?php if (count($quantities) > 1): ?>
                                <ul class="multi-value-list">
                                    <?php foreach ($quantities as $qty): if (!empty($qty)): ?>
                                        <li class="multi-value-item"><?php echo htmlentities($qty); ?></li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <?php echo htmlentities($quantities[0]); ?>
                            <?php endif; ?>
                        </td>

                        <!-- Discount -->
                        <td>
                            <span class="discount-badge"><?php echo number_format($item['discount'], 2); ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:2rem; color:var(--text-gray);">
                            No discount data available.
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($hasInvalidWeight): ?>
            <div class="alert alert-danger" style="margin-top:1rem;">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>Warning:</strong> Some items have missing or invalid weight data (0 kg). Please update the weight information in the tire details before updating the order status.</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Update Status & Save ───────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-edit"></i> Update Order Status &amp; Save Discounts</h2>
        </div>
        <div class="card-body">
            <?php
            $currentStatus  = $orderData['status'];
            $isRestricted   = ($currentStatus === 'cus_confirmed');
            $isCusPiConfirm = ($currentStatus === 'cus_pi_confirm');
            ?>

            <div class="info-item" style="margin-bottom:1.5rem;">
                <div class="info-label">Current Status</div>
                <div class="info-value">
                    <?php
                    $badgeClass = $currentStatus == 'confirmed'
                                  ? 'status-confirmed'
                                  : ($currentStatus == 'cus_confirmed' ? 'status-cus_confirmed' : 'status-pending');
                    $statusText = ucwords(str_replace('_', ' ', $currentStatus));
                    ?>
                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo htmlentities($statusText); ?></span>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span><strong>Important:</strong> Clicking "Update Status &amp; Save Discounts" will permanently save all discount and rate value data shown above to the database.</span>
            </div>

            <form method="POST" action=""
                  onsubmit="return confirm('Are you sure you want to update the status and save all discounts to the database?');">
                <div class="form-group">
                    <label class="form-label" for="status">Change Status</label>
                    <select name="status" id="status" class="form-control" required
                            <?php echo ($isRestricted || $hasInvalidWeight) ? 'disabled' : ''; ?>>
                        <option value="">-- Select Status --</option>
                        <option value="Manager_confirm_discount"    <?php echo ($currentStatus == 'Manager_confirm_discount')    ? 'selected' : ''; ?>>ACM Confirmed</option>
                        <option value="confirmed"      <?php echo ($currentStatus == 'confirmed')      ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $hasInvalidWeight) ? 'disabled' : ''; ?>>Confirmed</option>
                        <option value="cus_confirmed"  <?php echo ($currentStatus == 'cus_confirmed')  ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $hasInvalidWeight) ? 'disabled' : ''; ?>>Customer Confirmed</option>
                        <option value="cus_pi_confirm" <?php echo ($currentStatus == 'cus_pi_confirm') ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $hasInvalidWeight) ? 'disabled' : ''; ?>>Customer PI Confirmed</option>
                        <option value="Share_planning" <?php echo ($currentStatus == 'Share_planning') ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $hasInvalidWeight) ? 'disabled' : ''; ?>>Share Planning</option>
                    </select>

                    <?php if ($isRestricted): ?>
                    <input type="hidden" name="status" value="Manager_confirm_discount">
                    <p style="margin-top:0.5rem; color:var(--text-gray); font-size:0.9rem;">
                        <i class="fas fa-info-circle"></i> When status is "Customer Confirmed", only "ACM Confirmed" can be selected.
                    </p>
                    <?php endif; ?>

                    <?php if ($isCusPiConfirm): ?>
                    <input type="hidden" name="status" value="Share_planning">
                    <p style="margin-top:0.5rem; color:var(--text-gray); font-size:0.9rem;">
                        <i class="fas fa-info-circle"></i> When status is "Customer PI Confirmed", it will be automatically set to "Share Planning".
                    </p>
                    <?php endif; ?>

                    <?php if ($hasInvalidWeight): ?>
                    <p style="margin-top:0.5rem; color:#e74c3c; font-size:0.9rem;">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Status update is disabled</strong> because some items have missing or invalid weight data.
                    </p>
                    <?php endif; ?>
                </div>

                <button type="submit" name="update_status" class="btn btn-success"
                        <?php echo $hasInvalidWeight ? 'disabled' : ''; ?>>
                    <i class="fas fa-save"></i> Update Status &amp; Save Discounts
                </button>
            </form>
        </div>
    </div>

    <!-- ── Action Buttons ─────────────────────────────────────────────────── -->
    <div class="card">
        <div class="card-body">
            <div class="action-buttons">
                <a href="account-manager-dashboard.php?id=<?php echo urlencode($orderId); ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Order
                </a>
                <a href="generate_quotation.php?id=<?php echo urlencode($orderId); ?>&discount_type=<?php echo urlencode($discountType); ?>" class="btn btn-primary">
                    <i class="fas fa-file-invoice"></i> Generate Quotation
                </a>
                <a href="tire-orders.php" class="btn btn-success">
                    <i class="fas fa-list"></i> View All Orders
                </a>
            </div>
        </div>
    </div>

</div><!-- /.main-wrapper -->

</body>
</html>