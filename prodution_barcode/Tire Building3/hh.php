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
    // Convert category to lowercase to match array keys
    $category = strtolower($category);
    
    // Check if the category exists in weights
    if (isset($weights[$category])) {
        return $weights[$category];
    }
    return null;
}

// Function to get matching quantity
function getMatchingQuantity($conn, $jobNumber, $batchNumber, $tableName) {
    $stmt = $conn->prepare("SELECT COUNT(*) as quantity FROM $tableName WHERE job_number = ? AND batch_number = ?");
    $stmt->bind_param("ss", $jobNumber, $batchNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data['quantity'];
}

// Function to calculate total weight
function calculateTotalWeight($unitWeight, $quantity) {
    return number_format($unitWeight * $quantity, 2);
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
    
    $table_name = 'qr_scanned_data';
    if ($scanner == 'scanner2') $table_name = 'qr_scanned_data2';
    if ($scanner == 'scanner3') $table_name = 'qr_scanned_data3';
    
    $stmt = $conn->prepare("INSERT INTO $table_name (compound_name, batch_number, job_number, serial_number, tire_code) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $CN, $BN, $JN, $serialNumber, $TC);
    
    if ($stmt->execute()) {
        $_SESSION[$scanner . '_success'] = "Data successfully inserted into the database.";
        unset($_SESSION[$scanner . '_data']); // Clear the preview data
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
        .quantity-info .data-label {
            font-weight: bold;
            color: #495057;
        }
        .quantity-info .weight-value {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
            margin-left: 5px;
        }
        .total-weight {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1 class="page-title">Tire Details Entry</h1>

        <!-- Base Scanner Section -->
        <div class="scanner-section">
            <h2 class="scanner-title">Base QR Code Scanner</h2>
            
            <?php if (isset($_SESSION['scanner1_error'])): ?>
                <div class="message error-message"><?php echo $_SESSION['scanner1_error']; unset($_SESSION['scanner1_error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['scanner1_success'])): ?>
                <div class="message success-message"><?php echo $_SESSION['scanner1_success']; unset($_SESSION['scanner1_success']); ?></div>
            <?php endif; ?>

            <form class="scan-form" method="POST">
                <input type="text" class="scan-input" id="qrInput1" name="qrData" placeholder="Scan Base QR Code here" autofocus required>
                <input type="hidden" name="scanner" value="scanner1">
                <button type="submit" class="preview-button">Preview Base Data</button>
            </form>

            <?php if (isset($_SESSION['scanner1_data'])): ?>
                <div class="preview-box">
                    <h3 class="preview-title">Base QR Code Data Preview</h3>
                    <div class="preview-data">
                        <p><span class="data-label">Compound Name:</span> <span class="data-value"><?php echo $_SESSION['scanner1_data']['processed_data']['compound_name']; ?></span></p>
                        <p><span class="data-label">Batch Number:</span> <span class="data-value"><?php echo $_SESSION['scanner1_data']['processed_data']['batch_number']; ?></span></p>
                        <p><span class="data-label">Job Number:</span> <span class="data-value"><?php echo $_SESSION['scanner1_data']['processed_data']['job_number']; ?></span></p>
                        <p><span class="data-label">Tire Code:</span> <span class="data-value"><?php echo $_SESSION['scanner1_data']['tire_code']; ?></span></p>
                        <?php if (isset($_SESSION['scanner1_cat'])): ?>
                            <p><span class="data-label">Category:</span> <span class="data-value"><?php echo $_SESSION['scanner1_cat']; ?></span></p>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['scanner1_weights']) && isset($_SESSION['scanner1_cat'])): ?>
                            <div class="compound-weights">
                                <h4>Compound Weight for Category <?php echo strtoupper($_SESSION['scanner1_cat']); ?>:</h4>
                                <?php 
                                $categoryWeight = getCategoryWeight($_SESSION['scanner1_weights'], $_SESSION['scanner1_cat']);
                                if ($categoryWeight !== null): 
                                ?>
                                    <p><span class="data-label">Weight Value: </span><span class="weight-value"><?php echo $categoryWeight; ?></span></p>
                                <?php else: ?>
                                    <p>No weight found for this category.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        $jobNumber = $_SESSION['scanner1_data']['processed_data']['job_number'];
                        $batchNumber = $_SESSION['scanner1_data']['processed_data']['batch_number'];
                        $quantity = getMatchingQuantity($conn, $jobNumber, $batchNumber, 'qr_scanned_data');
                        
                        // Calculate total weight if category weight exists
                        $totalWeight = 0;
                        if (isset($categoryWeight) && $categoryWeight !== null) {
                            $totalWeight = calculateTotalWeight($categoryWeight, $quantity);
                        }
                        ?>
                        <div class="quantity-info">
                            <h4>Quantity Information</h4>
                            <p><span class="data-label">Total scans for this Job/Batch: </span>
                            <span class="weight-value"><?php echo $quantity; ?></span></p>
                            <?php if ($totalWeight > 0): ?>
                                <p><span class="data-label">Total Weight (Weight × Quantity): </span>
                                <span class="total-weight"><?php echo $totalWeight; ?></span></p>
                            <?php endif; ?>
                        </div>
                    </div> 
                    <form method="POST">
                        <input type="hidden" name="confirm_scanner" value="scanner3">
                        <input type="hidden" name="compound_name" value="<?php echo $_SESSION['scanner3_data']['processed_data']['compound_name']; ?>">
                        <input type="hidden" name="batch_number" value="<?php echo $_SESSION['scanner3_data']['processed_data']['batch_number']; ?>">
                        <input type="hidden" name="job_number" value="<?php echo $_SESSION['scanner3_data']['processed_data']['job_number']; ?>">
                        <input type="hidden" name="tire_code" value="<?php echo $_SESSION['scanner3_data']['tire_code']; ?>">
                        <button type="submit" name="confirm_insert" class="confirm-button">Confirm & Insert Data</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
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