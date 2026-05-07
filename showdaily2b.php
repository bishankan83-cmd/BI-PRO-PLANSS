<!DOCTYPE html>
<html>
<head>
<style>
        /* Your CSS styles */

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
    </style>
</head>
<body>
<table>
    <tr>
        <th>ICode</th>
        <th>Description</th>
        <th>CStock</th>
        <th>Date</th>
        <th>Shift</th>
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

    $sql = "SELECT template2.id, template2.icode, template2.cstock, template2.date, template2.shift, tire.description
            FROM template2
            JOIN tire ON template2.icode = tire.icode";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td class='editable-icode' data-id='" . $row['id'] . "' data-field='icode'>";
            
            // Fetch distinct 'icode' values from the 'tire' table
            $tire_sql = "SELECT DISTINCT icode FROM tire";
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
            echo "<td class 'editable' data-id='" . $row['id'] . "' data-field='date' contentEditable='true'>" . $row['date'] . "</td>";
            echo "<td class='editable' data-id='" . $row['id'] . "' data-field='shift' contentEditable='true'>" . $row['shift'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "0 results";
    }

    $conn->close();
    ?>
</table>

<button id="navigateButton">Confirm The Details</button>

<script>
    // Add a click event listener to the button
    const navigateButton = document.getElementById('navigateButton');
    navigateButton.addEventListener('click', () => {
        // Specify the URL of the page you want to navigate to
        const targetURL = 'add_reject2b.php'; // Replace with your target URL

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
    xhr.open('POST', 'updateb.php', true);
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
