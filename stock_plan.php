<?php
// Database connection details
$servername = "localhost:3306"; // Database server address and port
$username = "planatir_task_managemen"; // Database username
$password = "Bishan@1919"; // Database password
$database = "planatir_task_managemen"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch data from the calculated_data_stock table
$sql = "SELECT * FROM calculated_data_stock";
$result = $conn->query($sql);

// Check if the query returns any results
if ($result->num_rows > 0) {
    // Start an HTML table to display the data
    echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>ERP</th>
                <th>ICODE</th>
                <th>Description</th>
                <th>Mold ID</th>
                <th>Cavity ID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Plan</th>
                <th>Calculated Green Tire Weight</th>
                <th>Calculated STGreen Tire Weight</th>
            </tr>";

    // Output data for each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . $row["date"] . "</td>
                <td>" . $row["erp"] . "</td>
                <td>" . $row["icode"] . "</td>
                <td>" . $row["description"] . "</td>
                <td>" . $row["mold_id"] . "</td>
                <td>" . $row["cavity_id"] . "</td>
                <td>" . $row["start_date"] . "</td>
                <td>" . $row["end_date"] . "</td>
                <td>" . $row["plan"] . "</td>
                <td>" . $row["calculated_green_tire_weight"] . "</td>
                <td>" . $row["calculated_stgreen_tire_weight"] . "</td>
              </tr>";
    }

    // Close the table tag
    echo "</table>";
} else {
    // No results found
    echo "No results found.";
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data</title>
</head>
<body>

    <!-- Form to trigger data insertion -->
    <form action="stock_plan_add.php" method="post">
        <button type="submit" name="insert_data" class="btn btn-primary">Insert Data</button>
    </form>

</body>
</html>
