<?php
session_start();
include('include/config.php');

if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // ═══════════════════════════════════════════════════════════════
    // FETCH ADMIN DETAILS & RESOLVE ACM REF
    // ═══════════════════════════════════════════════════════════════
    $adminId = intval($_SESSION["aid"]);
    $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
    $adminData = mysqli_fetch_array($adminQuery);

    // Resolve ACM Reference
    if (isset($_GET['acm_ref']) && !empty($_GET['acm_ref'])) {
        $requestedAcmRef = mysqli_real_escape_string($con, $_GET['acm_ref']);
        $adminAcmRef = mysqli_real_escape_string($con, $adminData['acm_ref']);
        if ($requestedAcmRef !== $adminAcmRef) {
            $requestedAcmRef = $adminAcmRef;
        }
        $activeAcmRef = $requestedAcmRef;
    } else {
        $activeAcmRef = $adminId;
    }

    // ═══════════════════════════════════════════════════════════════
    // GET ALLOWED CUSTOMERS
    // ═══════════════════════════════════════════════════════════════
    $customerQuery = mysqli_query($con, "SELECT cus_id, fullName, userEmail, customer_code, Country, company_rn FROM users WHERE acm_ref='$activeAcmRef'");
    $allowedCustomerIds = [];
    while ($customer = mysqli_fetch_array($customerQuery)) {
        if (!empty($customer['cus_id'])) {
            $allowedCustomerIds[] = intval($customer['cus_id']);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // VALIDATE ORDER_ID & FETCH ORDER
    // ═══════════════════════════════════════════════════════════════
    $orderNotFound = false;
    $order = null;
    $lineItems = [];
    $shipment = null;
    $charges = [];
    $allRevisions = [];

    if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
        $orderNotFound = true;
    } else {
        $requestedOrderId = mysqli_real_escape_string($con, $_GET['order_id']);
        
        // Fetch order with customer info
        $orderQuery = mysqli_query($con, "
            SELECT o.*,
                   u.customer_code, u.fullName, u.userEmail, u.Country, u.company_rn
            FROM tire_orders o
            LEFT JOIN users u ON o.customer_id = u.cus_id
            WHERE o.order_id='$requestedOrderId'
            LIMIT 1
        ");

        if (!$orderQuery || mysqli_num_rows($orderQuery) === 0) {
            $orderNotFound = true;
        } else {
            $order = mysqli_fetch_assoc($orderQuery);
            
            // Security: verify customer belongs to admin's scope
            if (!in_array(intval($order['customer_id']), $allowedCustomerIds)) {
                $orderNotFound = true;
                $order = null;
            } else {
                // Fetch line items
                $itemsQuery = mysqli_query($con, "
                    SELECT ti.item_id, ti.icode, ti.quantity, ti.unit_price,
                           ti.discount, ti.unit_weight, ti.total_weight,
                           ti.payment_amount, ti.total_payment, ti.total_cbm,
                           ti.unit_cbm, ti.rate_value, ti.revised,
                           ti.original_order_id AS item_orig_order,
                           td.description
                    FROM tire_order_items ti
                    LEFT JOIN tire_details td ON ti.icode = td.icode
                    WHERE ti.order_id = '$requestedOrderId'
                    ORDER BY ti.item_id
                ");

                if ($itemsQuery) {
                    while ($row = mysqli_fetch_assoc($itemsQuery)) {
                        $qty = floatval(preg_replace('/[^0-9.]/', '', $row['quantity']));
                        $price = floatval(preg_replace('/[^0-9.]/', '', $row['unit_price']));
                        $row['quantity_numeric'] = $qty;
                        $row['unit_price_numeric'] = $price;
                        $row['total_price'] = $qty * $price;
                        $lineItems[] = $row;
                    }
                }

                // Fetch shipment
                $shipmentQuery = mysqli_query($con, "SELECT * FROM shipments WHERE order_id = '$requestedOrderId' LIMIT 1");
                if ($shipmentQuery && mysqli_num_rows($shipmentQuery) > 0) {
                    $shipment = mysqli_fetch_assoc($shipmentQuery);
                }

                // Extract charges
                for ($i = 1; $i <= 4; $i++) {
                    $n = trim($order["charge{$i}_name"] ?? '');
                    $v = trim($order["charge{$i}_value"] ?? '');
                    if ($n !== '' && $v !== '') {
                        $charges[] = ['name' => $n, 'value' => $v];
                    }
                }

                // Build revision chain
                $isRevision = (int)$order['is_revision'] === 1;
                $originalOrderId = $order['original_order_id'];
                $rootOrderId = $isRevision ? $originalOrderId : $requestedOrderId;

                if (!empty($rootOrderId)) {
                    $safeRoot = mysqli_real_escape_string($con, $rootOrderId);
                    
                    // Fetch original order
                    $origRes = mysqli_query($con, "SELECT * FROM tire_orders WHERE order_id='$safeRoot' LIMIT 1");
                    if ($origRes && mysqli_num_rows($origRes) > 0) {
                        $origOrder = mysqli_fetch_assoc($origRes);
                        $origItems = [];
                        
                        $origItemsRes = mysqli_query($con, "
                            SELECT ti.item_id, ti.icode, ti.quantity, ti.unit_price,
                                   ti.discount, ti.unit_weight, ti.total_weight,
                                   ti.total_payment, ti.total_cbm, ti.unit_cbm,
                                   td.description
                            FROM tire_order_items ti
                            LEFT JOIN tire_details td ON ti.icode = td.icode
                            WHERE ti.order_id = '$safeRoot'
                            ORDER BY ti.item_id
                        ");
                        
                        if ($origItemsRes) {
                            while ($origItemRow = mysqli_fetch_assoc($origItemsRes)) {
                                $origItems[] = $origItemRow;
                            }
                        }

                        $allRevisions[] = [
                            'order' => $origOrder,
                            'items' => $origItems,
                            'version' => 0,
                            'is_original' => true,
                            'order_id' => $safeRoot,
                        ];
                    }

                    // Fetch revisions
                    $revRes = mysqli_query($con, "
                        SELECT order_id FROM tire_orders
                        WHERE original_order_id = '$safeRoot' AND is_revision = 1
                        ORDER BY order_id ASC
                    ");

                    if ($revRes) {
                        $vNum = 1;
                        while ($revRow = mysqli_fetch_assoc($revRes)) {
                            $rid = $revRow['order_id'];
                            $rRes = mysqli_query($con, "SELECT * FROM tire_orders WHERE order_id='$rid' LIMIT 1");
                            
                            if ($rRes && mysqli_num_rows($rRes) > 0) {
                                $revOrder = mysqli_fetch_assoc($rRes);
                                $revItems = [];

                                $revItemsRes = mysqli_query($con, "
                                    SELECT ti.item_id, ti.icode, ti.quantity, ti.unit_price,
                                           ti.discount, ti.unit_weight, ti.total_weight,
                                           ti.total_payment, ti.total_cbm, ti.unit_cbm,
                                           td.description
                                    FROM tire_order_items ti
                                    LEFT JOIN tire_details td ON ti.icode = td.icode
                                    WHERE ti.order_id = '$rid'
                                    ORDER BY ti.item_id
                                ");

                                if ($revItemsRes) {
                                    while ($revItemRow = mysqli_fetch_assoc($revItemsRes)) {
                                        $revItems[] = $revItemRow;
                                    }
                                }

                                $allRevisions[] = [
                                    'order' => $revOrder,
                                    'items' => $revItems,
                                    'version' => $vNum++,
                                    'is_original' => false,
                                    'order_id' => $rid,
                                ];
                            }
                        }
                    }
                }
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // FORMATTING HELPER FUNCTIONS
    // ═══════════════════════════════════════════════════════════════
    function fmtDate($d) { 
        return $d ? date('M j, Y', strtotime($d)) : '—'; 
    }

    function fmtDateTime($d) { 
        return $d ? date('M j, Y \a\t g:i A', strtotime($d)) : '—'; 
    }

    function fmtMoney($v) { 
        return '$' . number_format((float)$v, 2); 
    }

    function fmtNum($v, $d = 0) { 
        return number_format((float)$v, $d); 
    }

    function statusBadge($s) {
        $s = strtolower(trim($s ?? 'pending'));
        $map = [
            'pending' => ['status-pending', 'fa-clock', 'Pending'],
            'in process' => ['status-in-process', 'fa-cog', 'In Process'],
            'in-process' => ['status-in-process', 'fa-cog', 'In Process'],
            'closed' => ['status-closed', 'fa-check-circle', 'Closed'],
            'completed' => ['status-closed', 'fa-check-circle', 'Completed'],
            'complete' => ['status-closed', 'fa-check-circle', 'Complete'],
            'pi_confirm' => ['status-pi-confirm', 'fa-file-invoice', 'PI Confirm'],
            'revised' => ['status-revised', 'fa-sync-alt', 'Revised'],
            'share_planning' => ['status-in-process', 'fa-calendar-alt', 'In Planning'],
        ];
        [$cls, $icon, $label] = $map[$s] ?? ['status-pending', 'fa-circle', ucfirst(str_replace('_', ' ', $s))];
        return "<span class=\"status-badge $cls\"><i class=\"fas $icon\"></i> " . htmlentities($label) . "</span>";
    }

    function shipmentStatusBadge($s, $type = 'generic') {
        $s = strtolower(trim($s ?? 'pending'));
        if ($type === 'payment') {
            $map = [
                'pending' => ['ship-badge-pending', 'fa-hourglass-half', 'Pending'],
                'paid' => ['ship-badge-done', 'fa-check-circle', 'Paid'],
                'overdue' => ['ship-badge-overdue', 'fa-exclamation-circle', 'Overdue'],
                'partial' => ['ship-badge-partial', 'fa-adjust', 'Partial'],
            ];
        } else {
            $map = [
                'pending' => ['ship-badge-pending', 'fa-hourglass-half', 'Pending'],
                'received' => ['ship-badge-done', 'fa-check-circle', 'Received'],
                'issued' => ['ship-badge-done', 'fa-check-circle', 'Issued'],
                'sent' => ['ship-badge-done', 'fa-check-circle', 'Sent'],
                'completed' => ['ship-badge-done', 'fa-check-circle', 'Completed'],
                'n/a' => ['ship-badge-na', 'fa-minus-circle', 'N/A'],
            ];
        }
        [$cls, $icon, $label] = $map[$s] ?? ['ship-badge-pending', 'fa-circle', ucfirst($s)];
        return "<span class=\"ship-badge $cls\"><i class=\"fas $icon\"></i> " . htmlentities($label) . "</span>";
    }

    // Build back link
    $acmRefParam = isset($_GET['acm_ref']) ? '?acm_ref=' . urlencode($_GET['acm_ref']) : '';
    $cusIdParam = isset($_GET['cus_id']) ? (empty($acmRefParam) ? '?' : '&') . 'cus_id=' . urlencode($_GET['cus_id']) : '';
    $backLink = 'all-orders.php' . $acmRefParam . $cusIdParam;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Order Details</title>
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
            --orange-light: rgba(242, 128, 24, 0.1);
            --success: #27ae60;
            --success-light: rgba(39,174,96,0.1);
            --error: #e74c3c;
            --error-light: rgba(231,76,60,.1);
            --warning: #f39c12;
            --warning-light: rgba(241,196,15,.1);
            --revised: #9b59b6;
            --revised-light: rgba(155,89,182,.1);
            --info: #2980b9;
            --info-light: rgba(41,128,185,.1);
            --teal: #5bc0be;
            --teal-lt: rgba(91,192,190,0.12);
            --white: #ffffff;
            --blue: #3b82f6;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-ship: linear-gradient(135deg,#1a6fa8 0%,#2980b9 100%);
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.12);
            --shadow-xl: 0 24px 48px rgba(0,0,0,0.18);
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .main-wrapper { padding: 2rem; min-height: 100vh; max-width: 1400px; margin: 0 auto; }

        /* Back Button */
        .btn-back {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.6rem 1.25rem; border-radius: 0.75rem;
            background: var(--white); border: 2px solid var(--border-gray);
            color: var(--dark-gray); font-weight: 600; font-size: 0.9rem;
            text-decoration: none; margin-bottom: 1.5rem; transition: all 0.2s;
        }
        .btn-back:hover { border-color: var(--primary-orange); color: var(--primary-orange); }

        /* Page Header */
        .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2rem; flex-wrap:wrap; gap:1rem; }
        .page-title  { font-size:2rem; font-weight:800; color:var(--dark-gray); }
        .page-subtitle { color:var(--text-gray); font-size:0.95rem; margin-top:0.3rem; }

        /* Card */
        .card { background:var(--white); border-radius:1rem; border:1px solid var(--border-gray); overflow:hidden; box-shadow:var(--shadow-md); margin-bottom:1.5rem; }
        .card-header {
            padding:1.25rem 2rem; border-bottom:1px solid var(--border-gray);
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:1rem; background:#fdfdfd;
        }
        .card-title { font-size:1.15rem; font-weight:700; display:flex; align-items:center; gap:0.75rem; }
        .info-badge { padding:0.45rem 1rem; background:var(--orange-light); color:var(--primary-orange); border-radius:0.5rem; font-weight:700; font-size:0.9rem; }
        .card-body { padding:2rem; }

        /* Info Grid */
        .info-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:0; }
        .info-row { display:flex; flex-direction:column; padding:1.1rem 1.5rem; border-bottom:1px solid var(--border-gray); border-right:1px solid var(--border-gray); }
        .info-row:nth-child(4n) { border-right:none; }
        .info-label { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-gray); margin-bottom:0.35rem; }
        .info-value { font-size:0.95rem; color:var(--dark-gray); font-weight:500; }

        /* Section Label */
        .section-label { font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-gray); margin:2rem 0 1.2rem 0; display:flex; align-items:center; gap:0.75rem; padding-bottom:0.75rem; border-bottom:2px solid var(--border-gray); }

        /* Status Badges */
        .status-badge { padding:0.35rem 0.85rem; border-radius:2rem; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap; display:inline-flex; align-items:center; gap:0.4rem; }
        .status-pending { background:var(--error-light); color:var(--error); }
        .status-in-process { background:var(--warning-light); color:var(--warning); }
        .status-closed { background:var(--success-light); color:var(--success); }
        .status-pi-confirm { background:rgba(52,152,219,.1); color:#2980b9; }
        .status-revised { background:var(--revised-light); color:var(--revised); }

        /* Table */
        .table-responsive { overflow-x:auto; }
        .table { width:100%; border-collapse:collapse; font-size:0.88rem; }
        .table thead { background:var(--gradient-1); }
        .table th { padding:0.9rem; text-align:left; font-weight:700; color:white; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; }
        .table tbody tr { border-bottom:1px solid var(--border-gray); transition:background 0.2s; }
        .table tbody tr:hover { background:var(--orange-light); }
        .table td { padding:0.9rem; }

        /* Charges */
        .charges-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:0.75rem; }
        .charge-item { background:var(--bg-light); border:1px solid var(--border-gray); border-radius:0.6rem; padding:0.85rem 1.2rem; display:flex; justify-content:space-between; align-items:center; }
        .charge-name { font-size:0.85rem; color:var(--text-gray); font-weight:500; }
        .charge-value { font-size:0.95rem; font-weight:700; color:var(--dark-gray); }

        /* Totals Row */
        .totals-row { background:linear-gradient(135deg, rgba(242,128,24,.06), rgba(230,126,34,.04)); border-radius:0.75rem; padding:1.2rem 1.5rem; display:flex; gap:2rem; flex-wrap:wrap; align-items:center; border:1px solid rgba(242,128,24,.18); }
        .total-item { display:flex; flex-direction:column; gap:0.15rem; }
        .total-label { font-size:0.7rem; font-weight:600; text-transform:uppercase; color:var(--text-gray); }
        .total-value { font-size:1.1rem; font-weight:800; color:var(--dark-gray); }
        .total-value.highlight { color:var(--primary-orange); }

        /* Notes Box */
        .notes-box { background:var(--bg-light); border:1px solid var(--border-gray); border-radius:0.6rem; padding:1.1rem 1.3rem; font-size:0.88rem; color:var(--dark-gray); white-space:pre-wrap; line-height:1.7; }
        .no-val { font-style:italic; color:var(--text-gray); opacity:0.6; }

        /* Shipment Info Grid */
        .ship-info-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:0; }
        .ship-info-row { display:flex; flex-direction:column; padding:0.95rem 1.5rem; border-bottom:1px solid var(--border-gray); border-right:1px solid var(--border-gray); }
        .ship-info-row:nth-child(4n) { border-right:none; }
        .ship-info-label { font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; color:#2980b9; margin-bottom:0.3rem; display:flex; align-items:center; gap:0.35rem; }
        .ship-info-label i { opacity:0.7; }
        .ship-info-value { font-size:0.93rem; color:var(--dark-gray); font-weight:500; }

        /* Ship Badges */
        .ship-badge { padding:0.24rem 0.65rem; border-radius:1rem; font-size:0.7rem; font-weight:700; display:inline-flex; align-items:center; gap:0.28rem; }
        .ship-badge-pending { background:var(--warning-light); color:var(--warning); }
        .ship-badge-done { background:var(--success-light); color:var(--success); }
        .ship-badge-overdue { background:var(--error-light); color:var(--error); }
        .ship-badge-partial { background:var(--orange-light); color:var(--primary-orange); }
        .ship-badge-na { background:#f0f0f0; color:#999; }

        /* Revision Timeline */
        .timeline { position:relative; padding:1.5rem 0; }
        .timeline-item { position:relative; display:flex; gap:1.25rem; margin-bottom:1.3rem; }
        .timeline-dot { width:2.4rem; height:2.4rem; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:800; flex-shrink:0; position:relative; z-index:1; border:2px solid var(--white); }
        .timeline-dot.is-original { background:#e8eaed; color:#555; box-shadow:0 0 0 2px #ccc; }
        .timeline-dot.is-revision { background:var(--revised); color:var(--white); box-shadow:0 0 0 2px rgba(155,89,182,.35); }
        .timeline-dot.is-current { box-shadow:0 0 0 3px var(--primary-orange)!important; }
        .timeline-content { flex:1; background:var(--bg-light); border:1px solid var(--border-gray); border-radius:0.75rem; padding:1rem 1.3rem; }

        /* No Data */
        .no-data { text-align:center; padding:3rem 2rem; color:var(--text-gray); }
        .no-data i { font-size:3.5rem; opacity:0.15; margin-bottom:1rem; display:block; color:var(--primary-orange); }
        .no-data h3 { font-size:1.3rem; font-weight:700; margin-bottom:0.4rem; }

        /* Scrollbar */
        ::-webkit-scrollbar { width:8px; }
        ::-webkit-scrollbar-track { background:var(--bg-light); }
        ::-webkit-scrollbar-thumb { background:var(--primary-orange); border-radius:4px; }

        @media (max-width:768px) {
            .main-wrapper { padding:1rem; }
            .page-header { flex-direction:column; }
            .page-title { font-size:1.6rem; }
            .info-grid, .ship-info-grid { grid-template-columns:1fr; }
            .info-row, .ship-info-row { border-right:none; }
            .card-body { padding:1.25rem; }
            .totals-row { gap:1rem; }
        }
    </style>
</head>
<body>
<div class="main-wrapper">

    <!-- Back Button -->
    <a href="<?php echo $backLink; ?>" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>

    <?php if ($orderNotFound): ?>
    
    <!-- Order Not Found -->
    <div class="card">
        <div class="card-body">
            <div class="no-data">
                <i class="fas fa-search"></i>
                <h3>Order Not Found</h3>
                <p>The requested order could not be found or you don't have access to it.</p>
            </div>
        </div>
    </div>

    <?php else: ?>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Order #<?php echo htmlentities($order['order_id']); ?></h1>
            <p class="page-subtitle"><?php echo fmtDateTime($order['order_date']); ?></p>
        </div>
        <div style="display:flex; gap:1rem; align-items:center;">
            <?php echo statusBadge($order['status']); ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         ORDER INFORMATION SECTION
    ═══════════════════════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-info-circle"></i> Order Information</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Order ID</span>
                    <span class="info-value"><strong>#<?php echo htmlentities($order['order_id']); ?></strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value"><?php echo htmlentities($order['fullName'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer Code</span>
                    <span class="info-value"><?php echo htmlentities($order['customer_code'] ?: '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlentities($order['userEmail'] ?: '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Country</span>
                    <span class="info-value"><?php echo htmlentities($order['Country'] ?: '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Company</span>
                    <span class="info-value"><?php echo htmlentities($order['company_rn'] ?: '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Date</span>
                    <span class="info-value"><?php echo fmtDateTime($order['order_date']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Reference</span>
                    <span class="info-value"><?php echo htmlentities($order['order_reference'] ?: '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Invoice No.</span>
                    <span class="info-value"><?php echo htmlentities($order['invoice_no'] ?: '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value"><?php echo statusBadge($order['status']); ?></span>
                </div>
                <?php if ($order['destination_port']): ?>
                <div class="info-row">
                    <span class="info-label">Destination Port</span>
                    <span class="info-value"><?php echo htmlentities($order['destination_port']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['shipping_method']): ?>
                <div class="info-row">
                    <span class="info-label">Shipping Method</span>
                    <span class="info-value"><?php echo htmlentities($order['shipping_method']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['container_size']): ?>
                <div class="info-row">
                    <span class="info-label">Container Size</span>
                    <span class="info-value"><?php echo htmlentities($order['container_size']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['hs_code']): ?>
                <div class="info-row">
                    <span class="info-label">HS Code</span>
                    <span class="info-value"><?php echo htmlentities($order['hs_code']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['packing']): ?>
                <div class="info-row">
                    <span class="info-label">Packing</span>
                    <span class="info-value"><?php echo htmlentities($order['packing']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         ORDER ITEMS SECTION
    ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($lineItems)): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-list"></i> Order Items</h2>
            <div class="info-badge">Total: <?php echo count($lineItems); ?> item<?php echo count($lineItems) !== 1 ? 's' : ''; ?></div>
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
                            <th>Unit Price</th>
                            <th>Discount</th>
                            <th>Unit Wt.</th>
                            <th>Total Wt.</th>
                            <th>Unit CBM</th>
                            <th>Total CBM</th>
                            <th>Total Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lineItems as $idx => $item): ?>
                        <tr>
                            <td><?php echo $idx + 1; ?></td>
                            <td><strong><?php echo htmlentities($item['icode']); ?></strong></td>
                            <td style="font-size:0.85rem; color:var(--text-gray);"><?php echo htmlentities($item['description'] ?: '—'); ?></td>
                            <td><?php echo htmlentities($item['quantity']); ?></td>
                            <td><?php echo htmlentities($item['unit_price'] ?: '—'); ?></td>
                            <td><?php echo htmlentities($item['discount'] ?: '—'); ?></td>
                            <td><?php echo htmlentities($item['unit_weight'] ?: '—'); ?></td>
                            <td><?php echo htmlentities($item['total_weight'] ?: '—'); ?></td>
                            <td><?php echo htmlentities($item['unit_cbm'] ?: '—'); ?></td>
                            <td><?php echo htmlentities($item['total_cbm'] ?: '—'); ?></td>
                            <td style="font-weight:700; color:var(--primary-orange);"><?php echo htmlentities($item['total_payment'] ?: '—'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals Row -->
            <div style="margin-top:1.5rem;">
                <div class="totals-row">
                    <div class="total-item">
                        <span class="total-label">Total Items</span>
                        <span class="total-value"><?php echo count($lineItems); ?></span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">Total Quantity</span>
                        <span class="total-value"><?php echo fmtNum($order['total_quantity']); ?> pcs</span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">Total Weight</span>
                        <span class="total-value"><?php echo $order['total_weight'] ? fmtNum($order['total_weight'], 2) . ' kg' : '—'; ?></span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">Total CBM</span>
                        <span class="total-value"><?php echo $order['total_cbm'] ? fmtNum($order['total_cbm'], 4) . ' m³' : '—'; ?></span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">Order Value</span>
                        <span class="total-value highlight"><?php echo $order['total_payment'] ? fmtMoney($order['total_payment']) : '—'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         CHARGES SECTION
    ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($charges)): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-plus-circle"></i> Additional Charges</h2>
        </div>
        <div class="card-body">
            <div class="charges-grid">
                <?php foreach ($charges as $charge): ?>
                <div class="charge-item">
                    <span class="charge-name"><?php echo htmlentities($charge['name']); ?></span>
                    <span class="charge-value"><?php echo htmlentities($charge['value']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         NOTES & COMMENTS SECTION
    ═══════════════════════════════════════════════════════════════ -->
    <?php if ($order['order_notes'] || $order['customer_comment'] || $order['acm_comment']): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-comment-alt"></i> Notes & Comments</h2>
        </div>
        <div class="card-body" style="display:flex; flex-direction:column; gap:1.5rem;">
            <?php if ($order['order_notes']): ?>
            <div>
                <div class="info-label" style="margin-bottom:0.5rem;">Order Notes</div>
                <div class="notes-box"><?php echo htmlentities($order['order_notes']); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($order['customer_comment']): ?>
            <div>
                <div class="info-label" style="margin-bottom:0.5rem;">Customer Comment</div>
                <div class="notes-box"><?php echo htmlentities($order['customer_comment']); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($order['acm_comment']): ?>
            <div>
                <div class="info-label" style="margin-bottom:0.5rem;">ACM Comment</div>
                <div class="notes-box"><?php echo htmlentities($order['acm_comment']); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         SHIPMENT SECTION
    ═══════════════════════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-ship"></i> Logistics & Shipment</h2>
        </div>
        <div class="card-body">
            <?php if ($shipment): ?>
            <div class="ship-info-grid">
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-handshake"></i> Incoterm</span>
                    <span class="ship-info-value"><?php echo htmlentities($shipment['inco_term'] ?: '—'); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-calendar-alt"></i> Loading Date</span>
                    <span class="ship-info-value"><?php echo fmtDate($shipment['loading_date']); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-building"></i> Freight Forwarder</span>
                    <span class="ship-info-value"><?php echo htmlentities($shipment['freight_forwarder'] ?: '—'); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-dollar-sign"></i> Freight Cost</span>
                    <span class="ship-info-value"><?php echo $shipment['freight_cost'] ? fmtMoney($shipment['freight_cost']) : '—'; ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-ship"></i> Vessel / Voyage</span>
                    <span class="ship-info-value"><?php echo htmlentities($shipment['vessel_voy'] ?: '—'); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-file-invoice"></i> B/L Number</span>
                    <span class="ship-info-value"><?php echo htmlentities($shipment['bl_number'] ?: '—'); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-map-marker-alt"></i> Port of Discharge</span>
                    <span class="ship-info-value"><?php echo htmlentities($shipment['port_of_discharge'] ?: '—'); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-flag-checkered"></i> Final Destination</span>
                    <span class="ship-info-value"><?php echo htmlentities($shipment['final_destination'] ?: '—'); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-clock"></i> ETA</span>
                    <span class="ship-info-value"><?php echo fmtDate($shipment['eta']); ?></span>
                </div>
                <div class="ship-info-row">
                    <span class="ship-info-label"><i class="fas fa-credit-card"></i> Payment Status</span>
                    <span class="ship-info-value"><?php echo shipmentStatusBadge($shipment['payment_status'] ?? 'pending', 'payment'); ?></span>
                </div>
            </div>
            <?php else: ?>
            <div class="no-data" style="padding:2.5rem;">
                <i class="fas fa-ship"></i>
                <h3>No Shipment Record</h3>
                <p>No shipment has been created for this order yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         REVISION TIMELINE SECTION
    ═══════════════════════════════════════════════════════════════ -->
    <?php if (!empty($allRevisions) && count($allRevisions) > 1): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-code-branch"></i> Revision History</h2>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php foreach ($allRevisions as $rev): ?>
                <div class="timeline-item">
                    <div class="timeline-dot <?php echo $rev['is_original'] ? 'is-original' : 'is-revision'; ?> <?php echo $rev['order_id'] == $order['order_id'] ? 'is-current' : ''; ?>">
                        <?php echo $rev['version']; ?>
                    </div>
                    <div class="timeline-content">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                            <div style="font-weight:700; font-size:0.95rem;">
                                <?php echo $rev['is_original'] ? 'Original Order' : 'Revision ' . $rev['version']; ?>
                                <?php echo $rev['order_id'] == $order['order_id'] ? '<span style="font-size:0.65rem; background:var(--primary-orange); color:white; padding:0.2rem 0.5rem; border-radius:1rem; margin-left:0.5rem;">Current</span>' : ''; ?>
                            </div>
                        </div>
                        <div style="font-size:0.8rem; color:var(--text-gray); margin-bottom:0.7rem;">
                            Order #<?php echo htmlentities($rev['order_id']); ?> • <?php echo fmtDateTime($rev['order']['order_date']); ?>
                        </div>
                        <?php if (!empty($rev['items'])): ?>
                        <div style="font-size:0.8rem; color:var(--text-gray); line-height:1.6;">
                            <strong><?php echo count($rev['items']); ?> item<?php echo count($rev['items']) !== 1 ? 's' : ''; ?></strong> | 
                            <strong><?php echo fmtNum($rev['order']['total_quantity']); ?></strong> pcs | 
                            <strong><?php echo $rev['order']['total_payment'] ? fmtMoney($rev['order']['total_payment']) : '—'; ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>

</div>
</body>
</html>
<?php } ?>