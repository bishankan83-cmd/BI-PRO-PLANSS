<?php
// Database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to delete all data from the table
$sql = "DELETE FROM `calculated_data`";

if ($conn->query($sql) === TRUE) {
    echo "All records deleted successfully";
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close connection
$conn->close();
?>



<?php
// Database connection details
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

// SQL query to delete all data from the table
$sql = "DELETE FROM plan_by_date_shift";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "All records deleted successfully.";
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close connection
$conn->close();
?>





<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to delete all data from the highest_plan_data table
$sql = "TRUNCATE TABLE highest_plan_data";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "All data deleted from 'highest_plan_data' successfully.";
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close the connection
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
                $minutesDifferenceUserToEnd,//
                $minutesDifferenceUserToFound,//
                $entry['Time Difference (Minutes)'],//
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
            $plan = round($minTimeDifference / max($timeTaken, 1));

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
    //echo "Connection failed: " . $e->getMessage();
} finally {
    // Close the database connection
    $conn = null;
}

?>







<?php
// Database connection details
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Create a Temporary Table with Aggregated Sums
$sql_create_temp_table = "
    CREATE TEMPORARY TABLE temp_plan_sums AS
    SELECT 
        erp,
        icode,
        mold_id,
        cavity_id,
        DATE(date) AS plan_date,
        SUM(plan) AS total_plan
    FROM 
        calculated_data23
    WHERE 
        shift LIKE '%NIGHT%'
    GROUP BY 
        erp,
        icode,
        mold_id,
        cavity_id,
        DATE(date);
";

if ($conn->query($sql_create_temp_table) === TRUE) {
    echo "Temporary table created successfully.<br>";
} else {
    echo "Error creating temporary table: " . $conn->error . "<br>";
}

// Step 2: Update the Original Table with the Aggregated Sums
$sql_update = "
    UPDATE 
        calculated_data23 cd
    JOIN 
        temp_plan_sums tps
    ON 
        cd.erp = tps.erp
        AND cd.icode = tps.icode
        AND cd.mold_id = tps.mold_id
        AND cd.cavity_id = tps.cavity_id
        AND DATE(cd.date) = tps.plan_date
    SET 
        cd.plan = tps.total_plan
    WHERE 
        cd.shift LIKE '%NIGHT%';
";

if ($conn->query($sql_update) === TRUE) {
    echo "Table updated successfully.<br>";
} else {
    echo "Error updating table: " . $conn->error . "<br>";
}

// Step 3: Drop the Temporary Table
$sql_drop_temp_table = "DROP TEMPORARY TABLE IF EXISTS temp_plan_sums;";

if ($conn->query($sql_drop_temp_table) === TRUE) {
    echo "Temporary table dropped successfully.<br>";
} else {
    echo "Error dropping temporary table: " . $conn->error . "<br>";
}

// Close connection
$conn->close();
?>











<?php
// Database connection details
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

// Step 1: Create a Temporary Table with Unique IDs
$sql_create_temp = "
    CREATE TEMPORARY TABLE temp_unique AS
    SELECT MIN(id) AS id
    FROM calculated_data23
    GROUP BY date, erp, icode, mold_id, cavity_id, plan, shift;
";

if ($conn->query($sql_create_temp) === TRUE) {
    echo "Temporary table created successfully.<br>";
} else {
    echo "Error creating temporary table: " . $conn->error . "<br>";
}

// Step 2: Delete Duplicate Rows from the Original Table
$sql_delete_duplicates = "
    DELETE FROM calculated_data23
    WHERE id NOT IN (SELECT id FROM temp_unique);
";

if ($conn->query($sql_delete_duplicates) === TRUE) {
    echo "Duplicates removed successfully.<br>";
} else {
    echo "Error removing duplicates: " . $conn->error . "<br>";
}

// Step 3: Drop the Temporary Table
$sql_drop_temp = "DROP TEMPORARY TABLE temp_unique;";

if ($conn->query($sql_drop_temp) === TRUE) {
    echo "Temporary table dropped successfully.<br>";
} else {
    echo "Error dropping temporary table: " . $conn->error . "<br>";
}

// Close connection
$conn->close();
?>








<?php
// Database connection details
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the SQL query to delete rows where date is before today's date
$sql = "DELETE FROM `calculated_data23` WHERE `date` < CURDATE()";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records deleted successfully";
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close the connection
$conn->close();
?>



