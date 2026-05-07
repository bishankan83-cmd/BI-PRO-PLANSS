<?php
session_start();
include('include/config.php');

if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // Fetch admin details
    $adminId = intval($_SESSION["aid"]);
    $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
    $adminData = mysqli_fetch_array($adminQuery);

    // Get allowed customers based on acm_ref = admin ID
    $adminAcmRef = $adminId;
    $customerQuery = mysqli_query($con, "SELECT cus_id FROM users WHERE acm_ref='$adminAcmRef'");
    $allowedCustomerIds = [];
    while ($customer = mysqli_fetch_array($customerQuery)) {
        if (!empty($customer['cus_id'])) {
            $allowedCustomerIds[] = intval($customer['cus_id']);
        }
    }

    // Build safe customer ID list
    $customerIdList = !empty($allowedCustomerIds) ? implode(',', $allowedCustomerIds) : '0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planatir CMS | All Revision Requests</title>
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
            --danger-light: rgba(231, 76, 60, 0.1);
            --info-light: rgba(52, 152, 219, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-danger: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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

        .header {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }

        .logo-section { display: flex; align-items: center; gap: 1rem; }
        .brand-text {
            font-size: 1.5rem; font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-actions { display: flex; align-items: center; gap: 1rem; }
        .user-btn {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 1rem;
            background: var(--orange-light); border-radius: 2rem; color: var(--dark-gray);
        }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--gradient-1); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.9rem;
        }

        .main-wrapper { min-height: calc(100vh - 80px); padding: 2rem; }

        .page-header { 
            display: flex; justify-content: space-between; align-items: flex-start; 
            margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; 
        }
        .page-title { 
            font-size: 2rem; font-weight: 800; color: var(--dark-gray);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .page-title i { color: #e74c3c; }
        .page-subtitle { color: var(--text-gray); font-size: 1rem; margin-top: 0.25rem; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-gray);
            box-shadow: var(--shadow-sm);
        }

        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-card.danger .stat-icon {
            background: var(--danger-light);
            color: #e74c3c;
        }

        .stat-card.info .stat-icon {
            background: var(--info-light);
            color: #3498db;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark-gray);
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .card { background: var(--white); border-radius: 1rem; border: 1px solid var(--border-gray); overflow: hidden; box-shadow: var(--shadow-sm); }
        .card-header {
            padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-gray);
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
            background: linear-gradient(to bottom, #fff, #fafafa);
        }
        .card-title { 
            font-size: 1.3rem; font-weight: 700; 
            display: flex; align-items: center; gap: 0.75rem; 
        }
        .info-badge { 
            padding: 0.5rem 1rem; 
            background: var(--danger-light); 
            color: #e74c3c; 
            border-radius: 0.5rem; 
            font-weight: 600; 
            border: 1px solid #e74c3c;
        }

        .card-body { padding: 2rem; }
        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-gray); }
        .table th { 
            font-weight: 600; color: var(--text-gray); 
            text-transform: uppercase; font-size: 0.85rem;
            background: #fafafa;
        }
        .table tbody tr:hover { background: var(--danger-light); }
        .table tbody tr { transition: all 0.2s; }

        .status-badge {
            padding: 0.4rem 0.9rem; border-radius: 0.6rem; font-size: 0.75rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .status-cus_confirmed { background: #d1ecf1; color: #0d6efd; border: 1px solid #a0d8e8; }
        .status-cus_pi_confirm { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .status-confirm_marketing { background: var(--success-light); color: #27ae60; border: 1px solid #27ae60; }
        .status-request_revise { background: var(--danger-light); color: #e74c3c; border: 1px solid #e74c3c; }

        .items-list { font-size: 0.88rem; line-height: 1.8; color: var(--text-gray); }
        .items-list div { 
            padding: 0.25rem 0; 
            border-bottom: 1px dashed #eee;
        }
        .items-list div:last-child { border-bottom: none; }
        .items-list strong { color: var(--dark-gray); font-weight: 600; }

        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem;
            border: none; border-radius: 0.75rem; font-weight: 600; font-size: 0.85rem;
            cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .btn-primary { background: var(--gradient-1); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-back { background: var(--light-gray); color: var(--dark-gray); margin-bottom: 1rem; }
        .btn-back:hover { background: var(--border-gray); }

        .no-data { text-align: center; padding: 4rem 2rem; color: var(--text-gray); }
        .no-data i { font-size: 4rem; opacity: 0.2; margin-bottom: 1rem; display: block; }

        .alert-info {
            padding: 1rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem;
            background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;
            display: flex; align-items: center; gap: 0.75rem;
        }

        .alert-warning {
            padding: 1rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem;
            background: var(--danger-light); color: #c0392b; border: 2px solid #e74c3c;
            display: flex; align-items: center; gap: 0.75rem;
            font-weight: 600;
        }

        .order-id-cell {
            font-weight: 700;
            color: var(--primary-orange);
        }

        .highlight-row {
            background: #fff5f5 !important;
        }

        @media (max-width: 768px) {
            .header, .main-wrapper { padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; }
            .card-header { flex-direction: column; align-items: flex-start; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <a href="account-manager-dashboard.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    All Revision Requests
                </h1>
                <p class="page-subtitle">All orders requiring revisions from your assigned customers</p>
            </div>
        </div>

        <div class="alert-warning">
            <i class="fas fa-info-circle"></i>
            <span><strong>Showing ALL orders with request_status = 'request_revise'</strong> across all statuses</span>
        </div>

        <?php if (empty($allowedCustomerIds)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>No customers are assigned to your account. Contact the system administrator.</span>
            </div>
        <?php endif; ?>

        <?php
        // Calculate statistics
        $totalRevisions = 0;
        $statusBreakdown = [];
        
        if (!empty($allowedCustomerIds)) {
            // Total count
            $countQuery = "SELECT COUNT(*) as t FROM tire_orders WHERE customer_id IN ($customerIdList) AND request_status = 'request_revise'";
            $totalResult = mysqli_query($con, $countQuery);
            $totalRevisions = $totalResult ? mysqli_fetch_assoc($totalResult)['t'] : 0;
            
            // Status breakdown
            $breakdownQuery = "SELECT status, COUNT(*) as count FROM tire_orders WHERE customer_id IN ($customerIdList) AND request_status = 'request_revise' GROUP BY status";
            $breakdownResult = mysqli_query($con, $breakdownQuery);
            while ($row = mysqli_fetch_assoc($breakdownResult)) {
                $statusBreakdown[$row['status']] = $row['count'];
            }
        }
        ?>

      

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-list"></i> All Orders Requiring Revision
                </h2>
                <div class="info-badge">
                    <i class="fas fa-edit"></i> Total: <?php echo $totalRevisions; ?>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order ID</th>
                                <th>Customer ID</th>
                                <th>Order Status</th>
                                <th>Date & Time</th>
                                <th>Items (Code × Qty)</th>
                                <th>Total Qty</th>
                                <th>Request Status</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($allowedCustomerIds)) {
                                $query = mysqli_query($con, "
                                    SELECT o.*, 
                                           GROUP_CONCAT(CONCAT(ti.icode, ' × ', ti.quantity) SEPARATOR ' | ') as items_list
                                    FROM tire_orders o
                                    LEFT JOIN tire_order_items ti ON o.order_id = ti.order_id
                                    WHERE o.customer_id IN ($customerIdList)
                                    AND o.request_status = 'request_revise'
                                    GROUP BY o.order_id
                                    ORDER BY o.order_date DESC
                                ");

                                $cnt = 1;
                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                        // Determine order status display
                                        $orderStatus = $row['status'];
                                        $statusClass = 'status-' . str_replace('_', '', $orderStatus);
                                        $statusLabel = '';
                                        
                                        switch($orderStatus) {
                                            case 'cus_confirmed':
                                                $statusLabel = 'Customer Confirmed';
                                                break;
                                            case 'cus_pi_confirm':
                                                $statusLabel = 'PI Confirmed';
                                                break;
                                            case 'confirm_marketing':
                                                $statusLabel = 'Marketing Confirmed';
                                                break;
                                            default:
                                                $statusLabel = ucwords(str_replace('_', ' ', $orderStatus));
                                        }
                            ?>
                                    <tr class="highlight-row">
                                        <td><?php echo $cnt++; ?></td>
                                        <td class="order-id-cell">#<?php echo htmlentities($row['order_id']); ?></td>
                                        <td><strong><?php echo htmlentities($row['customer_id']); ?></strong></td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo htmlentities($statusLabel); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($row['order_date'])); ?><br>
                                            <small style="color:#888;"><?php echo date('h:i A', strtotime($row['order_date'])); ?></small>
                                        </td>
                                        <td class="items-list">
                                            <?php 
                                            if ($row['items_list']) {
                                                $items = explode(' | ', $row['items_list']);
                                                foreach ($items as $item) {
                                                    echo "<div><strong>" . htmlentities($item) . "</strong></div>";
                                                }
                                            } else {
                                                echo "<em>No items</em>";
                                            }
                                            ?>
                                        </td>
                                        <td><strong style="font-size: 1.1rem;"><?php echo htmlentities($row['total_quantity']); ?></strong></td>
                                        <td>
                                            <span class="status-badge status-request_revise">
                                                <i class="fas fa-edit"></i> Revision Requested
                                            </span>
                                        </td>
                                        <td style="max-width: 200px;">
                                            <small><?php echo nl2br(htmlentities($row['order_notes'] ?: '-')); ?></small>
                                        </td>
                                        <td>
                                            <a href="tire-order-rev.php?oid=<?php echo $row['order_id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                            <?php
                                    }
                                } else {
                            ?>
                                    <tr>
                                        <td colspan="10">
                                            <div class="no-data">
                                                <i class="fas fa-check-circle"></i>
                                                <h3>No Revision Requests Found</h3>
                                                <p>Great! There are no orders requiring revisions at this time.</p>
                                            </div>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="10">
                                        <div class="no-data">
                                            <i class="fas fa-users-slash"></i>
                                            <h3>No Customers Assigned</h3>
                                            <p>Contact admin to get customers assigned to your account.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php } ?>



