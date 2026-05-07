<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a connection to the database
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputDate = $_POST["inputDate"];
    $shift = $_POST["shift"];
    $icodeArray = $_POST["icode"];
    $cstockArray = $_POST["cstock"];
    $reasonArray = $_POST["reason"];
    $rejectArray = $_POST["reject"]; // Add the Reject field

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO template (icode, cstock, date, shift, reason, reject) VALUES (?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $stmt->bind_param("ssssss", $icode, $cstock, $inputDate, $shift, $reason, $reject);

    // Iterate through the submitted data and insert it into the database
    for ($i = 0; $i < count($icodeArray); $i++) {
        $icode = !empty($icodeArray[$i]) ? $icodeArray[$i] : 0;
        $cstock = !empty($cstockArray[$i]) ? $cstockArray[$i] : 0;
        $reason = !empty($reasonArray[$i]) ? $reasonArray[$i] : 0;
        $reject = !empty($rejectArray[$i]) ? $rejectArray[$i] : 0; // Get the Reject value

        // Execute the prepared statement
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
            break; // exit the loop if an error occurs
        }
    }

    // Close the prepared statement
    $stmt->close();

    // Close the database connection
    $conn->close();

    header("Location: showdaily2.php");
    exit();
}
?>
