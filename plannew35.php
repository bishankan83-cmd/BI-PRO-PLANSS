<?php
// Establish database connection
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the ERP ID from the form submission
    $erp = isset($_POST['erp']) ? $_POST['erp'] : '';

    // Validate the ERP ID (you can add your own validation logic here)
    if (empty($erp)) {
        die("Please enter a valid ERP ID");
    }

    // Sanitize the ERP ID to prevent SQL injection
    $erp = mysqli_real_escape_string($conn, $erp);

    // Generate Production Plan

    // Retrieve the tire IDs and quantities for the ERP, excluding negative quantities
    $sql = "SELECT wt.icode, wt.tobe, t.description , t.time_taken, m.availability_date
    FROM tobeplan wt
    INNER JOIN tire t ON wt.icode = t.icode
    INNER JOIN tire_mold tm ON t.icode = tm.icode
    INNER JOIN mold m ON tm.mold_id = m.mold_id
    WHERE wt.erp = '$erp' AND wt.tobe >= 0"; // Exclude negative quantities
$result = mysqli_query($conn, $sql);
    // Check if the query executed successfully
    if ($result) {
        // Check if the ERP exists
        if (mysqli_num_rows($result) > 0) {
            // Split the tire IDs and quantities
            $tires = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $icode = $row['icode'];
                $tobe = $row['tobe'];
                $tires[] = array('icode' => $icode, 'tobe' => $tobe);
            }

            // Get the number of molds that can be put in a press at the same time of the day from press_config table
       
            // Get the latest completion date among existing production plans
            $latest_end_date = null;
            $sql = "SELECT MAX(end_date) AS latest_end_date
                    FROM production_plan";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if ($row['latest_end_date']) {
                $latest_end_date = $row['latest_end_date'];
            }

            $next_start_date = $latest_end_date ? date("Y-m-d H:i:s", strtotime("$latest_end_date + 1 minute")) : date("Y-m-d H:i:s");

            // Initialize variable for maximum total time
            $max_total_time = 0;

            // Iterate over each tire in the ERP
            foreach ($tires as $tire) {
                $icode = $tire['icode'];
                $tobe = $tire['tobe'];

                // Retrieve the time taken for the tire type and availability date of the associated mold
                $sql = "SELECT t.time_taken, m.availability_date
                        FROM tire t
                        INNER JOIN tire_mold tm ON t.icode = tm.icode
                        INNER JOIN mold m ON tm.mold_id = m.mold_id
                        WHERE t.icode = '$icode'";
                $result2 = mysqli_query($conn, $sql);

                // Check if the query executed successfully
                if ($result2) {
                    $row2 = mysqli_fetch_assoc($result2);

                    $time_taken = $row2['time_taken'];
                    $mold_availability_date = $row2['availability_date'];

                    // Calculate the total time for all tires in the ERP
                    $total_time = $time_taken * $tobe;

                    // Update the maximum total time if necessary
                    if ($total_time > $max_total_time) {
                        $max_total_time = $total_time;
                    }

                    // Calculate the start and end dates based on the availability date of the mold and total time
                    $start_date = max($next_start_date, $mold_availability_date);
                    $end_date = date("Y-m-d H:i:s", strtotime("$start_date + $total_time minutes"));

                    // Check for available press, mold, and cavity matching the tire_id
                    $sql = "SELECT p.press_id, p.press_name, m.mold_id, m.mold_name, c.cavity_id, c.cavity_name
                    FROM press p
                    INNER JOIN mold_press mp ON p.press_id = mp.press_id
                    INNER JOIN mold m ON mp.mold_id = m.mold_id
                    INNER JOIN press_cavity pc ON p.press_id = pc.press_id
                    INNER JOIN cavity c ON pc.cavity_id = c.cavity_id
                    INNER JOIN tire_mold tm ON m.mold_id = tm.mold_id
                    INNER JOIN tire t ON tm.icode = t.icode
                    WHERE p.is_available = 1 AND m.is_available = 1 AND c.is_available = 1 AND t.icode = '$icode' AND (t.cuing_group_id = 0 OR t.cuing_group_id = (SELECT cuing_group_id FROM tire WHERE icode = '$icode'))";
                    $result3 = mysqli_query($conn, $sql);

                    // Check if the query executed successfully
                    if ($result3) {
                        // Iterate over each press
                        while ($row3 = mysqli_fetch_assoc($result3)) {
                            $press_id = $row3['press_id'];
                            $press_name = $row3['press_name'];
                            $molds_per_press = $press_config[$press_id];
                            $mold_ids = explode(',', $row3['mold_ids']);
                            $mold_names = explode(',', $row3['mold_names']);
                            $cavity_ids = explode(',', $row3['cavity_ids']);
                            $cavity_names = explode(',', $row3['cavity_names']);

                            // Limit the number of molds used in a press to the configured value
                            $mold_count = min($molds_per_press, count($mold_ids)); // Number of molds to use in the press

                            // Update the next start date for the next tire
                            $next_start_date = $end_date;

                            // Iterate over each mold and cavity combination
                            for ($i = 0; $i < $mold_count; $i++) {
                                $mold_id = $mold_ids[$i];
                                $mold_name = $mold_names[$i];
                                $cavity_id = $cavity_ids[$i];
                                $cavity_name = $cavity_names[$i];

                                // Insert the production plan into the database
                                $sql = "INSERT INTO production_plan (erp, icode, press_id, press_name, mold_id, mold_name, cavity_id, cavity_name, start_date, end_date)
                                        VALUES ('$erp', '$icode', '$press_id', '$press_name', '$mold_id', '$mold_name', '$cavity_id', '$cavity_name', '$start_date', '$end_date')";
                                mysqli_query($conn, $sql);
                            }
                        }
                    } else {
                        echo "Error: " . mysqli_error($conn);
                    }
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            }

            // Output the maximum total time
            echo "Maximum total time: " . $max_total_time . " minutes";
        } else {
            echo "No tires found for the given ERP ID";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Production Plan Generator</title>
</head>

<body>
    <h2>Production Plan Generator</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="erp">ERP ID:</label>
        <input type="text" id="erp" name="erp" required>
        <button type="submit">Generate Plan</button>
    </form>
</body>

</html>  