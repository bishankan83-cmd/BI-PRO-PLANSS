<?php


// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 'On');
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



$sql = "
    SELECT tp.icode, tp.tobe, ti.time_taken, tm.mold_id, m.availability_date AS mold_avail_date, tc.cavity_id, c.availability_date AS cavity_avail_date
    FROM tobeplan_plan tp
    JOIN tire ti ON tp.icode = ti.icode
    JOIN tire_molddd tm ON tp.icode = tm.icode
    JOIN mold m ON tm.mold_id = m.mold_id
    JOIN tire_cavity tc ON tp.icode = tc.icode
    JOIN cavity c ON tc.cavity_id = c.cavity_id
    ORDER BY tc.id"; // Change "tc.id" to the appropriate column name

$result = $conn->query($sql);

// Handle query execution error
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create an array to store the tire information
$tires = array();

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

        $tires[] = $tire;
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
    $insert_sql = "INSERT INTO quick_plan (icode, mold_id, cavity_id) VALUES " . $quick_plan_values;

   
}

// Close the database connection
$conn->close();

header("Location: quick_update19.php");
exit();
?>  