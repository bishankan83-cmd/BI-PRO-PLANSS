<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM worder WHERE 1=1";

if (!empty($_GET['customer'])) {
    $customer = $conn->real_escape_string($_GET['customer']);
    $sql .= " AND Customer LIKE '%$customer%'";
}

if (!empty($_GET['wono'])) {
    $wono = $conn->real_escape_string($_GET['wono']);
    $sql .= " AND wono LIKE '%$wono%'";
}

if (!empty($_GET['ref'])) {
    $ref = $conn->real_escape_string($_GET['ref']);
    $sql .= " AND ref LIKE '%$ref%'";
}

if (!empty($_GET['erp'])) {
    $erp = $conn->real_escape_string($_GET['erp']);
    $sql .= " AND erp LIKE '%$erp%'";
}

if (!empty($_GET['icode'])) {
    $icode = $conn->real_escape_string($_GET['icode']);
    $sql .= " AND icode LIKE '%$icode%'";
}

if (!empty($_GET['t_size'])) {
    $t_size = $conn->real_escape_string($_GET['t_size']);
    $sql .= " AND t_size LIKE '%$t_size%'";
}

if (!empty($_GET['brand'])) {
    $brand = $conn->real_escape_string($_GET['brand']);
    $sql .= " AND brand LIKE '%$brand%'";
}

if (!empty($_GET['col'])) {
    $col = $conn->real_escape_string($_GET['col']);
    $sql .= " AND col LIKE '%$col%'";
}

if (!empty($_GET['fit'])) {
    $fit = $conn->real_escape_string($_GET['fit']);
    $sql .= " AND fit LIKE '%$fit%'";
}

if (!empty($_GET['rim'])) {
    $rim = $conn->real_escape_string($_GET['rim']);
    $sql .= " AND rim LIKE '%$rim%'";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>WO No</th>
            <th>Reference</th>
            <th>ERP</th>
            <th>Item Code</th>
            <th>Size</th>
            <th>Brand</th>
            <th>Color</th>
            <th>Fit</th>
            <th>Rim</th>
            <th>Consumption</th>
            <th>Fabric Weight</th>
            <th>PTV</th>
            <th>New</th>
            <th>CBM</th>
            <th>KGS</th>
          </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . $row["date"] . "</td>
                <td>" . $row["Customer"] . "</td>
                <td>" . $row["wono"] . "</td>
                <td>" . $row["ref"] . "</td>
                <td>" . $row["erp"] . "</td>
                <td>" . $row["icode"] . "</td>
                <td>" . $row["t_size"] . "</td>
                <td>" . $row["brand"] . "</td>
                <td>" . $row["col"] . "</td>
                <td>" . $row["fit"] . "</td>
                <td>" . $row["rim"] . "</td>
                <td>" . $row["cons"] . "</td>
                <td>" . $row["fweight"] . "</td>
                <td>" . $row["ptv"] . "</td>
                <td>" . $row["new"] . "</td>
                <td>" . $row["cbm"] . "</td>
                <td>" . $row["kgs"] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

$conn->close();
?>
