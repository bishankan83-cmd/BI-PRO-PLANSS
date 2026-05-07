<!DOCTYPE html>
<html>
<head>
    <title>Inventory Input Form</title>
    <style>
        /* ... (your existing CSS styles) ... */
    </style>
</head>
<body>
    <h1>Manage Production Data</h1>

    <!-- Display existing records in a table -->
    <table border="1">
        <thead>
            <tr>
                <th>Item Code</th>
                <th>Description</th>
                <th>Number Of tire</th>
            </tr>
        </thead>
        <tbody>
            <?php

 // Display existing records from the 'template' table
 $hostname = 'localhost';
 $username = 'planatir_task_managemen';
 $password = 'Bishan@1919';
 $database = 'planatir_task_managemen';

 $conn = new mysqli($hostname, $username, $password, $database);

 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 }

            // Retrieve and display existing records from the 'template' table
            $sql = "SELECT * FROM template";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['icode'] . "</td>";
                   
                    echo "<td>" . $row['cstock'] . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>

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
        <!-- Hidden input to identify which records are being updated -->
        <input type="hidden" name="updateIds[]" value="">
        <div class="btn-container">
            <input type="button" value="Add Row" onclick="addRow(true)">
            <input type="submit" value="Submit to Database">
        </div>
    </form>

    <script>
        // Function to add a new row or update an existing row
        function addRow(isUpdate) {
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

            if (isUpdate) {
                // Add an "Update" button in the last cell for updating records
                const updateButton = document.createElement("input");
                updateButton.type = "button";
                updateButton.value = "Update";
                updateButton.onclick = function() {
                    // Set the hidden input value to the ID of the record being updated
                    document.querySelector("input[name='updateIds[]']").value = '';
                };
                cell3.appendChild(updateButton);
            }
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
