<?php
// Database connection
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to update process.cavity_id using press_selections.cavity_ids
$sql = "
    UPDATE process p
    JOIN press_selections ps 
      ON p.icode = ps.icode AND p.mold_id = ps.mold_id
    SET p.cavity_id = ps.cavity_ids
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    // Redirect to another page on success
    header("Location: plannew45new2.php");
    exit(); // Always call exit() after header redirect
} else {
    echo "Error updating record: " . $conn->error;
}

// Close connection
$conn->close();
?>

