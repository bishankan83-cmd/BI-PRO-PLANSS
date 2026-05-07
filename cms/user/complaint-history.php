<?php
session_start();
include('include/config.php');

// Check if user is logged in
if (empty($_SESSION['id'])) {
    header('location:index.php');
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
    <title>Tire Complaint History</title>
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
            padding: 2rem;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
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

        .btn-back {
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-back:hover {
            background: var(--bg-light);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
            transform: translateX(-2px);
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
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

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
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

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .header-top {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 1rem;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .table thead th {
                font-size: 0.75rem;
                padding: 0.75rem;
            }

            .table tbody td {
                padding: 0.75rem;
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
    </style>
</head>
<body>
    <div class="page-header">
        <div class="header-top">
            <a href="dashboard.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <a href="register-complaint.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                New Complaint
            </a>
        </div>
        <h1 class="page-title">Tire Complaint History</h1>
        <p class="page-subtitle">View and manage all your submitted complaints</p>
    </div>

    <div class="card animate-in">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-history"></i>
                Your Complaints
            </h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Complaint ID</th>
                            <th>Complainant Name</th>
                            <th>Registration Date</th>
                            <th>Tire Serial</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $uid = $_SESSION['id'];
                        $stmt = $con->prepare("SELECT tbl_tire_complaints.*, users.fullName AS name FROM tbl_tire_complaints JOIN users ON users.id = tbl_tire_complaints.userId WHERE tbl_tire_complaints.userId = ? ORDER BY tbl_tire_complaints.created_at DESC");
                        $stmt->bind_param("i", $uid);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $cnt = 1;

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td><?php echo htmlentities($row['id']); ?></td>
                                    <td><?php echo htmlentities($row['name']); ?></td>
                                    <td><?php echo date('M j, Y \a\t g:i A', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo htmlentities($row['serial_number']); ?></td>
                                    <td>
                                        <?php
                                        $status = isset($row['status']) ? $row['status'] : '';
                                        if ($status == '') {
                                            echo '<span class="status-badge status-pending">Not Processed Yet</span>';
                                        } elseif ($status == 'in process') {
                                            echo '<span class="status-badge status-in-process">In Process</span>';
                                        } elseif ($status == 'closed') {
                                            echo '<span class="status-badge status-closed">Closed</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="complaint-details.php?cid=<?php echo htmlentities($row['id']); ?>" 
                                           class="btn btn-primary btn-sm">
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
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox empty-icon"></i>
                                        <h3 style="margin-bottom: 0.5rem; color: var(--dark-gray);">No complaints found</h3>
                                        <p>You haven't submitted any complaints yet.</p>
                                        <a href="register-complaint.php" class="btn btn-primary" style="margin-top: 1rem;">
                                            <i class="fas fa-plus"></i>
                                            File a Complaint
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Animate elements on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.card').forEach(el => {
            observer.observe(el);
        });

        console.log('Tire Complaint History Page loaded successfully!');
    </script>
</body>
</html>