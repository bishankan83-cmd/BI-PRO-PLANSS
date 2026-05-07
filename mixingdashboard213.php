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
    <h1>Output Compound Production Details</h1>
    
    <div class="search-form">
        <input type="text" id="inputDateFilter" placeholder="Search by Input Date...">
        <input type="text" id="shiftFilter" placeholder="Search by Shift...">
        <input type="text" id="compoundNameFilter" placeholder="Search by Compound Name...">
        <input type="text" id="serialNumberFilter" placeholder="Search by Serial Number...">
        <input type="text" id="palletFilter" placeholder="Search by Pallet...">
        <input type="text" id="outputDateFilter" placeholder="Search by Output Date...">
    </div>

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

    // SQL query to select all data from the pbcompound_copy2 table
    $sql = "SELECT * FROM pbcompound_copy2";
    $result = $conn->query($sql);

    // Create a table if there are results
    if ($result->num_rows > 0) {
        echo "<table id='dataTable'>";
        echo "<thead>";
        echo "<tr>";
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
        echo "<th>Output Date</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody id='dataBody'>";
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
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
            echo "<td>".$row["output_date"]."</td>";
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

<script>
    // JavaScript to handle dynamic filtering
    document.addEventListener('DOMContentLoaded', function() {
        var filters = {
            inputDate: document.getElementById('inputDateFilter'),
            shift: document.getElementById('shiftFilter'),
            compoundName: document.getElementById('compoundNameFilter'),
            serialNumber: document.getElementById('serialNumberFilter'),
            pallet: document.getElementById('palletFilter'),
            outputDate: document.getElementById('outputDateFilter')
        };
        var dataBody = document.getElementById('dataBody');

        Object.values(filters).forEach(function(input) {
            input.addEventListener('input', function() {
                var filterValues = {
                    inputDate: filters.inputDate.value.toLowerCase(),
                    shift: filters.shift.value.toLowerCase(),
                    compoundName: filters.compoundName.value.toLowerCase(),
                    serialNumber: filters.serialNumber.value.toLowerCase(),
                    pallet: filters.pallet.value.toLowerCase(),
                    outputDate: filters.outputDate.value.toLowerCase()
                };
                
                var rows = dataBody.getElementsByTagName('tr');
                Array.from(rows).forEach(function(row) {
                    var cells = row.getElementsByTagName('td');
                    var match = true;

                    // Check each column against the filter values
                    if (cells[0].textContent.toLowerCase().indexOf(filterValues.inputDate) === -1) match = false;
                    if (cells[1].textContent.toLowerCase().indexOf(filterValues.shift) === -1) match = false;
                    if (cells[2].textContent.toLowerCase().indexOf(filterValues.compoundName) === -1) match = false;
                    if (cells[9].textContent.toLowerCase().indexOf(filterValues.serialNumber) === -1) match = false;
                    if (cells[6].textContent.toLowerCase().indexOf(filterValues.pallet) === -1) match = false;
                    if (cells[10].textContent.toLowerCase().indexOf(filterValues.outputDate) === -1) match = false;

                    row.style.display = match ? '' : 'none';
                });
            });
        });
    });
</script>

</body>
</html>
