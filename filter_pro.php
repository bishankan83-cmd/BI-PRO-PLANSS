


<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to delete all data from the table
$sql = "DELETE FROM filtered_data";

if ($conn->query($sql) === TRUE) {
    //echo "All records deleted successfully";
} else {
  //  echo "Error deleting records: " . $conn->error;
}

$conn->close();
?>




<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL statement to delete all data from cal table
$sql_delete = "DELETE FROM `cal`";

if ($conn->query($sql_delete) === TRUE) {
   // echo "All data deleted successfully from cal table.";
} else {
    //echo "Error deleting data: " . $conn->error;
}

// Retrieve user input
$date = $_GET['date'];
$shift = $_GET['shift'];

// Prepare SQL query to fetch filtered data from calculated_data23
$sql_select = "SELECT `id`, `erp`, `icode`, `mold_id`, `cavity_id`, `start_date`, `end_date`, `tires_per_mold`, `plan`
               FROM `calculated_data23`
               WHERE `date` = ? AND `shift` = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("ss", $date, $shift);
$stmt_select->execute();
$result = $stmt_select->get_result();

// Prepare SQL statement for insertion into cal
$sql_insert = "INSERT INTO `cal` (`id`, `erp`, `icode`, `mold_id`, `cavity_id`, `start_date`, `end_date`, `tires_per_mold`, `plan`)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

// Bind parameters for insertion
$stmt_insert->bind_param("isssssiii", $id, $erp, $icode, $mold_id, $cavity_id, $start_date, $end_date, $tires_per_mold, $plan);

// Iterate over fetched rows and insert into cal
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $erp = $row['erp'];
    $icode = $row['icode'];
    $mold_id = $row['mold_id'];
    $cavity_id = $row['cavity_id'];
    $start_date = $row['start_date'];
    $end_date = $row['end_date'];
    $tires_per_mold = $row['tires_per_mold'];
    $plan = $row['plan'];

    // Execute insertion
    $stmt_insert->execute();
}

// Prepare SQL statement to insert filtered date and shift into filtered_data table
$sql_insert_filtered = "INSERT INTO `filtered_data` (`date`, `shift`) VALUES (?, ?)";
$stmt_insert_filtered = $conn->prepare($sql_insert_filtered);
$stmt_insert_filtered->bind_param("ss", $date, $shift);

// Execute insertion into filtered_data table
if ($stmt_insert_filtered->execute() === TRUE) {
   // echo "Filtered date and shift inserted successfully into filtered_data table.";
} else {
    //echo "Error inserting filtered date and shift: " . $conn->error;
}

//echo "Data inserted successfully.";

// Close statements and connection
$stmt_select->close();
$stmt_insert->close();
$stmt_insert_filtered->close();
$conn->close();
?>








<?php
// Database configuration
$config = [
    'servername' => "localhost",
    'username' => "planatir_task_managemen",
    'password' => "Bishan@1919",
    'dbname' => "planatir_task_managemen"
];

