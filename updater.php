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

if(isset($_POST['submit'])) {
    // Prepare and bind parameters for update
    $update_query = "UPDATE another_table_name SET  quality_approved=?, expire_date=?, staff_name=?, sg_value=?, hardness=?, mh=?, ml=?, t10=?, t90=?, rebound=? WHERE id=?";
    $stmt = $connection->prepare($update_query);
    $stmt->bind_param("ssssssssssi", $quality_approved, $expire_date, $staff_name, $sg_value, $hardness, $mh, $ml, $t10, $t90, $rebound, $id);
    
    // Update data in the table
    foreach($_POST['quality_approved'] as $id => $value) {
        $quality_approved = $_POST['quality_approved'][$id];
        $expire_date = $_POST['expire_date'][$id];
        $staff_name = $_POST['staff_name'][$id];
        $sg_value = $_POST['sg_value'][$id];
        $hardness = $_POST['hardness'][$id];
        $mh = $_POST['mh'][$id];
        $ml = $_POST['ml'][$id];
        $t10 = $_POST['t10'][$id];
        $t90 = $_POST['t90'][$id];
        $rebound = $_POST['rebound'][$id];
        $id = $id;
        
        // Execute the update statement
        $stmt->execute();
    }
    
    // Close statement
    $stmt->close();
    
    // Provide feedback to the user and refresh the page after data update
    echo "<div class='container'>Data updated successfully.</div>";
}
?>
