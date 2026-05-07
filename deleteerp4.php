<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Get the ERP number from the derp table
$sql_select = "SELECT erp FROM derp";
$result = $conn->query($sql_select);

if ($result->num_rows > 0) {
    // Step 2: Iterate through the result and delete related data from merged_data table
    while ($row = $result->fetch_assoc()) {
        $erp_number = $row["erp"];
        // Step 2a: Delete related data from merged_data table
        $sql_delete_merged = "DELETE FROM merged_data WHERE erp = '$erp_number'";
        if ($conn->query($sql_delete_merged) !== TRUE) {
            echo "Error deleting data from merged_data: " . $conn->error;
        }
        
        // Step 2b: Delete related data from tobeplan1 table
        $sql_delete_tobeplan1 = "DELETE FROM tobeplan1 WHERE erp = '$erp_number'";
        if ($conn->query($sql_delete_tobeplan1) !== TRUE) {
            echo "Error deleting data from tobeplan1: " . $conn->error;
        }
    }

    // Step 3: Delete all data from the derp table
    $sql_delete_all = "DELETE FROM derp";
    if ($conn->query($sql_delete_all) !== TRUE) {
        echo "Error deleting data from derp: " . $conn->error;
    } else {
        echo "Data deleted successfully from merged_data, tobeplan1, and derp.";
    }
} else {
    echo "No ERP numbers found in derp table.";
}

// Close the connection
$conn->close();

// Redirect to another_page.php after updates
header("Location: planning.php");
exit; // Make sure to exit the script to prevent further execution
?>
