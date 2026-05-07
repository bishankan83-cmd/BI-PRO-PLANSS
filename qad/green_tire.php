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

// Initialize variables
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inspection'])) {
    $date = $conn->real_escape_string($_POST['date']);
    $shift_time = $conn->real_escape_string($_POST['shift_time']);
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $serialNumbers = $_POST['serialNumber'] ?? [];
    $tyre_sizes = $_POST['tyre_size'] ?? [];
    $brands = $_POST['brand'] ?? [];
    $m_no_1s = $_POST['m_no_1'] ?? [];
    $temps = $_POST['temp'] ?? [];
    $width_1s = $_POST['width_1'] ?? [];
    $diams = $_POST['diam'] ?? [];
    $end_time_1s = $_POST['end_time_1'] ?? [];
    $m_no_2s = $_POST['m_no_2'] ?? [];
    $cushions = $_POST['cushion'] ?? [];
    $end_2s = $_POST['end_2'] ?? [];
    $m_no_3s = $_POST['m_no_3'] ?? [];
    $treads = $_POST['tread'] ?? [];
    $width_2s = $_POST['width_2'] ?? [];
    $diam_2s = $_POST['diam_2'] ?? [];
    $end_time_2s = $_POST['end_time_2'] ?? [];
    $curing_start_times = $_POST['curing_start_time'] ?? [];
    $press_nos = $_POST['press_no'] ?? [];

    // Insert each row into tire_production_f table
    for ($i = 0; $i < count($serialNumbers); $i++) {
        if (empty($serialNumbers[$i])) continue; // Skip empty rows

        $serialNumber = $conn->real_escape_string($serialNumbers[$i]);
        $tyre_size = $conn->real_escape_string($tyre_sizes[$i] ?? '');
        $brand = $conn->real_escape_string($brands[$i] ?? '');
        $m_no_1 = $conn->real_escape_string($m_no_1s[$i] ?? '');
        $temp = $conn->real_escape_string($temps[$i] ?? '');
        $width_1 = $conn->real_escape_string($width_1s[$i] ?? '');
        $diam = $conn->real_escape_string($diams[$i] ?? '');
        $end_time_1 = $conn->real_escape_string($end_time_1s[$i] ?? '');
        $m_no_2 = $conn->real_escape_string($m_no_2s[$i] ?? '');
        $cushion = $conn->real_escape_string($cushions[$i] ?? '');
        $end_2 = $conn->real_escape_string($end_2s[$i] ?? '');
        $m_no_3 = $conn->real_escape_string($m_no_3s[$i] ?? '');
        $tread = $conn->real_escape_string($treads[$i] ?? '');
        $width_2 = $conn->real_escape_string($width_2s[$i] ?? '');
        $diam_2 = $conn->real_escape_string($diam_2s[$i] ?? '');
        $end_time_2 = $conn->real_escape_string($end_time_2s[$i] ?? '');
        $curing_start_time = $conn->real_escape_string($curing_start_times[$i] ?? '');
        $press_no = $conn->real_escape_string($press_nos[$i] ?? '');

        $insertQuery = "INSERT INTO tire_production_f (
            date, shift_time, serialNumber, tyre_size, brand, m_no_1, temp, width_1, diam, end_time_1,
            m_no_2, cushion, end_2, m_no_3, tread, width_2, diam_2, end_time_2, curing_start_time, press_no, employee_id
        ) VALUES (
            '$date', '$shift_time', '$serialNumber', '$tyre_size', '$brand', '$m_no_1', '$temp', '$width_1', '$diam', '$end_time_1',
            '$m_no_2', '$cushion', '$end_2', '$m_no_3', '$tread', '$width_2', '$diam_2', '$end_time_2', '$curing_start_time', '$press_no', '$employee_id'
        )";

        if (!$conn->query($insertQuery)) {
            die("Insert query failed: " . $conn->error);
        }
    }

    // Set a success message in the session
    $_SESSION['success_message'] = "Production records added successfully!";
    // Redirect to the same page to prevent resubmission
    header("Location: green_tire.php");
    exit();
}

