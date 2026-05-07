<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['cavity'])) {
    $cavity = $_GET['cavity'];

    // Fetch the corresponding cavity_id from the cavity table
    $selectQuery = "SELECT `cavity_id` FROM cavity WHERE `cavity_name` = '$cavity' LIMIT 1";
    $result = mysqli_query($connection, $selectQuery);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $cavityId = $row['cavity_id'];

        // Send the cavity_id as a response
        echo $cavityId;
    } else {
        // If no matching cavity found, send an empty response or an error response
        echo "Error: Cavity not found.";
    }
} else {
    // If the 'cavity' parameter is not provided, send an error response
    echo "Error: Missing parameter 'cavity'.";
}

mysqli_close($connection);
?>
