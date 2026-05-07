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

function processQRData($qrData) {
    $pattern = "/\|CN-([^|]+)\|BN-([^|]+)\|JN-([^|]+)/i";
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

        // Query to fetch the 'cat' and 'c_cat' values from the Compound_name table
        $compoundName = $processedData['compound_name'];
        $stmt = $conn->prepare("SELECT cat, c_cat FROM Compound_name WHERE compound_name = ?");
        $stmt->bind_param("s", $compoundName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $_SESSION[$scanner . '_cat'] = $data['cat'];
            $_SESSION[$scanner . '_c_cat'] = $data['c_cat'];
        } else {
            $_SESSION[$scanner . '_cat'] = "Compound name not found.";
            $_SESSION[$scanner . '_c_cat'] = "Unknown";
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
    
    // Calculate weight difference
    $newScannersTotal = $thisScannersTotal + $categoryWeight;
    $weightDifference = $actualWeight - $newScannersTotal;
    
    // Insert data
    $stmt = $conn->prepare("INSERT INTO $table_name (compound_name, batch_number, job_number, serial_number, tire_code, weight_difference, category_weight) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdd", $CN, $BN, $JN, $serialNumber, $TC, $weightDifference, $categoryWeight);
    
    if ($stmt->execute()) {
        $_SESSION[$scanner . '_success'] = "Data successfully inserted into the database.";
        unset($_SESSION[$scanner . '_data']);
        
        if ($weightDifference < 0) {
            header("Location: negative_weight.php?serialNumber=" . urlencode($serialNumber));
            exit();
        }
    } else {
        $_SESSION[$scanner . '_error'] = "Database insert failed: " . $stmt->error;
    }
    $stmt->close();
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
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i|Cantarell:400,700" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="assets/vendor/fontawesome/css/fontawesome-all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .main-container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 20px;
            background-color: #F28018;
            border-radius: 8px;
        }

        .scanner-section {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #CCCCCC;
            border-radius: 8px;
            background-color: #ffffff;
            transition: box-shadow 0.3s;
        }

        .scanner-section:hover {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .scanner-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            padding-bottom: 10px;
            border-bottom: 2px solid #F28018;
        }

        .scan-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .scan-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s;
        }

        .scan-input:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 5px rgba(242,128,24,0.3);
        }

        .preview-button, .confirm-button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 12px 25px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            white-space: nowrap;
        }

        .preview-button:hover, .confirm-button:hover {
            background-color: #333333;
        }

        .preview-box {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }

        .preview-title {
            font-weight: bold;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #F28018;
        }

        .data-label {
            font-weight: bold;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            min-width: 150px;
            display: inline-block;
        }

        .data-value {
            color: #333333;
            font-family: 'Open Sans', sans-serif;
        }

        .message {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-family: 'Open Sans', sans-serif;
        }

        .error-message {
            background-color: #ffe6e6;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .success-message {
            background-color: #e6ffe6;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .info-section {
            background-color: #fff;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            border-left: 4px solid #F28018;
        }

        .info-section h4 {
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            margin-bottom: 10px;
        }

        .weight-value {
            font-size: 1.2em;
            color: #F28018;
            font-weight: bold;
        }

        .btn-warning {
            background-color: #F28018;
            border-color: #F28018;
            color: #ffffff;
            border-radius: 40px;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .btn-warning:hover {
            background-color: #d86d0f;
            border-color: #d86d0f;
        }

        .positive-diff { color: #28a745; }
        .negative-diff { 
            color: #dc3545; 
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
            }

            .scanner-section {
                padding: 15px;
            }

            .scan-form {
                flex-direction: column;
            }

            .scan-input {
                width: 100%;
                margin-bottom: 10px;
            }

            .preview-button {
                width: 100%;
            }

            .data-label {
                min-width: auto;
                display: block;
                margin-bottom: 5px;
            }
        }
        
        .preview-data {
            padding: 20px;
        }

        .preview-data .row {
            margin-bottom: 20px;
        }

        .preview-data .col-md-4 {
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .preview-data .col-md-4 h4 {
            margin-top: 0;
        }

        .preview-data .col-md-12 {
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .preview-data .col-md-4 {
                margin-bottom: 20px;
            }
        }
        
        .category-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .category-0 { background-color: #6c757d; } /* Gray for category 0 */
        .category-1 { background-color: #28a745; } /* Green for category 1 */
        .category-2 { background-color: #17a2b8; } /* Blue for category 2 */
        .category-3 { background-color: #dc3545; } /* Red for category 3 */
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
                <h2 class="scanner-title">
                    <i class="fas fa-qrcode"></i>
                    <?php echo $scannerNum == 1 ? "Base QR Code Scanner" : ($scannerNum == 2 ? "Cusion QR Code Scanner" : ($scannerNum == 3 ? "Thread QR Code Scanner" : "Scanner " . $scannerNum)); ?>
                </h2>
                
                <?php if (isset($_SESSION[$scannerKey . '_error'])): ?>
                    <div class="message error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $_SESSION[$scannerKey . '_error']; unset($_SESSION[$scannerKey . '_error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION[$scannerKey . '_success'])): ?>
                    <div class="message success-message">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $_SESSION[$scannerKey . '_success']; unset($_SESSION[$scannerKey . '_success']); ?>
                    </div>
                <?php endif; ?>

                <form class="scan-form" method="POST">
                    <input type="text" class="scan-input" id="qrInput<?php echo $scannerNum; ?>" name="qrData" 
                           placeholder="Scan <?php echo $scannerNum == 1 ? "Base" : "Scanner " . $scannerNum; ?> QR Code here" required>
                    <input type="hidden" name="scanner" value="<?php echo $scannerKey; ?>">
                    <button type="submit" class="preview-button">
                        <i class="fas fa-search"></i> Preview Data
                    </button>
                </form>

                <?php if (isset($_SESSION[$scannerKey . '_data'])): ?>
                    <div class="preview-box">
                        <h3 class="preview-title">
                            <i class="fas fa-file-alt"></i> QR Code Data Preview
                        </h3>
                        <div class="preview-data">
                            <div class="row">
                                <div class="col-md-4">
                                    <h4><i class="fas fa-flask"></i> Compound Information</h4>
                                    <p>
                                        <span class="data-label">Compound Name:</span> 
                                        <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']; ?></span>
                                    </p>
                                    <p>
                                        <span class="data-label">Batch Number:</span> 
                                        <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['batch_number']; ?></span>
                                    </p>
                                    <p>
                                        <span class="data-label">Job Number:</span> 
                                        <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['processed_data']['job_number']; ?></span>
                                    </p>
                                    <p>
                                        <span class="data-label">Tire Code:</span> 
                                        <span class="data-value"><?php echo $_SESSION[$scannerKey . '_data']['tire_code']; ?></span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <h4><i class="fas fa-tag"></i> Category Information</h4>
                                    <?php if (isset($_SESSION[$scannerKey . '_cat']) && isset($_SESSION[$scannerKey . '_c_cat'])): ?>
                                        <p>
                                            <span class="data-label">Category:</span> 
                                            <span class="data-value">
                                                <?php echo $_SESSION[$scannerKey . '_cat']; ?>
                                                <?php 
                                                    $c_cat = $_SESSION[$scannerKey . '_c_cat'];
                                                    if (is_numeric($c_cat)):
                                                ?>
                                                    <span class="category-badge category-<?php echo $c_cat; ?>">
                                                        C_Category <?php echo $c_cat; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION[$scannerKey . '_weights']) && isset($_SESSION[$scannerKey . '_cat'])): ?>
                                        <h4><i class="fas fa-weight"></i> Weight Information</h4>
                                        <?php 
                                        $categoryWeight = getCategoryWeight($_SESSION[$scannerKey . '_weights'], $_SESSION[$scannerKey . '_cat']);
                                        if ($categoryWeight !== null): 
                                        ?>
                                            <p>
                                                <span class="data-label">Category Weight:</span>
                                                <span class="weight-value"><?php echo number_format($categoryWeight, 2); ?> kg</span>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-warning">No weight found for this category.</p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <h4><i class="fas fa-balance-scale"></i> Actual Weight Details</h4>
                                    <?php 
                                    $actualWeight = getActualWeight($conn, $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']);
                                    if ($actualWeight !== null): 
                                    ?>
                                        <p>
                                            <span class="data-label">Actual Weight:</span>
                                            <span class="weight-value"><?php echo number_format($actualWeight, 2); ?> kg</span>
                                        </p>
                                    <?php endif; ?>
                                    <?php
                                    $jobNumber = $_SESSION[$scannerKey . '_data']['processed_data']['job_number'];
                                    $batchNumber = $_SESSION[$scannerKey . '_data']['processed_data']['batch_number'];
                                    $details = getMatchingDetails($conn, $jobNumber, $batchNumber, $tableName);
                                    ?>
                                    <h4><i class="fas fa-clipboard-list"></i> Batch Statistics</h4>
                                    <p>
                                        <span class="data-label">Total Scans:</span>
                                        <span class="weight-value"><?php echo $details['quantity']; ?></span>
                                    </p>
                                    <p>
                                        <span class="data-label">Total Weight:</span>
                                        <span class="weight-value"><?php echo number_format($details['table_total_weight'], 2); ?> kg</span>
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if (isset($actualWeight) && isset($categoryWeight)): ?>
                                        <h4><i class="fas fa-calculator"></i> Weight Analysis</h4>
                                        <?php
                                        $newTotal = $details['table_total_weight'] + $categoryWeight;
                                        $weightDifference = $actualWeight - $newTotal;
                                        $colorClass = $weightDifference >= 0 ? 'positive-diff' : 'negative-diff';
                                        ?>
                                        <p>
                                            <span class="data-label">Weight Difference:</span>
                                            <span class="weight-value <?php echo $colorClass; ?>">
                                                <?php echo number_format($weightDifference, 2); ?> kg
                                            </span>
                                        </p>
                                        <p>
                                            <span class="data-label">New Total Weight:</span>
                                            <span class="weight-value">
                                                <?php echo number_format($newTotal, 2); ?> kg
                                            </span>
                                        </p>
                                        <?php if ($weightDifference < 0): ?>
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Warning: Total weight would exceed actual weight!
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="confirm_scanner" value="<?php echo $scannerKey; ?>">
                            <input type="hidden" name="compound_name" 
                                value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['compound_name']; ?>">
                            <input type="hidden" name="batch_number" 
                                value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['batch_number']; ?>">
                            <input type="hidden" name="job_number" 
                                value="<?php echo $_SESSION[$scannerKey . '_data']['processed_data']['job_number']; ?>">
                            <input type="hidden" name="tire_code" 
                                value="<?php echo $_SESSION[$scannerKey . '_data']['tire_code']; ?>">
                            <button type="submit" name="confirm_insert" class="confirm-button">
                                <i class="fas fa-save"></i> Confirm & Insert Data
                            </button>
                        </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Function to focus on the next available input
            function focusNextAvailableInput() {
                const inputs = ['qrInput1', 'qrInput2', 'qrInput3'];
                for (let inputId of inputs) {
                    const input = document.getElementById(inputId);
                    const scannerSection = input.closest('.scanner-section');
                    const previewBox = scannerSection.querySelector('.preview-box');
                    
                    if (!previewBox) {
                        input.focus();
                        break;
                    }
                }
            }

            // Initial focus
            focusNextAvailableInput();

            // After successful scan and preview
            const successMessages = document.querySelectorAll('.success-message');
            if (successMessages.length > 0) {
                focusNextAvailableInput();
            }

            // Focus on next input when negative weight difference is detected
            const negativeDiffMessages = document.querySelectorAll('.negative-diff');
            if (negativeDiffMessages.length > 0) {
                focusNextAvailableInput();
            }

            // Add animation for success messages
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s ease-in-out';
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.remove();
                    }, 500);
                }, 3000);
            });
        });
    </script>
