<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Handle auto-saving form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $id = $_POST['id'];
    $quality_approved = $_POST['quality_approved'];
    $expire_date = $_POST['expire_date'];
    $staff_name = $_POST['staff_name'];
    $sg_value = $_POST['sg_value'];
    $hardness = $_POST['hardness'];
    $mh = $_POST['mh'];
    $ml = $_POST['ml'];
    $t10 = $_POST['t10'];
    $t90 = $_POST['t90'];
    $rebound = $_POST['rebound'];

    // Prepare and bind parameters for update
    $update_query = "UPDATE another_table_name SET quality_approved=?, expire_date=?, staff_name=?, sg_value=?, hardness=?, mh=?, ml=?, t10=?, t90=?, rebound=? WHERE id=?";
    $stmt = $connection->prepare($update_query);
    $stmt->bind_param("ssssssssssi", $quality_approved, $expire_date, $staff_name, $sg_value, $hardness, $mh, $ml, $t10, $t90, $rebound, $id);

    // Execute the update statement
    if ($stmt->execute()) {
        echo "Form data auto-saved successfully.";
    } else {
        echo "Error: " . $connection->error;
    }

    // Close statement
    $stmt->close();
}

// Close connection
$connection->close();
?>
