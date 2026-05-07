
<!DOCTYPE html>
<html>
<head>
    <title>Retrieve Events</title>
</head>
<body>
    <h2>Retrieve Events</h2>
    <form action="new_daily_prod.php" method="post">
        <label for="start_time">Start Date and Time:</label>
        <input type="datetime-local" id="start_time" name="start_time" required>
        <br><br>
        <label for="end_time">End Date and Time:</label>
        <input type="datetime-local" id="end_time" name="end_time" required>
        <br><br>
        <input type="submit" value="Retrieve Events">
    </form>
</body>
</html>







<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user input for start and end times
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_start_time = $_POST['start_time'];
    $user_end_time = $_POST['end_time'];
    
    // SQL query to retrieve records from the plannew table including "time_taken" from the tire table
    $sql = "SELECT p.plan_id, p.icode, p.start_date, p.end_date, t.time_taken
            FROM plannew p
            JOIN tire t ON p.icode = t.icode";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $icode = $row['icode'];
            $plan_id = $row['plan_id'];
            $found_start_time = $row['start_date'];
            $found_end_time = $row['end_date'];
            $time_given = $row['time_taken'];

            // Calculate the time difference in minutes
            $startTimestamp = strtotime($row["start_date"]);
            $endTimestamp = strtotime($user_end_time);
            $timeDifference = ($endTimestamp - $startTimestamp) / 60; // Convert to minutes

            echo "icode: $icode<br>";
            echo "plan_id: $plan_id<br>";
            echo "Start Time: $found_start_time<br>";
            echo "End Date: $found_end_time<br>";
            echo "Time Given: $time_given minutes<br>";
            
            // Calculate Time Taken / Time Difference
            $timeTaken = $time_given; // Assuming "time_given" should be used
            $timeTakenDividedByDifference = $timeDifference / $timeTaken;
            echo "Time Difference: $timeDifference minutes<br>";
            echo "To Be: $timeTakenDividedByDifference<br>";
            
            // Display the "Start Date and Time" from the merged_data table
            echo "Start Date and Time (from merged_data): " . $row["start_date"] . "<br>";
            
            // Display the "End Date and Time" provided by the user
            echo "End Date and Time (provided by user): $user_end_time<br>";
            
            // Display the "Start Date and Time" provided by the user
            echo "Start Date and Time (User Input): $user_start_time<br>";
            
            echo "<br>"; // Add a line break for separation
        }
    } else {
        echo "No records found in the database.";
    }
    
    $conn->close();
}
?>





<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user input for start and end times
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_start_time = $_POST['start_time'];
    $user_end_time = $_POST['end_time'];

      // Remove the 'T' from the end date and time
      $user_end_time = str_replace('T', ' ', $user_end_time);
    
      // Remove the 'T' from the end date and time
      $user_start_time = str_replace('T', ' ', $user_start_time);
    
    // SQL query to retrieve records from the database
    $sql = "SELECT p.plan_id, p.icode, p.start_date, p.end_date, t.time_taken
            FROM plannew p
            JOIN tire t ON p.icode = t.icode";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plan_id = $row['icode'];
            $found_start_time = $row['start_date'];
            $found_end_time = $row['end_date'];
            $icode = $row['icode'];
            $time_given = $row['time_taken'];


            if ($found_start_time >= $user_start_time && $found_end_time <= $user_end_time) {
                

                 // Calculate the time difference in minutes
           
            $timeDifference = ($user_end_time - $user_start_time) / 60; // Convert to minutes

            echo "icode: $icode<br>";
            echo "plan_id: $plan_id<br>";
            echo "Start Time: $found_start_time<br>";
            echo "End Date: $found_end_time<br>";
            echo "Time Given: $time_given minutes<br>";
            
            // Calculate Time Taken / Time Difference
            $timeTaken = $time_given; // Assuming "time_given" should be used
            $timeTakenDividedByDifference = $timeDifference / $timeTaken;
            echo "Time Difference: $timeDifference minutes<br>";
            echo "To Be: $timeTakenDividedByDifference<br>";
            
          
            
            // Display the "End Date and Time" provided by the user
            echo "End Date and Time (provided by user): $user_end_time<br>";
            
            // Display the "Start Date and Time" provided by the user
            echo "Start Date and Time (User Input): $user_start_time<br>";
            
            echo "<br>"; 
            } elseif ($found_start_time <= $user_start_time && $found_end_time >= $user_end_time) {
                 // Calculate the time difference in minutes
                 $timeDifference = ($user_end_time - $user_start_time) / 60; // Convert to minutes
            echo "icode: $icode<br>";
            echo "plan_id: $plan_id<br>";
            echo "Start Time: $found_start_time<br>";
            echo "End Date: $found_end_time<br>";
            echo "Time Given: $time_given minutes<br>";
            
            // Calculate Time Taken / Time Difference
            $timeTaken = $time_given; // Assuming "time_given" should be used
            $timeTakenDividedByDifference = $timeDifference / $timeTaken;
            echo "Time Difference: $timeDifference minutes<br>";
            echo "To Be: $timeTakenDividedByDifference<br>";
            
          
            // Display the "End Date and Time" provided by the user
            echo "End Date and Time (provided by user): $user_end_time<br>";
            
            // Display the "Start Date and Time" provided by the user
            echo "Start Date and Time (User Input): $user_start_time<br>";
            
            echo "<br>"; 
            } elseif ($found_start_time <= $user_end_time && $found_end_time >= $user_start_time) {
               // Calculate the time difference in minutes
               $timeDifference = ($user_end_time - $user_start_time) / 60; // Convert to minutes

            echo "icode: $icode<br>";
            echo "plan_id: $plan_id<br>";
            echo "Start Time: $found_start_time<br>";
            echo "End Date: $found_end_time<br>";
            echo "Time Given: $time_given minutes<br>";
            
            // Calculate Time Taken / Time Difference
            $timeTaken = $time_given; // Assuming "time_given" should be used
            $timeTakenDividedByDifference = $timeDifference / $timeTaken;
            echo "Time Difference: $timeDifference minutes<br>";
            echo "To Be: $timeTakenDividedByDifference<br>";
            
           
            
            // Display the "End Date and Time" provided by the user
            echo "End Date and Time (provided by user): $user_end_time<br>";
            
            // Display the "Start Date and Time" provided by the user
            echo "Start Date and Time (User Input): $user_start_time<br>";
            
            echo "<br>"; 
            } else {
               
            }
        }
    } else {
        echo "No records found in the database.";
    }
    
    $conn->close();
}
?>