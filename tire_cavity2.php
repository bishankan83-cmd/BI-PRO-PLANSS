<?php
// MySQL database credentials
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

$sql = "
    SELECT `icode`, GROUP_CONCAT(`cavity_id` ORDER BY `plan_id` ASC) AS `matching_cavity_ids`
    FROM `production_plan`
    GROUP BY `icode`
    ORDER BY `plan_id`;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Open a new connection for inserting data into a separate table
    $insertConn = new mysqli($servername, $username, $password, $dbname);
    if ($insertConn->connect_error) {
        die("Insertion connection failed: " . $insertConn->connect_error);
    }

    while ($row = $result->fetch_assoc()) {
        $icode = $row["icode"];
        $matchingCavityIds = explode(',', $row["matching_cavity_ids"]);

        foreach ($matchingCavityIds as $cavityId) {
            $insertSql = "INSERT INTO tire_cavity (icode, cavity_id) VALUES ('$icode', '$cavityId')";
            if ($insertConn->query($insertSql) !== true) {
                echo "Error inserting data: " . $insertConn->error;
            }
        }
    }

    // Close the insertion connection
    $insertConn->close();
} else {
    echo "No results found.";
}

$conn->close();

header("Location: tire_mold2.php");
exit();
?>
