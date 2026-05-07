<?php
session_start();
include('include/config.php');

// Check if user is logged in
if (empty($_SESSION['id'])) {
    header('location:index.php');
    exit();
}

// Get and validate the order ID from URL
$oid = isset($_GET['oid']) ? intval($_GET['oid']) : 0;
if ($oid <= 0) {
    header('location:dashboard.php');
    exit();
}

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

// Fetch user data
$userId = $_SESSION['id'];
$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
$userData = mysqli_fetch_assoc($queryUser);
if (!$userData) {
    header('location:index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Share Planning</title>
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
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --info: #3498db;
            --info-light: rgba(52, 152, 219, 0.1);
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
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border: 1px solid var(--border-gray);
            background: var(--white);
            color: var(--text-gray);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 1.1rem;
        }

        .back-btn:hover {
            background: var(--orange-light);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
            transform: translateX(-2px);
        }

        .page-header-content {
            flex: 1;
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
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #229954 100%);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-disabled {
            background: var(--light-gray);
            color: var(--text-gray);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-disabled:hover {
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .order-id-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--orange-light);
            color: var(--primary-orange);
            border: 1px solid var(--primary-orange);
            border-radius: 0.5rem;
            padding: 0.3rem 0.75rem;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .card-body {
            padding: 2rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        .table thead th {
            background: var(--bg-light);
            color: var(--text-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border-gray);
            text-align: left;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.2s;
        }

        .table tbody tr:hover {
            background: var(--orange-light);
        }

        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-gray);
            color: var(--dark-gray);
        }

        .item-codes {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.8;
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
            white-space: nowrap;
        }

        .status-share-planning  { background: var(--info-light);    color: var(--info);    }
        .status-pending         { background: var(--warning-light);  color: var(--warning); }
        .status-approved        { background: var(--success-light);  color: var(--success); }
        .status-rejected        { background: var(--error-light);    color: var(--error);   }
        .status-default         { background: var(--light-gray);     color: var(--text-gray); }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-gray);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .alert-warning {
            background: var(--warning-light);
            color: var(--warning);
            border: 1px solid rgba(243, 156, 18, 0.3);
        }

        @media (max-width: 768px) {
            body { padding: 1rem; }

            .page-header { gap: 1rem; }

            .page-title { font-size: 1.5rem; }

            .page-subtitle { font-size: 0.9rem; }

            .card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .table-responsive { font-size: 0.85rem; }

            .table thead th {
                font-size: 0.75rem;
                padding: 0.75rem;
            }

            .table tbody td { padding: 0.75rem; }

            .action-buttons { flex-direction: column; }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: slideIn 0.6s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <a href="view_order.php" class="back-btn" title="Back to Order History">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="page-header-content">
            <h1 class="page-title">Revise Order</h1>
            <p class="page-subtitle">Viewing details for a specific Share Planning order</p>
        </div>
    </div>

    <div class="card animate-in">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-share-nodes"></i>
                Share Planning Order
            </h2>
            <span class="order-id-badge">
                <i class="fas fa-hashtag"></i>
                Order ID: <?= htmlentities($oid) ?>
            </span>
            <a href="add_order.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                New Order
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Order Date</th>
                            <th>Item Codes</th>
                            <th>Total Items</th>
                            <th>Total Quantity</th>
                            <th>Request Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody">
                        <?php
                        $uid = $_SESSION['id'];

                        // Query filtered by both customer_id (session) AND specific order_id from URL
                        $stmt = $con->prepare("
                            SELECT tor.*, u.fullName AS name, GROUP_CONCAT(ti.icode ORDER BY ti.icode ASC SEPARATOR ',') AS item_codes
                            FROM tire_orders tor
                            JOIN users u ON u.id = tor.customer_id
                            LEFT JOIN tire_order_items ti ON ti.order_id = tor.order_id
                            WHERE tor.customer_id = ?
                              AND tor.order_id = ?
                            GROUP BY tor.order_id
                            ORDER BY tor.order_date DESC
                        ");

                        if (!$stmt) {
                            die("Prepare failed: " . $con->error);
                        }

                        $stmt->bind_param("ii", $uid, $oid);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $cnt = 1;

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):

                                // Format item codes — 4 per line
                                $itemCodes = $row['item_codes'] ?: 'N/A';
                                $formattedCodes = 'N/A';
                                if ($itemCodes !== 'N/A') {
                                    $codesArray = explode(',', $itemCodes);
                                    $chunks = array_chunk($codesArray, 4);
                                    $lines = array_map(fn($c) => implode(', ', $c), $chunks);
                                    $formattedCodes = implode('<br>', $lines);
                                }

                                // Request status
                                $requestStatus  = $row['request_status'] ?? '';
                                $isAccepted     = ($requestStatus === 'accepted');

                                // Badge class for request status
                                $badgeClass = 'status-default';
                                if ($requestStatus === 'accepted')  $badgeClass = 'status-approved';
                                elseif ($requestStatus === 'rejected') $badgeClass = 'status-rejected';
                                elseif ($requestStatus === 'pending')  $badgeClass = 'status-pending';
                        ?>
                                <tr data-order-id="<?= htmlentities($row['order_id']) ?>">
                                    <td><?= $cnt ?></td>
                                    <td><strong><?= htmlentities($row['order_id']) ?></strong></td>
                                    <td><?= htmlentities($row['name']) ?></td>
                                    <td><?= date('M j, Y \a\t g:i A', strtotime($row['order_date'])) ?></td>
                                    <td><div class="item-codes"><?= $formattedCodes ?></div></td>
                                    <td><?= htmlentities($row['total_items']) ?></td>
                                    <td><?= htmlentities($row['total_quantity']) ?></td>
                                    <td>
                                        <?php if ($requestStatus): ?>
                                            <span class="status-badge <?= $badgeClass ?>">
                                                <?= htmlentities(str_replace('_', ' ', ucfirst($requestStatus))) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-default">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="get_request.php?oid=<?= htmlentities($row['order_id']) ?>"
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-paper-plane"></i> Get Request
                                            </a>
                                            <?php if ($isAccepted): ?>
                                                <a href="edit_revise.php?oid=<?= htmlentities($row['order_id']) ?>"
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-disabled" disabled
                                                        title="Only available for accepted requests">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                                $cnt++;
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fas fa-search empty-icon"></i>
                                        <h3 style="margin-bottom:0.5rem; color:var(--dark-gray);">Order not found</h3>
                                        <p>No order found with ID <strong>#<?= htmlentities($oid) ?></strong> for your account.</p>
                                        <a href="view_order.php" class="btn btn-primary" style="margin-top:1rem;">
                                            <i class="fas fa-arrow-left"></i> Back to Order History
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endif;
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Intersection observer for animate-in
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('animate-in'); });
        }, { threshold: 0.1 });

        document.querySelectorAll('.card').forEach(el => observer.observe(el));

        console.log('Revise Order Page loaded for Order ID: <?= $oid ?>');
    </script>
</body>
</html>