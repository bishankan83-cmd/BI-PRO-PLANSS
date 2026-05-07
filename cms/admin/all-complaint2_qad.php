<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | All Tire Complaints</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --medium-gray: #343a40;
            --black: #000000;
            --red: #FF0000;
            --red-accent: #ff4757;
            --border-gray: #e0e0e0;
            --light-border: #CCCCCC;
            --bg-light: #f8fafc;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --ring-orange: 0 0 0 3px rgba(242, 128, 24, 0.2);
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

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .back-button:hover {
            background: var(--primary-orange);
            color: var(--white);
            border-color: var(--primary-orange);
            transform: translateX(-4px);
        }

        .page-header {
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

        /* Card */
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
            background: var(--orange-light);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-orange);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        /* Table */
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
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--border-gray);
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.2s;
            border-bottom: 1px solid var(--border-gray);
        }

        .table tbody tr:hover {
            background: var(--orange-light);
        }

        .table tbody td {
            padding: 1rem;
            color: var(--dark-gray);
            vertical-align: middle;
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

        .status-pending {
            background: var(--error-light);
            color: var(--error);
        }

        .status-in-process {
            background: var(--warning-light);
            color: var(--warning);
        }

        .status-closed {
            background: var(--success-light);
            color: var(--success);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--text-gray);
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-orange);
            opacity: 0.5;
        }

        .info-badge {
            background: var(--orange-light);
            color: var(--primary-orange);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }

            .btn {
                padding: 0.5rem 0.875rem;
                font-size: 0.75rem;
            }

            .back-button {
                padding: 0.625rem 1.25rem;
                font-size: 0.85rem;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: slideIn 0.6s ease-out forwards;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-orange);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <a href="qad_cms.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Manage Tire Complaints</h1>
            <p class="page-subtitle">View and manage all tire-related complaints submitted by users</p>
        </div>

        <!-- Complaints Table -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-list-alt"></i>
                    All Tire Complaints
                </h2>
            </div>
            <div class="card-body">
                <?php
                // Get admin ID from session
                $adminId = intval($_SESSION["aid"]);
                
                // Check if this admin ID exists as acm_ref in users table
                $checkAdminRefQuery = mysqli_query($con, "SELECT COUNT(*) as count FROM users WHERE acm_ref = '$adminId'");
                $checkResult = mysqli_fetch_array($checkAdminRefQuery);
                $isAccountManager = $checkResult['count'] > 0;
                
                // Display filter info if admin is an account manager
                if ($isAccountManager) {
                    echo '<div class="info-badge">
                            <i class="fas fa-filter"></i>
                            Showing complaints for customers under your management (ACM Ref: ' . htmlentities($adminId) . ')
                          </div>';
                }
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Complaint ID</th>
                                <th>Complainant Name</th>
                                <th>Tire Size</th>
                                <th>Reg Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Check if this admin is an account manager (admin.id exists in users.acm_ref)
                            if ($isAccountManager) {
                                // Query with ACM filter - only show complaints from users managed by this admin
                                $query = mysqli_query($con, "SELECT DISTINCT tbl_tire_complaints.*, users.fullName as name, users.cus_id
                                                            FROM tbl_tire_complaints 
                                                            JOIN users ON users.id = tbl_tire_complaints.userId 
                                                            WHERE users.acm_ref = '$adminId'
                                                            ORDER BY tbl_tire_complaints.created_at DESC");
                            } else {
                                // Query without ACM filter - show all complaints
                                $query = mysqli_query($con, "SELECT tbl_tire_complaints.*, users.fullName as name, users.cus_id
                                                            FROM tbl_tire_complaints 
                                                            JOIN users ON users.id = tbl_tire_complaints.userId 
                                                            ORDER BY tbl_tire_complaints.created_at DESC");
                            }
                            
                            $cnt = 1;
                            $hasRecords = false;
                            
                            if (mysqli_num_rows($query) > 0) {
                                $hasRecords = true;
                                while ($row = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td><strong><?php echo htmlentities($row['id']); ?></strong></td>
                                    <td><?php echo htmlentities($row['name']); ?></td>
                                    <td><?php echo htmlentities($row['tire_size'] ? $row['tire_size'] : 'N/A'); ?></td>
                                    <td><?php echo htmlentities(date('d M Y', strtotime($row['created_at']))); ?></td>
                                    <td>
                                        <?php
                                        $status = $row['status'];
                                        if ($status == '' || $status == null) {
                                            echo '<span class="status-badge status-pending">Not Processed Yet</span>';
                                        } elseif ($status == 'in process') {
                                            echo '<span class="status-badge status-in-process">In Process</span>';
                                        } elseif ($status == 'closed') {
                                            echo '<span class="status-badge status-closed">Closed</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="complaint-details_qad.php?cid=<?php echo htmlentities($row['id']); ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                    $cnt++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    
                    <?php if (!$hasRecords): ?>
                    <div class="no-data">
                        <i class="fas fa-inbox"></i>
                        <p>No complaints found<?php echo $isAccountManager ? ' for your managed customers.' : ' in the system.'; ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Log page load
        console.log('All Tire Complaints page loaded successfully');
        console.log('Total complaints displayed: <?php echo $cnt - 1; ?>');
        console.log('Account Manager Filter Active: <?php echo $isAccountManager ? "Yes (Admin ID: " . $adminId . ")" : "No"; ?>');
    </script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>