</body>
</html>














<?php
// Database connection
$host = "localhost";
$dbname = "planatir_task_managemen";
$username = "planatir_task_managemen";
$password = "Bishan@1919";

// Establish connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$serialNumber = '';
$message = '';
$buttonEnabled = false;
$tireCode = '';
$scannedCount = 0;
$baseRequiredDisplay = true; // Default value

// Process serial number if provided
if (isset($_GET['serialNumber']) && !empty($_GET['serialNumber'])) {
    $serialNumber = htmlspecialchars($_GET['serialNumber']);
    
    // Fetch tire code from tire_data table
    $stmt = $conn->prepare("SELECT tireCode FROM tire_data WHERE serialNumber = ?");
    $stmt->bind_param("s", $serialNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tireData = $result->fetch_assoc();
        $tireCode = $tireData['tireCode'];
        
        // Check if all components are scanned
        // Check for Base Component (qr_scanned_data)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM qr_scanned_data WHERE serial_number = ?");
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $row1 = $stmt->get_result()->fetch_assoc();
        
        // Check for Cushion Component (qr_scanned_data2)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM qr_scanned_data2 WHERE serial_number = ?");
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $row2 = $stmt->get_result()->fetch_assoc();
        
        // Check for Thread Component (qr_scanned_data3)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM qr_scanned_data3 WHERE serial_number = ?");
        $stmt->bind_param("s", $serialNumber);
        $stmt->execute();
        $row3 = $stmt->get_result()->fetch_assoc();
        
        // Calculate total scanned components
        $scannedCount = ($row1['count'] > 0 ? 1 : 0) + ($row2['count'] > 0 ? 1 : 0) + ($row3['count'] > 0 ? 1 : 0);
        
        // Check if the tire code exists in bom_new table and if columns b and c are empty
        $baseRequired = true; // Default: base component is required
        
        if (!empty($tireCode)) {
            $stmt = $conn->prepare("SELECT b, c FROM bom_new WHERE icode = ?");
            $stmt->bind_param("s", $tireCode);
            $stmt->execute();
            $bom_result = $stmt->get_result();
            
            if ($bom_result->num_rows > 0) {
                $bom_data = $bom_result->fetch_assoc();
                // If columns b and c are empty or null, base component is not required
                if (empty($bom_data['b']) && empty($bom_data['c'])) {
                    $baseRequired = false;
                }
            } else {
                // If no data exists in the table for this tire code, base component is not required
                $baseRequired = false;
            }
            
            // Store the base required status for use in the display
            $baseRequiredDisplay = $baseRequired;
        }
        
        // Modify the button enabled logic based on whether base component is required
        if ($baseRequired) {
            // Original logic - all 3 components required
            $buttonEnabled = ($scannedCount == 3);
        } else {
            // Only Cushion and Thread required - base is optional
            $cushionScanned = ($row2['count'] > 0 ? 1 : 0);
            $threadScanned = ($row3['count'] > 0 ? 1 : 0);
            $buttonEnabled = ($cushionScanned + $threadScanned == 2);
        }
        
        // Update the message based on the new logic
        if ($buttonEnabled) {
            $message = '<div class="success-message">
                <i class="fas fa-check-circle"></i> All required components have been successfully scanned. You may proceed to the next step.
            </div>';
        } else {
            $message = '<div class="error-message">
                <i class="fas fa-exclamation-circle"></i> Not all required components have been scanned yet. Please complete the scanning process.
            </div>';
        }
    } else {
        $message = '<div class="error-message">
            <i class="fas fa-exclamation-circle"></i> Serial number not found in the database.
        </div>';
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Serial Number Validation">
    <meta name="author" content="Enterprise Development">
    <link rel="shortcut icon" href="assets/custom/images/shortcut.png">
    <title>Serial Number Validation</title>
    
    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i|Cantarell:400,700" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="assets/vendor/fontawesome/css/fontawesome-all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .main-container {
            margin: 0 auto;
            max-width: 800px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 20px;
            background-color: #F28018;
            border-radius: 8px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #CCCCCC;
            border-radius: 40px;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s;
            font-size: 16px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 5px rgba(242,128,24,0.3);
        }
        
        .search-button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 12px 25px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }
        
        .search-button:hover {
            background-color: #333333;
        }
        
        .action-button {
            display: block;
            width: 100%;
            background-color: <?php echo $buttonEnabled ? '#F28018' : '#cccccc'; ?>;
            color: #FFFFFF;
            padding: 15px;
            border: none;
            border-radius: 40px;
            cursor: <?php echo $buttonEnabled ? 'pointer' : 'not-allowed'; ?>;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
        }
        
        .action-button:hover {
            background-color: <?php echo $buttonEnabled ? '#d86d0f' : '#cccccc'; ?>;
            color: #FFFFFF;
            text-decoration: none;
        }
        
        .result-section {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        
        .success-message {
            padding: 15px;
            background-color: #e6ffe6;
            color: #155724;
            border-left: 4px solid #28a745;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-message {
            padding: 15px;
            background-color: #ffe6e6;
            color: #721c24;
            border-left: 4px solid #dc3545;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            margin-bottom: 5px;
            display: block;
        }
        
        .info-value {
            color: #333333;
            font-family: 'Open Sans', sans-serif;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .progress-container {
            margin: 20px 0;
        }
        
        .progress-bar {
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #F28018;
            border-radius: 10px;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 12px;
            transition: width 0.5s ease-in-out;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .text-optional {
            color: #6c757d;
            font-style: italic;
            font-size: 12px;
            margin-left: 5px;
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1 class="page-title">
            <i class="fas fa-barcode"></i> Serial Number Validation
        </h1>
        
        <form method="GET" class="search-form">
            <input type="text" class="search-input" id="serialNumber" name="serialNumber" 
                   placeholder="Enter or scan serial number" value="<?php echo $serialNumber; ?>" required>
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i> Check
            </button>
        </form>
        
        <?php if (!empty($serialNumber)): ?>
            <?php echo $message; ?>
            
            <div class="result-section">
                <div class="info-item">
                    <span class="info-label">Serial Number</span>
                    <div class="info-value"><?php echo $serialNumber; ?></div>
                </div>
                
                <?php if (!empty($tireCode)): ?>
                <div class="info-item">
                    <span class="info-label">Tire Code</span>
                    <div class="info-value"><?php echo $tireCode; ?></div>
                </div>
                <?php endif; ?>
                
                <div class="progress-container">
                    <span class="info-label">Scanning Progress</span>
                    <?php 
                    // Adjust total required components based on whether base is required
                    $totalRequiredComponents = isset($baseRequiredDisplay) && !$baseRequiredDisplay ? 2 : 3;
                    
                    // Calculate required components that have been scanned
                    $requiredScannedCount = $scannedCount;
                    if (!$baseRequiredDisplay && isset($row1) && $row1['count'] > 0) {
                        $requiredScannedCount--; // Don't count base component if it's optional
                    }
                    
                    // Calculate progress percentage based on required components
                    $progressPercentage = ($totalRequiredComponents > 0) ? 
                        (min($requiredScannedCount, $totalRequiredComponents) / $totalRequiredComponents) * 100 : 0;
                    ?>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progressPercentage; ?>%">
                            <?php echo "$requiredScannedCount/$totalRequiredComponents"; ?>
                        </div>
                    </div>
                </div>
                
                <div class="scan-status">
                    <div class="info-item">
                        <span class="info-label">Scan Status</span>
                        <div class="info-value">
                            <div>
                                <i class="fas <?php echo isset($row1) && $row1['count'] > 0 ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?>"></i> 
                                Base Component
                                <?php if (isset($baseRequiredDisplay) && !$baseRequiredDisplay): ?>
                                <span class="text-optional">(Optional for this tire code)</span>
                                <?php endif; ?>
                            </div>
                            <div><i class="fas <?php echo isset($row2) && $row2['count'] > 0 ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?>"></i> Cushion Component</div>
                            <div><i class="fas <?php echo isset($row3) && $row3['count'] > 0 ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?>"></i> Thread Component</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="<?php echo $buttonEnabled ? 'about1.php?serialNumber='.urlencode($serialNumber) : '#'; ?>" 
               class="action-button" <?php echo !$buttonEnabled ? 'onclick="return false;"' : ''; ?>>
                <i class="fas fa-arrow-right"></i> Continue to Next Step
            </a>
            
            <?php if (!$buttonEnabled): ?>
            <p style="text-align: center; margin-top: 10px; color: #721c24;">
                <i class="fas fa-exclamation-circle"></i> Please complete all required component scans to proceed.
            </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Scripts -->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on input field when page loads
            document.getElementById('serialNumber').focus();
            
            // Auto-submit when scanning with a barcode scanner
            const input = document.getElementById('serialNumber');
            let lastTime = new Date().getTime();
            let scanBuffer = '';
            let scanTimeout = null;
            
            input.addEventListener('keydown', function(e) {
                // Detect if input is coming from a barcode scanner (very fast typing)
                const currentTime = new Date().getTime();
                const timeDiff = currentTime - lastTime;
                lastTime = currentTime;
                
                // If Enter key is pressed and typing was fast (barcode scanner typically types very fast)
                if (e.key === 'Enter' && input.value.length > 0) {
                    e.preventDefault();
                    document.querySelector('.search-form').submit();
                }
                
                // Extra handling for barcode scanners that don't send Enter key
                if (timeDiff < 50) { // Very fast typing indicates barcode scanner
                    clearTimeout(scanTimeout);
                    scanTimeout = setTimeout(function() {
                        if (input.value.length > 5) {
                            document.querySelector('.search-form').submit();
                        }
                    }, 300); // Wait for scanning to complete
                }
            });
        });
    </script>
</body>
</html>