<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen"; 
$password = "Bishan@1919"; 
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define getDistinctValues function
function getDistinctValues($conn, $table, $column) {
    $values = [];
    $sql = "SELECT DISTINCT $column FROM $table ORDER BY $column";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $values[] = $row[$column];
        }
    }
    
    return $values;
}

// Handle AJAX request for mold_name
if (isset($_GET['action']) && $_GET['action'] == 'get_mold_name' && isset($_GET['mold_id'])) {
    $mold_id = trim($_GET['mold_id']);
    $sql = "SELECT mold_name FROM mold WHERE mold_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mold_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $response = ['mold_name' => ''];
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['mold_name'] = $row['mold_name'];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle AJAX request for record details
if (isset($_GET['action']) && $_GET['action'] == 'get_record' && isset($_GET['icode'])) {
    $icode = intval($_GET['icode']);
    $sql = "SELECT tm.icode, tm.mold_id, m.mold_name, ml.mold_size, ml.per_day 
            FROM tire_mold tm 
            LEFT JOIN mold m ON tm.mold_id = m.mold_id 
            LEFT JOIN mold_list ml ON tm.icode = ml.icode AND tm.mold_id = ml.mold_id 
            WHERE tm.icode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $icode);
    $stmt->execute();
    $result = $stmt->get_result();
    $response = [];
    if ($result->num_rows > 0) {
        $response = $result->fetch_assoc();
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Initialize message variable
$message = '';
$messageType = '';

// Process form submission for insert
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'insert') {
    $icode = trim($_POST['icode']);
    $mold_id = trim($_POST['mold_id']);
    $mold_name = trim($_POST['mold_name']);
    $mold_size = trim($_POST['mold_size']);
    $per_day = intval($_POST['per_day']);

    $conn->begin_transaction();
    try {
        // Check if combination exists
        $checkTireMold = "SELECT icode, mold_id FROM tire_mold WHERE icode = ? AND mold_id = ?";
        $checkStmt = $conn->prepare($checkTireMold);
        $checkStmt->bind_param("is", $icode, $mold_id);
        $checkStmt->execute();
        $tireMoldResult = $checkStmt->get_result();

        $checkMoldList = "SELECT icode, mold_id FROM mold_list WHERE icode = ? AND mold_id = ?";
        $checkStmt = $conn->prepare($checkMoldList);
        $checkStmt->bind_param("is", $icode, $mold_id);
        $checkStmt->execute();
        $moldListResult = $checkStmt->get_result();

        if ($tireMoldResult->num_rows > 0 || $moldListResult->num_rows > 0) {
            throw new Exception("This icode and mold_id combination already exists.");
        }

        // Check if mold_id exists in mold table
        $checkMoldId = "SELECT mold_id FROM mold WHERE mold_id = ?";
        $checkStmt = $conn->prepare($checkMoldId);
        $checkStmt->bind_param("s", $mold_id);
        $checkStmt->execute();
        $moldIdResult = $checkStmt->get_result();
        
        if ($moldIdResult->num_rows == 0) {
            throw new Exception("Invalid mold_id. Please select a valid mold ID.");
        }

        // Insert into tire_mold table
        $sql2 = "INSERT INTO tire_mold (icode, mold_id) VALUES (?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("is", $icode, $mold_id);
        $stmt2->execute();

        // Insert into mold_list table
        $sql3 = "INSERT INTO mold_list (icode, mold_size, mold_id, per_day) VALUES (?, ?, ?, ?)";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("issi", $icode, $mold_size, $mold_id, $per_day);
        $stmt3->execute();

        $conn->commit();
        $message = "Record inserted successfully!";
        $messageType = "success";
        
        echo "<script>setTimeout(function() { window.location.href = '" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'; }, 1500);</script>";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Process form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $icode = intval($_POST['icode']);
    $old_mold_id = trim($_POST['old_mold_id']);
    $mold_id = trim($_POST['mold_id']);
    $mold_size = trim($_POST['mold_size']);
    $per_day = intval($_POST['per_day']);

    $conn->begin_transaction();
    try {
        // Check if new combination exists (only if mold_id changed)
        if ($old_mold_id != $mold_id) {
            $checkTireMold = "SELECT icode, mold_id FROM tire_mold WHERE icode = ? AND mold_id = ?";
            $checkStmt = $conn->prepare($checkTireMold);
            $checkStmt->bind_param("is", $icode, $mold_id);
            $checkStmt->execute();
            $tireMoldResult = $checkStmt->get_result();

            if ($tireMoldResult->num_rows > 0) {
                throw new Exception("This icode and mold_id combination already exists.");
            }
        }

        // Check if mold_id exists in mold table
        $checkMoldId = "SELECT mold_id FROM mold WHERE mold_id = ?";
        $checkStmt = $conn->prepare($checkMoldId);
        $checkStmt->bind_param("s", $mold_id);
        $checkStmt->execute();
        $moldIdResult = $checkStmt->get_result();
        
        if ($moldIdResult->num_rows == 0) {
            throw new Exception("Invalid mold_id. Please select a valid mold ID.");
        }

        // Update tire_mold table
        $sql1 = "UPDATE tire_mold SET mold_id = ? WHERE icode = ? AND mold_id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("sis", $mold_id, $icode, $old_mold_id);
        $stmt1->execute();

        // Update mold_list table
        $sql2 = "UPDATE mold_list SET mold_size = ?, mold_id = ?, per_day = ? WHERE icode = ? AND mold_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ssiis", $mold_size, $mold_id, $per_day, $icode, $old_mold_id);
        $stmt2->execute();

        $conn->commit();
        $message = "Record updated successfully!";
        $messageType = "success";
        
        echo "<script>setTimeout(function() { window.location.href = '" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'; }, 1500);</script>";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Process form submission for delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $icode = intval($_POST['icode']);
    $mold_id = trim($_POST['mold_id']);

    $conn->begin_transaction();
    try {
        // Delete from tire_mold table
        $sql1 = "DELETE FROM tire_mold WHERE icode = ? AND mold_id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("is", $icode, $mold_id);
        $stmt1->execute();

        // Delete from mold_list table
        $sql2 = "DELETE FROM mold_list WHERE icode = ? AND mold_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("is", $icode, $mold_id);
        $stmt2->execute();

        $conn->commit();
        $message = "Record deleted successfully!";
        $messageType = "success";
        
        echo "<script>setTimeout(function() { window.location.href = '" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'; }, 1500);</script>";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Process filter form
$filter_icode = isset($_POST['filter_icode']) ? trim($_POST['filter_icode']) : '';
$filter_mold_id = isset($_POST['filter_mold_id']) ? trim($_POST['filter_mold_id']) : '';
$filter_mold_size = isset($_POST['filter_mold_size']) ? trim($_POST['filter_mold_size']) : '';

$records = [];
$sql = "SELECT tm.icode, tm.mold_id, m.mold_name, ml.mold_size, ml.per_day 
        FROM tire_mold tm 
        LEFT JOIN mold m ON tm.mold_id = m.mold_id 
        LEFT JOIN mold_list ml ON tm.icode = ml.icode AND tm.mold_id = ml.mold_id 
        WHERE 1=1";
$params = [];
$types = "";

if ($filter_icode !== '') {
    $sql .= " AND tm.icode = ?";
    $params[] = intval($filter_icode);
    $types .= "i";
}
if ($filter_mold_id !== '') {
    $sql .= " AND tm.mold_id = ?";
    $params[] = $filter_mold_id;
    $types .= "s";
}
if ($filter_mold_size !== '') {
    $sql .= " AND ml.mold_size = ?";
    $params[] = $filter_mold_size;
    $types .= "s";
}

$sql .= " ORDER BY tm.icode";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

// Get distinct values for dropdowns
$moldIds = getDistinctValues($conn, 'mold', 'mold_id');
$moldSizes = getDistinctValues($conn, 'mold_p', 'mold_size');
$icodes = getDistinctValues($conn, 'tire_mold', 'icode');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Advanced Mold Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #f0f0f0;
            --primary-text: #000000;
            --secondary-bg: #FFFFFF;
            --tertiary-text: #333333;
            --border-color: #CCCCCC;
            --accent-color: #F28018;
            --dark-bg: #343a40;
            --white: #ffffff;
            --danger: red;
            --black: Black;
            --shadow: rgba(0, 0, 0, 0.15);
        }
        
        body {
            background-color: var(--primary-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-text);
        }
        
        .container-fluid {
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, var(--dark-bg), var(--accent-color));
            color: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px var(--shadow);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background-color: var(--white);
        }
        
        .card-header {
            background-color: var(--dark-bg);
            color: var(--white);
            padding: 15px 20px;
            font-weight: bold;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--tertiary-text);
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            transition: all 0.3s;
            background-color: var(--white);
            color: var(--primary-text);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
            background-color: var(--white);
        }
        
        .btn {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: #d6691a;
            color: var(--white);
        }
        
        .btn-secondary {
            background-color: var(--tertiary-text);
            color: var(--white);
        }
        
        .btn-secondary:hover {
            background-color: var(--primary-text);
            color: var(--white);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }
        
        .btn-danger:hover {
            background-color: #cc0000;
            color: var(--white);
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: var(--primary-text);
        }
        
        .btn-warning:hover {
            background-color: #ffca2c;
            color: var(--primary-text);
        }
        
        .alert {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .table {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--dark-bg);
            color: var(--white);
            border-bottom: 2px solid var(--border-color);
            padding: 12px;
            font-weight: 600;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            color: var(--primary-text);
        }
        
        .table tbody tr:hover {
            background-color: var(--primary-bg);
        }
        
        .modal-content {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: 0 4px 6px var(--shadow);
        }
        
        .modal-header {
            background-color: var(--dark-bg);
            color: var(--white);
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-footer {
            border-top: 1px solid var(--border-color);
        }
        
        .nav-tabs .nav-link {
            color: var(--tertiary-text);
            border: 1px solid var(--border-color);
            background-color: var(--white);
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--accent-color);
            color: var(--white);
            border-color: var(--accent-color);
        }
        
        .nav-tabs .nav-link:hover {
            background-color: var(--primary-bg);
        }
        
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
            
            .table-responsive {
                border-radius: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header text-center">
            <h1><i class="fas fa-cogs me-2"></i> Advanced Mold Management System</h1>
            <p class="mb-0">Complete CRUD operations for mold records</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="insert-tab" data-bs-toggle="tab" data-bs-target="#insert" type="button" role="tab">
                    <i class="fas fa-plus me-2"></i>Insert New Record
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="view-tab" data-bs-toggle="tab" data-bs-target="#view" type="button" role="tab">
                    <i class="fas fa-table me-2"></i>View & Manage Records
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="myTabContent">
            <!-- Insert Tab -->
            <div class="tab-pane fade show active" id="insert" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-edit me-2"></i> Insert Mold Details
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="moldForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="icode" class="form-label">Item Code *</label>
                                        <input type="number" class="form-control" id="icode" name="icode" required>
                                        <div class="form-text">Must be unique for each mold.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mold_id" class="form-label">Mold ID *</label>
                                        <select class="form-select" id="mold_id" name="mold_id" required>
                                            <option value="">Select Mold ID</option>
                                            <?php foreach ($moldIds as $id): ?>
                                                <option value="<?php echo htmlspecialchars($id); ?>">
                                                    <?php echo htmlspecialchars($id); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Unique identifier from mold table.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mold_name" class="form-label">Mold Name</label>
                                        <input type="text" class="form-control" id="mold_name" name="mold_name" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mold_size" class="form-label">Mold Size *</label>
                                        <select class="form-select" id="mold_size" name="mold_size" required>
                                            <option value="">Select Mold Size</option>
                                            <?php foreach ($moldSizes as $size): ?>
                                                <option value="<?php echo htmlspecialchars($size); ?>">
                                                    <?php echo htmlspecialchars($size); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Select size from mold_p table.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="per_day" class="form-label">Per Day *</label>
                                        <input type="number" class="form-control" id="per_day" name="per_day" required min="0">
                                        <div class="form-text">Production capacity per day.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-center mt-4">
                                <button type="submit" name="action" value="insert" class="btn btn-primary me-2">
                                    <i class="fas fa-plus me-1"></i> Insert
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- View Tab -->
            <div class="tab-pane fade" id="view" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-table me-2"></i> Existing Records
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="filterForm" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="filter_icode" class="form-label">Filter by Item Code</label>
                                        <select class="form-select" id="filter_icode" name="filter_icode">
                                            <option value="">All Item Codes</option>
                                            <?php foreach ($icodes as $icode): ?>
                                                <option value="<?php echo htmlspecialchars($icode); ?>" <?php echo ($filter_icode == $icode) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($icode); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="filter_mold_id" class="form-label">Filter by Mold ID</label>
                                        <select class="form-select" id="filter_mold_id" name="filter_mold_id">
                                            <option value="">All Mold IDs</option>
                                            <?php foreach ($moldIds as $id): ?>
                                                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo ($filter_mold_id == $id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($id); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="filter_mold_size" class="form-label">Filter by Mold Size</label>
                                        <select class="form-select" id="filter_mold_size" name="filter_mold_size">
                                            <option value="">All Mold Sizes</option>
                                            <?php foreach ($moldSizes as $size): ?>
                                                <option value="<?php echo htmlspecialchars($size); ?>" <?php echo ($filter_mold_size == $size) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($size); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('filter_icode').value='';document.getElementById('filter_mold_id').value='';document.getElementById('filter_mold_size').value='';document.getElementById('filterForm').submit();">
                                    <i class="fas fa-undo me-1"></i> Clear Filters
                                </button>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Mold ID</th>
                                        <th>Mold Name</th>
                                        <th>Mold Size</th>
                                        <th>Per Day</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($records)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No records found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($records as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['icode']); ?></td>
                                                <td><?php echo htmlspecialchars($record['mold_id']); ?></td>
                                                <td><?php echo htmlspecialchars($record['mold_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['mold_size']); ?></td>
                                                <td><?php echo htmlspecialchars($record['per_day']); ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm me-1" onclick="editRecord(<?php echo $record['icode']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteRecord(<?php echo $record['icode']; ?>, '<?php echo htmlspecialchars($record['mold_id']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_old_mold_id" name="old_mold_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_icode" class="form-label">Item Code *</label>
                                    <input type="number" class="form-control" id="edit_icode" name="icode" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_mold_id" class="form-label">Mold ID *</label>
                                    <select class="form-select" id="edit_mold_id" name="mold_id" required>
                                        <option value="">Select Mold ID</option>
                                        <?php foreach ($moldIds as $id): ?>
                                            <option value="<?php echo htmlspecialchars($id); ?>">
                                                <?php echo htmlspecialchars($id); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_mold_name" class="form-label">Mold Name</label>
                                    <input type="text" class="form-control" id="edit_mold_name" name="mold_name" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_mold_size" class="form-label">Mold Size *</label>
                                    <select class="form-select" id="edit_mold_size" name="mold_size" required>
                                        <option value="">Select Mold Size</option>
                                        <?php foreach ($moldSizes as $size): ?>
                                            <option value="<?php echo htmlspecialchars($size); ?>">
                                                <?php echo htmlspecialchars($size); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_per_day" class="form-label">Per Day *</label>
                                    <input type="number" class="form-control" id="edit_per_day" name="per_day" required min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" name="action" value="update" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-trash me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="deleteForm">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Are you sure you want to delete this record? This action cannot be undone.
                        </div>
                        <input type="hidden" id="delete_icode" name="icode">
                        <input type="hidden" id="delete_mold_id" name="mold_id">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Item Code:</strong>
                                <p id="delete_display_icode" class="text-muted"></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Mold ID:</strong>
                                <p id="delete_display_mold_id" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" name="action" value="delete" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to get mold name when mold_id is selected
        function getMoldName(moldId, targetElement) {
            if (moldId) {
                fetch(`<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?action=get_mold_name&mold_id=${moldId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById(targetElement).value = data.mold_name || '';
                    })
                    .catch(error => {
                        console.error('Error fetching mold name:', error);
                        document.getElementById(targetElement).value = '';
                    });
            } else {
                document.getElementById(targetElement).value = '';
            }
        }

        // Event listeners for mold_id changes
        document.getElementById('mold_id').addEventListener('change', function() {
            getMoldName(this.value, 'mold_name');
        });

        document.getElementById('edit_mold_id').addEventListener('change', function() {
            getMoldName(this.value, 'edit_mold_name');
        });

        // Function to edit record
        function editRecord(icode) {
            fetch(`<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?action=get_record&icode=${icode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.icode) {
                        document.getElementById('edit_icode').value = data.icode;
                        document.getElementById('edit_old_mold_id').value = data.mold_id;
                        document.getElementById('edit_mold_id').value = data.mold_id;
                        document.getElementById('edit_mold_name').value = data.mold_name || '';
                        document.getElementById('edit_mold_size').value = data.mold_size || '';
                        document.getElementById('edit_per_day').value = data.per_day || '';
                        
                        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                        editModal.show();
                    } else {
                        alert('Record not found');
                    }
                })
                .catch(error => {
            console.error('Error fetching record:', error);
            alert('Error loading record data');
        });
    }

    // Function to delete record
    function deleteRecord(icode, moldId) {
        document.getElementById('delete_icode').value = icode;
        document.getElementById('delete_mold_id').value = moldId;
        document.getElementById('delete_display_icode').textContent = icode;
        document.getElementById('delete_display_mold_id').textContent = moldId;
        
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Form validation
    document.getElementById('moldForm').addEventListener('submit', function(e) {
        const icode = document.getElementById('icode').value;
        const moldId = document.getElementById('mold_id').value;
        const moldSize = document.getElementById('mold_size').value;
        const perDay = document.getElementById('per_day').value;

        if (!icode || !moldId || !moldSize || !perDay) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }

        if (parseInt(perDay) < 0) {
            e.preventDefault();
            alert('Per day value must be 0 or greater');
            return false;
        }
    });

    // Edit form validation
    document.getElementById('editForm').addEventListener('submit', function(e) {
        const moldId = document.getElementById('edit_mold_id').value;
        const moldSize = document.getElementById('edit_mold_size').value;
        const perDay = document.getElementById('edit_per_day').value;

        if (!moldId || !moldSize || !perDay) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }

        if (parseInt(perDay) < 0) {
            e.preventDefault();
            alert('Per day value must be 0 or greater');
            return false;
        }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>