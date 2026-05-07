+<?php
// Establish database connection
$conn = mysqli_connect("localhost:3306", "root", "", "bisnew3");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Generate Production Plan
if (isset($_POST['work_order_ids'])) {
    $work_order_ids = $_POST['work_order_ids'];

    // Split the work order IDs into an array
    $work_order_ids = explode(",", $work_order_ids);

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
            $row2 = mysqli_fetch_assoc($result2);
            $time_taken = $row2['time_taken'];

            

            // Check for available press and mold matching the tire_id
            $sql = "SELECT p.press_id, p.press_name, m.mold_id, m.mold_name, pc.cavity_id
            FROM press p
            INNER JOIN mold m ON p.mold_id = m.mold_id
            INNER JOIN tire t ON m.tire_id = t.tire_id
            INNER JOIN press_cavity pc ON p.press_id = pc.press_id AND m.mold_id = pc.mold_id
            WHERE p.is_available = 1 AND t.tire_id = $tire_id AND pc.is_available = 1
            LIMIT 1";
    
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
                $cavity_id = $row3['cavity_id'];
                $cavity_name = $row3['cavity_name'];

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
                        WHERE press_id = $press_id";
                mysqli_query($conn, $sql);

                // Update the availability of the assigned mold
                $sql = "UPDATE mold
                        SET availability_date = '$end_date
                        WHERE mold_id = $mold_id";
                mysqli_query($conn, $sql);

                // Update the availability of the assigned cavity
                $sql = "UPDATE press_cavity
                        SET available_date = '$end_date'
                        WHERE cavity_id = $cavity_id";
                mysqli_query($conn, $sql);
            }
        }
    }
}
?>










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

                // Insert the production plan into the database for the entire quantity
                $sql = "INSERT INTO plannew (erp, icode, press, mold, cavity, start_date, end_date)
                        VALUES ('$erp', '$icode', '$press', '$mold', '$cavity', '$start_date', '$end_date')";
                mysqli_query($conn, $sql);

                // Get the ID of the inserted production plan
                $production_plan_id = mysqli_insert_id($conn);

