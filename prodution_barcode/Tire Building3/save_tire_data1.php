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
    // Get and sanitize form data
    $serialNumber = $conn->real_escape_string($_POST['serialNumber']);
    $tireCode = $conn->real_escape_string($_POST['tireCode']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $tireWeight = $conn->real_escape_string($_POST['tireWeight']);
    $pressNumber = $conn->real_escape_string($_POST['pressNumber']);
    
    // Validate form data
    if (empty($serialNumber) || empty($tireCode) || empty($brand) || empty($tireWeight) || empty($pressNumber)) {
        die("Error: All fields are required.");
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // Check if the press is available
        $checkPressSql = "SELECT is_available FROM press WHERE press_name = ? AND is_available = 1";
        $checkPressStmt = $conn->prepare($checkPressSql);
        
        if (!$checkPressStmt) {
            throw new Exception("Press check preparation failed: " . $conn->error);
        }

        $checkPressStmt->bind_param("s", $pressNumber);
        $checkPressStmt->execute();
        $pressResult = $checkPressStmt->get_result();
        $checkPressStmt->close();

        if ($pressResult->num_rows === 0) {
            throw new Exception("Selected press is not available");
        }

        // Check if the serial number already exists
        $checkSql = "SELECT serialNumber FROM tire_data WHERE serialNumber = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if (!$checkStmt) {
            throw new Exception("Statement preparation failed: " . $conn->error);
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
                throw new Exception("Update statement preparation failed: " . $conn->error);
            }

            $stmt->bind_param("sssss", $tireCode, $brand, $tireWeight, $pressNumber, $serialNumber);
        } else {
            // Serial number doesn't exist, perform INSERT
            $insertSql = "INSERT INTO tire_data (serialNumber, tireCode, brand, tireWeight, pressNumber) 
                         VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            
            if (!$stmt) {
                throw new Exception("Insert statement preparation failed: " . $conn->error);
            }

            $stmt->bind_param("sssss", $serialNumber, $tireCode, $brand, $tireWeight, $pressNumber);
        }

        // Execute the prepared statement
        if (!$stmt->execute()) {
            throw new Exception("Error executing query: " . $stmt->error);
        }

        // Update press availability
        $updatePressSql = "UPDATE press SET is_available = 0, availability_date = NOW() 
                          WHERE press_name = ?";
        $updatePressStmt = $conn->prepare($updatePressSql);
        
        if (!$updatePressStmt) {
            throw new Exception("Press update preparation failed: " . $conn->error);
        }

        $updatePressStmt->bind_param("s", $pressNumber);
        
        if (!$updatePressStmt->execute()) {
            throw new Exception("Error updating press availability: " . $updatePressStmt->error);
        }

        $updatePressStmt->close();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to about2.php with the serial number
        header("Location: about2.php?serialNumber=" . urlencode($serialNumber));
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

$conn->close();
?>