<?php
// Database connection configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Update existing record
    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $serial_number = $_POST['serial_number'];
        $inputDate = $_POST['inputDate'] ? $_POST['inputDate'] : NULL;
        $shift = $_POST['shift'];
        $compound_name = $_POST['compound_name'];
        $description = $_POST['description'];
        $cstock = $_POST['cstock'];
        $batch = $_POST['batch'];
        $pallet = $_POST['pallet'];
        $weight = $_POST['weight'];
        $quality_approved = $_POST['quality_approved'] ? $_POST['quality_approved'] : NULL;
        $expire_date = $_POST['expire_date'] ? $_POST['expire_date'] : NULL;
        $staff_name = $_POST['staff_name'];
        $sg_value = $_POST['sg_value'];
        $hardness = $_POST['hardness'];
        $mh = $_POST['mh'];
        $ml = $_POST['ml'];
        $t10 = $_POST['t10'];
        $t90 = $_POST['t90'];
        $rebound = $_POST['rebound'];
        $T52 = $_POST['T52'];

        $sql = "UPDATE target_table2 SET 
                serial_number = ?, inputDate = ?, shift = ?, compound_name = ?, description = ?, 
                cstock = ?, batch = ?, pallet = ?, weight = ?, quality_approved = ?, expire_date = ?, 
                staff_name = ?, sg_value = ?, hardness = ?, mh = ?, ml = ?, t10 = ?, t90 = ?, 
                rebound = ?, T52 = ? WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssdsssssssssssi", 
            $serial_number, $inputDate, $shift, $compound_name, $description, 
            $cstock, $batch, $pallet, $weight, $quality_approved, $expire_date, $staff_name, 
            $sg_value, $hardness, $mh, $ml, $t10, $t90, $rebound, $T52, $id);
        
        if ($stmt->execute()) {
            $message = "Record updated successfully";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Delete record
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM target_table2 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Record deleted successfully";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get data for editing
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM target_table2 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// Pagination
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " WHERE 
        serial_number LIKE '%$search%' OR 
        compound_name LIKE '%$search%' OR 
        batch LIKE '%$search%' OR 
        staff_name LIKE '%$search%'";
}

// Get total records for pagination
$total_records_sql = "SELECT COUNT(*) FROM target_table2" . $search_condition;
$total_result = $conn->query($total_records_sql);
$total_records = $total_result->fetch_row()[0];
$total_pages = ceil($total_records / $records_per_page);

