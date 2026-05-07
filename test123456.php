







<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tableRows = '';
$totalPlan = 0;
$totalGreenTireWeight = 0;
$sql_erp_numbers = "SELECT DISTINCT erp, Customer
                    FROM plannew
                    WHERE erp IN (
                        SELECT erp FROM plannew GROUP BY erp HAVING SUM(tires_per_mold) > 0
                    )";

$result_erp_numbers = $conn->query($sql_erp_numbers);
$erp_customers = [];
$erp_numbers = [];

while ($row_erp = $result_erp_numbers->fetch_assoc()) {
    $erp_customers[$row_erp['erp']] = $row_erp['Customer'];
    $erp_numbers[] = $row_erp['erp'];
}

$sql_erp_data = "SELECT erp, ref, wono
                 FROM worder
                 WHERE erp IN (" . implode(",", array_fill(0, count($erp_numbers), "?")) . ")";

$stmt_erp_data = $conn->prepare($sql_erp_data);
$stmt_erp_data->bind_param(str_repeat("s", count($erp_numbers)), ...$erp_numbers);
$stmt_erp_data->execute();
$result_erp_data = $stmt_erp_data->get_result();

$erp_worder_data = [];

while ($row_erp_data = $result_erp_data->fetch_assoc()) {
    $erp_worder_data[$row_erp_data['erp']] = [
        'ref' => $row_erp_data['ref'],
        'wono' => $row_erp_data['wono'],
    ];
}

$stmt_erp_data->close();

function getRefWorderData($erp) {
    global $conn;

    $sql = "SELECT ref FROM worder WHERE erp = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $erp);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result->fetch_assoc();
}

function getDescriptionByIcode($icode) {
    global $conn;

    $sql = "SELECT description FROM tire WHERE icode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $icode);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $row = $result->fetch_assoc();
    return isset($row['description']) ? $row['description'] : '';
}

require 'vendor/autoload.php';  // Autoload Composer dependencies

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_selected_erps = isset($_POST["selected_erps"]) ? $_POST["selected_erps"] : [];
    $start_date = isset($_POST["start_date"]) ? $_POST["start_date"] : null;
    $end_date = isset($_POST["end_date"]) ? $_POST["end_date"] : null;

    if (empty($user_selected_erps)) {
        echo "Please select at least one ERP number.";
    } else {
        $sql = "SELECT * FROM calculated_data WHERE erp IN (";
        $placeholders = implode(",", array_fill(0, count($user_selected_erps), "?"));
        $sql .= $placeholders . ")";

        if ($start_date && $end_date) {
            $sql .= " AND date BETWEEN ? AND ?";
            $user_selected_erps[] = $start_date;
            $user_selected_erps[] = $end_date;
        }

        $stmt = $conn->prepare($sql);
        $bind_params = str_repeat("s", count($user_selected_erps));
        $stmt->bind_param($bind_params, ...$user_selected_erps);
        $stmt->execute();
        $result = $stmt->get_result();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $header = ['Date', 'ERP', 'Customer', 'ICode', 'Description', 'Mold ID', 'Cavity ID', 'Start Date', 'End Date', 'Time Taken', 'Plan', 'Green Tire Weight', 'Ref from worder'];
        $sheet->fromArray($header, null, 'A1');

        $rowIndex = 2;

        while ($row = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $rowIndex, $row['date']);
            $sheet->setCellValue('B' . $rowIndex, $row['erp']);
            $sheet->setCellValue('C' . $rowIndex, $erp_customers[$row['erp']]);
            $sheet->setCellValue('D' . $rowIndex, $row['icode']);
            $description = getDescriptionByIcode($row['icode']);
            $sheet->setCellValue('E' . $rowIndex, $description);
            $sheet->setCellValue('F' . $rowIndex, $row['mold_id']);
            $sheet->setCellValue('G' . $rowIndex, $row['cavity_id']);
            $sheet->setCellValue('H' . $rowIndex, $row['start_date']);
            $sheet->setCellValue('I' . $rowIndex, $row['end_date']);
            $sheet->setCellValue('J' . $rowIndex, $row['time_taken']);
            $sheet->setCellValue('K' . $rowIndex, $row['plan']);
            $sheet->setCellValue('L' . $rowIndex, $row['calculated_green_tire_weight']);

            $refWorderData = getRefWorderData($row['erp']);
            $ref = isset($refWorderData['ref']) ? $refWorderData['ref'] : '';
            $sheet->setCellValue('M' . $rowIndex, $ref);

            $rowIndex++;

            $totalPlan += $row['plan'];
            $totalGreenTireWeight += $row['calculated_green_tire_weight'];
        }

        $sheet->setCellValue('A' . $rowIndex, 'Total Plan:');
        $sheet->setCellValue('K' . $rowIndex, $totalPlan);
        $sheet->setCellValue('A' . ($rowIndex + 1), 'Total Green Tire Weight:');
        $sheet->setCellValue('L' . ($rowIndex + 1), $totalGreenTireWeight);

        $filename = 'exported_data.xlsx';

        // Save the Excel file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        // Provide a download link for the user
        echo "<p><a href='$filename' download>Download Excel File</a></p>";

        $stmt->close();
    }
}


