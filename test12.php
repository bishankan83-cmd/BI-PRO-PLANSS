<!DOCTYPE html>
<html>
<head>
    <title>Inventory Input Form</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2; /* Light Gray */
        }

        .container {
            width: 90%;
            margin: 20px auto;
            background-color: #fff; /* White */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #F28018; /* Orange */
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        #inputDate {
            width: 20%;
        }

        input[type="date"],
        select,
        input[type="text"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid black;
            border-radius: 5px;
        }

        select {
            width: 20%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        select[name='icode[]'] option[value=""] {
            width: 130%; /* Adjusted width */
        }

        .btn-container {
            margin-top: 20px;
            text-align: center;
        }

        .btn-container input[type="button"],
        .btn-container input[type="submit"] {
            padding: 10px 20px;
            background-color: #F28018; /* Green */
            color: #FFFFFF; /* White */
            border: none;
            cursor: pointer;
            margin-right: 10px;
            border-radius: 5px;
        }

        .btn-container input[type="button"]:hover,
        .btn-container input[type="submit"]:hover {
            background-color: black; /* Darker Green */
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Enter Mixing</h1>
    <form id="data-form" action="admix.php" method="post">
        <label for="inputDate">Date:</label>
        <input type="date" id="inputDate" name="inputDate" required>

        <label for="shift">Shift:</label>
        <select name="shift" id="shift" required>
            <option value="">Select Shift</option>
            <option value="DAY A">DAY A</option>
            <option value="DAY B">DAY B</option>
            <option value="DAY C">DAY C</option>
            <option value="NIGHT A">NIGHT A</option>
            <option value="NIGHT B">NIGHT B</option>
            <option value="NIGHT C">NIGHT C</option>
        </select>

        <label for="supervisor">Data enter supervisor:</label>
        <select name="description" id="description" required>
            <option value="">Select Data enter supervisor</option>
            <option value="DIMUTHU">Dimuthu</option>
            <option value="Subanu">Subanu</option>
            <option value="Chandrarathna">Chandrarathna</option>
            <!-- Add more options as needed -->
        </select>

        <table id="data-table">
            <thead>
            <tr>
                <th>Compound Name</th>
                <th>ERP Code</th>
                <th>Batch number</th>
                <th>Pallet Number</th>
                <th>Total Weight</th>
                <th>Job Number</th>
                <th>Action</th> <!-- New column for delete action -->
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <select name="icode[]" onchange="fetchDescription(this)">
                        <option value="">Select a Compound Name</option>
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

                        $sql = "SELECT compound_name FROM compounds";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $itemCode = $row['compound_name'];
                                echo "<option value='$itemCode'>$itemCode</option>";
                            }
                        }

                        $conn->close();
                        ?>
                    </select>
                </td>
                <td><input type="text" name="cstock[]" readonly></td>
                <td><input type="text" name="batch[]" required> <input type="text" name="batch2[]" required> </td>
                <td><input type="text" name="pallet[]" required></td>
                <td><input type="text" name="weight[]" required></td>
                <td><input type="text" name="serialNumber[]" required></td>
                <td><button type="button" onclick="deleteRow(this)">Delete</button></td> <!-- Delete button -->
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
            const cell4 = newRow.insertCell(3);
            const cell5 = newRow.insertCell(4);
            const cell6 = newRow.insertCell(5);
            const cell7 = newRow.insertCell(6); // New cell for delete button

            const itemCodeSelect = document.createElement("select");
            itemCodeSelect.name = "icode[]";
            itemCodeSelect.onchange = function () {
                fetchDescription(itemCodeSelect);
            }

            // Clone the options from the existing item code select dropdown
            const existingSelect = document.querySelector("select[name='icode[]']");
            for (const option of existingSelect.options) {
                const clonedOption = option.cloneNode(true);
                itemCodeSelect.appendChild(clonedOption);
            }

            cell1.appendChild(itemCodeSelect);

            const tireInput = document.createElement("input");
            tireInput.type = "text";
            tireInput.name = "cstock[]";
            tireInput.readOnly = true;
            cell2.appendChild(tireInput);

            const tireInputt = document.createElement("input");
            tireInputt.type = "text";
            tireInputt.name = "batch[]";
            tireInputt.required = true;
            cell3.appendChild(tireInputt);

            const tireInputtu = document.createElement("input");
            tireInputtu.type = "text";
            tireInputtu.name = "batch2[]";
            tireInputtu.required = true;
            cell3.appendChild(tireInputtu);

            const tireInputtt = document.createElement("input");
            tireInputtt.type = "text";
            tireInputtt.name = "pallet[]";
            tireInputtt.required = true;
            cell4.appendChild(tireInputtt);

            const tireInputttw = document.createElement("input");
            tireInputttw.type = "text";
            tireInputttw.name = "weight[]";
            tireInputttw.required = true;
            cell5.appendChild(tireInputttw);

            const tireInputttwo = document.createElement("input");
            tireInputttwo.type = "text";
            tireInputttwo.name = "serialNumber[]";
            tireInputttwo.required = true;
            cell6.appendChild(tireInputttwo);

            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.textContent = "Delete";
            deleteButton.onclick = function () {
                deleteRow(deleteButton);
            };
            cell7.appendChild(deleteButton);
        }

        // Function to delete the row
        function deleteRow(btn) {
            const row = btn.parentNode.parentNode;
            row.parentNode.removeChild(row);
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
                fetch(`fetch_compound.php?compound_name=${selectedItemCode}`)
                    .then(response => response.text())
                    .then(data => {
                        descriptionInput.value = data;
                    })
                    .catch(error => console.error("Error fetching description: " + error));
            }
        }
    </script>
</div>
</body>
</html>