// Get records for current page
$sql = "SELECT * FROM target_table2" . $search_condition . " ORDER BY id DESC LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compound Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #343a40;
            --accent-color: #ffa94d;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark-color);
        }
        
        .container {
            max-width: 1400px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 40px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 25px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: skewX(-30deg);
        }
        
        .page-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .dashboard-stats {
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 5px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card h3 {
            font-size: 1.2rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .navigation-buttons {
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(242, 128, 24, 0.3);
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #e67100;
            border-color: #e67100;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(242, 128, 24, 0.4);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
            transition: all 0.3s;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: var(--dark-color);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
            transition: all 0.3s;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 193, 7, 0.4);
        }
        
        .btn-info {
            background-color: var(--info-color);
            border-color: var(--info-color);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(23, 162, 184, 0.3);
            transition: all 0.3s;
        }
        
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(23, 162, 184, 0.4);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
        }
        
        .search-box {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .search-box .form-control {
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 16px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .search-box .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
        }
        
        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: <?php echo $edit_data ? 'block' : 'none'; ?>;
            border-top: 5px solid var(--primary-color);
        }
        
        .form-container h3 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .form-control, .form-select {
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
        }
        
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 5px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table-dark th {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 600;
            border: none;
            vertical-align: middle;
            padding: 15px 10px;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(242, 128, 24, 0.05);
        }
        
        .table tbody tr {
            transition: background-color 0.3s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(242, 128, 24, 0.1);
        }
        
        .pagination {
            margin-top: 30px;
        }
        
        .pagination .page-link {
            color: var(--primary-color);
            border-radius: 50%;
            margin: 0 5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .pagination .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 5px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border-left: 5px solid var(--danger-color);
        }
        
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
        }
        
        .btn-close {
            color: white;
            opacity: 1;
        }
        
        .badge {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .highlight-message {
            font-size: 16px;
            color: white;
            background: linear-gradient(135deg, #F28018, #ff5722);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(242, 128, 24, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(242, 128, 24, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(242, 128, 24, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(242, 128, 24, 0);
            }
        }
        
        /* Override table responsive behavior */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #e67100;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 20px 0;
            color: #6c757d;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
        }
        
        /* Quick status indicators */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .status-approved {
            background-color: var(--success-color);
        }
        
        .status-pending {
            background-color: var(--warning-color);
        }
        
        .status-expired {
            background-color: var(--danger-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .stat-card .stat-value {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .page-header {
                padding: 20px 15px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .search-box .row {
                flex-direction: column;
            }
            
            .search-box .col-md-2 {
                margin-top: 10px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-flask me-2"></i> Compound Management System</h1>
        </div>
        
        <!-- Dashboard Stats -->
        <div class="row dashboard-stats">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Records</h3>
                    <div class="stat-value"><?php echo $total_records; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Pages</h3>
                    <div class="stat-value"><?php echo $total_pages; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Current Page</h3>
                    <div class="stat-value"><?php echo $page; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Records Per Page</h3>
                    <div class="stat-value"><?php echo $records_per_page; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <div class="row">
                <div class="col-md-12">
                    <a href="rebatch21.php" class="btn btn-primary">
                        <i class="fas fa-qrcode me-2"></i> Generate QR
                    </a>
                    <?php if ($edit_data): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary float-end">
                            <i class="fas fa-list me-2"></i> View All Records
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="search-box">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control" placeholder="Search by serial number, compound name, batch, or staff name" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary w-100">
                        <i class="fas fa-times me-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Edit Form - Only shown when editing -->
        <?php if ($edit_data): ?>
        <div class="form-container">
            <h3><i class="fas fa-edit me-2"></i> Edit Record</h3>
            <form method="POST" class="row g-3">
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                
                <div class="col-md-4">
                    <label for="serial_number" class="form-label">Serial Number*</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" class="form-control" id="serial_number" name="serial_number" required 
                               value="<?php echo htmlspecialchars($edit_data['serial_number']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="inputDate" class="form-label">Input Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control" id="inputDate" name="inputDate"
                               value="<?php echo $edit_data['inputDate'] ? htmlspecialchars($edit_data['inputDate']) : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="shift" class="form-label">Shift</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        <select class="form-select" id="shift" name="shift">
                            <option value="">Select Shift</option>
                            <option value="Morning" <?php echo $edit_data['shift'] == 'Morning' ? 'selected' : ''; ?>>Morning</option>
                            <option value="Afternoon" <?php echo $edit_data['shift'] == 'Afternoon' ? 'selected' : ''; ?>>Afternoon</option>
                            <option value="Night" <?php echo $edit_data['shift'] == 'Night' ? 'selected' : ''; ?>>Night</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="compound_name" class="form-label">Compound Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-flask"></i></span>
                        <input type="text" class="form-control" id="compound_name" name="compound_name"
                               value="<?php echo htmlspecialchars($edit_data['compound_name']); ?>">
                    </div>
                </div>
                
                <div class="col-md-8">
                    <label for="description" class="form-label">Description</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                        <textarea class="form-control" id="description" name="description" rows="2"><?php echo htmlspecialchars($edit_data['description']); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="cstock" class="form-label">C-Stock</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                        <input type="text" class="form-control" id="cstock" name="cstock"
                               value="<?php echo htmlspecialchars($edit_data['cstock']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="batch" class="form-label">Batch</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                        <input type="text" class="form-control" id="batch" name="batch"
                               value="<?php echo htmlspecialchars($edit_data['batch']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="pallet" class="form-label">Pallet</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-pallet"></i></span>
                        <input type="text" class="form-control" id="pallet" name="pallet"
                               value="<?php echo htmlspecialchars($edit_data['pallet']); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="weight" class="form-label">Weight</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-weight"></i></span>
                        <input type="number" step="0.01" class="form-control" id="weight" name="weight"
                               value="<?php echo htmlspecialchars($edit_data['weight']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="quality_approved" class="form-label">Quality Approved Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                        <input type="date" class="form-control" id="quality_approved" name="quality_approved"
                               value="<?php echo $edit_data['quality_approved'] ? htmlspecialchars($edit_data['quality_approved']) : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="expire_date" class="form-label">Expire Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-times"></i></span>
                        <input type="date" class="form-control" id="expire_date" name="expire_date"
                               value="<?php echo $edit_data['expire_date'] ? htmlspecialchars($edit_data['expire_date']) : ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="staff_name" class="form-label">Staff Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="staff_name" name="staff_name"
                               value="<?php echo htmlspecialchars($edit_data['staff_name']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="sg_value" class="form-label">SG Value</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                        <input type="text" class="form-control" id="sg_value" name="sg_value"
                               value="<?php echo htmlspecialchars($edit_data['sg_value']); ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="hardness" class="form-label">Hardness</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-cubes"></i></span>
                        <input type="text" class="form-control" id="hardness" name="hardness"
                               value="<?php echo htmlspecialchars($edit_data['hardness']); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="mh" class="form-label">MH</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-chart-line"></i></span>
                        <input type="text" class="form-control" id="mh" name="mh"
                               value="<?php echo htmlspecialchars($edit_data['mh']); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="ml" class="form-label">ML</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-chart-area"></i></span>
                        <input type="text" class="form-control" id="ml" name="ml"
                               value="<?php echo htmlspecialchars($edit_data['ml']); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="t10" class="form-label">T10</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-temperature-high"></i></span>
                        <input type="text" class="form-control" id="t10" name="t10"
                               value="<?php echo htmlspecialchars($edit_data['t10']); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="t90" class="form-label">T90</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-temperature-low"></i></span>
                        <input type="text" class="form-control" id="t90" name="t90"
                               value="<?php echo htmlspecialchars($edit_data['t90']); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="rebound" class="form-label">Rebound</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                        <input type="text" class="form-control" id="rebound" name="rebound"
                               value="<?php echo htmlspecialchars($edit_data['rebound']); ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="T52" class="form-label">T52</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-vial"></i></span>
                        <input type="text" class="form-control" id="T52" name="T52"
                               value="<?php echo htmlspecialchars($edit_data['T52']); ?>">
                    </div>
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" name="update" class="btn btn-success">
                        <i class="fas fa-save me-2"></i> Update Record
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">
                        <i class="fas fa-trash me-2"></i> Delete
                    </button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary float-end">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this record? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $edit_data ? $edit_data['id'] : ''; ?>">
                            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Records Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Serial No.</th>
                            <th>Input Date</th>
                            <th>Compound Name</th>
                            <th>Batch</th>
                            <th>Weight</th>
                            <th>Staff</th>
                            <th>Expire Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = ($page - 1) * $records_per_page + 1;
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Check if expired
                                $expired = false;
                                if (!empty($row['expire_date'])) {
                                    $expire_date = new DateTime($row['expire_date']);
                                    $current_date = new DateTime();
                                    $expired = ($current_date > $expire_date);
                                }
                                
                                $row_class = $expired ? 'table-danger' : '';
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td><?php echo $counter; ?></td>
                            <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['inputDate']); ?></td>
                            <td><?php echo htmlspecialchars($row['compound_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['batch']); ?></td>
                            <td><?php echo htmlspecialchars($row['weight']); ?></td>
                            <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                            <td>
                                <?php if (!empty($row['expire_date'])): ?>
                                    <?php if ($expired): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i> 
                                            Expired: <?php echo htmlspecialchars($row['expire_date']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> 
                                            Valid until: <?php echo htmlspecialchars($row['expire_date']); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Not Set</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo $_SERVER['PHP_SELF'] . '?edit=' . $row['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="view_record.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php 
                                $counter++;
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i> No records found.
                                    <?php if (!empty($search)): ?>
                                        Try different search criteria.
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $_SERVER['PHP_SELF'] . '?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . $_SERVER['PHP_SELF'] . '?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = ($i == $page) ? 'active' : '';
                    echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="' . $_SERVER['PHP_SELF'] . '?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $i . '</a></li>';
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="' . $_SERVER['PHP_SELF'] . '?page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                }
                ?>
                
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $_SERVER['PHP_SELF'] . '?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <div class="row">
                <div class="col-md-12">
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> Compound Management System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
        
        // Confirm delete
        const deleteForm = document.querySelector('form button[name="delete"]');
        if (deleteForm) {
            deleteForm.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>