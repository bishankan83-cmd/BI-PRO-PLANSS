<?php
// Database connection configuration
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to process data from queries
function processData($result, $data, $key) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $erp = $row['erp'];
            if (isset($data[$erp])) {
                $data[$erp][$key] = $row[$key];
            } else {
                $data[$erp] = array(
                    'ref' => '',
                    'wono' => '',
                    'total_new' => 0,
                    'total_positive_tobe' => 0,
                    'total_kgs' => 0,
                    'last_end_date' => '',
                    'cargo_ready_date' => '',
                    'country' => '',
                    'pattern' => ''
                );
                $data[$erp][$key] = $row[$key];
            }
        }
    }
    return $data;
}

// Function to sort by "To be Produce (Nos)" column
function sortByToBeProduce($a, $b) {
    if ($a['total_positive_tobe'] == 0 && $b['total_positive_tobe'] != 0) {
        return -1;
    } elseif ($a['total_positive_tobe'] != 0 && $b['total_positive_tobe'] == 0) {
        return 1;
    } else {
        return 0;
    }
}

// Check if data exists in the process table
$sql = "SELECT COUNT(*) as count FROM process";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    // If data exists, display the message
    if ($count > 0) {
        echo '
        <div style="max-width: 600px; margin: 20px auto; background-color: #f8f9fa; border-left: 5px solid #F28018; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center;">
                <div style="margin-right: 15px;">
                    <i class="fas fa-sync fa-spin" style="font-size: 24px; color:rgb(0, 13, 15);"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: #F28018; font-weight: 600;">System Notice</h4>
                    <p style="margin: 10px 0 0; font-size: 16px;">The Planning Department is currently updating the Work Orders. Please check back soon for the latest information. Thank you for your patience</p>
                </div>
            </div>
        </div>';
    }
}

// Handle form submission for date range check
if(isset($_POST['submit'])){
    header("Location: testing789.php");
    exit;
}

// Data cleanup operations
$tables_to_clean = ['worder72', 'worder', 'dwork2'];
foreach ($tables_to_clean as $table) {
    $sql = "UPDATE `$table` SET `kgs` = REPLACE(`kgs`, ',', '')";
    $conn->query($sql);
}

// Delete and update complete_date entries
$deleteSQL = "DELETE cd FROM complete_date cd JOIN plannew pn ON cd.erp = pn.erp";
$conn->query($deleteSQL);

$insertSQL = "INSERT INTO `complete_date` (`erp`, `com_date`)
SELECT `erp`, MAX(`end_date`) AS `last_end_date`
FROM `plannew`
GROUP BY `erp`
ON DUPLICATE KEY UPDATE `com_date` = VALUES(`com_date`)";
$conn->query($insertSQL);

// Update press names in plannew table
$updatePressSQL = "UPDATE plannew
JOIN press_cavity ON plannew.cavity_id = press_cavity.cavity_id
JOIN press ON press_cavity.press_id = press.press_id
SET plannew.press_name = press.press_name";
$conn->query($updatePressSQL);

