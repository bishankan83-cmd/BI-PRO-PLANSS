<?php
session_start();
include('include/config.php');
if (empty($_SESSION['aid'])) {
    header('location:index.php');
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

// Fetch admin details
$adminId = intval($_SESSION['aid']);
$adminData = null;
$adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
if ($adminQuery && mysqli_num_rows($adminQuery) > 0) {
    $adminData = mysqli_fetch_array($adminQuery);
} else {
    $_SESSION['error'] = "Unable to fetch admin details. Please check your session or database.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Inprocess Tire Complaints</title>
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
            --gradient-2: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --gradient-4: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title-section {
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

        .header-actions {
            display: flex;
            gap: 1rem;
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
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--medium-gray);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-secondary:hover {
            background: var(--dark-gray);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, rgba(242, 128, 24, 0.05) 0%, rgba(230, 126, 34, 0.05) 100%);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            vertical-align: middle;
        }

        .table th {
            background: var(--medium-gray);
            color: var(--white);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table td {
            border-bottom: 1px solid var(--border-gray);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background: var(--bg-light);
        }

        .table-hover tbody tr:hover {
            background: var(--light-gray);
            transition: background 0.2s;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
        }

        .badge-danger {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }

        .badge-warning {
            background: var(--warning-light);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .badge-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
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
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }

        .alert-dismissible {
            padding-right: 3rem;
        }

        .close {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
            transition: opacity 0.2s;
        }

        .close:hover {
            opacity: 1;
        }

        .mt-3 {
            margin-top: 1.5rem;
        }

        .text-muted {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-actions {
                flex-direction: column;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

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
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1 class="page-title">Inprocess Tire Complaints</h1>
                <p class="page-subtitle">View and manage all tire complaints currently in process.</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible animate-in">
                <i class="fas fa-exclamation-circle"></i>
                <span><strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); ?></span>
                <button type="button" class="close" aria-label="Close" onclick="this.parentElement.style.display='none'">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php $_SESSION['error'] = ''; ?>
            </div>
        <?php endif; ?>

        <!-- Complaints Table Card -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-spinner"></i>
                    Inprocess Complaints
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">Complaint ID</th>
                                <th width="20%">User Name</th>
                                <th width="15%">Serial Number</th>
                                <th width="15%">Tire Size</th>
                                <th width="15%">Reg Date</th>
                                <th width="15%">Status</th>
                                <th width="20%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $st = 'in process';
                            $query = mysqli_query($con, "SELECT tbl_tire_complaints.*, users.fullName AS name 
                                FROM tbl_tire_complaints 
                                JOIN users ON users.id = tbl_tire_complaints.userId 
                                WHERE tbl_tire_complaints.status='$st'");
                            $cnt = 1;
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlentities($cnt); ?></strong></td>
                                    <td><?php echo htmlentities($row['id']); ?></td>
                                    <td><?php echo htmlentities($row['name']); ?></td>
                                    <td><?php echo htmlentities($row['serial_number']); ?></td>
                                    <td><?php echo htmlentities($row['tire_size']); ?></td>
                                    <td><?php echo htmlentities($row['created_at']); ?></td>
                                    <td>
                                        <?php 
                                        $status = $row['status'];
                                        if ($status == ''): ?>
                                            <span class="badge badge-danger">Not Processed Yet</span>
                                        <?php elseif ($status == 'in process'): ?>
                                            <span class="badge badge-warning">In Process</span>
                                        <?php elseif ($status == 'closed'): ?>
                                            <span class="badge badge-success">Closed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        
                                        <a href="complaint-details_qad.php?cid=<?php echo htmlentities($row['id']); ?>" 
                                           class="btn btn-secondary btn-sm">
                                           <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                    $cnt++;
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <h3>No in-process complaints found.</h3>
                                        <p>Check other complaint statuses or wait for new submissions.</p>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <div class="mt-3">
                        <small class="text-muted">
                            Total Inprocess Complaints: <strong><?php echo mysqli_num_rows($query); ?></strong>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Popup window function
        let popUpWin = null;
        function popUpWindow(URLStr, left, top, width, height) {
            if (popUpWin && !popUpWin.closed) {
                popUpWin.close();
            }
            popUpWin = window.open(URLStr, 'popUpWin', 
                `toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width=${width},height=${height},left=${left},top=${top},screenX=${left},screenY=${top}`);
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert) => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });

        // Add animation delays for cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    </script>
</body>
</html>
<?php mysqli_close($con); ?>