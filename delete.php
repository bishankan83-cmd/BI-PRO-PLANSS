<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE template SET deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Delete successful";
    } else {
        echo "Delete failed";
    }

    $stmt->close();
} else {
    echo "Invalid request";
}

$conn->close();
?>
