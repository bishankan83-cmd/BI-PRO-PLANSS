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
$serialNumber = "";
$brand = "";
$errorMessage = "";
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "";
unset($_SESSION['success_message']);

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

// Fetch employee IDs for datalist
$employeeQuery = "SELECT DISTINCT employee_id FROM tire_defect_nm";
$employeeResult = $conn->query($employeeQuery);
if ($employeeResult === false) {
    die("Query failed (employeeQuery): " . $conn->error);
}
$employeeIds = [];
while ($employeeResult && $row = $employeeResult->fetch_assoc()) {
    $employeeIds[] = $row['employee_id'];
}

// Define defects array with labels
$defects = [
    'back_rind' => 'Back Rind',
    'bead_wire_exposed' => 'Bead Wire Exposed',
    'damage_contour' => 'Damage Contour',
    'deformed_tread_bloc' => 'Deformed Tread Bloc',
    'double_molding' => 'Double Molding',
    'flow_marks' => 'Flow Marks',
    'foriegn_material' => 'Foreign Material',
    'nm_flow_clip' => 'NM Flow Clip',
    'mold_out_of_line' => 'Mold Out of Line',
    'side_wall_open_black' => 'Side Wall Open Black',
    'black_mix' => 'Black Mix',
    'peel' => 'Peel',
    'base_flow_marks' => 'Base Flow Marks',
    'cure_flash' => 'Cure Flash',
    'band_edge_cushion' => 'Band Edge Cushion',
    'band_seperate' => 'Band Separate'
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inspection'])) {
    $serialNumber = $conn->real_escape_string($_POST['serialNumber']);
    $h1 = isset($_POST['h1']) ? $conn->real_escape_string($_POST['h1']) : '';
    $h2 = isset($_POST['h2']) ? $conn->real_escape_string($_POST['h2']) : '';
    $h3 = isset($_POST['h3']) ? $conn->real_escape_string($_POST['h3']) : '';
    $h4 = isset($_POST['h4']) ? $conn->real_escape_string($_POST['h4']) : '';
    $us1 = isset($_POST['us1']) ? $conn->real_escape_string($_POST['us1']) : '';
    $us2 = isset($_POST['us2']) ? $conn->real_escape_string($_POST['us2']) : '';
    $us3 = isset($_POST['us3']) ? $conn->real_escape_string($_POST['us3']) : '';
    $us4 = isset($_POST['us4']) ? $conn->real_escape_string($_POST['us4']) : '';
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $notes = isset($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : '';

    // Validate form data
    $formValid = true;
    if (empty($serialNumber)) {
        $errorMessage = "Serial Number is required.";
        $formValid = false;
    }
    if (empty($employee_id)) {
        $errorMessage = "Employee ID is required.";
        $formValid = false;
    }
    // Validate hardness measurements
    if (!empty($h1) && !is_numeric($h1) || !empty($h2) && !is_numeric($h2) || 
        !empty($h3) && !is_numeric($h3) || !empty($h4) && !is_numeric($h4)) {
        $errorMessage = "All hardness values must be numeric.";
        $formValid = false;
    }
    // Validate ultrasonic measurements
    if (!empty($us1) && !is_numeric($us1) || !empty($us2) && !is_numeric($us2) || 
        !empty($us3) && !is_numeric($us3) || !empty($us4) && !is_numeric($us4)) {
        $errorMessage = "All ultrasonic values must be numeric.";
        $formValid = false;
    }

    if ($formValid) {
        // Fetch brand based on serial number
        $brandQuery = "SELECT brand FROM tire_data WHERE serialNumber = ?";
        $stmt = $conn->prepare($brandQuery);
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $brandResult = $stmt->get_result();
        if ($brandResult === false) {
            die("Query failed (brandQuery): " . $conn->error);
        }
        $brand = $brandResult->fetch_assoc()['brand'] ?? '';
        $stmt->close();

        // Prepare defect data
        $values = [];
        foreach ($defects as $defect => $label) {
            $values[$defect] = isset($_POST[$defect]) ? 1 : 0;
        }

        // Insert into tire_defect_nm table
        $columns = array_merge(
            ['serialNumber', 'brand', 'h1', 'h2', 'h3', 'h4', 'us1', 'us2', 'us3', 'us4', 'employee_id', 'notes'],
            array_keys($defects)
        );
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $types = str_repeat('s', 2) . str_repeat('d', 8) . str_repeat('s', 2) . str_repeat('i', count($defects));
        $insertQuery = "INSERT INTO tire_defect_nm (" . implode(',', $columns) . ") VALUES ($placeholders)";
        
        $stmt = $conn->prepare($insertQuery);
        $bindParams = array_merge(
            [$serialNumber, $brand, $h1, $h2, $h3, $h4, $us1, $us2, $us3, $us4, $employee_id, $notes],
            array_values($values)
        );
        $stmt->bind_param($types, ...$bindParams);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Record added successfully!";
            header("Location: daily_defect_nm_nm.php");
            exit();
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRE - Tire Inspection System</title>
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
            max-width: 1200px;
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
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
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
        
        .defects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .defect-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--input-border);
            transition: all 0.3s ease;
        }
        
        .defect-item:hover {
            border-color: var(--primary-color);
            background-color: rgba(242, 128, 24, 0.05);
        }
        
        .form-check {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
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
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="atire.png" alt="ATIRE Logo" class="header-logo me-3">
                    <h1 class="page-title">Tire Inspection System</h1>
                </div>
                <nav>
                    
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Tire Defect Inspection</h2>
            <p class="text-muted">Record defects and measurements for tire quality inspection</p>
        </div>

        <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle"></i> Register Defected Tire
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="serialNumber" class="form-label required-field">Serial Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <select class="form-select select2" id="serialNumber" name="serialNumber" required>
                                    <option value="">Select a Serial Number</option>
                                    <?php foreach ($serialNumbers as $sn): ?>
                                    <option value="<?php echo htmlspecialchars($sn['serialNumber']); ?>" 
                                            data-brand="<?php echo htmlspecialchars($sn['brand']); ?>"
                                            <?php echo ($sn['serialNumber'] === $serialNumber) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sn['serialNumber']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="brand" class="form-label">Brand</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="<?php echo htmlspecialchars($brand); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="employee_id" class="form-label required-field">Employee ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-check"></i></span>
                                <input list="employee_ids" class="form-control" id="employee_id" name="employee_id" required>
                                <datalist id="employee_ids">
                                    <?php foreach ($employeeIds as $eid): ?>
                                    <option value="<?php echo htmlspecialchars($eid); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                    </div>

                    <div class="measurement-group">
                        <div class="measurement-title">Hardness Measurements</div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="h1" class="form-label">Hardness 1</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="h1" name="h1" placeholder="e.g. 65">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="h2" class="form-label">Hardness 2</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="h2" name="h2" placeholder="e.g. 64">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="h3" class="form-label">Hardness 3</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="h3" name="h3" placeholder="e.g. 66">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="h4" class="form-label">Hardness 4</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="h4" name="h4" placeholder="e.g. 65">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="measurement-group">
                        <div class="measurement-title">Ultrasonic Measurements</div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="us1" class="form-label">US 1</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us1" name="us1" placeholder="e.g. 59.5">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="us2" class="form-label">US 2</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us2" name="us2" placeholder="e.g. 58.5">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="us3" class="form-label">US 3</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us3" name="us3" placeholder="e.g. 59.0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="us4" class="form-label">US 4</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                    <input type="number" step="0.01" class="form-control" id="us4" name="us4" placeholder="e.g. 59.2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="measurement-group">
                        <div class="measurement-title">Defect Types</div>
                        <div class="defects-grid">
                            <?php foreach ($defects as $key => $label): ?>
                            <div class="defect-item">
                                <input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" class="form-check">
                                <label for="<?php echo $key; ?>" class="form-label"><?php echo $label; ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="notes" class="form-label">Notes</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional observations or notes"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="highlight-message mb-4">
                        Please ensure all measurements and defect selections are accurate before submission
                    </div>

                    <div class="text-center">
                        <button type="submit" name="submit_inspection" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i> Save Record
                        </button>
                        <button type="reset" class="btn btn-secondary px-5 ms-3">
                            <i class="fas fa-undo me-2"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ATIRE Tire Inspection System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: "Select a Serial Number",
                allowClear: true,
                width: '100%'
            });

            // Update brand based on serial number
            $('#serialNumber').on('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const brandInput = $('#brand');
                if (this.selectedIndex > 0) {
                    brandInput.val(selectedOption.getAttribute('data-brand'));
                } else {
                    brandInput.val('');
                }
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
            $('input, select, textarea').on('change', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>