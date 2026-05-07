<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT p.plan_id, p.tires_per_mold, n.creation_time, COALESCE(r.total_id_count, 0) AS total_id_count, r.icode, p.mold_id, p.cavity_id
        FROM plannew p
        LEFT JOIN result_table r ON p.plan_id = r.id
        LEFT JOIN new_table n ON p.plan_id = n.plan_id";

$result = $conn->query($sql);

if ($result === false) {
    echo "Query error: " . $conn->error;
} elseif ($result->num_rows > 0) {
    // Create a flag array to track inserted plan_ids
    $insertedPlanIds = array();

    while ($row = $result->fetch_assoc()) {
        $id = $row["plan_id"];
        if (in_array($id, $insertedPlanIds)) {
            continue; // Skip if plan_id is already inserted
        }
        $insertedPlanIds[] = $id; // Mark this plan_id as inserted

        $tiresPerMold = $row["tires_per_mold"];
        $creationTime = $row["creation_time"];
        $totalIdCount = $row["total_id_count"];
        $icode = $row["icode"];
        $moldId = $row["mold_id"];
        $cavityId = $row["cavity_id"];
        $difference = $tiresPerMold - $totalIdCount;

        // Insert data into your_new_table
        $insertSql = "INSERT INTO new_table3 (plan_id, tires_per_mold, creation_time, total_id_count, icode, mold_id, cavity_id, difference)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("sissiisi", $id, $tiresPerMold, $creationTime, $totalIdCount, $icode, $moldId, $cavityId, $difference);

        if ($stmt->execute()) {
           // echo "Data inserted successfully for plan_id: $id<br>";
        } else {
           // echo "Error inserting data for plan_id: $id - " . $stmt->error;
        }
    }
} else {
    echo "No results found.";
}

// Close the connection
$conn->close();
header("Location: idcount3.php");
exit();
?>


