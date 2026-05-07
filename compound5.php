<!DOCTYPE html>
<html>
<head>
   
    <title>Production Plan Details</title>
    <style>
         <style>
        body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
        }

        h1 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
        }

        th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #ECECEC;
        }

        .erp-window {
            text-align: left;
        }

        .erp-window span {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
        }

        .erp-window .green {
            color: #000000;
            font-weight: bold;
        }

        .erp-window .red {
            color: #F28018;
            font-weight: bold;
        }

        @keyframes blink {
    0% {
        opacity: 0;
    }
    50% {
        opacity: 2;
    }
    100% {
        opacity: 1;
    }
}




    </style>
    </style>
</head>
<body>


        <!-- Display User End Time and User Start Time outside of the table -->
        
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

        // Initialize totalColumns array
        $totalColumns = [
            'a' => 0, 'b' => 0, 'c' => 0, 'd' => 0, 'e' => 0, 'f' => 0, 'g' => 0,
            'h' => 0, 'i' => 0, 'j' => 0, 'k' => 0, 'l' => 0, 'm' => 0, 'n' => 0, 'o' => 0
        ];

        // SQL query to retrieve records from the database in ascending order of cavity_id
        $sql = "SELECT p.plan_id, p.icode, p.start_date, p.end_date, p.mold_id, p.cavity_id, t.time_taken
        FROM plannew p
        JOIN tire t ON p.icode = t.icode
        ORDER BY p.cavity_id ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $plan_id = $row['icode'];
                $found_start_time = $row['start_date'];
                $found_end_time = $row['end_date'];
                $icode = $row['icode'];
                $time_given = $row['time_taken'];
                $mold_id = $row['mold_id'];
                $cavity_id = $row['cavity_id'];

                // Query to fetch mold_name from the mold table
                $mold_query = "SELECT mold_name FROM mold WHERE mold_id = $mold_id";
                $mold_result = $conn->query($mold_query);
                $mold_row = $mold_result->fetch_assoc();
                $mold_name = $mold_row['mold_name'];

                // Query to fetch cavity_name from the cavity table
                $cavity_query = "SELECT cavity_name FROM cavity WHERE cavity_id = $cavity_id";
                $cavity_result = $conn->query($cavity_query);
                $cavity_row = $cavity_result->fetch_assoc();
                $cavity_name = $cavity_row['cavity_name'];

                if ($found_start_time >= $user_start_time && $found_end_time <= $user_end_time) {
                    // Convert date and time strings to timestamps using strtotime
                    $user_end_timestamp = strtotime($user_end_time);
                    $user_start_timestamp = strtotime($user_start_time);
                    $found_start_timestamp = strtotime($found_start_time);

                    // Ensure $found_end_timestamp is not greater than $user_end_timestamp
                    $found_end_timestamp = min(strtotime($found_end_time), $user_end_timestamp);

                    // Calculate the time difference in minutes
                    if ($user_start_timestamp > $found_start_timestamp) {
                        // If user_start_time is less than found_start_time, calculate from user_start_time
                        $timeDifference = ($user_end_timestamp - $user_start_timestamp) / 60;
                    } else {
                        // Otherwise, calculate from found_start_time
                        $timeDifference = ($found_end_timestamp - $found_start_timestamp) / 60;
                    }

                    // Calculate Time Taken / Time Difference
                    $timeTaken = $time_given; // Assuming "time_given" should be used
                    $timeTakenDividedByDifference = $timeDifference / $timeTaken;

                    // Append the data to the results array
                    $results[] = array(
                        'icode' => $icode,
                        'mold_id' => $mold_id,
                        'mold_name' => $mold_name,
                        'cavity_id' => $cavity_id,
                        'cavity_name' => $cavity_name,
                        'found_start_time' => $found_start_time,
                        'found_end_time' => $found_end_time,
                        'time_given' => $time_given,
                        'timeDifference' => $timeDifference,
                        'user_end_time' => $user_end_time,
                        'user_start_time' => $user_start_time,
                        'tobe' => $timeTakenDividedByDifference,
                        'description' => getDescription($icode, $conn),
                    );

                
                }
                // ... Handle other conditions (elseif statements) ...
                elseif ($found_start_time <= $user_start_time && $found_end_time >= $user_end_time) {
                    // Convert date and time strings to timestamps using strtotime
                    $user_end_timestamp = strtotime($user_end_time);
                    $user_start_timestamp = strtotime($user_start_time);
                    $found_start_timestamp = strtotime($found_start_time);
                    
                    // Ensure $found_end_timestamp is not greater than $user_end_timestamp
                    $found_end_timestamp = min(strtotime($found_end_time), $user_end_timestamp);
                
                    // Calculate the time difference in minutes
                    if ($user_start_timestamp > $found_start_timestamp) {
                        // If user_start_time is less than found_start_time, calculate from user_start_time
                        $timeDifference = ($user_end_timestamp - $user_start_timestamp) / 60;
                    } else {
                        // Otherwise, calculate from found_start_time
                        $timeDifference = ($found_end_timestamp - $found_start_timestamp) / 60;
                    }
                
                    // Calculate Time Taken / Time Difference
                    $timeTaken = $time_given; // Assuming "time_given" should be used
                    $timeTakenDividedByDifference = $timeDifference / $timeTaken;
                
                    // Append the data to the results array
                    $results[] = array(
                        'icode' => $icode,
                        'mold_id' => $mold_id,
                        'mold_name' => $mold_name,
                        'cavity_id' => $cavity_id,
                        'cavity_name' => $cavity_name,
                        'found_start_time' => $found_start_time,
                        'found_end_time' => $found_end_time,
                        'time_given' => $time_given,
                        'timeDifference' => $timeDifference,
                        'user_end_time' => $user_end_time,
                        'user_start_time' => $user_start_time,
                        'tobe' => $timeTakenDividedByDifference,
                        'description' => getDescription($icode, $conn),
                    );
                } elseif ($found_start_time <= $user_end_time && $found_end_time >= $user_start_time) {
                    $user_end_timestamp = strtotime($user_end_time);
                    $user_start_timestamp = strtotime($user_start_time);
                    $found_start_timestamp = strtotime($found_start_time);
                    
                    // Ensure $found_end_timestamp is not greater than $user_end_timestamp
                    $found_end_timestamp = min(strtotime($found_end_time), $user_end_timestamp);
                
                    // Calculate the time difference in minutes
                    if ($user_start_timestamp > $found_start_timestamp) {
                        // If user_start_time is less than found_start_time, calculate from user_start_time
                        $timeDifference = ($user_end_timestamp - $user_start_timestamp) / 60;
                    } else {
                        // Otherwise, calculate from found_start_time
                        $timeDifference = ($found_end_timestamp - $found_start_timestamp) / 60;
                    }
                
                    // Calculate Time Taken / Time Difference
                    $timeTaken = $time_given; // Assuming "time_given" should be used
                    $timeTakenDividedByDifference = $timeDifference / $timeTaken;
                
                    // Append the data to the results array
                    $results[] = array(
                        'icode' => $icode,
                        'mold_id' => $mold_id,
                        'mold_name' => $mold_name,
                        'cavity_id' => $cavity_id,
                        'cavity_name' => $cavity_name,
                        'found_start_time' => $found_start_time,
                        'found_end_time' => $found_end_time,
                        'time_given' => $time_given,
                        'timeDifference' => $timeDifference,
                        'user_end_time' => $user_end_time,
                        'user_start_time' => $user_start_time,
                        'tobe' => $timeTakenDividedByDifference,
                        'description' => getDescription($icode, $conn),
                    );
                }

                
            }
        } else {
            echo "No records found in the database.";
        }

        echo '<table>';
        echo '<tr>';

     
        echo '<th>B-ATS 15</th>';
        echo '<th>B-BNS 24</th>';
        echo '<th>BG-BLS 12</th>';
        echo '<th>CG - BS 901</th>';
        echo '<th>C - SMS 501</th>';
        echo '<th>C-ATS 20</th>';
        echo '<th>C-SMS 702</th>';
        echo '<th>T - TRS 102</th>';
        echo '<th>T-ATNM S</th>';
        echo '<th>T-ATS 30</th>';
        echo '<th>T-ATS 35</th>';
        echo '<th>T-KS 40</th>';
        echo '<th>T-TRNMS 402</th>';
        echo '<th>T-TRNMS 402G</th>';
        echo '<th>T-TRS 202</th>';
        echo '</tr>';

        foreach ($results as $result) {
            $icode = $result['icode'];
        
            // Calculate the $plan variable here (assuming it's based on some logic with $result data)
            $plan = $result['tobe'];
        
        
            $columnsToMultiply = [
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'
            ];
        
            foreach ($columnsToMultiply as $columnName) {
                $sql = "SELECT `$columnName` FROM bom_new WHERE icode = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $icode);
                $stmt->execute();
                $stmt->bind_result($columnValue);
                $stmt->fetch();
                $stmt->close();
        
                $multipliedValue = $columnValue * round($plan);
               
        
                // Add the multiplied value to the corresponding column total
                $totalColumns[$columnName] += $multipliedValue;
            }
        
            echo '</tr>';
        }
        

       
        echo '<th</th>';
        foreach ($totalColumns as $totalValue) {
            echo '<th>' . $totalValue . '</th>';
        }
        echo '</tr>';

        echo '</table>';

        

    }

    // Function to fetch the description from the tire table
    function getDescription($icode, $conn) {
        $description = "";
        $description_query = "SELECT description FROM tire WHERE icode = '$icode'";
        $description_result = $conn->query($description_query);
        if ($description_result->num_rows > 0) {
            $description_row = $description_result->fetch_assoc();
            $description = $description_row['description'];
        }
        return $description;
    }

  // Query to retrieve work orders data and calculate total quantity
$sql = "SELECT icode, SUM(new) AS total_quantity, t_size FROM worder GROUP BY icode";
$result = $conn->query($sql);
$workOrders = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];
        $totalQuantity = $row['total_quantity'];
        $tSize = $row['t_size'];

        $workOrders[$icode] = array(
            'total_quantity' => $totalQuantity,
            't_size' => $tSize
        );
    }
}



