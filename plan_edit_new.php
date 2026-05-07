<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit and Filter Plannew Table</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        input {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
    <script>
        function editRow(row) {
            let cells = row.querySelectorAll("td[data-editable='true']");
            cells.forEach(cell => {
                let currentText = cell.innerText;
                cell.innerHTML = `<input type="text" value="${currentText}">`;
            });
            row.querySelector(".edit-btn").style.display = 'none';
            row.querySelector(".save-btn").style.display = 'inline';
        }

        function saveRow(row) {
            let id = row.querySelector(".id").innerText;
            let inputs = row.querySelectorAll("input");
            let data = { id: id };

            inputs.forEach(input => {
                data[input.parentElement.dataset.column] = input.value;
            });

            fetch('search1.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    inputs.forEach(input => {
                        input.parentElement.innerText = input.value;
                    });
                    row.querySelector(".edit-btn").style.display = 'inline';
                    row.querySelector(".save-btn").style.display = 'none';
                } else {
                    alert('Error updating record');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }

        function filterTable() {
            let input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("filterInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("plannewTable");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                tds = tr[i].getElementsByTagName("td");
                let rowMatchesFilter = false;
                for (let j = 0; j < tds.length; j++) {
                    td = tds[j];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            rowMatchesFilter = true;
                            break;
                        }
                    }
                }
                if (rowMatchesFilter) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
</head>
<body>

<input type="text" id="filterInput" onkeyup="filterTable()" placeholder="Search for anything...">

<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM plannew";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table id='plannewTable'>
            <tr>
                
                <th>ERP</th>
                <th>Customer</th>
                <th>Item Code</th>
                <th>Description</th>
                <th>To Be</th>
                <th>Press</th>
                <th>Press Name</th>
                <th>Mold ID</th>
                <th>Mold Name</th>
                <th>Cavity ID</th>
                <th>Cavity Name</th>
                <th>Cuing Group ID</th>
                <th>Cuing Group Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Tires Per Mold</th>
                <th>Actions</th>
            </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td class='id'>{$row['id']}</td>
                
                <td data-editable='true' data-column='erp'>{$row['erp']}</td>
                <td data-editable='true' data-column='Customer'>{$row['Customer']}</td>
                <td data-editable='true' data-column='icode'>{$row['icode']}</td>
                <td data-editable='true' data-column='description'>{$row['description']}</td>
                <td data-editable='true' data-column='tobe'>{$row['tobe']}</td>
                <td data-editable='true' data-column='press'>{$row['press']}</td>
                <td data-editable='true' data-column='press_name'>{$row['press_name']}</td>
                <td data-editable='true' data-column='mold_id'>{$row['mold_id']}</td>
                <td data-editable='true' data-column='mold_name'>{$row['mold_name']}</td>
                <td data-editable='true' data-column='cavity_id'>{$row['cavity_id']}</td>
                <td data-editable='true' data-column='cavity_name'>{$row['cavity_name']}</td>
                <td data-editable='true' data-column='cuing_group_id'>{$row['cuing_group_id']}</td>
                <td data-editable='true' data-column='cuing_group_name'>{$row['cuing_group_name']}</td>
                <td data-editable='true' data-column='start_date'>{$row['start_date']}</td>
                <td data-editable='true' data-column='end_date'>{$row['end_date']}</td>
                <td data-editable='true' data-column='tires_per_mold'>{$row['tires_per_mold']}</td>
                <td>
                    <button class='edit-btn' onclick='editRow(this.closest(\"tr\"))'>Edit</button>
                    <button class='save-btn' style='display:none;' onclick='saveRow(this.closest(\"tr\"))'>Save</button>
                </td>
            </tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

$conn->close();
?>

</body>
</html>
