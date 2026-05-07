
<?php
// Establish database connection
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve the production data with start and end dates
$sql = "SELECT DATE(start_date) AS production_date, icode
        FROM plannew
        ORDER BY production_date, start_date";
$result = mysqli_query($conn, $sql);

// Check if any production data exists
if (mysqli_num_rows($result) > 0) {
    // Store the production data in an array grouped by date
    $productionData = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $productionDate = $row['production_date'];
        $icode = $row['icode'];
        if (!isset($productionData[$productionDate])) {
            $productionData[$productionDate] = array();
        }
        $productionData[$productionDate][] = $icode;
    }

    // Display the production data
    foreach ($productionData as $date => $tireTypes) {
        echo "Date: $date<br>";
        echo "Tire Types: " . implode(", ", $tireTypes);
        echo "<br><br>";
    }
} else {
    echo "No production data found.";
}

// Close the database connection
mysqli_close($conn);
?>

?>
<!DOCTYPE html>
<html>
<head>
    <title>Tire Production</title>
</head>
<body>
    <h1>Tire Production</h1>
    <form method="POST" action="plannew57.php">
        <label for="desired_date">Enter Desired Date:</label>
        <input type="date" id="desired_date" name="desired_date" required>
        <br>
        <button type="submit">Get Tire Types</button>
    </form>
</body>
</html>
