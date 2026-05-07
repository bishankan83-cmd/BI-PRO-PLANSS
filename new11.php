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
            $plan = ceil($minTimeDifference / max($timeTaken, 1));

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
        $sumPlanDifference = ceil($sumOfPlanValues - $previousSumOfPlanValues);

     

        // Update previous day's sum of plan values for the next iteration
        $previousSumOfPlanValues = $sumOfPlanValues;

        // Display the number of different cavity IDs for the current date
        $numCavityIDs = count($cavityCount[$date]);
    

     

        // Calculate Average Utilization Percentage and round it to Sankyat
        $utilizedCavityNos = $numCavityIDs;
        $totalCavityNos = 130; // Assuming Total Cavity is always 130
        $averageUtilization = round(($utilizedCavityNos / $totalCavityNos) * 100);
        

    }

    echo '</table>';
} else {
    echo "No results found";
}

// Close connection
$conn->close();
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
$sql = "UPDATE calculated_data
        SET date = DATE_ADD(date, INTERVAL $daysToAdd DAY)
        WHERE date IN (SELECT holiday_date FROM holidays)";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Update successful!";
} else {
    echo "Error updating record: " . $conn->error;
}

// Close the connection
$conn->close();

?>



<?php
// Replace these values with your actual database connection details
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
                // Delete existing data for the current date
                $deleteQuery = "DELETE FROM cal";
                $conn->query($deleteQuery);
    
    // Get user input
    $startDate = $_POST["start_date"];
    $endDate = $_POST["end_date"];
    $startTime = $_POST["start_time"];
    $endTime = $_POST["end_time"];

    // Combine date and time values
    $startDatetime = $startDate;
    $endDatetime = $endDate;
    
    // Prepare and execute the SQL query
    $sql = "SELECT * FROM calculated_data WHERE date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDatetime, $endDatetime);
    $stmt->execute();
    // Get the result set
    $result = $stmt->get_result();

    // Display the details for each row
    if ($result->num_rows > 0) {

        
        while ($row = $result->fetch_assoc()) {
          
            
        // Get "system Start Date" and "system End Date" from the database
        $systemStartDate = $row['start_date'];
        $systemEndDate = $row['end_date'];

        // Convert database start date and time to DateTime objects
        $systemStartDatetime = new DateTime($systemStartDate);
        $systemEndDatetime = new DateTime($systemEndDate);

        // Calculate time difference in minutes between system start and end dates
        $systemTimeDifferenceMinutes = $systemStartDatetime->diff($systemEndDatetime)->days * 24 * 60 +
            $systemStartDatetime->diff($systemEndDatetime)->h * 60 +
            $systemStartDatetime->diff($systemEndDatetime)->i;



  
    
    // Combine date and time values
    $startDatetime = $startDate . ' ' . $startTime;
    $endDatetime = $endDate . ' ' . $endTime;

    // Convert to DateTime objects
    $startDateTimeObj = new DateTime($startDatetime);
    $endDateTimeObj = new DateTime($endDatetime);

    // Calculate time difference in minutes
    $timeDifferenceMinutesss = $startDateTimeObj->diff($endDateTimeObj)->days * 24 * 60 +
                             $startDateTimeObj->diff($endDateTimeObj)->h * 60 +
                             $startDateTimeObj->diff($endDateTimeObj)->i;



     
   // Convert database start date and time to DateTime object
   $recordStartDatetime = new DateTime($row['start_date']);

   // Calculate time difference in minutes between user input and database start date
   $timeDifferenceMinutess =  $endDateTimeObj->diff($recordStartDatetime)->days * 24 * 60 +
                            $endDateTimeObj->diff($recordStartDatetime)->h * 60 +
                            $endDateTimeObj->diff($recordStartDatetime)->i;



       
   // Convert database start date and time to DateTime object
   $recordEndDatetime = new DateTime($row['end_date']);

   // Calculate time difference in minutes between user input and database start date
   $timeDifferenceMinutes =  $startDateTimeObj->diff($recordEndDatetime)->days * 24 * 60 +
                            $startDateTimeObj->diff($recordEndDatetime)->h * 60 +
                            $startDateTimeObj->diff($recordEndDatetime)->i;







   // Extract date and time from the "Actual Date" column
   $actualDate = $row['date'] . " 00:00:00";

   // Create DateTime objects for the "Actual Date" and "System Start Date"
   $actualDateObj = new DateTime($actualDate);
   $systemEndDatetime = new DateTime($systemEndDate);

   // Calculate time difference in minutes between "Actual Date" and "System Start Date"
   $timeDifferenceActualVsSystemm = $actualDateObj->diff($systemEndDatetime)->days * 24 * 60 +
       $actualDateObj->diff($systemEndDatetime)->h * 60 +
       $actualDateObj->diff($systemEndDatetime)->i;

  





 // Extract date and time from the "Actual Date" column
 $actuallDate = $row['date'] . "23:59:59";

 // Create DateTime objects for the "Actual Date" and "System Start Date"
 $actuallDateObj = new DateTime($actuallDate);
 $systemStartDatetime = new DateTime($systemStartDate);

 // Calculate time difference in minutes between "Actual Date" and "System Start Date"
 $timeDifferenceActualVsSysteem = $actuallDateObj->diff($systemStartDatetime)->days * 24 * 60 +
     $actuallDateObj->diff($systemStartDatetime)->h * 60 +
     $actuallDateObj->diff($systemStartDatetime)->i;

 
// Get "user_time" and "found_time" from the database
$userTime = new DateTime($row['user_time']);
$foundTime = new DateTime($row['found_time']);

// Calculate time difference in minutes between user_time and found_time
$timeDifferenceUserToFound = $userTime->diff($foundTime)->days * 24 * 60 +
    $userTime->diff($foundTime)->h * 60 +
    $userTime->diff($foundTime)->i;






    // Display the time differences
    $minTimeDifference = min(
        $timeDifferenceMinutess,
        $timeDifferenceMinutes,
        $timeDifferenceMinutesss,
        $systemTimeDifferenceMinutes,
        $timeDifferenceActualVsSysteem,
        $timeDifferenceActualVsSystemm,
        $timeDifferenceUserToFound
    );

   
            
          // Divide minTimeDifference by time_taken and display the result
$divisionResult = $minTimeDifference / $row['time_taken'];


// Insert all data into the new_table
$insertSql = "INSERT INTO cal (id, erp, icode, mold_id, cavity_id, start_date, end_date, tires_per_mold, plan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);

if ($insertStmt) {
    // Bind parameters
    $insertStmt->bind_param("sssssssss", $row['id'], $row['erp'], $row['icode'], $row['mold_id'], $row['cavity_id'], $row['start_date'], $row['end_date'], $row['tires_per_mold'], $divisionResult);

    // Execute the query
    $insertStmt->execute();

    // Close the statement
    $insertStmt->close();
}



        }
    } else {
        echo "No records found within the given date range";
    }

    


    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>




