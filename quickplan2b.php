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

try {
// Set a longer timeout for the MySQL connection (e.g., 5 minutes)
mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 3);

// Retrieve the data from the necessary tables
$sql = "
    SELECT tp.icode, tp.tobe, ti.time_taken, tm.mold_id, m.availability_date AS mold_avail_date, tc.cavity_id, c.availability_date AS cavity_avail_date,
    c.cavity_group_id, ig.icode_group_id
    FROM tobeplan tp
    JOIN tire ti ON tp.icode = ti.icode
    JOIN tire_molddd tm ON tp.icode = tm.icode
    JOIN mold m ON tm.mold_id = m.mold_id
    JOIN tire_cavity tc ON tp.icode = tc.icode
    JOIN cavity c ON tc.cavity_id = c.cavity_id
    JOIN cavity_group cg ON c.cavity_group_id = cg.cavity_group_id
    JOIN icode_group ig ON tp.icode = ig.icode
    ORDER BY tc.id;
";
$result = $conn->query($sql);

// Handle query execution error
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create an array to store the tire information
$tires = array();

// Additional associative arrays to track cavity group availability and icode group mappings
$cavity_group_availability = array();
$icode_group_mapping = array();

// Process the retrieved data
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tire = array(
            'icode' => $row['icode'],
            'tobe' => $row['tobe'],
            'time_taken' => $row['time_taken'],
            'mold_id' => $row['mold_id'],
            'mold_avail_date' => $row['mold_avail_date'],
            'cavity_id' => $row['cavity_id'],
            'cavity_avail_date' => $row['cavity_avail_date']
        );

        // Check if the current cavity group is available on the same day as the icode group
        if (
            isset($cavity_group_availability[$row['cavity_group_id']])
            && $cavity_group_availability[$row['cavity_group_id']] != $tire['mold_avail_date']
        ) {
            continue; // Skip this tire if cavity group is not available on the same day
        }

        // Check if the icode group is already mapped to a cavity group
        if (!isset($icode_group_mapping[$row['icode_group_id']])) {
            $icode_group_mapping[$row['icode_group_id']] = $row['cavity_group_id'];
        }

        // Check if the mapped cavity group matches the current cavity's group
        if ($icode_group_mapping[$row['icode_group_id']] != $row['cavity_group_id']) {
            continue; // Skip this tire if cavity group doesn't match the icode group's mapping
        }

        $tires[] = $tire;

        // Update cavity group availability if it's the first time encountered
        if (!isset($cavity_group_availability[$row['cavity_group_id']])) {
            $cavity_group_availability[$row['cavity_group_id']] = $tire['cavity_avail_date'];
        }
    }
}

// Sort the tires based on the availability date of the molds and cavities in ascending order
usort($tires, function ($a, $b) {
    $moldDateComparison = strtotime($a['mold_avail_date']) <=> strtotime($b['mold_avail_date']);
    if ($moldDateComparison === 0) {
        // If mold dates are equal, compare based on cavity dates
        return strtotime($a['cavity_avail_date']) <=> strtotime($b['cavity_avail_date']);
    }
    return $moldDateComparison;
});

// Prepare the data for insertion into the quick_plan table
$quick_plan_values = '';
$production_schedule = array();
$mold_availability = array();
$cavity_availability = array();
$mold_tire_count = array();

foreach ($tires as $tire) {
    $mold_id = $tire['mold_id'];
    $cavity_id = $tire['cavity_id'];

    $icode = $tire['icode'];

    if (
        !isset($mold_availability[$mold_id])
        && !isset($cavity_availability[$cavity_id])
        && $tire['tobe'] > 0
    ) {
        $mold_availability[$mold_id] = $tire['mold_avail_date'];
        $cavity_availability[$cavity_id] = $tire['cavity_avail_date'];

        $production_schedule[] = $tire;

        // Reduce the amount of tires to be made for the corresponding icode
        $tire['tobe']--;

        // Track the number of tires processed by each mold_id
        if (!isset($mold_tire_count[$mold_id])) {
            $mold_tire_count[$mold_id] = 0;
        }
        $mold_tire_count[$mold_id]++;

        // Prepare the values for the quick_plan table insertion
        $quick_plan_values .= "('" . $tire['icode'] . "', '" . $mold_id . "', '" . $cavity_id . "'),";
    }
}

// Remove the trailing comma from the values
$quick_plan_values = rtrim($quick_plan_values, ',');

// Insert the values into the quick_plan table
if (!empty($quick_plan_values)) {
    $insert_sql = "INSERT INTO quick_plan2 (icode, mold_id, cavity_id) VALUES " . $quick_plan_values;

    if ($conn->query($insert_sql) === TRUE) {
        echo "Data inserted into the quick_plan table successfully.";
    } else {
       echo "Error inserting data into the quick_plan table: " . $conn->error;
    }
}

// Delete all data from the tobeplannew1 table
$delete_sql = "DELETE FROM tobeplannew1";

if ($conn->query($delete_sql) === TRUE) {
    echo "Data deleted from the tobeplannew1 table successfully";
} else {
    echo "Error deleting data from the tobeplannew1 table: $conn->error";
}

// Close the database connection
$conn->close();

header("Location: quicktobe.php");
exit();
} catch (mysqli_sql_exception $ex) {
    // Handle the database error gracefully
    //echo "An error occurred while processing your request. Please try again later.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Data</title>
</head>
<body>
    

    <p><a href="delete_data.php">NEXT</a></p>
</body>
</html>