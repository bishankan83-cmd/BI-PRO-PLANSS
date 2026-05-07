<?php
// Assuming you have a database connection established.
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if there is data in the `daily_plan_data1` table.
$sql = "SELECT COUNT(*) AS count FROM daily_plan_data1";
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $count = $row['count'];

    if ($count > 0) {
        // Redirect to another PHP page.
        header("Location: daily_plan_update.php");
        exit();
    } else {
        // Display an error message.
        echo "No data found in the table.";
    }
} else {
    // Handle any errors that occurred during the query.
    echo "Error: " . mysqli_error($conn);
}

// Close the database connection.
mysqli_close($conn);
?>
