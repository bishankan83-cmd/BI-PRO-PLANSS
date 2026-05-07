<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Database connection parameters
$host = "localhost";
$dbname = "planatir_task_managemen";
$username = "planatir_task_managemen";
$password = "Bishan@1919";

// Get the tire code from URL parameter
$tireCode = isset($_GET['tireCode']) ? $_GET['tireCode'] : '';

// Validate input
if (empty($tireCode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Tire code is required'
    ]);
    exit;
}

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare and execute the query to fetch tire details for the given tire code
    $stmt = $pdo->prepare("
        SELECT 
            brand, 
            tireWeight
        FROM 
            tire_data
        WHERE 
            tireCode = :tireCode
        LIMIT 1
    ");
    
    $stmt->bindParam(':tireCode', $tireCode, PDO::PARAM_STR);
    $stmt->execute();
    
    // Fetch the tire details
    $tireDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If no tire details found, return error
    if (!$tireDetails) {
        echo json_encode([
            'success' => false,
            'message' => 'No tire details found for this code'
        ]);
        exit;
    }
    
    // Return the result as JSON
    echo json_encode([
        'success' => true,
        'brand' => $tireDetails['brand'],
        'tireWeight' => $tireDetails['tireWeight']
    ]);
    
} catch (PDOException $e) {
    // Log the error (to error log)
    error_log("Database error in get_tireDetails.php: " . $e->getMessage());
    
    // Return generic error message to client (for security)
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again or contact support.'
    ]);
}
?>