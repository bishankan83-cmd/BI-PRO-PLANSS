


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

function isHoliday($date, $conn) {
    $formattedDate = date('Y-m-d', strtotime($date));
    $query = "SELECT * FROM holidays WHERE holiday_date = '$formattedDate'";
    $result = $conn->query($query);
    return $result->num_rows > 0;
}

// Get today's date
$currentDate = new DateTime();
$currentDateStr = $currentDate->format('Y-m-d');

// Initialize sum of plan values
$sumOfPlanValues = 0;

// Initialize previous day's sum of plan values
$previousSumOfPlanValues = 0;



// Fetch data for each date
$result = $conn->query("SELECT * FROM plannew");
if ($result->num_rows > 0) {
    $groupedData = [];
    $cavityCount = [];

    echo '<table border="1">';
    
    while ($row = $result->fetch_assoc()) {
        $startDate = new DateTime($row['start_date']);
        $endDate = new DateTime($row['end_date']);

        // Skip entries before today
        if ($endDate < $currentDate) {
            continue;
        }

        $interval = new DateInterval('P1D'); // 1 day interval
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        foreach ($dateRange as $date) {
            $currentDate = $date->format('Y-m-d');
            $endDateMidnight = new DateTime($currentDate . '18:59:59'); // Set end time to 23:59:59

            // Check if the date is a holiday
            if (isHoliday($currentDate, $conn)) {
                // If it's a holiday, skip processing and move to the next day
                continue;
            }

            // Delete existing data for the current date
            $deleteQuery = "DELETE FROM calculated_data23";
            $conn->query($deleteQuery);

            // Calculate time difference between start date and end date
            $timeDifference = $startDate->diff($endDate);
            $minutesDifference = $timeDifference->days * 24 * 60 + $timeDifference->h * 60 + $timeDifference->i;

            // Calculate time difference between start date and found time
            $foundTimeDifference = $startDate->diff($endDateMidnight);
            $foundTimeMinutesDifference = $foundTimeDifference->days * 24 * 60 + $foundTimeDifference->h * 60 + $foundTimeDifference->i;

            // Fetch time_taken from tire table based on icode
            $icode = $row['icode'];
            $timeTakenQuery = $conn->query("SELECT time_taken FROM tire WHERE icode = '$icode'");
            $timeTaken = $timeTakenQuery->num_rows > 0 ? $timeTakenQuery->fetch_assoc()['time_taken'] : 'N/A';

            $groupedData[$currentDate][] = [
                'Plan ID' => $row['plan_id'],
                'erp' => $row['erp'],
                'ICode' => $icode,
                'Description' => $row['description'],
                'Mold ID' => $row['mold_id'],
                'Cavity ID' => $row['cavity_id'],
                'Start Date' => $row['start_date'],
                'End Date' => $row['end_date'], // Original end time
                'Found Time' => $endDateMidnight->format('Y-m-d H:i:s'), // Separate field with end time set to 23:59:59
                'User Time' => $endDateMidnight->format('Y-m-d 07:00:00'), // Separate field with time set to 00:00:00
                'Tires per Mold' => $row['tires_per_mold'],
                'Time Difference (Minutes)' => $minutesDifference,
                'Found Time Difference (Minutes)' => $foundTimeMinutesDifference, // New field for found time difference
                'Time Taken' => $timeTaken, // New field for time_taken from tire table
            ];

            // Count the number of different cavity IDs for each date
            $cavityCount[$currentDate][$row['cavity_id']] = 1;
        }
    }

    // Display the data for each day
    foreach ($groupedData as $date => $entries) {
        echo "<tr>";
      

        // Calculate and display plan values for each entry on this date
        foreach ($entries as $entry) {

    
            // Calculate the time difference between User Time and End Date
            $userTime = new DateTime($entry['User Time']);
            $endDate = new DateTime($entry['End Date']);
            $timeDifferenceUserToEnd = $userTime->diff($endDate);
            $minutesDifferenceUserToEnd = $timeDifferenceUserToEnd->days * 24 * 60 + $timeDifferenceUserToEnd->h * 60 + $timeDifferenceUserToEnd->i;

            // Calculate the time difference between User Time and Found Time
            $timeDifferenceUserToFound = $userTime->diff(new DateTime($entry['Found Time']));
            $minutesDifferenceUserToFound = $timeDifferenceUserToFound->days * 24 * 60 + $timeDifferenceUserToFound->h * 60 + $timeDifferenceUserToFound->i;

            // Display the time differences
            $minTimeDifference = min(
                $minutesDifferenceUserToEnd,
                $minutesDifferenceUserToFound,
                $entry['Time Difference (Minutes)'],
                $entry['Found Time Difference (Minutes)']
            );

            // Calculate the "plan" value
            $timeTaken = $entry['Time Taken'];
            $plan = round($minTimeDifference / max($timeTaken, 1));

            $insertQuery = "
            INSERT INTO calculated_data23 (
                date,
                plan_id,
                erp,
                icode,
                description,
                mold_id,
                cavity_id,
                start_date,
                end_date,
                found_time,
                user_time,
                tires_per_mold,
                time_difference_minutes,
                found_time_difference_minutes,
                time_taken,
                min_time_difference_minutes,
                time_difference_user_to_end_minutes,
                time_difference_user_to_found_minutes,
                plan,
                shift
            ) VALUES (
                '$date',
                '{$entry['Plan ID']}',
                '{$entry['erp']}',
                '{$entry['ICode']}',
                '{$entry['Description']}',
                '{$entry['Mold ID']}',
                '{$entry['Cavity ID']}',
                '{$entry['Start Date']}',
                '{$entry['End Date']}',
                '{$entry['Found Time']}',
                '{$entry['User Time']}',
                '{$entry['Tires per Mold']}',
                '{$entry['Time Difference (Minutes)']}',
                '{$entry['Found Time Difference (Minutes)']}',
                '{$entry['Time Taken']}',
                '{$minTimeDifference}',
                '{$minutesDifferenceUserToEnd}',
                '{$minutesDifferenceUserToFound}',
                '{$plan}',
                'DAY'
            )
        ";
        $conn->query($insertQuery);
        // Increment the sum of plan values

            // Increment the sum of plan values
            $sumOfPlanValues += $plan;
        }

        // Calculate the difference between consecutive days' sum of plan values
        $sumPlanDifference = round($sumOfPlanValues - $previousSumOfPlanValues);

      //  echo "<td>$sumPlanDifference</td>";

        // Update previous day's sum of plan values for the next iteration
        $previousSumOfPlanValues = $sumOfPlanValues;

        // Display the number of different cavity IDs for the current date
        $numCavityIDs = count($cavityCount[$date]);
       // echo "<td>$numCavityIDs</td>";

        // Add a new column for Total Cavity and set each value to 130
        //echo "<td>130</td>";

        // Calculate Average Utilization Percentage and round it to Sankyat
        $utilizedCavityNos = $numCavityIDs;
        $totalCavityNos = 134; // Assuming Total Cavity is always 130
        $averageUtilization = round(($utilizedCavityNos / $totalCavityNos) * 100, 0); // Round to Sankyat
        //echo "<td>$averageUtilization%</td>";

       // echo "</tr>";
    }

    echo '</table>';
} else {
    echo "No results found";
}

