<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update ERP numbers
$sqlUpdateERP = "
    UPDATE process p
    JOIN production_plan pp ON p.icode = pp.icode
    SET p.erp = pp.erp
";

if ($conn->query($sqlUpdateERP) === TRUE) {
    echo "ERP numbers updated successfully<br>";
} else {
    echo "Error updating ERP numbers: " . $conn->error . "<br>";
}

// SQL query to select rows from the 'process' table ordered by 'icode' and 'id'
$sqlSelect = "SELECT * FROM process ORDER BY icode, id";

$result = $conn->query($sqlSelect);

$current_icode = null; // Variable to keep track of the current 'icode' value
$counter = 0; // Counter for numbering rows within each group

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];

        // Check if 'icode' value has changed
        if ($icode != $current_icode) {
            // Display a header for the new group
            echo "<h2>Group $icode</h2>";
            $current_icode = $icode;

            // Reset the counter for the new group
            $counter = 0;
        }

        // Increment the counter and display it
        $counter++;
        echo "<p>{$counter}. {$row['mold_name']}</p>"; // Replace 'column_name' with your actual column names

        // Update the 'serial' column in the database
        $serial = $counter;
        $updateSql = "UPDATE process SET serial = $serial WHERE id = {$row['id']}";
        $conn->query($updateSql);

        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        // Create a connection to the database for Code 2
        $conn2 = new mysqli($servername, $username, $password, $dbname);

        // Check connection for Code 2
        if ($conn2->connect_error) {
            die("Connection failed: " . $conn2->connect_error);
        }

        // SQL query to update the process table for Code 2
        $sql2 = "UPDATE process AS new
                INNER JOIN old_process AS old ON new.icode = old.icode AND new.erp = old.erp AND new.serial = old.serial
                SET
                  new.cavity_id = old.cavity_id,
                  new.mold_id = old.mold_id,
                  new.cavity_name = old.cavity_name,
                  new.mold_name = old.mold_name";

        if ($conn2->query($sql2) === TRUE) {
            
        } else {
           
        }

        // Close the database connection for Code 2
        $conn2->close();
    }
} else {
    echo "0 results";
}

// Close the database connection for Code 1
$conn->close();

// Redirect the user to another page after the actions are completed
header("Location: check_date.php");
exit(); // Make sure to exit to prevent further execution
?>
