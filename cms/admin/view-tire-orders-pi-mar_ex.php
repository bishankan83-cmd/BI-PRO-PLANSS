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

    // Get order ID
    $orderId = mysqli_real_escape_string($con, $_GET['id']);

    // Handle marketing_executive_comment update WITH auto status change
    if (isset($_POST['update_marketing_comment'])) {
        $marketingComment = mysqli_real_escape_string($con, $_POST['marketing_executive_comment']);

        // Server-side guard: if status is confirm_marketing, ERP number must exist first
        $guardQuery = mysqli_query($con, "SELECT status, erp_number FROM tire_orders WHERE order_id = '$orderId'");
        $guardData  = mysqli_fetch_array($guardQuery);
        if (strtolower($guardData['status']) === 'confirm_marketing' && empty(trim($guardData['erp_number'] ?? ''))) {
            $errorMsg = "Action blocked: You must enter and save the ERP Number before saving the Marketing Executive comment.";
        } else {

        // Auto-determine status based on whether comment exists
        if (!empty(trim($marketingComment))) {
            $autoStatus = 'Share_planning_tem';
        } else {
            $autoStatus = 'confirm_wait_marketing_man';
        }

        $updateMarketing = mysqli_query($con, "
            UPDATE tire_orders 
            SET marketing_executive_comment = '$marketingComment',
                status = '$autoStatus'
            WHERE order_id = '$orderId'
        ");

        if ($updateMarketing) {
            $statusLabel = $autoStatus === 'Share_planning_tem'
                ? 'Share Planning (Temporary)'
                : 'Confirm — Wait Marketing Manager';
            $_SESSION['success_msg'] = "Comment saved and status automatically updated to \"$statusLabel\"!";
            header('Location: sent_mail_planning.php?id=' . urlencode($orderId));
            exit();
        } else {
            $errorMsg = "Failed to update Marketing Executive comment.";
        }
        } // end ERP guard else
    }

    // Handle ERP number save (AJAX and regular form submission)
    if (isset($_POST['save_erp_number'])) {
        $erpNumber = mysqli_real_escape_string($con, trim($_POST['erp_number']));

        if (empty($erpNumber)) {
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ERP Number is required.']);
                exit();
            }
            $errorMsg = "ERP Number is required. Please enter the ERP number before saving.";
        } else {
            $updateErp = mysqli_query($con, "
                UPDATE tire_orders 
                SET erp_number = '$erpNumber'
                WHERE order_id = '$orderId'
            ");

            if ($updateErp) {
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => "ERP Number \"$erpNumber\" has been saved successfully!", 'erp_number' => $erpNumber]);
                    exit();
                }
                $_SESSION['success_msg'] = "ERP Number \"$erpNumber\" has been saved successfully!";
                header('Location: sent_mail_planning.php?id=' . urlencode($orderId));
                exit();
            } else {
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to save ERP Number. Please try again.']);
                    exit();
                }
                $errorMsg = "Failed to save ERP Number. Please try again.";
            }
        }
    }

    // Fetch order details
    $orderQuery = mysqli_query($con, "
        SELECT o.* 
        FROM tire_orders o
        WHERE o.order_id = '$orderId'
    ");
    $orderData = mysqli_fetch_array($orderQuery);

    // Fetch order items joined with tire_details
    $itemsQuery = mysqli_query($con, "
        SELECT
            oi.*,
            td.Description  AS td_description,
            td.tire_size    AS td_tire_size,
            td.Brand        AS td_brand,
            td.Type         AS td_type,
            td.Colour       AS td_colour,
            td.Rim          AS td_rim,
            td.fweight      AS td_fweight,
            td.cbm          AS td_cbm
        FROM tire_order_items oi
        LEFT JOIN tire_details td ON td.icode = oi.icode
        WHERE oi.order_id = '$orderId'
        ORDER BY oi.item_id
    ");

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
            --gradient-excel: linear-gradient(135deg, #1D6F42 0%, #217346 100%);
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

        .main-wrapper { min-height: 100vh; padding: 2rem; }

        .page-header {
            display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;
        }
        .page-title { font-size: 2rem; font-weight: 800; color: var(--dark-gray); margin-bottom: 0.5rem; }
        .page-subtitle { color: var(--text-gray); font-size: 1rem; }

        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem;
            border: none; border-radius: 0.75rem; font-weight: 600; font-size: 0.9rem;
            cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .btn-back { background: var(--light-gray); color: var(--dark-gray); margin-bottom: 1.5rem; }
        .btn-back:hover { background: var(--border-gray); }
        .btn-primary { background: var(--gradient-1); color: white; box-shadow: var(--shadow); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-success { background: var(--gradient-2); color: white; box-shadow: var(--shadow); }
        .btn-success:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }

        /* ── Excel download button ── */
        .btn-excel {
            background: var(--gradient-excel);
            color: white;
            box-shadow: var(--shadow);
            font-size: 0.95rem;
            padding: 0.7rem 1.4rem;
            border-radius: 0.75rem;
        }
        .btn-excel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(29, 111, 66, 0.35);
        }
        .btn-excel i { font-size: 1.1rem; }

        /* ── Download action bar at top of page ── */
        .action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border: 1px solid var(--border-gray);
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.75rem;
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
            gap: 1rem;
        }
        .action-bar-left {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .action-bar-label {
            font-size: 0.85rem;
            color: var(--text-gray);
            font-weight: 500;
        }
        .action-bar-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .excel-badge {
            background: #E9F5EE;
            color: #1D6F42;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.15rem 0.55rem;
            border-radius: 0.4rem;
            border: 1px solid #A8D5BC;
            letter-spacing: 0.3px;
        }

        .card {
            background: var(--white); border-radius: 1rem; border: 1px solid var(--border-gray);
            overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem;
        }
        .card-header {
            padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-gray); background: #fdfdfd;
        }
        .card-title {
            font-size: 1.3rem; font-weight: 700; color: var(--dark-gray);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .card-body { padding: 2rem; }

        .info-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem; margin-bottom: 2rem;
        }
        .info-item {
            padding: 1rem; background: var(--bg-light);
            border-radius: 0.75rem; border: 1px solid var(--border-gray);
        }
        .info-label {
            font-size: 0.85rem; color: var(--text-gray); text-transform: uppercase;
            font-weight: 600; margin-bottom: 0.5rem; letter-spacing: 0.5px;
        }
        .info-value { font-size: 1.1rem; font-weight: 600; color: var(--dark-gray); }

        .status-badge {
            padding: 0.5rem 1rem; border-radius: 0.6rem; font-size: 0.85rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;
        }
        .status-pending                    { background: #fff3cd; color: #f39c12; border: 1px solid #ffeaa7; }
        .status-confirmed                  { background: #d4edda; color: #27ae60; border: 1px solid #a3e4b7; }
        .status-cus_confirmed              { background: #d1ecf1; color: #0d6efd; border: 1px solid #a0d8e8; }
        .status-share_planning_tem         { background: #e8d5f5; color: #7b2d8b; border: 1px solid #d1a8e8; }
        .status-confirm_wait_marketing_man { background: #fde8d8; color: #c0530a; border: 1px solid #f5c4a0; }
        .status-confirm_marketing          { background: #d4f5e0; color: #1a7f42; border: 1px solid #7ed4a0; }

        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-gray); }
        .table th {
            font-weight: 600; color: var(--text-gray); text-transform: uppercase;
            font-size: 0.85rem; background: var(--bg-light);
        }
        .table td { font-size: 0.95rem; }

        /* ── Enhanced table with tire_details columns ── */
        .table th.col-fweight,
        .table th.col-cbm {
            background: #E9F5EE;
            color: #1D6F42;
        }
        .table td.col-fweight,
        .table td.col-cbm {
            background: #F5FBF7;
            color: #1D6F42;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .multi-value-list { list-style: none; padding: 0; margin: 0; }
        .multi-value-item { padding: 0.25rem 0; }
        .multi-value-item:not(:last-child) {
            border-bottom: 1px dashed var(--border-gray); margin-bottom: 0.25rem; padding-bottom: 0.5rem;
        }

        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--dark-gray); }
        .form-label .required-star { color: #e74c3c; margin-left: 0.25rem; font-size: 1rem; }
        .form-control {
            width: 100%; padding: 0.75rem 1rem; border: 2px solid var(--border-gray);
            border-radius: 0.75rem; font-size: 0.95rem; transition: all 0.2s; font-family: inherit;
        }
        .form-control:focus {
            outline: none; border-color: var(--primary-orange); box-shadow: 0 0 0 3px var(--orange-light);
        }
        .form-control.is-invalid {
            border-color: #e74c3c; box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        textarea.form-control { resize: vertical; min-height: 130px; line-height: 1.6; }

        .alert {
            padding: 1rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
        }
        .alert-success { background: var(--success-light); color: #27ae60; border: 1px solid #a3e4b7; }
        .alert-danger  { background: #f8d7da; color: #c0392b; border: 1px solid #f5c6cb; }
        .alert-warning { background: var(--warning-light); color: #f39c12; border: 1px solid #ffeaa7; }
        .alert-info    { background: var(--info-light); color: #2980b9; border: 1px solid #a0d8e8; }

        .no-data { text-align: center; padding: 3rem 2rem; color: var(--text-gray); }
        .no-data i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; display: block; }

        .comment-display {
            background: var(--bg-light); border: 1px solid var(--border-gray);
            border-radius: 0.75rem; padding: 1rem 1.25rem; font-size: 1rem;
            color: var(--dark-gray); line-height: 1.7; min-height: 60px;
        }
        .comment-display.empty { color: var(--text-gray); font-style: italic; }

        .redirect-note {
            font-size: 0.85rem; color: var(--text-gray); margin-top: 0.75rem;
            display: flex; align-items: center; gap: 0.4rem;
        }

        .comments-container {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem; margin-bottom: 2rem;
        }

        /* Auto-status info box */
        .auto-status-box {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-gray);
            background: var(--bg-light);
            margin-bottom: 1.5rem;
        }
        .auto-status-box .rule {
            flex: 1;
            font-size: 0.92rem;
            color: var(--dark-gray);
            line-height: 1.7;
        }
        .auto-status-box .rule strong { font-weight: 700; }
        .pill {
            display: inline-block;
            padding: 0.2rem 0.7rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            vertical-align: middle;
        }
        .pill-spt  { background: #e8d5f5; color: #7b2d8b; border: 1px solid #d1a8e8; }
        .pill-cwmm { background: #fde8d8; color: #c0530a; border: 1px solid #f5c4a0; }

        /* Live preview strip */
        .status-will-be {
            margin-top: 0.85rem;
            padding: 0.65rem 1rem;
            border-radius: 0.65rem;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .status-will-be.spt  { background: #e8d5f5; color: #7b2d8b; border: 1px solid #d1a8e8; }
        .status-will-be.cwmm { background: #fde8d8; color: #c0530a; border: 1px solid #f5c4a0; }

        /* ── ERP Number Section ─────────────────────────────────────── */
        .erp-card {
            border: 2px solid #1a7f42;
            box-shadow: 0 0 0 4px rgba(26, 127, 66, 0.08), var(--shadow-sm);
        }
        .erp-card .card-header {
            background: linear-gradient(135deg, #f0faf4 0%, #e6f7ed 100%);
            border-bottom: 2px solid #7ed4a0;
        }
        .erp-card .card-title { color: #1a7f42; }

        .erp-info-box {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #7ed4a0;
            background: #f0faf4;
            margin-bottom: 1.75rem;
        }
        .erp-info-box p {
            font-size: 0.92rem;
            color: #1a6636;
            line-height: 1.7;
            flex: 1;
        }
        .erp-info-box p strong { font-weight: 700; }

        .erp-input-row {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .erp-input-row .form-group {
            flex: 1;
            min-width: 220px;
            margin-bottom: 0;
        }
        .erp-field {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-color: #7ed4a0;
        }
        .erp-field:focus {
            border-color: #1a7f42;
            box-shadow: 0 0 0 3px rgba(26, 127, 66, 0.12);
        }
        .erp-field.is-invalid {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .erp-field-error {
            color: #e74c3c;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.45rem;
            display: none;
            align-items: center;
            gap: 0.4rem;
        }
        .erp-field-error.visible { display: flex; }

        .erp-saved-display {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            background: #d4f5e0;
            border: 1px solid #7ed4a0;
            border-radius: 0.75rem;
            position: relative;
        }
        .erp-saved-display .erp-number-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.2rem;
            font-weight: 800;
            color: #1a7f42;
            letter-spacing: 0.75px;
        }
        .erp-saved-display .erp-saved-label {
            font-size: 0.85rem;
            color: #2d8a55;
            font-weight: 600;
        }
        .erp-edit-link {
            margin-left: auto;
            font-size: 0.85rem;
            color: #1a7f42;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .erp-edit-link:hover { color: #0f5028; }

        /* ── Auto-save indicator ─────────────────────────────────────── */
        .erp-auto-save-indicator {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .erp-auto-save-indicator.show { opacity: 1; }
        .erp-auto-save-indicator.saving { color: #2980b9; }
        .erp-auto-save-indicator.saved  { color: #27ae60; }

        /* ── Download spinner overlay ─────────────────────────────────── */
        .download-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.38);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1rem;
        }
        .download-overlay.show { display: flex; }
        .download-spinner-box {
            background: #fff;
            border-radius: 1.25rem;
            padding: 2.5rem 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.25rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            min-width: 280px;
        }
        .download-spinner-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #1D6F42 0%, #27ae60 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            animation: spinPulse 1.2s ease-in-out infinite;
        }
        @keyframes spinPulse {
            0%,100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(29,111,66,0.3); }
            50%      { transform: scale(1.08); box-shadow: 0 0 0 12px rgba(29,111,66,0); }
        }
        .download-spinner-text {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark-gray);
        }
        .download-spinner-sub {
            font-size: 0.85rem;
            color: var(--text-gray);
            text-align: center;
        }

        @media (max-width: 768px) {
            .main-wrapper { padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; }
            .info-grid { grid-template-columns: 1fr; }
            .comments-container { grid-template-columns: 1fr; }
            .erp-input-row { flex-direction: column; }
            .action-bar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<!-- ── Download spinner overlay ─────────────────────────────────────────────── -->
<div class="download-overlay" id="downloadOverlay">
    <div class="download-spinner-box">
        <div class="download-spinner-icon"><i class="fas fa-file-excel"></i></div>
        <div class="download-spinner-text">Generating Work Order…</div>
        <div class="download-spinner-sub">Building your Excel file from<br>the latest tire data. Please wait.</div>
    </div>
</div>

<div class="main-wrapper">

    <a href="tire-orders-marketing-share-planning_ex.php" class="btn btn-back">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>

    <?php if (isset($errorMsg)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlentities($errorMsg); ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></span>
    </div>
    <?php endif; ?>

    <?php if ($orderData): ?>

    <!-- ── Page header ──────────────────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Order #<?php echo htmlentities($orderData['order_id']); ?></h1>
            <p class="page-subtitle">Complete order details and items</p>
        </div>
    </div>

    <!-- ── Download action bar ──────────────────────────────────────────────── -->
    <div class="action-bar">
        <div class="action-bar-left">
            <span class="action-bar-label">Export this order</span>
            <span class="action-bar-title">
                <i class="fas fa-file-excel" style="color:#1D6F42;"></i>
                Work Order Document
                <span class="excel-badge">XLSX</span>
            </span>
        </div>
        <a href="generate_work_order.php?id=<?php echo urlencode($orderData['order_id']); ?>"
           class="btn btn-excel"
           id="downloadBtn"
           onclick="triggerDownload(event, this)">
            <i class="fas fa-download"></i>
            Download Work Order Excel
        </a>
    </div>

    <!-- ── Order Summary Card ───────────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><i class="fas fa-info-circle"></i> Order Summary</h2>
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
                        $statusLower = strtolower($status);
                        if ($statusLower === 'confirmed') {
                            $badgeClass = 'status-confirmed';
                        } elseif ($statusLower === 'cus_confirmed') {
                            $badgeClass = 'status-cus_confirmed';
                        } elseif ($statusLower === 'share_planning_tem') {
                            $badgeClass = 'status-share_planning_tem';
                        } elseif ($statusLower === 'confirm_wait_marketing_man') {
                            $badgeClass = 'status-confirm_wait_marketing_man';
                        } elseif ($statusLower === 'confirm_marketing') {
                            $badgeClass = 'status-confirm_marketing';
                        } else {
                            $badgeClass = 'status-pending';
                        }
                        $statusText = ucwords(str_replace('_', ' ', $status));
                        ?>
                        <span class="status-badge <?php echo $badgeClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </div>
                </div>
                <?php if (!empty($orderData['erp_number'])): ?>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-barcode" style="margin-right:0.4rem;"></i> ERP Number</div>
                    <div class="info-value" style="font-family:'Courier New',Courier,monospace;color:#1a7f42;">
                        <?php echo htmlentities($orderData['erp_number']); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($orderData['order_notes']): ?>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <div class="info-label">Order Notes</div>
                    <div class="info-value" style="font-weight: 400; line-height: 1.6;">
                        <?php echo nl2br(htmlentities($orderData['order_notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Comments Display -->
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--dark-gray);margin-bottom:1.5rem;margin-top:1.5rem;display:flex;align-items:center;gap:0.5rem;">
                <i class="fas fa-comments"></i> Comments
            </h3>
            <div class="comments-container">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-user-tie" style="margin-right:0.5rem;"></i> ACM Comment</div>
                    <div class="info-value" style="font-weight:400;">
                        <?php if (!empty($orderData['acm_comment'])): ?>
                            <div class="comment-display"><?php echo nl2br(htmlentities($orderData['acm_comment'])); ?></div>
                        <?php else: ?>
                            <div class="comment-display empty">No ACM comment has been added yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-chart-line" style="margin-right:0.5rem;"></i> Marketing Executive Comment</div>
                    <div class="info-value" style="font-weight:400;">
                        <?php if (!empty($orderData['marketing_executive_comment'])): ?>
                            <div class="comment-display"><?php echo nl2br(htmlentities($orderData['marketing_executive_comment'])); ?></div>
                        <?php else: ?>
                            <div class="comment-display empty">No Marketing Executive comment has been added yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Order Items Card (with tire_details columns) ─────────────────────── -->
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <h2 class="card-title"><i class="fas fa-boxes"></i> Order Items</h2>
                <span style="font-size:0.82rem;color:#1D6F42;font-weight:600;background:#E9F5EE;padding:0.3rem 0.75rem;border-radius:0.5rem;border:1px solid #A8D5BC;">
                    <i class="fas fa-database" style="margin-right:0.35rem;"></i>
                    Weight &amp; CBM pulled from Tire Details
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Tyre Size</th>
                            <th>Brand</th>
                            <th>Colour</th>
                            <th>Fit</th>
                            <th>Rim</th>
                            <th>Description</th>
                            <th class="col-fweight">Avg. Finish Weight (kg)</th>
                            <th class="col-cbm">Per Tyre Vol. (cbm)</th>
                            <th>Qty (pcs)</th>
                            <th class="col-fweight">Total Tonnage (kg)</th>
                            <th class="col-cbm">Total Volume (cbm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cnt = 1;
                        $grandQty     = 0;
                        $grandVolume  = 0;
                        $grandTonnage = 0;

                        if ($itemsQuery && mysqli_num_rows($itemsQuery) > 0) {
                            while ($item = mysqli_fetch_array($itemsQuery)) {
                                $icodes     = parseCommaSeparatedValue($item['icode']);
                                $quantities = parseCommaSeparatedValue($item['quantity']);

                                // Single or multi-code rows
                                if (empty($icodes)) {
                                    $icodes     = [$item['icode']];
                                    $quantities = [$item['quantity']];
                                }

                                $maxCount   = max(count($icodes), count($quantities));
                                $icodes     = array_pad($icodes,     $maxCount, '');
                                $quantities = array_pad($quantities, $maxCount, '');

                                // For multi-code rows, fetch individual tire_details
                                $subRows = [];
                                if (count($icodes) > 1) {
                                    foreach ($icodes as $idx => $icode) {
                                        if (empty($icode)) continue;
                                        $icode_esc = mysqli_real_escape_string($con, $icode);
                                        $tdQ = mysqli_query($con, "SELECT * FROM tire_details WHERE icode='$icode_esc' LIMIT 1");
                                        $td  = $tdQ ? mysqli_fetch_assoc($tdQ) : [];
                                        $subRows[] = [
                                            'icode'    => $icode,
                                            'qty'      => (float)($quantities[$idx] ?? 0),
                                            'tire_size'=> $td['tire_size']   ?? 'N/A',
                                            'brand'    => $td['Brand']       ?? 'N/A',
                                            'colour'   => $td['Colour']      ?? 'N/A',
                                            'fit'      => $td['Type']        ?? 'N/A',
                                            'rim'      => $td['Rim']         ?? '',
                                            'desc'     => $td['Description'] ?? 'N/A',
                                            'fweight'  => (float)($td['fweight'] ?? 0),
                                            'cbm'      => (float)($td['cbm']     ?? 0),
                                        ];
                                    }

                                    foreach ($subRows as $sr) {
                                        $rowTonnage = $sr['fweight'] * $sr['qty'];
                                        $rowVolume  = $sr['cbm']     * $sr['qty'];
                                        $grandQty      += $sr['qty'];
                                        $grandTonnage  += $rowTonnage;
                                        $grandVolume   += $rowVolume;
                                        ?>
                                        <tr>
                                            <td><?php echo $cnt++; ?></td>
                                            <td><strong style="color:#0000ff;"><?php echo htmlentities($sr['icode']); ?></strong></td>
                                            <td><?php echo htmlentities($sr['tire_size']); ?></td>
                                            <td><?php echo htmlentities($sr['brand']); ?></td>
                                            <td><?php echo htmlentities($sr['colour']); ?></td>
                                            <td><?php echo htmlentities($sr['fit']); ?></td>
                                            <td><?php echo htmlentities($sr['rim']) ?: '—'; ?></td>
                                            <td><?php echo htmlentities($sr['desc']); ?></td>
                                            <td class="col-fweight"><?php echo $sr['fweight'] > 0 ? number_format($sr['fweight'], 3) : '—'; ?></td>
                                            <td class="col-cbm"><?php echo $sr['cbm']     > 0 ? number_format($sr['cbm'],     4) : '—'; ?></td>
                                            <td><strong><?php echo number_format($sr['qty'], 0); ?></strong></td>
                                            <td class="col-fweight"><?php echo $rowTonnage > 0 ? number_format($rowTonnage, 2) : '—'; ?></td>
                                            <td class="col-cbm"><?php echo $rowVolume  > 0 ? number_format($rowVolume,  4) : '—'; ?></td>
                                        </tr>
                                        <?php
                                    }

                                } else {
                                    // Single icode — use the joined td_ columns from the query
                                    $qty      = (float)$quantities[0];
                                    $fweight  = (float)($item['td_fweight'] ?? 0);
                                    $cbm      = (float)($item['td_cbm']     ?? 0);
                                    $rowTonnage = $fweight * $qty;
                                    $rowVolume  = $cbm     * $qty;
                                    $grandQty     += $qty;
                                    $grandTonnage += $rowTonnage;
                                    $grandVolume  += $rowVolume;
                                    ?>
                                    <tr>
                                        <td><?php echo $cnt++; ?></td>
                                        <td><strong style="color:#0000ff;"><?php echo htmlentities($icodes[0]); ?></strong></td>
                                        <td><?php echo htmlentities($item['td_tire_size'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlentities($item['td_brand']     ?? 'N/A'); ?></td>
                                        <td><?php echo htmlentities($item['td_colour']    ?? 'N/A'); ?></td>
                                        <td><?php echo htmlentities($item['td_type']      ?? 'N/A'); ?></td>
                                        <td><?php echo htmlentities($item['td_rim'] ?? '') ?: '—'; ?></td>
                                        <td><?php echo htmlentities($item['td_description'] ?? 'N/A'); ?></td>
                                        <td class="col-fweight"><?php echo $fweight > 0 ? number_format($fweight, 3) : '—'; ?></td>
                                        <td class="col-cbm"><?php     echo $cbm     > 0 ? number_format($cbm,     4) : '—'; ?></td>
                                        <td><strong><?php echo number_format($qty, 0); ?></strong></td>
                                        <td class="col-fweight"><?php echo $rowTonnage > 0 ? number_format($rowTonnage, 2) : '—'; ?></td>
                                        <td class="col-cbm"><?php     echo $rowVolume  > 0 ? number_format($rowVolume,  4) : '—'; ?></td>
                                    </tr>
                                    <?php
                                }
                            }

                            // ── Grand totals row ─────────────────────────────
                            ?>
                            <tr style="background:#F0F9F4;font-weight:700;border-top:2px solid #A8D5BC;">
                                <td colspan="10" style="text-align:right;padding-right:1.5rem;color:#1D6F42;font-size:0.9rem;text-transform:uppercase;letter-spacing:0.5px;">
                                    <i class="fas fa-sigma" style="margin-right:0.4rem;"></i> TOTALS
                                </td>
                                <td style="color:#1D6F42;"><?php echo number_format($grandQty, 0); ?></td>
                                <td class="col-fweight" style="color:#1D6F42;"><?php echo number_format($grandTonnage, 2); ?></td>
                                <td class="col-cbm"     style="color:#1D6F42;"><?php echo number_format($grandVolume,  4); ?></td>
                            </tr>
                            <?php

                        } else { ?>
                        <tr>
                            <td colspan="13">
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

    <?php
    // ── ERP Number Section — only when status is confirm_marketing ───────────
    if (strtolower($orderData['status']) === 'confirm_marketing'):
        $existingErp = $orderData['erp_number'] ?? '';
    ?>
    <div class="card erp-card" id="erpSection">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-barcode"></i> ERP Number
                <span class="status-badge status-confirm_marketing" style="font-size:0.75rem;padding:0.3rem 0.75rem;margin-left:0.5rem;">
                    Confirm Marketing
                </span>
            </h2>
        </div>
        <div class="card-body">
            <div class="erp-info-box">
                <i class="fas fa-exclamation-circle" style="color:#1a7f42;font-size:1.3rem;margin-top:0.1rem;flex-shrink:0;"></i>
                <p>
                    This order has been <strong>confirmed by Marketing</strong>. You must enter and save the
                    <strong>ERP Number</strong> before proceeding. This field is <strong>mandatory</strong>
                    — the form cannot be submitted without a valid ERP number.
                </p>
            </div>

            <?php if (!empty($existingErp) && !isset($_GET['edit_erp'])): ?>
            <div class="erp-saved-display">
                <i class="fas fa-check-circle" style="color:#1a7f42;font-size:1.4rem;flex-shrink:0;"></i>
                <div>
                    <div class="erp-saved-label">ERP Number saved</div>
                    <div class="erp-number-value"><?php echo htmlentities($existingErp); ?></div>
                </div>
                <a href="?id=<?php echo urlencode($orderId); ?>&edit_erp=1" class="erp-edit-link">
                    <i class="fas fa-pencil-alt"></i> Edit
                </a>
            </div>

            <?php else: ?>
            <form id="erpForm" novalidate>
                <div class="erp-input-row">
                    <div class="form-group">
                        <label class="form-label" for="erp_number">
                            ERP Number <span class="required-star">*</span>
                        </label>
                        <div style="position: relative;">
                            <input
                                type="text"
                                id="erp_number"
                                name="erp_number"
                                class="form-control erp-field"
                                placeholder="e.g. ERP-2024-00123"
                                value="<?php echo htmlentities($existingErp); ?>"
                                autocomplete="off"
                                oninput="validateErp()"
                                onblur="autoSaveErp()"
                            >
                            <div class="erp-auto-save-indicator" id="erpAutoSaveIndicator">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span id="erpAutoSaveText">Saving...</span>
                            </div>
                        </div>
                        <div class="erp-field-error" id="erpError">
                            <i class="fas fa-exclamation-circle"></i>
                            ERP Number is required. Please enter a valid ERP number.
                        </div>
                    </div>
                </div>
                <p class="redirect-note" style="margin-top:1rem;">
                    <i class="fas fa-lock"></i>
                    The ERP number will be automatically saved when you leave the field.
                </p>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Marketing Executive Comment Edit Card ─────────────────────────────── -->
    <?php
    $isConfirmMarketing = (strtolower($orderData['status']) === 'confirm_marketing');
    $erpSaved           = !empty(trim($orderData['erp_number'] ?? ''));
    $commentBtnLocked   = $isConfirmMarketing && !$erpSaved;
    ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-pen-square"></i> Edit Marketing Executive Comment
            </h2>
        </div>
        <div class="card-body">

            <?php if ($commentBtnLocked): ?>
            <div class="alert alert-danger" style="margin-bottom:1.5rem;">
                <i class="fas fa-lock"></i>
                <span>
                    <strong>Action locked.</strong> You must save an <strong>ERP Number</strong> (see the section above) before you can save the Marketing Executive comment.
                </span>
            </div>
            <?php endif; ?>

            <div class="auto-status-box">
                <i class="fas fa-magic" style="color:var(--primary-orange);font-size:1.3rem;margin-top:0.15rem;"></i>
                <div class="rule">
                    The order status will be set <strong>automatically</strong> when you save:<br>
                    • Comment <strong>filled in</strong> → status becomes <span class="pill pill-spt">Share Planning (Temporary)</span><br>
                    • Comment <strong>left empty</strong> → status becomes <span class="pill pill-cwmm">Confirm — Wait Marketing Manager</span>
                </div>
            </div>

            <form method="POST" action="" onsubmit="return checkCommentSubmit()">
                <div class="form-group">
                    <label class="form-label" for="marketing_executive_comment">Marketing Executive Comment</label>
                    <textarea
                        id="marketing_executive_comment"
                        name="marketing_executive_comment"
                        class="form-control"
                        rows="6"
                        placeholder="Enter Marketing Executive comment here… (leave blank to set status to Confirm — Wait Marketing Manager)"
                        oninput="updatePreview()"
                        <?php if ($commentBtnLocked) echo 'readonly style="opacity:0.6;cursor:not-allowed;"'; ?>
                    ><?php echo htmlentities($orderData['marketing_executive_comment'] ?? ''); ?></textarea>

                    <div id="statusWillBe" class="status-will-be"></div>
                </div>

                <?php if ($commentBtnLocked): ?>
                <div style="position:relative;display:inline-block;">
                    <button
                        type="button"
                        class="btn btn-primary"
                        style="opacity:0.45;cursor:not-allowed;filter:grayscale(0.3);"
                        onclick="showErpBlockedAlert()"
                        title="Enter ERP Number first"
                    >
                        <i class="fas fa-lock"></i> Save Comment &amp; Update Status
                    </button>
                </div>
                <p class="redirect-note" style="color:#e74c3c;margin-top:0.75rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    Please enter and save the ERP Number above before saving the comment.
                </p>
                <?php else: ?>
                <button type="submit" name="update_marketing_comment" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Comment &amp; Update Status
                </button>
                <p class="redirect-note">
                    <i class="fas fa-check-circle"></i>
                    Status will update automatically based on the comment above.
                </p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Order not found. Please check the order ID and try again.</span>
    </div>
    <?php endif; ?>

</div><!-- /.main-wrapper -->

<script>
    /* ── Marketing comment live preview ── */
    function updatePreview() {
        const comment = document.getElementById('marketing_executive_comment').value.trim();
        const box     = document.getElementById('statusWillBe');
        if (!box) return;

        if (comment.length > 0) {
            box.className = 'status-will-be spt';
            box.innerHTML = '<i class="fas fa-tag"></i> Status will be set to: <strong>Share Planning (Temporary)</strong>';
        } else {
            box.className = 'status-will-be cwmm';
            box.innerHTML = '<i class="fas fa-tag"></i> Status will be set to: <strong>Confirm — Wait Marketing Manager</strong>';
        }
    }

    /* ── Alert when locked Save Comment button is clicked ── */
    function showErpBlockedAlert() {
        const erpCard = document.getElementById('erpSection');
        if (erpCard) {
            erpCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            erpCard.style.transition = 'box-shadow 0.3s';
            erpCard.style.boxShadow  = '0 0 0 4px rgba(231,76,60,0.45), 0 0 0 8px rgba(231,76,60,0.15)';
            setTimeout(() => { erpCard.style.boxShadow = ''; }, 1800);
        }
        const erpInput = document.getElementById('erp_number');
        if (erpInput) {
            erpInput.focus();
            erpInput.classList.add('is-invalid');
            erpInput.style.animation = 'none';
            erpInput.offsetHeight;
            erpInput.style.animation = 'erpShake 0.4s ease';
            const errBox = document.getElementById('erpError');
            if (errBox) errBox.classList.add('visible');
        }
    }

    /* ── ERP validation ── */
    function validateErp() {
        const input  = document.getElementById('erp_number');
        const errBox = document.getElementById('erpError');
        if (!input) return true;

        if (input.value.trim().length === 0) {
            input.classList.add('is-invalid');
            if (errBox) errBox.classList.add('visible');
            return false;
        } else {
            input.classList.remove('is-invalid');
            if (errBox) errBox.classList.remove('visible');
            return true;
        }
    }

    /* ── Auto-save ERP on blur ── */
    function autoSaveErp() {
        if (!validateErp()) {
            const input = document.getElementById('erp_number');
            if (input) {
                input.style.animation = 'none';
                input.offsetHeight;
                input.style.animation = 'erpShake 0.4s ease';
            }
            return false;
        }

        const erpNumber = document.getElementById('erp_number').value.trim();
        const indicator = document.getElementById('erpAutoSaveIndicator');
        const indicatorText = document.getElementById('erpAutoSaveText');
        if (!erpNumber) return false;

        if (indicator) {
            indicator.classList.remove('saved');
            indicator.classList.add('show', 'saving');
            indicatorText.textContent = 'Saving...';
        }

        const formData = new FormData();
        formData.append('save_erp_number', '1');
        formData.append('erp_number', erpNumber);
        formData.append('ajax', '1');

        fetch(window.location.href, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (indicator) {
                if (data.success) {
                    indicator.classList.remove('saving');
                    indicator.classList.add('saved');
                    indicatorText.innerHTML = '<i class="fas fa-check-circle"></i> Saved!';
                    setTimeout(() => { indicator.classList.remove('show'); }, 2000);
                    const commentBtn = document.querySelector('button[name="update_marketing_comment"]');
                    if (commentBtn) { setTimeout(() => { location.reload(); }, 500); }
                } else {
                    indicator.classList.remove('saving');
                    indicatorText.textContent = 'Error!';
                    setTimeout(() => { indicator.classList.remove('show'); }, 3000);
                }
            }
        })
        .catch(() => {
            if (indicator) {
                indicator.classList.remove('saving');
                indicatorText.textContent = 'Error!';
                setTimeout(() => { indicator.classList.remove('show'); }, 3000);
            }
        });
        return false;
    }

    /* ── Excel download with spinner ── */
    function triggerDownload(e, link) {
        // Show the spinner overlay
        const overlay = document.getElementById('downloadOverlay');
        if (overlay) overlay.classList.add('show');

        // Hide overlay after a generous timeout (file will have downloaded by then)
        setTimeout(() => {
            if (overlay) overlay.classList.remove('show');
        }, 5000);

        // Allow the normal href navigation to proceed (triggers the download)
        return true;
    }

    /* ── Init on load ── */
    document.addEventListener('DOMContentLoaded', function () {
        updatePreview();
        const erpInput = document.getElementById('erp_number');
        if (erpInput) validateErp();
    });
</script>

<style>
    @keyframes erpShake {
        0%, 100% { transform: translateX(0); }
        20%       { transform: translateX(-6px); }
        40%       { transform: translateX(6px); }
        60%       { transform: translateX(-4px); }
        80%       { transform: translateX(4px); }
    }
</style>

</body>
</html>
<?php } ?>