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
$copy2_data = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch = $_POST['batch'];
    $serial_number = $_POST['serial_number'];
    $inputDate = $_POST['inputDate'];
    $shift = $_POST['shift'];

    // Query for bcompound
    $sql = "SELECT * FROM `bcompound` WHERE 1=1";
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

    // Query for bcompound_copy2
    $sql_copy2 = "SELECT * FROM `bcompound_copy2`";
    $stmt_copy2 = $conn->prepare($sql_copy2);
    $stmt_copy2->execute();
    $result_copy2 = $stmt_copy2->get_result();

    while ($row_copy2 = $result_copy2->fetch_assoc()) {
        $copy2_data[] = $row_copy2;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Display bcompound Data</title>
    <style>
        /* General styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="date"],
        select {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            cursor: pointer;
        }

        table th {
            background-color: #F28018;
            color: #ffffff;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #ddd;
        }

        .highlighted-row {
            background-color: green !important;
            color: white;
        }

        .btn-container {
            margin-top: 20px;
            text-align: center;
        }

        input[type="button"],
        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="button"]:hover,
        input[type="submit"]:hover {
            background-color: #333333;
        }
    </style>
    <script type="text/javascript">
        function toggle(source) {
            checkboxes = document.getElementsByName('selected_rows[]');
            for(var i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Display Filter Data</h1>

        <?php if ($result && $result->num_rows > 0) { ?>
        <form action="insert_bcompound.php" method="post">
            <table>
                <tr>
                    <th><input type="checkbox" onClick="toggle(this)"> Select All</th>
                    
                    <th>Input Date</th>
                    <th>Shift</th>
                    <th>Compound Name</th>
                    <th>Description</th>
                    <th>CStock</th>
                    <th>Batch</th>
                    <th>Pallet</th>
                    <th>Created At</th>
                    <th>Weight</th>
                    <th>Job Number</th>
                </tr>
                <?php while($row = $result->fetch_assoc()) { 
                    $highlight = false;
                    foreach ($copy2_data as $copy2_row) {
                        if ($row['inputDate'] === $copy2_row['inputDate'] &&
                            $row['shift'] === $copy2_row['shift'] &&
                            $row['compound_name'] === $copy2_row['compound_name'] &&
                            $row['description'] === $copy2_row['description'] &&
                            $row['cstock'] === $copy2_row['cstock'] &&
                            $row['batch'] === $copy2_row['batch'] &&
                            $row['pallet'] === $copy2_row['pallet'] &&
                            $row['weight'] === $copy2_row['weight'] &&
                            $row['serial_number'] === $copy2_row['serial_number']) {
                            $highlight = true;
                            break;
                        }
                    }
                ?>
                <tr class="<?php echo $highlight ? 'highlighted-row' : ''; ?>">
                    <td><input type="checkbox" name="selected_rows[]" value="<?php echo $row['iid']; ?>"></td>
                   
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
            <div class="btn-container">
                <input type="submit" value="Insert Selected Rows">
            </div>
        </form>
        <?php } else { ?>
        <p>No results found.</p>
        <?php } ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$stmt_copy2->close();
$conn->close();
?>
