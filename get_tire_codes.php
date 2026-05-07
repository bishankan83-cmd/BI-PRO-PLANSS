<?php
// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";  // Default MySQL username
$password = "Bishan@1919";      // Default MySQL password (empty for XAMPP/local dev)
$dbname = "planatir_task_managemen";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Return JSON error response
    header('Content-Type: application/json');
    echo json_encode([
        "error" => "Connection failed: " . $conn->connect_error,
        "status" => "error"
    ]);
    exit();
}

try {
    // Prepare SQL to fetch distinct tire codes
    $sql = "SELECT icode FROM tire_details";
    
    // Execute query
    $result = $conn->query($sql);

    // Check if any tire codes exist
    if ($result->num_rows > 0) {
        $tire_codes = [];
        
        // Fetch tire codes
        while ($row = $result->fetch_assoc()) {
            // Trim and sanitize tire code
            $icode = htmlspecialchars(trim($row['icode']));
            
            // Add to array if not empty
            if (!empty($icode)) {
                $tire_codes[] = $icode;
            }
        }

        // Return successful JSON response
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "icodes" => $tire_codes,
            "icodess" => count($tire_codes)
        ]);
    } else {
        // No tire codes found
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "no_data",
            "message" => "No tire codes found in the database.",
            "icodes" => []
        ]);
    }
} catch (Exception $e) {
    // Catch any unexpected errors
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "error",
        "message" => "An unexpected error occurred: " . $e->getMessage()
    ]);
} finally {
    // Always close the database connection
    $conn->close();
}
?>