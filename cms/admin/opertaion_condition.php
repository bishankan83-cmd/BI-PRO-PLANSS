<?php
session_start();
include('include/config.php');

if (empty($_SESSION['aid'])) {
    header('location:index.php');
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

// Handle Add Operation
if (isset($_POST['add_operating_condition'])) {
    $condition_name = mysqli_real_escape_string($con, trim($_POST['condition_name']));
    
    if (empty($condition_name)) {
        $_SESSION['error'] = "Operating Condition cannot be empty!";
    } elseif (strlen($condition_name) > 255) {
        $_SESSION['error'] = "Operating Condition must not exceed 255 characters!";
    } elseif (strlen($condition_name) < 2) {
        $_SESSION['error'] = "Operating Condition must be at least 2 characters long!";
    } else {
        // Check if operating condition already exists
        $check_query = mysqli_query($con, "SELECT * FROM operating_conditions WHERE condition_name = '$condition_name'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Operating Condition already exists!";
        } else {
            $sql = mysqli_query($con, "INSERT INTO operating_conditions(condition_name, is_active) VALUES('$condition_name', 1)");
            if ($sql) {
                $_SESSION['msg'] = "Operating Condition created successfully!";
            } else {
                $_SESSION['error'] = "Error creating operating condition: " . mysqli_error($con);
            }
        }
    }
}

// Handle Update Operation
if (isset($_POST['update_operating_condition'])) {
    $id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $condition_name = mysqli_real_escape_string($con, trim($_POST['edit_condition_name']));
    
    if (empty($condition_name)) {
        $_SESSION['error'] = "Operating Condition cannot be empty!";
    } elseif (strlen($condition_name) > 255) {
        $_SESSION['error'] = "Operating Condition must not exceed 255 characters!";
    } elseif (strlen($condition_name) < 2) {
        $_SESSION['error'] = "Operating Condition must be at least 2 characters long!";
    } else {
        // Check if operating condition already exists (excluding current record)
        $check_query = mysqli_query($con, "SELECT * FROM operating_conditions WHERE condition_name = '$condition_name' AND id != '$id'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Operating Condition already exists!";
        } else {
            $sql = mysqli_query($con, "UPDATE operating_conditions SET condition_name = '$condition_name' WHERE id = '$id'");
            if ($sql) {
                $_SESSION['msg'] = "Operating Condition updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating operating condition: " . mysqli_error($con);
            }
        }
    }
}

// Handle Deactivate Operation
if (isset($_POST['deactivate_operating_condition'])) {
    $id = mysqli_real_escape_string($con, $_POST['deactivate_id']);
    
    $sql = mysqli_query($con, "UPDATE operating_conditions SET is_active = 0 WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Operating Condition deactivated successfully!";
    } else {
        $_SESSION['error'] = "Error deactivating operating condition: " . mysqli_error($con);
    }
}

// Handle Delete Operation
if (isset($_POST['delete_operating_condition'])) {
    $id = mysqli_real_escape_string($con, $_POST['delete_id']);
    
    $sql = mysqli_query($con, "DELETE FROM operating_conditions WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Operating Condition deleted permanently!";
    } else {
        $_SESSION['error'] = "Error deleting operating condition: " . mysqli_error($con);
    }
}

// Get edit data if edit button was clicked
$edit_data = null;
if (isset($_POST['edit_btn'])) {
    $edit_id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $edit_query = mysqli_query($con, "SELECT * FROM operating_conditions WHERE id = '$edit_id' AND is_active = 1");
    if (mysqli_num_rows($edit_query) > 0) {
        $edit_data = mysqli_fetch_array($edit_query);
    }
}

// Cancel edit
if (isset($_POST['cancel_edit'])) {
    $edit_data = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Operating Conditions</title>
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
        }

        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            overflow: hidden;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
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

        .header-actions-right {
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

        .btn-success {
            background: var(--gradient-2);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-warning {
            background: var(--gradient-4);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-warning:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-danger {
            background: var(--gradient-3);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-danger:hover {
            transform: translateY(-1px);
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

        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            box-shadow: var(--shadow-sm);
            transition: opacity 0.3s ease;
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

        .alert-warning {
            background: var(--warning-light);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .alert .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            background: none;
            border: none;
            color: inherit;
            font-size: 1rem;
            cursor: pointer;
        }

        .edit-form {
            background: var(--white);
            border: 1px solid var(--primary-orange);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            color: var(--dark-gray);
            background: var(--white);
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px var(--orange-light);
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
        }

        .status-badge {
            padding: 0.375rem 0.875rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-active {
            background: var(--success-light);
            color: var(--success);
        }

        .status-inactive {
            background: var(--error-light);
            color: var(--error);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
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

        /* Sidebar highlighting */
        .sidebar-nav .nav-item.active {
            background: var(--orange-light);
            border-left: 4px solid var(--primary-orange);
        }

        .sidebar-nav .nav-item.active a {
            color: var(--primary-orange);
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
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
    <?php include('include/header.php'); ?>
    <div class="container">
        <?php include('include/sidebar.php'); ?>
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Operating Conditions Management</h1>
                    <p class="page-subtitle">Manage operating condition categories for efficient issue tracking.</p>
                </div>
                <div class="header-actions-right">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list"></i>
                        <?php echo $edit_data ? 'Edit Operating Condition' : 'Add New Operating Condition'; ?>
                    </h2>
                </div>
                <div class="card-body">
                    <!-- Success Messages -->
                    <?php if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])): ?>
                        <div class="alert alert-success animate-in">
                            <strong>Success!</strong> <?php echo htmlentities($_SESSION['msg']); $_SESSION['msg'] = ''; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger animate-in">
                            <strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); $_SESSION['error'] = ''; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Delete/Deactivate Messages -->
                    <?php if (isset($_SESSION['delmsg']) && !empty($_SESSION['delmsg'])): ?>
                        <div class="alert alert-warning animate-in">
                            <strong>Action Completed!</strong> <?php echo htmlentities($_SESSION['delmsg']); $_SESSION['delmsg'] = ''; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Edit Form (when editing) -->
                    <?php if ($edit_data): ?>
                        <div class="edit-form animate-in">
                            <h6><i class="fas fa-edit"></i> Editing: <strong><?php echo htmlentities($edit_data['condition_name']); ?></strong></h6>
                            <form method="post" class="mt-3">
                                <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Operating Condition <span class="text-danger">*</span></label>
                                            <input type="text" name="edit_condition_name" class="form-control"
                                                   value="<?php echo htmlentities($edit_data['condition_name']); ?>"
                                                   required maxlength="255" placeholder="Enter operating condition">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label>&nbsp;</label>
                                        <div class="form-group">
                                            <button type="submit" name="update_operating_condition" class="btn btn-success">
                                                <i class="fas fa-check"></i> Update
                                            </button>
                                            <button type="submit" name="cancel_edit" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Add Form (when not editing) -->
                        <form method="post">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="condition_name">Operating Condition <span class="text-danger">*</span></label>
                                        <input type="text" name="condition_name" id="condition_name" class="form-control"
                                               required maxlength="255" placeholder="Enter operating condition">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <div class="form-group">
                                        <button type="submit" name="add_operating_condition" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Operating Condition
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-list"></i>
                        Manage Operating Conditions
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="40%">Operating Condition</th>
                                    <th width="20%">Status</th>
                                    <th width="30%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = mysqli_query($con, "SELECT * FROM operating_conditions ORDER BY condition_name ASC");
                                $cnt = 1;

                                if (mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_array($query)) {
                                ?>
                                    <tr>
                                        <td><strong><?php echo $cnt; ?></strong></td>
                                        <td><?php echo htmlentities($row['condition_name']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="text-center action-buttons">
                                            <!-- Edit Button (only for active records) -->
                                            <?php if ($row['is_active']): ?>
                                                <form method="post" style="display: inline-block;">
                                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="edit_btn" class="btn btn-primary btn-sm"
                                                            title="Edit Operating Condition">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <!-- Deactivate Button (only for active records) -->
                                            <?php if ($row['is_active']): ?>
                                                <form method="post" style="display: inline-block;"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this operating condition: <?php echo htmlentities($row['condition_name']); ?>?');">
                                                    <input type="hidden" name="deactivate_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="deactivate_operating_condition" class="btn btn-warning btn-sm"
                                                            title="Deactivate Operating Condition">
                                                        <i class="fas fa-pause-circle"></i> Deactivate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <!-- Delete Button -->
                                            <form method="post" style="display: inline-block;"
                                                  onsubmit="return confirm('Are you sure you want to permanently delete this operating condition: <?php echo htmlentities($row['condition_name']); ?>? This action cannot be undone.');">
                                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete_operating_condition" class="btn btn-danger btn-sm"
                                                        title="Delete Operating Condition">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php
                                        $cnt++;
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="4" class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-inbox"></i>
                                            </div>
                                            <h3>No operating conditions found.</h3>
                                            <p>Add your first operating condition using the form above.</p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Total Operating Conditions: <strong><?php echo mysqli_num_rows($query); ?></strong>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function (alert) {
                setTimeout(function () {
                    alert.style.opacity = '0';
                    setTimeout(function () {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });

            // Highlight sidebar link
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar-nav .nav-item a');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.parentElement.classList.add('active');
                }
            });
        });

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        }

        // Form validation
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const input = form.querySelector('input[name="condition_name"], input[name="edit_condition_name"]');
                if (input && input.value.trim().length < 2) {
                    alert('Operating condition must be at least 2 characters long.');
                    e.preventDefault();
                    return false;
                }
                if (input && input.value.trim().length > 255) {
                    alert('Operating condition must not exceed 255 characters.');
                    e.preventDefault();
                    return false;
                }
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