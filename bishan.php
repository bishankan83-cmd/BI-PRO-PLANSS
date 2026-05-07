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

// Create a new table for calculated data if it doesn't exist
$createTableQuery = "
    CREATE TABLE IF NOT EXISTS calculated_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE,
        plan_id INT,
        erp VARCHAR(255),
        icode VARCHAR(255),
        description VARCHAR(255),
        mold_id INT,
        cavity_id INT,
        start_date DATETIME,
        end_date DATETIME,
        found_time DATETIME,
        user_time DATETIME,
        tires_per_mold INT,
        time_difference_minutes INT,
        found_time_difference_minutes INT,
        time_taken VARCHAR(255),
        min_time_difference_minutes INT,
        time_difference_user_to_end_minutes INT,
        time_difference_user_to_found_minutes INT,
        plan INT
    )
";
$conn->query($createTableQuery);

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
            $endDateMidnight = new DateTime($currentDate . ' 23:59:59'); // Set end time to 23:59:59

            // Check if the date is a holiday
            if (isHoliday($currentDate, $conn)) {
                // If it's a holiday, skip processing and move to the next day
                continue;
            }

            // Delete existing data for the current date
            $deleteQuery = "DELETE FROM calculated_data";
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
            $plan = ($minTimeDifference / max($timeTaken, 1));

            $insertQuery = "
            INSERT INTO calculated_data (
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
                plan
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
                '{$plan}'
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
        $totalCavityNos = 130; // Assuming Total Cavity is always 130
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

// Create a new table for calculated data if it doesn't exist
$createTableQuery = "
    CREATE TABLE IF NOT EXISTS calculated_data1 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE,
        plan_id INT,
        erp VARCHAR(255),
        icode VARCHAR(255),
        description VARCHAR(255),
        mold_id INT,
        cavity_id INT,
        start_date DATETIME,
        end_date DATETIME,
        found_time DATETIME,
        user_time DATETIME,
        tires_per_mold INT,
        time_difference_minutes INT,
        found_time_difference_minutes INT,
        time_taken VARCHAR(255),
        min_time_difference_minutes INT,
        time_difference_user_to_end_minutes INT,
        time_difference_user_to_found_minutes INT,
        plan INT
    )
