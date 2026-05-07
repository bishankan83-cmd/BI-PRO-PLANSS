<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // ─────────────────────────────────────────────────────────────
    // Auto-create order-level charge columns if they don't exist
    // Run once; safe to leave in production (IF NOT EXISTS pattern)
    // ─────────────────────────────────────────────────────────────
    $alterStatements = [
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge1_name  VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge1_value VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge2_name  VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge2_value VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge3_name  VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge3_value VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge4_name  VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE tire_orders ADD COLUMN IF NOT EXISTS charge4_value VARCHAR(100) NOT NULL DEFAULT ''",
    ];
    foreach ($alterStatements as $sql) {
        mysqli_query($con, $sql); // silently ignored if column already exists
    }

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

    // Get order ID
    $orderId = mysqli_real_escape_string($con, $_GET['id']);

    // ─────────────────────────────────────────────────────────────
    // Handle status update BEFORE fetching order details
    // ─────────────────────────────────────────────────────────────
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
                if ($newStatus === 'Share_planning' || $newStatus === 'cus_pi_confirm') {
                    header('Location: dashboard.php?id=' . urlencode($orderId));
                } else {
                    header('Location: sent_mail6.php?id=' . urlencode($orderId));
                }
                exit();
            } else {
                $errorMsg = "Failed to update order status.";
            }
        } else {
            $errorMsg = "You don't have permission to update this order.";
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Handle ORDER-LEVEL charge save (4 charges on the whole order)
    // ─────────────────────────────────────────────────────────────
    if (isset($_POST['save_order_charges'])) {
        if (!empty($allowedCustomerIds)) {
            $customerIdList = implode(',', array_map('intval', $allowedCustomerIds));
            $c1n = mysqli_real_escape_string($con, $_POST['charge1_name']  ?? '');
            $c1v = mysqli_real_escape_string($con, $_POST['charge1_value'] ?? '');
            $c2n = mysqli_real_escape_string($con, $_POST['charge2_name']  ?? '');
            $c2v = mysqli_real_escape_string($con, $_POST['charge2_value'] ?? '');
            $c3n = mysqli_real_escape_string($con, $_POST['charge3_name']  ?? '');
            $c3v = mysqli_real_escape_string($con, $_POST['charge3_value'] ?? '');
            $c4n = mysqli_real_escape_string($con, $_POST['charge4_name']  ?? '');
            $c4v = mysqli_real_escape_string($con, $_POST['charge4_value'] ?? '');

            $chargeUpdate = mysqli_query($con, "
                UPDATE tire_orders
                SET charge1_name  = '$c1n', charge1_value = '$c1v',
                    charge2_name  = '$c2n', charge2_value = '$c2v',
                    charge3_name  = '$c3n', charge3_value = '$c3v',
                    charge4_name  = '$c4n', charge4_value = '$c4v'
                WHERE order_id = '$orderId' AND customer_id IN ($customerIdList)
            ");

            if ($chargeUpdate) {
                $_SESSION['success_msg'] = "Order charges saved successfully!";
            } else {
                $errorMsg = "Failed to save order charges.";
            }
            header('Location: view-tire-order.php?id=' . urlencode($orderId));
            exit();
        } else {
            $errorMsg = "You don't have permission to update this order.";
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Handle discount submission
    // ─────────────────────────────────────────────────────────────
    if (isset($_POST['apply_discount'])) {
        $discountType = $_POST['discount_type'];
        $orderId      = mysqli_real_escape_string($con, $_POST['order_id']);

        if (isset($_POST['item_rate_value']) && is_array($_POST['item_rate_value'])) {
            foreach ($_POST['item_rate_value'] as $itemId => $rateVal) {
                $itemId  = intval($itemId);
                $rateVal = floatval($rateVal);
                mysqli_query($con, "
                    UPDATE tire_order_items
                    SET rate_value = '$rateVal'
                    WHERE item_id = '$itemId'
                ");
            }
        }

        if ($discountType === 'uniform') {
            $uniformDiscount = floatval($_POST['uniform_discount']);
            header('Location: process_discount.php?id=' . urlencode($orderId) . '&type=uniform&discount=' . urlencode($uniformDiscount));
            exit();
        } elseif ($discountType === 'individual') {
            $itemDiscounts  = [];
            $itemRateValues = [];

            if (isset($_POST['item_discount']) && is_array($_POST['item_discount'])) {
                foreach ($_POST['item_discount'] as $itemId => $discount) {
                    $itemDiscounts[intval($itemId)] = floatval($discount);
                }
            }
            if (isset($_POST['item_rate_value']) && is_array($_POST['item_rate_value'])) {
                foreach ($_POST['item_rate_value'] as $itemId => $rateVal) {
                    $itemRateValues[intval($itemId)] = floatval($rateVal);
                }
            }

            $_SESSION['item_discounts']   = $itemDiscounts;
            $_SESSION['item_rate_values'] = $itemRateValues;
            header('Location: process_discount.php?id=' . urlencode($orderId) . '&type=individual');
            exit();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Fetch order + items
    // ─────────────────────────────────────────────────────────────
    if (!empty($allowedCustomerIds)) {
        $customerIdList = implode(',', array_map('intval', $allowedCustomerIds));
        $orderQuery = mysqli_query($con, "
            SELECT o.*, u.customer_code AS customer_code
            FROM tire_orders o
            LEFT JOIN users u ON o.customer_id = u.id
            WHERE o.order_id = '$orderId' AND o.customer_id IN ($customerIdList)
        ");
        $orderData = mysqli_fetch_array($orderQuery);

        $paymentRate = 0;
        $customerId  = 0;
        if ($orderData) {
            $customerId = intval($orderData['customer_id']);
            $paymentRateQuery = mysqli_query($con, "
                SELECT payment_rate FROM users WHERE id = '$customerId'
            ");
            $paymentRateRow = mysqli_fetch_array($paymentRateQuery);
            $paymentRate = $paymentRateRow ? floatval($paymentRateRow['payment_rate']) : 0;
        }
    } else {
        $orderData   = null;
        $paymentRate = 0;
        $customerId  = 0;
    }

    // ─────────────────────────────────────────────────────────────
    // Helper: resolve the effective price for one icode + customer.
    //
    //   1. If customer_items has a row for (cus_id, icode) → use that price.
    //   2. Otherwise fall back to fweight * paymentRate.
    //
    // Returns float|null  (null = cannot determine a price)
    // $source is set by reference to 'customer_items' | 'rate' | null
    // ─────────────────────────────────────────────────────────────
    function getEffectivePrice($con, $icode, $customerId, $paymentRate, &$source = null) {
        if (empty($icode)) { $source = null; return null; }

        $icSafe = mysqli_real_escape_string($con, $icode);
        $cidInt = intval($customerId);

        // 1. Check customer_items table first
        if ($cidInt > 0) {
            $ciQuery = mysqli_query($con, "
                SELECT price FROM customer_items
                WHERE cus_id = '$cidInt' AND icode = '$icSafe'
                LIMIT 1
            ");
            if ($ciQuery && mysqli_num_rows($ciQuery) > 0) {
                $ciRow = mysqli_fetch_array($ciQuery);
                $source = 'customer_items';
                return floatval($ciRow['price']);
            }
        }

        // 2. Fallback: fweight × paymentRate
        if ($paymentRate > 0) {
            $twQuery = mysqli_query($con, "SELECT fweight FROM tire_details WHERE icode = '$icSafe'");
            if ($twQuery) {
                $twRow = mysqli_fetch_array($twQuery);
                $fw    = $twRow ? $twRow['fweight'] : null;
                if (!empty($fw) && floatval($fw) != 0) {
                    $source = 'rate';
                    return floatval($fw) * $paymentRate;
                }
            }
        }

        $source = null;
        return null;
    }

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
            --danger-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,.05);
            --shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05);
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
            background:var(--bg-light); color:var(--dark-gray);
            line-height:1.6; -webkit-font-smoothing:antialiased;
        }
        .main-wrapper { min-height:100vh; padding:2rem; }

        /* ── Buttons ── */
        .btn {
            display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1.2rem;
            border:none; border-radius:.75rem; font-weight:600; font-size:.9rem;
            cursor:pointer; text-decoration:none; transition:all .2s;
        }
        .btn-back    { background:var(--light-gray); color:var(--dark-gray); margin-bottom:1.5rem; }
        .btn-back:hover { background:var(--border-gray); }
        .btn-primary { background:var(--gradient-1); color:#fff; box-shadow:var(--shadow); }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:var(--shadow-lg); }
        .btn-success { background:var(--gradient-2); color:#fff; box-shadow:var(--shadow); }
        .btn-success:hover { transform:translateY(-2px); box-shadow:var(--shadow-lg); }
        .btn-success:disabled { background:#ccc; cursor:not-allowed; transform:none; opacity:.6; }
        .btn-info    { background:var(--gradient-3); color:#fff; box-shadow:var(--shadow); }
        .btn-info:hover { transform:translateY(-2px); box-shadow:var(--shadow-lg); }
        .btn-warning { background:linear-gradient(135deg,#f39c12 0%,#e67e22 100%); color:#fff; box-shadow:var(--shadow); }
        .btn-warning:hover { transform:translateY(-2px); box-shadow:var(--shadow-lg); }

        /* ── Page header ── */
        .page-header  { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; }
        .page-title   { font-size:2rem; font-weight:800; color:var(--dark-gray); margin-bottom:.5rem; }
        .page-subtitle{ color:var(--text-gray); font-size:1rem; }

        /* ── Cards ── */
        .card        { background:var(--white); border-radius:1rem; border:1px solid var(--border-gray); overflow:hidden; box-shadow:var(--shadow-sm); margin-bottom:1.5rem; }
        .card-header { padding:1.5rem 2rem; border-bottom:1px solid var(--border-gray); background:#fdfdfd; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; }
        .card-title  { font-size:1.3rem; font-weight:700; color:var(--dark-gray); display:flex; align-items:center; gap:.75rem; }
        .card-body   { padding:2rem; }

        /* ── Info grid ── */
        .info-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.5rem; margin-bottom:2rem; }
        .info-item  { padding:1rem; background:var(--bg-light); border-radius:.75rem; border:1px solid var(--border-gray); }
        .info-label { font-size:.85rem; color:var(--text-gray); text-transform:uppercase; font-weight:600; margin-bottom:.5rem; letter-spacing:.5px; }
        .info-value { font-size:1.1rem; font-weight:600; color:var(--dark-gray); }

        /* ── Status badge ── */
        .status-badge                    { padding:.5rem 1rem; border-radius:.6rem; font-size:.85rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; display:inline-block; }
        .status-pending                  { background:#fff3cd; color:#f39c12; border:1px solid #ffeaa7; }
        .status-confirmed                { background:#d4edda; color:#27ae60; border:1px solid #a3e4b7; }
        .status-cus_confirmed            { background:#d1ecf1; color:#0d6efd; border:1px solid #a0d8e8; }
        .status-manager_confirm_disc_success { background:#e8d5f5; color:#7b2d8b; border:1px solid #c8a0d8; }

        /* ── Table ── */
        .table-responsive { overflow-x:auto; }
        .table { width:100%; border-collapse:collapse; min-width:700px; }
        .table th,
        .table td { padding:.85rem 1rem; text-align:left; border-bottom:1px solid var(--border-gray); vertical-align:top; }
        .table th { font-weight:600; color:var(--text-gray); text-transform:uppercase; font-size:.78rem; background:var(--bg-light); white-space:nowrap; }
        .table td { font-size:.95rem; }

        /* ── Order-level charges grid ── */
        .charges-grid {
            display:grid;
            grid-template-columns: repeat(4, 1fr);
            gap:1.25rem;
            margin-bottom:1.5rem;
        }
        .charge-card {
            background:#fffaf4;
            border:2px solid #edd9bc;
            border-radius:.85rem;
            padding:1.1rem 1.25rem;
            display:flex;
            flex-direction:column;
            gap:.65rem;
            position:relative;
        }
        .charge-card-label {
            font-size:.72rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.6px;
            color:var(--primary-orange);
            margin-bottom:.1rem;
        }
        .charge-input {
            width:100%; padding:.55rem .75rem;
            border:2px solid #edd9bc; border-radius:.5rem;
            font-size:.88rem; font-family:inherit;
            background:#fff; color:var(--dark-gray);
            transition:border-color .18s, box-shadow .18s;
        }
        .charge-input:focus {
            outline:none; border-color:var(--primary-orange);
            box-shadow:0 0 0 3px var(--orange-light);
        }
        .charge-input::placeholder { color:#c8b49a; font-size:.82rem; }
        .charge-input.charge-name  { font-weight:600; }
        .charge-divider { border:none; border-top:1px dashed #e0ccb4; margin:0; }

        /* Save charges bar */
        .save-charges-bar {
            display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;
            margin-top:.5rem; padding:1rem 1.5rem;
            background:#fef6ec; border:1px solid rgba(242,128,24,.2); border-radius:.75rem;
        }
        .save-charges-bar p { color:var(--text-gray); font-size:.88rem; margin:0; }

        /* Multi-value list */
        .multi-value-list { list-style:none; padding:0; margin:0; }
        .multi-value-item { padding:.25rem 0; }
        .multi-value-item:not(:last-child) { border-bottom:1px dashed var(--border-gray); margin-bottom:.25rem; padding-bottom:.5rem; }

        /* Forms */
        .form-group  { margin-bottom:1.5rem; }
        .form-label  { display:block; font-weight:600; margin-bottom:.5rem; color:var(--dark-gray); }
        .form-control {
            width:100%; padding:.75rem 1rem;
            border:2px solid var(--border-gray); border-radius:.75rem;
            font-size:.95rem; transition:all .2s;
        }
        .form-control:focus   { outline:none; border-color:var(--primary-orange); box-shadow:0 0 0 3px var(--orange-light); }
        .form-control:disabled { background:#f5f5f5; cursor:not-allowed; opacity:.6; }
        .form-control-sm { padding:.5rem .75rem; font-size:.9rem; }

        /* Alerts */
        .alert         { padding:1rem 1.5rem; border-radius:.75rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:.75rem; }
        .alert-success { background:var(--success-light); color:#27ae60; border:1px solid #a3e4b7; }
        .alert-danger  { background:var(--danger-light);  color:#e74c3c; border:1px solid #f5c6cb; }
        .alert-warning { background:var(--warning-light); color:#f39c12; border:1px solid #ffeaa7; }
        .alert-info    { background:var(--info-light);    color:#2980b9; border:1px solid #a0d8e8; }
        .alert-purple  { background:#f3e8ff; color:#7b2d8b; border:1px solid #c8a0d8; }

        .no-data   { text-align:center; padding:3rem 2rem; color:var(--text-gray); }
        .no-data i { font-size:3rem; opacity:.3; margin-bottom:1rem; display:block; }

        /* Weight / rate badges */
        .weight-badge { display:inline-block; padding:.25rem .6rem; background:var(--orange-light); color:var(--primary-orange); border-radius:.5rem; font-size:.82rem; font-weight:600; border:1px solid rgba(242,128,24,.2); }
        .weight-badge.weight-missing { background:var(--danger-light); color:#e74c3c; border:1px solid rgba(231,76,60,.2); }
        .rate-badge   { display:inline-block; padding:.25rem .6rem; background:var(--info-light); color:#2980b9; border-radius:.5rem; font-size:.82rem; font-weight:600; border:1px solid rgba(52,152,219,.2); margin-left:.4rem; }
        /* Fixed (customer_items) price badge */
        .rate-badge.rate-badge-fixed { background:rgba(39,174,96,.12); color:#27ae60; border:1px solid rgba(39,174,96,.25); margin-left:0; }

        .weight-cell-wrap { display:flex; align-items:center; flex-wrap:wrap; gap:.35rem; }

        /* ── Price type pill badges used in the order items table ── */
        .price-type-pill {
            display:inline-flex; align-items:center; gap:.3rem;
            padding:.18rem .55rem; border-radius:999px;
            font-size:.72rem; font-weight:700; letter-spacing:.3px;
            vertical-align:middle; margin-left:.35rem;
        }
        .price-type-pill.fixed    { background:rgba(39,174,96,.13); color:#1e9455; border:1px solid rgba(39,174,96,.3); }
        .price-type-pill.standard { background:rgba(52,152,219,.12); color:#2471a3; border:1px solid rgba(52,152,219,.28); }

        /* ── Discount section ── */
        .discount-toggle-group {
            display:flex; gap:1rem; margin-bottom:1.5rem;
            background:var(--bg-light); padding:.5rem; border-radius:.75rem;
        }
        .discount-toggle-btn {
            flex:1; padding:.75rem 1.5rem; border:2px solid transparent;
            background:transparent; border-radius:.5rem; font-weight:600;
            cursor:pointer; transition:all .2s; color:var(--text-gray);
        }
        .discount-toggle-btn:hover { background:#fff; }
        .discount-toggle-btn.active { background:#fff; border-color:var(--primary-orange); color:var(--primary-orange); box-shadow:var(--shadow-sm); }

        .discount-content        { display:none; }
        .discount-content.active { display:block; }

        .discount-input-group { display:flex; align-items:center; gap:.5rem; }
        .discount-input-group .form-control { flex:1; }
        .discount-suffix { font-weight:600; color:var(--text-gray); }

        .item-discount-row {
            display:grid; grid-template-columns:1.2fr 2fr 180px 160px;
            gap:1rem; align-items:center;
            padding:1rem; background:var(--bg-light);
            border-radius:.5rem; margin-bottom:.75rem; border:1px solid var(--border-gray);
        }
        .item-discount-icode { font-weight:700; color:var(--primary-orange); font-size:.95rem; word-break:break-all; }
        .item-discount-desc  { color:var(--dark-gray); font-size:.9rem; font-weight:500; }
        .item-discount-rate  { display:flex; flex-direction:column; gap:.25rem; }
        .item-discount-rate-label { font-size:.72rem; font-weight:600; color:var(--text-gray); text-transform:uppercase; letter-spacing:.4px; }

        /* Rate input variants */
        .rate-editable-input { border-color:#a0d8e8 !important; background:#f0f9ff !important; }
        .rate-editable-input:focus { border-color:var(--primary-orange) !important; background:#fff !important; }
        /* Fixed (customer_items) price input — green tint */
        .rate-editable-input.rate-fixed { border-color:rgba(39,174,96,.5) !important; background:#f0fdf4 !important; }
        .rate-editable-input.rate-fixed:focus { border-color:#27ae60 !important; background:#fff !important; }

        .discount-header-row {
            display:grid; grid-template-columns:1.2fr 2fr 180px 160px;
            gap:1rem; padding:.5rem 1rem; margin-bottom:.25rem;
        }
        .discount-header-row div { font-size:.8rem; font-weight:700; color:var(--text-gray); text-transform:uppercase; letter-spacing:.5px; }

        /* Fixed / Standard inline label pill (discount section) */
        .price-source-label {
            display:inline-flex; align-items:center; gap:.28rem;
            font-size:.7rem; font-weight:700; letter-spacing:.35px;
            text-transform:uppercase; margin-top:.25rem;
        }
        .price-source-label.fixed    { color:#1e9455; }
        .price-source-label.standard { color:#2471a3; }
        .price-source-label.missing  { color:#e74c3c; }

        /* ── Manager Discount Success Notice ── */
        .disc-success-notice {
            display:flex; align-items:flex-start; gap:1rem;
            background:#f3e8ff; border:1px solid #c8a0d8;
            border-radius:.85rem; padding:1.25rem 1.5rem;
            margin-bottom:1.5rem;
        }
        .disc-success-notice-icon {
            font-size:1.8rem; color:#7b2d8b; flex-shrink:0; margin-top:.1rem;
        }
        .disc-success-notice h4 { color:#5b1f6e; font-size:1rem; font-weight:700; margin-bottom:.3rem; }
        .disc-success-notice p  { color:#7b2d8b; font-size:.88rem; margin:0; }

        @media (max-width:1100px) {
            .charges-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width:900px) {
            .item-discount-row, .discount-header-row { grid-template-columns:1fr 1fr 160px 140px; }
        }
        @media (max-width:768px) {
            .main-wrapper { padding:1rem; }
            .page-header  { flex-direction:column; gap:1rem; }
            .info-grid    { grid-template-columns:1fr; }
            .charges-grid { grid-template-columns:1fr 1fr; }
            .discount-toggle-group { flex-direction:column; }
            .item-discount-row, .discount-header-row { grid-template-columns:1fr 1fr; gap:.5rem; }
            .item-discount-desc { grid-column:1 / -1; }
        }
        @media (max-width:500px) {
            .charges-grid { grid-template-columns:1fr; }
            .item-discount-row, .discount-header-row { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<div class="main-wrapper">

    <a href="tire-orders.php" class="btn btn-back">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>

    <?php if (isset($errorMsg)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><span><?php echo $errorMsg; ?></span></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i><span><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></span></div>
    <?php endif; ?>

    <?php if ($orderData): ?>

    <?php
    // ─────────────────────────────────────────────────────────────
    // Determine special status flags used throughout the page
    // ─────────────────────────────────────────────────────────────
    $currentStatus         = $orderData['status'];
    $isManagerDiscSuccess  = ($currentStatus === 'manager_confirm_disc_success');
    ?>

    <div class="page-header">
        <div>
            <h1 class="page-title">Order #<?php echo htmlentities($orderData['order_id']); ?></h1>
            <p class="page-subtitle">Complete order details and items</p>
        </div>
    </div>

    <!-- ── Order Summary ── -->
    <div class="card">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-info-circle"></i> Order Summary</h2></div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Order ID</div>
                    <div class="info-value">#<?php echo htmlentities($orderData['order_id']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Customer Code</div>
                    <div class="info-value"><?php echo htmlentities($orderData['customer_code'] ?: 'N/A'); ?></div>
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
                    <div class="info-label">Payment Rate</div>
                    <div class="info-value"><?php echo $paymentRate > 0 ? number_format($paymentRate, 2) : 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <?php
                        $badgeClass = $currentStatus == 'confirmed'                     ? 'status-confirmed'
                                    : ($currentStatus == 'cus_confirmed'                 ? 'status-cus_confirmed'
                                    : ($currentStatus == 'manager_confirm_disc_success'  ? 'status-manager_confirm_disc_success'
                                    : 'status-pending'));
                        $statusText = ucwords(str_replace('_', ' ', $currentStatus));
                        ?>
                        <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                    </div>
                </div>
                <?php if ($orderData['order_notes']): ?>
                <div class="info-item" style="grid-column:1/-1;">
                    <div class="info-label">Order Notes</div>
                    <div class="info-value" style="font-weight:400;line-height:1.6;"><?php echo nl2br(htmlentities($orderData['order_notes'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Order Items ── -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-boxes"></i> Order Items</h2>
            <!-- Legend -->
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <span class="price-type-pill fixed"><i class="fas fa-lock"></i> Fixed</span>
                <span style="font-size:.78rem;color:var(--text-gray);">= Customer price list</span>
                <span class="price-type-pill standard"><i class="fas fa-calculator"></i> Standard</span>
                <span style="font-size:.78rem;color:var(--text-gray);">= Weight × rate</span>
            </div>
        </div>
        <div class="card-body">
            <?php
            $itemsDisplayQuery = mysqli_query($con, "
                SELECT * FROM tire_order_items
                WHERE order_id = '$orderId'
                ORDER BY item_id
            ");

            $itemsData        = [];
            $hasInvalidWeight = false;
            $allItemsFixed    = true;   // becomes false if ANY icode is Standard (not customer_items)
            $cnt              = 1;
            ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Price / Rate Value</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($itemsDisplayQuery) > 0):
                        while ($item = mysqli_fetch_array($itemsDisplayQuery)):
                            $icodes     = parseCommaSeparatedValue($item['icode']);
                            $quantities = parseCommaSeparatedValue($item['quantity']);

                            if (empty($icodes)) {
                                $icodes     = [$item['icode']];
                                $quantities = [$item['quantity']];
                            }

                            $maxCount   = max(count($icodes), count($quantities));
                            $icodes     = array_pad($icodes,     $maxCount, '');
                            $quantities = array_pad($quantities, $maxCount, '');

                            // ── Build per-icode effective price ──
                            $icodeRateValues = [];
                            $icodePriceSrc   = [];
                            $icodeWeights    = [];

                            foreach ($icodes as $ic) {
                                if (!empty($ic)) {
                                    $src   = null;
                                    $price = getEffectivePrice($con, $ic, $customerId, $paymentRate, $src);
                                    $icodeRateValues[$ic] = $price;
                                    $icodePriceSrc[$ic]   = $src;

                                    $icSafe = mysqli_real_escape_string($con, $ic);
                                    $twqTmp = mysqli_query($con, "SELECT fweight FROM tire_details WHERE icode='$icSafe'");
                                    $twTmp  = mysqli_fetch_array($twqTmp);
                                    $icodeWeights[$ic] = $twTmp ? $twTmp['fweight'] : null;
                                }
                            }

                            $savedRateValue = isset($item['rate_value']) && $item['rate_value'] != '' ? floatval($item['rate_value']) : null;

                            // If any icode in this item row is NOT from customer_items, order is not all-fixed
                            foreach ($icodePriceSrc as $src) {
                                if ($src !== 'customer_items') { $allItemsFixed = false; break; }
                            }

                            $itemsData[] = [
                                'item_id'         => $item['item_id'],
                                'icodes'          => $icodes,
                                'quantities'      => $quantities,
                                'icodeRateValues' => $icodeRateValues,
                                'icodePriceSrc'   => $icodePriceSrc,
                                'savedRateValue'  => $savedRateValue,
                            ];

                            // Render price cell for a single icode
                            $renderPriceCell = function($icode) use ($icodeRateValues, $icodePriceSrc, $icodeWeights, &$hasInvalidWeight) {
                                $fw    = $icodeWeights[$icode] ?? null;
                                $inv   = (empty($fw) || $fw == '0' || $fw == 0);
                                $price = $icodeRateValues[$icode] ?? null;
                                $src   = $icodePriceSrc[$icode]   ?? null;

                                $isFixed = ($src === 'customer_items');

                                if ($inv && !$isFixed) $hasInvalidWeight = true;

                                echo '<div class="weight-cell-wrap">';

                                if ($isFixed) {
                                    // ── Fixed: from customer_items ──
                                    echo '<span class="rate-badge rate-badge-fixed" title="Fixed price from customer price list">'
                                       . '<i class="fas fa-lock"></i> $' . number_format($price, 2) . '</span>'
                                       . '<span class="price-type-pill fixed"><i class="fas fa-lock"></i> Fixed</span>';
                                } else {
                                    // ── Standard: weight × rate ──
                                    $wCls = $inv ? 'weight-missing' : '';
                                    $wDsp = $inv ? 'No weight' : htmlentities($fw) . ' kg';
                                    echo '<span class="weight-badge ' . $wCls . '"><i class="fas fa-weight-hanging"></i> ' . $wDsp . '</span>';
                                    if ($price !== null) {
                                        echo '<span class="rate-badge" title="Standard price: weight × payment rate">'
                                           . '<i class="fas fa-dollar-sign"></i> ' . number_format($price, 2) . '</span>'
                                           . '<span class="price-type-pill standard"><i class="fas fa-calculator"></i> Standard</span>';
                                    }
                                }

                                echo '</div>';
                            };
                    ?>
                    <tr>
                        <td><?php echo $cnt++; ?></td>

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
                                        $icC = mysqli_real_escape_string($con, $icode);
                                        $tdq = mysqli_query($con, "SELECT description FROM tire_details WHERE icode='$icC'");
                                        $td  = mysqli_fetch_array($tdq);
                                    ?>
                                        <li class="multi-value-item"><?php echo $td ? htmlentities($td['description']) : 'N/A'; ?></li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            <?php else:
                                $icC = mysqli_real_escape_string($con, $icodes[0]);
                                $tdq = mysqli_query($con, "SELECT description FROM tire_details WHERE icode='$icC'");
                                $td  = mysqli_fetch_array($tdq);
                                echo $td ? htmlentities($td['description']) : 'N/A';
                            endif; ?>
                        </td>

                        <!-- Price / Rate -->
                        <td>
                            <?php if (count($icodes) > 1): ?>
                                <ul class="multi-value-list">
                                    <?php foreach ($icodes as $icode): if (!empty($icode)): ?>
                                        <li class="multi-value-item"><?php $renderPriceCell($icode); ?></li>
                                    <?php endif; endforeach; ?>
                                </ul>
                            <?php else:
                                $renderPriceCell($icodes[0]);
                            endif; ?>
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
                    </tr>
                    <?php
                        endwhile;
                    else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="no-data"><i class="fas fa-box-open"></i><p>No items found for this order.</p></div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($hasInvalidWeight): ?>
            <div class="alert alert-danger" style="margin-top:1rem;">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>Warning:</strong> Some items have missing or invalid weight data and no customer-specific price is set. Please update the weight information in the tire details, or add the item to the customer's price list.</span>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ── Order-Level Charges ── -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-tags" style="color:var(--primary-orange);"></i> Order Charges
            </h2>
            <span style="font-size:.83rem;color:var(--text-gray);">
                <i class="fas fa-info-circle"></i>
                These charges apply to the <strong>entire order</strong>. Fill in a name and value for each charge you want to include.
            </span>
        </div>
        <div class="card-body">

            <?php
            $hasSavedCharges = false;
            for ($ci = 1; $ci <= 4; $ci++) {
                if (!empty($orderData["charge{$ci}_name"]) || !empty($orderData["charge{$ci}_value"])) {
                    $hasSavedCharges = true; break;
                }
            }
            if ($hasSavedCharges): ?>
            <div class="alert alert-info" style="margin-bottom:1.25rem;">
                <i class="fas fa-check-circle"></i>
                <span>
                    <strong>Saved charges:</strong>
                    <?php
                    $savedParts = [];
                    for ($ci = 1; $ci <= 4; $ci++) {
                        $cn = trim($orderData["charge{$ci}_name"] ?? '');
                        $cv = trim($orderData["charge{$ci}_value"] ?? '');
                        if ($cn !== '' || $cv !== '') {
                            $savedParts[] = '<strong>' . htmlentities($cn ?: '(unnamed)') . '</strong>: ' . htmlentities($cv ?: '—');
                        }
                    }
                    echo implode(' &nbsp;|&nbsp; ', $savedParts);
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="charges-grid">
                    <?php for ($ci = 1; $ci <= 4; $ci++):
                        $cn = htmlentities($orderData["charge{$ci}_name"]  ?? '');
                        $cv = htmlentities($orderData["charge{$ci}_value"] ?? '');
                    ?>
                    <div class="charge-card">
                        <div class="charge-card-label"><i class="fas fa-tag"></i> &nbsp;Charge <?php echo $ci; ?></div>
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--text-gray);display:block;margin-bottom:.3rem;">Name</label>
                            <input type="text" name="charge<?php echo $ci; ?>_name" class="charge-input charge-name" placeholder="e.g. Freight, Insurance…" value="<?php echo $cn; ?>">
                        </div>
                        <hr class="charge-divider">
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--text-gray);display:block;margin-bottom:.3rem;">Amount / Value</label>
                            <input type="text" name="charge<?php echo $ci; ?>_value" class="charge-input" placeholder="e.g. 120.00" value="<?php echo $cv; ?>">
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="save-charges-bar">
                    <p>
                        <i class="fas fa-lightbulb" style="color:var(--primary-orange);"></i>
                        Leave any charge blank if it does not apply to this order. Charges are saved at the order level.
                    </p>
                    <button type="submit" name="save_order_charges" class="btn btn-warning">
                        <i class="fas fa-save"></i> Save Charges
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Apply Discount — hidden when status is manager_confirm_disc_success ── -->
    <?php if (!$isManagerDiscSuccess): ?>
    <div class="card">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-percent"></i> Apply Discount</h2></div>
        <div class="card-body">

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>
                    Choose between applying a uniform discount to all items or setting individual discounts per item.
                    In the <strong>Individual</strong> tab, the <strong>Rate Value</strong> column shows one of two pricing types:<br>
                    <span style="display:inline-flex;align-items:center;gap:.35rem;margin-top:.4rem;">
                        <span class="price-type-pill fixed"><i class="fas fa-lock"></i> Fixed</span>
                        <span style="font-size:.85rem;"> — price comes directly from the customer's price list.</span>
                    </span><br>
                    <span style="display:inline-flex;align-items:center;gap:.35rem;margin-top:.3rem;">
                        <span class="price-type-pill standard"><i class="fas fa-calculator"></i> Standard</span>
                        <span style="font-size:.85rem;"> — price is calculated from weight × payment rate.</span>
                    </span><br>
                    <span style="font-size:.85rem;margin-top:.3rem;display:block;">You can edit any value before applying the discount.</span>
                </span>
            </div>

            <form method="POST" action="" id="discountForm">
                <input type="hidden" name="order_id"      value="<?php echo htmlentities($orderData['order_id']); ?>">
                <input type="hidden" name="discount_type" id="discount_type" value="uniform">

                <div class="discount-toggle-group">
                    <button type="button" class="discount-toggle-btn active" data-type="uniform">
                        <i class="fas fa-equals"></i> Uniform Discount
                    </button>
                    <button type="button" class="discount-toggle-btn" data-type="individual">
                        <i class="fas fa-list"></i> Individual Discounts
                    </button>
                </div>

                <!-- Uniform -->
                <div class="discount-content active" id="uniform-content">
                    <div class="form-group">
                        <label class="form-label" for="uniform_discount">
                            <i class="fas fa-percentage"></i> Discount Percentage (for all items)
                        </label>
                        <?php if (!$allItemsFixed): ?>
                        <div class="alert alert-warning" style="margin-bottom:.85rem;">
                            <i class="fas fa-lock"></i>
                            <span>
                                <strong>Uniform discount is disabled.</strong>
                                One or more items are priced as <span class="price-type-pill standard" style="font-size:.78rem;"><i class="fas fa-calculator"></i> Standard</span> (weight × rate).
                                All items must be <span class="price-type-pill fixed" style="font-size:.78rem;"><i class="fas fa-lock"></i> Fixed</span> (customer price list) to use uniform discounting.
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="discount-input-group">
                            <input type="number" name="uniform_discount" id="uniform_discount"
                                   class="form-control" placeholder="<?php echo $allItemsFixed ? 'Enter discount percentage' : 'Not available — contains Standard priced items'; ?>"
                                   min="0" max="100" step="0.01"
                                   <?php echo $allItemsFixed ? 'required' : 'disabled'; ?>>
                            <span class="discount-suffix">%</span>
                        </div>
                        <small style="color:<?php echo $allItemsFixed ? 'var(--text-gray)' : '#e74c3c'; ?>;margin-top:.5rem;display:block;">
                            <?php if ($allItemsFixed): ?>
                                This discount will be applied to all items in the order.
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle"></i>
                                Set all items to Fixed pricing first, or use <strong>Individual Discounts</strong> instead.
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <!-- Individual -->
                <div class="discount-content" id="individual-content">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-list-ul"></i> Set Discount for Each Item</label>

                        <?php if (!empty($itemsData)): ?>
                        <div class="discount-header-row">
                            <div>Item Code</div>
                            <div>Description</div>
                            <div>Rate Value <i class="fas fa-pencil-alt" style="font-size:.7rem;opacity:.6;"></i></div>
                            <div>Discount %</div>
                        </div>

                        <?php foreach ($itemsData as $itemData):
                            $firstIcode    = $itemData['icodes'][0];
                            $hasMoreIcodes = count($itemData['icodes']) > 1;
                            $ficC = mysqli_real_escape_string($con, $firstIcode);
                            $dq   = mysqli_query($con, "SELECT description FROM tire_details WHERE icode='$ficC'");
                            $dr   = mysqli_fetch_array($dq);
                            $desc = $dr ? htmlentities($dr['description']) : '<span style="color:#e74c3c;">No description</span>';

                            // Effective price for first icode
                            $computedPrice = $itemData['icodeRateValues'][$firstIcode] ?? null;
                            $priceSource   = $itemData['icodePriceSrc'][$firstIcode]   ?? null;

                            // savedRateValue (user-overridden) takes priority over computed price
                            $displayRate = $itemData['savedRateValue'] !== null
                                         ? number_format($itemData['savedRateValue'], 2, '.', '')
                                         : ($computedPrice !== null ? number_format($computedPrice, 2, '.', '') : '');

                            $rateMissing = ($displayRate === '');

                            // Fixed = from customer_items AND not yet overridden by a saved rate
                            $isFixed         = ($priceSource === 'customer_items') && ($itemData['savedRateValue'] === null);
                            $inputExtraClass = $isFixed ? 'rate-fixed' : '';
                            $inputTitle      = $isFixed
                                             ? 'Fixed price from customer price list — editable'
                                             : 'Standard price: weight × payment rate — editable';
                        ?>
                        <div class="item-discount-row">
                            <div class="item-discount-icode">
                                <?php echo htmlentities($firstIcode); ?>
                                <?php if ($hasMoreIcodes): ?>
                                    <span style="color:var(--text-gray);font-size:.8rem;font-weight:400;">(+<?php echo count($itemData['icodes'])-1; ?> more)</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-discount-desc"><?php echo $desc; ?></div>
                            <div class="item-discount-rate">
                                <div class="discount-input-group">
                                    <span style="color:var(--text-gray);font-size:.9rem;flex-shrink:0;">$</span>
                                    <input type="number"
                                           name="item_rate_value[<?php echo $itemData['item_id']; ?>]"
                                           class="form-control form-control-sm rate-editable-input <?php echo $inputExtraClass; ?>"
                                           placeholder="0.00" min="0" step="0.01"
                                           value="<?php echo htmlentities($displayRate); ?>"
                                           title="<?php echo $inputTitle; ?>">
                                </div>
                                <?php if ($isFixed): ?>
                                    <span class="price-source-label fixed">
                                        <i class="fas fa-lock"></i> Fixed Price
                                    </span>
                                <?php elseif ($rateMissing): ?>
                                    <span class="price-source-label missing">
                                        <i class="fas fa-exclamation-triangle"></i> Enter manually
                                    </span>
                                <?php elseif ($hasMoreIcodes): ?>
                                    <span class="price-source-label standard">
                                        <i class="fas fa-calculator"></i> Standard (1st item)
                                    </span>
                                <?php else: ?>
                                    <span class="price-source-label standard">
                                        <i class="fas fa-calculator"></i> Standard
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="discount-input-group">
                                <input type="number"
                                       name="item_discount[<?php echo $itemData['item_id']; ?>]"
                                       class="form-control form-control-sm"
                                       placeholder="0.00" min="0" max="100" step="0.01">
                                <span class="discount-suffix">%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php else: ?>
                            <p style="color:var(--text-gray);">No items available for discount.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" name="apply_discount" class="btn btn-info">
                    <i class="fas fa-check-circle"></i> Apply &amp; Continue
                </button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- ── Discount Approved Notice (replaces the Apply Discount card) ── -->
    <div class="disc-success-notice">
        <div class="disc-success-notice-icon"><i class="fas fa-badge-check fa-fw"></i></div>
        <div>
            <h4><i class="fas fa-check-circle"></i> &nbsp;Discount Approved by Manager</h4>
            <p>
                The discount for this order has already been reviewed and confirmed by the manager
                (<strong>manager_confirm_disc_success</strong>). The Apply Discount section is no longer available.
                You may now update the order status to <strong>ACM Confirm</strong> below.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Update Status ── -->
    <div class="card">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-edit"></i> Update Order Status</h2></div>
        <div class="card-body">

            <?php
            $isRestricted   = ($currentStatus === 'cus_confirmed');
            $isCusPiConfirm = ($currentStatus === 'cus_pi_confirm');
            // When manager_confirm_disc_success: only acm_confirm is allowed — no other blocking rules apply
            $statusBlocked  = !$isManagerDiscSuccess && (!$allItemsFixed || $hasInvalidWeight);
            ?>

            <?php if ($isManagerDiscSuccess): ?>
            <!-- Special notice for manager_confirm_disc_success -->
            <div class="alert alert-purple" style="margin-bottom:1.25rem;">
                <i class="fas fa-info-circle"></i>
                <span>
                    The order status is <strong>Manager Confirm Disc Success</strong>.
                    The only available action is to update the status to <strong>ACM Confirm</strong>.
                </span>
            </div>
            <?php elseif (!$allItemsFixed): ?>
            <div class="alert alert-warning" style="margin-bottom:1.25rem;">
                <i class="fas fa-lock"></i>
                <span>
                    <strong>Status update is disabled.</strong>
                    One or more items are priced as <span class="price-type-pill standard" style="font-size:.78rem;"><i class="fas fa-calculator"></i> Standard</span> (weight × rate).
                    All items must be <span class="price-type-pill fixed" style="font-size:.78rem;"><i class="fas fa-lock"></i> Fixed</span> (customer price list) before the order status can be updated.
                </span>
            </div>
            <?php endif; ?>

            <form method="POST" action="">

                <?php if ($isManagerDiscSuccess): ?>
                <!-- When manager_confirm_disc_success: fixed hidden input, no dropdown -->
                <input type="hidden" name="status" value="acm_confirm">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div style="padding:.75rem 1rem;background:var(--bg-light);border:2px solid var(--border-gray);border-radius:.75rem;font-weight:600;color:#7b2d8b;">
                        <i class="fas fa-arrow-right"></i> &nbsp;ACM Confirm
                        <span style="font-size:.82rem;color:var(--text-gray);font-weight:400;margin-left:.5rem;">(only available action)</span>
                    </div>
                    <p style="margin-top:.5rem;color:var(--text-gray);font-size:.9rem;">
                        <i class="fas fa-info-circle"></i>
                        Clicking <strong>Update Status</strong> will set this order to <strong>ACM Confirm</strong>.
                    </p>
                </div>

                <?php else: ?>
                <div class="form-group">
                    <label class="form-label" for="status">Change Status</label>
                    <select name="status" id="status" class="form-control" required
                            <?php echo ($isRestricted || $statusBlocked) ? 'disabled' : ''; ?>>
                        <option value="">-- Select Status --</option>
                        <option value="acm_confirm"    <?php echo $currentStatus=='acm_confirm'    ? 'selected' : ''; ?>>ACM Confirmed</option>
                        <option value="confirmed"      <?php echo $currentStatus=='confirmed'      ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $statusBlocked) ? 'disabled' : ''; ?>>Confirmed</option>
                        <option value="cus_confirmed"  <?php echo $currentStatus=='cus_confirmed'  ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $statusBlocked) ? 'disabled' : ''; ?>>Customer Confirmed</option>
                        <option value="cus_pi_confirm" <?php echo $currentStatus=='cus_pi_confirm' ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $statusBlocked) ? 'disabled' : ''; ?>>Customer PI Confirmed</option>
                        <option value="Share_planning" <?php echo $currentStatus=='Share_planning' ? 'selected' : ''; ?>
                                <?php echo ($isRestricted || $statusBlocked) ? 'disabled' : ''; ?>>Share Planning</option>
                    </select>

                    <?php if ($isRestricted): ?>
                    <input type="hidden" name="status" value="acm_confirm">
                    <p style="margin-top:.5rem;color:var(--text-gray);font-size:.9rem;">
                        <i class="fas fa-info-circle"></i> When status is "Customer Confirmed", only "ACM Confirmed" can be selected.
                    </p>
                    <?php endif; ?>

                    <?php if ($isCusPiConfirm): ?>
                    <input type="hidden" name="status" value="Share_planning">
                    <p style="margin-top:.5rem;color:var(--text-gray);font-size:.9rem;">
                        <i class="fas fa-info-circle"></i> When status is "Customer PI Confirmed", it will be automatically set to "Share Planning".
                    </p>
                    <?php endif; ?>

                    <?php if ($hasInvalidWeight && $allItemsFixed): ?>
                    <p style="margin-top:.5rem;color:#e74c3c;font-size:.9rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Status update is disabled</strong> because some items have missing or invalid weight data and no customer-specific price is set. Please update the weight information or add the item to the customer's price list.
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <button type="submit" name="update_status" class="btn btn-success" <?php echo $statusBlocked ? 'disabled' : ''; ?>>
                    <i class="fas fa-check"></i> Update Status
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

</div><!-- /.main-wrapper -->

<script>
    // Discount toggle — only active when the discount section is rendered
    document.querySelectorAll('.discount-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const type = this.getAttribute('data-type');
            document.querySelectorAll('.discount-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.discount-content').forEach(c => c.classList.remove('active'));
            document.getElementById(type + '-content').classList.add('active');
            document.getElementById('discount_type').value = type;
            if (type === 'uniform') {
                document.getElementById('uniform_discount').setAttribute('required', 'required');
                document.querySelectorAll('input[name^="item_discount"]').forEach(i => i.removeAttribute('required'));
            } else {
                document.getElementById('uniform_discount').removeAttribute('required');
            }
        });
    });

    // Discount form validation — only runs when the form exists in the DOM
    const discountForm = document.getElementById('discountForm');
    if (discountForm) {
        discountForm.addEventListener('submit', function (e) {
            const discountType = document.getElementById('discount_type').value;
            if (discountType === 'uniform') {
                const input = document.getElementById('uniform_discount');
                if (input.disabled) {
                    e.preventDefault();
                    alert('Uniform discount is not available because one or more items are Standard priced. Please use Individual Discounts instead.');
                    return false;
                }
                const val = input.value;
                if (!val || val === '' || parseFloat(val) < 0) {
                    e.preventDefault();
                    alert('Please enter a valid uniform discount percentage.');
                    return false;
                }
            } else if (discountType === 'individual') {
                const inputs = document.querySelectorAll('input[name^="item_discount"]');
                let hasValue = false;
                inputs.forEach(inp => { if (inp.value && inp.value !== '') hasValue = true; });
                if (!hasValue) {
                    e.preventDefault();
                    alert('Please enter at least one item discount percentage.');
                    return false;
                }
            }
        });
    }
</script>

</body>
</html>
<?php } ?>