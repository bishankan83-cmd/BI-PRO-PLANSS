<?php
// Include necessary files
include './includes/data_base_save_update.php';
include 'includes/App_Code.php';

// Establish database connection
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize the WHERE clause
$whereClause = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Check if the press_name filter is set and not empty
    if (isset($_GET['press_name']) && !empty($_GET['press_name'])) {
        $press_names = array_map(function($name) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $name) . "'";
        }, $_GET['press_name']);
        $whereClause .= " AND press_name IN (" . implode(',', $press_names) . ")";
    }

    // Check if the mold_id filter is set and not empty
    if (isset($_GET['mold_id']) && !empty($_GET['mold_id'])) {
        $mold_ids = array_map(function($id) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $id) . "'";
        }, $_GET['mold_id']);
        $whereClause .= " AND mold_id IN (" . implode(',', $mold_ids) . ")";
    }

    // Check if the icode filter is set and not empty
    if (isset($_GET['icode']) && !empty($_GET['icode'])) {
        $tire_ids = array_map(function($id) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $id) . "'";
        }, $_GET['icode']);
        $whereClause .= " AND icode IN (" . implode(',', $tire_ids) . ")";
    }

    // Check if the date range filter is set
    if (isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $start_date = mysqli_real_escape_string($conn, $_GET['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_GET['end_date']);
        $whereClause .= " AND start_date >= '$start_date' AND end_date <= '$end_date'";
    }
}

// Function to safely retrieve GET data
function getPostValue($key) {
    return isset($_GET[$key]) ? $_GET[$key] : [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Filter</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
            background-color: #ffffff;
        }

        h3 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
        }

        h6 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            font-size: 12px;
        }

        h5 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 15px;
            padding: 5px;
            background-color: light black;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0; }
        }

        h8 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
        }

        .cargo-loading-date {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
            color: #F28018;
            padding: 5px;
            font-size: 16px;
            background-color: black;
            border: 1px dashed gray;
            border-radius: 10px;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
        }

        .label-container {
            text-align: center;
            background-color: #F28018;
            color: #000000;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="button-container">
        <button><a href="planning.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a></button>
    </div>
    <div class="container">
        <h2>Production Filter</h2>
        <div class="label-container">
            <form method="GET" action="">
                <label for="press_name">Select Press Name(s):</label>
                <select name="press_name[]" id="press_name" multiple="multiple">
                    <option value="">All Press Names</option>
                    <?php
                    $sql = "SELECT DISTINCT press_name FROM plannew";
                    $result = mysqli_query($conn, $sql);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $selected = in_array($row['press_name'], getPostValue('press_name')) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['press_name']) . "' $selected>" . htmlspecialchars($row['press_name']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="mold_id">Select Mold ID(s):</label>
                <select name="mold_id[]" id="mold_id" multiple="multiple">
                    <option value="">All Mold IDs</option>
                    <?php
                    $sql = "SELECT DISTINCT mold_id FROM plannew";
                    $result = mysqli_query($conn, $sql);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $selected = in_array($row['mold_id'], getPostValue('mold_id')) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['mold_id']) . "' $selected>" . htmlspecialchars($row['mold_id']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="icode">Select Tire ID(s):</label>
                <select name="icode[]" id="icode" multiple="multiple">
                    <option value="">All Tire IDs</option>
                    <?php
                    $sql = "SELECT DISTINCT icode FROM plannew";
                    $result = mysqli_query($conn, $sql);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $selected = in_array($row['icode'], getPostValue('icode')) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['icode']) . "' $selected>" . htmlspecialchars($row['icode']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <label for="start_date">Production Date Range:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                <label for="end_date">to</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">

                <input type="submit" value="Filter">
            </form>
        </div>
        <?php
// Display results if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($whereClause)) {
    // Fetch ERP information with the last completion date
    $erpSql = "SELECT erp,  MAX(end_date) as last_completion_date FROM plannew WHERE 1=1 $whereClause GROUP BY erp";
    $erpResult = mysqli_query($conn, $erpSql);

    if ($erpResult && mysqli_num_rows($erpResult) > 0) {
        while ($erpRow = mysqli_fetch_assoc($erpResult)) {
            $erp = htmlspecialchars($erpRow['erp']);
         
            $lastCompletionDate = htmlspecialchars($erpRow['last_completion_date']);
            $cargoLoadingDate = date('Y-m-d', strtotime($lastCompletionDate . ' +3 days'));

            echo "<div class='erp-info'>";
            echo "<p><strong>ERP Number:</strong> $erp</p>";
         
            echo "<p><strong>Last Completion Date:</strong> <span class='cargo-loading-date'>$lastCompletionDate</span></p>";
            echo "<p><strong>Cargo Loading Date:</strong> $cargoLoadingDate</p>";
            echo "</div>";

            // Fetch details for the specific ERP including cavity name
            $detailsSql = "
                SELECT p.icode, p.mold_id, p.press_name, p.start_date, p.end_date, 
                       t.description, c.cavity_name 
                FROM plannew p 
                LEFT JOIN tire_details t ON p.icode = t.icode 
                LEFT JOIN cavity c ON p.cavity_id = c.cavity_id 
                WHERE p.erp = '$erp' $whereClause
            ";
            $detailsResult = mysqli_query($conn, $detailsSql);

            // Display details in a table
            echo "<table class='table'>";
            echo "<tr>
                    <th>Tire ID</th>
                    <th>Description</th>
                    <th>Mold Name</th>
                    <th>Press Name</th>
                    <th>Cavity Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Order Quantity</th>
                    <th>Stock On Hand</th>
                    <th>To Be Produced</th>
                  </tr>";

            if ($detailsResult && mysqli_num_rows($detailsResult) > 0) {
                while ($detailRow = mysqli_fetch_assoc($detailsResult)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($detailRow['icode']) . "</td>";
                    echo "<td>" . htmlspecialchars($detailRow['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($detailRow['mold_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($detailRow['press_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($detailRow['cavity_name']) . "</td>"; // Displaying cavity name
                    echo "<td>" . htmlspecialchars($detailRow['start_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($detailRow['end_date']) . "</td>";
                
                    // You can add more fields here as needed
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No records found for this ERP.</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>No records found for the selected filters.</p>";
    }
}
?>


    </div>

    <script>
        $(document).ready(function() {
            $('#press_name, #mold_id, #icode').select2();
        });
    </script>
</body>
</html>
