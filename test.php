<?php
// MySQL database credentials
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

// Function to run SQL queries and handle errors
function runQuery($conn, $sql, $errorMessage)
{
    $result = $conn->query($sql);
    if (!$result) {
        die($errorMessage . ": " . $conn->error);
    }
    return $result;
}

// Part 1: First SQL Query
$sql1 = "
    SELECT `icode`, GROUP_CONCAT(`cavity_id` ORDER BY `plan_id` ASC) AS `matching_cavity_ids`
    FROM `production_plan`
    GROUP BY `icode`
    ORDER BY `plan_id`;
";

$result1 = runQuery($conn, $sql1, "Error executing the first SQL query");

if ($result1->num_rows > 0) {
    // Open a new connection for inserting data into a separate table
    $insertConn = new mysqli($servername, $username, $password, $dbname);
    if ($insertConn->connect_error) {
        die("Insertion connection failed: " . $insertConn->connect_error);
    }

    while ($row = $result1->fetch_assoc()) {
        $icode = $row["icode"];
        $matchingCavityIds = explode(',', $row["matching_cavity_ids"]);

        foreach ($matchingCavityIds as $cavityId) {
            $insertSql = "INSERT INTO tire_cavity (icode, cavity_id) VALUES ('$icode', '$cavityId')";
            if ($insertConn->query($insertSql) !== true) {
                echo "Error inserting data: " . $insertConn->error;
            }
        }
    }

    // Close the insertion connection
    $insertConn->close();
} else {
    echo "No results found.";
}

// Part 2: Second SQL Query
$sql2 = "SELECT icode, GROUP_CONCAT(DISTINCT mold_id) AS mold_ids
        FROM production_plan
        GROUP BY icode";

$result2 = runQuery($conn, $sql2, "Error executing the second SQL query");

if ($result2->num_rows > 0) {
    // Prepare the INSERT statement for the tire_molddd table
    $insertSql = "INSERT INTO tire_molddd (icode, mold_id) VALUES ";

    // Loop through the result and build the VALUES part of the INSERT statement
    $values = [];
    while ($row = $result2->fetch_assoc()) {
        $icode = $row["icode"];
        $moldIds = $row["mold_ids"];

        // Split the mold_ids into an array
        $moldIdsArray = explode(",", $moldIds);

        // Remove any duplicate mold_ids
        $uniqueMoldIds = array_unique($moldIdsArray);

        // Build the values string for each unique mold_id
        foreach ($uniqueMoldIds as $moldId) {
            $values[] = "('$icode', '$moldId')";
        }
    }

    // Combine all the values into a single string
    $valuesString = implode(", ", $values);

    // Complete the INSERT statement
    $insertSql .= $valuesString;

    // Execute the INSERT statement
    if ($conn->query($insertSql) === true) {
        echo "Data inserted into tire_molddd successfully.";
    } else {
        echo "Error inserting data into tire_molddd: " . $conn->error;
    }
} else {
    echo "No data found in the production_plan table.";
}

// Part 3: Third SQL Query
$sql3 = "
    SELECT tp.icode, tp.tobe, ti.time_taken, tm.mold_id, m.availability_date AS mold_avail_date, tc.cavity_id, c.availability_date AS cavity_avail_date
    FROM tobeplan tp
    JOIN tire ti ON tp.icode = ti.icode
    JOIN tire_molddd tm ON tp.icode = tm.icode
    JOIN mold m ON tm.mold_id = m.mold_id
    JOIN tire_cavity tc ON tp.icode = tc.icode
    JOIN cavity c ON tc.cavity_id = c.cavity_id
    ORDER BY tc.id"; // Change "tc.id" to the appropriate column name

$result3 = runQuery($conn, $sql3, "Error executing the third SQL query");

// Define the $tires array to fix the error
$tires = array();

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

    if ($conn->query($insert_sql) === TRUE) {
        echo "Data inserted into quick_plan successfully.";
    } else {
        echo "Error inserting data into quick_plan: " . $conn->error;
    }
} else {
    echo "No data to insert into quick_plan.";
}

// Close the database connection
$conn->close();

//header("Location: quick_update19.php");
//exit();
?>
