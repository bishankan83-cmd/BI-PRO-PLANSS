<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to store success message
session_start();

// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create employees table if it doesn't exist
$createEmployeesTable = "CREATE TABLE IF NOT EXISTS employees (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    user_type ENUM('admin', 'inspector', 'supervisor') NOT NULL DEFAULT 'inspector',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createEmployeesTable)) {
    die("Error creating employees table: " . $conn->error);
}

// Modify tire_inspections2 table to add inspection_date and shift columns if they don't exist
$checkColumnsQuery = "SHOW COLUMNS FROM tire_inspections2 LIKE 'inspection_date'";
$columnExists = $conn->query($checkColumnsQuery);
if ($columnExists->num_rows === 0) {
    $alterTableQuery = "ALTER TABLE tire_inspections2 
                        ADD COLUMN inspection_date DATE NOT NULL DEFAULT CURRENT_DATE,
                        ADD COLUMN shift VARCHAR(20) NOT NULL DEFAULT 'Day'";
    if (!$conn->query($alterTableQuery)) {
        die("Error altering tire_inspections2 table: " . $conn->error);
    }
}

// Handle employee registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_employee'])) {
    $employee_id = $conn->real_escape_string($_POST['new_employee_id']);
    $name = $conn->real_escape_string($_POST['employee_name']);
    $user_type = $conn->real_escape_string($_POST['user_type']);
    
    $insertEmployee = "INSERT INTO employees (employee_id, name, user_type) 
                      VALUES ('$employee_id', '$name', '$user_type')";
    
    if ($conn->query($insertEmployee)) {
        $_SESSION['success_message'] = "Employee registered successfully!";
        header("Location: daily_defect.php");
        exit();
    } else {
        die("Error registering employee: " . $conn->error);
    }
}

// Handle form submission for inspection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inspection'])) {
    $serialNumber = $conn->real_escape_string($_POST['serialNumber']);
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $inspection_date = $conn->real_escape_string($_POST['inspection_date']);
    $shift = $conn->real_escape_string($_POST['shift']);

    // Fetch brand and tireCode based on serial number
    $tireDataQuery = "SELECT brand, tireCode FROM tire_data WHERE serialNumber = '$serialNumber'";
    $tireDataResult = $conn->query($tireDataQuery);
    if ($tireDataResult === false) {
        die("Query failed (tireDataQuery): " . $conn->error);
    }
    $tireData = $tireDataResult->fetch_assoc();
    $brand = $tireData['brand'] ?? '';
    $tireCode = $tireData['tireCode'] ?? '';

    // Prepare defect data
    $defects = [
        'back_rind', 'bead_wire_exposed', 'damage_contour', 'deformed_tread_block',
        'double_molding', 'flow_marks', 'clip_flow_marks', 'mechanical_damage',
        'lug_damage', 'mold_out_of_line', 'base_flow_marks', 'brand_separate',
        'cure_flash', 'base_air_bubble', 'side_wall_air_bubble', 'thick_flash',
        'vent_hole', 'peel'
    ];

    $values = [];
    foreach ($defects as $defect) {
        $values[$defect] = isset($_POST[$defect]) ? 1 : 0;
    }

    // Insert into tire_inspections2 table with date and shift
    $insertQuery = "INSERT INTO tire_inspections2 (
        serialNumber, brand, employee_id, inspection_date, shift, " . implode(',', $defects) . "
    ) VALUES (
        '$serialNumber', '$brand', '$employee_id', '$inspection_date', '$shift', " . implode(',', array_values($values)) . "
    )";

    if ($conn->query($insertQuery)) {
        // Set a success message in the session
        $_SESSION['success_message'] = "Inspection record added successfully!";
        // Redirect to the same page to prevent resubmission
        header("Location: daily_defect.php");
        exit();
    } else {
        die("Insert query failed: " . $conn->error);
    }
}

// Fetch serial numbers, brands, and tireCodes for dropdown
$serialNumbersQuery = "SELECT serialNumber, brand, tireCode FROM tire_data";
$serialNumbersResult = $conn->query($serialNumbersQuery);
if ($serialNumbersResult === false) {
    die("Query failed (serialNumbersQuery): " . $conn->error);
}

// Fetch inspection data for display
$inspectionQuery = "SELECT ti.*, td.tireCode, e.name as employee_name, e.user_type 
                   FROM tire_inspections2 ti 
                   LEFT JOIN employees e ON ti.employee_id = e.employee_id 
                   LEFT JOIN tire_data td ON ti.serialNumber = td.serialNumber
                   ORDER BY ti.created_at DESC";
