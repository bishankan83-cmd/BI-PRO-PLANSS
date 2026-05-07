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

// Fetch compound names based on the selected date
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['date'])) {
    $date = $_POST['date'];

    // Prepare SQL statement to fetch compound names for the given date
    $sql = "SELECT DISTINCT compound_name FROM bcompoundr WHERE inputDate = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $compoundNames = array();
    while ($row = $result->fetch_assoc()) {
        $compoundNames[] = $row['compound_name'];
    }


    
    // Close statement
    $stmt->close();

    // Return compound names as JSON
    echo json_encode($compoundNames);
} else {
    // If date is not set or if it's not a POST request, return an empty array
    echo json_encode(array());
}

// Close connection
$conn->close();
?>