<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch data from the 'cal' table, join with 'tire_details' to get descriptions,
    // 'press_cavity' to get press_id, and 'press' to get press_name
    $stmt = $pdo->prepare("
        SELECT cal.*, tire_details.description, press_cavity.press_id, press.press_name
        FROM cal
        LEFT JOIN tire_details ON cal.icode = tire_details.icode
        LEFT JOIN press_cavity ON cal.cavity_id = press_cavity.cavity_id
        LEFT JOIN press ON press_cavity.press_id = press.press_id
        LEFT JOIN alp ON press.press_name = alp.press_name
        ORDER BY alp.id ASC, cal.cavity_id ASC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the data in an HTML table
    echo '<table border="1">';
    echo '<tr><th>Press Name</th><th>ERP</th><th>Item Code</th><th>Description</th><th>Mold ID</th><th>Start Date</th><th>End Date</th><th>Tires per Mold</th><th>Plan</th></tr>';
    
    $totalPlan = 0; // Variable to store the total plan
    $uniqueCavityIds = []; // Array to store unique cavity_ids

    foreach ($result as $row) {
        echo '<tr>';
        echo '<td>' . $row['press_name'] . '</td>'; // Display the press_name
        echo '<td>' . $row['erp'] . '</td>';
        echo '<td>' . $row['icode'] . '</td>';
        echo '<td>' . $row['description'] . '</td>'; // Display the description
        echo '<td>' . $row['mold_id'] . '</td>';
        //echo '<td>' . $row['cavity_id'] . '</td>';
        echo '<td>' . $row['start_date'] . '</td>';
        echo '<td>' . $row['end_date'] . '</td>';
        echo '<td>' . $row['tires_per_mold'] . '</td>';
        echo '<td>' . round($row['plan']) . '</td>';
        
        // Add the plan value to the total
        $totalPlan += round($row['plan']);

        // Track unique cavity_ids
        $uniqueCavityIds[$row['cavity_id']] = true;
        
        echo '</tr>';
    }

    // Display the total row
    echo '<tr><td colspan="9"></td><td>Total</td><td>' . $totalPlan . '</td></tr>';

    // Display the count of unique cavity_ids
    echo '<tr><td colspan="9"></td><td>Unique Cavity IDs Count</td><td>' . count($uniqueCavityIds) . '</td></tr>';
    
    echo '</table>';

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$pdo = null;
?>


