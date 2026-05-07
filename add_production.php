<!DOCTYPE html>
<html>
<head>
    <title>Inventory Input Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        form {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            margin: 0 auto;
            max-width: 800px; /* Increase the width to accommodate a larger table */
            padding: 20px;
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 90%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        table {
           
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        th:nth-child(2)
        {
            /* Increase the width of the Description column */
            width: 50%;
        }

        td:nth-child(2) {
            /* Increase the width of the Description column */
            width: 45%;
        }

        input[type="button"],
        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
           
            border-radius: 3px;
            cursor: pointer;
        }
/* Add space between Description and Number Of Tire columns */
td:nth-child(2) {
    margin-right: 20px; /* Adjust the value to your preference */
}

        input[type="button"]:hover,
        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .btn-container {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Enter Daily Production</h1>
    <form id="data-form" action="adinsert.php" method="post">
        <label for="inputDate">Date:</label>
        <input type="date" id="inputDate" name="inputDate" required>
        
        <label for="shift">Shift:</label>
        <select name="shift" id="shift">
        <option value="DAY A">DAY A</option>
            <option value="DAY B">DAY B</option>
            <option value="DAY C">DAY C</option>

            <option value="NIGHT A">NIGHT A</option>
            <option value="NIGHT B">NIGHT B</option>
            <option value="NIGHT C">NIGHT C</option>
        </select>

        <table id="data-table" border="1">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Number Of tire</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="icode[]" onchange="fetchDescription(this)">
                            <option value="">Select an Item Code</option>
                            <?php
                            // PHP code to fetch item codes from the 'tire' table
                            $hostname = 'localhost';
                            $username = 'planatir_task_managemen';
                            $password = 'Bishan@1919';
                            $database = 'planatir_task_managemen';
                            
                            $conn = new mysqli($hostname, $username, $password, $database);

                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }

                            $sql = "SELECT icode FROM tire";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $itemCode = $row['icode'];
                                    echo "<option value='$itemCode'>$itemCode</option>";
                                }
                            }

                            $conn->close();
                            ?>
                        </select>
                    </td>
                    <td><input type="text" name="description[]" readonly></td>
                    <td><input type="text" name="cstock[]" required></td>
                </tr>
            </tbody>
        </table>
        <div class="btn-container">
            <input type="button" value="Add Row" onclick="addRow()">
            <input type="submit" value="Submit to Database">
        </div>
    </form>

    <script>
        // Function to add a new row to the table
        function addRow() {
            const table = document.getElementById("data-table").getElementsByTagName('tbody')[0];
            const newRow = table.insertRow(table.rows.length);
            const cell1 = newRow.insertCell(0);
            const cell2 = newRow.insertCell(1);
            const cell3 = newRow.insertCell(2);

            const itemCodeSelect = document.createElement("select");
            itemCodeSelect.name = "icode[]";
            itemCodeSelect.onchange = function() {
                fetchDescription(itemCodeSelect);
            }

            // Clone the options from the existing item code select dropdown
            const existingSelect = document.querySelector("select[name='icode[]']");
            for (const option of existingSelect.options) {
                const clonedOption = option.cloneNode(true);
                itemCodeSelect.appendChild(clonedOption);
            }

            cell1.appendChild(itemCodeSelect);

            const descriptionInput = document.createElement("input");
            descriptionInput.type = "text";
            descriptionInput.name = "description[]";
            descriptionInput.readOnly = true;
            cell2.appendChild(descriptionInput);

            const tireInput = document.createElement("input");
            tireInput.type = "text";
            tireInput.name = "cstock[]";
            tireInput.required = true;
            cell3.appendChild(tireInput);
        }

        // Function to fetch and update the description based on the selected item code
        function fetchDescription(itemCodeSelect) {
            const selectedOption = itemCodeSelect.options[itemCodeSelect.selectedIndex];
            const descriptionInput = itemCodeSelect.parentElement.parentElement.cells[1].getElementsByTagName('input')[0];
            const selectedItemCode = selectedOption.value;

            if (selectedItemCode === "") {
                descriptionInput.value = "";
            } else {
                // Make an AJAX request to fetch the description from your server
                // Replace 'fetch_description.php' with the actual endpoint
                fetch(`fetch_description.php?icode=${selectedItemCode}`)
                    .then(response => response.text())
                    .then(data => {
                        descriptionInput.value = data;
                    })
                    .catch(error => console.error("Error fetching description: " + error));
            }
        }
    </script>
</body>
</html>
