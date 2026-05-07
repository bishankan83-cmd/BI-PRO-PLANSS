<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $erp = $_POST["erp"];
    $new_quantity = $_POST["new_quantity"];

    // Database connection settings
    $host = "localhost";
    $username = "planatir_task_managemen";
  
    $password = "Bishan@1919";
    $database = "planatir_task_managemen";


    // Create a new MySQLi connection
    $conn = new mysqli($host, $username, $password, $database);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update the quantity based on ERP value
    $updateSql = "UPDATE dwork SET quantity = ? WHERE erp = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("is", $new_quantity, $erp);

    if ($stmt->execute()) {
        header("Location: dwork.php"); // Redirect back to the manage_quantity.php page
    } else {
        echo "Error updating quantity: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
