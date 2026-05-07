<?php
// Establish database connection
$conn = mysqli_connect("localhost:3306", "root", "", "task_management");

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

   // Retrieve all tire IDs, quantities, press, mold, and time_taken from the database table
$sql = "SELECT s.icode, s.tires_per_mold, s.mold_id, s.cavity_id, t.time_taken, p.erp, m.availability_date AS mold_availability, c.availability_date AS cavity_availability
FROM process s
INNER JOIN tire t ON s.icode = t.icode
INNER JOIN tobeplan p ON s.icode = p.icode
LEFT JOIN mold m ON s.mold_id = m.mold_id
LEFT JOIN cavity c ON s.cavity_id = c.cavity_id";
    $result = mysqli_query($conn, $sql);

    // Check if the ERP exists
    if (mysqli_num_rows($result) > 0) {
        // Split the tire IDs and quantities
        $tires = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $icode = $row['icode'];
            $tobe = $row['tobe'];
            $tires[] = array('icode' => $icode, 'tobe' => $tobe);
        }

        // Get the latest completion date among existing production plans
        $latest_end_date = null;
        $sql = "SELECT end_date
                FROM process";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if ($row['latest_end_date']) {
            $latest_end_date = $row['latest_end_date'];
        }

        $next_start_date = $latest_end_date ? date("Y-m-d H:i:s", strtotime("$latest_end_date + 1 minute")) : date("Y-m-d H:i:s");

        // Iterate over each tire in the ERP
        foreach ($tires as $tire) {
            $icode = $tire['icode'];
            $tobe = $tire['tobe'];

            // Retrieve the time taken for the tire type
            $sql = "SELECT time_taken
                    FROM tire
                    WHERE icode = '$icode'";
            $result2 = mysqli_query($conn, $sql);

            if (!$result2) {
                die("Query failed: " . mysqli_error($conn));
            }

            $row2 = mysqli_fetch_assoc($result2);

         // Retrieve the time taken for the tire type
         $time_taken = $tire['time_taken'];

         // Retrieve the latest end date for the current press
         $latest_end_date = isset($latest_end_dates[$press]) ? $latest_end_dates[$press] : null;

         // Calculate the start date based on the latest end date of the previous tire type or the current time
         $start_date = $latest_end_date ? date("Y-m-d H:i:s", strtotime("$latest_end_date + 1 minute")) : date("Y-m-d H:i:s");

         // Calculate the total time for all tires in the ERP
         $total_time = $time_taken * $tobe;

         // Calculate the end date based on the total time
         $end_date = date("Y-m-d H:i:s", strtotime("$start_date + $total_time minutes"));

         // Update the next start date for the next tire type of the same press
         $latest_end_dates[$press] = $end_date;

            $result3 = mysqli_query($conn, $sql);

            if (!$result3) {
                die("Query failed: " . mysqli_error($conn));
            }

            $row3 = mysqli_fetch_assoc($result3);

            if ($row3) {
                $press_id = $row3['press_id'];
                $press_name = $row3['press_name'];
                $mold_id = $row3['mold_id'];
                $mold_name = $row3['mold_name'];

                // Update the next start date for the next tire
                $next_start_date = $end_date;

                // Insert the production plan into the database for the entire quantity
                $sql = "INSERT INTO plannew (erp, icode, press_id, press_name, mold_id, mold_name, start_date, end_date)
                        VALUES ('$erp', '$icode', '$press_id', '$press_name', '$mold_id', '$mold_name', '$start_date', '$end_date')";
                mysqli_query($conn, $sql);

                // Get the ID of the inserted production plan
                $production_plan_id = mysqli_insert_id($conn);

                // Update the production plan with the corresponding erp_id and tire_id
                $sql = "UPDATE production_plan
                        SET erp = '$erp', icode = '$icode'
                        WHERE production_plan_id = '$production_plan_id'";
                mysqli_query($conn, $sql);

                // Update the availability of the assigned press and mold
              
                $sql = "UPDATE mold
                        SET availability_date = '$end_date'
                        WHERE mold_id = '$mold_id'";
                mysqli_query($conn, $sql);
            }
        }

        echo "Production plan generated successfully!";
    } else {
        echo "No tires found for the provided ERP ID.";
    }
    header("Location: plan_details.php?erp=" . urlencode($erp));
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