?>


<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tableRows = '';
$totalPlan = 0;
$totalGreenTireWeight = 0;
$sql_erp_numbers = "SELECT DISTINCT erp, Customer
                    FROM plannew
                    WHERE erp IN (
                        SELECT erp FROM plannew GROUP BY erp HAVING SUM(tires_per_mold) > 0
                    )";

$result_erp_numbers = $conn->query($sql_erp_numbers);
$erp_customers = [];
$erp_numbers = [];

while ($row_erp = $result_erp_numbers->fetch_assoc()) {
    $erp_customers[$row_erp['erp']] = $row_erp['Customer'];
    $erp_numbers[] = $row_erp['erp'];
}


// Retrieve ref and wono for each ERP number
$sql_erp_data = "SELECT erp, ref, wono
                 FROM worder
                 WHERE erp IN (" . implode(",", array_fill(0, count($erp_numbers), "?")) . ")";

$stmt_erp_data = $conn->prepare($sql_erp_data);
$stmt_erp_data->bind_param(str_repeat("s", count($erp_numbers)), ...$erp_numbers);
$stmt_erp_data->execute();
$result_erp_data = $stmt_erp_data->get_result();

$erp_worder_data = [];

while ($row_erp_data = $result_erp_data->fetch_assoc()) {
    $erp_worder_data[$row_erp_data['erp']] = [
        'ref' => $row_erp_data['ref'],
        'wono' => $row_erp_data['wono'],
    ];
}

$stmt_erp_data->close();





