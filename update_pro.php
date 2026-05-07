<?php
// Connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling AJAX request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_immediately'])) {
    // Loop through the posted data to update records
    foreach ($_POST['new_plan_value'] as $recordID => $newPlanValue) {
        $newAdditionalData = $_POST['new_additional_data'][$recordID];

        // Update query
        $sql = "UPDATE daily_plan_data1
                SET Plan = ?, AdditionalData = ?
                WHERE ID = ?";

        // Prepare and bind the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $newPlanValue, $newAdditionalData, $recordID);

        // Execute the statement
        $stmt->execute();
        $stmt->close();
    }

    echo "Records updated successfully";
} else {
    echo "Invalid request";
}

// Close the connection
$conn->close();
?>
