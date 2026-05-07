<?php
// Initialize variables to store form data if submitted
$formSubmitted = false;
$cycles = [];
$successMessage = '';
$errorMessage = '';

// Database connection configuration
$servername = "localhost";
$username = "planatir_task_managemen"; // Replace with your database username
$password = "Bishan@1919"; // Replace with your database password
$dbname = "planatir_task_managemen"; // Replace with your database name

// Create connection
$conn = null;
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    $dbError = "Database connection error: " . $e->getMessage();
}

// Endpoint for AJAX search
if (isset($_GET['search_serial'])) {
    $search = $_GET['search_serial'];
    $result = [];
    
    if ($conn) {
        $stmt = $conn->prepare("SELECT serialNumber, brand, tireCode FROM tire_data WHERE serialNumber LIKE ? LIMIT 10");
        $searchParam = "%$search%";
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $queryResult = $stmt->get_result();
        
        while ($row = $queryResult->fetch_assoc()) {
            $result[] = [
                'id' => $row['serialNumber'],
                'text' => $row['serialNumber'],
                'brand' => $row['brand'],
                'tireCode' => $row['tireCode']
            ];
        }
        $stmt->close();
    }
    
    header('Content-Type: application/json');
    echo json_encode(['results' => $result]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formSubmitted = true;
    
    // Process form data
    $date = $_POST['date'] ?? '';
    $shift = $_POST['shift'] ?? '';
    $pressOperator = $_POST['press_operator'] ?? '';
    $pressNo = $_POST['press_no'] ?? '';
    
    // Process cycle data
    $cycleCount = $_POST['cycle_count'] ?? 0;
    
    for ($i = 0; $i < $cycleCount; $i++) {
        if (isset($_POST["cycle_no_$i"])) {
            $cycle = [
                'cycle_no' => $_POST["cycle_no_$i"] ?? '',
                'serials' => []
            ];
            
            $serialCount = $_POST["serial_count_$i"] ?? 0;
            
            for ($j = 0; $j < $serialCount; $j++) {
                $serial = [
                    'serial_no' => $_POST["serial_no_{$i}_{$j}"] ?? '',
                    'brand_version' => $_POST["brand_version_{$i}_{$j}"] ?? '',
                    'size' => $_POST["size_{$i}_{$j}"] ?? '',
                    'green_temp' => [
                        'base' => $_POST["base_{$i}_{$j}"] ?? '',
                        'center' => $_POST["center_{$i}_{$j}"] ?? '',
                        'tread' => $_POST["tread_{$i}_{$j}"] ?? '',
                    ],
                    'pattern_temp' => [
                        'left' => $_POST["pattern_left_{$i}_{$j}"] ?? '',
                        'center' => $_POST["pattern_center_{$i}_{$j}"] ?? '',
                        'right' => $_POST["pattern_right_{$i}_{$j}"] ?? '',
                    ],
                    'mould_temp' => $_POST["mould_temp_{$i}_{$j}"] ?? '',
                    'in_time' => $_POST["in_time_{$i}_{$j}"] ?? '',
                    'hydraulic_pressure' => [
                        'at_1_3_cy' => $_POST["at_1_3_cy_{$i}_{$j}"] ?? '',
                        'at_2_3_cy' => $_POST["at_2_3_cy_{$i}_{$j}"] ?? '',
                        'just_before_unload' => $_POST["just_before_unload_{$i}_{$j}"] ?? '',
                    ],
                    'actual_times' => [
                        'start' => $_POST["start_time_{$i}_{$j}"] ?? '',
                        'finish' => $_POST["finish_time_{$i}_{$j}"] ?? '',
                        'actual_cure_time' => $_POST["actual_cure_time_{$i}_{$j}"] ?? '',
                    ],
                    'quality_check' => $_POST["quality_check_{$i}_{$j}"] ?? '',
                ];
                
                $cycle['serials'][] = $serial;
            }
            
            $cycles[] = $cycle;
        }
    }
    
    // Save data to database if connection is available
    if ($conn) {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Insert inspection header
            $headerStmt = $conn->prepare("INSERT INTO quality_inspection_header 
                (inspection_date, shift, press_operator, press_no, created_at) 
                VALUES (?, ?, ?, ?, NOW())");
            $headerStmt->bind_param("ssss", $date, $shift, $pressOperator, $pressNo);
            $headerStmt->execute();
            
            // Get the inserted header ID
            $headerId = $conn->insert_id;
            $headerStmt->close();
            
            // Insert cycle data
            foreach ($cycles as $cycle) {
                $cycleStmt = $conn->prepare("INSERT INTO quality_inspection_cycle 
                    (header_id, cycle_no, created_at) 
                    VALUES (?, ?, NOW())");
                $cycleStmt->bind_param("ii", $headerId, $cycle['cycle_no']);
                $cycleStmt->execute();
                
                // Get the inserted cycle ID
                $cycleId = $conn->insert_id;
                $cycleStmt->close();
                
                // Insert serial data for each cycle
                foreach ($cycle['serials'] as $serial) {
                    $serialStmt = $conn->prepare("INSERT INTO quality_inspection_serial 
                        (cycle_id, serial_no, brand_version, size, 
                        base_temp, center_temp, tread_temp, 
                        pattern_left_temp, pattern_center_temp, pattern_right_temp,
                        mould_temp, in_time, 
                        pressure_1_3_cy, pressure_2_3_cy, pressure_before_unload, 
                        start_time, finish_time, actual_cure_time, quality_check, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    
                    $serialStmt->bind_param(
                        "issssssssssssssssss", 
                        $cycleId, 
                        $serial['serial_no'], 
                        $serial['brand_version'], 
                        $serial['size'],
                        $serial['green_temp']['base'],
                        $serial['green_temp']['center'],
                        $serial['green_temp']['tread'],
                        $serial['pattern_temp']['left'],
                        $serial['pattern_temp']['center'],
                        $serial['pattern_temp']['right'],
                        $serial['mould_temp'],
                        $serial['in_time'],
                        $serial['hydraulic_pressure']['at_1_3_cy'],
                        $serial['hydraulic_pressure']['at_2_3_cy'],
                        $serial['hydraulic_pressure']['just_before_unload'],
                        $serial['actual_times']['start'],
                        $serial['actual_times']['finish'],
                        $serial['actual_times']['actual_cure_time'],
                        $serial['quality_check']
                    );
                    
                    $serialStmt->execute();
                    $serialStmt->close();
                }
            }
            
            // Commit transaction
            $conn->commit();
            $successMessage = "Data successfully saved to database!";
            
        } catch (Exception $e) {
            // Rollback in case of error
            $conn->rollback();
            $errorMessage = "Error saving data: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Database connection not available. Data could not be saved.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRE Quality Inspection Sheet</title>

    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        
        .logo {
            font-weight: bold;
            font-size: 24px;
        }
        
        .form-header {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .cycle-header {
            background-color: #e6e6e6;
            padding: 10px;
            margin: 20px 0 10px;
            font-weight: bold;
            border-radius: 4px;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .add-btn {
            background-color: #2196F3;
            margin-right: 10px;
        }
        
        .add-btn:hover {
            background-color: #0b7dda;
        }
        
        .remove-btn {
            background-color: #f44336;
        }
        
        .remove-btn:hover {
            background-color: #d32f2f;
        }
        
        .submitted-data {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .document-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .section-title {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            font-size: 18px;
            background-color: #f2f2f2;
            padding: 10px;
            border-radius: 4px;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4CAF50;
        }
        
        .serial-select-container {
            margin-bottom: 5px;
        }
        
        .error-message {
            color: #f44336;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .success-message {
            color: #4CAF50;
            padding: 10px;
            background-color: #E8F5E9;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">ATIRE</div>
            <h1>QUALITY INSPECTION SHEET</h1>
            <div>QUALITY ASSURANCE DEPARTMENT</div>
        </div>
        
        <?php if (isset($dbError)): ?>
            <div class="error-message">
                <?php echo $dbError; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($formSubmitted && empty($errorMessage)): ?>
            <div class="submitted-data">
                <div class="document-info">
                    <div><strong>Date:</strong> <?php echo htmlspecialchars($date); ?></div>
                    <div><strong>Shift:</strong> <?php echo htmlspecialchars($shift); ?></div>
                    <div><strong>Press Operator:</strong> <?php echo htmlspecialchars($pressOperator); ?></div>
                    <div><strong>Press No:</strong> <?php echo htmlspecialchars($pressNo); ?></div>
                </div>
                
                <?php foreach($cycles as $index => $cycle): ?>
                    <div class="cycle-data">
                        <h3>Cycle No: <?php echo htmlspecialchars($cycle['cycle_no']); ?></h3>
                        <table>
                            <thead>
                                <tr>
                                    <th rowspan="2">Serial No</th>
                                    <th rowspan="2">Brand Version</th>
                                    <th rowspan="2">Size</th>
                                    <th colspan="3">Green tire temp. Prior to Curing</th>
                                    <th colspan="3">Pattern temp.</th>
                                    <th rowspan="2">Mould Temp</th>
                                    <th rowspan="2">In Time</th>
                                    <th colspan="3">Actual hydraulic pressure(Bar)</th>
                                    <th colspan="3">Time</th>
                                    <th rowspan="2">Quality Check</th>
                                </tr>
                                <tr>
                                    <th>Base</th>
                                    <th>Center</th>
                                    <th>Tread</th>
                                    <th>Left</th>
                                    <th>Center</th>
                                    <th>Right</th>
                                    <th>at 1/3 Cy</th>
                                    <th>at 2/3 Cy</th>
                                    <th>Just Before Unload</th>
                                    <th>Start</th>
                                    <th>Finish</th>
                                    <th>Actual Cure Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cycle['serials'] as $serial): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($serial['serial_no']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['brand_version']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['size']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['green_temp']['base']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['green_temp']['center']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['green_temp']['tread']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['pattern_temp']['left']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['pattern_temp']['center']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['pattern_temp']['right']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['mould_temp']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['in_time']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['hydraulic_pressure']['at_1_3_cy']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['hydraulic_pressure']['at_2_3_cy']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['hydraulic_pressure']['just_before_unload']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['actual_times']['start']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['actual_times']['finish']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['actual_times']['actual_cure_time']); ?></td>
                                        <td><?php echo htmlspecialchars($serial['quality_check']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 20px;">
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="add-btn" style="text-decoration: none; display: inline-block;">Add New Inspection</a>
                </div>
            </div>
        <?php else: ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="qualityForm">
                <div class="form-header">
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="shift">Shift:</label>
                        <select id="shift" name="shift" required>
                            <option value="">Select Shift</option>
                            <option value="A - (Day)">A - (Day)</option>
                            <option value="B - (Night)">B - (Night)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="press_operator">Press Operator:</label>
                        <input type="text" id="press_operator" name="press_operator" required>
                    </div>
                    <div class="form-group">
                        <label for="press_no">Press No:</label>
                        <input type="text" id="press_no" name="press_no" required>
                    </div>
                </div>
                
                <div class="section-title">CURING SECTION</div>
                
                <div id="cycles-container">
                    <!-- Cycle template will be added here by JavaScript -->
                </div>
                
                <input type="hidden" id="cycle_count" name="cycle_count" value="0">
                
                <div style="margin: 20px 0;">
                    <button type="button" class="add-btn" id="add-cycle">Add Cycle</button>
                    <button type="submit">Submit</button>
                </div>
            </form>
            
            <!-- Template for cycle -->
            <template id="cycle-template">
                <div class="cycle" data-cycle-index="0">
                    <div class="cycle-header">CYCLE NO. <span class="cycle-number">1</span></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div>Hydraulic pressure(Bar): <input type="text" class="hydraulic-pressure" value="140" style="width: 60px;"></div>
                        <button type="button" class="add-btn add-serial">Add Serial</button>
                        <button type="button" class="remove-btn remove-cycle">Remove Cycle</button>
                    </div>
                    <input type="hidden" name="cycle_no_0" value="1">
                    <input type="hidden" class="serial_count" name="serial_count_0" value="0">
                    
                    <table>
                        <thead>
                            <tr>
                                <th rowspan="2">Serial No</th>
                                <th rowspan="2">Brand version</th>
                                <th rowspan="2">Size</th>
                                <th colspan="3">Green tire temp. Prior to Curing</th>
                                <th colspan="3">Pattern temp.</th>
                                <th rowspan="2">Mould temp.</th>
                                <th rowspan="2">In time</th>
                                <th colspan="3">Actual hydraulic pressure(Bar)</th>
                                <th colspan="3">Time</th>
                                <th rowspan="2">Quality Check</th>
                                <th rowspan="2">Action</th>
                            </tr>
                            <tr>
                                <th>Base</th>
                                <th>Center</th>
                                <th>Tread</th>
                                <th>Left</th>
                                <th>Center</th>
                                <th>Right</th>
                                <th>at 1/3 Cy</th>
                                <th>at 2/3 Cy</th>
                                <th>Just Before Unload</th>
                                <th>Start Time</th>
                                <th>Finish Time</th>
                                <th>Actual Cure Time</th>
                            </tr>
                        </thead>
                        <tbody class="serials-container">
                            <!-- Serial rows will be added here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </template>
            
            <!-- Template for serial row -->
            <template id="serial-template">
                <tr class="serial-row" data-serial-index="0">
                    <td class="serial-select-container">
                        <select class="serial-select" name="serial_no_0_0" style="width: 100%" required></select>
                    </td>
                    <td><input type="text" name="brand_version_0_0" class="brand-version" readonly></td>
                    <td><input type="text" name="size_0_0" class="tire-size" required></td>
                    <td><input type="text" name="base_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="center_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="tread_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="pattern_left_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="pattern_center_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="pattern_right_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="mould_temp_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="in_time_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="at_1_3_cy_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="at_2_3_cy_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="just_before_unload_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="start_time_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="finish_time_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="actual_cure_time_0_0" style="width: 50px;"></td>
                    <td><input type="text" name="quality_check_0_0" style="width: 50px;"></td>
                    <td><button type="button" class="remove-btn remove-serial">X</button></td>
                </tr>
            </template>
            
            <!-- Include jQuery -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <!-- Include Select2 JS -->
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const cyclesContainer = document.getElementById('cycles-container');
                    const cycleTemplate = document.getElementById('cycle-template');
                    const serialTemplate = document.getElementById('serial-template');
                    const addCycleBtn = document.getElementById('add-cycle');
                    const cycleCountInput = document.getElementById('cycle_count');
                    
                    let cycleCount = 0;
                    
                    // Add Cycle
                    addCycleBtn.addEventListener('click', function() {
                        addCycle();
                    });
                    
                    // Add first cycle automatically
                    addCycle();
                    
                    function addCycle() {
                        const cycleIndex = cycleCount;
                        cycleCount++;
                        
                        // Clone cycle template
                        const cycleClone = document.importNode(cycleTemplate.content, true);
                        
                        // Update cycle index
                        const cycleElement = cycleClone.querySelector('.cycle');
                        cycleElement.dataset.cycleIndex = cycleIndex;
                        
                        // Update cycle number
                        cycleClone.querySelector('.cycle-number').textContent = cycleIndex + 1;
                        
                        // Update input names
                        const cycleNoInput = cycleClone.querySelector('input[name^="cycle_no_"]');
                        cycleNoInput.name = `cycle_no_${cycleIndex}`;
                        cycleNoInput.value = cycleIndex + 1;
                        
                        const serialCountInput = cycleClone.querySelector('.serial_count');
                        serialCountInput.name = `serial_count_${cycleIndex}`;
                        
                        // Add event listener for adding serial
                        const addSerialBtn = cycleClone.querySelector('.add-serial');
                        addSerialBtn.addEventListener('click', function() {
                            addSerial(cycleElement);
                        });
                        
                        // Add event listener for removing cycle
                        const removeCycleBtn = cycleClone.querySelector('.remove-cycle');
                        removeCycleBtn.addEventListener('click', function() {
                            if (cycleCount > 1) {
                                cycleElement.remove();
                                cycleCount--;
                                updateCycleCount();
                                reindexCycles();
                            } else {
                                alert('At least one cycle is required');
                            }
                        });
                        
                        // Add to DOM
                        cyclesContainer.appendChild(cycleClone);
                        
                        // Add first serial automatically
                        addSerial(cycleElement);
                        
                        // Update cycle count
                        updateCycleCount();
                    }
                    
                    function addSerial(cycleElement) {
                        const cycleIndex = parseInt(cycleElement.dataset.cycleIndex);
                        const serialsContainer = cycleElement.querySelector('.serials-container');
                        const serialCountInput = cycleElement.querySelector('.serial_count');
                        let serialCount = parseInt(serialCountInput.value);
                        
                        // Clone serial template
                        const serialClone = document.importNode(serialTemplate.content, true);
                        
                        // Update serial index
                        const serialRow = serialClone.querySelector('.serial-row');
                        serialRow.dataset.serialIndex = serialCount;
                        
                        // Update input names
                        const inputs = serialClone.querySelectorAll('input');
                        inputs.forEach(input => {
                            const nameParts = input.name.split('_');
                            input.name = `${nameParts[0]}_${cycleIndex}_${serialCount}`;
                        });
                        
                        // Update select names
                        const selects = serialClone.querySelectorAll('select');
                        selects.forEach(select => {
                            const nameParts = select.name.split('_');
                            select.name = `${nameParts[0]}_${cycleIndex}_${serialCount}`;
                            select.id = `${nameParts[0]}_${cycleIndex}_${serialCount}`;
                        });
                        
                        // Add event listener for removing serial
                        const removeSerialBtn = serialClone.querySelector('.remove-serial');
                        removeSerialBtn.addEventListener('click', function() {
                            if (serialsContainer.children.length > 1) {
                                serialRow.remove();
                                reindexSerials(cycleElement);
                            } else {
                                alert('At least one serial is required per cycle');
                            }
                        });
                        
                        // Add to DOM
                        serialsContainer.appendChild(serialClone);
                        
                        // Initialize Select2 for the new serial select
                        initializeSelect2ForSerialRow(serialRow, cycleIndex, serialCount);
                        
                        // Increment serial count
                        serialCount++;