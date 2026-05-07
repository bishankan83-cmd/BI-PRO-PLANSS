<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);


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
    }
} else {
    echo "0 results";
}

// Close the database connection
$conn->close();