<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to insert data into the existing highest_plan_data table
$sql = "INSERT INTO highest_plan_data (erp, icode, mold_id, cavity_id, highest_plan)
        SELECT
            erp,
            icode,
            mold_id,
            cavity_id,
            MAX(plan) AS highest_plan
        FROM
            calculated_data23
        GROUP BY
            erp, icode, mold_id, cavity_id
        ON DUPLICATE KEY UPDATE
            highest_plan = VALUES(highest_plan)";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data inserted into 'highest_plan_data' successfully.";
} else {
    echo "Error inserting data: " . $conn->error;
}

// Close the connection
$conn->close();
?>









<?php
// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a new mysqli instance
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to create a temporary table with IDs to keep
$sql_create_temp = "
CREATE TEMPORARY TABLE temp_keep_ids AS
SELECT id
FROM calculated_data23
WHERE (date, erp, icode, mold_id, cavity_id) IN (
    SELECT min(date) AS max_date, erp, icode, mold_id, cavity_id
    FROM calculated_data23
    GROUP BY erp, icode, mold_id, cavity_id
);
";

// Execute the query
if ($conn->query($sql_create_temp) === TRUE) {
    echo "Temporary table created successfully.\n";
} else {
    echo "Error creating temporary table: " . $conn->error . "\n";
}

// SQL query to delete records not in the temporary table
$sql_delete = "
DELETE FROM calculated_data23
WHERE id NOT IN (SELECT id FROM temp_keep_ids);
";

// Execute the deletion query
if ($conn->query($sql_delete) === TRUE) {
    echo "Records deleted successfully.\n";
} else {
    echo "Error deleting records: " . $conn->error . "\n";
}

// SQL query to drop the temporary table
$sql_drop_temp = "DROP TEMPORARY TABLE temp_keep_ids;";

// Execute the drop query
if ($conn->query($sql_drop_temp) === TRUE) {
    echo "Temporary table dropped successfully.\n";
} else {
    echo "Error dropping temporary table: " . $conn->error . "\n";
}

// Close the database connection
$conn->close();
?>

<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to update plan value from 0 to 1
$sql = "UPDATE `calculated_data23` SET `plan` = 1 WHERE `plan` = 0";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating records: " . $conn->error;
}

// Close the connection
$conn->close();
?>



<?php
// Database connection details
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

// SQL to create the table
$sql_create_table = "CREATE TABLE IF NOT EXISTS `plan_by_date_shift` (
    `erp` varchar(255),
    `icode` varchar(255),
    `mold_id` varchar(100),
    `cavity_id` int(11),
    `shift` varchar(50),
    `plan` int(11),
    `tires_per_mold` int(11),
    `date` date,
    PRIMARY KEY (`erp`, `icode`, `mold_id`, `cavity_id`, `shift`, `date`)
)";

// Execute the query
if ($conn->query($sql_create_table) === TRUE) {
    echo "Table `plan_by_date_shift` created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// SQL to insert data
$sql_insert_data = "
INSERT INTO `plan_by_date_shift` (erp, icode, mold_id, cavity_id, shift, plan, tires_per_mold, date)
SELECT
    erp,
    icode,
    mold_id,
    cavity_id,
    shift,
    MAX(plan) AS plan,
    MAX(tires_per_mold) AS tires_per_mold,
    date
FROM
    (
        SELECT
            erp,
            icode,
            mold_id,
            cavity_id,
            shift,
            plan,
            tires_per_mold,
            DATE_ADD(start_date, INTERVAL n.n DAY) AS date
        FROM
            calculated_data23
        JOIN (
            SELECT @row := @row + 1 AS n
            FROM (SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) t1,
                 (SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) t2,
                 (SELECT @row := -1) numbers
        ) n
        WHERE DATE_ADD(start_date, INTERVAL n.n DAY) <= end_date
    ) AS date_expansion
GROUP BY
    erp,
    icode,
    mold_id,
    cavity_id,
    shift,
    date
";

// Execute the query
if ($conn->query($sql_insert_data) === TRUE) {
    echo "Data inserted into `plan_by_date_shift` successfully.<br>";
} else {
    echo "Error inserting data: " . $conn->error . "<br>";
}

// Close connection
$conn->close();
?>





<?php
// Database connection details
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

// SQL query to update the plan column
$sql = "
    UPDATE plan_by_date_shift p
    JOIN highest_plan_data h
    ON p.erp = h.erp
    AND p.icode = h.icode
    AND p.mold_id = h.mold_id
    AND p.cavity_id = h.cavity_id
    SET p.plan = h.highest_plan
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating records: " . $conn->error;
}

