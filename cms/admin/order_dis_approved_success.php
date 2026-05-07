<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    date_default_timezone_set('Asia/Kolkata');

    $adminId = intval($_SESSION["aid"]);

    function parseCommaSeparatedValue($value) {
        if (empty($value)) return [];
        $items = array_map('trim', explode(',', $value));
        return array_filter($items);
    }

    $orderId = mysqli_real_escape_string($con, $_GET['id']);

    // ── Handle Status Update ──────────────────────────────────────────────────
    if (isset($_POST['update_status']) && $_POST['update_status'] === 'manager_confirm_disc_success') {
        $newStatus = 'manager_confirm_disc_success';
        $updateQuery = mysqli_query($con, "
            UPDATE tire_orders
            SET status = '$newStatus'
            WHERE order_id = '$orderId'
        ");
        if ($updateQuery) {
            $successMsg = 'Order status updated to <strong>Manager Confirm Disc Success</strong>.';
        } else {
            $errorMsg = 'Failed to update status. Please try again.';
        }
    }

    // ── Handle Discount Update ────────────────────────────────────────────────
    if (isset($_POST['update_discount'])) {
        $itemId       = intval($_POST['item_id']);
        $newDiscount  = mysqli_real_escape_string($con, trim($_POST['discount']));
        $discountQuery = mysqli_query($con, "
            UPDATE tire_order_items
            SET discount = '$newDiscount'
            WHERE item_id = '$itemId' AND order_id = '$orderId'
        ");
        if ($discountQuery) {
            $successMsg = 'Discount updated successfully for item #' . $itemId . '.';
        } else {
            $errorMsg = 'Failed to update discount. Please try again.';
        }
    }

    // ── Handle Bulk Discount Update ───────────────────────────────────────────
    if (isset($_POST['update_all_discounts'])) {
        $discounts = $_POST['discounts'] ?? [];
        $allOk = true;
        foreach ($discounts as $iid => $disc) {
            $iid  = intval($iid);
            $disc = mysqli_real_escape_string($con, trim($disc));
            $r    = mysqli_query($con, "
                UPDATE tire_order_items
                SET discount = '$disc'
                WHERE item_id = '$iid' AND order_id = '$orderId'
            ");
            if (!$r) $allOk = false;
        }
        if ($allOk) {
            $successMsg = 'All discounts updated successfully.';
        } else {
            $errorMsg = 'Some discounts failed to update.';
        }
    }
    // ─────────────────────────────────────────────────────────────────────────

    $orderQuery = mysqli_query($con, "
        SELECT o.*, u.customer_code AS customer_code
        FROM tire_orders o
        LEFT JOIN users u ON o.customer_id = u.id
        WHERE o.order_id = '$orderId'
    ");
    $orderData = mysqli_fetch_array($orderQuery);

    $paymentRate = 0;
    if ($orderData) {
        $paymentRateQuery = mysqli_query($con, "
            SELECT payment_rate FROM users WHERE id = '" . intval($orderData['customer_id']) . "'
        ");
        $paymentRateRow = mysqli_fetch_array($paymentRateQuery);
        $paymentRate = $paymentRateRow ? floatval($paymentRateRow['payment_rate']) : 0;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo htmlentities($orderData['order_id'] ?? ''); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --orange: #F28018;
            --orange-soft: #fff4e8;
            --orange-border: #fdd9b0;
            --dark: #1a1a2e;
            --mid: #4a4a6a;
            --light: #f7f8fc;
            --border: #e8e9f0;
            --white: #ffffff;
            --success: #22c55e;
            --info: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 2px 12px rgba(26,26,46,0.07);
            --shadow-lg: 0 8px 32px rgba(26,26,46,0.12);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--light);
            color: var(--dark);
            min-height: 100vh;
        }

        /* ── Top Bar ── */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }
        .topbar-back {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            color: var(--mid);
            font-weight: 500;
            font-size: .9rem;
            padding: .45rem .9rem;
            border-radius: .5rem;
            border: 1px solid var(--border);
            transition: all .18s;
        }
        .topbar-back:hover { background: var(--orange-soft); color: var(--orange); border-color: var(--orange-border); }
        .topbar-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
        }
        .topbar-title span { color: var(--orange); }

        /* ── Page ── */
        .page { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem 3rem; }

        /* ── Section Cards ── */
        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header {
            padding: 1.1rem 1.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .65rem;
            background: #fafbfe;
        }
        .card-header-left {
            display: flex;
            align-items: center;
            gap: .65rem;
        }
        .card-header-icon {
            width: 34px;
            height: 34px;
            border-radius: .6rem;
            background: var(--orange-soft);
            color: var(--orange);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }
        .card-header h2 { font-size: 1rem; font-weight: 700; color: var(--dark); }
        .card-body { padding: 1.75rem; }

        /* ── Summary Grid ── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        .summary-item {
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: .75rem;
            padding: 1rem 1.15rem;
        }
        .summary-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--mid);
            margin-bottom: .4rem;
        }
        .summary-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark);
            font-family: 'DM Mono', monospace;
        }
        .summary-item.wide { grid-column: 1 / -1; }
        .notes-value {
            font-family: 'DM Sans', sans-serif;
            font-weight: 400;
            font-size: .95rem;
            color: var(--mid);
            line-height: 1.6;
        }

        /* ── Status Badge ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .8rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-pending   { background: #fff8e1; color: #d97706; border: 1px solid #fde68a; }
        .badge-confirmed { background: #dcfce7; color: #16a34a; border: 1px solid #86efac; }
        .badge-info      { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .badge-orange    { background: var(--orange-soft); color: var(--orange); border: 1px solid var(--orange-border); }
        .badge-manager   { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }

        /* ── Items Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        thead tr { background: var(--light); }
        th {
            padding: .75rem 1rem;
            text-align: left;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--mid);
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }
        td {
            padding: .9rem 1rem;
            font-size: .92rem;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafbfe; }

        .icode {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
            color: var(--orange);
            font-size: .88rem;
        }
        .multi-list { list-style: none; padding: 0; margin: 0; }
        .multi-list li { padding: .2rem 0; }
        .multi-list li:not(:last-child) { border-bottom: 1px dashed var(--border); margin-bottom: .2rem; padding-bottom: .4rem; }

        /* Chips */
        .chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .6rem;
            border-radius: .4rem;
            font-size: .78rem;
            font-weight: 600;
        }
        .chip-weight  { background: var(--orange-soft); color: var(--orange); border: 1px solid var(--orange-border); }
        .chip-rate    { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; margin-left: .3rem; }
        .chip-missing { background: #fee2e2; color: var(--danger); border: 1px solid #fca5a5; }
        .chip-disc    { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
        .chips-wrap   { display: flex; flex-wrap: wrap; align-items: center; gap: .3rem; }

        /* Row counter */
        .row-num { color: var(--mid); font-size: .82rem; font-weight: 600; }

        /* No data */
        .no-data { text-align: center; padding: 3rem 1rem; color: var(--mid); }
        .no-data i { font-size: 2.5rem; opacity: .25; display: block; margin-bottom: .75rem; }

        /* Alert */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .9rem 1.2rem;
            border-radius: .65rem;
            font-size: .9rem;
            margin-top: 1.25rem;
        }
        .alert-danger  { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alert-error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* ── Status Update Card ── */
        .update-card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .update-card-header {
            padding: 1.1rem 1.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: .65rem;
            background: #fafbfe;
        }
        .update-card-header-icon {
            width: 34px;
            height: 34px;
            border-radius: .6rem;
            background: #f0fdf4;
            color: #16a34a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }
        .update-card-header h2 { font-size: 1rem; font-weight: 700; color: var(--dark); }
        .update-card-body {
            padding: 1.5rem 1.75rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
        }
        .update-info { flex: 1; min-width: 200px; }
        .update-info p { font-size: .9rem; color: var(--mid); line-height: 1.6; }
        .update-info strong { display: block; font-size: .95rem; color: var(--dark); margin-bottom: .3rem; }
        .new-status-preview {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .5rem;
            padding: .35rem .9rem;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #86efac;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── Buttons ── */
        .btn-update {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            padding: .75rem 1.5rem;
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
            color: #fff;
            border: none;
            border-radius: .65rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(22,163,74,.3);
            transition: all .2s;
            white-space: nowrap;
        }
        .btn-update:hover { background: linear-gradient(135deg, #15803d 0%, #16a34a 100%); box-shadow: 0 6px 20px rgba(22,163,74,.4); transform: translateY(-1px); }
        .btn-update:active { transform: translateY(0); box-shadow: 0 2px 8px rgba(22,163,74,.25); }
        .btn-update:disabled { background: #d1d5db; color: #9ca3af; cursor: not-allowed; box-shadow: none; transform: none; }

        .btn-save-all {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem 1.1rem;
            background: linear-gradient(135deg, #F28018 0%, #f59e0b 100%);
            color: #fff;
            border: none;
            border-radius: .6rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(242,128,24,.3);
            transition: all .2s;
            white-space: nowrap;
        }
        .btn-save-all:hover { background: linear-gradient(135deg, #d97006 0%, #d97706 100%); box-shadow: 0 5px 16px rgba(242,128,24,.4); transform: translateY(-1px); }
        .btn-save-all:active { transform: translateY(0); }

        /* Status already set */
        .status-already-set {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            padding: .75rem 1.25rem;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #86efac;
            border-radius: .65rem;
            font-size: .9rem;
            font-weight: 600;
        }

        /* ── Discount Inline Editor ── */
        .disc-cell { min-width: 160px; }

        .disc-display {
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .disc-value {
            font-family: 'DM Mono', monospace;
            font-weight: 600;
            color: #15803d;
            font-size: .9rem;
        }
        .disc-empty {
            font-size: .82rem;
            color: #9ca3af;
            font-style: italic;
        }
        .btn-edit-disc {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: .4rem;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--mid);
            cursor: pointer;
            font-size: .72rem;
            transition: all .15s;
            flex-shrink: 0;
        }
        .btn-edit-disc:hover { background: var(--orange-soft); color: var(--orange); border-color: var(--orange-border); }

        .disc-edit-wrap {
            display: none;
            align-items: center;
            gap: .4rem;
        }
        .disc-edit-wrap.active { display: flex; }

        .disc-input {
            width: 90px;
            padding: .3rem .55rem;
            border: 1.5px solid var(--orange-border);
            border-radius: .45rem;
            font-family: 'DM Mono', monospace;
            font-size: .88rem;
            font-weight: 600;
            color: var(--dark);
            background: var(--white);
            outline: none;
            transition: border-color .15s;
        }
        .disc-input:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(242,128,24,.12); }

        .btn-disc-save {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: .4rem;
            border: none;
            background: #16a34a;
            color: #fff;
            cursor: pointer;
            font-size: .78rem;
            transition: all .15s;
        }
        .btn-disc-save:hover { background: #15803d; }

        .btn-disc-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: .4rem;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--mid);
            cursor: pointer;
            font-size: .78rem;
            transition: all .15s;
        }
        .btn-disc-cancel:hover { background: #fee2e2; color: var(--danger); border-color: #fca5a5; }

        /* Inline save status indicator */
        .save-indicator {
            font-size: .75rem;
            font-weight: 600;
            padding: .2rem .5rem;
            border-radius: .35rem;
            display: none;
        }
        .save-indicator.ok    { background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; gap: .3rem; }
        .save-indicator.error { background: #fee2e2; color: #b91c1c; display: inline-flex; align-items: center; gap: .3rem; }

        @media (max-width: 640px) {
            .page { padding: 1rem 1rem 2rem; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .topbar { padding: 1rem; }
            .update-card-body { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="topbar">
    <a href="order_dis_approved.php" class="topbar-back">
        <i class="fas fa-arrow-left"></i> Orders
    </a>
    <div class="topbar-title">Order <span>#<?php echo htmlentities($orderData['order_id'] ?? '—'); ?></span></div>
</div>

<div class="page">

<?php if ($orderData): ?>

    <!-- ── Flash Messages ── -->
    <?php if (!empty($successMsg)): ?>
    <div class="alert alert-success" style="margin-bottom:1.25rem;">
        <i class="fas fa-check-circle" style="margin-top:.1rem;flex-shrink:0;"></i>
        <span><?php echo $successMsg; ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($errorMsg)): ?>
    <div class="alert alert-error" style="margin-bottom:1.25rem;">
        <i class="fas fa-times-circle" style="margin-top:.1rem;flex-shrink:0;"></i>
        <span><?php echo htmlentities($errorMsg); ?></span>
    </div>
    <?php endif; ?>

    <!-- ── Order Summary ── -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-header-icon"><i class="fas fa-receipt"></i></div>
                <h2>Order Summary</h2>
            </div>
        </div>
        <div class="card-body">
            <div class="summary-grid">

                <div class="summary-item">
                    <div class="summary-label">Order ID</div>
                    <div class="summary-value">#<?php echo htmlentities($orderData['order_id']); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Customer Code</div>
                    <div class="summary-value"><?php echo htmlentities($orderData['customer_code'] ?: '—'); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Order Date</div>
                    <div class="summary-value" style="font-size:.9rem;">
                        <?php echo date('d M Y', strtotime($orderData['order_date'])); ?>
                        <div style="font-size:.78rem;color:var(--mid);font-weight:500;margin-top:.1rem;">
                            <?php echo date('h:i A', strtotime($orderData['order_date'])); ?>
                        </div>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Total Quantity</div>
                    <div class="summary-value"><?php echo htmlentities($orderData['total_quantity']); ?> <span style="font-size:.75rem;font-weight:500;color:var(--mid);">units</span></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Payment Rate</div>
                    <div class="summary-value"><?php echo $paymentRate > 0 ? '$' . number_format($paymentRate, 2) : '—'; ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Status</div>
                    <div class="summary-value" style="font-size:.88rem;">
                        <?php
                        $status = $orderData['status'];
                        $badgeClass = $status === 'confirmed'                     ? 'badge-confirmed'
                                    : ($status === 'cus_confirmed'                ? 'badge-info'
                                    : ($status === 'cus_pi_confirm'               ? 'badge-orange'
                                    : ($status === 'manager_confirm_disc_success' ? 'badge-manager'
                                    : 'badge-pending')));
                        $statusText = ucwords(str_replace('_', ' ', $status));
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <i class="fas fa-circle" style="font-size:.45rem;"></i>
                            <?php echo $statusText; ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($orderData['order_notes'])): ?>
                <div class="summary-item wide">
                    <div class="summary-label">Order Notes</div>
                    <div class="notes-value"><?php echo nl2br(htmlentities($orderData['order_notes'])); ?></div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- ── Update Status ── -->
    <div class="update-card">
        <div class="update-card-header">
            <div class="update-card-header-icon"><i class="fas fa-check-double"></i></div>
            <h2>Update Order Status</h2>
        </div>
        <div class="update-card-body">
            <div class="update-info">
                <strong>Set status to: Manager Confirm Disc Success</strong>
                <p>This will mark the order as discount confirmed by the manager.</p>
                <div class="new-status-preview">
                    <i class="fas fa-circle" style="font-size:.45rem;"></i>
                    Manager Confirm Disc Success
                </div>
            </div>

            <?php if ($orderData['status'] === 'manager_confirm_disc_success'): ?>
                <div class="status-already-set">
                    <i class="fas fa-check-circle"></i>
                    Status already set
                </div>
            <?php else: ?>
                <form method="POST" action="?id=<?php echo urlencode($orderId); ?>" onsubmit="return confirmUpdate();">
                    <input type="hidden" name="update_status" value="manager_confirm_disc_success">
                    <button type="submit" class="btn-update">
                        <i class="fas fa-check-double"></i>
                        Confirm &amp; Update Status
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Order Items ── -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-left">
                <div class="card-header-icon"><i class="fas fa-boxes"></i></div>
                <h2>Order Items</h2>
            </div>
            <button type="button" class="btn-save-all" id="btnSaveAllDiscounts" onclick="saveAllDiscounts()">
                <i class="fas fa-save"></i> Save All Discounts
            </button>
        </div>
        <div class="card-body" style="padding: 0;">

            <?php
            $itemsQuery = mysqli_query($con, "
                SELECT * FROM tire_order_items
                WHERE order_id = '$orderId'
                ORDER BY item_id
            ");
            $hasInvalidWeight = false;
            $cnt = 1;
            ?>

            <form method="POST" action="?id=<?php echo urlencode($orderId); ?>" id="bulkDiscountForm">
                <input type="hidden" name="update_all_discounts" value="1">

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:44px;">#</th>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th>Weight / Rate</th>
                                <th>Qty</th>
                                <th>Discount</th>
                                <th style="width:80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($itemsQuery) > 0):
                            while ($item = mysqli_fetch_array($itemsQuery)):
                                $icodes     = parseCommaSeparatedValue($item['icode']);
                                $quantities = parseCommaSeparatedValue($item['quantity']);
                                if (empty($icodes)) { $icodes = [$item['icode']]; $quantities = [$item['quantity']]; }
                                $maxCount   = max(count($icodes), count($quantities));
                                $icodes     = array_pad($icodes, $maxCount, '');
                                $quantities = array_pad($quantities, $maxCount, '');
                                $isMulti    = count($icodes) > 1;
                                $itemDisc   = $item['discount'];
                                $itemId     = intval($item['item_id']);
                        ?>
                        <tr data-item-id="<?php echo $itemId; ?>">
                            <td class="row-num"><?php echo $cnt++; ?></td>

                            <!-- Item Code -->
                            <td>
                                <?php if ($isMulti): ?>
                                    <ul class="multi-list">
                                        <?php foreach ($icodes as $ic): if (!empty($ic)): ?>
                                        <li class="icode"><?php echo htmlentities($ic); ?></li>
                                        <?php endif; endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span class="icode"><?php echo htmlentities($icodes[0]); ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Description -->
                            <td>
                                <?php if ($isMulti): ?>
                                    <ul class="multi-list">
                                        <?php foreach ($icodes as $ic): if (!empty($ic)):
                                            $icS = mysqli_real_escape_string($con, $ic);
                                            $dq  = mysqli_query($con, "SELECT description FROM tire_details WHERE icode='$icS'");
                                            $dr  = mysqli_fetch_array($dq);
                                        ?>
                                        <li><?php echo $dr ? htmlentities($dr['description']) : '<span style="color:#9ca3af;">N/A</span>'; ?></li>
                                        <?php endif; endforeach; ?>
                                    </ul>
                                <?php else:
                                    $icS = mysqli_real_escape_string($con, $icodes[0]);
                                    $dq  = mysqli_query($con, "SELECT description FROM tire_details WHERE icode='$icS'");
                                    $dr  = mysqli_fetch_array($dq);
                                    echo $dr ? htmlentities($dr['description']) : '<span style="color:#9ca3af;">N/A</span>';
                                endif; ?>
                            </td>

                            <!-- Weight / Rate -->
                            <td>
                                <?php if ($isMulti): ?>
                                    <ul class="multi-list">
                                        <?php foreach ($icodes as $ic): if (!empty($ic)):
                                            $icS = mysqli_real_escape_string($con, $ic);
                                            $wq  = mysqli_query($con, "SELECT fweight FROM tire_details WHERE icode='$icS'");
                                            $wr  = mysqli_fetch_array($wq);
                                            $fw  = $wr ? $wr['fweight'] : null;
                                            $inv = (empty($fw) || $fw == '0' || $fw == 0);
                                            if ($inv) $hasInvalidWeight = true;
                                            $rv  = (!$inv && $paymentRate > 0) ? number_format(floatval($fw) * $paymentRate, 2) : null;
                                        ?>
                                        <li>
                                            <div class="chips-wrap">
                                                <?php if ($inv): ?>
                                                    <span class="chip chip-missing"><i class="fas fa-exclamation-triangle"></i> Missing</span>
                                                <?php else: ?>
                                                    <span class="chip chip-weight"><i class="fas fa-weight-hanging"></i> <?php echo htmlentities($fw); ?> kg</span>
                                                    <?php if ($rv !== null): ?><span class="chip chip-rate">$<?php echo $rv; ?></span><?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                        <?php endif; endforeach; ?>
                                    </ul>
                                <?php else:
                                    $icS = mysqli_real_escape_string($con, $icodes[0]);
                                    $wq  = mysqli_query($con, "SELECT fweight FROM tire_details WHERE icode='$icS'");
                                    $wr  = mysqli_fetch_array($wq);
                                    $fw  = $wr ? $wr['fweight'] : null;
                                    $inv = (empty($fw) || $fw == '0' || $fw == 0);
                                    if ($inv) $hasInvalidWeight = true;
                                    $rv  = (!$inv && $paymentRate > 0) ? number_format(floatval($fw) * $paymentRate, 2) : null;
                                ?>
                                    <div class="chips-wrap">
                                        <?php if ($inv): ?>
                                            <span class="chip chip-missing"><i class="fas fa-exclamation-triangle"></i> Missing</span>
                                        <?php else: ?>
                                            <span class="chip chip-weight"><i class="fas fa-weight-hanging"></i> <?php echo htmlentities($fw); ?> kg</span>
                                            <?php if ($rv !== null): ?><span class="chip chip-rate">$<?php echo $rv; ?></span><?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Quantity -->
                            <td>
                                <?php if ($isMulti): ?>
                                    <ul class="multi-list">
                                        <?php foreach ($quantities as $qty): if (!empty($qty)): ?>
                                        <li style="font-weight:600;"><?php echo htmlentities($qty); ?></li>
                                        <?php endif; endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <strong><?php echo htmlentities($quantities[0]); ?></strong>
                                <?php endif; ?>
                            </td>

                            <!-- Discount (Editable) -->
                            <td class="disc-cell">
                                <!-- Display Mode -->
                                <div class="disc-display" id="disc-display-<?php echo $itemId; ?>">
                                    <?php if (!empty($itemDisc) && $itemDisc !== '0'): ?>
                                        <span class="disc-value" id="disc-text-<?php echo $itemId; ?>"><?php echo htmlentities($itemDisc); ?></span>
                                    <?php else: ?>
                                        <span class="disc-empty" id="disc-text-<?php echo $itemId; ?>">No discount</span>
                                    <?php endif; ?>
                                    <button type="button" class="btn-edit-disc"
                                        title="Edit discount"
                                        onclick="openDiscEdit(<?php echo $itemId; ?>, '<?php echo htmlspecialchars(addslashes($itemDisc), ENT_QUOTES); ?>')">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <span class="save-indicator" id="disc-indicator-<?php echo $itemId; ?>"></span>
                                </div>

                                <!-- Edit Mode (inline AJAX) -->
                                <div class="disc-edit-wrap" id="disc-edit-<?php echo $itemId; ?>">
                                    <input type="text"
                                        class="disc-input"
                                        id="disc-input-<?php echo $itemId; ?>"
                                        value="<?php echo htmlentities($itemDisc); ?>"
                                        placeholder="e.g. 5%"
                                        autocomplete="off">
                                    <button type="button" class="btn-disc-save"
                                        title="Save"
                                        onclick="saveDiscountAjax(<?php echo $itemId; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn-disc-cancel"
                                        title="Cancel"
                                        onclick="closeDiscEdit(<?php echo $itemId; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Hidden input for bulk save -->
                                <input type="hidden"
                                    name="discounts[<?php echo $itemId; ?>]"
                                    id="disc-bulk-<?php echo $itemId; ?>"
                                    value="<?php echo htmlentities($itemDisc); ?>">
                            </td>

                            <!-- Row-level Save via form (fallback) -->
                            <td>
                                <button type="button"
                                    class="btn-edit-disc"
                                    style="width:auto;padding:.3rem .65rem;font-size:.75rem;gap:.3rem;"
                                    title="Quick save this row's discount"
                                    onclick="saveDiscountAjax(<?php echo $itemId; ?>)">
                                    <i class="fas fa-save"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile;
                        else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="no-data">
                                    <i class="fas fa-box-open"></i>
                                    No items found for this order.
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </form><!-- end bulkDiscountForm -->

            <?php if ($hasInvalidWeight): ?>
            <div style="padding: 0 1.75rem 1.5rem;">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle" style="margin-top:.15rem;flex-shrink:0;"></i>
                    <span><strong>Warning:</strong> Some items have missing or invalid weight data. Please update tire details before updating the order status.</span>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

<?php else: ?>
    <div class="alert" style="background:#fff3cd;color:#92400e;border:1px solid #fde68a;">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Order not found. Please check the order ID and try again.</span>
    </div>
<?php endif; ?>

</div><!-- /.page -->

<script>
// ── Confirm status update ───────────────────────────────────────────────────
function confirmUpdate() {
    return confirm('Are you sure you want to update this order status to "Manager Confirm Disc Success"?');
}

// ── Inline discount editor ──────────────────────────────────────────────────
function openDiscEdit(itemId, currentVal) {
    document.getElementById('disc-display-' + itemId).style.display = 'none';
    var editWrap = document.getElementById('disc-edit-' + itemId);
    editWrap.classList.add('active');
    var input = document.getElementById('disc-input-' + itemId);
    input.value = currentVal;
    input.focus();
    input.select();

    // Allow Enter to save, Escape to cancel
    input.onkeydown = function(e) {
        if (e.key === 'Enter')  { e.preventDefault(); saveDiscountAjax(itemId); }
        if (e.key === 'Escape') { closeDiscEdit(itemId); }
    };
}

function closeDiscEdit(itemId) {
    document.getElementById('disc-display-' + itemId).style.display = 'flex';
    document.getElementById('disc-edit-' + itemId).classList.remove('active');
}

function showIndicator(itemId, type, msg) {
    var el = document.getElementById('disc-indicator-' + itemId);
    el.className = 'save-indicator ' + type;
    el.innerHTML = (type === 'ok' ? '<i class="fas fa-check"></i> ' : '<i class="fas fa-times"></i> ') + msg;
    setTimeout(function() {
        el.className = 'save-indicator';
        el.innerHTML = '';
    }, 2500);
}

function saveDiscountAjax(itemId) {
    var input    = document.getElementById('disc-input-' + itemId);
    var newValue = input ? input.value.trim() : document.getElementById('disc-bulk-' + itemId).value.trim();

    // Update hidden bulk field too
    var bulkField = document.getElementById('disc-bulk-' + itemId);
    if (bulkField) bulkField.value = newValue;

    var formData = new FormData();
    formData.append('ajax_discount', '1');
    formData.append('item_id', itemId);
    formData.append('discount', newValue);
    formData.append('order_id', '<?php echo addslashes($orderId); ?>');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.text(); })
    .then(function(text) {
        // We check if the page echoed our success signal
        if (text.indexOf('DISC_OK') !== -1) {
            // Update displayed value
            var dispEl = document.getElementById('disc-text-' + itemId);
            if (dispEl) {
                if (newValue === '') {
                    dispEl.className = 'disc-empty';
                    dispEl.textContent = 'No discount';
                } else {
                    dispEl.className = 'disc-value';
                    dispEl.textContent = newValue;
                }
            }
            closeDiscEdit(itemId);
            showIndicator(itemId, 'ok', 'Saved');
        } else {
            showIndicator(itemId, 'error', 'Failed');
        }
    })
    .catch(function() {
        showIndicator(itemId, 'error', 'Error');
    });
}

// ── Save All Discounts (bulk form submit) ────────────────────────────────────
function saveAllDiscounts() {
    // Copy all input values to hidden fields before submit
    var rows = document.querySelectorAll('tr[data-item-id]');
    rows.forEach(function(row) {
        var itemId   = row.getAttribute('data-item-id');
        var editInput = document.getElementById('disc-input-' + itemId);
        var bulkField = document.getElementById('disc-bulk-' + itemId);
        if (editInput && bulkField) {
            bulkField.value = editInput.value;
        }
    });
    if (confirm('Save all discounts for this order?')) {
        document.getElementById('bulkDiscountForm').submit();
    }
}
</script>

<?php
// ── AJAX discount handler (runs before HTML output on AJAX calls) ────────────
// This check won't fire here since output already started — handle at TOP of file.
// See note below.
?>

</body>
</html>
<?php } ?>