// Establish database connection function
function connectToDatabase($config) {
    try {
        $pdo = new PDO("mysql:host={$config['servername']};dbname={$config['dbname']}", 
                       $config['username'], 
                       $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to clear tables
function clearTables($pdo, $tables) {
    try {
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("DELETE FROM `$table`");
            $stmt->execute();
            echo "All records deleted successfully from $table table.<br>";
        }
        return true;
    } catch (PDOException $e) {
        echo "Error deleting records: " . $e->getMessage();
        return false;
    }
}

// Function to filter and load data
function filterAndLoadData($pdo, $date, $shift) {
    try {
        // 1. Select data from calculated_data23 based on date and shift
        $stmt_select = $pdo->prepare("
            SELECT `id`, `erp`, `icode`, `mold_id`, `cavity_id`, `start_date`, `end_date`, `tires_per_mold`, `plan`
            FROM `calculated_data23`
            WHERE `date` = ? AND `shift` = ?
        ");
        $stmt_select->execute([$date, $shift]);
        $results = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Insert selected data into cal table
        $stmt_insert = $pdo->prepare("
            INSERT INTO `cal` (`id`, `erp`, `icode`, `mold_id`, `cavity_id`, `start_date`, `end_date`, `tires_per_mold`, `plan`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($results as $row) {
            $stmt_insert->execute([
                $row['id'], 
                $row['erp'], 
                $row['icode'], 
                $row['mold_id'], 
                $row['cavity_id'], 
                $row['start_date'], 
                $row['end_date'], 
                $row['tires_per_mold'], 
                $row['plan']
            ]);
        }
        
        // 3. Record the filter criteria in filtered_data table
        $stmt_filter = $pdo->prepare("
            INSERT INTO `filtered_data` (`date`, `shift`) 
            VALUES (?, ?)
        ");
        $stmt_filter->execute([$date, $shift]);
        
        echo "Data filtered and loaded successfully.<br>";
        return true;
    } catch (PDOException $e) {
        echo "Error processing data: " . $e->getMessage();
        return false;
    }
}

// Function to generate comprehensive report
function generateReport($pdo) {
    try {
        // 1. Clear previous report data
        $stmt_delete = $pdo->prepare("DELETE FROM cal_report");
        $stmt_delete->execute();
        
        // 2. Generate new report data with joins across multiple tables
        $stmt = $pdo->prepare("
            SELECT cal.*, tire_details.description, tire_details.rim, tire_details.brand, 
                   tire_details.type, tire_details.colour, press_cavity.press_id, 
                   press.press_name, bom_new.`grand totalcompound weight`, 
                   bom_new.`green tire weight`, DATE(filtered_data.date) AS new_process_start_date, 
                   new_process.erp
            FROM cal
            LEFT JOIN tire_details ON cal.icode = tire_details.icode
            LEFT JOIN press_cavity ON cal.cavity_id = press_cavity.cavity_id
            LEFT JOIN press ON press_cavity.press_id = press.press_id
            LEFT JOIN alp ON press.press_name = alp.press_name
            LEFT JOIN bom_new ON cal.icode = bom_new.icode
            LEFT JOIN new_process ON cal.icode = new_process.icode 
                              AND cal.mold_id = new_process.mold_id 
                              AND cal.erp = new_process.erp 
                              AND cal.cavity_id = new_process.cavity_id
            LEFT JOIN filtered_data ON DATE(new_process.start_date) = DATE(filtered_data.date)
            ORDER BY alp.id ASC, cal.cavity_id ASC
        ");
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt_tobe = $pdo->prepare("
        SELECT icode, SUM(tobe) as total_tobe 
        FROM tobeplan1
        WHERE tobe > 0
        GROUP BY icode
    ");

        $stmt_tobe->execute();
        $tobe_data = $stmt_tobe->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Aggregate results by press_name, icode, and mold_id
        $aggregatedResults = [];
        $uniqueCavityIds = [];
        
        foreach ($results as $row) {
            if ($row['plan'] == 0) continue;
            
            // Create a unique key for each combination of press_name, icode, and mold_id
            $key = $row['press_name'] . '_' . $row['icode'] . '_' . $row['mold_id'];
            
            // Record unique cavity IDs
            $uniqueCavityIds[$row['cavity_id']] = true;
            
            // Calculate remark value
            $remark = '';
            if ($row['new_process_start_date'] && $row['erp']) {
                $remark = 'tobe started';
            }
            
            // Get total_tobe from our new lookup array
            $total_tobe = isset($tobe_data[$row['icode']]) ? $tobe_data[$row['icode']] : 0;
            
            // If this combination already exists, add to its plan value
            if (isset($aggregatedResults[$key])) {
                $aggregatedResults[$key]['plan'] += $row['plan'];
            } else {
                // Otherwise, create a new entry
                $aggregatedResults[$key] = [
                    'press_name' => $row['press_name'],
                    'icode' => $row['icode'],
                    'description' => $row['description'],
                    'rim' => $row['rim'],
                    'brand' => $row['brand'],
                    'type' => $row['type'],
                    'colour' => $row['colour'],
                    'grand totalcompound weight' => $row['grand totalcompound weight'],
                    'mold_id' => $row['mold_id'],
                    'total_tobe' => $total_tobe,
                    'plan' => $row['plan'],
                    'remark' => $remark
                ];
            }
        }
        
        // 3. Insert the aggregated data into the report table
        $stmt_insert = $pdo->prepare("
            INSERT INTO cal_report (
                press_name, icode, description, rim, brand, type, colour, 
                green_weight, mold_id, total_tobe, plan, plan_weight, 
                black, nm, prod, loss, remark
            ) VALUES (
                :press_name, :icode, :description, :rim, :brand, :type, :colour,
                :green_weight, :mold_id, :total_tobe, :plan, :plan_weight,
                :black, :nm, :prod, :loss, :remark
            )
        ");
        
        $totalPlan = 0;
        
        foreach ($aggregatedResults as $row) {
            // Calculate Plan * Green Tire Weight
            $planGreenTireWeight = ($row['plan'] * $row['grand totalcompound weight']);
            
            $stmt_insert->execute([
                ':press_name' => $row['press_name'],
                ':icode' => $row['icode'],
                ':description' => $row['description'],
                ':rim' => $row['rim'],
                ':brand' => $row['brand'],
                ':type' => $row['type'],
                ':colour' => $row['colour'],
                ':green_weight' => $row['grand totalcompound weight'],
                ':mold_id' => $row['mold_id'],
                ':total_tobe' => $row['total_tobe'],
                ':plan' => $row['plan'],
                ':plan_weight' => $planGreenTireWeight,
                ':black' => '',
                ':nm' => '',
                ':prod' => '',
                ':loss' => '',
                ':remark' => $row['remark']
            ]);
            
            $totalPlan += $row['plan'];
        }
        
        return [
            'report_data' => getReportData($pdo),
            'total_plan' => $totalPlan,
            'unique_cavity_count' => count($uniqueCavityIds)
        ];
    } catch (PDOException $e) {
        echo "Error generating report: " . $e->getMessage();
        return null;
    }
}

// Function to get report data
function getReportData($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM cal_report");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to export data to Excel
function exportToExcel($reportData, $filename = "production_plan") {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Start output buffering
    ob_start();
    
    echo '<table border="1">';
    echo '<tr><th>Press Name</th><th>Item Code</th><th>Description</th><th>Rim</th><th>Brand</th><th>Type</th><th>Color</th><th>Green Weight</th><th>Mold ID</th><th>Total ToBe</th><th>Plan</th><th>Plan Weight</th><th>Black</th><th>NM</th><th>Prod</th><th>Loss</th><th>Remark</th></tr>';
    
    foreach ($reportData as $row) {
        echo '<tr>';
        echo '<td>' . $row['press_name'] . '</td>';
        echo '<td>' . $row['icode'] . '</td>';
        echo '<td>' . $row['description'] . '</td>';
        echo '<td>' . $row['rim'] . '</td>';
        echo '<td>' . $row['brand'] . '</td>';
        echo '<td>' . $row['type'] . '</td>';
        echo '<td>' . $row['colour'] . '</td>';
        echo '<td>' . $row['green_weight'] . '</td>';
        echo '<td>' . $row['mold_id'] . '</td>';
        echo '<td>' . $row['total_tobe'] . '</td>';
        echo '<td>' . $row['plan'] . '</td>';
        echo '<td>' . $row['plan_weight'] . '</td>';
        echo '<td>' . $row['black'] . '</td>';
        echo '<td>' . $row['nm'] . '</td>';
        echo '<td>' . $row['prod'] . '</td>';
        echo '<td>' . $row['loss'] . '</td>';
        echo '<td>' . $row['remark'] . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    $excelData = ob_get_contents();
    ob_end_clean();
    
    echo $excelData;
    exit;
}

// Check if this is an export request
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $pdo = connectToDatabase($config);
    $reportData = getReportData($pdo);
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $shift = isset($_GET['shift']) ? $_GET['shift'] : '';
    exportToExcel($reportData, "production_plan_$date" . ($shift ? "_$shift" : ""));
}

// Main application logic starts here
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$shift = isset($_GET['shift']) ? $_GET['shift'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

$pdo = connectToDatabase($config);
$message = '';

// Process form actions
if (!empty($action)) {
    switch ($action) {
        case 'filter':
            // Clear necessary tables first
            clearTables($pdo, ['cal', 'filtered_data']);
            
            // Filter and load data
            if (filterAndLoadData($pdo, $date, $shift)) {
                $message = "Data successfully filtered for date: $date, shift: $shift";
            } else {
                $message = "Error occurred while filtering data.";
            }
            break;
            
        case 'generate_report':
            $reportData = generateReport($pdo);
            if ($reportData) {
                $message = "Report generated successfully.";
            } else {
                $message = "Error generating report.";
            }
            break;
    }
}

// Get report data for display
$reportData = [];
$totalPlan = 0;
$uniqueCavityCount = 0;

$fullReport = generateReport($pdo);
if ($fullReport) {
    $reportData = $fullReport['report_data'];
    $totalPlan = $fullReport['total_plan'];
    $uniqueCavityCount = $fullReport['unique_cavity_count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Production Planning System</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
            background-color: #ffffff;
            margin: 0;
            padding: 20px;
        }

        h1, h2 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .alert {
            background-color: #f2f2f2;
            border-left: 5px solid #F28018;
            padding: 10px;
            margin-bottom: 20px;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        .filters {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }

        .filters label {
            display: inline-block;
            width: 80px;
            margin-right: 10px;
        }

        .filters input, .filters select {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn {
            background-color: #F28018;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #d96a0c;
        }

        .btn-secondary {
            background-color: #333;
        }

        .btn-secondary:hover {
            background-color: #555;
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

        th {
            background-color: #F28018;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .footer-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .actions {
            margin-top: 20px;
            text-align: left;
        }
        
        @keyframes blink {
            0%, 50%, 100% {
                opacity: 1;
            }
            25%, 75% {
                opacity: 0;
            }
        }

        .blinking-text {
            animation: blink 1s infinite;
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
    </style>
</head>
<body>
    <div class="container">
       
        
        <div class="actions">
            <form method="GET" action="" style="display: inline-block;">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" name="shift" value="<?php echo $shift; ?>">
                <input type="hidden" name="action" value="generate_report">
                <button type="submit" class="btn">Generate Report</button>
            </form>
            
            <form method="GET" action="" style="display: inline-block;">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" name="shift" value="<?php echo $shift; ?>">
                <input type="hidden" name="action" value="export">
                <button type="submit" class="btn btn-secondary">Export to Excel</button>
            </form>
        </div>
        
        <?php if (!empty($reportData)): ?>
        <h2>Production Plan Report</h2>
        <p>Date: <?php echo $date; ?> | Shift: <?php echo $shift; ?></p>
        
        <div style="overflow-x: auto;">
            <table>
                <tr>
                    <th>Press Name</th>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Rim</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Color</th>
                    <th>Green Weight</th>
                    <th>Mold ID</th>
                    <th>Total ToBe</th>
                    <th>Plan</th>
                    <th>Plan Weight</th>
                    <th>Black</th>
                    <th>NM</th>
                    <th>Prod</th>
                    <th>Loss</th>
                    <th>Remark</th>
                </tr>
                
                <?php foreach ($reportData as $row): ?>
                <tr>
                    <td><?php echo $row['press_name']; ?></td>
                    <td><?php echo $row['icode']; ?></td>
                    <td><?php echo $row['description']; ?></td>
                    <td><?php echo $row['rim']; ?></td>
                    <td><?php echo $row['brand']; ?></td>
                    <td><?php echo $row['type']; ?></td>
                    <td><?php echo $row['colour']; ?></td>
                    <td><?php echo $row['green_weight']; ?></td>
                    <td><?php echo $row['mold_id']; ?></td>
                    <td><?php echo $row['total_tobe']; ?></td>
                    <td><?php echo $row['plan']; ?></td>
                    <td><?php echo $row['plan_weight']; ?></td>
                    <td><?php echo $row['black']; ?></td>
                    <td><?php echo $row['nm']; ?></td>
                    <td><?php echo $row['prod']; ?></td>
                    <td><?php echo $row['loss']; ?></td>
                    <td><?php echo $row['remark']; ?></td>
                </tr>
                <?php endforeach; ?>
                
                <tr class="footer-row">
                    <td colspan="9"></td>
                    <td>Total</td>
                    <td><?php echo $totalPlan; ?></td>
                    <td colspan="6"></td>
                </tr>
                
                <tr class="footer-row">
                    <td colspan="9"></td>
                    <td>Unique Cavity IDs</td>
                    <td><?php echo $uniqueCavityCount; ?></td>
                    <td colspan="6"></td>
                </tr>
            </table>
        </div>
        <?php else: ?>
        <p>No report data available. Please select a date and shift, then click "Generate Report".</p>
        <?php endif; ?>
    </div>
</body>
</html>