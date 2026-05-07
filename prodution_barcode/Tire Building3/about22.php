<?php
session_start();

// Database connection
$host = "localhost";
$dbname = "planatir_task_managemen";
$username = "planatir_task_managemen";
$password = "Bishan@1919";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add weight_difference and category_weight columns to tables if they don't exist
$tables = ['qr_scanned_data', 'qr_scanned_data2', 'qr_scanned_data3'];
foreach ($tables as $table) {
    $sql = "ALTER TABLE $table ADD COLUMN IF NOT EXISTS weight_difference DECIMAL(10,2)";
    $conn->query($sql);
    $sql = "ALTER TABLE $table ADD COLUMN IF NOT EXISTS category_weight DECIMAL(10,2)";
    $conn->query($sql);
}

// Function to get total category weight across all tables
function getTotalCategoryWeight($conn, $jobNumber, $batchNumber) {
    $tables = ['qr_scanned_data', 'qr_scanned_data2', 'qr_scanned_data3'];
    $totalWeight = 0;
    
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT SUM(category_weight) as total_weight FROM $table WHERE job_number = ? AND batch_number = ?");
        $stmt->bind_param("ss", $jobNumber, $batchNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $totalWeight += $data['total_weight'] ?? 0;
        $stmt->close();
    }
    
    return $totalWeight;
}

