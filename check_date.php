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

// SQL query to select data where tires_per_mold is 0
$sql = "SELECT * FROM old_process WHERE tires_per_mold = 0";

// Execute the query
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $erp = $row["erp"];
        $serial = $row["serial"];
        $icode = $row["icode"];
        $mold_id = $row["mold_id"];
        $cavity_id = $row["cavity_id"];

        // Check if the specified fields exist in the process table
        $checkQuery = "SELECT * FROM process WHERE erp = '$erp' AND serial = '$serial' AND icode = '$icode' AND mold_id = '$mold_id' AND cavity_id = '$cavity_id'";
        $checkResult = $conn->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            // Delete the row from process
            $deleteQuery = "DELETE FROM process WHERE erp = '$erp' AND serial = '$serial' AND icode = '$icode' AND mold_id = '$mold_id' AND cavity_id = '$cavity_id'";
            $conn->query($deleteQuery);

           
        } 
    }
} else {
    echo "No results found where tires_per_mold is 0";
}

// Close the database connection
$conn->close();

// Redirect to another page after the processing is complete
header("Location: ch.php");
exit();

?>

