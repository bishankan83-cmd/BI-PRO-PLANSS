



<?php
// Establish database connection
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Delete existing records in the plannew1 table
$deleteQuery = "DELETE FROM plannew1";
$conn->query($deleteQuery);

$deleteQuery = "DELETE FROM plannew";
$conn->query($deleteQuery);




// Retrieve all tire IDs, quantities, press, mold, and time_taken from the database table ordered by start_date
$sql = "SELECT s.id, s.icode, s.tires_per_mold, s.mold_id, s.cavity_id, t.time_taken, m.availability_date AS mold_availability, c.availability_date AS cavity_availability, s.start_date AS process_start_date, s.erp
        FROM process s
        INNER JOIN tire t ON s.icode = t.icode
        LEFT JOIN mold m ON s.mold_id = m.mold_id
        LEFT JOIN cavity c ON s.cavity_id = c.cavity_id
        WHERE s.tires_per_mold > 0
        ORDER BY s.id ASC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Check if any tires are available for production
if (mysqli_num_rows($result) > 0) {
    // Initialize array to store the latest end date for each press, mold, and cavity
    $latest_end_dates = array();

    // Iterate over each tire in the database table
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['id'];
        $icode = $row['icode'];
        $tobe = $row['tires_per_mold'];
        $mold = $row['mold_id'];
        $cavity = $row['cavity_id'];
        $time_taken = $row['time_taken'];
        $mold_availability = $row['mold_availability'];
        $cavity_availability = $row['cavity_availability'];
        $process_start_date = $row['process_start_date'];
        $erp_number = $row['erp'];
        
        // Fetch the start_date from old_process where icode and erp match with the current row
        $sql_old_process = "SELECT start_date FROM new_process WHERE icode = '$icode' AND erp = '$erp_number'";
        $result_old_process = mysqli_query($conn, $sql_old_process);
        
        if (!$result_old_process) {
            die("Query failed: " . mysqli_error($conn));
        }

        $old_process_start_date = null;
        if (mysqli_num_rows($result_old_process) > 0) {
            $old_process_row = mysqli_fetch_assoc($result_old_process);
            $old_process_start_date = $old_process_row['start_date'];
        }

        // Skip the tire if the 'tobe' value is 0
        if ($tobe == 0) {
            continue;
        }

        // Calculate the start date based on the latest end date of the previous tire type or the current process start date or old process start date,
        // taking into account the availability_date of the icode
        $start_date = max(
            $latest_end_dates[$mold] ?? $mold_availability,
            $latest_end_dates[$cavity] ?? $cavity_availability,
            $old_process_start_date ?: $process_start_date,
            $process_start_date
        );

        // Calculate the total time for all tires in the current iteration
        $total_time = $time_taken * $tobe;

        // Calculate the end date based on the total time
        $end_date = date("Y-m-d H:i:s", strtotime("$start_date + $total_time minutes"));

        // Update the next start dates for the next tire types of the same mold and cavity
        $latest_end_dates[$mold] = $end_date;
        $latest_end_dates[$cavity] = $end_date;

        // Insert the production plan into the database for the entire quantity, including the customer name
        $sql = "INSERT INTO plannew (id, icode, mold_id, cavity_id, start_date, end_date, erp, tires_per_mold)
                VALUES ('$id','$icode', '$mold', '$cavity', '$start_date', '$end_date', '$erp_number', '$tobe')";
        mysqli_query($conn, $sql);


        
         // Update the availability date of mold
         $sql = "UPDATE mold SET availability_date = '$end_date' WHERE mold_id = '$mold'";
         mysqli_query($conn, $sql);
 
         // Update the availability date of cavity
         $sql = "UPDATE cavity SET availability_date = '$end_date' WHERE cavity_id = '$cavity'";
         mysqli_query($conn, $sql);
         
    }

    echo "Production plan generated successfully!";
} else {
    echo "No tires found in the database.";
}
// Close the database connection     
mysqli_close($conn);

?>


<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919'; // Update this with the actual password
$targetDb = 'planatir_plann';

