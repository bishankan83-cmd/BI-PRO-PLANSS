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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inspection'])) {
    $serialNumber = $conn->real_escape_string($_POST['serialNumber']);
    $pattern = isset($_POST['pattern']) ? $conn->real_escape_string($_POST['pattern']) : '';
    $h1 = isset($_POST['h1']) ? $conn->real_escape_string($_POST['h1']) : '';
    $h2 = isset($_POST['h2']) ? $conn->real_escape_string($_POST['h2']) : '';
    $h3 = isset($_POST['h3']) ? $conn->real_escape_string($_POST['h3']) : '';
    $h4 = isset($_POST['h4']) ? $conn->real_escape_string($_POST['h4']) : '';
    $us1 = isset($_POST['us1']) ? $conn->real_escape_string($_POST['us1']) : '';
    $us2 = isset($_POST['us2']) ? $conn->real_escape_string($_POST['us2']) : '';
    $us3 = isset($_POST['us3']) ? $conn->real_escape_string($_POST['us3']) : '';
    $us4 = isset($_POST['us4']) ? $conn->real_escape_string($_POST['us4']) : '';
    $employee_id = $conn->real_escape_string($_POST['employee_id']);

    // Fetch brand based on serial number
    $brandQuery = "SELECT brand FROM tire_data WHERE serialNumber = '$serialNumber'";
    $brandResult = $conn->query($brandQuery);
    if ($brandResult === false) {
        die("Query failed (brandQuery): " . $conn->error);
    }
    $brand = $brandResult->fetch_assoc()['brand'] ?? '';

    // Prepare defect data
    $defects = [
        'back_rind', 'bead_wire_exposed', 'damage_contour', 'deformed_tread_bloc',
        'double_molding', 'flow_marks', 'foriegn_material', 'nm_flow_clip',
        'mold_out_of_line', 'side_wall_open_black', 'black_mix', 'peel', 'base_flow_marks',
        'cure_flash', 'band_edge_cushion', 'band_seperate'
    ];

    $values = [];
    foreach ($defects as $defect) {
        $values[$defect] = isset($_POST[$defect]) ? 1 : 0;
    }

    // Insert into tire_inspections table
    $insertQuery = "INSERT INTO tire_defect3 (
        serialNumber, brand, pattern, h1, h2, h3, h4, us1, us2, us3, us4, employee_id, " . implode(',', $defects) . "
    ) VALUES (
        '$serialNumber', '$brand', '$pattern', '$h1', '$h2', '$h3', '$h4', '$us1', '$us2', '$us3', '$us4', '$employee_id', " . implode(',', array_values($values)) . "
    )";

    if ($conn->query($insertQuery)) {
        // Set a success message in the session
        $_SESSION['success_message'] = "Record added successfully!";
        // Redirect to the same page to prevent resubmission
        header("Location: Tire defect.php");
        exit();
    } else {
        die("Insert query failed: " . $conn->error);
    }
}

// Fetch serial numbers for dropdown
$serialNumbersQuery = "SELECT serialNumber, brand FROM tire_data";
$serialNumbersResult = $conn->query($serialNumbersQuery);
if ($serialNumbersResult === false) {
    die("Query failed (serialNumbersQuery): " . $conn->error);
}

