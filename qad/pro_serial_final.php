<?php
// Database connection parameters
$host = "localhost";
$dbname = "planatir_task_managemen";
$username = "planatir_task_managemen";
$password = "Bishan@1919";

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch all serial numbers for dropdown
function getAllSerialNumbers($pdo) {
    $stmt = $pdo->query("SELECT serialNumber, brand FROM tire_data ORDER BY serialNumber");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get tire details by serial number
function getTireDetails($pdo, $serialNumber) {
    $stmt = $pdo->prepare("SELECT t.* FROM tire_data t WHERE t.serialNumber = ?");
    $stmt->execute([$serialNumber]);
    $tireData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tireData) {
        $stmt = $pdo->prepare("SELECT td.description FROM tire_details td WHERE td.icode = ?");
        $stmt->execute([$tireData['tireCode']]);
        $tireDetail = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $tireData['description'] = $tireDetail && isset($tireDetail['description']) ? $tireDetail['description'] : 'No description available';
    }
    
    return $tireData;
}

// Check if inspection exists for a serial number
function checkInspectionExists($pdo, $serialNumber) {
    $stmt = $pdo->prepare("SELECT * FROM tire_inspections WHERE serialNumber = ? ORDER BY inspectionDate DESC LIMIT 1");
    $stmt->execute([$serialNumber]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Save inspection data
function saveInspectionData($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tire_inspections 
            (serialNumber, inspectionDate, innerDiameter1, innerDiameter2, innerDiameter3, innerDiameter4, 
            width, hardness1, hardness2, gaugeOK, gaugeCenter, gaugeEdge, checkedBy, tireCode, brand, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['serialNumber'], 
            $data['inspectionDate'], 
            $data['innerDiameter1'],
            $data['innerDiameter2'],
            $data['innerDiameter3'],
            $data['innerDiameter4'],
            $data['width'],
            $data['hardness1'],
            $data['hardness2'],
            $data['gaugeOK'],
            $data['gaugeCenter'],
            $data['gaugeEdge'],
            $data['checkedBy'],
            $data['tireCode'],
            $data['brand'],
            $data['description']
        ]);
        
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

// Process form submission
$message = '';
$messageType = '';
$selectedTire = null;
$existingInspection = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fetch_tire'])) {
        if (!empty($_POST['serialNumber'])) {
            $selectedTire = getTireDetails($pdo, $_POST['serialNumber']);
            $existingInspection = checkInspectionExists($pdo, $_POST['serialNumber']);
            
            if ($existingInspection) {
                $message = "Warning: An inspection record already exists for this serial number. The latest inspection was on " . $existingInspection['inspectionDate'] . ".";
                $messageType = 'warning';
            }
        } else {
            $message = "Please select a serial number.";
            $messageType = 'danger';
        }
    } elseif (isset($_POST['save_inspection'])) {
        $inspectionData = [
            'serialNumber' => $_POST['serialNumber'],
            'inspectionDate' => $_POST['inspectionDate'],
            'innerDiameter1' => $_POST['innerDiameter1'],
            'innerDiameter2' => $_POST['innerDiameter2'],
            'innerDiameter3' => $_POST['innerDiameter3'],
            'innerDiameter4' => $_POST['innerDiameter4'],
            'width' => $_POST['width'],
            'hardness1' => $_POST['hardness1'],
            'hardness2' => $_POST['hardness2'],
            // Convert checkbox values to 1 or 0
            'gaugeOK' => isset($_POST['gaugeOK']) ? 1 : 0,
            'gaugeCenter' => isset($_POST['gaugeCenter']) ? 1 : 0,
            'gaugeEdge' => isset($_POST['gaugeEdge']) ? 1 : 0,
            'checkedBy' => $_POST['checkedBy'],
            'tireCode' => $_POST['tireCode'],
            'brand' => $_POST['brand'],
            'description' => $_POST['description']
        ];
        
        // Validate numeric inputs (excluding gauge fields which are now checkboxes)
        $numericFields = ['innerDiameter1', 'innerDiameter2', 'innerDiameter3', 'innerDiameter4', 'width', 'hardness1', 'hardness2'];
        $formValid = true;
        foreach ($numericFields as $field) {
            if (!is_numeric($inspectionData[$field])) {
                $message = "All measurement values must be numeric.";
                $messageType = 'danger';
                $formValid = false;
                break;
            }
        }
        
        if (empty($inspectionData['checkedBy'])) {
            $message = "Please enter who checked this record.";
            $messageType = 'danger';
            $formValid = false;
        }
        
        if ($formValid) {
            $result = saveInspectionData($pdo, $inspectionData);
            if ($result === true) {
                $message = "Inspection data saved successfully!";
                $messageType = 'success';
                $selectedTire = null; // Reset form after successful save
            } else {
                $message = "Error: " . $result;
                $messageType = 'danger';
            }
        }
    }
}

