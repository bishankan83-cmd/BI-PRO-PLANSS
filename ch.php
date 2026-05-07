<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to select the required data
    $sql = "
        SELECT
            p.id,
            p.icode,
            GROUP_CONCAT(p.mold_id) AS mold_ids,
            SUM(p.tires_per_mold) AS total_tires_per_mold,
            tp.tobe
        FROM
            process p
        JOIN
            tobeplan tp ON p.icode = tp.icode
        WHERE
            tp.tobe > 0
        GROUP BY
            p.icode
        HAVING
            total_tires_per_mold < tp.tobe;
    ";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process the results
    foreach ($results as $result) {
        // Check if the sum of tires_per_mold is less than the positive tobe value
        if ($result['total_tires_per_mold'] < $result['tobe']) {
            // Display the initial information
            echo "ID: {$result['id']}, Icode: {$result['icode']}, Total Tires Per Mold: {$result['total_tires_per_mold']}, Tobe: {$result['tobe']}<br>";

            // Calculate the value obtained when tires_per_mold is subtracted from tobe
            $calculationResult =  $result['tobe'] - $result['total_tires_per_mold'];

            // Display the calculated result
            echo "Calculation Result: $calculationResult<br>";

            // Get mold IDs as an array
            $moldIds = explode(',', $result['mold_ids']);

            // Calculate the equal division for each mold
            $equalDivision = $calculationResult / count($moldIds);

            // Update tires_per_mold values for each mold
            foreach ($moldIds as $moldId) {
                $updateMoldSql = "
                    UPDATE process
                    SET tires_per_mold = tires_per_mold + :equalDivision
                    WHERE icode = :icode AND mold_id = :moldId;
                ";

                $updateMoldStmt = $pdo->prepare($updateMoldSql);
                $updateMoldStmt->bindParam(':equalDivision', $equalDivision, PDO::PARAM_STR);
                $updateMoldStmt->bindParam(':icode', $result['icode'], PDO::PARAM_STR);
                $updateMoldStmt->bindParam(':moldId', $moldId, PDO::PARAM_INT);
                $updateMoldStmt->execute();
            }

            // Display a message indicating the update
            echo "Tires_per_mold values updated for Icode: {$result['icode']}<br>";

            // Output a separator between results
            echo "<hr>";
        } else {
            // Display a message if the sum of tires_per_mold is not less than tobe
            echo "No update needed for Icode: {$result['icode']}<br><hr>";
        }
    }

    
} catch (PDOException $e) {
    // Handle database connection errors
    echo "Connection failed: " . $e->getMessage();
}
?>

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

// SQL query to update start_date in process table
$sql = "
    UPDATE process p
    JOIN old_process op ON p.mold_id = op.mold_id
                        AND p.cavity_id = op.cavity_id
                        AND p.erp = op.erp
                        AND p.serial = op.serial
    SET p.start_date = op.start_date
";

if ($conn->query($sql) === TRUE) {
    echo "Start date updated successfully";
} else {
    echo "Error updating start date: " . $conn->error;
}

// Close connection
$conn->close();

// Redirect to another page after the processing is complete
   header("Location: sleep3.php");
   exit();

?>
