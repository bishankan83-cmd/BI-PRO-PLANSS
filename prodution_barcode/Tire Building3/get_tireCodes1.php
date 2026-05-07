<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    $query = "SELECT icode as code, Description as description FROM tire_details";
    $result = $conn->query($query);

    if ($result) {
        $tireCodes = array();
        while ($row = $result->fetch_assoc()) {
            $tireCodes[] = $row;
        }
        echo json_encode(array("status" => "success", "tireCodes" => $tireCodes));
    } else {
        throw new Exception("Error executing query");
    }
} catch (Exception $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
} finally {
    $conn->close();
}
?>