<?php
// Replace these with your actual database connection details
$hostname = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the new_table
$sql = "SELECT plan_id, difference, mold_id, cavity_id, creation_time FROM new_table3";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plan_id = $row["plan_id"];
        $difference = $row["difference"];
        $mold_id = $row["mold_id"];
        $cavity_id = $row["cavity_id"];
        $creation_time = $row["creation_time"];

        // Calculate time taken to create one ID in seconds
        $time_per_id_seconds = $creation_time * 60;

        // Calculate time taken to create all difference quantities in seconds
        $total_time_seconds = $time_per_id_seconds * $difference;

        // Calculate the last record's end date for the same plan_id
        $last_record_end_date_sql = "SELECT MAX(end_date) AS last_end_date FROM plannew WHERE plan_id = $plan_id";
        $last_record_result = $conn->query($last_record_end_date_sql);
        $last_record_row = $last_record_result->fetch_assoc();
        $last_end_date = $last_record_row["last_end_date"];

        // Calculate end date by adding total time to the last end date
        $end_date = date('Y-m-d H:i:s', strtotime($last_end_date) + $total_time_seconds);

        // Update end_date in the plannew table
        $update_sql = "UPDATE plannew SET end_date = '$end_date' WHERE plan_id = $plan_id";
        $conn->query($update_sql);


    }
} else {
    echo "No records found in new_table";
}

// Close the database connection
$conn->close();

header("Location: inddelete.php");
exit();
?>
