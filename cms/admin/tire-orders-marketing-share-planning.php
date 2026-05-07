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

    // === Handle both statuses ===
    $statusFilter = " AND o.status IN ('confirm_marketing', 'confirm_wait_marketing_man')";
    $pageTitle = 'Account Manager Successfully Confirmed PI';
    $pageSubtitle = 'Customer has confirmed order';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planatir CMS | <?php echo htmlentities($pageTitle); ?></title>
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
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
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

        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .page-title { font-size: 2rem; font-weight: 800; color: var(--dark-gray); }
        .page-subtitle { color: var(--text-gray); font-size: 1rem; margin-top: 0.25rem; }

        .card { background: var(--white); border-radius: 1rem; border: 1px solid var(--border-gray); overflow: hidden; box-shadow: var(--shadow-sm); }
        .card-header {
            padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-gray);
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
            background: #fdfdfd;
        }
        .card-title { font-size: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; }
        .info-badge { padding: 0.5rem 1rem; background: var(--orange-light); color: var(--primary-orange); border-radius: 0.5rem; font-weight: 600; }

        .card-body { padding: 2rem; }
        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border-gray); }
        .table th { font-weight: 600; color: var(--text-gray); text-transform: uppercase; font-size: 0.85rem; }
        .table tbody tr:hover { background: var(--orange-light); }

        /* Status badges */
        .status-badge {
            padding: 0.4rem 0.9rem; border-radius: 0.6rem; font-size: 0.75rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            display: inline-block;
        }
        .status-confirm-marketing {
            background: #fff3cd; color: #856404; border: 1px solid #ffeaa7;
        }
        .status-confirm-wait {
            background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;
        }

        .items-list { font-size: 0.88rem; line-height: 1.5; color: var(--text-gray); }
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

        /* Action buttons for confirm_wait_marketing_man */
        .btn-approve {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }
        .btn-approve:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-reject {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            margin-top: 0.4rem;
        }
        .btn-reject:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }

        .action-group { display: flex; flex-direction: column; gap: 0.4rem; }

        .no-data { text-align: center; padding: 4rem 2rem; color: var(--text-gray); }
        .no-data i { font-size: 4rem; opacity: 0.2; margin-bottom: 1rem; display: block; }

        /* Alert messages */
        .alert {
            padding: 1rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 0.75rem; font-weight: 500;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger  { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        @media (max-width: 768px) {
            .header, .main-wrapper { padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; }
            .card-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">

    <a href="Marketing.php" class="btn btn-back">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <?php
    // ============================================================
    // Handle Approve / Reject for confirm_wait_marketing_man
    // ============================================================
    if (isset($_POST['action']) && isset($_POST['order_id'])) {
        $orderId = intval($_POST['order_id']);
        $action  = $_POST['action'];

        if ($action === 'approve') {
            // Move to the next status after marketing manager approves
            $newStatus = 'confirm_marketing_man'; // adjust to your actual next status
            $updateQ = mysqli_query($con, "UPDATE tire_orders SET status='$newStatus' WHERE order_id='$orderId'");
            if ($updateQ) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Order #' . $orderId . ' approved successfully.</div>';
            } else {
                echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Failed to approve order. Please try again.</div>';
            }
        } elseif ($action === 'reject') {
            $newStatus = 'rejected_marketing_man'; // adjust to your actual reject status
            $updateQ = mysqli_query($con, "UPDATE tire_orders SET status='$newStatus' WHERE order_id='$orderId'");
            if ($updateQ) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Order #' . $orderId . ' rejected successfully.</div>';
            } else {
                echo '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Failed to reject order. Please try again.</div>';
            }
        }
    }
    ?>

    <div class="page-header">
        <div>
            <h1 class="page-title"><?php echo htmlentities($pageTitle); ?></h1>
            <p class="page-subtitle"><?php echo htmlentities($pageSubtitle); ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-clipboard-check"></i> <?php echo htmlentities($pageTitle); ?>
            </h2>
            <div class="info-badge">
                Total:
                <?php
                $countQuery  = "SELECT COUNT(*) as t FROM tire_orders o WHERE 1=1 $statusFilter";
                $totalResult = mysqli_query($con, $countQuery);
                $total       = $totalResult ? mysqli_fetch_assoc($totalResult)['t'] : 0;
                echo $total;
                ?>
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
                            <th>Date &amp; Time</th>
                            <th>Items (Code × Qty)</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($con, "
                            SELECT o.*,
                                   GROUP_CONCAT(CONCAT(ti.icode, ' × ', ti.quantity) SEPARATOR ' | ') as items_list
                            FROM tire_orders o
                            LEFT JOIN tire_order_items ti ON o.order_id = ti.order_id
                            WHERE 1=1
                            $statusFilter
                            GROUP BY o.order_id
                            ORDER BY o.order_date DESC
                        ");

                        $cnt = 1;
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_array($query)) {
                                $isWaiting = ($row['status'] === 'confirm_wait_marketing_man');
                        ?>
                            <tr>
                                <td><?php echo $cnt++; ?></td>
                                <td><strong>#<?php echo htmlentities($row['order_id']); ?></strong></td>
                                <td><?php echo htmlentities($row['customer_id']); ?></td>
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
                                <td><strong><?php echo htmlentities($row['total_quantity']); ?></strong></td>
                                <td>
                                    <?php if ($isWaiting): ?>
                                        <span class="status-badge status-confirm-wait">
                                            <i class="fas fa-hourglass-half"></i> Waiting Marketing Manager
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-confirm-marketing">
                                            <i class="fas fa-check"></i> Account Manager Confirmed PI
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo nl2br(htmlentities($row['order_notes'] ?: '-')); ?></small></td>
                                <td>
                                    <div class="action-group">
                                        <!-- Always show View button -->
                                        <a href="view-tire-orders-pi-mar.php?id=<?php echo $row['order_id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>

                                        <?php if ($isWaiting): ?>
                                            <!-- Approve button -->
                                            <form method="POST" style="margin:0;" onsubmit="return confirm('Approve Order #<?php echo $row['order_id']; ?>?');">
                                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                <input type="hidden" name="action"   value="approve">
                                                <button type="submit" class="btn btn-approve">
                                                    <i class="fas fa-check-circle"></i> Approve
                                                </button>
                                            </form>
                                            <!-- Reject button -->
                                            <form method="POST" style="margin:0;" onsubmit="return confirm('Reject Order #<?php echo $row['order_id']; ?>?');">
                                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                <input type="hidden" name="action"   value="reject">
                                                <button type="submit" class="btn btn-reject">
                                                    <i class="fas fa-times-circle"></i> Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="9">
                                    <div class="no-data">
                                        <i class="fas fa-inbox"></i>
                                        <h3>No Orders Found</h3>
                                        <p>There are no orders with confirmed PI or waiting marketing manager status.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<?php } ?>