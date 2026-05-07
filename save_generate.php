<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$serialData = json_decode($input, true);

if (!$serialData || !is_array($serialData)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data received']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    $success_count = 0;
    $error_count = 0;
    
    // Prepare insert statement for generated_serials
    $insert_sql = "INSERT INTO generated_serials 
                   (serial_number, icode, brand, description, date, maxload) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    if (!$insert_stmt) {
        throw new Exception("Failed to prepare insert statement: " . $conn->error);
    }
    
    // Insert each serial into generated_serials
    foreach ($serialData as $item) {
        $serial_number = $item['serial_number'] ?? '';
        $icode = $item['tyre_code'] ?? '';
        $brand = $item['brand'] ?? '';
        $description = $item['description'] ?? '';
        $date = $item['date'] ?? null;
        $maxload = $item['maxload'] ?? '';
        
        $insert_stmt->bind_param("ssssss", 
            $serial_number, 
            $icode, 
            $brand, 
            $description, 
            $date, 
            $maxload
        );
        
        if ($insert_stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
        }
    }
    
    $insert_stmt->close();
    
    // Clear the get_serial2 table after successful insertion
    $clear_sql = "TRUNCATE TABLE get_serial2";
    if (!$conn->query($clear_sql)) {
        throw new Exception("Failed to clear get_serial2 table: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Successfully archived $success_count records and cleared pending queue",
        'archived_count' => $success_count,
        'error_count' => $error_count
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>