// Connect to source database
$sourceConn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);
if ($sourceConn->connect_error) {
    die("Source connection failed: " . $sourceConn->connect_error);
}

// Connect to target database
$targetConn = new mysqli($targetHost, $targetUser, $targetPass, $targetDb);
if ($targetConn->connect_error) {
    die("Target connection failed: " . $targetConn->connect_error);
}

// Step 1: Delete existing data in the target table
$deleteQuery = "DELETE FROM plannew";
if ($targetConn->query($deleteQuery) === TRUE) {
    echo "Existing data in target table deleted successfully.<br>";
} else {
    die("Error deleting data in target table: " . $targetConn->error);
}

// Step 2: Fetch data from source table
$selectQuery = "SELECT * FROM plannew";
$result = $sourceConn->query($selectQuery);

if ($result && $result->num_rows > 0) {
    // Prepare insert query for the target table
    while ($row = $result->fetch_assoc()) {
        $insertQuery = "INSERT INTO plannew (id, plan_id, erp, Customer, icode, description, tobe, press, press_name, mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name, start_date, end_date, tires_per_mold) 
                        VALUES (
                            '{$row['id']}', '{$row['plan_id']}', '{$row['erp']}', '{$row['Customer']}', '{$row['icode']}', '{$row['description']}', '{$row['tobe']}', '{$row['press']}', 
                            '{$row['press_name']}', '{$row['mold_id']}', '{$row['mold_name']}', '{$row['cavity_id']}', '{$row['cavity_name']}', '{$row['cuing_group_id']}', 
                            '{$row['cuing_group_name']}', '{$row['start_date']}', '{$row['end_date']}', '{$row['tires_per_mold']}'
                        )";

        if (!$targetConn->query($insertQuery)) {
            echo "Error inserting data: " . $targetConn->error . "<br>";
        }
    }
    echo "Data copied successfully.<br>";
} else {
    echo "No data found in source table.<br>";
}

// Close connections
$sourceConn->close();
$targetConn->close();
?>







<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_plann';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919'; // Update this with the actual password
$targetDb = 'planatir_task_managemen';

// Connect to source database
$sourceConn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);
if ($sourceConn->connect_error) {
    die("Source connection failed: " . $sourceConn->connect_error);
}

// Connect to target database
$targetConn = new mysqli($targetHost, $targetUser, $targetPass, $targetDb);
if ($targetConn->connect_error) {
    die("Target connection failed: " . $targetConn->connect_error);
}

// Step 1: Delete existing data in the target table
$deleteQuery = "DELETE FROM plannew";
if ($targetConn->query($deleteQuery) === TRUE) {
    echo "Existing data in target table deleted successfully.<br>";
} else {
    die("Error deleting data in target table: " . $targetConn->error);
}

// Step 2: Fetch data from source table
$selectQuery = "SELECT * FROM plannew";
$result = $sourceConn->query($selectQuery);

if ($result && $result->num_rows > 0) {
    // Prepare insert query for the target table
    while ($row = $result->fetch_assoc()) {
        $insertQuery = "INSERT INTO plannew (id, plan_id, erp, Customer, icode, description, tobe, press, press_name, mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name, start_date, end_date, tires_per_mold) 
                        VALUES (
                            '{$row['id']}', '{$row['plan_id']}', '{$row['erp']}', '{$row['Customer']}', '{$row['icode']}', '{$row['description']}', '{$row['tobe']}', '{$row['press']}', 
                            '{$row['press_name']}', '{$row['mold_id']}', '{$row['mold_name']}', '{$row['cavity_id']}', '{$row['cavity_name']}', '{$row['cuing_group_id']}', 
                            '{$row['cuing_group_name']}', '{$row['start_date']}', '{$row['end_date']}', '{$row['tires_per_mold']}'
                        )";

        if (!$targetConn->query($insertQuery)) {
            echo "Error inserting data: " . $targetConn->error . "<br>";
        }
    }
    echo "Data copied successfully.<br>";
} else {
    echo "No data found in source table.<br>";
}

// Close connections
$sourceConn->close();
$targetConn->close();


header("Location: get_process2.php");
exit();
?>

