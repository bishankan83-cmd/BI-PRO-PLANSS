<?php
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

// Check if the date parameter is set
if (isset($_POST['date'])) {
    // Get the selected date
    $selectedDate = $_POST['date'];

    // Prepare a SQL statement to fetch serial numbers based on the selected date
    $sql = "SELECT DISTINCT serial_number FROM bcompoundr WHERE inputDate = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch serial numbers from the result set
    $serialNumbers = array();
    while ($row = $result->fetch_assoc()) {
        $serialNumbers[] = $row['serial_number'];
    }

    // Close statement
    $stmt->close();

    // Return the serial numbers as JSON
    echo json_encode($serialNumbers);
} else {
    // If date parameter is not set, return an empty array
    echo json_encode(array());
}

// Close connection
$conn->close();
?>
