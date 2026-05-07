<?php
// Database configuration
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch RM codes for dropdown
function getRMCodes($conn) {
    $options = '<option value="">Select RM Code</option>';
    $result = $conn->query("SELECT DISTINCT RM_code FROM rm_band_data ORDER BY RM_code");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . htmlspecialchars($row['RM_code']) . "'>" . 
                        htmlspecialchars($row['RM_code']) . "</option>";
        }
    }

    return $options;
}

// Fetch band sizes for a specific RM code
function getBandSizes($conn, $rm_code) {
    $options = '<option value="">Select Band Size</option>';
    $stmt = $conn->prepare("SELECT DISTINCT band_size FROM rm_band_data WHERE RM_code = ?");
    $stmt->bind_param('s', $rm_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . htmlspecialchars($row['band_size']) . "'>" . 
                        htmlspecialchars($row['band_size']) . "</option>";
        }
    }

    return $options;
}

// Rest of the existing database and form handling code remains the same...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Existing head content -->
    <style>
        /* Existing styles remain the same */
    </style>
</head>
<body>
<div class="container">
    <h1>Inventory Input Form</h1>
    <form method="POST" action="" id="inventoryForm">
        <table id="data-table">
            <thead>
                <tr>
                    <th>RM Code</th>
                    <th>Band Size</th>
                    <th>Description</th>
                    <th>Number of Bands</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="RM_code[]" onchange="fetchBandSizes(this)" required>
                            <?php echo getRMCodes($conn); ?>
                        </select>
                    </td>
                    <td>
                        <select name="band_size[]" required disabled>
                            <option value="">Select Band Size</option>
                        </select>
                    </td>
                    <td><input type="text" name="description[]" readonly required></td>
                    <td><input type="number" name="num_of_bands[]" min="1" required></td>
                    <td><button type="button" class="delete-button" onclick="deleteRow(this)">Delete Row</button></td>
                </tr>
            </tbody>
        </table>
        <div class="btn-container">
            <input type="button" value="Add Row" onclick="addRow()">
            <input type="submit" value="Submit to Database">
        </div>
    </form>
</div>

<script>
    // Function to add a new row to the table
    function addRow() {
        const table = document.getElementById('data-table').getElementsByTagName('tbody')[0];
        const newRow = table.insertRow();
        newRow.innerHTML = `
            <td>
                <select name="RM_code[]" onchange="fetchBandSizes(this)" required>
                    <?php echo getRMCodes($conn); ?>
                </select>
            </td>
            <td>
                <select name="band_size[]" required> </option>
                </select>
            </td>
            <td><input type="text" name="description[]" readonly required></td>
            <td><input type="number" name="num_of_bands[]" min="1" required></td>
            <td><button type="button" class="delete-button" onclick="deleteRow(this)">Delete Row</button></td>
        `;
    }

    // Function to delete a row from the table
    function deleteRow(btn) {
        const tbody = btn.closest('tbody');
        if (tbody.rows.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert("You must have at least one row in the table.");
        }
    }

    // Function to fetch band sizes based on RM code selection
    function fetchBandSizes(selectElement) {
        const rmCode = selectElement.value;
        const row = selectElement.closest('tr');
        const bandSizeSelect = row.querySelector('select[name="band_size[]"]');
        const descriptionInput = row.querySelector('input[name="description[]"]');

        // Reset band size and description
        bandSizeSelect.innerHTML = '<option value="">Select Band Size</option>';
        bandSizeSelect.disabled = true;
        descriptionInput.value = '';

        if (rmCode) {
            fetch('get_band_sizes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `RM_code=${encodeURIComponent(rmCode)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Populate band sizes
                    data.band_sizes.forEach(size => {
                        const option = document.createElement('option');
                        option.value = size.band_size;
                        option.textContent = size.band_size;
                        bandSizeSelect.appendChild(option);
                    });

                    // Enable band size dropdown
                    bandSizeSelect.disabled = false;

                    // Set description
                    descriptionInput.value = data.description;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching band sizes:', error);
                alert('Error fetching band sizes');
            });
        }
    }
</script>
</body>
</html>