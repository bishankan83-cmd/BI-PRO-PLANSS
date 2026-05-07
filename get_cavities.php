<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_GET['press_name']) && isset($_GET['mold_name'])) {
    $pressName = $_GET['press_name'];
    $moldName = $_GET['mold_name'];

    $cavities = array();

    $selectQuery = "SELECT cavity_name FROM production_plan WHERE press_name = '$pressName' AND mold_name = '$moldName'";
    $result = mysqli_query($connection, $selectQuery);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cavities[] = $row['cavity_name'];
        }
    }

    mysqli_free_result($result);

    // Send the cavity names as a JSON response
    header('Content-Type: application/json');
    echo json_encode($cavities);
} else {
    echo "Invalid request parameters.";
}

mysqli_close($connection);
?>
