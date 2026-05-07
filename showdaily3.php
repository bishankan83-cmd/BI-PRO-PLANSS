





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title</title>
    <!-- Add your other head elements here -->

    <style>
        /* Your CSS styles */
        .bgrade-button {
        background-color: #007bff;
        color: #ffffff;
    }

    .hold-button {
        background-color: #28a745;
        color: #ffffff;
    }

    .cut-button {
        background-color: #dc3545;
        color: #ffffff;
    }

    .reject-button {
        background-color: red;
        color: #ffffff;
    }
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
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
        }

        table th,
        table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
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


        .instructions-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #d3d3d3;
        border-radius: 8px;
    }

    .instructions-container p {
        margin: 0;
        color: #333333;
        font-size: 16px;
        line-height: 1.5;
    }



    .type-button {
    cursor: pointer;
}

#reasonModal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border: 1px solid #ccc;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

    </style>


</head>
<body>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title</title>
    <!-- Add your stylesheets and other head elements here -->
</head>
<body>

<table>
    <tr>
        <th>Select</th>
        <th>ICode</th>
        <th>Description</th>
        <th>CStock</th>
        <th>Date</th>
        <th>Shift</th>
        <th>Reason</th>
        <th>TYPE</th>
        <th>Select TYPE</th>
    </tr>
    <?php
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT template2b.id, template2b.icode, template2b.cstock, template2b.date, template2b.shift, template2b.reason, template2b.reject, tire_details.description
            FROM template2b
            JOIN tire_details ON template2b.icode = tire_details.icode";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Update the 'reason' column to 0 if TYPE is 'bgrade', 'HOLD', or 'CUT'
            if ($row['reject'] === 'BGRADE' || $row['reject'] === 'CUT' || $row['reject'] === 'AGrade') {
              $row['reason'] = 0;
            } elseif ($row['reject'] === 'REJECT') {
           //     // Handle the 'REJECT' type by checking if a reason is provided
               if (empty($row['reason'])) {
                  // Set a default reason if not provided
                   $row['reason'] = "Default REJECT reason";
               }
            }

            // Update the database with the new 'reason' value
            $updateSql = "UPDATE template2b SET reason = '" . $row['reason'] . "' WHERE id = " . $row['id'];
            $conn->query($updateSql);

            echo "<tr>";
            echo "<td><input type='checkbox' value='" . $row['id'] . "'></td>";
            echo "<td class='editable-icode' data-id='" . $row['id'] . "' data-field='icode'>";

            // Fetch distinct 'icode' values from the 'tire' table
            $tire_sql = "SELECT DISTINCT icode FROM tire_details";
            $tire_result = $conn->query($tire_sql);
            echo "<select class='icode-select'>";
            while ($tire_row = $tire_result->fetch_assoc()) {
                $selected = ($row['icode'] === $tire_row['icode']) ? 'selected' : '';
                echo "<option value='" . $tire_row['icode'] . "' $selected>" . $tire_row['icode'] . "</option>";
            }
            echo "</select>";

            echo "</td>";
            echo "<td class='description-cell'>" . $row['description'] . "</td>";
            echo "<td class='editable' data-id='" . $row['id'] . "' data-field='cstock' contentEditable='true'>" . $row['cstock'] . "</td>";
            echo "<td class='editable' data-id='" . $row['id'] . "' data-field='date' contentEditable='true'>" . $row['date'] . "</td>";
            echo "<td class='editable' data-id='" . $row['id'] . "' data-field='shift' contentEditable='true'>" . $row['shift'] . "</td>";
            echo "<td class='editable' data-id='" . $row['id'] . "' data-field='shift' contentEditable='true'>" . $row['reason'] . "</td>";
            echo "<td class='editable' data-id='" . $row['id'] . "' data-field='shift' contentEditable='true'>" . $row['reject'] . "</td>";

            // Add buttons to each horizontal column
            echo "<td>";
            echo "<button class='type-button reject-button' data-id='" . $row['id'] . "' data-field='type' data-value='REJECT'>REJECT</button>";
            echo "<button class='type-button bgrade-button' data-id='" . $row['id'] . "' data-field='type' data-value='BGRADE'>BGRADE</button>";
            echo "<button class='type-button hold-button' data-id='" . $row['id'] . "' data-field='type' data-value='HOLD'>HOLD</button>";
            echo "<button class='type-button cut-button' data-id='" . $row['id'] . "' data-field='type' data-value='CUT'>CUT</button>";
            echo "<button class='type-button AGrade-button' data-id='" . $row['id'] . "' data-field='type' data-value='AGrade'>Agrade</button>";
            echo "<button class='type-button Test-button' data-id='" . $row['id'] . "' data-field='type' data-value='Test'>Test</button>";


            echo "</td>";

            echo "</tr>";
        }
    } else {
        echo "0 results";
    }

    $conn->close();
    ?>
