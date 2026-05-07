<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['field']) && isset($_POST['value'])) {
    $id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("UPDATE template SET $field = ? WHERE id = ?");
    $stmt->bind_param("si", $value, $id);
    
    if ($stmt->execute()) {
        // The update was successful
        echo "Update successful";
    } else {
        // Handle the update failure, if needed
        echo "Update failed";
    }
    
    $stmt->close();
}

$conn->close();

?>
