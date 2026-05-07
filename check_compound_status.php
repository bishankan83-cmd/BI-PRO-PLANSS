<?php
// Database connection details
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

// SQL query to get records
$sql = "
    -- Records in bcompound or bcompound76 that are also in bcompound_copy2
    SELECT 
        'bcompound' AS table_name,
        bcomp.inputDate,
        bcomp.shift,
        bcomp.compound_name,
        bcomp.batch,
        bcomp.pallet,
        bcomp.weight,
        bcomp.serial_number,
        'In both tables' AS status
    FROM 
        bcompound bcomp
    INNER JOIN 
        bcompound_copy2 bcomp2
    ON 
        bcomp.inputDate = bcomp2.inputDate
        AND bcomp.shift = bcomp2.shift
        AND bcomp.compound_name = bcomp2.compound_name
        AND bcomp.batch = bcomp2.batch
        AND bcomp.pallet = bcomp2.pallet
        AND bcomp.weight = bcomp2.weight
        AND bcomp.serial_number = bcomp2.serial_number

    UNION ALL

    SELECT 
        'bcompound76' AS table_name,
        bcomp76.inputDate,
        bcomp76.shift,
        bcomp76.compound_name,
        bcomp76.batch,
        bcomp76.pallet,
        bcomp76.weight,
        bcomp76.serial_number,
        'In both tables' AS status
    FROM 
        bcompound76 bcomp76
    INNER JOIN 
        bcompound_copy2 bcomp2
    ON 
        bcomp76.inputDate = bcomp2.inputDate
        AND bcomp76.shift = bcomp2.shift
        AND bcomp76.compound_name = bcomp2.compound_name
        AND bcomp76.batch = bcomp2.batch
        AND bcomp76.pallet = bcomp2.pallet
        AND bcomp76.weight = bcomp2.weight
        AND bcomp76.serial_number = bcomp2.serial_number

    UNION ALL

    -- Records in bcompound or bcompound76 that are not in bcompound_copy2
    SELECT 
        'bcompound' AS table_name,
        bcomp.inputDate,
        bcomp.shift,
        bcomp.compound_name,
        bcomp.batch,
        bcomp.pallet,
        bcomp.weight,
        bcomp.serial_number,
        'Only in bcompound' AS status
    FROM 
        bcompound bcomp
    LEFT JOIN 
        bcompound_copy2 bcomp2
    ON 
        bcomp.inputDate = bcomp2.inputDate
        AND bcomp.shift = bcomp2.shift
        AND bcomp.compound_name = bcomp2.compound_name
        AND bcomp.batch = bcomp2.batch
        AND bcomp.pallet = bcomp2.pallet
        AND bcomp.weight = bcomp2.weight
        AND bcomp.serial_number = bcomp2.serial_number
    WHERE 
        bcomp2.serial_number IS NULL

    UNION ALL

    SELECT 
        'bcompound76' AS table_name,
        bcomp76.inputDate,
        bcomp76.shift,
        bcomp76.compound_name,
        bcomp76.batch,
        bcomp76.pallet,
        bcomp76.weight,
        bcomp76.serial_number,
        'Only in bcompound76' AS status
    FROM 
        bcompound76 bcomp76
    LEFT JOIN 
        bcompound_copy2 bcomp2
    ON 
        bcomp76.inputDate = bcomp2.inputDate
        AND bcomp76.shift = bcomp2.shift
        AND bcomp76.compound_name = bcomp2.compound_name
        AND bcomp76.batch = bcomp2.batch
        AND bcomp76.pallet = bcomp2.pallet
        AND bcomp76.weight = bcomp2.weight
        AND bcomp76.serial_number = bcomp2.serial_number
    WHERE 
        bcomp2.serial_number IS NULL;
";

// Execute the query
$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Display results in a table
echo "
    <form id='filterForm'>
        <label>Table Name: <input type='text' id='filterTableName'></label>
        <label>Input Date: <input type='text' id='filterInputDate'></label>
        <label>Shift: <input type='text' id='filterShift'></label>
        <label>Compound Name: <input type='text' id='filterCompoundName'></label>
        <label>Batch: <input type='text' id='filterBatch'></label>
        <label>Pallet: <input type='text' id='filterPallet'></label>
        <label>Weight: <input type='text' id='filterWeight'></label>
        <label>Serial Number: <input type='text' id='filterSerialNumber'></label>
        <label>Status: <input type='text' id='filterStatus'></label>
    </form>
    <table border='1' id='dataTable'>
        <thead>
            <tr>
                <th>Table Name</th>
                <th>Input Date</th>
                <th>Shift</th>
                <th>Compound Name</th>
                <th>Batch</th>
                <th>Pallet</th>
                <th>Weight</th>
                <th>Serial Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>" . htmlspecialchars($row['table_name']) . "</td>
        <td>" . htmlspecialchars($row['inputDate']) . "</td>
        <td>" . htmlspecialchars($row['shift']) . "</td>
        <td>" . htmlspecialchars($row['compound_name']) . "</td>
        <td>" . htmlspecialchars($row['batch']) . "</td>
        <td>" . htmlspecialchars($row['pallet']) . "</td>
        <td>" . htmlspecialchars($row['weight']) . "</td>
        <td>" . htmlspecialchars($row['serial_number']) . "</td>
        <td>" . htmlspecialchars($row['status']) . "</td>
    </tr>";
}

echo "</tbody>
    </table>";

// Close connection
$conn->close();
?>

<script>
document.querySelectorAll('#filterForm input').forEach(input => {
    input.addEventListener('input', filterTable);
});

function filterTable() {
    var table = document.getElementById('dataTable');
    var inputs = document.querySelectorAll('#filterForm input');
    var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var cells = row.getElementsByTagName('td');
        var showRow = true;

        inputs.forEach((input, index) => {
            var filter = input.value.toLowerCase();
            var cellText = cells[index] ? cells[index].textContent.toLowerCase() : '';
            if (filter && cellText.indexOf(filter) === -1) {
                showRow = false;
            }
        });

        row.style.display = showRow ? '' : 'none';
    }
}
</script>