</table>

<!-- Buttons -->

<button id="deleteButton">Select Confirm</button>




<div id="reasonModal">
    <label for="reasonSelect">Please provide a reason for REJECT:</label>
    <select id="reasonInput">
    <option value="CLIP DAMAGE">CLIP DAMAGE</option>
        <option value="MACHANICAL DAMAGE">MACHANICAL DAMAGE</option>
        <option value="US FAIL">US FAIL</option>
        <option value="FLOW MARK (SIDE WALL)">FLOW MARK (SIDE WALL)</option>
        <option value="BEAD OPEN">BEAD OPEN</option>
        <option value="TREAD BLOCK">TREAD BLOCK</option>
        <option value="AIR BUBBLE">AIR BUBBLE</option>
        <option value="BACK RIND">BACK RIND</option>
        <option value="UNDER WEIGHT">UNDER WEIGHT</option>
        <option value="BAND RUBBER SEPARATION">BAND RUBBER SEPARATION</option>
        <option value="LUG DAMAGE">LUG DAMAGE</option>
        <option value="PEELING (TREAD)">PEELING (TREAD)</option>
        <option value="UNDER CURE">UNDER CURE</option>
        <option value="DOUBLE LINE">DOUBLE LINE</option>
        <option value="PRESSURE DROP">PRESSURE DROP</option>
        <option value="FOREIGN MATERIAL">FOREIGN MATERIAL</option>
        <option value="TREAD DEFORM">TREAD DEFORM</option>
        <option value="CURED FLASH">CURED FLASH</option>
        <option value="BAND DEFORM">BAND DEFORM</option>
        <option value="THICK SPEW LINE">THICK SPEW LINE</option>
        <option value="PEAK">PEAK</option>
        <option value="BLACK MIX">BLACK MIX</option>
        <option value="NM FLOW">NM FLOW</option>
        <option value="MOULD DAMAGE">MOULD DAMAGE</option>
        <option value="OUT OF LINE">OUT OF LINE</option>
        <option value="PEELING (SIDE WALL)">PEELING (SIDE WALL)</option>
        <option value="FLOW MARK (BASE)">FLOW MARK (BASE)</option>
        <option value="OTHERS">OTHERS</option>
        <option value="COLOUR VARIATION">COLOUR VARIATION</option>
        <option value="BLACK FLOW">BLACK FLOW</option>
        <option value="VENT HOLE DAMAGE">VENT HOLE DAMAGE</option>
    </select>
    <button id="submitReason">Submit</button>
</div>

<script>


 


   // Add event listener to handle type buttons click
const typeButtons = document.querySelectorAll('.type-button');