// Close connection
$conn->close();

?>











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

// Get today's date
$currentDate = new DateTime();
$currentDateStr = $currentDate->format('Y-m-d');

// Initialize sum of plan values
$sumOfPlanValues = 0;

// Initialize previous day's sum of plan values
$previousSumOfPlanValues = 0;


// Fetch data for each date
$result = $conn->query("SELECT * FROM plannew");
if ($result->num_rows > 0) {
    $groupedData = [];
    $cavityCount = [];

    echo '<table border="1">';
    
    while ($row = $result->fetch_assoc()) {
        $startDate = new DateTime($row['start_date']);
        $endDate = new DateTime($row['end_date']);

        // Skip entries before today
        if ($endDate < $currentDate) {
            continue;
        }

        $interval = new DateInterval('P1D'); // 1 day interval
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        foreach ($dateRange as $date) {
            $currentDate = $date->format('Y-m-d');
            $endDateMidnight = new DateTime($currentDate . '23:59:59'); // Set end time to 23:59:59

            // Check if the date is a holiday
            if (isHoliday($currentDate, $conn)) {
                // If it's a holiday, skip processing and move to the next day
                continue;
            }

         
            // Calculate time difference between start date and end date
            $timeDifference = $startDate->diff($endDate);
            $minutesDifference = $timeDifference->days * 24 * 60 + $timeDifference->h * 60 + $timeDifference->i;

            // Calculate time difference between start date and found time
            $foundTimeDifference = $startDate->diff($endDateMidnight);
            $foundTimeMinutesDifference = $foundTimeDifference->days * 24 * 60 + $foundTimeDifference->h * 60 + $foundTimeDifference->i;

            // Fetch time_taken from tire table based on icode
            $icode = $row['icode'];
            $timeTakenQuery = $conn->query("SELECT time_taken FROM tire WHERE icode = '$icode'");
            $timeTaken = $timeTakenQuery->num_rows > 0 ? $timeTakenQuery->fetch_assoc()['time_taken'] : 'N/A';

            $groupedData[$currentDate][] = [
                'Plan ID' => $row['plan_id'],
                'erp' => $row['erp'],
                'ICode' => $icode,
                'Description' => $row['description'],
                'Mold ID' => $row['mold_id'],
                'Cavity ID' => $row['cavity_id'],
                'Start Date' => $row['start_date'],
                'End Date' => $row['end_date'], // Original end time
                'Found Time' => $endDateMidnight->format('Y-m-d H:i:s'), // Separate field with end time set to 23:59:59
                'User Time' => $endDateMidnight->format('Y-m-d 18:59:59'), // Separate field with time set to 00:00:00
                'Tires per Mold' => $row['tires_per_mold'],
                'Time Difference (Minutes)' => $minutesDifference,
                'Found Time Difference (Minutes)' => $foundTimeMinutesDifference, // New field for found time difference
                'Time Taken' => $timeTaken, // New field for time_taken from tire table
            ];

            // Count the number of different cavity IDs for each date
            $cavityCount[$currentDate][$row['cavity_id']] = 1;
        }
    }

    // Display the data for each day
    foreach ($groupedData as $date => $entries) {
        echo "<tr>";
      

        // Calculate and display plan values for each entry on this date
        foreach ($entries as $entry) {

    
            // Calculate the time difference between User Time and End Date
            $userTime = new DateTime($entry['User Time']);
            $endDate = new DateTime($entry['End Date']);
            $timeDifferenceUserToEnd = $userTime->diff($endDate);
            $minutesDifferenceUserToEnd = $timeDifferenceUserToEnd->days * 24 * 60 + $timeDifferenceUserToEnd->h * 60 + $timeDifferenceUserToEnd->i;

            // Calculate the time difference between User Time and Found Time
            $timeDifferenceUserToFound = $userTime->diff(new DateTime($entry['Found Time']));
            $minutesDifferenceUserToFound = $timeDifferenceUserToFound->days * 24 * 60 + $timeDifferenceUserToFound->h * 60 + $timeDifferenceUserToFound->i;

            // Display the time differences
            $minTimeDifference = min(
                $minutesDifferenceUserToEnd,
                $minutesDifferenceUserToFound,
                $entry['Time Difference (Minutes)'],
                $entry['Found Time Difference (Minutes)']
            );

            // Calculate the "plan" value
            $timeTaken = $entry['Time Taken'];
            $plan = round($minTimeDifference / max($timeTaken, 1));

            $insertQuery = "
            INSERT INTO calculated_data23(
                date,
                plan_id,
                erp,
                icode,
                description,
                mold_id,
                cavity_id,
                start_date,
                end_date,
                found_time,
                user_time,
                tires_per_mold,
                time_difference_minutes,
                found_time_difference_minutes,
                time_taken,
                min_time_difference_minutes,
                time_difference_user_to_end_minutes,
                time_difference_user_to_found_minutes,
                plan,
                shift
            ) VALUES (
                '$date',
                '{$entry['Plan ID']}',
                '{$entry['erp']}',
                '{$entry['ICode']}',
                '{$entry['Description']}',
                '{$entry['Mold ID']}',
                '{$entry['Cavity ID']}',
                '{$entry['Start Date']}',
                '{$entry['End Date']}',
                '{$entry['Found Time']}',
                '{$entry['User Time']}',
                '{$entry['Tires per Mold']}',
                '{$entry['Time Difference (Minutes)']}',
                '{$entry['Found Time Difference (Minutes)']}',
                '{$entry['Time Taken']}',
                '{$minTimeDifference}',
                '{$minutesDifferenceUserToEnd}',
                '{$minutesDifferenceUserToFound}',
                '{$plan}',
                'NIGHT'
            )
        ";
        $conn->query($insertQuery);
        // Increment the sum of plan values

            // Increment the sum of plan values
            $sumOfPlanValues += $plan;
        }

        // Calculate the difference between consecutive days' sum of plan values
        $sumPlanDifference = round($sumOfPlanValues - $previousSumOfPlanValues);

      //  echo "<td>$sumPlanDifference</td>";

        // Update previous day's sum of plan values for the next iteration
        $previousSumOfPlanValues = $sumOfPlanValues;

        // Display the number of different cavity IDs for the current date
        $numCavityIDs = count($cavityCount[$date]);
       // echo "<td>$numCavityIDs</td>";

        // Add a new column for Total Cavity and set each value to 130
        //echo "<td>130</td>";

        // Calculate Average Utilization Percentage and round it to Sankyat
        $utilizedCavityNos = $numCavityIDs;
        $totalCavityNos = 134; // Assuming Total Cavity is always 130
        $averageUtilization = round(($utilizedCavityNos / $totalCavityNos) * 100, 0); // Round to Sankyat
        //echo "<td>$averageUtilization%</td>";

       // echo "</tr>";
    }

    echo '</table>';
} else {
    echo "No results found";
}

