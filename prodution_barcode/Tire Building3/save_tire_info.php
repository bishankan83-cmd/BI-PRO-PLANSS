<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serialNumber = $_POST['serialNumber'];
    $tireCode = $_POST['tireCode'];
    $tireDescription = $_POST['tireDescription'];
    $brand = $_POST['brand'];
    $tireWeight = $_POST['tireWeight'];
    $pressNumber = $_POST['pressNumber'];

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO tire_info (serial_number, tireCode, tire_description, brand, tire_weight, press_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $serialNumber, $tireCode, $tireDescription, $brand, $tireWeight, $pressNumber);

    if ($stmt->execute()) {
        echo "Tire information saved successfully! <a href='about2.html'>Go back</a>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
