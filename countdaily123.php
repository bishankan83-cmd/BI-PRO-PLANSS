<?php
// Database connection parameters
$host = 'localhost';
$dbName = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create a new PDO instance
$pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);

// Fetch data from the new_table
$query = "SELECT id, icode, plan_id FROM new_table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$newTableData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Iterate through each row and update cavity_id and mold_id
foreach ($newTableData as $row) {
    $id = $row['id'];
    $icode = $row['icode'];
    $planId = $row['plan_id'];

    // Fetch the cavity_id and mold_id from the plannew table
    $planQuery = "SELECT cavity_id, mold_id FROM plannew WHERE plan_id = :planId";
    $planStmt = $pdo->prepare($planQuery);
    $planStmt->bindParam(':planId', $planId, PDO::PARAM_INT);
    $planStmt->execute();
    $planData = $planStmt->fetch(PDO::FETCH_ASSOC);

    if ($planData) {
        $cavityId = $planData['cavity_id'];
        $moldId = $planData['mold_id'];

        // Update the cavity_id and mold_id in the new_table
        $updateQuery = "UPDATE new_table SET cavity_id = :cavityId, mold_id = :moldId WHERE id = :id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':cavityId', $cavityId, PDO::PARAM_INT);
        $updateStmt->bindParam(':moldId', $moldId, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->execute();

        
    } else {
        echo "ID: $id, iCode: $icode, Cavity ID and Mold ID: Not Found\n";
    }
}

// Close the database connection
$pdo = null;
header("Location: countdaily2.php");
exit();
?>