// Close connection
$conn->close();
?>


<?php
// Database connection details
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

// SQL query to insert data from plan_by_date_shift to calculated_data23
$sql = "INSERT INTO calculated_data (erp, icode, mold_id, cavity_id, plan, tires_per_mold, date)
        SELECT erp, icode, mold_id, cavity_id, plan, tires_per_mold, date
        FROM plan_by_date_shift";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
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
            calculated_data cd
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

        $updateSql = "UPDATE calculated_data SET calculated_green_tire_weight = :calculated_green_tire_weight, calculated_stgreen_tire_weight = :calculated_stgreen_tire_weight WHERE id = :id";
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
    <title>Your Data Display</title>
   
</head>
<body>

<div class="container">

    <!-- Display a form for user input -->
    <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
       Start Date:
        <input type="date" id="start_date" name="start_date">

       End Date:
        <input type="date" id="end_date" name="end_date">

        <input type="submit" value="Submit">
    </form>
    <form action="test123456.php" method="post">
        <!-- Use button type="submit" to submit the form and navigate to the target page -->
        <input type="submit" value="Work Order Range">
    </form>

    <form action="test_export.php" method="post">
        <!-- Use button type="submit" to submit the form and navigate to the target page -->
        <input type="submit" value="export excel">
    </form>


<?php

// Check if start date and end date are provided in the URL parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Assuming you have a database connection established
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

// Fetch the holiday dates from the holidays table
$holidaysQuery = "SELECT holiday_date FROM holidays";
$holidaysResult = $conn->query($holidaysQuery);

// Create an array to store holiday dates
$holidays = array();
while ($holidayRow = $holidaysResult->fetch_assoc()) {
    $holidays[] = $holidayRow['holiday_date'];
}

// Modify the SQL query to exclude data from days before today
$sql = "SELECT date,
               SUM(plan) AS total_data_plan_amount,
               COUNT(DISTINCT cavity_id) AS unique_cavity_id_quantity,
               SUM(calculated_green_tire_weight) AS total_green_tire_weight,
               SUM(calculated_stgreen_tire_weight) AS total_steel_weight
        FROM calculated_data";

// Add WHERE clause if start and end dates are provided
if ($startDate && $endDate) {
    $sql .= " WHERE date BETWEEN '$startDate' AND '$endDate' AND date >= CURDATE()";

    // Exclude holidays
    if (!empty($holidays)) {
        $sql .= " AND date NOT IN ('" . implode("','", $holidays) . "')";
    }
} else {
    // Exclude holidays if no specific start and end dates
    $sql .= " WHERE date >= CURDATE()";

    if (!empty($holidays)) {
        $sql .= " AND date NOT IN ('" . implode("','", $holidays) . "')";
    }
}

$sql .= " GROUP BY date";


$result = $conn->query($sql);

// Display the results
if ($result->num_rows > 0) {
    echo "<table><tr><th>Date</th><th>Total Plan tires Nos</th><th>Total Stock order tires Nos</th><th>Utilized/Plan Cavity Nos</th><th>Total Cavity</th><th>Average Utilization (%)</th><th>Total Green Tire Weight</th><th>Total Steel Weight</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row["date"] . "</strong></td>";

        echo "<td>{$row["total_data_plan_amount"]}</td>";

        echo "<td></td>";
        echo "<td>{$row["unique_cavity_id_quantity"]}</td>";
        
        // Hardcoded value for Total Cavity (you might want to make this dynamic)
        $totalCavity = 134;
        echo "<td>{$totalCavity}</td>";
        
        // Calculate and display average utilization percentage
        $percentage = ($row["unique_cavity_id_quantity"] / $totalCavity) * 100;
        echo "<td>" . number_format($percentage, 2) . "%</td>";
        
        echo "<td>{$row["total_green_tire_weight"]}</td>";
        echo "<td>{$row["total_steel_weight"]}</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No results found.";
}

// Close the database connection
$conn->close();

?>
</div>
</body>
</html>
