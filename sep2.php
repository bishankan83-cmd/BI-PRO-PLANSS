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

// SQL query to fetch the required data
$sql = "
WITH TimeCalculation AS (
    SELECT 
        p.icode, 
        t.time_taken,
        p.tires_per_mold,
        (p.tires_per_mold * t.time_taken) AS total_time,
        p.start_date,
        DATE_ADD(p.start_date, INTERVAL (p.tires_per_mold * t.time_taken) DAY) AS end_date,
        DATEDIFF(DATE_ADD(p.start_date, INTERVAL (p.tires_per_mold * t.time_taken) DAY), p.start_date) AS days_to_elapse
    FROM 
        plannew p
    JOIN 
        tire t 
    ON 
        p.icode = t.icode
)
SELECT 
    icode,
    time_taken,
    tires_per_mold,
    total_time,
    start_date,
    end_date,
    days_to_elapse,
    CASE 
        WHEN days_to_elapse > 0 THEN tires_per_mold / days_to_elapse
        ELSE 0 
    END AS tires_per_day
FROM 
    TimeCalculation;
";

// Execute the query
$result = $conn->query($sql);

// Check if any rows are returned
if ($result->num_rows > 0) {
    // Output data of each row
    echo "<table border='1'>
            <tr>
                <th>icode</th>
                <th>Time Taken</th>
                <th>Tires Per Mold</th>
                <th>Total Time</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days to Elapse</th>
                <th>Tires Per Day</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["icode"] . "</td>
                <td>" . $row["time_taken"] . "</td>
                <td>" . $row["tires_per_mold"] . "</td>
                <td>" . $row["total_time"] . "</td>
                <td>" . $row["start_date"] . "</td>
                <td>" . $row["end_date"] . "</td>
                <td>" . $row["days_to_elapse"] . "</td>
                <td>" . $row["tires_per_day"] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

// Close the connection
$conn->close();
?>
