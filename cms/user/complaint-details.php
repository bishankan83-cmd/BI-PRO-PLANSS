<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['id']) == 0) {
    header('location:index.php');
    exit;
} else {
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
    <title>Tire Complaint Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
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
            gap: 1rem;
        }

        .header-buttons {
            display: flex;
            gap: 0.75rem;
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

        .btn-secondary {
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-secondary:hover {
            background: var(--bg-light);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
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
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            border: 1px solid var(--border-gray);
            text-align: left;
            vertical-align: top;
        }

        .table th {
            background: var(--orange-light);
            color: var(--primary-orange);
            font-weight: 600;
            width: 200px;
        }

        .table td {
            background: var(--white);
            color: var(--dark-gray);
        }

        .table tr:nth-child(even) td {
            background: var(--bg-light);
        }

        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-danger {
            background: var(--error-light);
            color: var(--error);
        }

        .badge-warning {
            background: var(--warning-light);
            color: var(--warning);
        }

        .badge-success {
            background: var(--success-light);
            color: var(--success);
        }

        .view-file-link {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-file-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .header-top {
                flex-direction: column;
                align-items: stretch;
            }

            .header-buttons {
                flex-direction: column;
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

            .table th,
            .table td {
                font-size: 0.85rem;
                padding: 0.75rem;
            }

            .table th {
                width: auto;
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
                Back to Dashboard
            </a>
            <div class="header-buttons">
                <a href="complaint-history.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    View All Complaints
                </a>
            </div>
        </div>
        <h1 class="page-title">Tire Complaint Details</h1>
        <p class="page-subtitle">View detailed information about your tire complaint</p>
    </div>

    <div class="card animate-in">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-file-alt"></i>
                Complaint Information
            </h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <?php 
                        $cid = $_GET['cid'];
                        $query = mysqli_query($con, "SELECT tbl_tire_complaints.*, users.fullName as name 
                            FROM tbl_tire_complaints 
                            JOIN users ON users.id = tbl_tire_complaints.userId 
                            WHERE tbl_tire_complaints.id = '$cid'");
                        while ($row = mysqli_fetch_array($query)) {
                        ?>
                        <tr>
                            <th>Complaint ID</th>
                            <td><?php echo htmlentities($row['id']); ?></td>
                            <th>Complainant Name</th>
                            <td><?php echo htmlentities($row['name']); ?></td>
                            <th>Registration Date</th>
                            <td><?php echo htmlentities($row['created_at']); ?></td>
                        </tr>
                        <tr>
                            <th>Serial Number</th>
                            <td><?php echo htmlentities($row['serial_number']); ?></td>
                            <th>Tire Size</th>
                            <td><?php echo htmlentities($row['tire_size']); ?></td>
                            <th>Purchase Date</th>
                            <td><?php echo htmlentities($row['purchase_date']); ?></td>
                        </tr>
                        <tr>
                            <th>Vehicle Make/Model</th>
                            <td><?php echo htmlentities($row['vehicle_make_model']); ?></td>
                            <th>Vehicle Year</th>
                            <td><?php echo htmlentities($row['vehicle_year']); ?></td>
                            <th>Usage Type</th>
                            <td>
                                <?php echo htmlentities($row['usage_type']); ?>
                                <?php if ($row['usage_type_other'] != '') { echo ' (' . htmlentities($row['usage_type_other']) . ')'; } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Nature of Complaint</th>
                            <td colspan="3">
                                <?php echo htmlentities($row['nature_complaint']); ?>
                                <?php if ($row['nature_other'] != '') { echo ' (' . htmlentities($row['nature_other']) . ')'; } ?>
                            </td>
                            <th>Mileage/Hours</th>
                            <td><?php echo htmlentities($row['mileage_hours']); ?></td>
                        </tr>
                        <tr>
                            <th>Detailed Description</th>
                            <td colspan="5"><?php echo htmlentities($row['detailed_description']); ?></td>
                        </tr>
                        <tr>
                            <th>Operating Conditions</th>
                            <td colspan="3"><?php echo htmlentities($row['operating_conditions']); ?></td>
                            <th>Surface Conditions</th>
                            <td><?php echo htmlentities($row['surface_conditions']); ?></td>
                        </tr>
                        <tr>
                            <th>Temperature Conditions</th>
                            <td><?php echo htmlentities($row['temperature_conditions']); ?></td>
                            <th>Speed Operation</th>
                            <td><?php echo htmlentities($row['speed_operation']); ?></td>
                            <th>Load Capacity</th>
                            <td><?php echo htmlentities($row['load_capacity']); ?></td>
                        </tr>
                        <tr>
                            <th>Resolution Requested</th>
                            <td colspan="3">
                                <?php echo htmlentities($row['resolution_requested']); ?>
                                <?php if ($row['resolution_other'] != '') { echo ' (' . htmlentities($row['resolution_other']) . ')'; } ?>
                            </td>
                            <th>Impact</th>
                            <td>
                                <?php echo htmlentities($row['impact']); ?>
                                <?php if ($row['impact_other'] != '') { echo ' (' . htmlentities($row['impact_other']) . ')'; } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>File (if any)</th>
                            <td colspan="5">
                                <?php 
                                $cfile = $row['complaint_file'];
                                if ($cfile == "" || $cfile == "NULL") {
                                    echo "<span style='color: var(--text-gray);'>No file attached</span>";
                                } else { ?>
                                    <a href="../user/complaintdocs/<?php echo htmlentities($row['complaint_file']); ?>" 
                                       class="view-file-link" 
                                       onclick="popUpWindow(this.href, 100, 100, 600, 600); return false;">
                                       <i class="fas fa-file-pdf"></i> View Attached File
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Final Status</th>
                            <td colspan="5">
                                <?php 
                                $status = $row['status'];
                                if ($status == ''): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-clock"></i> Not Processed Yet
                                    </span>
                                <?php elseif ($status == 'in process'): ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-spinner"></i> In Process
                                    </span>
                                <?php elseif ($status == 'closed'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Closed
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                        $ret = mysqli_query($con, "SELECT complaintremark.remark as remark, complaintremark.status as sstatus, complaintremark.remarkDate as rdate 
                            FROM complaintremark 
                            JOIN tbl_tire_complaints ON tbl_tire_complaints.id = complaintremark.complaintNumber 
                            WHERE complaintremark.complaintNumber = '$cid'");
                        $cnt = 1;
                        $count = mysqli_num_rows($ret);
                        if ($count):
                        ?>
                        <tr>
                            <th colspan="4" style="background: var(--primary-orange); color: var(--white);">
                                <i class="fas fa-comments"></i> Remark
                            </th>
                            <th style="background: var(--primary-orange); color: var(--white);">Status</th>
                            <th style="background: var(--primary-orange); color: var(--white);">Updation Date</th>
                        </tr>
                        <?php while ($rw = mysqli_fetch_array($ret)) { ?>
                        <tr>
                            <td colspan="4"><?php echo htmlentities($rw['remark']); ?></td>
                            <td><strong><?php echo htmlentities($rw['sstatus']); ?></strong></td>
                            <td><?php echo htmlentities($rw['rdate']); ?></td>
                        </tr>
                        <?php $cnt = $cnt + 1; } ?>
                        <?php endif; } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
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

        // Popup window function
        let popUpWin = 0;
        function popUpWindow(URLStr, left, top, width, height) {
            if (popUpWin) {
                if (!popUpWin.closed) popUpWin.close();
            }
            popUpWin = window.open(URLStr, 'popUpWin', 
                'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,' +
                'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',screenX=' + left + ',screenY=' + top);
        }

        console.log('Tire Complaint Details page loaded successfully!');
    </script>
</body>
</html>
<?php } ?>