<?php
// Database configuration
$host = 'localhost';
$db = 'planatir_task_managemen';
$user = 'planatir_task_managemen';
$password = 'Bishan@1919'; // Add your MySQL password if needed

// Create database connection
$conn = new mysqli($host, $user, $password, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mrn_number = isset($_POST['mrn_number']) ? intval($_POST['mrn_number']) : null;
    $rm_code = isset($_POST['RM_code']) ? $_POST['RM_code'] : null;
    $band_size = isset($_POST['band_size']) ? $_POST['band_size'] : null;
    $num_of_bands = isset($_POST['num_of_bands']) ? $_POST['num_of_bands'] : null;

    // Validate MRN number
    if ($mrn_number === null) {
        die("MRN number is missing!");
    }

    $stmt = $conn->prepare("INSERT INTO material_request (mrn_number, RM_code, band_size, num_of_bands) VALUES (?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Check if band_size is an array and handle it accordingly
    if (is_array($band_size)) {
        foreach ($band_size as $index => $size) {
            if (isset($num_of_bands[$index]) && $num_of_bands[$index] !== null) {
                $stmt->bind_param("isss", $mrn_number, $rm_code[$index], $size, $num_of_bands[$index]);
                if (!$stmt->execute()) {
                    echo "<script>alert('Error saving band size: " . htmlspecialchars($stmt->error) . "');</script>";
                }
            }
        }
    } else {
        $stmt->bind_param("isss", $mrn_number, $rm_code, $band_size, $num_of_bands);
        if (!$stmt->execute()) {
            echo "<script>alert('Error saving data: " . htmlspecialchars($stmt->error) . "');</script>";
        }
    }

    $stmt->close();
}

$conn->close();
?>