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
