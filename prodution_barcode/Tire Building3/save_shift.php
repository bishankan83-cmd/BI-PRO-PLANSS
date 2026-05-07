<?php
// Database connection details
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";




try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $logDate = $_POST['logDate'];
    $shift = $_POST['shift'];

    // Validate input
    if (empty($logDate) || empty($shift)) {
        echo "Log date and shift are required.";
        exit;
    }

    // Insert the data into the database
    $stmt = $pdo->prepare("INSERT INTO shifts (log_date, shift) VALUES (:logDate, :shift)");
    $stmt->bindParam(':logDate', $logDate);
    $stmt->bindParam(':shift', $shift);

    if ($stmt->execute()) {
        // Redirect to about1.php after successfully saving data
        header("Location: about1.php");
        exit; // Make sure to call exit after header to stop further script execution
    } else {
        echo "Error saving shift data.";
    }
}
?>
