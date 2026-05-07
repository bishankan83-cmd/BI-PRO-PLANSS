


<?php

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to delete all data from another_table
    $sql = "DELETE FROM another_table";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    echo "All data deleted successfully from another_table.";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Close the connection
$conn = null;

?>






<?php

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query with the recursive CTE and adjustments
    $sql = "
    WITH RECURSIVE DateSeries AS (
        SELECT MIN(start_date) AS date
        FROM plannew
        UNION ALL
        SELECT date + INTERVAL 1 DAY
        FROM DateSeries
        WHERE date + INTERVAL 1 DAY <= (SELECT MAX(end_date) FROM plannew)
    )
    SELECT 
        (ds.date) AS date,
        CASE 
            WHEN DATE(ds.date) = DATE(pn.end_date) THEN pn.end_date
            ELSE DATE_ADD(DATE(ds.date + INTERVAL 1 DAY), INTERVAL 7 HOUR)
        END AS last_time,
        DATE(
            CASE 
                WHEN DATE(ds.date) = DATE(pn.end_date) THEN pn.end_date
                ELSE DATE_ADD(DATE(ds.date + INTERVAL 1 DAY), INTERVAL 7 HOUR)
            END
        ) AS last_time_date_only,
        (pn.start_date) AS start_date,
        (pn.end_date) AS end_date,
        pn.id,
        pn.icode,
        pn.start_date AS plan_start_date,
        pn.end_date AS plan_end_date,
        pn.cavity_id,
        pn.mold_id
    FROM 
        DateSeries ds
    JOIN 
        plannew pn
    ON 
        ds.date BETWEEN pn.start_date AND pn.end_date
    ORDER BY 
        ds.date, pn.id;
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all rows as associative arrays
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Insert fetched data into another table
    $insertSql = "INSERT INTO another_table (date, last_time, last_time_date_only, start_date, end_date, id, icode, plan_start_date, plan_end_date, cavity_id, mold_id)
                  VALUES (:date, :last_time, :last_time_date_only, :start_date, :end_date, :id, :icode, :plan_start_date, :plan_end_date, :cavity_id, :mold_id)";
    
    $insertStmt = $conn->prepare($insertSql);

    // Begin transaction
    $conn->beginTransaction();

    foreach ($results as $row) {
        // Bind parameters for insertion
        $insertStmt->bindParam(':date', $row['date']);
        $insertStmt->bindParam(':last_time', $row['last_time']);
        $insertStmt->bindParam(':last_time_date_only', $row['last_time_date_only']);
        $insertStmt->bindParam(':start_date', $row['start_date']);
        $insertStmt->bindParam(':end_date', $row['end_date']);
        $insertStmt->bindParam(':id', $row['id']);
        $insertStmt->bindParam(':icode', $row['icode']);
        $insertStmt->bindParam(':plan_start_date', $row['plan_start_date']);
        $insertStmt->bindParam(':plan_end_date', $row['plan_end_date']);
        $insertStmt->bindParam(':cavity_id', $row['cavity_id']);
        $insertStmt->bindParam(':mold_id', $row['mold_id']);
        
        // Execute the insertion
        $insertStmt->execute();
    }

    // Commit transaction
    $conn->commit();

    echo "Data inserted successfully into another_table.";

} catch(PDOException $e) {
    // Rollback transaction on failure
    $conn->rollback();
    echo "Connection failed: " . $e->getMessage();
}

// Close the connection
$conn = null;

?>









