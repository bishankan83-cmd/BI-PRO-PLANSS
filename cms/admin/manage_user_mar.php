<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // Handle Delete
    if (isset($_GET['del']) && $_GET['del'] != '') {
        $adminId = intval($_GET['del']);
        $query = mysqli_query($con, "DELETE FROM admin WHERE id='$adminId' AND role='Marketing'");
        if ($query) {
            echo '<script>alert("Marketing Admin Deleted Successfully")</script>';
            echo "<script>window.location.href='manage-marketing-admins.php'</script>";
        }
    }

    // Handle Add
    if (isset($_POST['submit'])) {
        $fullname = $_POST['fullname'];
        $mobilenumber = $_POST['mobilenumber'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $role = 'Marketing';
        
        $query = mysqli_query($con, "INSERT INTO admin(fullname, role, mobilenumber, email, username, password) VALUES('$fullname', '$role', '$mobilenumber', '$email', '$username', '$password')");
        
        if ($query) {
            echo '<script>alert("Marketing Admin Added Successfully")</script>';
            echo "<script>window.location.href='manage-marketing-admins.php'</script>";
        } else {
            echo '<script>alert("Something went wrong. Please try again")</script>';
        }
    }

    // Handle Edit
    if (isset($_POST['update'])) {
        $adminId = intval($_POST['admin_id']);
        $fullname = $_POST['fullname'];
        $mobilenumber = $_POST['mobilenumber'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $query = mysqli_query($con, "UPDATE admin SET fullname='$fullname', mobilenumber='$mobilenumber', email='$email', username='$username', password='$password', updationDate=NOW() WHERE id='$adminId' AND role='Marketing'");
        } else {
            $query = mysqli_query($con, "UPDATE admin SET fullname='$fullname', mobilenumber='$mobilenumber', email='$email', username='$username', updationDate=NOW() WHERE id='$adminId' AND role='Marketing'");
        }
        
        if ($query) {
            echo '<script>alert("Marketing Admin Updated Successfully")</script>';
            echo "<script>window.location.href='manage-marketing-admins.php'</script>";
        } else {
            echo '<script>alert("Something went wrong. Please try again")</script>';
        }
    }

    // Fetch current admin details
    $adminId = intval($_SESSION["aid"]);
    $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
    $adminData = mysqli_fetch_array($adminQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Manage Marketing Admins</title>
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
            --marketing-purple: #9b59b6;
            --marketing-light: rgba(155, 89, 182, 0.1);
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

        .header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 2rem;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .user-btn:hover {
            background: var(--orange-light);
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--gradient-1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-details h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .user-details span {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
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

        .breadcrumb {
            list-style: none;
            display: flex;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-gray);
            margin-top: 1rem;
        }

        .breadcrumb-item a {
            color: var(--text-gray);
            text-decoration: none;
            transition: all 0.2s;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-orange);
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

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-gray);
            font-size: 0.9rem;
        }

        .table th {
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .table td {
            color: var(--dark-gray);
        }

        .table tbody tr:hover {
            background: var(--marketing-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            background: var(--white);
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .marketing-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: var(--white);
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 0;
            border-radius: 1rem;
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-xl);
        }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border-gray);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .close {
            color: var(--text-gray);
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }

        .close:hover {
            color: var(--dark-gray);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--text-gray);
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .user-details {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <a href="dashboard.php" class="brand-text">Complaint Management System</a>
        </div>

        <div class="header-actions">
            <div class="user-menu">
                <a href="admin-profile.php" class="user-btn">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($adminData['fullname'], 0, 2)); ?>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlentities($adminData['fullname']); ?></h4>
                        <span>Administrator</span>
                    </div>
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Manage Marketing Admins</h1>
            <p class="page-subtitle">Add, edit, and manage administrators with Marketing role</p>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item">Manage Marketing Admins</li>
            </ul>
        </div>

        <!-- Add Button -->
        <div style="margin-bottom: 2rem;">
            <button class="btn btn-success" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Marketing Admin
            </button>
        </div>

        <!-- Marketing Admins Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-users-cog"></i>
                    Marketing Administrators
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Mobile Number</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($con, "SELECT * FROM admin WHERE role='Marketing' ORDER BY creationDate DESC");
                            $cnt = 1;
                            $rowCount = mysqli_num_rows($query);
                            
                            if ($rowCount > 0) {
                                while ($row = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td><?php echo $cnt; ?></td>
                                    <td><strong><?php echo htmlentities($row['fullname']); ?></strong></td>
                                    <td><span class="marketing-badge">Marketing</span></td>
                                    <td><?php echo htmlentities($row['mobilenumber'] ? $row['mobilenumber'] : 'N/A'); ?></td>
                                    <td><?php echo htmlentities($row['email'] ? $row['email'] : 'N/A'); ?></td>
                                    <td><?php echo htmlentities($row['username'] ? $row['username'] : 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['creationDate'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-warning btn-sm" onclick='openEditModal(<?php echo json_encode($row); ?>)'>
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="?del=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this admin?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                    $cnt++;
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="no-data">
                                            <i class="fas fa-users-cog"></i>
                                            <h3>No Marketing Admins Found</h3>
                                            <p>Click the "Add New Marketing Admin" button to create one.</p>
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

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Add Marketing Admin</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name <span style="color: red;">*</span></label>
                            <input type="text" name="fullname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" name="mobilenumber" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Username <span style="color: red;">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password <span style="color: red;">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Edit Marketing Admin</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="admin_id" id="edit_admin_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name <span style="color: red;">*</span></label>
                            <input type="text" name="fullname" id="edit_fullname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" name="mobilenumber" id="edit_mobilenumber" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Username <span style="color: red;">*</span></label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password <small style="color: var(--text-gray);">(Leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password to change">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(data) {
            document.getElementById('edit_admin_id').value = data.id;
            document.getElementById('edit_fullname').value = data.fullname;
            document.getElementById('edit_mobilenumber').value = data.mobilenumber || '';
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_username').value = data.username || '';
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>