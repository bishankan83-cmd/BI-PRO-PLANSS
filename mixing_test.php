<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bcompound2 Table</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #F28018;
            color: #fff;
            font-weight: bold;
        }

        table td {
            vertical-align: top;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Compound Production Details</h1>

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

    // New SQL query using recursive CTE for batch ranges
   // SQL query
$sql = "
WITH RECURSIVE BatchRange AS (
  SELECT `id`, `batch`, `batch2`
  FROM `bcompound76`
  UNION ALL
  SELECT `id`, `batch` + 1, `batch2`
  FROM BatchRange
  WHERE `batch` < `batch2`
)
SELECT b.`id`, b.`inputDate`, b.`shift`, b.`compound_name`, b.`description`, b.`cstock`, br.`batch`, b.`pallet`, b.`created_at`, b.`weight`, b.`serial_number`
FROM BatchRange br
JOIN `bcompound76` b ON br.`id` = b.`id` AND br.`batch` BETWEEN b.`batch` AND b.`batch2`
ORDER BY b.`id`, br.`batch`;
";

    // Perform query
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table id='dataTable'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Input Date</th>";
        echo "<th>Shift</th>";
        echo "<th>Compound Name</th>";
        echo "<th>Description</th>";
        echo "<th>CStock</th>";
        echo "<th>Batch</th>";
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
            echo "<td>".$row["pallet"]."</td>";
            echo "<td>".$row["created_at"]."</td>";
            echo "<td>".$row["weight"]."</td>";
            echo "<td>".$row["serial_number"]."</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p>No results found.</p>";
    }

    // Close connection
    $conn->close();
    ?>
</div>

</body>
</html>
