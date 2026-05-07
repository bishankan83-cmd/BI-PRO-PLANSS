<?php
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";  // Ensure this is the correct DB name

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve POST data
    $labelName = $_POST['labelName'];
    $batchNumber = $_POST['batchNumber'];
    $qrReference = $_POST['qrReference'];

    // Validate inputs (optional)
    if (!empty($labelName) && !empty($batchNumber) && !empty($qrReference)) {
        // Prepare and execute SQL statement to insert data into the qr_code_scans table
        $stmt = $conn->prepare("INSERT INTO qr_code_scans (label_name, batch_number, qr_reference) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $labelName, $batchNumber, $qrReference);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to another page (about3.html) after successful insertion
            header("Location: about3.html");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Please fill in all fields!";
    }
}

// Close the connection
$conn->close();
?>
