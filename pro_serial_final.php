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
    $stmt = $pdo->prepare("SELECT * FROM tire_data WHERE serialNumber = ?");
    $stmt->execute([$serialNumber]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Save inspection data
function saveInspectionData($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tire_inspections 
            (serialNumber, inspectionDate, innerDiameter1, innerDiameter2, innerDiameter3, innerDiameter4, 
            width, hardness1, hardness2, checkedBy, tireCode, brand) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $data['checkedBy'],
            $data['tireCode'],
            $data['brand']
        ]);
        
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

// Process form submission
$message = '';
$selectedTire = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fetch_tire'])) {
        // User selected a tire from dropdown
        if (!empty($_POST['serialNumber'])) {
            $selectedTire = getTireDetails($pdo, $_POST['serialNumber']);
        }
    } elseif (isset($_POST['save_inspection'])) {
        // User submitted inspection data
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
            'checkedBy' => $_POST['checkedBy'],
            'tireCode' => $_POST['tireCode'],
            'brand' => $_POST['brand']
        ];
        
        $result = saveInspectionData($pdo, $inspectionData);
        
        if ($result === true) {
            $message = "Inspection data saved successfully!";
        } else {
            $message = "Error: " . $result;
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
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
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
        select, input, button {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -10px;
            margin-left: -10px;
        }
        .form-col {
            flex: 0 0 25%;
            max-width: 25%;
            padding: 0 10px;
            box-sizing: border-box;
        }
        .form-col-half {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .form-col, .form-col-half {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        .section-title {
            width: 100%;
            margin: 10px 0;
            padding-left: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ATIRE (PVT) LTD.</h1>
        <div class="header">
            <div>QUALITY ASSURANCE DEPARTMENT</div>
            <div>FINAL INSPECTION BOOK - PQR</div>
            <div>Document No: C39</div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Select tire form -->
        <form method="post" action="">
            <div class="form-group">
                <label for="serialNumber">Select Serial Number:</label>
                <select name="serialNumber" id="serialNumber" required>
                    <option value="">-- Select Serial Number --</option>
                    <?php foreach ($serialNumbers as $tire): ?>
                        <option value="<?php echo htmlspecialchars($tire['serialNumber']); ?>">
                            <?php echo htmlspecialchars($tire['serialNumber']); ?> 
                            (<?php echo htmlspecialchars($tire['brand']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="fetch_tire">Fetch Tire Details</button>
        </form>
        
        <!-- Inspection data form -->
        <?php if ($selectedTire): ?>
            <h2>Enter Inspection Data</h2>
            <form method="post" action="">
                <input type="hidden" name="serialNumber" value="<?php echo htmlspecialchars($selectedTire['serialNumber']); ?>">
                <input type="hidden" name="tireCode" value="<?php echo htmlspecialchars($selectedTire['tireCode']); ?>">
                <input type="hidden" name="brand" value="<?php echo htmlspecialchars($selectedTire['brand']); ?>">
                
                <div class="form-group">
                    <label>Serial Number: <?php echo htmlspecialchars($selectedTire['serialNumber']); ?></label>
                </div>
                
                <div class="form-group">
                    <label>Tire Code: <?php echo htmlspecialchars($selectedTire['tireCode']); ?></label>
                </div>
                
                <div class="form-group">
                    <label>Brand: <?php echo htmlspecialchars($selectedTire['brand']); ?></label>
                </div>
                
                <div class="form-group">
                    <label for="inspectionDate">Inspection Date:</label>
                    <input type="date" name="inspectionDate" id="inspectionDate" required>
                </div>
                
                <div class="form-row">
                    <div class="section-title">Inner Diameter Measurements (mm):</div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="innerDiameter1">Inner Dia. 1:</label>
                            <input type="number" step="0.1" name="innerDiameter1" id="innerDiameter1" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="innerDiameter2">Inner Dia. 2:</label>
                            <input type="number" step="0.1" name="innerDiameter2" id="innerDiameter2" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="innerDiameter3">Inner Dia. 3:</label>
                            <input type="number" step="0.1" name="innerDiameter3" id="innerDiameter3" required>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="innerDiameter4">Inner Dia. 4:</label>
                            <input type="number" step="0.1" name="innerDiameter4" id="innerDiameter4" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="width">Width (mm):</label>
                    <input type="number" step="0.1" name="width" id="width" required>
                </div>
                
                <div class="form-row">
                    <div class="section-title">Hardness Measurements:</div>
                    
                    <div class="form-col-half">
                        <div class="form-group">
                            <label for="hardness1">Hardness 1:</label>
                            <input type="number" step="0.1" name="hardness1" id="hardness1" required>
                        </div>
                    </div>
                    
                    <div class="form-col-half">
                        <div class="form-group">
                            <label for="hardness2">Hardness 2:</label>
                            <input type="number" step="0.1" name="hardness2" id="hardness2" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="checkedBy">Checked By:</label>
                    <input type="text" name="checkedBy" id="checkedBy" required>
                </div>
                
                <button type="submit" name="save_inspection">Save Inspection Data</button>
            </form>
        <?php endif; ?>
        
        <!-- Inspection Records Table -->
        <h2>Recent Inspections</h2>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Date</th>
                    <th>Serial No.</th>
                    <th>Tire Code</th>
                    <th>Brand</th>
                    <th>Inner Dia. 1</th>
                    <th>Inner Dia. 2</th>
                    <th>Inner Dia. 3</th>
                    <th>Inner Dia. 4</th>
                    <th>Width</th>
                    <th>Hardness 1</th>
                    <th>Hardness 2</th>
                    <th>Checked By</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch recent inspection records
                $recentInspections = $pdo->query("SELECT * FROM tire_inspections ORDER BY inspectionDate DESC LIMIT 10");
                $counter = 1;
                
                while ($row = $recentInspections->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['inspectionDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['serialNumber']); ?></td>
                    <td><?php echo htmlspecialchars($row['tireCode']); ?></td>
                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                    <td><?php echo htmlspecialchars($row['innerDiameter1']); ?></td>
                    <td><?php echo htmlspecialchars($row['innerDiameter2']); ?></td>
                    <td><?php echo htmlspecialchars($row['innerDiameter3']); ?></td>
                    <td><?php echo htmlspecialchars($row['innerDiameter4']); ?></td>
                    <td><?php echo htmlspecialchars($row['width']); ?></td>
                    <td><?php echo htmlspecialchars($row['hardness1']); ?></td>
                    <td><?php echo htmlspecialchars($row['hardness2']); ?></td>
                    <td><?php echo htmlspecialchars($row['checkedBy']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>