// Close connection
$conn->close();

?>





<?php // night //?>







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

// Get today's date
$currentDate = new DateTime();
$currentDateStr = $currentDate->format('Y-m-d');

// Initialize sum of plan values
$sumOfPlanValues = 0;

// Initialize previous day's sum of plan values
$previousSumOfPlanValues = 0;



// Fetch data for each date
$result = $conn->query("SELECT * FROM plannew");
if ($result->num_rows > 0) {
    $groupedData = [];
    $cavityCount = [];

    echo '<table border="1">';
    
    while ($row = $result->fetch_assoc()) {
        $startDate = new DateTime($row['start_date']);
        $endDate = new DateTime($row['end_date']);

        // Skip entries before today
        if ($endDate < $currentDate) {
            continue;
        }

        $interval = new DateInterval('P1D'); // 1 day interval
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        foreach ($dateRange as $date) {
            $currentDate = $date->format('Y-m-d');
            $endDateMidnight = new DateTime($currentDate . ' 07:00:00'); // Set end time to 07:00:00

            // Check if the date is a holiday
            if (isHoliday($currentDate, $conn)) {
                // If it's a holiday, skip processing and move to the next day
                continue;
            }

            // Delete existing data for the current date
            $deleteQuery = "DELETE FROM calculated_data233";
            $conn->query($deleteQuery);

            // Calculate time difference between start date and end date
            $timeDifference = $startDate->diff($endDate);
            $minutesDifference = $timeDifference->days * 24 * 60 + $timeDifference->h * 60 + $timeDifference->i;

            // Calculate time difference between start date and found time
            $foundTimeDifference = $startDate->diff($endDateMidnight);
            $foundTimeMinutesDifference = $foundTimeDifference->days * 24 * 60 + $foundTimeDifference->h * 60 + $foundTimeDifference->i;

            // Fetch time_taken from tire table based on icode
            $icode = $row['icode'];
            $timeTakenQuery = $conn->query("SELECT time_taken FROM tire WHERE icode = '$icode'");
            $timeTaken = $timeTakenQuery->num_rows > 0 ? $timeTakenQuery->fetch_assoc()['time_taken'] : 'N/A';

            $groupedData[$currentDate][] = [
                'Plan ID' => $row['plan_id'],
                'erp' => $row['erp'],
                'ICode' => $icode,
                'Description' => $row['description'],
                'Mold ID' => $row['mold_id'],
                'Cavity ID' => $row['cavity_id'],
                'Start Date' => $row['start_date'],
                'End Date' => $row['end_date'], // Original end time
                'Found Time' => $endDateMidnight->format('Y-m-d H:i:s'), // Separate field with end time set to 07:00:00
                'User Time' => $endDateMidnight->format('Y-m-d 00:00:00'), // Separate field with time set to 00:00:00
                'Tires per Mold' => $row['tires_per_mold'],
                'Time Difference (Minutes)' => $minutesDifference,
                'Found Time Difference (Minutes)' => $foundTimeMinutesDifference, // New field for found time difference
                'Time Taken' => $timeTaken, // New field for time_taken from tire table
            ];

            // Count the number of different cavity IDs for each date
            $cavityCount[$currentDate][$row['cavity_id']] = 1;
        }
    }

    // Display the data for each day
    foreach ($groupedData as $date => $entries) {
        // Calculate and display plan values for each entry on this date
        foreach ($entries as $entry) {
            // Calculate the time difference between User Time and End Date
            $userTime = new DateTime($entry['User Time']);
            $endDate = new DateTime($entry['End Date']);
            $timeDifferenceUserToEnd = $userTime->diff($endDate);
            $minutesDifferenceUserToEnd = $timeDifferenceUserToEnd->days * 24 * 60 + $timeDifferenceUserToEnd->h * 60 + $timeDifferenceUserToEnd->i;

            // Calculate the time difference between User Time and Found Time
            $timeDifferenceUserToFound = $userTime->diff(new DateTime($entry['Found Time']));
            $minutesDifferenceUserToFound = $timeDifferenceUserToFound->days * 24 * 60 + $timeDifferenceUserToFound->h * 60 + $timeDifferenceUserToFound->i;

            // Display the time differences
            $minTimeDifference = min(
                $minutesDifferenceUserToEnd,
                $minutesDifferenceUserToFound,
                $entry['Time Difference (Minutes)'],
                $entry['Found Time Difference (Minutes)']
            );

            // Calculate the "plan" value
            $timeTaken = $entry['Time Taken'];
            $plan = ($minTimeDifference / max($timeTaken, 1));

            $insertQuery = "
                INSERT INTO calculated_data233 (
                    date,
                    plan_id,
                    erp,
                    icode,
                    description,
                    mold_id,
                    cavity_id,
                    start_date,
                    end_date,
                    found_time,
                    user_time,
                    tires_per_mold,
                    time_difference_minutes,
                    found_time_difference_minutes,
                    time_taken,
                    min_time_difference_minutes,
                    time_difference_user_to_end_minutes,
                    time_difference_user_to_found_minutes,
                    plan,
                    shift
                ) VALUES (
                    '$date',
                    '{$entry['Plan ID']}',
                    '{$entry['erp']}',
                    '{$entry['ICode']}',
                    '{$entry['Description']}',
                    '{$entry['Mold ID']}',
                    '{$entry['Cavity ID']}',
                    '{$entry['Start Date']}',
                    '{$entry['End Date']}',
                    '{$entry['Found Time']}',
                    '{$entry['User Time']}',
                    '{$entry['Tires per Mold']}',
                    '{$entry['Time Difference (Minutes)']}',
                    '{$entry['Found Time Difference (Minutes)']}',
                    '{$entry['Time Taken']}',
                    '{$minTimeDifference}',
                    '{$minutesDifferenceUserToEnd}',
                    '{$minutesDifferenceUserToFound}',
                    '{$plan}',
                    'NIGHT'
                )
            ";
            $conn->query($insertQuery);

            // Increment the sum of plan values
            $sumOfPlanValues += $plan;
        }

        // Calculate the difference between consecutive days' sum of plan values
        $sumPlanDifference = round($sumOfPlanValues - $previousSumOfPlanValues);

        // Update previous day's sum of plan values for the next iteration
        $previousSumOfPlanValues = $sumOfPlanValues;

        // Display the number of different cavity IDs for the current date
        $numCavityIDs = count($cavityCount[$date]);

        // Calculate Average Utilization Percentage and round it to Sankyat
        $utilizedCavityNos = $numCavityIDs;
        $totalCavityNos = 134; // Assuming Total Cavity is always 134
        $averageUtilization = round(($utilizedCavityNos / $totalCavityNos) * 100, 0); // Round to Sankyat
    }

    echo '</table>';
} else {
    echo "No results found";
}

