<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit;
}

if (isset($_GET['icode'])) {
    $icode = $conn->real_escape_string($_GET['icode']);
    $sql = "SELECT description, greenweight, stgreenweight FROM tire_details WHERE icode = '$icode'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(["success" => true, "description" => $row['description'], "greenweight" => $row['greenweight'], "stgreenweight" => $row['stgreenweight']]);
    } else {
        echo json_encode(["success" => false, "message" => "ICode not found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

$conn->close();
?>
