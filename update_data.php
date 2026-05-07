<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $icode = $_POST['icode'];
    $moldName = $_POST['moldName'];
    $cavityName = $_POST['cavity'];
    $isMold = $_POST['isMold'];
    $tiresPerMold = $_POST['tiresPerMold'];

    // Fetch the mold_id corresponding to the selected mold_name
    $moldIdQuery = "SELECT mold_id FROM mold WHERE mold_name = '$moldName'";
    $moldIdResult = mysqli_query($connection, $moldIdQuery);

    if (mysqli_num_rows($moldIdResult) > 0) {
        $row = mysqli_fetch_assoc($moldIdResult);
        $moldId = $row['mold_id'];

        // Update the mold_id and mold_name in the process table
        $updateQueryMold = "UPDATE `process` SET `mold_id` = '$moldId', `mold_name` = '$moldName' WHERE `cavity_name` = '$cavityName' AND `icode` = '$icode'";
        mysqli_query($connection, $updateQueryMold);
    }

    // Fetch the cavity_id corresponding to the selected cavity_name
    $cavityIdQuery = "SELECT cavity_id FROM cavity WHERE cavity_name = '$cavityName'";
    $cavityIdResult = mysqli_query($connection, $cavityIdQuery);

    if (mysqli_num_rows($cavityIdResult) > 0) {
        $row = mysqli_fetch_assoc($cavityIdResult);
        $cavityId = $row['cavity_id'];

        // Update the cavity_id and cavity_name in the process table
        $updateQueryCavity = "UPDATE `process` SET `cavity_id` = '$cavityId', `cavity_name` = '$cavityName' WHERE `mold_name` = '$moldName' AND `icode` = '$icode'";
        mysqli_query($connection, $updateQueryCavity);
    }

    // Update other columns as needed (e.g., tires_per_mold)
    $updateQueryOther = "UPDATE `process` SET `tires_per_mold` = '$tiresPerMold' WHERE `mold_name` = '$moldName' AND `cavity_name` = '$cavityName' AND `icode` = '$icode'";
    mysqli_query($connection, $updateQueryOther);
}

mysqli_close($connection);
?>
