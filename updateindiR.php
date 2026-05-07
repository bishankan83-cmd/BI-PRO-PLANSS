<?php
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

// Get the last start_date for each plan_id in the new_table
$lastStartDateQuery = "SELECT plan_id, MAX(start_date) AS last_start_date FROM new_table GROUP BY plan_id";
$lastStartDateResult = $conn->query($lastStartDateQuery);

if ($lastStartDateResult->num_rows > 0) {
    while ($row = $lastStartDateResult->fetch_assoc()) {
        $plan_id = $row['plan_id'];
        $last_start_date = $row['last_start_date'];

        // Update the corresponding end_date in the new_table using plan_id
        $updateQuery = "UPDATE new_table nt
                        JOIN plannew pn ON nt.plan_id = pn.plan_id
                        SET nt.end_date = pn.end_date
                        WHERE nt.plan_id = '$plan_id' AND nt.start_date = '$last_start_date'";

        if ($conn->query($updateQuery) === TRUE) {
            //echo "End date updated successfully for plan_id: $plan_id<br>";
        } else {
           // echo "Error updating end date for plan_id: $plan_id - " . $conn->error . "<br>";
        }
    }
} else {
    echo "No data found in new_table.<br>";
}

// Close the connection
$conn->close();

header("Location: indidate2R.php");
exit();
?>