// Fetch serial numbers for dropdown
$serialNumbersQuery = "SELECT serialNumber, brand FROM tire_data";
$serialNumbersResult = $conn->query($serialNumbersQuery);
if ($serialNumbersResult === false) {
    die("Query failed (serialNumbersQuery): " . $conn->error);
}
$serialNumbers = [];
while ($row = $serialNumbersResult->fetch_assoc()) {
    $serialNumbers[] = $row;
}

// Fetch employee IDs
$employeeQuery = "SELECT DISTINCT employee_id FROM employees ORDER BY employee_id";
$employeeResult = $conn->query($employeeQuery);
if ($employeeResult === false) {
    $employeeResult = $conn->query("SELECT DISTINCT employee_id FROM tire_inspections ORDER BY employee_id");
}
$employeeIds = [];
if ($employeeResult) {
    while ($row = $employeeResult->fetch_assoc()) {
        $employeeIds[] = $row['employee_id'];
    }
}

// Fetch submitted data for display
$submittedDataQuery = "SELECT date, shift_time, serialNumber, tyre_size, brand, m_no_1, temp, width_1, diam, end_time_1,
    m_no_2, cushion, end_2, m_no_3, tread, width_2, diam_2, end_time_2, curing_start_time, press_no
    FROM tire_production_f ORDER BY created_at DESC";
