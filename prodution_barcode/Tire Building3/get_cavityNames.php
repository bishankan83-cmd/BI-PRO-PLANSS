<?php
/**
 * API endpoint to fetch available cavity names from the database
 * Returns JSON data for populating the cavity name dropdown
 */

// Include database connection
require_once 'db_connect.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Prepare SQL query to get all cavity names
    $stmt = $conn->prepare("SELECT DISTINCT cavityName FROM molds ORDER BY cavityName");
    $stmt->execute();
    
    // Fetch all results as an associative array
    $cavityNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return JSON response
    echo json_encode(['cavityNames' => $cavityNames]);
    
} catch (PDOException $e) {
    // Log error to server log
    error_log("Error fetching cavity names: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'error' => 'Failed to fetch cavity names',
        'cavityNames' => []
    ]);
}
?>