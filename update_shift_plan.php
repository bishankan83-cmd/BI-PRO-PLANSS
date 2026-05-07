<?php
// Assuming you have a MySQL database connection established
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user inputs from the form
$date = $_POST['inputDate'];
$shift = $_POST['shift'];

// Construct and execute the SQL query to update all records
$sql = "UPDATE shift_plan SET date = ?, shift = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $date, $shift);

if ($stmt->execute()) {
    echo "All shift plans updated successfully!";
    // Close the database connection
    $stmt->close();
    $conn->close();
    
    // Redirect to another page (e.g., dashboard.php)
    header("Location: plan_edit2.php");
    exit();
} else {
    echo "Error updating shift plans: " . $conn->error;
}

// Close the database connection (in case of an error)
$stmt->close();
$conn->close();
?>