// Close connection
$conn->close();


?>








<?php

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "UPDATE calculated_data233 SET date = DATE_SUB(date, INTERVAL 1 DAY)";
    $pdo->exec($query);

    echo "Update successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>





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

// SQL query to insert data from calculated_data1 to calculate_data
$sql = "
    INSERT INTO calculated_data23 (
        date,
        plan_id,
        erp,
        icode,
        description,
        mold_id,
        cavity_id,
        start_date,
        end_date,
        found_time,
        user_time,
        tires_per_mold,
        time_difference_minutes,
        found_time_difference_minutes,
        time_taken,
        min_time_difference_minutes,
        time_difference_user_to_end_minutes,
        time_difference_user_to_found_minutes,
        plan,
        shift
    )
    SELECT
        date,
        plan_id,
        erp,
        icode,
        description,
        mold_id,
        cavity_id,
        start_date,
        end_date,
        found_time,
        user_time,
        tires_per_mold,
        time_difference_minutes,
        found_time_difference_minutes,
        time_taken,
        min_time_difference_minutes,
        time_difference_user_to_end_minutes,
        time_difference_user_to_found_minutes,
        plan,
        shift
    FROM calculated_data233;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>

<?php

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Your SQL query
    $sql = "DELETE FROM calculated_data23 WHERE start_date > found_time";

    // Prepare the SQL statement
    $stmt = $pdo->prepare($sql);

    // Execute the query
    $stmt->execute();

    echo "Rows deleted successfully";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>






