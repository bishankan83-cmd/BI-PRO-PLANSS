<?php
// Database connection details
$servername = "localhost"; // Server name and port
$username = "planatir_task_managemen"; // Your username
$password = "Bishan@1919"; // Your password
$dbname = "planatir_task_managemen"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filters from POST request
$icode_filter = isset($_POST['icode']) ? $_POST['icode'] : '';
$description_filter = isset($_POST['description']) ? $_POST['description'] : '';
$mold_id_filter = isset($_POST['mold_id']) ? $_POST['mold_id'] : '';

// SQL query to get data
$sql = "
    SELECT 
        tm.icode, 
        td.description, 
        GROUP_CONCAT(tm.mold_id ORDER BY tm.mold_id ASC) AS mold_ids 
    FROM 
        tire_mold tm
    JOIN 
        tire_details td ON tm.icode = td.icode
    WHERE 
        tm.icode != 0
";

// Apply filters
if (!empty($icode_filter)) {
    $sql .= " AND tm.icode = '" . $conn->real_escape_string($icode_filter) . "'";
}
if (!empty($description_filter)) {
    $sql .= " AND td.description LIKE '%" . $conn->real_escape_string($description_filter) . "%'";
}
if (!empty($mold_id_filter)) {
    $sql .= " AND tm.mold_id LIKE '%" . $conn->real_escape_string($mold_id_filter) . "%'";
}

$sql .= " GROUP BY tm.icode, td.description";
$result = $conn->query($sql);

// Set headers for the Excel file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="data_export.xls"');

// Output data
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Icode</th><th>Description</th><th>Mold IDs</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['icode'] . "</td><td>" . $row['description'] . "</td><td>" . $row['mold_ids'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No results found.";
}

// Close connection
$conn->close();
?>
