<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>bcompound2 Table</title>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }
    th {
        background-color: #f2f2f2;
    }
</style>
</head>
<body>

<?php
// Database connection parameters
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch data from bcompound2 table
$sql = "SELECT * FROM bcompound2";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Input Date</th>";
    echo "<th>Shift</th>";
    echo "<th>Compound Name</th>";
    echo "<th>Description</th>";
    echo "<th>CStock</th>";
    echo "<th>Batch</th>";
    echo "<th>Batch2</th>";
    echo "<th>Pallet</th>";
    echo "<th>Created At</th>";
    echo "<th>Weight</th>";
    echo "<th>Serial Number</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row["id"]."</td>";
        echo "<td>".$row["inputDate"]."</td>";
        echo "<td>".$row["shift"]."</td>";
        echo "<td>".$row["compound_name"]."</td>";
        echo "<td>".$row["description"]."</td>";
        echo "<td>".$row["cstock"]."</td>";
        echo "<td>".$row["batch"]."</td>";
        echo "<td>".$row["batch2"]."</td>";
        echo "<td>".$row["pallet"]."</td>";
        echo "<td>".$row["created_at"]."</td>";
        echo "<td>".$row["weight"]."</td>";
        echo "<td>".$row["serial_number"]."</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>

</body>
</html>
