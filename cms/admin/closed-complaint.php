<?php
session_start();
include('include/config.php');
error_reporting(0);
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
    <title>CMS | Closed Tire Complaints</title>
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

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 600;
            border: 1px solid var(--border-gray);
            transition: all 0.2s;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .back-button:hover {
            background: var(--primary-orange);
            color: var(--white);
            border-color: var(--primary-orange);
            transform: translateX(-4px);
            box-shadow: var(--shadow-md);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 1.1rem;
        }

        /* Card */
        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            background: linear-gradient(to right, var(--orange-light), transparent);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-title i {
            color: var(--primary-orange);
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
            font-size: 0.95rem;
        }

        .table thead th {
            background: var(--bg-light);
            color: var(--dark-gray);
            font-weight: 700;
            padding: 1.25rem 1rem;
            text-align: left;
            border-bottom: 2px solid var(--border-gray);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
        }

        .table tbody tr {
            transition: all 0.2s;
            border-bottom: 1px solid var(--border-gray);
        }

        .table tbody tr:hover {
            background: var(--orange-light);
            transform: scale(1.01);
            box-shadow: var(--shadow-sm);
        }

        .table tbody td {
            padding: 1.25rem 1rem;
            color: var(--dark-gray);
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: 0.05em;
        }

        .status-badge i {
            font-size: 0.7rem;
        }

        .status-pending {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }

        .status-in-process {
            background: var(--warning-light);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .status-closed {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        /* Buttons */
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

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-gray);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--border-gray);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .page-subtitle {
                font-size: 0.95rem;
            }

            .card-header,
            .card-body {
                padding: 1.25rem;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }

            .btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }

            .back-button {
                padding: 0.6rem 1.25rem;
                font-size: 0.9rem;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
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
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-light);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-orange);
        }

        /* Complaint ID Highlight */
        .complaint-id {
            font-weight: 700;
            color: var(--primary-orange);
            font-family: 'Courier New', monospace;
        }

        /* Serial Number */
        .serial-number {
            font-weight: 600;
            color: var(--text-gray);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <a href="dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <h1 class="page-title">Closed Tire Complaints</h1>
            <p class="page-subtitle">View and manage all closed tire-related complaints</p>
        </div>

        <!-- Complaints Table -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-check-circle"></i>
                    Closed Complaints List
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Complaint ID</th>
                                <th>Complainant Name</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $st = 'closed';
                            $query = mysqli_query($con, "SELECT tbl_tire_complaints.*, users.fullName as name FROM tbl_tire_complaints JOIN users ON users.id=tbl_tire_complaints.userId WHERE tbl_tire_complaints.status='$st'");
                            $cnt = 1;
                            $rowCount = mysqli_num_rows($query);
                            
                            if ($rowCount > 0) {
                                while ($row = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td class="serial-number"><?php echo htmlentities($cnt); ?></td>
                                    <td class="complaint-id">#<?php echo htmlentities($row['id']); ?></td>
                                    <td><?php echo htmlentities($row['name']); ?></td>
                                    <td><?php echo htmlentities($row['created_at']); ?></td>
                                    <td>
                                        <?php
                                        $status = $row['status'];
                                        if ($status == '' || $status == null) {
                                            echo '<span class="status-badge status-pending"><i class="fas fa-clock"></i> Not Processed Yet</span>';
                                        } elseif ($status == 'in process') {
                                            echo '<span class="status-badge status-in-process"><i class="fas fa-spinner"></i> In Process</span>';
                                        } elseif ($status == 'closed') {
                                            echo '<span class="status-badge status-closed"><i class="fas fa-check-circle"></i> Closed</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" 
                                           onClick="popUpWindow('complaint-details.php?cid=<?php echo htmlentities($row['id']); ?>', 200, 200, 600, 600);" 
                                           class="btn btn-primary">
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
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <h3>No Closed Complaints Found</h3>
                                            <p>There are currently no closed tire complaints in the system.</p>
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

    <script>
        // Popup window function
        var popUpWin = 0;
        function popUpWindow(URLStr, left, top, width, height) {
            if (popUpWin) {
                if (!popUpWin.closed) popUpWin.close();
            }
            popUpWin = open(URLStr, 'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',screenX=' + left + ',screenY=' + top);
        }

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>