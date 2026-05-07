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
        FROM calculated_data23
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
            $insertQuery = "INSERT INTO calculated_data237 (plan_id, icode, erp, start_date, end_date, time_taken, cavity_id, mold_id, tires_per_mold, plan, date) VALUES ('" . $row["plan_id"] . "', '" . $row["icode"] . "', '" . $row["erp"] . "', '" . $row["start_date"] . "', '" . $row["end_date"] . "','" . $row["total_time_taken"] . "', '" . $row["cavity_ids"] . "', '" . $row["mold_ids"] . "', '" . $row["total_tires_per_mold"] . "', '" . $resultValue . "','" . $row["next_date"] . "')";

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