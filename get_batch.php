<?php
// Check if the date parameter is provided
if(isset($_POST['date'])) {
    // Get the date value
    $date = $_POST['date'];

    // Database connection details
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

    // Prepare SQL statement to fetch distinct batches based on input date
    $sql = "SELECT DISTINCT batch FROM bcompound WHERE inputDate = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $batches = array();

    if ($result->num_rows > 0) {
        // Fetch batch data and store in an array
        while ($row = $result->fetch_assoc()) {
            $batches[] = $row['batch'];
        }
        
        // Sort batches numerically
        sort($batches);
        
        // Reindex batches starting from 1
        $batches = array_values($batches);
    }

    // Close connection
    $conn->close();

    // Send batches as JSON response
    echo json_encode($batches);
} else {
    // If date parameter is not provided, return error message
    echo "Error: Date parameter is missing.";
}
?>