<?php

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection to the database
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Your SQL query to fetch data
    $selectSql = "
        SELECT
            cd.id,
            cd.date,
            cd.plan_id,
            cd.erp,
            cd.icode,
            cd.description,
            cd.mold_id,
            cd.cavity_id,
            cd.start_date,
            cd.end_date,
            cd.found_time,
            cd.user_time,
            cd.tires_per_mold,
            cd.time_difference_minutes,
            cd.found_time_difference_minutes,
            cd.time_taken,
            cd.min_time_difference_minutes,
            cd.time_difference_user_to_end_minutes,
            cd.time_difference_user_to_found_minutes,
            cd.plan,
            td.greenweight * cd.plan AS calculated_green_tire_weight,
            td.stgreenweight * cd.plan AS calculated_stgreen_tire_weight
        FROM
            calculated_data23 cd
        JOIN
            tire_details td ON cd.icode = td.icode;
    ";

    // Prepare and execute the SELECT query
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->execute();

    // Fetch the results as an associative array
    $results = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the results in an HTML table

    // Update the calculated_green_tire_weight and calculated_stgreen_tire_weight in the calculated_data table
    foreach ($results as $row) {
        $id = $row['id'];
        $calculatedGreenTireWeight = $row['calculated_green_tire_weight'];
        $calculatedStGreenTireWeight = $row['calculated_stgreen_tire_weight'];

        $updateSql = "UPDATE calculated_data23 SET calculated_green_tire_weight = :calculated_green_tire_weight, calculated_stgreen_tire_weight = :calculated_stgreen_tire_weight WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':calculated_green_tire_weight', $calculatedGreenTireWeight);
        $updateStmt->bindParam(':calculated_stgreen_tire_weight', $calculatedStGreenTireWeight);
        $updateStmt->bindParam(':id', $id);
        $updateStmt->execute();
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} finally {
    // Close the database connection
    $conn = null;
}

?>








<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Filter</title>
</head>
<body>

<h2>Data Filter</h2>

<form method="GET" action="filter_pro.php">
    <label for="date">Select Date:</label>
    <input type="date" id="date" name="date">

    <label for="shift">Select Shift:</label>
    <select id="shift" name="shift">
        <option value="Day">DAY</option>
        <option value="Night">Night</option>
        
    </select>

    <button type="submit">Filter Data</button>
</form>

</body>
</html>
