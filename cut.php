


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

// SQL query to update start_date to today's date at 7:00 AM
$sql = "UPDATE `process` SET `start_date` = CONCAT(CURDATE(), ' 07:00:00')";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

// Close connection
$conn->close();
?>





<?php
// Database connection details
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

// SQL query to update the process table based on matching fields from the new_process table
$sql = "UPDATE process p
        JOIN new_process np
        ON p.icode = np.icode 
       
        AND p.erp = np.erp
        SET p.start_date = np.start_date, 
            p.first_tobe = np.first_tobe";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating records: " . $conn->error;
}

// Close connection
$conn->close();
?>






<?php
// Database connection
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Begin transaction
$conn->begin_transaction();

try {
    // Step 1: Delete existing data from press_selections_copy
    $clearCopySql = "DELETE FROM press_selections_copy";
    
    if (!$conn->query($clearCopySql)) {
        throw new Exception("Error clearing press_selections_copy: " . $conn->error);
    }
    echo "Press selections copy cleared successfully.<br>";

    // Step 2: Copy data from press_selections to press_selections_copy
    $copySql = "
        INSERT INTO press_selections_copy 
        SELECT * FROM press_selections
    ";
    
    if (!$conn->query($copySql)) {
        throw new Exception("Error copying to press_selections_copy: " . $conn->error);
    }
    echo "Data copied to press_selections_copy successfully.<br>";

    // Step 3: Update matching records with cavity_ids
    $updateSql = "
        UPDATE process p
        JOIN press_selections ps
           ON p.icode = ps.icode AND p.mold_id = ps.mold_id
        SET p.cavity_id = ps.cavity_ids
    ";

    if (!$conn->query($updateSql)) {
        throw new Exception("Error updating process records: " . $conn->error);
    }
    echo "Process records updated successfully.<br>";

    // Step 4: Delete records where icode matches but mold_id is different
    $deleteSql = "
        DELETE p FROM process p
        WHERE p.icode IN (SELECT DISTINCT ps.icode FROM press_selections ps)
        AND p.mold_id NOT IN (
            SELECT ps.mold_id 
            FROM press_selections ps 
            WHERE ps.icode = p.icode
        )
    ";

    if (!$conn->query($deleteSql)) {
        throw new Exception("Error deleting process records: " . $conn->error);
    }
    echo "Process records deleted successfully.<br>";

    // Step 5: Insert non-matching records from press_selections_copy to process with erp from tobeplan
    $insertSql = "
        INSERT INTO process (icode, mold_id, cavity_id, erp)
        SELECT 
            psc.icode, 
            psc.mold_id, 
            psc.cavity_ids,
            tb.erp
        FROM press_selections_copy psc
        LEFT JOIN process p 
            ON psc.icode = p.icode AND psc.mold_id = p.mold_id
        JOIN tobeplan tb 
            ON psc.icode = tb.icode
        WHERE p.icode IS NULL AND p.mold_id IS NULL
    ";

    if (!$conn->query($insertSql)) {
        throw new Exception("Error inserting records into process: " . $conn->error);
    }
    echo "Non-matching records inserted into process successfully.<br>";

    // Step 6: Delete records from process where cavity_id is NULL
    $deleteNullCavitySql = "DELETE FROM process WHERE cavity_id IS NULL";
    
    if (!$conn->query($deleteNullCavitySql)) {
        throw new Exception("Error deleting process records with NULL cavity_id: " . $conn->error);
    }
    echo "Process records with NULL cavity_id deleted successfully.<br>";

    // Step  sodium: Delete data from press_selections
    $deleteSelectionsSql = "DELETE FROM press_selections";
    
    if (!$conn->query($deleteSelectionsSql)) {
        throw new Exception("Error deleting from press_selections: " . $conn->error);
    }
    echo "Press selections cleared successfully.<br>";

    // Commit transaction
    $conn->commit();
    echo "All operations completed successfully.<br>";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Transaction failed: " . $e->getMessage();
}

// Close connection
$conn->close();
?>




<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";


try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to delete duplicates, keeping the row with the lowest id
    $sql = "
        DELETE p1 FROM process p1
        INNER JOIN process p2
        WHERE 
            p1.icode = p2.icode 
            AND p1.mold_id = p2.mold_id 
            AND p1.erp = p2.erp 
            AND p1.id > p2.id
    ";

    // Execute the query
    $pdo->exec($sql);

    // Output success message
    echo "Duplicate rows deleted successfully.";

} catch (PDOException $e) {
    // Handle errors
    echo "Error: " . $e->getMessage();
}

// Close the connection
$pdo = null;
?>







<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Establish a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the SQL statement
    $stmt = $conn->prepare("
        UPDATE process p
        JOIN tobeplan t ON p.icode = t.icode AND p.erp = t.erp
        SET p.tires_per_mold = CEIL(t.tobe / (SELECT COUNT(*) FROM process p2 WHERE p2.icode = t.icode AND p2.erp = t.erp))
        WHERE EXISTS (SELECT 1 FROM process WHERE icode = t.icode AND erp = t.erp)
    ");

    // Execute the statement
    $stmt->execute();
    
    header("Location: plannew45new2.php");
    exit();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>