$serialNumbers = getAllSerialNumbers($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRE - Final Inspection Book</title>
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
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
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
        
        .footer {
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 40px;
            font-size: 14px;
            border-top: 3px solid var(--primary-color);
        }
        
        .existing-record {
            background-color: #e2f0fd;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #4a89dc;
            margin-bottom: 25px;
        }
        
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            border-collapse: collapse;
        }
        
        .table th {
            background-color: #343a40;
            color:rgb(242, 244, 247);
            font-family: 'Cantarell', sans-serif;
            font-weight: 600;
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        
        .table td {
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Custom checkbox styling */
        .custom-checkbox {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            background-color: white;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .custom-checkbox:hover {
            border-color: var(--primary-color);
            background-color: #f8f9fa;
        }
        
        .custom-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }
        
        .custom-checkbox label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="atire.png" alt="ATIRE Logo" class="header-logo me-3">
                    <h1 class="page-title">Quality System</h1>
                </div>
                <nav>
                    
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">FINAL INSPECTION BOOK - PQR</h2>
            <p class="text-muted">Document No: C39 | Quality Assurance Department</p>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-circle' : 'exclamation-triangle'); ?> me-2"></i> 
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-search"></i> Select Tire
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-9">
                            <label for="serialNumber" class="form-label required-field">Serial Number</label>
                            <select class="form-select select2" id="serialNumber" name="serialNumber" required>
                                <option value="">Select a Serial Number</option>
                                <?php foreach ($serialNumbers as $tire): ?>
                                <option value="<?php echo htmlspecialchars($tire['serialNumber']); ?>" <?php echo (isset($_POST['serialNumber']) && $_POST['serialNumber'] == $tire['serialNumber']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tire['serialNumber']); ?> (<?php echo htmlspecialchars($tire['brand']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" name="fetch_tire" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Fetch Tire Details
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($existingInspection): ?>
        <div class="existing-record">
            <h3 class="section-title">Existing Inspection Record</h3>
            <p>The following inspection record exists for serial number <?php echo htmlspecialchars($existingInspection['serialNumber']); ?>:</p>
            <table class="table">
                <tbody>
                    <tr><th>Date</th><td><?php echo htmlspecialchars($existingInspection['inspectionDate']); ?></td></tr>
                    <tr><th>Inner Dia. 1</th><td><?php echo htmlspecialchars($existingInspection['innerDiameter1']); ?> mm</td></tr>
                    <tr><th>Inner Dia. 2</th><td><?php echo htmlspecialchars($existingInspection['innerDiameter2']); ?> mm</td></tr>
                    <tr><th>Inner Dia. 3</th><td><?php echo htmlspecialchars($existingInspection['innerDiameter3']); ?> mm</td></tr>
                    <tr><th>Inner Dia. 4</th><td><?php echo htmlspecialchars($existingInspection['innerDiameter4']); ?> mm</td></tr>
                    <tr><th>Width</th><td><?php echo htmlspecialchars($existingInspection['width']); ?> mm</td></tr>
                    <tr><th>Hardness 1</th><td><?php echo htmlspecialchars($existingInspection['hardness1']); ?></td></tr>
                    <tr><th>Hardness 2</th><td><?php echo htmlspecialchars($existingInspection['hardness2']); ?></td></tr>
                    <?php if (isset($existingInspection['gaugeOK'])): ?>
                    <tr><th>Gauge - OK</th><td><?php echo $existingInspection['gaugeOK'] ? 'Yes' : 'No'; ?></td></tr>
                    <tr><th>Gauge - Center</th><td><?php echo $existingInspection['gaugeCenter'] ? 'Yes' : 'No'; ?></td></tr>
                    <tr><th>Gauge - Edge</th><td><?php echo $existingInspection['gaugeEdge'] ? 'Yes' : 'No'; ?></td></tr>
                    <?php endif; ?>
                    <tr><th>Checked By</th><td><?php echo htmlspecialchars($existingInspection['checkedBy']); ?></td></tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($selectedTire): ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clipboard-list"></i> Inspection Data Form
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="serialNumber" value="<?php echo htmlspecialchars($selectedTire['serialNumber']); ?>">
                    <input type="hidden" name="tireCode" value="<?php echo htmlspecialchars($selectedTire['tireCode']); ?>">
                    <input type="hidden" name="brand" value="<?php echo htmlspecialchars($selectedTire['brand']); ?>">
                    <input type="hidden" name="description" value="<?php echo htmlspecialchars($selectedTire['description']); ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="serialNumber" class="form-label">Serial Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control" id="serialNumber" value="<?php echo htmlspecialchars($selectedTire['serialNumber']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="tireCode" class="form-label">Tire Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" id="tireCode" value="<?php echo htmlspecialchars($selectedTire['tireCode']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="brand" class="form-label">Brand</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-industry"></i></span>
                                <input type="text" class="form-control" id="brand" value="<?php echo htmlspecialchars($selectedTire['brand']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="description" class="form-label">Description</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                <input type="text" class="form-control" id="description" value="<?php echo htmlspecialchars($selectedTire['description']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-group">
                        <div class="measurement-title">Inspection Details</div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="inspectionDate" class="form-label required-field">Inspection Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" id="inspectionDate" name="inspectionDate" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-group">
                        <div class="measurement-title">Inner Diameter Measurements (mm)</div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="innerDiameter1" class="form-label required-field">Inner Dia. 1</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="innerDiameter1" name="innerDiameter1" required placeholder="e.g. 500.5">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="innerDiameter2" class="form-label required-field">Inner Dia. 2</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="innerDiameter2" name="innerDiameter2" required placeholder="e.g. 500.7">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="innerDiameter3" class="form-label required-field">Inner Dia. 3</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="innerDiameter3" name="innerDiameter3" required placeholder="e.g. 500.6">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="innerDiameter4" class="form-label required-field">Inner Dia. 4</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="innerDiameter4" name="innerDiameter4" required placeholder="e.g. 500.8">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-group">
                        <div class="measurement-title">Other Measurements</div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="width" class="form-label required-field">Width (mm)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-arrows-alt-h"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="width" name="width" required placeholder="e.g. 200.0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="hardness1" class="form-label required-field">Hardness 1</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>        <input type="number" step="0.1" class="form-control" id="hardness1" name="hardness1" required placeholder="e.g. 85.0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="hardness2" class="form-label required-field">Hardness 2</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                    <input type="number" step="0.1" class="form-control" id="hardness2" name="hardness2" required placeholder="e.g. 86.0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-group">
                        <div class="measurement-title">Gauge Measurements</div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Gauge Status</label>
                                <div class="checkbox-group">
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="gaugeOK" name="gaugeOK" value="1">
                                        <label for="gaugeOK">Gauge OK</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Center Measurement</label>
                                <div class="checkbox-group">
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="gaugeCenter" name="gaugeCenter" value="1">
                                        <label for="gaugeCenter">Gauge Center</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Edge Measurement</label>
                                <div class="checkbox-group">
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="gaugeEdge" name="gaugeEdge" value="1">
                                        <label for="gaugeEdge">Gauge Edge</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="measurement-group">
                        <div class="measurement-title">Quality Control</div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="checkedBy" class="form-label required-field">Checked By</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="checkedBy" name="checkedBy" required placeholder="Enter inspector name">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload();">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button type="submit" name="save_inspection" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Inspection Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($existingInspection): ?>
        <div class="highlight-message">
            <i class="fas fa-exclamation-triangle me-2"></i>
            WARNING: This tire already has an inspection record. Please verify if you need to create a new record.
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> ATIRE Quality System - Final Inspection Book (Document No: C39)</p>
            <p class="mb-0"><small>Quality Assurance Department | All measurements in millimeters unless specified</small></p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 for better dropdown experience
            $('.select2').select2({
                placeholder: 'Select a Serial Number',
                allowClear: true,
                width: '100%'
            });
            
            // Auto-dismiss alerts after 5 seconds
            $('.alert').delay(5000).fadeOut('slow');
            
            // Form validation
            $('form').on('submit', function(e) {
                var isValid = true;
                var requiredFields = $(this).find('[required]');
                
                requiredFields.each(function() {
                    if ($(this).val() === '' || $(this).val() === null) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
            
            // Remove validation class on input
            $('input, select').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Numeric validation for measurement fields
            $('input[type="number"]').on('input', function() {
                var value = $(this).val();
                if (value && isNaN(value)) {
                    $(this).addClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                    $(this).after('<div class="invalid-feedback">Please enter a valid number</div>');
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
            
            // Confirm before saving if existing record exists
            <?php if ($existingInspection): ?>
            $('button[name="save_inspection"]').on('click', function(e) {
                if (!confirm('An inspection record already exists for this tire. Are you sure you want to create a new record?')) {
                    e.preventDefault();
                }
            });
            <?php endif; ?>
            
            // Auto-focus on first empty required field
            var firstEmptyField = $('input[required]:not([readonly]):first');
            if (firstEmptyField.length && firstEmptyField.val() === '') {
                firstEmptyField.focus();
            }
            
            // Add hover effects to measurement groups
            $('.measurement-group').hover(
                function() {
                    $(this).css('transform', 'translateY(-2px)');
                    $(this).css('box-shadow', '0 4px 15px rgba(0, 0, 0, 0.1)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                    $(this).css('box-shadow', 'none');
                }
            );
            
            // Smooth scroll to form when tire is selected
            <?php if ($selectedTire): ?>
            $('html, body').animate({
                scrollTop: $('.card:last').offset().top - 100
            }, 1000);
            <?php endif; ?>
        });
        
        // Print functionality (optional)
        function printInspectionRecord() {
            window.print();
        }
        
        // Export functionality (placeholder for future enhancement)
        function exportToExcel() {
            alert('Export functionality will be implemented in future version.');
        }
        
        // Validation helper functions
        function validateNumericInput(input) {
            var value = parseFloat(input.value);
            if (isNaN(value) || value < 0) {
                input.setCustomValidity('Please enter a valid positive number');
                return false;
            } else {
                input.setCustomValidity('');
                return true;
            }
        }
        
        // Auto-save draft functionality (optional enhancement)
        function saveDraft() {
            var formData = {};
            $('form input, form select').each(function() {
                if ($(this).attr('name')) {
                    formData[$(this).attr('name')] = $(this).val();
                }
            });
            
            // Store in localStorage (if needed for offline capability)
            // localStorage.setItem('inspection_draft', JSON.stringify(formData));
            console.log('Draft saved:', formData);
        }
        
        // Real-time calculation of average inner diameter
        function calculateAverages() {
            var dia1 = parseFloat($('#innerDiameter1').val()) || 0;
            var dia2 = parseFloat($('#innerDiameter2').val()) || 0;
            var dia3 = parseFloat($('#innerDiameter3').val()) || 0;
            var dia4 = parseFloat($('#innerDiameter4').val()) || 0;
            
            if (dia1 && dia2 && dia3 && dia4) {
                var average = (dia1 + dia2 + dia3 + dia4) / 4;
                console.log('Average Inner Diameter:', average.toFixed(2) + ' mm');
            }
            
            var hard1 = parseFloat($('#hardness1').val()) || 0;
            var hard2 = parseFloat($('#hardness2').val()) || 0;
            
            if (hard1 && hard2) {
                var avgHardness = (hard1 + hard2) / 2;
                console.log('Average Hardness:', avgHardness.toFixed(2));
            }
        }
        
        // Bind calculation to input events
        $(document).on('input', '#innerDiameter1, #innerDiameter2, #innerDiameter3, #innerDiameter4, #hardness1, #hardness2', calculateAverages);
    </script>
</body>
</html>