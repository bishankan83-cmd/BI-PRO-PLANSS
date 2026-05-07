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
        .page { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 3rem; }

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
            gap: .65rem;
            background: #fafbfe;
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

        /* ── Items Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
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

        /* Weight / rate chips */
        .chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .2rem .6rem;
            border-radius: .4rem;
            font-size: .78rem;
            font-weight: 600;
        }
        .chip-weight { background: var(--orange-soft); color: var(--orange); border: 1px solid var(--orange-border); }
        .chip-rate   { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; margin-left: .3rem; }
        .chip-missing { background: #fee2e2; color: var(--danger); border: 1px solid #fca5a5; }

        .chips-wrap { display: flex; flex-wrap: wrap; align-items: center; gap: .3rem; }

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
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        @media (max-width: 640px) {
            .page { padding: 1rem 1rem 2rem; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .topbar { padding: 1rem; }
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="topbar">
    <a href="tire-orders_mar.php" class="topbar-back">
        <i class="fas fa-arrow-left"></i> Orders
    </a>
    <div class="topbar-title">Order <span>#<?php echo htmlentities($orderData['order_id'] ?? '—'); ?></span></div>
</div>

<div class="page">

<?php if ($orderData): ?>

    <!-- ── Order Summary ── -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-receipt"></i></div>
            <h2>Order Summary</h2>
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
                        $badgeClass = $status === 'confirmed'      ? 'badge-confirmed'
                                    : ($status === 'cus_confirmed'  ? 'badge-info'
                                    : ($status === 'cus_pi_confirm' ? 'badge-orange'
                                    : 'badge-pending'));
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

    <!-- ── Order Items ── -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-boxes"></i></div>
            <h2>Order Items</h2>
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

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width:44px;">#</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Weight / Rate</th>
                            <th>Qty</th>
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
                    ?>
                    <tr>
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
                    </tr>
                    <?php endwhile;
                    else: ?>
                    <tr>
                        <td colspan="5">
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
</body>
</html>
<?php } ?>