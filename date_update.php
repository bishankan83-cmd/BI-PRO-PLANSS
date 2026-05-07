<?php
// Make sure to establish a MySQL database connection first
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user has provided a new availability date
if (isset($_POST['new_date'])) {
    // Sanitize and validate the user-provided date (you should perform more robust validation in a real application)
    $newDate = $_POST['new_date'];

    // Update the availability_date for the 'mold' table
    $sqlMold = "UPDATE mold SET availability_date = ?";
    $stmtMold = $conn->prepare($sqlMold);
    $stmtMold->bind_param("s", $newDate);


    // Update the availability_date for the 'table3' table
    $sqlTable3 = "UPDATE cavity SET availability_date = ?";
    $stmtTable3 = $conn->prepare($sqlTable3);
    $stmtTable3->bind_param("s", $newDate);

    if ($stmtMold->execute() &&$stmtTable3->execute()) {
        echo "Availability dates updated successfully for all three tables!";
        
        // Redirect to another PHP script
        header("Location: import2222.php");
    } else {
        echo "Error updating availability dates: " . $conn->error;
    }
    

    // Close the statements
    $stmtMold->close();
    
    $stmtTable3->close();
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Availability Date</title>
</head>
<body>
    <form method="post">
        <label for="new_date">Enter new availability date: </label>
        <input type="datetime-local" id="new_date" name="new_date" required>
        <button type="submit">Update Availability Date</button>
    </form>
</body>
</html>