$inspectionResult = $conn->query($inspectionQuery);
if ($inspectionResult === false) {
    die("Query failed (inspectionQuery): " . $conn->error);
}

// Fetch employee IDs
$employeeQuery = "SELECT employee_id, name, user_type FROM employees ORDER BY employee_id";
$employeeResult = $conn->query($employeeQuery);
if ($employeeResult === false || $employeeResult->num_rows == 0) {
    $employeeResult = $conn->query("SELECT DISTINCT employee_id FROM tire_inspections2 ORDER BY employee_id");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tire Inspection System</title>
    <!-- Adding Google Fonts for Open Sans and Cantarell -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Cantarell:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #343a40;
            --text-color: #000000;
            --bg-color: #f0f0f0;
            --border-color: #000000;
            --input-border: #CCCCCC;
            --card-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        body {
            background-color: var(--bg-color);
            font-family: 'Open Sans', sans-serif;
            padding-bottom: 40px;
            margin: 0;
            color: #333;
        }
        
        .header-logo {
            max-height: 60px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #000000 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-bottom: 4px solid var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .page-title {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            font-size: 28px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-link {
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            color: white !important;
            padding: 0.5rem 1rem;
            margin: 0 5px;
            border-radius: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-color);
            color: var(--text-color) !important;
        }
        
        .container {
            max-width: 1200px;
            padding: 0 20px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary-color), #FF9F45);
            color: var(--text-color);
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            font-size: 18px;
            padding: 15px 20px;
            border: none;
        }
        
        .card-header i {
            margin-right: 10px;
        }
        
        .card-body {
            padding: 25px;
            background-color: white;
        }
        
        .form-label {
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--input-border);
            padding: 12px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(242, 128, 24, 0.25);
        }
        
        .form-control:disabled, .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        
        .btn {
            border-radius: 40px;
            padding: 10px 25px;
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .btn-primary:hover {
            background-color: #e67615;
            border-color: #e67615;
            color: var(--text-color);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary:hover {
            background-color: #23272b;
            border-color: #23272b;
        }
        
        .section-title {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            color: var(--secondary-color);
            border-left: 4px solid var(--primary-color);
            padding-left: 15px;
            margin-bottom: 20px;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        /* Animation for the message */
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
        .highlight-message {
            font-size: 16px;
            color: white;
            background-color: black;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            animation: blink 2s infinite;
            border: 2px solid var(--primary-color);
        }
        
        /* Select2 custom styling */
        .select2-container--default .select2-selection--single {
            height: 47px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 47px;
            padding-left: 12px;
            color: var(--text-color);
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 45px;
        }
        
        .select2-dropdown {
            border-color: var(--input-border);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .measurement-group {
            position: relative;
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
        }
        
        .measurement-title {
            position: absolute;
            top: -12px;
            left: 20px;
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            font-size: 14px;
        }
        
        .footer {
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
            font-size: 14px;
            border-top: 3px solid var(--primary-color);
        }

        /* Additional styles from original file */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .header img {
            height: 60px;
        }

        .tabs {
            display: flex;
            gap: 5px;
            margin: 20px 0;
            border-bottom: 2px solid #dee2e6;
            padding: 0 20px;
        }
        
        .tab-link {
            padding: 10px 20px;
            font-size: 1.1em;
            font-weight: 600;
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s, border-bottom 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab-link:hover {
            color: var(--secondary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        .tab-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        .tab-content {
            display: none;
            padding: 20px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        h1 {
            font-size: 1.8em;
            text-align: center;
            color: var(--secondary-color);
            margin: 20px 0;
            font-weight: 700;
            font-family: 'Cantarell', sans-serif;
        }

        h2 {
            font-family: 'Cantarell', sans-serif;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 25px 0 15px;
            font-size: 1.5em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--secondary-color);
            font-family: 'Cantarell', sans-serif;
        }
        
        select, input[type="text"], input[list], input[type="password"], input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        select:focus, input[type="text"]:focus, input[list]:focus, input[type="password"]:focus, input[type="date"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 5px var(--primary-color);
            outline: none;
        }
        
        .defects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            padding: 15px 0;
        }
        
        .defect-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            transition: background 0.3s;
        }
        
        .defect-item:hover {
            background: #e9ecef;
        }
        
        input[type="checkbox"] {
            accent-color: var(--primary-color);
        }
        
        input[type="submit"], button {
            background-color: var(--secondary-color);
            color: #ffffff;
            padding: 12px 24px;
            border: none;
            border-radius: 40px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
        }
        
        input[type="submit"]:hover, button:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #ffffff;
            font-size: 0.85em;
            box-shadow: var(--card-shadow);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 10px 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        th {
            background-color: var(--secondary-color);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            font-family: 'Cantarell', sans-serif;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 0 20px 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            border-left: 4px solid #28a745;
        }
        
        #view, #employees {
            overflow-x: auto;
        }
        
        table th, table td {
            min-width: 50px;
        }
        
        th.defect-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            padding: 8px 4px;
            min-width: 30px;
        }
        
        .employee-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .employee-info {
            flex: 1;
        }
        
        .employee-info h3 {
            margin: 0 0 5px 0;
            color: var(--secondary-color);
            font-family: 'Cantarell', sans-serif;
        }
        
        .employee-info p {
            margin: 0;
            color: #6c757d;
        }
        
        .employee-type {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            font-family: 'Cantarell', sans-serif;
            letter-spacing: 0.5px;
        }
        
        .type-admin {
            background: #ffd166;
            color: #664d00;
        }
        
        .type-inspector {
            background: #06d6a0;
            color: #00382a;
        }
        
        .type-supervisor {
            background: #118ab2;
            color: #003546;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.7em;
            font-weight: 600;
            text-transform: uppercase;
            font-family: 'Cantarell', sans-serif;
        }
        
        /* Added subtitle style */
        .card-subtitle {
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            color: #555;
            margin-top: 5px;
            font-weight: normal;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; padding: 0 20px;">
            <img src="https://cdn.prod.website-files.com/64f8065a81be9734043da10e/64f818f083a1ce0044ce06a4_ATIRE-logo.png" alt="ATIRE Logo" class="header-logo">
            <h1 class="page-title">Tire Inspection System</h1>
            <div>
                <!-- Nav links could go here if needed -->
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Tabs for switching between sections -->
        <div class="tabs">
            <a href="javascript:void(0)" class="tab-link active" onclick="showTab('add-new')">Add New Inspection</a>
            <a href="javascript:void(0)" class="tab-link" onclick="showTab('view')">View Inspections</a>
            <a href="javascript:void(0)" class="tab-link" onclick="showTab('employees')">Manage Employees</a>
        </div>

        <!-- Display success message if set -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Add New Inspection Section -->
        <div id="add-new" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle"></i> DAILY DEFECTED TYRES - (BLACK)
                    <div class="card-subtitle">Record and submit new tire defect inspections for quality control</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <!-- Date and Shift Selection Row -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="inspection_date" class="form-label required-field">Inspection Date:</label>
                                <input type="date" name="inspection_date" id="inspection_date" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="shift" class="form-label required-field">Shift:</label>
                                <select name="shift" id="shift" required class="form-select">
                                    <option value="DAY A">DAY A</option>
                                    <option value="DAY B">DAY B</option>
                                    <option value="DAY C">DAY C</option>

                                    <option value="NIGHT A">NIGHT A</option>
                                    <option value="NIGHT B">NIGHT B</option>
                                    <option value="NIGHT C<">NIGHT C</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="serialNumber" class="form-label required-field">Select Serial Number:</label>
                                <select name="serialNumber" id="serialNumber" required onchange="updateTireInfo()" class="form-select">
                                    <option value="">Select Serial Number</option>
                                    <?php 
                                    // Reset result set pointer
                                    $serialNumbersResult->data_seek(0); 
                                    while ($row = $serialNumbersResult->fetch_assoc()): ?>
                                        <option value="<?php echo $row['serialNumber']; ?>" 
                                                data-brand="<?php echo $row['brand']; ?>"
                                                data-tirecode="<?php echo $row['tireCode']; ?>">
                                            <?php echo $row['serialNumber']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="brand" class="form-label">Brand:</label>
                                <input type="text" id="brand" readonly class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="tireCode" class="form-label">Tire Code:</label>
                                <input type="text" id="tireCode" readonly class="form-control">
                            </div>
                        </div>

                        <div class="measurement-group">
                            <div class="measurement-title">Defects Inspection</div>
                            <div class="defects-grid">
                                <?php
                                $defects = [
                                    'back_rind' => 'Back Rind',
                                    'bead_wire_exposed' => 'Bead Wire Exposed',
                                    'damage_contour' => 'Damage Contour',
                                    'deformed_tread_block' => 'Deformed Tread Block',
                                    'double_molding' => 'Double Molding',
                                    'flow_marks' => 'Flow Marks',
                                    'clip_flow_marks' => 'Clip Flow Marks',
                                    'mechanical_damage' => 'Mechanical Damage',
                                    'lug_damage' => 'Lug Damage',
                                    'mold_out_of_line' => 'Mold Out of Line',
                                    'base_flow_marks' => 'Base Flow Marks',
                                    'brand_separate' => 'Brand Separate',
                                    'cure_flash' => 'Cure Flash',
                                    'base_air_bubble' => 'Base Air Bubble',
                                    'side_wall_air_bubble' => 'Side Wall Air Bubble',
                                    'thick_flash' => 'Thick Flash',
                                    'vent_hole' => 'Vent Hole',
                                    'peel' => 'Peel'
                                ];
                                foreach ($defects as $key => $label): ?>
                                    <div class="defect-item">
                                        <input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>">
                                        <label for="<?php echo $key; ?>"><?php echo $label; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="employee_id" class="form-label required-field">Inspector ID:</label>
                                <select name="employee_id" id="employee_id" required class="form-select">
                                    <option value="">Select Inspector</option>
                                    <?php while ($employee = $employeeResult->fetch_assoc()): ?>
                                        <option value="<?php echo $employee['employee_id']; ?>">
                                            <?php 
                                            if (isset($employee['name'])) {
                                                echo $employee['employee_id'] . ' - ' . $employee['name'];
                                            } else {
                                                echo $employee['employee_id'];
                                            }
                                            ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="text-align: center; margin-top: 20px;">
                            <input type="submit" name="submit_inspection" value="Submit Inspection" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Inspections Section -->
        <div id="view" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-table"></i> Inspection Records
                    <div class="card-subtitle">View all inspection records with detailed defect data</div>
                </div>
                <div class="card-body">
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Shift</th>
                                    <th>Serial Number</th>
                                    <th>Brand</th>
                                    <th>Tire Code</th>
                                    <th>Inspector</th>
                                    <?php foreach ($defects as $key => $label): ?>
                                        <th class="defect-header" title="<?php echo $label; ?>"><?php echo $label; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($inspection = $inspectionResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $inspection['inspection_date']; ?></td>
                                    <td><?php echo $inspection['shift']; ?></td>
                                    <td><?php echo $inspection['serialNumber']; ?></td>
                                    <td><?php echo $inspection['brand']; ?></td>
                                    <td><?php echo $inspection['tireCode']; ?></td>
                                    <td><?php echo $inspection['employee_name'] ?? $inspection['employee_id']; ?></td>
                                    <?php foreach ($defects as $key => $label): ?>
                                        <td><?php echo $inspection[$key] ? 'Yes' : 'No'; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Employees Section -->
        <div id="employees" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i> Employee Management
                    <div class="card-subtitle">Register new employees and view existing employee information</div>
                </div>
                <div class="card-body">
                    <h2>Register New Employee</h2>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_employee_id" class="form-label required-field">Employee ID:</label>
                                <input type="text" name="new_employee_id" id="new_employee_id" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="employee_name" class="form-label required-field">Employee Name:</label>
                                <input type="text" name="employee_name" id="employee_name" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="user_type" class="form-label required-field">User Type:</label>
                                <select name="user_type" id="user_type" required class="form-select">
                                    <option value="inspector">Inspector</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="text-align: center; margin-top: 20px;">
                            <input type="submit" name="register_employee" value="Register Employee" class="btn btn-primary">
                        </div>
                    </form>

                    <h2>Existing Employees</h2>
                    <?php
                    // Fetch all employees to display
                    $employeeListQuery = "SELECT * FROM employees ORDER BY created_at DESC";
                    $employeeListResult = $conn->query($employeeListQuery);
                    
                    if ($employeeListResult && $employeeListResult->num_rows > 0) {
                        while ($employee = $employeeListResult->fetch_assoc()): ?>
                            <div class="employee-card">
                                <div class="employee-info">
                                    <h3><?php echo $employee['name']; ?></h3>
                                    <p>Employee ID: <?php echo $employee['employee_id']; ?></p>
                                </div>
                                <div class="employee-type type-<?php echo $employee['user_type']; ?>">
                                    <?php echo ucfirst($employee['user_type']); ?>
                                </div>
                            </div>
                        <?php endwhile;
                    } else {
                        echo '<p>No employees registered yet.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>© <?php echo date('Y'); ?> Tire Inspection System. All rights reserved.</p>
        </div>
    </div>

    <!-- Add your JavaScript here -->
    <script>
        // Function to show selected tab
        function showTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab links
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked tab link
            event.target.classList.add('active');
        }
        
        // Function to update tire info when serial number is selected
        function updateTireInfo() {
            const select = document.getElementById('serialNumber');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('brand').value = selectedOption.getAttribute('data-brand');
                document.getElementById('tireCode').value = selectedOption.getAttribute('data-tirecode');
            } else {
                document.getElementById('brand').value = '';
                document.getElementById('tireCode').value = '';
            }
        }
        
        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('inspection_date').value = today;
        });
    </script>
</body>
</html>