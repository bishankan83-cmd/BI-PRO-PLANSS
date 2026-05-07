<?php
// Database connection parameters
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

// Start a transaction
$conn->begin_transaction();

try {
    // 1. Delete all records from worder_result
    $sql_delete = "DELETE FROM `worder_summary`";
    if (!$conn->query($sql_delete)) {
        throw new Exception("Error deleting records: " . $conn->error);
    }

    // 2. Insert new records from worder_result into worder_summary, handle duplicates
    $sql_insert = "
        INSERT INTO `worder_summary` (`id`, `date`, `Customer`, `wono`, `ref`, `erp`)
        SELECT `id`, `date`, `Customer`, `wono`, `ref`, `erp`
        FROM `worder_result`
        ON DUPLICATE KEY UPDATE
            `date` = VALUES(`date`),
            `Customer` = VALUES(`Customer`),
            `wono` = VALUES(`wono`),
            `ref` = VALUES(`ref`),
            `erp` = VALUES(`erp`)
    ";
    if (!$conn->query($sql_insert)) {
        throw new Exception("Error inserting records: " . $conn->error);
    }

    // 3. Update the worder_result table with changes from worder_summary
    $sql_update = "
        UPDATE `worder_result` wr
        JOIN `worder_summary` ws
      
        SET
            wr.date = ws.date,
            wr.Customer = ws.Customer,
            wr.wono = ws.wono,
            wr.ref = ws.ref,
            wr.erp = ws.erp
    ";
    if (!$conn->query($sql_update)) {
        throw new Exception("Error updating records: " . $conn->error);
    }

    // Commit transaction
    $conn->commit();
    echo "Records updated successfully";

    // Redirect to another page
    header("Location: worder_result_edit.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    echo "Failed to update records: " . $e->getMessage();
}

// Close connection
$conn->close();
?>
