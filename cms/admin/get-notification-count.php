<?php
// get-notification-count.php
session_start();
include('include/config.php');

// Check if admin is logged in
if (!isset($_SESSION["aid"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Query to count pending complaints
$query = mysqli_query($con, "SELECT COUNT(id) as count FROM tbl_tire_complaints WHERE status IS NULL");
if ($query) {
    $result = mysqli_fetch_assoc($query);
    $count = $result['count'];
} else {
    $count = 0;
}

// Set response headers
header('Content-Type: application/json');

// Return the count in JSON format
echo json_encode(['count' => $count]);

// Close database connection
mysqli_close($con);
?>