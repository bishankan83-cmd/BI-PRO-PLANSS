<?php
// Assuming you have a database connection established already

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rowIds'])) {
    // Decode the JSON string sent from the client
    $selectedRowIds = json_decode($_POST['rowIds']);

    // Validate and sanitize the received data as needed

    // Perform deletion in the database
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    foreach ($selectedRowIds as $rowId) {
        $sql = "DELETE FROM template WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $rowId);

        if ($stmt->execute()) {
            // Deletion successful
            // You can log or handle success as needed
        } else {
            // Deletion failed
            // You can log or handle failure as needed
        }

        $stmt->close();
    }

    $conn->close();

    // Send a response back to the client
    echo "Deletion completed successfully";
} else {
    // Invalid request
    http_response_code(400);
    echo "Invalid request";
}
?>
