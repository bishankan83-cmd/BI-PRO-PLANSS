<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming the form submits all rows for updating
    $ids = $_POST['id'];
    $moldIds = $_POST['mold_id'];

    // Prepare the UPDATE statement
    $sql = "UPDATE tire_mold SET mold_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }

    // Bind parameters
    $stmt->bind_param("si", $moldId, $id);

    // Assuming each row has the same number of elements
    $rowCount = count($ids);

    // Update each row
    for ($i = 0; $i < $rowCount; $i++) {
        $id = $ids[$i];
        $moldId = $moldIds[$i];

        // Execute the statement
        if (!$stmt->execute()) {
            echo "Error updating record: " . $stmt->error;
            exit();
        }
    }

    // Close the statement
    $stmt->close();

    // Redirect back to the page where data was displayed
    header("Location: update_mold.php");
    exit();
}

// Close the database connection
$conn->close();
?>
