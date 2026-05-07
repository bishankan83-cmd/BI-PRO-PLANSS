

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h2 {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1>Mold Changing List</h1>

<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to retrieve matching mold_ids, cavity_ids, pressids, their corresponding first_start_date, press_name, and mold_name
$sql = "
SELECT m1.mold_id, m1.cavity_id, m1.icode, pc.press_id, p.press_name, m1.first_start_date, c.cavity_name, m.mold_name, sp.description
FROM match_table m1
JOIN match_table m2 ON m1.cavity_id = m2.cavity_id
JOIN cavity c ON m1.cavity_id = c.cavity_id
JOIN press_cavity pc ON m1.cavity_id = pc.cavity_id
JOIN press p ON pc.press_id = p.press_id
JOIN mold m ON m1.mold_id = m.mold_id
JOIN selectpress sp ON m1.icode = sp.icode
WHERE m1.id != m2.id
ORDER BY m.mold_name;
";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$currentMoldName = null;
$hasMultipleOccurrences = false;

while ($row = $result->fetch_assoc()) {
    if ($currentMoldName !== $row["mold_name"]) {
        // Check if the current mold_name has multiple occurrences
        if ($hasMultipleOccurrences) {
            echo "</table>";
        }

        $currentMoldName = $row["mold_name"];
        $hasMultipleOccurrences = false;
    }

    // Check if there is more than one occurrence of the current mold_name
    $sqlCount = "SELECT COUNT(*) AS count FROM match_table WHERE mold_id = '{$row["mold_id"]}'";
    $countResult = $conn->query($sqlCount);
    $countRow = $countResult->fetch_assoc();

    if ($countRow["count"] > 1) {
        if (!$hasMultipleOccurrences) {
            echo "<h2>$currentMoldName</h2>";
            echo "<table>";
            echo "<tr><th>icode</th><th>Description</th><th>Press Name</th><th>Cavity Name</th><th>Mold Name</th><th>First Start Date</th></tr>";
            $hasMultipleOccurrences = true;
        }

        echo "<tr>";
        echo "<td>" . $row["icode"] . "</td>";
        echo "<td>" . $row["description"] . "</td>";
        echo "<td>" . $row["press_name"] . "</td>";
        echo "<td>" . $row["cavity_name"] . "</td>";
        echo "<td>" . $row["mold_name"] . "</td>";
        echo "<td>" . $row["first_start_date"] . "</td>";
        echo "</tr>";
    }
}

if ($hasMultipleOccurrences) {
    echo "</table>";
} else {
    //echo "There is no mold_name with more than one occurrence.";
}

// Close the database connection
$conn->close();
?>
</body>
</html>