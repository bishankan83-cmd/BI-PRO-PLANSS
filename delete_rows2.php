
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

// SQL to delete all data from the table
$sql = "DELETE FROM `reject1234`";

if ($conn->query($sql) === TRUE) {
    echo "All data deleted successfully from reject1234";
} else {
    echo "Error deleting data: " . $conn->error;
}

$conn->close();
?>







<?php
// Assuming you have a database connection established already

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rowIds'])) {
    // Decode the JSON string sent from the client
    $selectedRowIds = json_decode($_POST['rowIds']);

    // Validate and sanitize the received data as needed

    // Perform deletion and copy to another table in the database
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    foreach ($selectedRowIds as $rowId) {
        // Select data from the row to be deleted
        $selectSql = "SELECT * FROM template2b WHERE id = ?";
        $selectStmt = $conn->prepare($selectSql);
        $selectStmt->bind_param("i", $rowId);
        $selectStmt->execute();
        $selectResult = $selectStmt->get_result();
        $rowToDelete = $selectResult->fetch_assoc();
        $selectStmt->close();

        // Delete the row from the original table
        $deleteSql = "DELETE FROM template2b WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $rowId);

        if ($deleteStmt->execute()) {
            // Deletion successful

            // Copy the data to another table (assuming the other table is named 'another_table')
            $copySql = "INSERT INTO reject1234 (id, icode, cstock, date, shift, reason, reject) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $copyStmt = $conn->prepare($copySql);
            $copyStmt->bind_param("isissss", $rowToDelete['id'], $rowToDelete['icode'], $rowToDelete['cstock'], $rowToDelete['date'], $rowToDelete['shift'], $rowToDelete['reason'], $rowToDelete['reject']);
            $copyStmt->execute();
            $copyStmt->close();

            // You can log or handle success as needed
        } else {
            // Deletion failed
            // You can log or handle failure as needed
        }

        $deleteStmt->close();
    }

    $conn->close();

    // Send a response back to the client
    echo "Deletion and copy completed successfully";
} else {
    // Invalid request
    http_response_code(400);
    echo "Invalid request";
}
?>



<?php

// Database connection settings
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Connect to the database
    $dbh = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    
    // Set PDO to throw exceptions on error
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define the reject tables names
    $rejectTable = 'reject1234';
    $newRejectTable = 'reject123'; // New table name

    // Check if there are any records in reject1234 where reject column is not 'AGrade'
    $selectQuery = "SELECT * FROM $rejectTable WHERE reject != 'AGrade'";
    $stmt = $dbh->prepare($selectQuery);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
        // If records found, insert them into the new table
        foreach ($rows as $row) {
            // Check if the primary key already exists in the new table
            $checkQuery = "SELECT COUNT(*) AS count FROM $newRejectTable WHERE id = :id";
            $checkStmt = $dbh->prepare($checkQuery);
            $checkStmt->execute([':id' => $row['id']]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] == 0) {
                // If the primary key does not exist, proceed with insertion
                $insertQuery = "INSERT INTO $newRejectTable (icode, cstock, date, shift, reason, reject) 
                                VALUES (:icode, :cstock, :date, :shift, :reason, :reject)";
                
                $stmt = $dbh->prepare($insertQuery);
                $stmt->execute([
             
                    ':icode' => $row['icode'],
                    ':cstock' => $row['cstock'],
                    ':date' => $row['date'],
                    ':shift' => $row['shift'],
                    ':reason' => $row['reason'],
                    ':reject' => $row['reject']
                ]);
            } else {
                // If the primary key already exists, skip insertion or handle it as needed
                echo "Primary key already exists for id: " . $row['id'] . ". Skipping insertion.";
            }
        }

        echo "Records inserted into $newRejectTable successfully.";
    } else {
        echo "No records found with reject != 'AGrade' in $rejectTable.";
    }

    // Define the reject table and realstock table names
    $rejectTable = 'reject1234';
    $realstockTable = 'realstock';

    // Update cstock in the realstock table where reject column in reject1234 is 'AGrade'
    $updateQuery = "UPDATE $realstockTable r
                    JOIN $rejectTable t ON r.icode = t.icode
                    SET r.cstock = r.cstock + t.cstock
                    WHERE t.reject = 'AGrade'";
    
    // Execute the update query
    $stmt = $dbh->prepare($updateQuery);
    $stmt->execute();

    echo "Records updated successfully.";

} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$dbh = null;

?>
