<?php
// Database connection parameters
$host = 'localhost';
$dbName = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create a new PDO instance
$pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);

// Fetch data from the new_table
$query = "SELECT id, icode FROM new_table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$newTableData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Iterate through each row and calculate the time taken
foreach ($newTableData as $row) {
    $id = $row['id'];
    $icode = $row['icode'];

    // Fetch the creation time from the tire table
    $query = "SELECT time_taken FROM tire WHERE icode = :icode";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':icode', $icode, PDO::PARAM_STR);
    $stmt->execute();
    $tireData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tireData) {
        $creationTime = $tireData['time_taken'];

        // Update the creation time in the new_table
        $updateQuery = "UPDATE new_table SET creation_time = :creationTime WHERE id = :id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':creationTime', $creationTime, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->execute();

    } else {
        echo "ID: $id, iCode: $icode, Creation Time: Not Found\n";
    }
}

// Close the database connection
$pdo = null;
header("Location: countdaily2R.php");
exit();
?>