// Function to get matching quantity and category weight details
function getMatchingDetails($conn, $jobNumber, $batchNumber, $tableName) {
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as quantity,
        SUM(category_weight) as table_total_weight 
    FROM $tableName 
    WHERE job_number = ? AND batch_number = ?");
    
    $stmt->bind_param("ss", $jobNumber, $batchNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    return [
        'quantity' => $data['quantity'],
        'table_total_weight' => $data['table_total_weight'] ?? 0
    ];
}

// Function to get actual weight from compound_data table
function getActualWeight($conn, $compoundName) {
    $stmt = $conn->prepare("SELECT actweigt FROM compound_data WHERE compound_name = ?");
    $stmt->bind_param("s", $compoundName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        return $data['actweigt'];
    }
    return null;
}

// Function to get compound weights from bom_new table
function getCompoundWeights($conn, $tireCode) {
    $stmt = $conn->prepare("SELECT a, b, c, d, e, f, g, h, i, j, k, l, m, o, p, q, r FROM bom_new WHERE icode = ?");
    $stmt->bind_param("s", $tireCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to get specific category weight
function getCategoryWeight($weights, $category) {
    $category = strtolower($category);
    if (isset($weights[$category])) {
        return $weights[$category];
    }
    return null;
}

// Function to process QR data and return extracted information
function processQRData($qrData) {
    $pattern = "/\|CN-([a-zA-Z0-9]+)\|BN-([a-zA0-9]+)\|JN-([a-zA-Z0-9]+)/";
    if (preg_match($pattern, $qrData, $matches)) {
        return [
            'compound_name' => $matches[1],
            'batch_number' => $matches[2],
            'job_number' => $matches[3]
        ];
    }
    return false;
}

// Get the serial number from the query parameter
if (isset($_GET['serialNumber'])) {
    $serialNumber = htmlspecialchars($_GET['serialNumber']);
    
    // Fetch tire code from tire_data table
    $stmt = $conn->prepare("SELECT tireCode FROM tire_data WHERE serialNumber = ?");
    $stmt->bind_param("s", $serialNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $tireCode = ($result->num_rows > 0) ? $result->fetch_assoc()['tireCode'] : 'N/A';
    $stmt->close();
} else {
    die("Error: Serial number not provided.");
}

// Handle data preview for all scanners
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['qrData']) && isset($_POST['scanner'])) {
    $qrData = htmlspecialchars($_POST['qrData']);
    $scanner = $_POST['scanner'];
    $processedData = processQRData($qrData);
    
    if ($processedData) {
        $_SESSION[$scanner . '_data'] = [
            'qr_data' => $qrData,
            'processed_data' => $processedData,
            'tire_code' => $tireCode
        ];

        // Get compound weights
        $compoundWeights = getCompoundWeights($conn, $tireCode);
        if ($compoundWeights) {
            $_SESSION[$scanner . '_weights'] = $compoundWeights;
        }

        // Query to fetch the 'cat' value from the Compound_name table
        $compoundName = $processedData['compound_name'];
        $stmt = $conn->prepare("SELECT cat FROM Compound_name WHERE compound_name = ?");
        $stmt->bind_param("s", $compoundName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $catValue = $result->fetch_assoc()['cat'];
            $_SESSION[$scanner . '_cat'] = $catValue;
        } else {
            $_SESSION[$scanner . '_cat'] = "Compound name not found.";
        }
        
        $stmt->close();
    } else {
        $_SESSION[$scanner . '_error'] = "Invalid QR code format. Please try again.";
    }
}

// Handle final submission for all scanners
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_insert'])) {
    $scanner = $_POST['confirm_scanner'];
    $CN = $_POST['compound_name'];
    $BN = $_POST['batch_number'];
    $JN = $_POST['job_number'];
    $TC = $_POST['tire_code'];
    
    // Get category weight
    $catValue = $_SESSION[$scanner . '_cat'];
    $compoundWeights = $_SESSION[$scanner . '_weights'];
    $categoryWeight = getCategoryWeight($compoundWeights, $catValue);
    
    // Get actual weight
    $actualWeight = getActualWeight($conn, $CN);
    
    $table_name = 'qr_scanned_data';
    if ($scanner == 'scanner2') $table_name = 'qr_scanned_data2';
    if ($scanner == 'scanner3') $table_name = 'qr_scanned_data3';
    
    // Get this scanner's current total weight
    $tableDetails = getMatchingDetails($conn, $JN, $BN, $table_name);
    $thisScannersTotal = $tableDetails['table_total_weight'];
    
    // CORRECTLY FIXED: Calculate weight difference as Actual Weight - This Scanner's Total Weight
    // Since we're adding a new record, include the new category weight in the total
    $newScannersTotal = $thisScannersTotal + $categoryWeight;
    $weightDifference = $actualWeight - $newScannersTotal;
    
    // Only proceed if weight difference is not negative
    if ($weightDifference >= 0) {
        // Modified INSERT statement to include weight_difference and category_weight
        $stmt = $conn->prepare("INSERT INTO $table_name (compound_name, batch_number, job_number, serial_number, tire_code, weight_difference, category_weight) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdd", $CN, $BN, $JN, $serialNumber, $TC, $weightDifference, $categoryWeight);
        
        if ($stmt->execute()) {
            $_SESSION[$scanner . '_success'] = "Data successfully inserted into the database.";
            unset($_SESSION[$scanner . '_data']); // Clear the preview data
        } else {
            $_SESSION[$scanner . '_error'] = "Database insert failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION[$scanner . '_error'] = "Cannot proceed: The total weight exceeds the actual weight. Please check the weights.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Tire Details Entry">
    <meta name="author" content="Enterprise Development">
    <link rel="shortcut icon" href="assets/custom/images/shortcut.png">
    <title>Tire Details Entry</title>
    
    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="assets/vendor/fontawesome/css/fontawesome-all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .message { padding: 10px; margin-bottom: 10px; }
        .error-message { background-color: #f8d7da; color: #721c24; }
        .success-message { background-color: #d4edda; color: #155724; }
        .preview-box { padding: 20px; background-color: #f4f4f4; border-radius: 5px; margin-top: 20px; }
        .preview-title { font-weight: bold; }
        .data-label { font-weight: bold; }
        .data-value { color: #333; }
        .scanner-section { margin-bottom: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .scanner-title { font-size: 1.5rem; font-weight: bold; margin-bottom: 15px; }
        .scan-form { margin-top: 10px; }
        .scan-input { width: 300px; padding: 10px; margin-right: 10px; }
        .preview-button, .confirm-button { 
            margin-top: 10px; 
            padding: 10px 20px; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            cursor: pointer;
            border-radius: 5px;
        }
        .preview-button:hover, .confirm-button:hover { background-color: #0056b3; }
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .compound-weights {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .weight-value {
            font-size: 1.2em;
            color: #007bff;
            font-weight: bold;
        }
        .quantity-info {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .quantity-info h4 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .actual-weight {
            margin-top: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        .weight-difference {
            margin-top: 10px;
            padding: 10px;
            background-color: #f0f8ff;
            border-radius: 5px;
            border-left: 4px solid #6c757d;
        }
        .positive-diff { color: #28a745; }
        .negative-diff { color: #dc3545; }
    </style>
</head>
<body>
    <div class="main-container">
        <h1 class="page-title">Tire Details Entry</h1>

        <?php
        // Function to generate scanner section
        function generateScannerSection($scannerNum) {
            global $conn;
            $scannerKey = "scanner" . $scannerNum;
            $tableName = "qr_scanned_data" . ($scannerNum > 1 ? $scannerNum : "");
            ?>
            <div class="scanner-section">
                <h2 class="scanner-title"><?php echo $scannerNum == 1 ? "Base" : "Scanner " . $scannerNum; ?> QR Code Scanner</h2>
                
                <?php if (isset($_SESSION[$scannerKey . '_error'])): ?>
                    <div class="message error-message"><?php echo $_SESSION[$scannerKey . '_error']; unset($_SESSION[$scannerKey . '_error']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION[$scannerKey . '_success'])): ?>
                    <div class="message success-message"><?php echo $_SESSION[$scannerKey . '_success']; unset($_SESSION[$scannerKey . '_success']); ?></div>
                <?php endif; ?>

                <form class="scan-form" method="POST">
                    <input type="text" class="scan-input" id="qrInput<?php echo $scannerNum; ?>" name="qrData" 
                           placeholder="Scan <?php echo $scannerNum == 1 ? "Base" : "Scanner " . $scannerNum; ?> QR Code here" required>
                    <input type="hidden" name="scanner" value="<?php echo $scannerKey; ?>">
                    <button type="submit" class="preview-button">Preview Data</button>
                </form>

                <?php if (isset($_SESSION[$scannerKey . '_data'])): ?>
                    <div class="preview-box">
                        <h3 class="preview-title">QR Code Data Preview</h3>
                        <div class="preview-data">
                        <p><span class="data-label">Compound Name:</span> 
                                <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']; ?></span>
                            </p>
                            <p><span class="data-label">Batch Number:</span> 
                                <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['batch_number']; ?></span>
                            </p>
                            <p><span class="data-label">Job Number:</span> 
                                <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['job_number']; ?></span>
                            </p>
                            <p><span class="data-label">Tire Code:</span> 
                                <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['tire_code']; ?></span>
                            </p>
                            
                            <?php if (isset($_SESSION[$scannerKey . '_cat'])): ?>
                                <p><span class="data-label">Category:</span> 
                                    <span class="data-value"><?php echo $_SESSION[$scannerKey . '_cat']; ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION[$scannerKey . '_weights']) && isset($_SESSION[$scannerKey . '_cat'])): ?>
                                <div class="compound-weights">
                                    <h4>Compound Weight for Category <?php echo strtoupper($_SESSION[$scannerKey . '_cat']); ?>:</h4>
                                    <?php 
                                    $categoryWeight = getCategoryWeight($_SESSION[$scannerKey . '_weights'], $_SESSION[$scannerKey . '_cat']);
                                    if ($categoryWeight !== null): 
                                    ?>
                                        <p><span class="data-label">Weight Value: </span>
                                            <span class="weight-value"><?php echo $categoryWeight; ?></span>
                                        </p>
                                    <?php else: ?>
                                        <p>No weight found for this category.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php 
                            $actualWeight = getActualWeight($conn, $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']);
                            if ($actualWeight !== null): 
                            ?>
                                <div class="actual-weight">
                                    <h4>Actual Weight from Compound Data:</h4>
                                    <p><span class="data-label">Actual Weight: </span>
                                        <span class="weight-value"><?php echo number_format($actualWeight, 2); ?> kg</span>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php
                            $jobNumber = $_SESSION[$scannerKey . '_data']['processed_data']['job_number'];
                            $batchNumber = $_SESSION[$scannerKey . '_data']['processed_data']['batch_number'];
                            $details = getMatchingDetails($conn, $jobNumber, $batchNumber, $tableName);
                            ?>
                            
                            <div class="quantity-info">
                                <h4>Quantity and Weight Information</h4>
                                <p><span class="data-label">Total scans for this Job/Batch: </span>
                                    <span class="weight-value"><?php echo $details['quantity']; ?></span>
                                </p>
                                <p><span class="data-label">This Scanner's Total Weight: </span>
                                    <span class="weight-value"><?php echo number_format($details['table_total_weight'], 2); ?> kg</span>
                                </p>
                                
                            </div>
                            
                            <?php if (isset($actualWeight) && isset($categoryWeight)): ?>
                                <div class="weight-difference">
                                    <h4>Weight Difference</h4>
                                    <?php
                                    // CORRECTLY FIXED: Calculate weight difference as Actual Weight - This Scanner's Total Weight
                                    // Include the new category weight in the calculation
                                    $newTotal = $details['table_total_weight'] + $categoryWeight;
                                    $weightDifference = $actualWeight - $newTotal;
                                    $colorClass = $weightDifference >= 0 ? 'positive-diff' : 'negative-diff';
                                    ?>
                                    <p><span class="data-label">Weight Difference (Actual Weight - This Scanner's Total Weight): </span>
                                        <span class="weight-value <?php echo $colorClass; ?>">
                                            <?php echo number_format($weightDifference, 2); ?> kg
                                        </span>
                                    </p>
                                    <p><span class="data-label">New Total Weight (including this scan): </span>
                                        <span class="weight-value">
                                            <?php echo number_format($newTotal, 2); ?> kg
                                        </span>
                                    </p>
                                    <?php if ($weightDifference < 0): ?>
                                        <p class="negative-diff">Warning: Total weight would exceed actual weight!</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($weightDifference) && $weightDifference >= 0): ?>
                            <form method="POST">
                                <input type="hidden" name="confirm_scanner" value="<?php echo $scannerKey; ?>">
                                <input type="hidden" name="compound_name" 
                                    value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']; ?>">
                                <input type="hidden" name="batch_number" 
                                    value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['batch_number']; ?>">
                                <input type="hidden" name="job_number" 
                                    value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['job_number']; ?>">
                                <input type="hidden" name="tire_code" 
                                    value="<?php echo $_SESSION[$scannerKey . '_data']['tire_code']; ?>">
                                <button type="submit" name="confirm_insert" class="confirm-button">Confirm & Insert Data</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }

        // Generate scanner sections
        for ($i = 1; $i <= 3; $i++) {
            generateScannerSection($i);
        }
        ?>
    </div>
    
    <!-- Scripts -->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus handling for scanners
        document.addEventListener('DOMContentLoaded', function() {
            // Function to focus on the next available input
            function focusNextAvailableInput() {
                const inputs = ['qrInput1', 'qrInput2', 'qrInput3'];
                for (let inputId of inputs) {
                    const input = document.getElementById(inputId);
                    const scannerSection = input.closest('.scanner-section');
                    const previewBox = scannerSection.querySelector('.preview-box');
                    
                    // If this scanner hasn't scanned yet (no preview box), focus on it
                    if (!previewBox) {
                        input.focus();
                        break;
                    }
                }
            }

            // Initial focus
            focusNextAvailableInput();

            // After successful scan and preview, focus on next scanner
            const successMessages = document.querySelectorAll('.success-message');
            if (successMessages.length > 0) {
                focusNextAvailableInput();
            }
        });
    </script>
</body>
</html>