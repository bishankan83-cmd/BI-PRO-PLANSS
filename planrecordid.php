<?php
// Database connection parameters
$host = 'localhost';
$user = 'planatir_task_managemen';
$pass = 'Bishan@1919';
$db = 'planatir_task_managemen';

// Create a database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Select distinct records based on the 'id' column
$sql = "SELECT * FROM tobeplan12345 GROUP BY id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $distinctRecords = array();

    // Step 2: Fetch the distinct records and store them in an array
    while ($row = $result->fetch_assoc()) {
        $distinctRecords[] = $row;
    }

    // Step 3: Delete all records from the original table
    $deleteSql = "DELETE FROM tobeplan12345";
    $conn->query($deleteSql);

    // Step 4: Insert the distinct records back into the table
    foreach ($distinctRecords as $record) {
        $insertSql = "INSERT INTO tobeplan12345 (id, icode, tobe, erp, stockonhand) 
                      VALUES ('{$record['id']}', '{$record['icode']}', '{$record['tobe']}', '{$record['erp']}', '{$record['stockonhand']}')";
        $conn->query($insertSql);
    }

    echo "Duplicates removed and distinct records inserted.";
} else {
    echo "No data found in the table.";
}

// Close the database connection
$conn->close();
header("Location: copy12345.php");
    exit;
?>
