<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serialNumber = $_POST['serialNumber'];
    $tireCode = $_POST['tireCode'];
    $brand = $_POST['brand'];
    $tireWeight = $_POST['tireWeight'];
    $pressNumber = isset($_POST['pressNumber']) ? $_POST['pressNumber'] : '';
    
    // Validate form data - make pressNumber optional when tireCode is 00000
    if (empty($serialNumber) || empty($tireCode) || empty($brand) || empty($tireWeight)) {
        die("Error: Serial Number, Tire Code, Brand, and Tire Weight are required.");
    }
    
    // For non-00000 tire codes, press number is required
    if ($tireCode !== "00000" && empty($pressNumber)) {
        die("Error: Press Number is required for Tire Codes other than 00000.");
    }
    
    // Handle tire code 00000 specially
    if ($tireCode === "00000") {
        // For tire code 00000, always insert into tire_data
        $insertSql = "INSERT INTO tire_data (serialNumber, tireCode, brand, tireWeight, pressNumber)
                      VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        
        if (!$stmt) {
            die("Statement preparation failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssss", $serialNumber, $tireCode, $brand, $tireWeight, $pressNumber);
        
        // Execute the prepared statement
        if ($stmt->execute()) {
            // Redirect to abt1.php as specified
            header("Location: abt1.php");
            exit();
        } else {
            die("Error executing query: " . $stmt->error);
        }
        
        $stmt->close();
    } else {
        // Normal handling for other tire codes
        // First, check if the serial number already exists
        $checkSql = "SELECT serialNumber FROM tire_data WHERE serialNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if (!$checkStmt) {
            die("Statement preparation failed: " . $conn->error);
        }
        
        $checkStmt->bind_param("s", $serialNumber);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $checkStmt->close();
        
        if ($result->num_rows > 0) {
            // Serial number exists, perform UPDATE
            $updateSql = "UPDATE tire_data
                          SET tireCode = ?, brand = ?, tireWeight = ?, pressNumber = ?
                          WHERE serialNumber = ?";
            $stmt = $conn->prepare($updateSql);
            
            if (!$stmt) {
                die("Statement preparation failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssss", $tireCode, $brand, $tireWeight, $pressNumber, $serialNumber);
        } else {
            // Serial number doesn't exist, perform INSERT
            $insertSql = "INSERT INTO tire_data (serialNumber, tireCode, brand, tireWeight, pressNumber)
                          VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            
            if (!$stmt) {
                die("Statement preparation failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssss", $serialNumber, $tireCode, $brand, $tireWeight, $pressNumber);
        }
        
        // Execute the prepared statement
        if ($stmt->execute()) {
            header("Location: about2.php?serialNumber=" . urlencode($serialNumber));
            exit();
        } else {
            die("Error executing query: " . $stmt->error);
        }
        
        $stmt->close();
    }
}

$conn->close();
?>