// Clear production_data table
$conn->query("DELETE FROM production_data");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Production Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
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

        h4 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 16px;
            text-align: left;
            padding-left: 10px;
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
            0%, 50%, 100% {
                opacity: 1;
            }
            25%, 75% {
                opacity: 0;
            }
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
        }

        .top-button {
            background-color: #F28018;
            color: #000000;
            padding: 10px 20px;
            border: none;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
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

        .production-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .production-table th, .production-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .production-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
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

        @keyframes blink {
            0% { visibility: visible; }
            50% { visibility: hidden; }
            100% { visibility: visible; }
        }

        .blinking-text {
            animation: blink 1s infinite;
        }

        .container {
            margin: 20px auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background-color: #f28018;
            color: #ffffff;
            font-weight: bold;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        .total-summary, .pattern-summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #F28018;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .summary-item {
            padding: 8px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            font-size: 14px;
        }

        .export-button {
            background-color: #F28018;
            color: #000000;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>

    <script>
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                window.location.href = 'planbuttoon.php';
            }
        });

        function highlightIfZeroOrIncomplete() {
            var table = document.querySelector('table');
            var rows = table.querySelectorAll('tr');

            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].querySelectorAll('td');
                var toBeProduceValue = parseInt(cells[7].innerText);
                var productionCompleteDate = cells[11].innerText;

                if (toBeProduceValue === 0) {
                    rows[i].style.backgroundColor = '#00FF00';
                }

                if (productionCompleteDate === '0000-00-00') {
                    rows[i].style.backgroundColor = '#FFFF00';
                }
            }
        }

        function redirectToAnotherPage(erpNumber) {
            window.location.href = 'planning3.php?erp=' + encodeURIComponent(erpNumber);
        }

        function exportToExcel() {
            let table = document.querySelector('table');
            let tableHtml = table.outerHTML;
            let blob = new Blob([tableHtml], { type: 'application/vnd.ms-excel' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'production_data.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        window.onload = function() {
            highlightIfZeroOrIncomplete();
        };
    </script>
</head>

<body>
    <div class="button-container">
        <button><a href="planbuttoon.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a></button>
    </div>

    <form method="post">
        <input type="submit" name="submit" value="Check Date Range">
    </form>

    <?php
    // Main data processing and display

    // Query to get all ERPs from process_plan table
    $processPlansQuery = "SELECT DISTINCT erp FROM process_plan";
    $processPlansResult = $conn->query($processPlansQuery);
    $processPlannedErps = [];

    if ($processPlansResult->num_rows > 0) {
        while ($row = $processPlansResult->fetch_assoc()) {
            $processPlannedErps[] = $row['erp'];
        }
    }

    // Define SQL queries to fetch data
    $sql1 = "SELECT 
                wo.erp, 
                wr.ref, 
                wr.wono,
                SUM(wr.new) AS total_new,
                DATE(wo.take_datetime) AS date,
                c.country,
                c.pattern
            FROM 
                work_order wo
                INNER JOIN worder wr ON wo.erp = wr.erp
                LEFT JOIN country c ON wo.erp = c.erp
            GROUP BY 
                wo.erp, 
                wr.ref,
                c.country,
                c.pattern";

    $sql2 = "SELECT 
                erp, 
                SUM(CASE WHEN tobe > 0 THEN tobe ELSE 0 END) AS total_positive_tobe
            FROM 
                tobeplan1
            GROUP BY 
                erp";

    $sql3 = "SELECT 
                erp, 
                SUM(kgs) AS total_kgs
            FROM 
                worder
            GROUP BY 
                erp";

    $sql4 = "SELECT erp, MAX(end_date) AS last_end_date FROM plannew GROUP BY erp";
    $sql5 = "SELECT erp, com_date FROM complete_date";

    // Execute SQL queries
    $result1 = $conn->query($sql1);
    $result2 = $conn->query($sql2);
    $result3 = $conn->query($sql3);
    $result4 = $conn->query($sql4);
    $result5 = $conn->query($sql5);

    // Combine results into one array
    $data = array();

    // Process first query results
    if ($result1->num_rows > 0) {
        while ($row = $result1->fetch_assoc()) {
            $erp = $row['erp'];
            if (!isset($data[$erp])) {
                $data[$erp] = array(
                    'ref' => $row['ref'],
                    'wono' => $row['wono'],
                    'total_new' => $row['total_new'],
                    'total_positive_tobe' => 0,
                    'total_kgs' => 0,
                    'date' => $row['date'],
                    'last_end_date' => '',
                    'cargo_ready_date' => '',
                    'country' => $row['country'],
                    'pattern' => $row['pattern']
                );
            }
        }
    }

    // Process remaining query results
    $data = processData($result2, $data, 'total_positive_tobe');
    $data = processData($result3, $data, 'total_kgs');
    $data = processData($result4, $data, 'last_end_date');

    // Fetch completion dates for each ERP
    $completion_dates = array();
    if ($result5->num_rows > 0) {
        while ($row = $result5->fetch_assoc()) {
            $erp = @$row['erp'];
            $com_date = @$row['com_date'];
            if (isset($erp) && isset($com_date)) {
                $completion_dates[$erp] = $com_date;
            }
        }
    }

    // Calculate cargo ready dates
    foreach ($data as $erp => $row) {
        $completion_date = isset($completion_dates[$erp]) ? $completion_dates[$erp] : '';

        if (!empty($completion_date)) {
            $cargo_ready_date = date('Y-m-d', strtotime($completion_date . ' +3 days'));
            $data[$erp]['cargo_ready_date'] = $cargo_ready_date;
        }
    }

    // Sort the data array
    uasort($data, 'sortByToBeProduce');

    // Insert data into production_data table
    foreach ($data as $erp => $row) {
        $completed_nos = $row['total_new'] - $row['total_positive_tobe'];
        $completion_date = isset($completion_dates[$erp]) ? $completion_dates[$erp] : '';
        
        $insertQuery = "INSERT INTO production_data (erp, work_order_no, customer_order_reference, country, pattern, quantity_nos, to_be_produced_nos, completed_nos, quantity_kgs, wo_release_date, production_complete_date, cargo_ready_date) VALUES ('{$erp}', '{$row['wono']}', '{$row['ref']}', '{$row['country']}', '{$row['pattern']}', '{$row['total_new']}', '{$row['total_positive_tobe']}', '{$completed_nos}', '{$row['total_kgs']}', '{$row['date']}', '{$completion_date}', '{$row['cargo_ready_date']}')";
        
        $conn->query($insertQuery);
    }

    // Query for current month dispatch data
    $currentMonthQuery = "SELECT pros.erp_number, 
                    GROUP_CONCAT(DISTINCT dwork2.wono SEPARATOR ', ') AS wonos,
                    GROUP_CONCAT(DISTINCT dwork2.ref SEPARATOR ', ') AS refs,
                    SUM(dwork2.quantity) AS total_quantity,
                    SUM(dwork2.kgs) AS total_quantity_kgs,
                    MAX(dwork2.date) AS wo_release_date,
                    pros.dispatch_date,
                    complete_date.com_date AS production_complete_date,
                    country.country AS country,
                    country.pattern AS pattern
             FROM pros 
             LEFT JOIN dwork2 ON pros.erp_number = dwork2.erp
             LEFT JOIN complete_date ON pros.erp_number = complete_date.erp
             LEFT JOIN country ON pros.erp_number = country.erp
             WHERE MONTH(pros.dispatch_date) = MONTH(CURRENT_DATE()) 
             AND YEAR(pros.dispatch_date) = YEAR(CURRENT_DATE())
             GROUP BY pros.erp_number, pros.dispatch_date, complete_date.com_date, country.country, country.pattern
             ORDER BY pros.dispatch_date ASC";

    $currentMonthResult = $conn->query($currentMonthQuery);

    // Query for production data
    $productionQuery = "SELECT 
                        pd.*, 
                        c.pattern 
                    FROM production_data pd 
                    LEFT JOIN country c ON pd.erp = c.erp 
                    ORDER BY pd.production_complete_date ASC";
    $productionResult = $conn->query($productionQuery);

    // Query for next month's FCL and LCL counts
    $nextMonthQuery = "SELECT 
                        SUM(CASE WHEN LOWER(pattern) = 'fcl' THEN 1 ELSE 0 END) AS next_month_fcl_count,
                        SUM(CASE WHEN LOWER(pattern) = 'lcl' THEN 1 ELSE 0 END) AS next_month_lcl_count
                       FROM production_data 
                       WHERE MONTH(production_complete_date) = MONTH(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH))
                       AND YEAR(production_complete_date) = YEAR(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH))";
    $nextMonthResult = $conn->query($nextMonthQuery);
    $nextMonthFclCount = 0;
    $nextMonthLclCount = 0;

    if ($nextMonthResult->num_rows > 0) {
        $row = $nextMonthResult->fetch_assoc();
        $nextMonthFclCount = (int)($row['next_month_fcl_count'] ?? 0);
        $nextMonthLclCount = (int)($row['next_month_lcl_count'] ?? 0);
    }

    // Combined results array
    $combinedResults = [];

    // Fetch current month results
    if ($currentMonthResult->num_rows > 0) {
        while ($row = $currentMonthResult->fetch_assoc()) {
            $cargo_ready_date = '';
            if (!empty($row['production_complete_date'])) {
                $cargo_ready_date = date('Y-m-d', strtotime($row['production_complete_date'] . ' +3 days'));
            }
            
            $combinedResults[] = [
                'erp_number' => $row['erp_number'],
                'wonos' => $row['wonos'],
                'refs' => $row['refs'],
                'country' => $row['country'],
                'pattern' => $row['pattern'],
                'total_quantity' => $row['total_quantity'],
                'total_quantity_kgs' => $row['total_quantity_kgs'],
                'wo_release_date' => $row['wo_release_date'],
                'dispatch_date' => $row['dispatch_date'],
                'production_complete_date' => $row['production_complete_date'],
                'cargo_ready_date' => $cargo_ready_date,
                'to_be_produced_nos' => '',
                'completed_nos' => $row['total_quantity'],
            ];
        }
    }

    // Fetch production data results
    if ($productionResult->num_rows > 0) {
        while ($row = $productionResult->fetch_assoc()) {
            $combinedResults[] = [
                'erp_number' => $row['erp'],
                'wonos' => $row['work_order_no'],
                'refs' => $row['customer_order_reference'],
                'country' => $row['country'],
                'pattern' => $row['pattern'],
                'total_quantity' => $row['quantity_nos'],
                'total_quantity_kgs' => $row['quantity_kgs'],
                'wo_release_date' => $row['wo_release_date'],
                'dispatch_date' => 'Pending',
                'production_complete_date' => $row['production_complete_date'],
                'cargo_ready_date' => $row['cargo_ready_date'],
                'to_be_produced_nos' => $row['to_be_produced_nos'],
                'completed_nos' => $row['completed_nos'],
            ];
        }
    }

    // Calculate totals
    $totalQuantityNos = 0;
    $totalToBeProducedNos = 0;
    $totalCompletedNos = 0;
    $totalQuantityKgs = 0;

    // Calculate LCL and FCL counts
    $lclCount = 0;
    $fclCount = 0;
    $dispatchedLclCount = 0;
    $dispatchedFclCount = 0;

    foreach ($combinedResults as $row) {
        $totalQuantityNos += (int)$row['total_quantity'];
        $totalToBeProducedNos += (int)($row['to_be_produced_nos'] ?? 0);
        $totalCompletedNos += (int)($row['completed_nos'] ?? 0);
        $totalQuantityKgs += (int)$row['total_quantity_kgs'];
        if (strtolower($row['pattern']) == 'lcl') {
            $lclCount++;
            if ($row['dispatch_date'] != 'Pending') {
                $dispatchedLclCount++;
            }
        }
        if (strtolower($row['pattern']) == 'fcl') {
            $fclCount++;
            if ($row['dispatch_date'] != 'Pending') {
                $dispatchedFclCount++;
            }
        }
    }

    // Calculate remaining LCL and FCL counts
    $remainingLclCount = $lclCount - $nextMonthLclCount;
    $remainingFclCount = $fclCount - $nextMonthFclCount;

    // Display export button
    echo '<button onclick="exportToExcel()" class="export-button">Export to Excel</button>';

    // Display FCL/LCL
    echo "<div class='pattern-summary'>
            <h2>FCL/LCL</h2>
            <div class='summary-grid'>
                <div class='summary-item'><strong>Total LCL Count:</strong> " . $lclCount . "</div>
                <div class='summary-item'><strong>Total FCL Count:</strong> " . $fclCount . "</div>
                <div class='summary-item'><strong>Dispatched LCL Count:</strong> " . $dispatchedLclCount . "</div>
                <div class='summary-item'><strong>Dispatched FCL Count:</strong> " . $dispatchedFclCount . "</div>
                <div class='summary-item'><strong>Next Month LCL Count:</strong> " . $nextMonthLclCount . "</div>
                <div class='summary-item'><strong>Next Month FCL Count:</strong> " . $nextMonthFclCount . "</div>
                <div class='summary-item'><strong>This Month LCL Count:</strong> " . $remainingLclCount . "</div>
                <div class='summary-item'><strong>This Month FCL Count:</strong> " . $remainingFclCount . "</div>
            </div>
          </div>";

    // Display Total Summary
    echo "<div class='total-summary'>
            <h2>Total Summary</h2>
            <div class='summary-grid'>
                <div class='summary-item'><strong>Total Quantity (Nos):</strong> " . number_format($totalQuantityNos) . "</div>
                <div class='summary-item'><strong>Total To be Produced (Nos):</strong> " . number_format($totalToBeProducedNos) . "</div>
                <div class='summary-item'><strong>Total Completed (Nos):</strong> " . number_format($totalCompletedNos) . "</div>
                <div class='summary-item'><strong>Total Quantity (Kgs):</strong> " . number_format($totalQuantityKgs) . "</div>
            </div>
          </div>";

    // Display main data table
    echo "<table border='1'>";
    echo "<tr>
            <th>#</th>
            <th>ERP</th>
            <th>Work Order No</th>
            <th>Customer Order <br> Reference</th>
            <th>Country</th>
            <th>Pattern</th>
            <th>Quantity <br>(Nos)</th>
            <th>To be <br> Produce <br>(Nos)</th>
            <th>Completed <br>(Nos)</th>
            <th>Quantity <br>(Kgs)</th>
            <th>WO Release <br> Date</th>
            <th>Production <br> Complete <br>Date</th>
            <th>Cargo Ready <br> Date</th>
            <th>Dispatch <br> Date</th>
            <th>Dispatch Month</th>
            <th>Check Order</th>
          </tr>";

    $nextMonth = date('n', strtotime('first day of next month'));
    $nextYear = date('Y', strtotime('first day of next month'));

    if (!empty($combinedResults)) {
        foreach ($combinedResults as $index => $row) {
            $highlight = '';
            
            if (in_array($row['erp_number'], $processPlannedErps)) {
                $highlight = 'background-color:rgb(253, 255, 153);';
            } elseif (date('n', strtotime($row['production_complete_date'])) == $nextMonth && 
                      date('Y', strtotime($row['production_complete_date'])) == $nextYear) {
                $highlight = 'background-color: #ADD8E6;';
            } elseif (empty($row['to_be_produced_nos'])) {
                $highlight = 'background-color: #FFCCCC;';
            }
        
            echo "<tr style='$highlight'>
                    <td>" . ($index + 1) . "</td>
                    <td>" . $row['erp_number'] . "</td>
                    <td>" . $row['wonos'] . "</td>
                    <td>" . $row['refs'] . "</td>
                    <td>" . $row['country'] . "</td>
                    <td>" . $row['pattern'] . "</td>
                    <td>" . $row['total_quantity'] . "</td>
                    <td>" . $row['to_be_produced_nos'] . "</td>
                    <td>" . $row['completed_nos'] . "</td>
                    <td>" . number_format($row['total_quantity_kgs']) . "</td> 
                    <td>" . $row['wo_release_date'] . "</td>
                    <td>" . $row['production_complete_date'] . "</td>
                    <td>" . $row['cargo_ready_date'] . "</td>
                    <td>" . $row['dispatch_date'] . "</td>
                    <td>" . date('F', strtotime($row['production_complete_date'] ?? '')) . "</td>
                    <td><button onclick='redirectToAnotherPage(\"{$row['erp_number']}\")'>Check</button></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='16'>No results found.</td></tr>";
    }

    echo "</table>";

    // Display Hold Work Orders section
    $holdQuery = "SELECT 
        erp,
        ref,
        wono,
        Customer,
        SUM(COALESCE(new, 0)) AS total_new,
        SUM(COALESCE(CAST(REPLACE(kgs, ',', '') AS DECIMAL), 0)) AS total_kgs 
    FROM 
        worder72 
    GROUP BY 
        erp,
        ref,
        wono,
        Customer 
    ORDER BY 
        total_new DESC,
        total_kgs DESC";

    $holdResult = $conn->query($holdQuery);

    if ($holdResult) {
        echo "<h4>HOLD WORK ORDERS</h4>";
        echo "<table border='1'>
                <tr>
                    <th>ERP</th>
                    <th>Reference</th>
                    <th>Work Order</th>
                    <th>Customer</th>
                    <th>Total New</th>
                    <th>Total KGS</th>
                </tr>";

        while ($row = $holdResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['erp'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['ref'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['wono'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['Customer'] ?? 'N/A') . "</td>";
            echo "<td>" . number_format($row['total_new'], 0) . "</td>";
            echo "<td>" . number_format($row['total_kgs'], 2) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
        $holdResult->free();
    }

    // Close connection
    $conn->close();
    ?>

</body>
</html>