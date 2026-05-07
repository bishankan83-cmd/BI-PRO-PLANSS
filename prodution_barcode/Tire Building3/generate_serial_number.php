<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the last serial number
$sql = "SELECT serialNumber FROM tire_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

$newSerialNumber = '';
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastSerialNumber = $row['serialNumber'];

    // Extract the month, year, and random part from the serial number
    $month = substr($lastSerialNumber, 0, 2);
    $year = substr($lastSerialNumber, 2, 4);
    $randomSuffix = substr($lastSerialNumber, 6);

    // Increment the random part (pad to 4 digits)
    $newRandomSuffix = str_pad($randomSuffix + 1, 5, '0', STR_PAD_LEFT);
    
    // Generate the new serial number
    $newSerialNumber = $month . $year . $newRandomSuffix;
} else {
    // If no serial number exists, create one with the current month and year
    $currentDate = new DateTime();
    $month = $currentDate->format('m');
    $year = $currentDate->format('Y');
    $newSerialNumber = $month . $year . '00001'; // Starting with '0001' for the first entry
}

// Ensure the generated serial number follows the expected format
$pattern = '/^\d{6}\d{5}$/'; // Ensure the serial number format is MMYYYYXXXX
if (!preg_match($pattern, $newSerialNumber)) {
    $newSerialNumber = $month . $year . '00001'; // Reset to '0001' if invalid
}

$conn->close();

// Return the new serial number as JSON
echo json_encode(['serialNumber' => $newSerialNumber]);
?>
