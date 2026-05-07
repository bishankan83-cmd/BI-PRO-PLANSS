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
if (isset($_POST['add_resolution_type'])) {
    $resolution_name = mysqli_real_escape_string($con, trim($_POST['resolution_name']));
    
    if (empty($resolution_name)) {
        $_SESSION['error'] = "Resolution Type cannot be empty!";
    } elseif (strlen($resolution_name) > 100) {
        $_SESSION['error'] = "Resolution Type must not exceed 100 characters!";
    } elseif (strlen($resolution_name) < 2) {
        $_SESSION['error'] = "Resolution Type must be at least 2 characters long!";
    } else {
        // Check if resolution type already exists
        $check_query = mysqli_query($con, "SELECT * FROM resolution_types WHERE resolution_name = '$resolution_name'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Resolution Type already exists!";
        } else {
            $sql = mysqli_query($con, "INSERT INTO resolution_types(resolution_name, is_active, created_at) VALUES('$resolution_name', 1, CURRENT_TIMESTAMP)");
            if ($sql) {
                $_SESSION['msg'] = "Resolution Type created successfully!";
            } else {
                $_SESSION['error'] = "Error creating resolution type: " . mysqli_error($con);
            }
        }
    }
}

// Handle Update Operation
if (isset($_POST['update_resolution_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $resolution_name = mysqli_real_escape_string($con, trim($_POST['edit_resolution_name']));
    
    if (empty($resolution_name)) {
        $_SESSION['error'] = "Resolution Type cannot be empty!";
    } elseif (strlen($resolution_name) > 100) {
        $_SESSION['error'] = "Resolution Type must not exceed 100 characters!";
    } elseif (strlen($resolution_name) < 2) {
        $_SESSION['error'] = "Resolution Type must be at least 2 characters long!";
    } else {
        // Check if resolution type already exists (excluding current record)
        $check_query = mysqli_query($con, "SELECT * FROM resolution_types WHERE resolution_name = '$resolution_name' AND id != '$id'");
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['error'] = "Resolution Type already exists!";
        } else {
            $sql = mysqli_query($con, "UPDATE resolution_types SET resolution_name = '$resolution_name' WHERE id = '$id'");
            if ($sql) {
                $_SESSION['msg'] = "Resolution Type updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating resolution type: " . mysqli_error($con);
            }
        }
    }
}

// Handle Deactivate Operation
if (isset($_POST['deactivate_resolution_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['deactivate_id']);
    
    $sql = mysqli_query($con, "UPDATE resolution_types SET is_active = 0 WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Resolution Type deactivated successfully!";
    } else {
        $_SESSION['error'] = "Error deactivating resolution type: " . mysqli_error($con);
    }
}

// Handle Delete Operation
if (isset($_POST['delete_resolution_type'])) {
    $id = mysqli_real_escape_string($con, $_POST['delete_id']);
    
    $sql = mysqli_query($con, "DELETE FROM resolution_types WHERE id = '$id'");
    if ($sql) {
        $_SESSION['delmsg'] = "Resolution Type deleted permanently!";
    } else {
        $_SESSION['error'] = "Error deleting resolution type: " . mysqli_error($con);
    }
}

// Get edit data if edit button was clicked
$edit_data = null;
if (isset($_POST['edit_btn'])) {
    $edit_id = mysqli_real_escape_string($con, $_POST['edit_id']);
    $edit_query = mysqli_query($con, "SELECT * FROM resolution_types WHERE id = '$edit_id' AND is_active = 1");
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
    <title>CMS | Resolution Types</title>
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .edit-form {
            background-color: #e3f2fd;
            border: 2px solid #2196f3;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 2px;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .alert {
            margin-bottom: 20px;
            transition: opacity 0.3s ease;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php include('include/sidebar.php'); ?>
    <?php include('include/header.php'); ?>

    <!-- [ Main Content ] start -->
    <section class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">Resolution Types Management</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="resolution-types.php">Resolution Types</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><?php echo $edit_data ? 'Edit Resolution Type' : 'Add New Resolution Type'; ?></h5>
                        </div>
                        <div class="card-body">
                            <!-- Success Messages -->
                            <?php if (isset($_SESSION['msg']) && !empty($_SESSION['msg'])): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <strong>Success!</strong> <?php echo htmlentities($_SESSION['msg']); $_SESSION['msg'] = ''; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Error Messages -->
                            <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <strong>Error!</strong> <?php echo htmlentities($_SESSION['error']); $_SESSION['error'] = ''; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Delete/Deactivate Messages -->
                            <?php if (isset($_SESSION['delmsg']) && !empty($_SESSION['delmsg'])): ?>
                                <div class="alert alert-warning alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <strong>Action Completed!</strong> <?php echo htmlentities($_SESSION['delmsg']); $_SESSION['delmsg'] = ''; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Edit Form (when editing) -->
                            <?php if ($edit_data): ?>
                                <div class="edit-form">
                                    <h6><i class="feather icon-edit"></i> Editing: <strong><?php echo htmlentities($edit_data['resolution_name']); ?></strong></h6>
                                    <form method="post" class="mt-3">
                                        <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label>Resolution Type <span class="text-danger">*</span></label>
                                                    <input type="text" name="edit_resolution_name" class="form-control"
                                                           value="<?php echo htmlentities($edit_data['resolution_name']); ?>"
                                                           required maxlength="100" placeholder="Enter resolution type">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label>&nbsp;</label>
                                                <div class="form-group">
                                                    <button type="submit" name="update_resolution_type" class="btn btn-success">
                                                        <i class="feather icon-check"></i> Update
                                                    </button>
                                                    <button type="submit" name="cancel_edit" class="btn btn-secondary">
                                                        <i class="feather icon-x"></i> Cancel
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
                                                <label for="resolution_name">Resolution Type <span class="text-danger">*</span></label>
                                                <input type="text" name="resolution_name" id="resolution_name" class="form-control"
                                                       required maxlength="100" placeholder="Enter resolution type">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label>&nbsp;</label>
                                            <div class="form-group">
                                                <button type="submit" name="add_resolution_type" class="btn btn-primary">
                                                    <i class="feather icon-plus"></i> Add Resolution Type
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <hr class="my-4">

                            <!-- Manage Resolution Types Table -->
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="feather icon-list"></i> Manage Resolution Types</h5>
                                </div>
                                <div class="card-body table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th width="10%">#</th>
                                                    <th width="40%">Resolution Type</th>
                                                    <th width="20%">Status</th>
                                                    <th width="30%" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = mysqli_query($con, "SELECT * FROM resolution_types ORDER BY resolution_name ASC");
                                                $cnt = 1;

                                                if (mysqli_num_rows($query) > 0) {
                                                    while ($row = mysqli_fetch_array($query)) {
                                                ?>
                                                    <tr>
                                                        <td><strong><?php echo $cnt; ?></strong></td>
                                                        <td><?php echo htmlentities($row['resolution_name']); ?></td>
                                                        <td><?php echo $row['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'; ?></td>
                                                        <td class="text-center action-buttons">
                                                            <!-- Edit Button (only for active records) -->
                                                            <?php if ($row['is_active']): ?>
                                                                <form method="post" style="display: inline-block;">
                                                                    <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                                                    <button type="submit" name="edit_btn" class="btn btn-primary btn-sm"
                                                                            title="Edit Resolution Type">
                                                                        <i class="feather icon-edit"></i> Edit
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            <!-- Deactivate Button (only for active records) -->
                                                            <?php if ($row['is_active']): ?>
                                                                <form method="post" style="display: inline-block;"
                                                                      onsubmit="return confirm('Are you sure you want to deactivate this resolution type: <?php echo htmlentities($row['resolution_name']); ?>?');">
                                                                    <input type="hidden" name="deactivate_id" value="<?php echo $row['id']; ?>">
                                                                    <button type="submit" name="deactivate_resolution_type" class="btn btn-warning btn-sm"
                                                                            title="Deactivate Resolution Type">
                                                                        <i class="feather icon-pause-circle"></i> Deactivate
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            <!-- Delete Button -->
                                                            <form method="post" style="display: inline-block;"
                                                                  onsubmit="return confirm('Are you sure you want to permanently delete this resolution type: <?php echo htmlentities($row['resolution_name']); ?>? This action cannot be undone.');">
                                                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                                <button type="submit" name="delete_resolution_type" class="btn btn-danger btn-sm"
                                                                        title="Delete Resolution Type">
                                                                    <i class="feather icon-trash-2"></i> Delete
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
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            <i class="feather icon-inbox" style="font-size: 48px;"></i>
                                                            <br><strong>No resolution types found.</strong>
                                                            <br>Add your first resolution type using the form above.
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if (mysqli_num_rows($query) > 0): ?>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                Total Resolution Types: <strong><?php echo mysqli_num_rows($query); ?></strong>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </section>

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

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
        });

        // Form validation
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                const input = form.querySelector('input[name="resolution_name"], input[name="edit_resolution_name"]');
                if (input && input.value.trim().length < 2) {
                    alert('Resolution type must be at least 2 characters long.');
                    e.preventDefault();
                    return false;
                }
                if (input && input.value.trim().length > 100) {
                    alert('Resolution type must not exceed 100 characters.');
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>