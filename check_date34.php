




<?php

// Replace these values with your actual database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Your SQL query to select rows with dates falling on holidays
$selectQuery = "
    SELECT *
    FROM plannew1
    WHERE DATE(start_date) IN (SELECT holiday_date FROM holidays)
       OR DATE(end_date) IN (SELECT holiday_date FROM holidays);
";

// Execute the select query
$selectResult = $conn->query($selectQuery);

// Check if the select query was successful
if ($selectResult) {
    // Fetch and display rows with dates falling on holidays
    while ($row = $selectResult->fetch_assoc()) {
        
        foreach ($row as $key => $value) {
           
        }
        echo "<br>";

        // Your update query to update the dates after the holiday
        $updateQuery = "
            UPDATE plannew1
            SET start_date = DATE_ADD(start_date, INTERVAL 1 DAY),
                end_date = DATE_ADD(end_date, INTERVAL 1 DAY)
            WHERE plan_id = " . $row['plan_id'] . ";
        ";

        // Execute the update query
        $updateResult = $conn->query($updateQuery);

        // Check if the update query was successful
        if ($updateResult) {
          
        } else {
            
        }
    }

    // Free the select result set
    $selectResult->free();
} else {
    // Handle the select query error
    echo "Error: " . $conn->error;
}



// Close the database connection
$conn->close();
header("Location: check_date342.php");
exit()

?>

