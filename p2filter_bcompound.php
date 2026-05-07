
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

$result = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch = $_POST['batch'];
    $serial_number = $_POST['serial_number'];
    $inputDate = $_POST['inputDate'];
    $shift = $_POST['shift'];

    $sql = "SELECT * FROM `pbcompound_copy` WHERE 1=1";
    $params = array();
    $types = "";

    if (!empty($batch)) {
        $sql .= " AND `batch` = ?";
        $params[] = $batch;
        $types .= "s";
    }

    if (!empty($serial_number)) {
        $sql .= " AND `serial_number` = ?";
        $params[] = $serial_number;
        $types .= "s";
    }

    if (!empty($inputDate)) {
        $sql .= " AND `inputDate` = ?";
        $params[] = $inputDate;
        $types .= "s";
    }

    if (!empty($shift)) {
        $sql .= " AND `shift` = ?";
        $params[] = $shift;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Display bcompound Data</title>
    <script type="text/javascript">
        function toggle(source) {
            checkboxes = document.getElementsByName('selected_rows[]');
            for(var i=0, n=checkboxes.length;i<n;i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>
<body>
    <?php if ($result && $result->num_rows > 0) { ?>
    <form action="p2insert_bcompound.php" method="post">
        <table border='1'>
            <tr>
                <th><input type="checkbox" onClick="toggle(this)"> Select All</th>
                <th>IID</th>
                <th>ID</th>
                <th>Input Date</th>
                <th>Shift</th>
                <th>Compound Name</th>
                <th>Description</th>
                <th>CStock</th>
                <th>Batch</th>
                <th>Pallet</th>
                <th>Created At</th>
                <th>Weight</th>
                <th>Serial Number</th>
            </tr>
            <?php while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><input type="checkbox" name="selected_rows[]" value="<?php echo $row['iid']; ?>"></td>
                <td><?php echo $row['iid']; ?></td>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['inputDate']; ?></td>
                <td><?php echo $row['shift']; ?></td>
                <td><?php echo $row['compound_name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['cstock']; ?></td>
                <td><?php echo $row['batch']; ?></td>
                <td><?php echo $row['pallet']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td><?php echo $row['weight']; ?></td>
                <td><?php echo $row['serial_number']; ?></td>
            </tr>
            <?php } ?>
        </table>
        <br>
        <input type="submit" value="Insert Selected Rows">
    </form>
    <?php } else { ?>
    <p>No results found.</p>
    <?php } ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
