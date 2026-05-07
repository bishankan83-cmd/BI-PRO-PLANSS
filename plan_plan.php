<?php
// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get parameters
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$shift = isset($_GET['shift']) ? $_GET['shift'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get available shifts from database
$shiftsQuery = "SELECT DISTINCT shift FROM production_plan56 ORDER BY shift";
$shiftsStmt = $pdo->query($shiftsQuery);
$availableShifts = $shiftsStmt->fetchAll(PDO::FETCH_COLUMN);

// If no shift selected, use first available shift
if (empty($shift) && !empty($availableShifts)) {
    $shift = $availableShifts[0];
}

$reportData = [];
$totalPlan = 0;
$totalPlanWeight = 0;
$uniqueCavityCount = 0;

// Handle Excel Export
if ($action === 'export_excel' && !empty($date) && !empty($shift)) {
    $sql = "SELECT 
                pp.press_name,
                pp.icode,
                pp.plan,
                pp.DATE,
                pp.shift,
                COALESCE(r.t_size, '') as t_size,
                COALESCE(r.brand, '') as brand,
                COALESCE(r.col, '') as colour,
                COALESCE(r.rim, '') as rim,
                COALESCE(ts.gweight, 0) as green_weight,
                COALESCE(ts.rim_size, '') as tire_rim_size,
                COALESCE(ts.spec, '') as tire_spec,
                COALESCE((SELECT SUM(tobe) FROM tobeplan1 WHERE icode = pp.icode AND tobe > 0), 0) as total_tobe,
                COALESCE(ps.id, 999999) as press_order
            FROM production_plan56 pp
            LEFT JOIN realstock r ON pp.icode = r.icode
            LEFT JOIN tire_spec ts ON pp.icode = ts.icode
            LEFT JOIN press_set ps ON pp.press_name = ps.press_name
            WHERE pp.DATE = :date AND pp.shift = :shift
            ORDER BY press_order, pp.icode";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':date' => $date, ':shift' => $shift]);
    $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalPlan = 0;
    $totalPlanWeight = 0;
    foreach ($exportData as &$row) {
        $row['plan_weight'] = $row['green_weight'] * $row['plan'];
        $totalPlan += $row['plan'];
        $totalPlanWeight += $row['plan_weight'];
    }
    unset($row);
    
    $uniqueCavityCount = count($exportData);
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Production_Plan_' . $date . '_Shift_' . $shift . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output Excel content
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
    echo 'th { background-color: #F28018; color: white; font-weight: bold; }';
    echo '.footer-row { background-color: #e0e0e0; font-weight: bold; }';
    echo '.number { mso-number-format:"0.00"; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    echo '<h2>Production Plan Report</h2>';
    echo '<p>Date: ' . htmlspecialchars($date) . ' | Shift: ' . htmlspecialchars($shift) . '</p>';
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Press Name</th>';
    echo '<th>Item Code</th>';
    echo '<th>Description</th>';
    echo '<th>Rim Size</th>';
    echo '<th>Brand</th>';
    echo '<th>Type</th>';
    echo '<th>Spec</th>';
    echo '<th>Color</th>';
    echo '<th>Green Weight</th>';
    echo '<th>Total ToBe</th>';
    echo '<th>Plan</th>';
    echo '<th>Plan Weight</th>';
    echo '<th>Black</th>';
    echo '<th>NM</th>';
    echo '<th>Prod</th>';
    echo '<th>Loss</th>';
    echo '<th>Remark</th>';
    echo '</tr>';
    
    foreach ($exportData as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['press_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['icode']) . '</td>';
        echo '<td>' . htmlspecialchars($row['t_size']) . '</td>';
        echo '<td>' . htmlspecialchars($row['tire_rim_size']) . '</td>';
        echo '<td>' . htmlspecialchars($row['brand']) . '</td>';
        echo '<td>' . htmlspecialchars($row['rim']) . '</td>';
        echo '<td>' . htmlspecialchars($row['tire_spec']) . '</td>';
        echo '<td>' . htmlspecialchars($row['colour']) . '</td>';
        echo '<td class="number">' . number_format($row['green_weight'], 2) . '</td>';
        echo '<td>' . number_format($row['total_tobe']) . '</td>';
        echo '<td>' . htmlspecialchars($row['plan']) . '</td>';
        echo '<td class="number">' . number_format($row['plan_weight'], 2) . '</td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '</tr>';
    }
    
    echo '<tr class="footer-row">';
    echo '<td colspan="10">Total</td>';
    echo '<td>' . $totalPlan . '</td>';
    echo '<td class="number">' . number_format($totalPlanWeight, 2) . '</td>';
    echo '<td colspan="5"></td>';
    echo '</tr>';
    
    echo '<tr class="footer-row">';
    echo '<td colspan="10">Unique Cavity IDs</td>';
    echo '<td>' . $uniqueCavityCount . '</td>';
    echo '<td colspan="6"></td>';
    echo '</tr>';
    
    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;
}

// Generate report
if ($action === 'generate_report') {
    $sql = "SELECT 
                pp.press_name,
                pp.icode,
                pp.plan,
                pp.DATE,
                pp.shift,
                COALESCE(r.t_size, '') as t_size,
                COALESCE(r.brand, '') as brand,
                COALESCE(r.col, '') as colour,
                COALESCE(r.rim, '') as rim,
                COALESCE(ts.gweight, 0) as green_weight,
                COALESCE(ts.rim_size, '') as tire_rim_size,
                COALESCE(ts.spec, '') as tire_spec,
                '' as description,
                '' as type,
                '' as mold_id,
                '' as cavity_id,
                COALESCE((SELECT SUM(tobe) FROM tobeplan1 WHERE icode = pp.icode AND tobe > 0), 0) as total_tobe,
                '' as black,
                '' as nm,
                '' as prod,
                '' as loss,
                '' as remark,
                COALESCE(ps.id, 999999) as press_order
            FROM production_plan56 pp
            LEFT JOIN realstock r ON pp.icode = r.icode
            LEFT JOIN tire_spec ts ON pp.icode = ts.icode
            LEFT JOIN press_set ps ON pp.press_name = ps.press_name
            WHERE pp.DATE = :date AND pp.shift = :shift
            ORDER BY press_order, pp.icode";
    
    $stmt = $pdo->prepare($sql);
    
    $params = [':date' => $date, ':shift' => $shift];
    
    $stmt->execute($params);
    
    $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate plan_weight for each row and totals
    $totalPlan = 0;
    $totalPlanWeight = 0;
    
    foreach ($reportData as &$row) {
        // Calculate plan_weight = green_weight * plan
        $row['plan_weight'] = $row['green_weight'] * $row['plan'];
        
        $totalPlan += $row['plan'];
        $totalPlanWeight += $row['plan_weight'];
    }
    unset($row); // Break reference
    
    // Set unique cavity count to the number of rows displayed
    $uniqueCavityCount = count($reportData);
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
            max-width: 1400px;
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

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 13px;
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
        <h1>Production Planning System</h1>
        
        <div class="filters">
            <form method="GET" action="">
                <label>Date:</label>
                <input type="date" name="date" value="<?php echo $date; ?>" required>
                
                <label>Shift:</label>
                <select name="shift" required>
                    <option value="">-- Select Shift --</option>
                    <?php foreach ($availableShifts as $shiftOption): ?>
                    <option value="<?php echo htmlspecialchars($shiftOption); ?>" 
                            <?php echo $shift == $shiftOption ? 'selected' : ''; ?>>
                        Shift <?php echo htmlspecialchars($shiftOption); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="hidden" name="action" value="generate_report">
                <button type="submit" class="btn">Generate Report</button>
            </form>
        </div>
        
        <?php if (!empty($reportData)): ?>
        <h2>Production Plan Report</h2>
        <p>Date: <?php echo $date; ?> | Shift: <?php echo $shift; ?></p>
        
        <div class="actions">
            <a href="?date=<?php echo urlencode($date); ?>&shift=<?php echo urlencode($shift); ?>&action=export_excel" class="btn btn-success">
                📥 Download Excel
            </a>
        </div>
        
        <div style="overflow-x: auto;">
            <table>
                <tr>
                    <th>Press Name</th>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Rim Size</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Spec</th>
                    <th>Color</th>
                    <th>Green Weight</th>
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
                    <td><?php echo htmlspecialchars($row['press_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['icode']); ?></td>
                    <td><?php echo htmlspecialchars($row['t_size']); ?></td>
                    <td><?php echo htmlspecialchars($row['tire_rim_size']); ?></td>
                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                    <td><?php echo htmlspecialchars($row['rim']); ?></td>
                    <td><?php echo htmlspecialchars($row['tire_spec']); ?></td>
                    <td><?php echo htmlspecialchars($row['colour']); ?></td>
                    <td><?php echo number_format($row['green_weight'], 2); ?></td>
                    <td><?php echo number_format($row['total_tobe']); ?></td>
                    <td><?php echo htmlspecialchars($row['plan']); ?></td>
                    <td><?php echo number_format($row['plan_weight'], 2); ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
                
                <tr class="footer-row">
                    <td colspan="10">Total</td>
                    <td><?php echo $totalPlan; ?></td>
                    <td><?php echo number_format($totalPlanWeight, 2); ?></td>
                    <td colspan="5"></td>
                </tr>
                
                <tr class="footer-row">
                    <td colspan="10">Unique Cavity IDs</td>
                    <td><?php echo $uniqueCavityCount; ?></td>
                    <td colspan="6"></td>
                </tr>
            </table>
        </div>
        <?php elseif ($action === 'generate_report'): ?>
        <p>No data found for the selected date and shift.</p>
        <?php else: ?>
        <p>Please select a date and shift, then click "Generate Report".</p>
        <?php endif; ?>
    </div>
</body>
</html>