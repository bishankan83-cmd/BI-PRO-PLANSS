<?php
// MySQL connection details
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

// Handle form submission
$inputDate = $_POST['inputDate'] ?? '';
$shift = $_POST['shift'] ?? '';
$compoundName = $_POST['compoundName'] ?? '';
$serialNumber = $_POST['serialNumber'] ?? '';
$pallet = $_POST['pallet'] ?? '';

// Define the SQL query with filters
$sql = "
    SELECT a.*
    FROM pbcompound_copy a
    LEFT JOIN pbcompound_copy2 b
    ON a.iid = b.iid
       AND a.id = b.id
       AND a.inputDate = b.inputDate
       AND a.shift = b.shift
       AND a.compound_name = b.compound_name
       AND a.description = b.description
       AND a.cstock = b.cstock
       AND a.batch = b.batch
       AND a.pallet = b.pallet
       AND a.weight = b.weight
       AND a.serial_number = b.serial_number
    WHERE b.iid IS NULL
";

// Add filters to the query if they are set
if (!empty($inputDate)) {
    $sql .= " AND a.inputDate = '$inputDate'";
}
if (!empty($shift)) {
    $sql .= " AND a.shift = '$shift'";
}
if (!empty($compoundName)) {
    $sql .= " AND a.compound_name = '$compoundName'";
}
if (!empty($serialNumber)) {
    $sql .= " AND a.serial_number = '$serialNumber'";
}
if (!empty($pallet)) {
    $sql .= " AND a.pallet = '$pallet'";
}

// Execute the query
$result = $conn->query($sql);

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compound Production Details</title>
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

        .search-form {
            text-align: center;
            margin: 10px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        .search-form label {
            margin-right: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Compound Stock</h1>
    
    <!-- Filter Form -->
    <form method="POST" class="search-form">
        <label for="inputDate">Input Date:</label>
        <input type="date" id="inputDate" name="inputDate" value="<?php echo htmlspecialchars($inputDate); ?>">
        
        <label for="shift">Shift:</label>
        <input type="text" id="shift" name="shift" value="<?php echo htmlspecialchars($shift); ?>">
        
        <label for="compoundName">Compound Name:</label>
        <input type="text" id="compoundName" name="compoundName" value="<?php echo htmlspecialchars($compoundName); ?>">
        
        <label for="serialNumber">Serial Number:</label>
        <input type="text" id="serialNumber" name="serialNumber" value="<?php echo htmlspecialchars($serialNumber); ?>">
        
        <label for="pallet">Pallet:</label>
        <input type="text" id="pallet" name="pallet" value="<?php echo htmlspecialchars($pallet); ?>">

        <input type="submit" value="Filter">
    </form>

    <!-- Data Table -->
    <?php
    if ($result->num_rows > 0) {
        echo "<table id='dataTable'>
                <thead>
                    <tr>
                        <th>iid</th>
                        <th>id</th>
                        <th>inputDate</th>
                        <th>shift</th>
                        <th>compound_name</th>
                        <th>description</th>
                        <th>cstock</th>
                        <th>batch</th>
                        <th>pallet</th>
                        <th>created_at</th>
                        <th>weight</th>
                        <th>serial_number</th>
                    </tr>
                </thead>
                <tbody id='dataBody'>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['iid']}</td>
                    <td>{$row['id']}</td>
                    <td>{$row['inputDate']}</td>
                    <td>{$row['shift']}</td>
                    <td>{$row['compound_name']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['cstock']}</td>
                    <td>{$row['batch']}</td>
                    <td>{$row['pallet']}</td>
                    <td>{$row['created_at']}</td>
                    <td>{$row['weight']}</td>
                    <td>{$row['serial_number']}</td>
                </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No records found</p>";
    }

    // Close connection
    $conn->close();
    ?>
</div>

<script>
    // JavaScript to handle dynamic filtering
    document.addEventListener('DOMContentLoaded', function() {
        var filters = {
            inputDate: document.getElementById('inputDate'),
            shift: document.getElementById('shift'),
            compoundName: document.getElementById('compoundName'),
            serialNumber: document.getElementById('serialNumber'),
            pallet: document.getElementById('pallet')
        };
        var dataBody = document.getElementById('dataBody');

        Object.values(filters).forEach(function(input) {
            input.addEventListener('input', function() {
                var filterValues = {
                    inputDate: filters.inputDate.value.toLowerCase(),
                    shift: filters.shift.value.toLowerCase(),
                    compoundName: filters.compoundName.value.toLowerCase(),
                    serialNumber: filters.serialNumber.value.toLowerCase(),
                    pallet: filters.pallet.value.toLowerCase()
                };
                
                var rows = dataBody.getElementsByTagName('tr');
                Array.from(rows).forEach(function(row) {
                    var cells = row.getElementsByTagName('td');
                    var match = true;

                    // Check each column against the filter values
                    if (filterValues.inputDate && cells[2].textContent.toLowerCase().indexOf(filterValues.inputDate) === -1) match = false;
                    if (filterValues.shift && cells[3].textContent.toLowerCase().indexOf(filterValues.shift) === -1) match = false;
                    if (filterValues.compoundName && cells[4].textContent.toLowerCase().indexOf(filterValues.compoundName) === -1) match = false;
                    if (filterValues.serialNumber && cells[10].textContent.toLowerCase().indexOf(filterValues.serialNumber) === -1) match = false;
                    if (filterValues.pallet && cells[8].textContent.toLowerCase().indexOf(filterValues.pallet) === -1) match = false;

                    row.style.display = match ? '' : 'none';
                });
            });
        });
    });
</script>

</body>
</html>