if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_selected_erps = isset($_POST["selected_erps"]) ? $_POST["selected_erps"] : [];
    $start_date = isset($_POST["start_date"]) ? $_POST["start_date"] : null;
    $end_date = isset($_POST["end_date"]) ? $_POST["end_date"] : null;

    if (empty($user_selected_erps)) {
        echo "Please select at least one ERP number.";
    } else {
        $sql = "SELECT * FROM calculated_data WHERE erp IN (";
        $placeholders = implode(",", array_fill(0, count($user_selected_erps), "?"));
        $sql .= $placeholders . ")";

        if ($start_date && $end_date) {
            $sql .= " AND date BETWEEN ? AND ?";
            $user_selected_erps[] = $start_date;
            $user_selected_erps[] = $end_date;
        }

        $stmt = $conn->prepare($sql);
        $bind_params = str_repeat("s", count($user_selected_erps));
        $stmt->bind_param($bind_params, ...$user_selected_erps);
        $stmt->execute();
        $result = $stmt->get_result();

        $tableRows = '<table>
                        <tr>
                            <th>Date</th>
                            <th>ERP</th>
                            <th>Customer</th> <!-- Added column header -->
                            <th>ICode</th>
                            <th>Description</th>
                            <th>Mold ID</th>
                            <th>Cavity ID</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Time Taken</th>
                            <th>Plan</th>
                            <th>Green Tire Weight</th>
                            <th>Ref from worder</th>
                        </tr>';

        while ($row = $result->fetch_assoc()) {


            $tableRows .= "<tr>";
            
            $tableRows .= "<td>{$row['date']}</td>";
            $tableRows .= "<td>{$row['erp']}</td>";
            $tableRows .= "<td>{$erp_customers[$row['erp']]}</td>"; // Display customer name
            $tableRows .= "<td>{$row['icode']}</td>";
            $description = getDescriptionByIcode($row['icode']);
            $tableRows .= "<td>{$description}</td>";
            $tableRows .= "<td>{$row['mold_id']}</td>";
            $tableRows .= "<td>{$row['cavity_id']}</td>";
            $tableRows .= "<td>{$row['start_date']}</td>";
            $tableRows .= "<td>{$row['end_date']}</td>";
            $tableRows .= "<td>{$row['time_taken']}</td>";
            $tableRows .= "<td>{$row['plan']}</td>";
            $tableRows .= "<td>{$row['calculated_green_tire_weight']}</td>";

            $refWorderData = getRefWorderData($row['erp']);
            $ref = isset($refWorderData['ref']) ? $refWorderData['ref'] : '';
            $tableRows .= "<td>{$ref}</td>";

            $tableRows .= "</tr>";

            $totalPlan += $row['plan'];
            $totalGreenTireWeight += $row['calculated_green_tire_weight'];
        }

        $tableRows .= "<tr>";
        $tableRows .= "<td colspan='10'><strong>Total Plan:</strong></td>";
        $tableRows .= "<td><strong>$totalPlan</strong></td>";
        $tableRows .= "<td><strong>Total Green Tire Weight:</strong></td>";
        $tableRows .= "<td><strong>$totalGreenTireWeight</strong></td>";
        $tableRows .= "</tr>";

        $tableRows .= "</table>";

        $stmt->close();
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Data Retrieval</title>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        
        table td {
            border: 1px solid #000000;
            padding: 12px;
            text-align: left;
        }
     /* Add a fixed position to the table header */
     table th  {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        

        td {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
        }

        form {
            text-align: center;
            margin: 20px 0;
        }

        label {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            display: block;
            margin-bottom: 8px;
        }

        input[type="date"],
        input[type="submit"],
        input[type="checkbox"] {
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: #000000;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 12px;
        }

        input[type="submit"]:hover {
            background-color: #333333;
        }

        .checkbox-container {
            list-style: none;
            padding: 0;
        }

        .checkbox-item {
        display: inline-block;  /* Add this line */
        margin-right: 10px;     /* Add this line for spacing between checkboxes */
    }


        /* Styling for checkbox container */
        .checkbox-item-box {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 5px;
            background-color: #F28018;
        }

    </style>
</head>

<body>
    <div class="container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label>Select work order:</label><br>

            
            <input type='checkbox' id='select-all' onclick='selectAllCheckbox()'>
            <label for='select-all'>Select All</label><br>

            <div class="checkbox-container">
                <?php
                foreach ($erp_numbers as $erp) {
                    $erp_data = isset($erp_worder_data[$erp]) ? $erp_worder_data[$erp] : ['ref' => '', 'wono' => ''];
                    $ref = $erp_data['ref'];
                    $wono = $erp_data['wono'];
                    // Wrap each checkbox item in a decorative box
                    echo "<div class='checkbox-item-box'>";
                    echo "<div class='checkbox-item'><input type='checkbox' name='selected_erps[]' value='$erp'> {$ref}, Wono: {$wono} - ERP: {$erp}</div>";
                    echo "</div>";
                }
                ?>
            </div>
            

            <br>
            <label>Start Date:</label>
            <input type="date" name="start_date">
            <br>
            <label>End Date:</label>
            <input type="date" name="end_date">
            <br>
            <button type="submit">Retrieve Data</button>
        </form>

        <?php echo $tableRows; ?>
    </div>

    <script>
        function selectAllCheckbox() {
            var checkboxes = document.getElementsByName('selected_erps[]');
            var selectAllCheckbox = document.getElementById('select-all');

            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = selectAllCheckbox.checked;
            }
        }
    </script>
</body>

</html>
