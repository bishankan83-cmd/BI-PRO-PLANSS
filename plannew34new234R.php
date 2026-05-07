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

// SQL query to update cavity_name in process table
$sql = "UPDATE `process` p
        JOIN `cavity` c ON p.`cavity_id` = c.`cavity_id`
        SET p.`cavity_name` = c.`cavity_name`";

if ($conn->query($sql) === TRUE) {
  
} else {
    echo "Error updating cavity names: " . $conn->error;
}

// Close the connection
$conn->close();

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

// Select positive values of tobe from tobeplan1
$sql_select = "SELECT icode, tobe, erp, stockonhand FROM tobeplan1 WHERE tobe > 0";
$result = $conn->query($sql_select);

// Check if any positive values are found
if ($result->num_rows > 0) {

    // Prepare and execute the SQL statement for inserting into tobeplan
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];
        $tobe = $row['tobe'];
        $erp = $row['erp'];
        $stockonhand = $row['stockonhand'];

        $sql_insert = "INSERT INTO tobeplan (icode, tobe, erp, stockonhand) VALUES ('$icode', $tobe, '$erp', $stockonhand)";

        if ($conn->query($sql_insert) === TRUE) {
            
        } else {
            echo "Error inserting record: " . $conn->error . "\n";
        }
    }
} else {
    echo "No positive values of 'tobe' found in tobeplan1\n";
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

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $selectQuery = "SELECT p.icode, p.mold_id, p.tires_per_mold, p.cavity_id, p.mold_name, p.cavity_name, p.press_name, 0 AS press_id, p.erp, '' AS serial, 0 AS is_completed, 0 AS is_highlighted, p.start_date
    FROM plannew p
    JOIN tobeplan t ON p.erp = t.erp AND p.icode = t.icode
    JOIN work_order w ON p.erp = w.erp
    WHERE t.tobe > 0
    ORDER BY w.datetime ASC";

    // Prepare and execute the SELECT query
    $stmtSelect = $conn->prepare($selectQuery);
    $stmtSelect->execute();

    // Fetch all the results as an associative array
    $result = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);

    // Your SQL query to insert data into the PROCESS table
    $insertQuery = "INSERT INTO process 
                    (`icode`, `mold_id`, `tires_per_mold`, `cavity_id`, `mold_name`, `cavity_name`, `press_name`, `press_id`, `erp`, `serial`, `is_completed`, `is_highlighted`, `start_date`)
                    VALUES 
                    (:icode, :mold_id, :tires_per_mold, :cavity_id, :mold_name, :cavity_name, :press_name, :press_id, :erp, :serial, :is_completed, :is_highlighted, :start_date)";

    // Prepare the INSERT query
    $stmtInsert = $conn->prepare($insertQuery);

    // Loop through the result set and insert each row into the PROCESS table
    foreach ($result as $row) {
        $stmtInsert->execute($row);
    }

   

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;


header("Location: cutR.php");
exit();
?>

