


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
