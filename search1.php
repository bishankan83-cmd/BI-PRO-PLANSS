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

// Get JSON data sent from frontend
$data = json_decode(file_get_contents("php://input"), true);

// Extract fields from JSON data
$id = $data['id'];
$plan_id = $data['plan_id'];
$erp = $data['erp'];
$Customer = $data['Customer'];
$icode = $data['icode'];
$description = $data['description'];
$tobe = $data['tobe'];
$press = $data['press'];
$press_name = $data['press_name'];
$mold_id = $data['mold_id'];
$mold_name = $data['mold_name'];
$cavity_id = $data['cavity_id'];
$cavity_name = $data['cavity_name'];
$cuing_group_id = $data['cuing_group_id'];
$cuing_group_name = $data['cuing_group_name'];
$start_date = $data['start_date'];
$end_date = $data['end_date'];
$tires_per_mold = $data['tires_per_mold'];

// Prepare SQL statement
$sql = "UPDATE plannew SET 
    plan_id = ?,
    erp = ?,
    Customer = ?,
    icode = ?,
    description = ?,
    tobe = ?,
    press = ?,
    press_name = ?,
    mold_id = ?,
    mold_name = ?,
    cavity_id = ?,
    cavity_name = ?,
    cuing_group_id = ?,
    cuing_group_name = ?,
    start_date = ?,
    end_date = ?,
    tires_per_mold = ?
    WHERE id = ?";

// Prepare and bind parameters
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssiisssississsi", $plan_id, $erp, $Customer, $icode, $description, $tobe, $press, $press_name, $mold_id, $mold_name, $cavity_id, $cavity_name, $cuing_group_id, $cuing_group_name, $start_date, $end_date, $tires_per_mold, $id);

// Execute SQL statement
if ($stmt->execute()) {
    // Return success response
    $response = array('success' => true);
} else {
    // Return error response
    $response = array('success' => false);
}

// Close statement and connection
$stmt->close();
$conn->close();

// Output JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
