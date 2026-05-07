<?php
// Establish database connection
$conn = mysqli_connect("localhost:3306", "root", "", "bisnew6");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Generate Production Plan
if (isset($_POST['work_order_ids'])) {
    $work_order_ids = $_POST['work_order_ids'];

    // Split the work order IDs into an array
    $work_order_ids = explode(",", $work_order_ids);

    $previous_end_date = null; // Initialize the variable
    $available_press_ids = array(); // Initialize the array
    $next_start_date = null; // Initialize the variable for the next tire start date

    // Iterate over each work order ID
    foreach ($work_order_ids as $work_order_id) {
        // Retrieve the tire IDs and quantities for the work order
        $sql = "SELECT wot.tire_id, wot.quantity
                FROM work_order_tire wot
                INNER JOIN work_order wo ON wot.work_order_id = wo.work_order_id
                WHERE wot.work_order_id = $work_order_id";
        $result = mysqli_query($conn, $sql);

        // Iterate over each tire ID and quantity
        while ($row = mysqli_fetch_assoc($result)) {
            $tire_id = $row['tire_id'];
            $quantity = $row['quantity'];

            // Retrieve the time taken for the tire type
            $sql = "SELECT time_taken
                    FROM tire
                    WHERE tire_id = $tire_id";
            $result2 = mysqli_query($conn, $sql);

            if (!$result2) {
                die("Query failed: " . mysqli_error($conn));
            }

            $row2 = mysqli_fetch_assoc($result2);

            $time_taken = $row2['time_taken'];

            // Calculate the total time for all tires in the work order
            $total_time = $time_taken * $quantity;

            // Calculate the start and end dates based on the total time
            $start_date = $previous_end_date ? $previous_end_date : date("Y-m-d H:i:s");
            $end_date = date("Y-m-d H:i:s", strtotime("$start_date + $total_time minutes"));

            // Check for available press and mold matching the tire_id
            $sql = "SELECT p.press_id, p.press_name, m.mold_id, m.mold_name, pc.capacity
                    FROM press p
                    INNER JOIN mold_press mp ON p.press_id = mp.press_id
                    INNER JOIN mold m ON mp.mold_id = m.mold_id
                    INNER JOIN tire_mold tm ON m.mold_id = tm.mold_id
                    INNER JOIN tire t ON tm.tire_id = t.tire_id
                    INNER JOIN press_capacity pc ON p.press_id = pc.press_id
                    WHERE p.is_available = 1 AND m.is_available = 1 AND t.tire_id = $tire_id";
            
            // Add condition to match available presses at the end of previous work orders
            if (!empty($available_press_ids)) {
                $press_ids_str = implode(",", $available_press_ids);
                $sql .= " AND p.press_id IN ($press_ids_str)";
            }

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
                $max_molds = $row3['capacity'];

                // Check for available cavities matching the mold_id
                $sql = "SELECT c.cavity_id, c.cavity_name
                        FROM cavity c
                        INNER JOIN mold_cavity mc ON c.cavity_id = mc.cavity_id
                        WHERE c.is_available = 1 AND mc.mold_id = $mold_id
                        LIMIT $max_molds";

                $result4 = mysqli_query($conn, $sql);

                if (!$result4) {
                    die("Query failed: " . mysqli_error($conn));
                }

                $row4 = mysqli_fetch_assoc($result4);

                if ($row4) {
                    $cavity_id = $row4['cavity_id'];
                    $cavity_name = $row4['cavity_name'];

                    // Calculate the start date for the next tire
                    $next_start_date = $end_date;

                    // Insert the production plan into the database for the entire quantity
                    $sql = "INSERT INTO production_plan (work_order_id, tire_id, press_id, press_name, mold_id, mold_name, cavity_id, cavity_name, start_date, end_date)
                            VALUES ($work_order_id, $tire_id, $press_id, '$press_name', $mold_id, '$mold_name', $cavity_id, '$cavity_name', '$start_date', '$end_date')";
                    mysqli_query($conn, $sql);

                    // Get the ID of the inserted production plan
                    $production_plan_id = mysqli_insert_id($conn);

                    // Update the production plan with the corresponding work_order_id and tire_id
                    $sql = "UPDATE production_plan
                            SET work_order_id = $work_order_id, tire_id = $tire_id
                            WHERE production_plan_id = $production_plan_id";
                    mysqli_query($conn, $sql);

                    // Update the availability of the assigned press
                    $sql = "UPDATE press
                            SET availability_date = '$end_date'
                            WHERE press_id = $press_id
                            LIMIT $max_molds";
                    mysqli_query($conn, $sql);

                    // Update the availability of the assigned mold
                    $sql = "UPDATE mold
                            SET availability_date = '$end_date'
                            WHERE mold_id = $mold_id
                            LIMIT $max_molds";
                    mysqli_query($conn, $sql);

                    // Update the availability of the assigned cavity
                    $sql = "UPDATE cavity
                            SET availability_date = '$end_date'
                            WHERE cavity_id = $cavity_id";
                    mysqli_query($conn, $sql);

                    $previous_end_date = $end_date; // Update the previous end date
                    $available_press_ids[] = $press_id; // Add press ID to the available presses array
                }
            }
        }

        // Update the start date for the next tire
        $previous_end_date = $next_start_date;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Production Planning</title>
</head>
<body>
    <h2>Generate Production Plan</h2>
    <form action="plannew32.php" method="post">
        <label for="work_order_ids">Work Order IDs (comma-separated):</label>
        <input type="text" name="work_order_ids" id="work_order_ids">
        <button type="submit">Generate Plan</button>
    </form>
</body>
</html>
