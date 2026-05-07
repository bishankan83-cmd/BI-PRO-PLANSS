<!DOCTYPE html>
<html>
<head>
    <title>Task Management</title>
    <style>
        /* CSS styles (unchanged) */
    </style>
</head>
<body>
    <h1>Task Management</h1>
    <?php
    // Replace with your MySQL database credentials
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Move the $dataByDates array initialization outside the if ($result->num_rows > 0) block
    $dataByDates = array();

    // SQL query to select data from the plannew table
    $sql = "SELECT * FROM plannew";

    // Execute the query
    $result = $conn->query($sql);

    // Check if there are any rows returned
    if ($result->num_rows > 0) {
        // Loop through each row of the result
        while ($row = $result->fetch_assoc()) {
            // Get the start_date and end_date for the current row
            $startDate = strtotime($row['start_date']); // Includes the time portion from the start_date
            $endDate = strtotime($row['end_date']);

            // Get the start time from the start_date
            $startTime = date('H:i:s', $startDate);

            // Determine if it's the first date
            $isFirstDate = true;

            // Loop through each date between start_date and end_date (inclusive)
            for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 day', $date)) {
                $currentDate = date('Y-m-d', $date);

                // For the first date, use the start time from the start_date; for others, set the start time to 00:00:00
                $startTime = $isFirstDate ? date('H:i:s', $startDate) : '00:00:00';

                // Store the distribution information for this date in an array
                $distributionInfo = array(
                    'erp' => $row['erp'],
                    'plan_id' => $row['plan_id'],
                    'icode' => $row['icode'],
                    'mold_id' => $row['mold_id'],
                    'cavity_id' => $row['cavity_id'],
                    'start_date' => $currentDate . ' ' . $startTime, // Combining the date and time
                    'end_time' => '23:59:00' // Set the end time as "23:59:00" for each plan_id
                );

                // Add the distribution information to the $dataByDates array using the current date as the key
                $dataByDates[$currentDate][] = $distributionInfo;

                // For subsequent dates, set isFirstDate to false
                $isFirstDate = false;
            }
        }

        // Now, the data is divided by dates in the $dataByDates array
        // You can access the data and distribution information for each date using the date as the key

        // Reconnect to the database for table creation and data insertion
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check if the connection was successful
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Create the task_management_results table if it does not exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS task_management_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            erp VARCHAR(255),
            plan_id INT,
            icode VARCHAR(255),
            mold_id INT,
            cavity_id INT,
            start_date DATETIME,
            end_time TIME
        )";

        if ($conn->query($createTableQuery) !== TRUE) {
            echo "Error creating table: " . $conn->error;
        }

        // Insert the data into the new table
        foreach ($dataByDates as $date => $data) {
            echo "<h2>Data for date: " . $date . "</h2>";
            echo "<table>";
            echo "<tr><th>ERP</th><th>Plan ID</th><th>Icode</th><th>Mold</th><th>Cavity</th><th>Start Date</th><th>End Time</th></tr>";
            foreach ($data as $info) {
                echo "<tr>";
                // Access the updated distribution information for each date as needed
                echo "<td>" . $info['erp'] . "</td>";
                echo "<td>" . $info['plan_id'] . "</td>";
                echo "<td>" . $info['icode'] . "</td>";
                echo "<td>" . $info['mold_id'] . "</td>";
                echo "<td>" . $info['cavity_id'] . "</td>";
                echo "<td>" . $info['start_date'] . "</td>";
                echo "<td>" . $info['end_time'] . "</td>";
                echo "</tr>";

                // Insert the data into the new table
                $erp = $info['erp'];
                $plan_id = $info['plan_id'];
                $icode = $info['icode'];
                $mold_id = $info['mold_id'];
                $cavity_id = $info['cavity_id'];
                $start_date = $info['start_date'];
                $end_time = $info['end_time'];

                // Create and execute the SQL insert query
                $insertQuery = "INSERT INTO task_management_results (erp, plan_id, icode, mold_id, cavity_id, start_date, end_time) VALUES ('$erp', $plan_id, '$icode', $mold_id, $cavity_id, '$start_date', '$end_time')";
                if ($conn->query($insertQuery) !== TRUE) {
                    echo "Error inserting data: " . $conn->error;
                }
            }
            echo "</table>";
            echo "<br>";
        }

        // Close the database connection
        $conn->close();
    } else {
        echo "No data found.";
        // Close the database connection
        $conn->close();
    }
    ?>
</body>
</html>
