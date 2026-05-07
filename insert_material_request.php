
<?php
// Previous PHP code remains the same up until the HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Previous head content remains the same -->
    <style>
        /* Previous styles remain the same */

        /* Add new styles for the back button */
        .navigation-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .back-button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #333333;
            transform: translateY(-1px);
        }

        /* Rest of the previous styles remain the same */
    </style>
</head>
<body>
<div class="container">
    <!-- Add navigation buttons at the top -->
    <div class="navigation-buttons">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

  

<!-- Previous JavaScript code remains the same -->
</body>
</html>




<?php
// Database configuration
$host = 'localhost';
$db = 'planatir_task_managemen';
$user = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create database connection
$conn = new mysqli($host, $user, $password, $db);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the next MRN number
function getNextMRNNumber($conn) {
    $result = $conn->query("SELECT MAX(mrn_number) AS max_mrn FROM material_request");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['max_mrn'] ? intval($row['max_mrn']) + 1 : 1;
    }
    return 1;
}

$nextMRNNumber = getNextMRNNumber($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mrn_number = isset($_POST['mrn_number']) ? intval($_POST['mrn_number']) : null;

    if (!$mrn_number) {
        echo "<script>alert('MRN Number is missing or invalid!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO material_request (mrn_number, RM_code, band_size, num_of_bands) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("issi", $mrn_number, $rm_code, $band_size, $num_of_bands);

        $conn->begin_transaction();
        try {
            foreach ($_POST['RM_code'] as $index => $rm_code) {
                $band_size = isset($_POST['band_size'][$index]) ? htmlspecialchars(trim($_POST['band_size'][$index])) : null;
                $num_of_bands = isset($_POST['num_of_bands'][$index]) ? filter_var($_POST['num_of_bands'][$index], FILTER_VALIDATE_INT) : null;

                if (empty($rm_code) || $num_of_bands === null) {
                    continue;
                }

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
            }
            $conn->commit();
            echo "<script>alert('Data successfully inserted!');</script>";
            echo "<script>window.location.href = 'insert_material_request.php';</script>";
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Transaction failed: " . $e->getMessage() . "');</script>";
        }

        $stmt->close();
    }
}

// Fetch RM codes for dropdown
function getRMCodes($conn) {
    $options = '<option value="">Select RM Code</option>';
    $result = $conn->query("SELECT DISTINCT RM_code, band_size FROM rm_band_data ORDER BY RM_code");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . htmlspecialchars($row['RM_code']) . "'>" . htmlspecialchars($row['RM_code']) . " - " . htmlspecialchars($row['band_size']) . "</option>";
        }
    }
    return $options;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steel Band Mrn Request</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #ffffff;
            font-family: 'Open Sans', sans-serif;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #000000;
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            font-size: 2em;
        }

        .mrn-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .mrn-section label {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
        }

        #mrnNumberDisplay {
            background-color: #F28018;
            color: #000000;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            width: 150px;
            font-family: 'Cantarell', sans-serif;
        }

        .stockr-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stockr-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 15px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .stockr-table td {
            border: 1px solid #000000;
            padding: 12px;
            font-family: 'Open Sans', sans-serif;
        }

        .stockr-row td {
            font-family: 'Open Sans', sans-serif;
            font-weight: regular;
        }

        select, input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-size: 14px;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 2px rgba(242, 128, 24, 0.1);
        }

        .button-container {
            text-align: left;
            margin: 10px;
            display: flex;
            gap: 10px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .button-container button:hover {
            background-color: #333333;
            transform: translateY(-1px);
        }

        .delete-button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 8px 16px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Cantarell', sans-serif;
        }

        .delete-button:hover {
            background-color: #333333;
            transform: translateY(-1px);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(10px);
            }
        }

        .container {
            animation: fadeIn 0.5s ease-out;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            display: none;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .stockr-table {
                display: block;
                overflow-x: auto;
            }

            .button-container {
                flex-direction: column;
            }

            .button-container button {
                width: 100%;
            }

            .mrn-section {
                flex-direction: column;
                align-items: stretch;
            }

            #mrnNumberDisplay {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Steel Band Mrn Request</h1>
    <form method="POST" id="inventoryForm">
        <div class="mrn-section">
            <label for="mrnNumberDisplay">MRN Number:</label>
            <input type="text" id="mrnNumberDisplay" value="<?php echo $nextMRNNumber; ?>" readonly>
            <input type="hidden" name="mrn_number" value="<?php echo $nextMRNNumber; ?>">
        </div>

        <table id="data-table" class="stockr-table">
            <thead>
                <tr class="header">
                    <th>RM Code</th>
                    <th>Band Size</th>
                    <th>Number of Bands</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr class="stockr-row">
                    <td>
                        <select name="RM_code[]" onchange="fetchBandSize(this)" required>
                            <?php echo getRMCodes($conn); ?>
                        </select>
                    </td>
                    <td><input type="text" name="band_size[]" readonly></td>
                    <td><input type="number" name="num_of_bands[]" min="1" required></td>
                    <td><button type="button" class="delete-button" onclick="deleteRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
            </tbody>
        </table>
        <div class="button-container">
            <button type="button" onclick="addRow()"><i class="fas fa-plus"></i> Add Row</button>
            <button type="submit"><i class="fas fa-save"></i> Submit</button>
        </div>
    </form>
</div>

<div id="toast" class="toast"></div>

<script>
function addRow() {
    const table = document.getElementById('data-table').getElementsByTagName('tbody')[0];
    const newRow = table.insertRow();
    newRow.className = 'stockr-row';
    newRow.style.animation = 'fadeIn 0.3s ease-out';

    newRow.innerHTML = `
        <td>
            <select name="RM_code[]" onchange="fetchBandSize(this)" required>
                <?php echo getRMCodes($conn); ?>
            </select>
        </td>
        <td><input type="text" name="band_size[]" readonly></td>
        <td><input type="number" name="num_of_bands[]" min="1" required></td>
        <td><button type="button" class="delete-button" onclick="deleteRow(this)"><i class="fas fa-trash"></i></button></td>
    `;
}

function deleteRow(button) {
    const row = button.closest('tr');
    row.style.animation = 'fadeOut 0.3s ease-out';
    setTimeout(() => row.remove(), 300);
}

function fetchBandSize(select) {
    const selectedOption = select.options[select.selectedIndex];
    const bandSize = selectedOption.text.match(/-\s(.*)$/)?.[1]?.trim() || '';
    const bandSizeInput = select.closest('tr').querySelector('input[name="band_size[]"]');
    bandSizeInput.value = bandSize;
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336';
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

document.getElementById('inventoryForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.stockr-row');
    if (rows.length === 0) {
        e.preventDefault();
        showToast('Please add at least one row of data', 'error');
    }
});
</script>
</body>
</html>