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

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to validate required fields
function validateRequiredFields($fields) {
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            return false;
        }
    }
    return true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_insert'])) {
    // Required fields validation
    $requiredFields = [
        'confirm_scanner',
        'compound_name',
        'batch_number',
        'job_number',
        'tire_code',
        'serial_number',
        'category_weight',
        'weight_difference'
    ];

    if (!validateRequiredFields($requiredFields)) {
        $_SESSION['process_error'] = "Error: Missing required fields";
        header("Location: tire_details_entry.php?serialNumber=" . urlencode($_POST['serial_number']));
        exit();
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        $scanner = $_POST['confirm_scanner'];
        $CN = $_POST['compound_name'];
        $BN = $_POST['batch_number'];
        $JN = $_POST['job_number'];
        $TC = $_POST['tire_code'];
        $serialNumber = $_POST['serial_number'];
        $categoryWeight = floatval($_POST['category_weight']);
        $weightDifference = floatval($_POST['weight_difference']);

        // Determine which table to use based on scanner
        $table_name = 'qr_scanned_data';
        if ($scanner == 'scanner2') $table_name = 'qr_scanned_data2';
        if ($scanner == 'scanner3') $table_name = 'qr_scanned_data3';

        // Insert the data
        $stmt = $conn->prepare("INSERT INTO $table_name (
            compound_name, 
            batch_number, 
            job_number, 
            serial_number, 
            tire_code, 
            weight_difference, 
            category_weight,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("sssssdd", 
            $CN, 
            $BN, 
            $JN, 
            $serialNumber, 
            $TC, 
            $weightDifference, 
            $categoryWeight
        );

        if (!$stmt->execute()) {
            throw new Exception("Error inserting data: " . $stmt->error);
        }

        // Log the successful scan
        $logStmt = $conn->prepare("INSERT INTO scan_logs (
            scanner_id, 
            compound_name, 
            batch_number, 
            job_number, 
            serial_number,
            status,
            weight_difference,
            category_weight,
            created_at
        ) VALUES (?, ?, ?, ?, ?, 'success', ?, ?, NOW())");

        $scannerId = str_replace('scanner', '', $scanner);
        $logStmt->bind_param("sssssdd", 
            $scannerId, 
            $CN, 
            $BN, 
            $JN, 
            $serialNumber,
            $weightDifference,
            $categoryWeight
        );

        if (!$logStmt->execute()) {
            throw new Exception("Error logging scan: " . $logStmt->error);
        }

        // If we got here, commit the transaction
        $conn->commit();

        // Clear the preview data and set success message
        $_SESSION[$scanner . '_success'] = "Data successfully inserted into the database.";
        unset($_SESSION[$scanner . '_data']);

        $stmt->close();
        $logStmt->close();

    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $_SESSION[$scanner . '_error'] = "Error: " . $e->getMessage();
    }

    // Update process status
    $processStatus = [
        'timestamp' => date('Y-m-d H:i:s'),
        'scanner' => $scanner,
        'status' => isset($_SESSION[$scanner . '_error']) ? 'error' : 'success'
    ];
    $_SESSION['process_status'] = $processStatus;

} else {
    $_SESSION['process_error'] = "Invalid request method";
}

// Close database connection
$conn->close();

// Redirect back to the main page
$returnUrl = "tire_details_entry.php?serialNumber=" . urlencode($_POST['serial_number']);
header("Location: " . $returnUrl);
exit();
?>