// Fetch inspection data for display
$inspectionQuery = "SELECT * FROM tire_defect3 ORDER BY created_at DESC";
$inspectionResult = $conn->query($inspectionQuery);
if ($inspectionResult === false) {
    die("Query failed (inspectionQuery): " . $conn->error);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tire Inspection System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #e9ecef;
            color: #333;
        }
        .container {
            max-width: 1400px;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .header img {
            height: 60px;
        }
        .header h2 {
            margin: 0;
            font-size: 1.8em;
            color: #212529;
        }
        .tabs {
            display: flex;
            gap: 5px;
            margin: 20px 0;
            border-bottom: 2px solid #dee2e6;
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
            color:rgb(11, 11, 11);
            border-bottom: 3px solid #iffer;
        }
        .tab-link.active {
            color:#F28018;
            border-bottom: 3px solid #F28018;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        h1 {
            font-size: 1.8em;
            text-align: center;
            color: #212529;
            margin: 20px 0;
            font-weight: 700;
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
            color: #495057;
        }
        select, input[type="text"], input[list] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        select:focus, input[type="text"]:focus, input[list]:focus {
            border-color:#F28018;
            box-shadow: 0 0 5px #F28018;
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
            accent-color: #F28018;
        }
        input[type="submit"] {
            background-color:rgb(7, 7, 7);
            color: #ffffff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        input[type="submit"]:hover {
            background-color:rgb(8, 8, 8);
            transform: translateY(-2px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #ffffff;
            font-size: 0.75em;
        }
        th, td {
            padding: 6px 8px;
            text-align: center;
            border: 1px solid #dee2e6;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        th {
            background-color:rgb(6, 6, 6);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.7em;
            text-transform: uppercase;
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
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #6c757d;
            text-align: center;
        }
        #view {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://cdn.prod.website-files.com/64f8065a81be9734043da10e/64f818f083a1ce0044ce06a4_ATIRE-logo.png" alt="ATIRE Logo">
            
        </div>

        <!-- Tabs for switching between Add New and View -->
        <div class="tabs">
            <a href="javascript:void(0)" class="tab-link active" onclick="showTab('add-new')">Add New</a>
            <a href="javascript:void(0)" class="tab-link" onclick="showTab('view')">View</a>
        </div>

        <!-- Display success message if set -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Add New Section -->
        <div id="add-new" class="tab-content active">
            <h1>Daily Defected Tyres </h1>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="serialNumber">Select Serial Number:</label>
                        <select name="serialNumber" id="serialNumber" required onchange="updateBrand()">
                            <option value="">Select Serial Number</option>
                            <?php 
                            // Reset result set pointer
                            $serialNumbersResult->data_seek(0); 
                            while ($row = $serialNumbersResult->fetch_assoc()): ?>
                                <option value="<?php echo $row['serialNumber']; ?>" 
                                        data-brand="<?php echo $row['brand']; ?>">
                                    <?php echo $row['serialNumber']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="brand">Brand:</label>
                        <input type="text" id="brand" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pattern">Pattern:</label>
                    <input type="text" name="pattern" id="pattern" required>
                </div>

                <div class="form-group">
                    <label>Defects (Select all that apply):</label>
                    <div class="defects-grid">
                        <?php
                        $defects = [
                            'back_rind' => 'Back Rind',
                            'bead_wire_exposed' => 'Bead Wire Exposed',
                            'damage_contour' => 'Damage Contour',
                            'deformed_tread_bloc' => 'Deformed Tread Bloc',
                            'double_molding' => 'Double Molding',
                            'flow_marks' => 'Flow Marks',
                            'foriegn_material' => 'foriegn_material',
                            'nm_flow_clip' => 'nm_flow_clip',
                            'mold_out_of_line' => 'Mold Out of Line',
                            'side_wall_open_black' => 'side_wall_open_black',
                            'black_mix' => 'black_mix',
                            'peel' => 'Peel',
                            'base_flow_marks' => 'Base Flow Marks',
                            'cure_flash' => 'Cure Flash',
                            'band_edge_cushion' => 'Band_edge_cushion',
                            'band_seperate' => 'Band Separate',
                            
                        ];
                        foreach ($defects as $key => $label): ?>
                            <div class="defect-item">
                                <input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>">
                                <label for="<?php echo $key; ?>"><?php echo $label; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="employee_id">Employee ID:</label>
                    <input list="employee_ids" name="employee_id" id="employee_id" required>
                    <datalist id="employee_ids">
                        <?php 
                        // Reset result set pointer
                        if ($employeeResult) $employeeResult->data_seek(0); 
                        while ($employeeResult && $row = $employeeResult->fetch_assoc()): ?>
                            <option value="<?php echo $row['employee_id']; ?>">
                        <?php endwhile; ?>
                    </datalist>
                </div>

                <input type="submit" name="submit_inspection" value="Submit Inspection">
            </form>
        </div>

        <!-- View Section -->
        <div id="view" class="tab-content">
            <h1>Daily Defected Tyres</h1>
            <table>
    <thead>
        <tr>
            <th rowspan="2">Tyre Size</th>
            <th rowspan="2">Serial No.</th>
            <th rowspan="2">Brand / Version</th>
            <th rowspan="2">Pattern</th>
            <?php foreach ($defects as $label): ?>
                <th rowspan="2" class="defect-header"><?php echo $label; ?></th>
            <?php endforeach; ?>
            <th colspan="4">Hardness</th>
            <th colspan="4">US</th>
            <th rowspan="2">Employee ID</th>
            <th rowspan="2">Date</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // Reset result set pointer
        $inspectionResult->data_seek(0); 
        while ($row = $inspectionResult->fetch_assoc()): ?>
            <tr>
                <td>-</td> <!-- Tyre Size not available in data -->
                <td><?php echo $row['serialNumber']; ?></td>
                <td><?php echo $row['brand']; ?></td>
                <td><?php echo $row['pattern'] ?: '-'; ?></td>
                <?php foreach ($defects as $key => $label): ?>
                    <td><?php echo $row[$key] ? '✓' : ''; ?></td>
                <?php endforeach; ?>
                <td><?php echo $row['h1']; ?></td>
                <td><?php echo $row['h2']; ?></td>
                <td><?php echo $row['h3']; ?></td>
                <td><?php echo $row['h4']; ?></td>
                <td><?php echo $row['us1']; ?></td>
                <td><?php echo $row['us2']; ?></td>
                <td><?php echo $row['us3']; ?></td>
                <td><?php echo $row['us4']; ?></td>
                <td><?php echo $row['employee_id']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
        </div>

        <div class="footer">
            © <?php echo date('Y'); ?> ATIRE. All rights reserved.
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`a[onclick="showTab('${tabId}')"]`).classList.add('active');
        }

        function updateBrand() {
            const serialSelect = document.getElementById('serialNumber');
            const brandInput = document.getElementById('brand');
            const selectedOption = serialSelect.options[serialSelect.selectedIndex];
            brandInput.value = selectedOption.dataset.brand || '';
        }

        window.onload = function() {
            <?php if (isset($_SESSION['success_message'])): ?>
                showTab('view');
                document.querySelector('a[onclick="showTab(\'view\')"]').classList.add('active');
            <?php endif; ?>
        };
    </script>
</body>
</html>

<?php $conn->close(); ?>