typeButtons.forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const field = 'reject'; // Update the 'reject' field
        const value = button.getAttribute('data-value');
        const typeField = 'TYPE'; // Update the 'TYPE' field

        // Update the database with the 'reject' and 'TYPE' values
        if (value === 'REJECT') {
            const reasonModal = document.getElementById('reasonModal');
            const reasonInput = document.getElementById('reasonInput');
            const submitReason = document.getElementById('submitReason');

            // Display the reason input modal
            reasonModal.style.display = 'block';

            // Handle the reason submission
            submitReason.addEventListener('click', () => {
                const reason = reasonInput.value.trim();
                if (reason === '') {
                    alert('A reason is required for REJECT. Please try again.');
                    return;
                }

                // Update the database with the 'reject' and 'TYPE' values
                updateDatabase(id, 'reason', reason);
                updateDatabase(id, field, value);
                updateDatabase(id, typeField, value); // Update the 'TYPE' field

                // Hide the reason input modal
                reasonModal.style.display = 'none';

                // Reload the page after updating
                location.reload();
            });
        } else {
            // Update the database with the 'reject' and 'TYPE' values for non-REJECT buttons
            updateDatabase(id, field, value);
            updateDatabase(id, typeField, value); // Update the 'TYPE' field

            // Reload the page after updating
            location.reload();
        }
    });
});

     // Add event listener for reason submission
  submitReason.addEventListener('click', () => {
        const reason = reasonInput.value.trim();
        if (reason === '') {
            alert('A reason is required for REJECT. Please try again.');
            return;
        }

        // Use AJAX to send the reason to the server
        fetch('update2b.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&reason=${encodeURIComponent(reason)}`,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(data => {
            // Handle success (if needed)
            console.log('Update successful:', data);

            // Hide the reason input modal
            reasonModal.style.display = 'none';

            // Reload the page after updating
            location.reload();
        })
        .catch(error => {
            // Handle errors
            console.error('Update failed:', error);
        });
    });
 
    // Add event listener to handle delete button click
    const deleteButton = document.getElementById('deleteButton');
    deleteButton.addEventListener('click', () => {
        // Get all selected checkboxes in the table
        const selectedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');

        // Create an array to store the selected row IDs
        const selectedRowIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);

        // Check if any rows are selected
        if (selectedRowIds.length > 0) {
            // Call the deleteRows function to delete selected rows
            deleteRows(selectedRowIds);
        } else {
            // Display a message if no rows are selected
            alert('No rows selected for deletion.');
        }
    });

    function deleteRows(selectedRowIds) {
        // Make an AJAX request to delete rows
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_rows2.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        // Handle the response after the deletion is complete
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Handle a successful deletion, if needed
                    console.log('Deletion successful');

                    // Reload the page after deletion
                    location.reload();
                } else {
                    // Handle errors or failed deletions, if needed
                    console.error('Deletion failed');
                }
            }
        };

        // Send the selected row IDs to the server for deletion
        xhr.send(`rowIds=${JSON.stringify(selectedRowIds)}`);
    }

    
</script>

<script>
    // Add a click event listener to the button
    const navigateButton = document.getElementById('navigateButton');
    navigateButton.addEventListener('click', () => {
        // Specify the URL of the page you want to navigate to
        const targetURL = 'add_reject22b.php'; // Replace with your target URL

        // Use JavaScript to redirect to the target URL
        window.location.href = targetURL;
    });
</script>

<script>
    const editableIcodeCells = document.querySelectorAll('.editable-icode select');
    const descriptionCells = document.querySelectorAll('.description-cell');

    // Add event listener to handle changes in the 'icode' selection
    editableIcodeCells.forEach(cell => {
        cell.addEventListener('change', () => {
            const id = cell.parentElement.getAttribute('data-id');
            const field = 'icode';
            const value = cell.value;

            // Fetch the description based on the selected 'icode'
            fetchDescription(value, id);

            // Update the database
            updateDatabase(id, field, value);

            // Reload the page after a selection
            location.reload();
        });
    });

    const editableCStockCells = document.querySelectorAll('.editable[data-field="cstock"]');
    editableCStockCells.forEach(cell => {
        cell.addEventListener('blur', () => {
            const id = cell.getAttribute('data-id');
            const field = 'cstock';
            const value = cell.textContent;

            updateDatabase(id, field, value);
        });
    });

    function updateDatabase(id, field, value, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update2b.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Handle a successful update, if needed
                    console.log('Update successful');
                } else {
                    // Handle errors or failed updates, if needed
                    console.error('Update failed');
                }

                if (typeof callback === 'function') {
                    callback();
                }
            }  
        };
        xhr.send(`id=${id}&field=${field}&value=${value}`);
    }

    // Function to fetch the description based on the selected 'icode'
    function fetchDescription(icode, id) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `get_description.php?icode=${icode}&id=${id}`, true);
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const descriptionCell = document.querySelector(`[data-id='${id}'] .description-cell`);
                descriptionCell.textContent = xhr.responseText;
            }
        };
        xhr.send();
    }
</script>
</body>
</html>    
