<?php
// Check if the script is accessed through a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && isset($_POST["reason"])) {
    $id = $_POST["id"];
    $reason = $_POST["reason"];

    // Perform necessary validations and sanitation on $id and $reason

    // Your database connection code (similar to what you have in your existing code)
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update the database with the selected reason
    $updateReasonSql = "UPDATE template2b SET reason = ? WHERE id = ?";
    $stmt = $conn->prepare($updateReasonSql);
    $stmt->bind_param("si", $reason, $id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
    
    // Provide a response (optional)
    echo "Update successful";
} else {
    // Handle invalid requests or direct access
    header("HTTP/1.1 400 Bad Request");
    echo "Invalid request";
}
?>
