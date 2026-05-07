<?php
session_start();
include('include/config.php');
if (empty($_SESSION['aid'])) {
    header('location:index.php');
    exit;
}

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
    <title>CMS | View Profile</title>
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
            overflow-x: hidden;
            padding: 2rem;
        }

        /* Main Content */
        .main-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
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

        .header-actions-right {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
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

        .btn-secondary {
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-secondary:hover {
            background: var(--bg-light);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 2rem;
            border-bottom: 1px solid var(--border-gray);
            background: var(--gradient-1);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2.5rem;
        }

        .info-group {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-gray);
        }

        .info-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: var(--dark-gray);
            padding: 0.75rem 1rem;
            background: var(--light-gray);
            border-radius: 0.5rem;
            border-left: 3px solid var(--primary-orange);
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

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-danger {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 1.5rem;
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
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">View Profile</h1>
                <p class="page-subtitle">View your profile information</p>
            </div>
            <div class="header-actions-right">
                <a href="add_signature.php" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Add Signature
                </a>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
            <div class="alert alert-danger animate-in">
                <i class="fas fa-exclamation-circle"></i>
                <span><strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); ?></span>
                <?php $_SESSION['error'] = ''; ?>
            </div>
        <?php endif; ?>

        <!-- View Profile Card -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-user"></i>
                    Profile Information
                </h2>
            </div>
            <div class="card-body">
                <?php
                $id = intval($_SESSION['aid']);
                $query = mysqli_query($con, "SELECT * FROM admin WHERE id='$id'");
                if ($query && mysqli_num_rows($query) > 0) {
                    $row = mysqli_fetch_array($query);
                ?>
                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-user"></i> Admin Username
                        </div>
                        <div class="info-value">
                            <?php echo htmlentities($row['username']); ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-id-card"></i> Full Name
                        </div>
                        <div class="info-value">
                            <?php echo htmlentities($row['fullname']); ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-phone"></i> Contact Number
                        </div>
                        <div class="info-value">
                            <?php echo htmlentities($row['mobilenumber']); ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i> Email Address
                        </div>
                        <div class="info-value">
                            <?php echo htmlentities($row['email']); ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-calendar-alt"></i> Registration Date
                        </div>
                        <div class="info-value">
                            <?php echo htmlentities($row['creationDate']); ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">
                            <i class="fas fa-clock"></i> Profile Last Updated
                        </div>
                        <div class="info-value">
                            <?php echo $row['updationDate'] ? htmlentities($row['updationDate']) : 'Never'; ?>
                        </div>
                    </div>
                <?php 
                } else {
                ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><strong>Error!</strong> Unable to fetch profile details.</span>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert) => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($con); ?>