$submittedDataResult = $conn->query($submittedDataQuery);
if ($submittedDataResult === false) {
    die("Query failed (submittedDataQuery): " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Tire Building Checklist</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-color);
            color: var(--text-color) !important;
        }
        
        .container {
            max-width: 1400px;
            padding: 0 20px;
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
        
        .table {
            font-size: 0.9rem;
        }
        
        .table th {
            background-color: var(--secondary-color);
            color: white;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        
        .table input, .table select {
            font-size: 0.9rem;
            padding: 6px;
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
        
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
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
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="https://cdn.prod.website-files.com/64f8065a81be9734043da10e/64f818f083a1ce0044ce06a4_ATIRE-logo.png" alt="ATIRE Logo" class="header-logo me-3">
                    <h1 class="page-title">Green Tire Building Checklist</h1>
                </div>
                <nav>
                    
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">GREEN TIRE BUILDING CHECKLIST</h2>
            <p class="text-muted">Enter and review production data for green tires</p>
        </div>

        <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-clipboard-list"></i> Production Data Entry
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="date" class="form-label required-field">Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" name="date" id="date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="shift_time" class="form-label required-field">Shift Time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                <select name="shift_time" id="shift_time" class="form-select" required>
                                    <option value="">Select Shift</option>
                                    <option value="Day A">Day A</option>
                                    <option value="Day B">Day B</option>
                                    <option value="Day C">Day C</option>
                                    <option value="Night A">Night A</option>
                                    <option value="Night B">Night B</option>
                                    <option value="Night C">Night C</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="measurement-group">
                        <div class="measurement-title">Tire Production Details</div>
                        <table class="table table-bordered" id="production-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" scope="col">No.</th>
                                    <th rowspan="2" scope="col">Serial No.</th>
                                    <th rowspan="2" scope="col">Tyre Size</th>
                                    <th rowspan="2" scope="col">Brand</th>
                                    <th colspan="5" scope="col">Base</th>
                                    <th colspan="3" scope="col">Cushion</th>
                                    <th colspan="5" scope="col">Tread</th>
                                    <th rowspan="2" scope="col">Curing Start Time</th>
                                    <th rowspan="2" scope="col">Press No.</th>
                                    <th rowspan="2" scope="col">Action</th>
                                </tr>
                                <tr>
                                    <th scope="col">M.No.</th>
                                    <th scope="col">Temp</th>
                                    <th scope="col">Width</th>
                                    <th scope="col">Diam</th>
                                    <th scope="col">End Time</th>
                                    <th scope="col">M.No.</th>
                                    <th scope="col">Cushion</th>
                                    <th scope="col">End</th>
                                    <th scope="col">M.No.</th>
                                    <th scope="col">Tread</th>
                                    <th scope="col">Width</th>
                                    <th scope="col">Diam</th>
                                    <th scope="col">End Time</th>
                                </tr>
                            </thead>
                            <tbody id="production-table-body">
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <select name="serialNumber[]" class="form-select select2" onchange="updateBrand(this, 0)">
                                            <option value="">Select Serial Number</option>
                                            <?php foreach ($serialNumbers as $sn): ?>
                                                <option value="<?php echo htmlspecialchars($sn['serialNumber']); ?>" 
                                                        data-brand="<?php echo htmlspecialchars($sn['brand']); ?>">
                                                    <?php echo htmlspecialchars($sn['serialNumber']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="tyre_size[]" class="form-control"></td>
                                    <td><input type="text" name="brand[]" class="form-control brand-input" readonly></td>
                                    <!-- Base -->
                                    <td><input type="text" name="m_no_1[]" class="form-control"></td>
                                    <td><input type="text" name="temp[]" class="form-control"></td>
                                    <td><input type="text" name="width_1[]" class="form-control"></td>
                                    <td><input type="text" name="diam[]" class="form-control"></td>
                                    <td><input type="text" name="end_time_1[]" class="form-control"></td>
                                    <!-- Cushion -->
                                    <td><input type="text" name="m_no_2[]" class="form-control"></td>
                                    <td><input type="text" name="cushion[]" class="form-control"></td>
                                    <td><input type="text" name="end_2[]" class="form-control"></td>
                                    <!-- Tread -->
                                    <td><input type="text" name="m_no_3[]" class="form-control"></td>
                                    <td><input type="text" name="tread[]" class="form-control"></td>
                                    <td><input type="text" name="width_2[]" class="form-control"></td>
                                    <td><input type="text" name="diam_2[]" class="form-control"></td>
                                    <td><input type="text" name="end_time_2[]" class="form-control"></td>
                                    <!-- Curing Start Time and Press No. -->
                                    <td><input type="text" name="curing_start_time[]" class="form-control"></td>
                                    <td><input type="text" name="press_no[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-secondary btn-sm remove-button" onclick="removeRow(this)">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mt-3" onclick="addTableRow()">Add New Row</button>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label required-field">Employee ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <select name="employee_id" id="employee_id" class="form-select select2" required>
                                    <option value="">Select Employee ID</option>
                                    <?php foreach ($employeeIds as $eid): ?>
                                        <option value="<?php echo htmlspecialchars($eid); ?>">
                                            <?php echo htmlspecialchars($eid); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="highlight-message mb-4">
                        Please ensure all measurements are accurate before submission
                    </div>

                    <div class="text-center">
                        <button type="submit" name="submit_inspection" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i> Submit Production Data
                        </button>
                        <button type="reset" class="btn btn-secondary px-5 ms-3">
                            <i class="fas fa-undo me-2"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-table"></i> Submitted Production Data
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2" scope="col">Date</th>
                            <th rowspan="2" scope="col">Shift Time</th>
                            <th rowspan="2" scope="col">Serial No.</th>
                            <th rowspan="2" scope="col">Tyre Size</th>
                            <th rowspan="2" scope="col">Brand</th>
                            <th colspan="5" scope="col">Base</th>
                            <th colspan="3" scope="col">Cushion</th>
                            <th colspan="5" scope="col">Tread</th>
                            <th rowspan="2" scope="col">Curing Start Time</th>
                            <th rowspan="2" scope="col">Press No.</th>
                        </tr>
                        <tr>
                            <th scope="col">M.No.</th>
                            <th scope="col">Temp</th>
                            <th scope="col">Width</th>
                            <th scope="col">Diam</th>
                            <th scope="col">End Time</th>
                            <th scope="col">M.No.</th>
                            <th scope="col">Cushion</th>
                            <th scope="col">End</th>
                            <th scope="col">M.No.</th>
                            <th scope="col">Tread</th>
                            <th scope="col">Width</th>
                            <th scope="col">Diam</th>
                            <th scope="col">End Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $submittedDataResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['date'] ?: '-'; ?></td>
                                <td><?php echo $row['shift_time'] ?: '-'; ?></td>
                                <td><?php echo $row['serialNumber'] ?: '-'; ?></td>
                                <td><?php echo $row['tyre_size'] ?: '-'; ?></td>
                                <td><?php echo $row['brand'] ?: '-'; ?></td>
                                <!-- Base -->
                                <td><?php echo $row['m_no_1'] ?: '-'; ?></td>
                                <td><?php echo $row['temp'] ?: '-'; ?></td>
                                <td><?php echo $row['width_1'] ?: '-'; ?></td>
                                <td><?php echo $row['diam'] ?: '-'; ?></td>
                                <td><?php echo $row['end_time_1'] ?: '-'; ?></td>
                                <!-- Cushion -->
                                <td><?php echo $row['m_no_2'] ?: '-'; ?></td>
                                <td><?php echo $row['cushion'] ?: '-'; ?></td>
                                <td><?php echo $row['end_2'] ?: '-'; ?></td>
                                <!-- Tread -->
                                <td><?php echo $row['m_no_3'] ?: '-'; ?></td>
                                <td><?php echo $row['tread'] ?: '-'; ?></td>
                                <td><?php echo $row['width_2'] ?: '-'; ?></td>
                                <td><?php echo $row['diam_2'] ?: '-'; ?></td>
                                <td><?php echo $row['end_time_2'] ?: '-'; ?></td>
                                <!-- Curing Start Time and Press No. -->
                                <td><?php echo $row['curing_start_time'] ?: '-'; ?></td>
                                <td><?php echo $row['press_no'] ?: '-'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> ATIRE Quality System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);

            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;

                $(this).find('[required]').each(function() {
                    if ($(this).val() === '') {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields marked with *');
                }
            });

            // Remove validation styling on input
            $('input, select').on('change', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
        });

        let rowCount = 1;

        function updateBrand(selectElement, rowIndex) {
            const brandInput = document.querySelector(`#production-table-body tr:nth-child(${rowIndex + 1}) .brand-input`);
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            brandInput.value = selectedOption.dataset.brand || '';
        }

        function addTableRow() {
            rowCount++;
            const tbody = document.getElementById('production-table-body');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${rowCount}</td>
                <td>
                    <select name="serialNumber[]" class="form-select select2" onchange="updateBrand(this, ${rowCount - 1})">
                        <option value="">Select Serial Number</option>
                        <?php foreach ($serialNumbers as $sn): ?>
                            <option value="<?php echo htmlspecialchars($sn['serialNumber']); ?>" 
                                    data-brand="<?php echo htmlspecialchars($sn['brand']); ?>">
                                <?php echo htmlspecialchars($sn['serialNumber']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="tyre_size[]" class="form-control"></td>
                <td><input type="text" name="brand[]" class="form-control brand-input" readonly></td>
                <!-- Base -->
                <td><input type="text" name="m_no_1[]" class="form-control"></td>
                <td><input type="text" name="temp[]" class="form-control"></td>
                <td><input type="text" name="width_1[]" class="form-control"></td>
                <td><input type="text" name="diam[]" class="form-control"></td>
                <td><input type="text" name="end_time_1[]" class="form-control"></td>
                <!-- Cushion -->
                <td><input type="text" name="m_no_2[]" class="form-control"></td>
                <td><input type="text" name="cushion[]" class="form-control"></td>
                <td><input type="text" name="end_2[]" class="form-control"></td>
                <!-- Tread -->
                <td><input type="text" name="m_no_3[]" class="form-control"></td>
                <td><input type="text" name="tread[]" class="form-control"></td>
                <td><input type="text" name="width_2[]" class="form-control"></td>
                <td><input type="text" name="diam_2[]" class="form-control"></td>
                <td><input type="text" name="end_time_2[]" class="form-control"></td>
                <!-- Curing Start Time and Press No. -->
                <td><input type="text" name="curing_start_time[]" class="form-control"></td>
                <td><input type="text" name="press_no[]" class="form-control"></td>
                <td><button type="button" class="btn btn-secondary btn-sm remove-button" onclick="removeRow(this)">Remove</button></td>
            `;
            tbody.appendChild(newRow);
            // Reinitialize Select2 for the new row
            $(newRow).find('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        }

        function removeRow(button) {
            const row = button.closest('tr');
            $(row).find('.select2').select2('destroy'); // Destroy Select2 instance
            row.remove();
            // Update row numbers
            const rows = document.querySelectorAll('#production-table-body tr');
            rows.forEach((row, index) => {
                row.cells[0].textContent = index + 1;
            });
            rowCount = rows.length;
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>