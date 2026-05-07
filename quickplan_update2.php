<?php
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

// Retrieve records from the plan table
$sql = "SELECT * FROM `quick_plan2`";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through each record in the plan table
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];
        $mold_id = $row['mold_id'];
        $cavity_id = $row['cavity_id'];

        // Update corresponding record in the quick_plan table
        $update_sql = "UPDATE `quick_plan` SET `cavity_id` = $cavity_id WHERE `icode` = '$icode' AND `mold_id` = $mold_id";
        if ($conn->query($update_sql) === TRUE) {
           // echo "Record updated successfully for icode: $icode and mold_id: $mold_id<br>";
        } else {
           // echo "Error updating record: " . $conn->error;
        }
    }
} else {
    echo "No records found in the plan table";
}

// Close connection
$conn->close();

header("Location: quickplan_delete2.php");
exit();

?>
