



<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT curing_group FROM curing"; // Fetching distinct curing groups
$result_groups = $conn->query($sql);

// Fetching all curing groups into an array
$curing_groups = [];
while ($row = $result_groups->fetch_assoc()) {
    $curing_groups[] = $row["curing_group"];
}

// Define colors for different curing groups
$colors = ['lightcoral', 'lightgreen', 'lightblue', 'lightyellow', 'lightcyan', 'lightpink', 'lightsalmon'];
$curing_group_colors = [];
$color_index = 0;

echo "
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
";

// Filter dropdown for curing groups
echo "<form method='get'>
        <label for='curing_group'>Select Curing Group:</label>
        <select id='curing_group' name='curing_group'>
            <option value=''>All</option>";
foreach ($curing_groups as $group) {
    echo "<option value='" . htmlspecialchars($group) . "'>" . htmlspecialchars($group) . "</option>";
}
echo "</select>
        <input type='submit' value='Apply Filter'>
      </form>";

$curing_group_filter = isset($_GET['curing_group']) ? $_GET['curing_group'] : '';

$sql = "SELECT * FROM curing";
if (!empty($curing_group_filter)) {
    $sql .= " WHERE curing_group = '$curing_group_filter'";
}
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Curing Group</th>
                <th>Tire Size</th>
                <th>Curing Time (Black)</th>
                <th>Curing Time (NM)</th>
                <th>Pressure</th>
            </tr>";
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $curing_group = $row["curing_group"];
        if (!isset($curing_group_colors[$curing_group])) {
            $curing_group_colors[$curing_group] = $colors[$color_index % count($colors)];
            $color_index++;
        }
        $color = $curing_group_colors[$curing_group];
        echo "<tr style='background-color: " . htmlspecialchars($color) . ";'>
                <td>" . htmlspecialchars($row["curing_group"]) . "</td>
                <td>" . htmlspecialchars($row["tire_size"]) . "</td>
                <td>" . htmlspecialchars($row["curing_time_black"]) . "</td>
                <td>" . htmlspecialchars($row["curing_time_NM"]) . "</td>
                <td>" . htmlspecialchars($row["press"]) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

echo "
</body>
</html>
";

$conn->close();
?>
