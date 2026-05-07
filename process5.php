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

// Query to get the number of molds for each icode from quick_plan table
$moldQuery = "SELECT icode, COUNT(DISTINCT mold_id) AS mold_count FROM quick_plan GROUP BY icode";
$moldResult = $conn->query($moldQuery);

// Check if the mold query was successful
if ($moldResult) {
    // Fetch the results as an associative array
    while ($row = $moldResult->fetch_assoc()) {
        $icode = $row['icode'];
        $moldCount = $row['mold_count'];

        // Query to get the number of tobes for each icode from tobeplan_plan table
        $tobeQuery = "SELECT tobe FROM tobeplan_plan WHERE icode = '$icode'";
        $tobeResult = $conn->query($tobeQuery);

        // Check if the tobe query was successful
        if ($tobeResult) {
            // Fetch the tobe count
            $tobeRow = $tobeResult->fetch_assoc();
            $tobeCount = $tobeRow['tobe'];

            $ratio = ceil(($moldCount > 0) ? ($tobeCount / $moldCount) : 0);
            
            // Query to get mold_id and cavity_id from quick_plan
            $moldInfoQuery = "SELECT mold_id, cavity_id FROM quick_plan WHERE icode = '$icode'";
            $moldInfoResult = $conn->query($moldInfoQuery);

            if ($moldInfoResult) {
                // Fetch and insert data into the "process_plan" table
                while ($moldInfoRow = $moldInfoResult->fetch_assoc()) {
                    $moldId = $moldInfoRow['mold_id'];
                    $cavityId = $moldInfoRow['cavity_id'];

                    // Query to get cavity_name based on cavity_id
                    $cavityNameQuery = "SELECT cavity_name FROM cavity WHERE cavity_id = '$cavityId'";
                    $cavityNameResult = $conn->query($cavityNameQuery);

                    if ($cavityNameResult) {
                        $cavityNameRow = $cavityNameResult->fetch_assoc();
                        $cavityName = $cavityNameRow['cavity_name'];

                        // Query to get mold_name based on mold_id
                        $moldNameQuery = "SELECT mold_name FROM mold WHERE mold_id = '$moldId'";
                        $moldNameResult = $conn->query($moldNameQuery);

                        if ($moldNameResult) {
                            $moldNameRow = $moldNameResult->fetch_assoc();
                            $moldName = $moldNameRow['mold_name'];

                            // Insert data into the "process_plan" table
                            $insertQuery = "INSERT INTO process_plan (icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, erp, serial, is_completed) VALUES ('$icode', '$moldId', '$ratio', '$cavityId', '$moldName', '$cavityName','YourERP', 'YourSerial', 0)";

                            if ($conn->query($insertQuery) === TRUE) {
                              //  echo "Data inserted successfully!<br>";
                            } else {
                               // echo "Error inserting data: " . $conn->error . "<br>";
                            }
                        } else {
                            // Handle mold name query error
                            echo "Error: " . $moldNameQuery . "<br>" . $conn->error;
                        }

                        // Free the mold name result set
                        $moldNameResult->free();
                    } else {
                        // Handle cavity name query error
                        echo "Error: " . $cavityNameQuery . "<br>" . $conn->error;
                    }

                    // Free the cavity name result set
                    $cavityNameResult->free();
                }

                // Free the mold info result set
                $moldInfoResult->free();
            } else {
                // Handle mold info query error
                echo "Error: " . $moldInfoQuery . "<br>" . $conn->error;
            }

            // Free the tobe result set
            $tobeResult->free();
        } else {
            // Handle tobe query error
            echo "Error: " . $tobeQuery . "<br>" . $conn->error;
        }
    }

    // Free the mold result set
    $moldResult->free();
} else {
    // Handle mold query error
    echo "Error: " . $moldQuery . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>


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
    UPDATE process_plan p
    JOIN production_plan pp ON p.icode = pp.icode
    SET p.erp = pp.erp
";

if ($conn->query($sqlUpdateERP) === TRUE) {
    //echo "ERP numbers updated successfully<br>";
} else {
    //echo "Error updating ERP numbers: " . $conn->error . "<br>";
}

// SQL query to select rows from the 'process_plan' table ordered by 'icode' and 'id'
$sqlSelect = "SELECT * FROM process_plan ORDER BY icode, id";

$result = $conn->query($sqlSelect);

$current_icode = null; // Variable to keep track of the current 'icode' value
$counter = 0; // Counter for numbering rows within each group

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];

        // Check if 'icode' value has changed
        if ($icode != $current_icode) {
            // Display a header for the new group
           // echo "<h2>Group $icode</h2>";
            $current_icode = $icode;

            // Reset the counter for the new group
            $counter = 0;
        }

        // Increment the counter and display it
        $counter++;
        //echo "<p>{$counter}. {$row['mold_name']}</p>"; // Replace 'column_name' with your actual column names

        // Update the 'serial' column in the database
        $serial = $counter;
        $updateSql = "UPDATE process_plan SET serial = $serial WHERE id = {$row['id']}";
        $conn->query($updateSql);
    }
} else {
    echo "0 results";
}

// Close the database connection
$conn->close();

    //Redirect to another page after the data is inserted successfully
 header("Location: planview.php");
   exit(); 
  
 // header("Location: plannew45.php");
 // exit();
?>
</body>
</html>