";
$conn->query($createTableQuery);

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
            $endDateMidnight = new DateTime($currentDate . ' 07:00:00'); // Set end time to 23:59:59

            // Check if the date is a holiday
            if (isHoliday($currentDate, $conn)) {
                // If it's a holiday, skip processing and move to the next day
                continue;
            }

             // Delete existing data for the current date
             $deleteQuery = "DELETE FROM calculated_data1";
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
            INSERT INTO calculated_data1 (
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
                plan
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
                '{$plan}'
            )
        ";
        $conn->query($insertQuery);
        // Increment the sum of plan values

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
        $totalCavityNos = 130; // Assuming Total Cavity is always 130
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

    $query = "UPDATE calculated_data1 SET date = DATE_SUB(date, INTERVAL 1 DAY)";
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
    INSERT INTO calculated_data (
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
        plan
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
        plan
    FROM calculated_data1;
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
    $sql = "DELETE FROM calculated_data WHERE start_date > found_time";

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

// SQL query to get the sum of plan, time_taken, cavity_id, mold_id, tires_per_mold, last_date, and next_date for each PLAN id
$sql = "SELECT plan_id, 
               icode,  -- Corrected from ecode
               erp,
               start_date,
               end_date,
               SUM(plan) AS total_data_plan_amount,
               (time_taken) AS total_time_taken,
               GROUP_CONCAT(DISTINCT cavity_id) AS cavity_ids,
               GROUP_CONCAT(DISTINCT mold_id) AS mold_ids,
               (tires_per_mold) AS total_tires_per_mold,
               MAX(date) AS last_date,
               DATE_FORMAT(DATE_ADD(MAX(date), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00') AS next_date
        FROM calculated_data
        GROUP BY plan_id, icode, erp, start_date, end_date";

$result = $conn->query($sql);

// Display the results and insert into a database table
if ($result->num_rows > 0) {
    
    while ($row = $result->fetch_assoc()) {
        $resultValue = $row["total_tires_per_mold"] - $row["total_data_plan_amount"];
        $calculatedValueInMinutes = $resultValue * $row["total_time_taken"];

        // Calculate the new date and time by adding the calculated value in minutes to the next date
        $newDateTime = date('Y-m-d H:i:s', strtotime($row["next_date"]) + ($calculatedValueInMinutes * 60));

        // Display only rows where the result value is positive
        if ($resultValue > 0) {
           
            // Insert into a database table
            $insertQuery = "INSERT INTO calculated_data (plan_id, icode, erp, start_date, end_date, time_taken, cavity_id, mold_id, tires_per_mold, plan, date) VALUES ('" . $row["plan_id"] . "', '" . $row["icode"] . "', '" . $row["erp"] . "', '" . $row["start_date"] . "', '" . $row["end_date"] . "','" . $row["total_time_taken"] . "', '" . $row["cavity_ids"] . "', '" . $row["mold_ids"] . "', '" . $row["total_tires_per_mold"] . "', '" . $resultValue . "','" . $row["next_date"] . "')";

            $conn->query($insertQuery);
        }
    }

    echo "</table>";
} else {
    echo "No results found.";
}

// Close the database connection
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
            td.greenweight * cd.plan AS calculated_green_tire_weight
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
   
    
    foreach ($results[0] as $key => $value) {
      
    }

    foreach ($results as $row) {
       
        foreach ($row as $value) {
            
        }
       
    }
    

    // Update the calculated_green_tire_weight in the calculated_data table
    foreach ($results as $row) {
        $id = $row['id'];
        $calculatedGreenTireWeight = $row['calculated_green_tire_weight'];

        $updateSql = "UPDATE calculated_data SET calculated_green_tire_weight = :calculated_green_tire_weight WHERE id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':calculated_green_tire_weight', $calculatedGreenTireWeight);
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

// Specify the number of days to add
$daysToAdd = 1;  // Change this to the desired number of days

// SQL query to update the date column in calculated_data table
$updateDateQuery = "UPDATE calculated_data
                    SET date = DATE_ADD(date, INTERVAL $daysToAdd DAY)
                    WHERE date IN (SELECT holiday_date FROM holidays)";

// Execute the update query
if ($conn->query($updateDateQuery) === TRUE) {
    echo "Date update successful!";
    
    // SQL query to delete rows with plan value 0
    $deleteRowsQuery = "DELETE FROM calculated_data WHERE plan = 0";

    // Execute the delete query
    if ($conn->query($deleteRowsQuery) === TRUE) {
        echo "Rows with plan value 0 deleted successfully!";
    } else {
        echo "Error deleting rows: " . $conn->error;
    }
} else {
    echo "Error updating date: " . $conn->error;
}

// Close the connection
$conn->close();

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Data Display</title>
    <style>
        /* Your CSS styles */
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        td {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
        }

        /* Style the form */
        form {
            text-align: center;
            margin: 10px;
        }

        label {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            display: block;
            margin-bottom: 5px;
        }

        input[type="date"],
        input[type="submit"] {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #333333;
        }
    </style>
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
               SUM(calculated_green_tire_weight) AS total_green_tire_weight
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
    echo "<table><tr><th>Date</th><th>Total Plan tires Nos</th><th>Utilized/Plan Cavity Nos</th><th>Total Cavity</th><th>Average Utilization (%)</th><th>Total Green Tire Weight</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row["date"] . "</strong></td>";
        echo "<td>{$row["total_data_plan_amount"]}</td>";
        echo "<td>{$row["unique_cavity_id_quantity"]}</td>";
        
        // Hardcoded value for Total Cavity (you might want to make this dynamic)
        $totalCavity = 130;
        echo "<td>{$totalCavity}</td>";
        
        // Calculate and display average utilization percentage
        $percentage = ($row["unique_cavity_id_quantity"] / $totalCavity) * 100;
        echo "<td>" . number_format($percentage, 2) . "%</td>";
        
        echo "<td>{$row["total_green_tire_weight"]}</td>";
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






