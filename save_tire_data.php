<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serialNumber = $_POST['serialNumber'];
    $icode = $_POST['icode'];
    $brand = $_POST['brand'];
    $tireWeight = $_POST['tireWeight'];
    $pressNumber = $_POST['pressNumber'];

    // Validate form data
    if (empty($serialNumber) || empty($icode) || empty($brand) || empty($tireWeight) || empty($pressNumber)) {
        die("Error: All fields are required.");
    }

    // Insert tire details into the database
    $sql = "INSERT INTO tire_data (serialNumber, icode, brand, tireWeight, pressNumber) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Statement preparation failed: " . $conn->error);
    }

    $stmt->bind_param("sssss", $serialNumber, $icode, $brand, $tireWeight, $pressNumber);

    if ($stmt->execute()) {
        // Redirect to about2.html after successful insertion
        header("Location: about2.html");
        exit();
    } else {
        die("Error executing query: " . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>
