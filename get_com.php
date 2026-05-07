<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// CSV Export functionality
if (isset($_GET['export']) && $_GET['export'] == 'csv' && !empty($_GET['erp'])) {
    $selectedErps = $_GET['erp'];
    $exportType = $_GET['export_type'] ?? 'daily_bom';
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tire_production_' . $exportType . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Export based on type
    if ($exportType == 'daily_bom') {
        exportDailyBOM($pdo, $selectedErps, $output);
    } elseif ($exportType == 'daily_breakdown') {
        exportDailyBreakdown($pdo, $selectedErps, $output);
    } elseif ($exportType == 'summary') {
        exportSummary($pdo, $selectedErps, $output);
    } elseif ($exportType == 'all_data') {
        exportAllData($pdo, $selectedErps, $output);
    }
    
    fclose($output);
    exit();
}

// Export Functions
function exportDailyBOM($pdo, $selectedErps, $output) {
    $componentLabels = [
        'a' => 'ATPRS', 'b' => 'B-ATS 15', 'c' => 'B-BNS 24', 'd' => 'BG-BLS 12',
        'e' => 'CG - BS 901', 'f' => 'C - SMS 501', 'g' => 'C-ATS 20', 'h' => 'C-SMS 702',
        'i' => 'C-ATS 20(O)', 'j' => 'T - TRS 102', 'k' => 'T-ATNM S', 'l' => 'T-ATS 30(O)',
        'm' => 'T-ATS 35', 'n' => 'T-KS 40', 'o' => 'T-TRNMS 402', 'p' => 'T-TRNMS 402G',
        'q' => 'T-TRS 202', 'r' => 'WC0001'
    ];
    
    $data = getProcessedData($pdo, $selectedErps);
    $dailySums = $data['dailySums'];
    $uniqueDays = $data['uniqueDays'];
    
    // Write header
    $header = array_merge(['Component'], $uniqueDays);
    fputcsv($output, $header);
    
    // Write data rows
    foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r'] as $component) {
        $row = [$componentLabels[$component]];
        foreach ($uniqueDays as $day) {
            $sum = isset($dailySums[$day][$component]) ? round($dailySums[$day][$component], 2) : 0.00;
            $row[] = $sum;
        }
        fputcsv($output, $row);
    }
}

function exportDailyBreakdown($pdo, $selectedErps, $output) {
    $data = getProcessedData($pdo, $selectedErps);
    $allDailyData = $data['allDailyData'];
    
    // Write header
    fputcsv($output, ['ID', 'Item Code', 'Day (7AM-7AM)', 'Tires Per Mold']);
    
    // Write data rows
    foreach ($allDailyData as $data) {
        $firstDate = explode(" to ", $data['day_boundary'])[0];
        $firstDate = substr($firstDate, 0, 10);
        fputcsv($output, [
            $data['id'],
            $data['icode'],
            $firstDate,
            round($data['tires_per_mold'], 2)
        ]);
    }
}

function exportSummary($pdo, $selectedErps, $output) {
    $data = getProcessedData($pdo, $selectedErps);
    $idSummary = $data['idSummary'];
    $grandTotalTires = $data['grandTotalTires'];
    
    // Write header
    fputcsv($output, ['ID', 'Total Tires Per Mold']);
    
    // Write data rows
    foreach ($idSummary as $id => $total) {
        fputcsv($output, [$id, $total]);
    }
    
    // Write grand total
    fputcsv($output, ['GRAND TOTAL', round($grandTotalTires, 2)]);
}

function exportAllData($pdo, $selectedErps, $output) {
    // Export comprehensive data with all tables in one sheet
    fputcsv($output, ['=== DAILY BOM SUMS ===']);
    fputcsv($output, []);
    exportDailyBOM($pdo, $selectedErps, $output);
    
    fputcsv($output, []);
    fputcsv($output, ['=== DAILY BREAKDOWN ===']);
    fputcsv($output, []);
    exportDailyBreakdown($pdo, $selectedErps, $output);
    
    fputcsv($output, []);
    fputcsv($output, ['=== SUMMARY ===']);
    fputcsv($output, []);
    exportSummary($pdo, $selectedErps, $output);
}

function getProcessedData($pdo, $selectedErps) {
    // Get all the processed data (reusing logic from original code)
    $placeholders = implode(',', array_fill(0, count($selectedErps), '?'));
    $sql = "SELECT * FROM plannew WHERE erp IN ($placeholders) ORDER BY erp, start_date";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($selectedErps);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group results by ID and collect unique icodes
    $groupedResults = [];
    $icodes = [];
    foreach ($results as $row) {
        $groupedResults[$row['id']][] = $row;
        $icodes[] = $row['icode'];
    }
    $icodes = array_unique($icodes);
    $icodes = array_values($icodes);
    
    // Fetch BOM data
    $bomData = [];
    if (!empty($icodes)) {
        $placeholders = implode(',', array_fill(0, count($icodes), '?'));
        $bomSql = "SELECT icode, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r FROM bom_new WHERE icode IN ($placeholders)";
        $bomStmt = $pdo->prepare($bomSql);
        $bomStmt->execute($icodes);
        while ($row = $bomStmt->fetch(PDO::FETCH_ASSOC)) {
            $bomData[$row['icode']] = $row;
        }
    }
    
    $grandTotalTires = 0;
    $idSummary = [];
    $allDailyData = [];
    $dayIcodes = [];
    
    // Process data
    foreach ($groupedResults as $id => $rows) {
        $idTotalTires = 0;
        
        foreach ($rows as $row) {
            $dailyData = distributeTiresPerDay($row['start_date'], $row['end_date'], $row['tires_per_mold']);
            
            foreach ($dailyData as $day) {
                $dayDate = substr($day['day_start'], 0, 10);
                $allDailyData[] = [
                    'id' => $id,
                    'icode' => $row['icode'],
                    'plan_id' => $row['plan_id'],
                    'day_boundary' => $day['day_start'] . " to " . $day['day_end'],
                    'tires_per_mold' => $day['tires_per_mold'],
                    'day_date' => $dayDate
                ];
                $idTotalTires += $day['tires_per_mold'];
                
                if (!isset($dayIcodes[$dayDate])) {
                    $dayIcodes[$dayDate] = [];
                }
                $dayIcodes[$dayDate][] = $row['icode'];
            }
        }
        
        $idSummary[$id] = round($idTotalTires, 2);
        $grandTotalTires += $idTotalTires;
    }
    
    // Calculate daily sums
    $dailySums = [];
    $uniqueDays = [];
    foreach ($dayIcodes as $dayDate => $icodeList) {
        $icodeList = array_unique($icodeList);
        $sums = array_fill_keys(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r'], 0);
        
        foreach ($icodeList as $icode) {
            if (isset($bomData[$icode])) {
                foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r'] as $col) {
                    $value = $bomData[$icode][$col];
                    $sums[$col] += is_numeric($value) ? floatval($value) : 0;
                }
            }
        }
        
        $dailySums[$dayDate] = $sums;
        $uniqueDays[] = $dayDate;
    }
    sort($uniqueDays);
    
    return [
        'dailySums' => $dailySums,
        'uniqueDays' => $uniqueDays,
        'allDailyData' => $allDailyData,
        'idSummary' => $idSummary,
        'grandTotalTires' => $grandTotalTires
    ];
}

// Fetch distinct ERP numbers for dropdown
$erpSql = "SELECT DISTINCT erp FROM plannew ORDER BY erp";
$erpStmt = $pdo->prepare($erpSql);
$erpStmt->execute();
$erpNumbers = $erpStmt->fetchAll(PDO::FETCH_COLUMN);

// Define labels for columns a through r
$componentLabels = [
    'a' => 'ATPRS',
    'b' => 'B-ATS 15',
    'c' => 'B-BNS 24',
    'd' => 'BG-BLS 12',
    'e' => 'CG - BS 901',
    'f' => 'C - SMS 501',
    'g' => 'C-ATS 20',
    'h' => 'C-SMS 702',
    'i' => 'C-ATS 20(O)',
    'j' => 'T - TRS 102',
    'k' => 'T-ATNM S',
    'l' => 'T-ATS 30(O)',
    'm' => 'T-ATS 35',
    'n' => 'T-KS 40',
    'o' => 'T-TRNMS 402',
    'p' => 'T-TRNMS 402G',
    'q' => 'T-TRS 202',
    'r' => 'WC0001'
];

// Function to distribute tires per mold across days (7AM to 7AM cycle)
function distributeTiresPerDay($startDate, $endDate, $tiresPerMold) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    // Calculate total hours between start and end
    $interval = $start->diff($end);
    $totalHours = ($interval->days * 24) + $interval->h + ($interval->i / 60);
    
    $dailyDistribution = [];
    
    // Find the first 7AM boundary
    $current = clone $start;
    
    // If start time is before 7AM, the day starts from previous day's 7AM
    if ($start->format('H') < 7) {
        $current->setTime(7, 0, 0);
        $current->sub(new DateInterval('P1D'));
    } else {
        // If start time is 7AM or after, the day starts from current day's 7AM
        $current->setTime(7, 0, 0);
    }
    
    while ($current < $end) {
        // Day starts at current 7AM
        $dayStart = clone $current;
        
        // Day ends at next day 7AM
        $dayEnd = clone $current;
        $dayEnd->add(new DateInterval('P1D'));
        
        // Actual start time for this day period
        $actualStart = ($dayStart > $start) ? $dayStart : $start;
        
        // Actual end time for this day period
        $actualEnd = ($dayEnd > $end) ? $end : $dayEnd;
        
        // Calculate hours for this day period
        $dayInterval = $actualStart->diff($actualEnd);
        $dayHours = ($dayInterval->days * 24) + $dayInterval->h + ($dayInterval->i / 60);
        
        // Calculate tires for this day proportionally
        $tiresForDay = ($totalHours > 0) ? ($dayHours / $totalHours) * $tiresPerMold : 0;
        
        $dailyDistribution[] = [
            'date' => $dayStart->format('Y-m-d') . ' (7AM-7AM)',
            'day_start' => $dayStart->format('Y-m-d H:i:s'),
            'day_end' => $dayEnd->format('Y-m-d H:i:s'),
            'actual_start' => $actualStart->format('Y-m-d H:i:s'),
            'actual_end' => $actualEnd->format('Y-m-d H:i:s'),
            'hours' => round($dayHours, 2),
            'tires_per_mold' => round($tiresForDay, 2)
        ];
        
        // Move to next day (next 7AM)
        $current->add(new DateInterval('P1D'));
    }
    
    return $dailyDistribution;
}

// Get ERP numbers from user input (multiple selection)
$selectedErps = [];
if (isset($_GET['erp']) && is_array($_GET['erp'])) {
    foreach ($_GET['erp'] as $erp) {
        if (in_array($erp, $erpNumbers)) {
            $selectedErps[] = $erp;
        }
    }
}
// Default to first ERP if none selected
if (empty($selectedErps) && !empty($erpNumbers)) {
    $selectedErps = [$erpNumbers[0]];
}

// Fetch ref values from worder table for ALL available ERPs (for display purposes)
$erpRefData = [];
if (!empty($erpNumbers)) {
    $placeholders = implode(',', array_fill(0, count($erpNumbers), '?'));
    $refSql = "SELECT DISTINCT erp, ref FROM worder WHERE erp IN ($placeholders) AND ref IS NOT NULL AND ref != ''";
    $refStmt = $pdo->prepare($refSql);
    $refStmt->execute($erpNumbers);
    $refResults = $refStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($refResults as $refRow) {
        if (!isset($erpRefData[$refRow['erp']])) {
            $erpRefData[$refRow['erp']] = [];
        }
        $erpRefData[$refRow['erp']][] = $refRow['ref'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tire Production Excel Export System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            color: #333;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(242, 128, 24, 0.3);
        }

        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo i {
            font-size: 32px;
            color: white;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .card {
            background: white;
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #343a40;
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #F28018;
            padding: 20px;
            border-radius: 13px 13px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .card-body {
            padding: 20px;
        }

        .controls-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        .erp-selection-grid {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #CCCCCC;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: #fafafa;
        }

        .erp-checkbox-item {
            display: block;
            margin: 8px 0;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        .erp-checkbox-item:hover {
            background: rgba(242, 128, 24, 0.1);
        }

        .erp-checkbox-item input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .erp-ref-info {
            color: #666;
            font-size: 0.9em;
            margin-left: 10px;
        }

        .select-all-item {
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }

        .button-primary {
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .button-primary:hover {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(242, 128, 24, 0.4);
        }

        .export-container {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .export-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
        }

        .selected-erps-display {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 10px;
            border: 2px solid #2196F3;
        }

        .selected-erps-title {
            margin: 0 0 15px 0;
            color: #1976d2;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .erp-item {
            margin: 8px 0;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th {
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 15px 12px;
            text-align: center;
            border: 1px solid #d35400;
        }

        .data-table td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: center;
            font-family: 'Open Sans', sans-serif;
            transition: all 0.3s ease;
        }

        .data-table tr:hover {
            background: rgba(242, 128, 24, 0.1);
            transform: scale(1.005);
        }

        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .component-cell {
            font-weight: bold;
            background-color: #f8f9fa !important;
            text-align: left !important;
        }

        .id-cell {
            vertical-align: middle;
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .numeric-value {
            text-align: right;
            font-weight: 600;
            color: #333;
        }

        .tips-section {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 10px;
            border: 2px solid #ffc107;
        }

        .tips-title {
            color: #856404;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tips-list {
            margin: 10px 0;
            padding-left: 20px;
            color: #856404;
        }

        .tips-list li {
            margin: 5px 0;
        }

        .footer-instructions {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #6c757d;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }

        .footer-title {
            color: #495057;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .instruction-list {
            color: #6c757d;
            line-height: 1.6;
        }

        .instruction-list li {
            margin: 10px 0;
        }

        .instruction-list ul {
            margin-top: 10px;
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .container {
                padding: 15px;
            }

            .card-header {
                font-size: 20px;
                padding: 15px;
            }

            .data-table {
                font-size: 12px;
            }

            .data-table th,
            .data-table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-industry"></i>
                <h1>Tire Production Excel Export System</h1>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- 1. Select ERP Numbers Section -->
        <div class="card animate-on-scroll">
            <div class="card-header">
                <i class="fas fa-factory"></i>
                Select ERP Numbers for Analysis
            </div>
            <div class="card-body">
                <form method="GET" id="erpForm">
                    <label for="erp" style="font-size: 16px; font-weight: 600; margin-bottom: 15px; display: block;">
                        <i class="fas fa-list-check"></i> ERP Numbers (Select multiple):
                    </label>
                    <div class="erp-selection-grid">
                        <label class="erp-checkbox-item select-all-item">
                            <input type="checkbox" id="select-all" onclick="toggleAll(this)">
                            <strong><i class="fas fa-check-double"></i> Select All</strong>
                        </label>
                        <?php foreach ($erpNumbers as $erp): ?>
                            <label class="erp-checkbox-item">
                                <input type="checkbox" name="erp[]" value="<?php echo htmlspecialchars($erp); ?>" 
                                       class="erp-checkbox"
                                       <?php echo in_array($erp, $selectedErps) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($erp); ?>
                                <?php if (isset($erpRefData[$erp]) && !empty($erpRefData[$erp])): ?>
                                    <span class="erp-ref-info">
                                        (Ref: <?php echo htmlspecialchars(implode(', ', $erpRefData[$erp])); ?>)
                                    </span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="button-primary">
                        <i class="fas fa-chart-line"></i>
                        Get Distribution
                    </button>
                </form>
                
                <?php if (!empty($selectedErps)): ?>
                    <div class="selected-erps-display">
                        <h4 class="selected-erps-title">
                            <i class="fas fa-clipboard-list"></i>
                            Selected ERPs with References:
                        </h4>
                        <?php foreach ($selectedErps as $erp): ?>
                            <div class="erp-item">
                                <strong><?php echo htmlspecialchars($erp); ?></strong>
                                <?php if (isset($erpRefData[$erp]) && !empty($erpRefData[$erp])): ?>
                                    - Ref: <?php echo htmlspecialchars(implode(', ', $erpRefData[$erp])); ?>
                                <?php else: ?>
                                    - <em>No reference found</em>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Excel Export Section -->
        <?php if (!empty($selectedErps)): ?>
        <div class="export-container animate-on-scroll">
            <h3 style="margin-bottom: 20px; color: #155724; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-file-excel"></i>
                Export to Excel/CSV
            </h3>
            <p style="margin-bottom: 25px; color: #155724; font-size: 16px;">Click the buttons below to export different data sets as CSV files that can be opened in Excel:</p>
            
            <div class="export-section">
                <h4 style="color: #155724; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-download"></i>
                    Available Export Options:
                </h4>
                <?php 
                $queryString = http_build_query(['erp' => $selectedErps]);
                ?>
                
                <a href="?<?php echo $queryString; ?>&export=csv&export_type=daily_bom" class="export-btn">
                    <i class="fas fa-chart-bar"></i>
                    Export Daily BOM Sums
                </a>
                <a href="?<?php echo $queryString; ?>&export=csv&export_type=daily_breakdown" class="export-btn">
                    <i class="fas fa-list"></i>
                    Export Daily Breakdown
                </a>
                <a href="?<?php echo $queryString; ?>&export=csv&export_type=summary" class="export-btn">
                    <i class="fas fa-calculator"></i>
                    Export Summary Table
                </a>
                <a href="?<?php echo $queryString; ?>&export=csv&export_type=all_data" class="export-btn">
                    <i class="fas fa-database"></i>
                    Export All Data
                </a>
                
                <div class="tips-section">
                    <div class="tips-title">
                        <i class="fas fa-lightbulb"></i>
                        Tips for Excel Import:
                    </div>
                    <ul class="tips-list">
                        <li>CSV files will automatically open in Excel</li>
                        <li>Data is pre-formatted for Excel analysis</li>
                        <li>Use "All Data" export for comprehensive analysis</li>
                        <li>Each export includes proper headers and formatting</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php
        // Fetch data for the selected ERPs
        if (empty($selectedErps)) {
            echo "<div class='card animate-on-scroll'>";
            echo "<div class='card-body'>";
            echo "<p style='text-align: center; color: #dc3545; font-size: 18px;'>";
            echo "<i class='fas fa-exclamation-triangle'></i> ";
            echo "No ERPs selected. Please select at least one ERP number above.";
            echo "</p>";
            echo "</div></div>";
        } else {
            $data = getProcessedData($pdo, $selectedErps);
            $dailySums = $data['dailySums'];
            $uniqueDays = $data['uniqueDays'];
            $allDailyData = $data['allDailyData'];
            $idSummary = $data['idSummary'];
            $grandTotalTires = $data['grandTotalTires'];

            if (empty($allDailyData)) {
                echo "<div class='card animate-on-scroll'>";
                echo "<div class='card-body'>";
                echo "<p style='text-align: center; color: #dc3545; font-size: 18px;'>";
                echo "<i class='fas fa-exclamation-triangle'></i> ";
                echo "No data found for selected ERPs: " . implode(', ', $selectedErps);
                echo "</p>";
                echo "</div></div>";
            } else {
                // 3. Display Daily BOM Sums (Vertical) Table
                echo "<div class='table-container animate-on-scroll'>";
                echo "<div class='card-header'>";
                echo "<i class='fas fa-chart-area'></i>";
                echo "Daily BOM Component Sums (Excel Ready Format)";
                echo "</div>";
                echo "<div class='table-wrapper'>";
                echo "<table class='data-table'>";
                echo "<tr>";
                echo "<th style='width: 200px;'><i class='fas fa-cog'></i> Component</th>";
                foreach ($uniqueDays as $day) {
                    echo "<th>" . htmlspecialchars($day) . "</th>";
                }
                echo "</tr>";
                
                $components = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r'];
                foreach ($components as $component) {
                    echo "<tr>";
                    echo "<td class='component-cell'>" . htmlspecialchars($componentLabels[$component]) . "</td>";
                    foreach ($uniqueDays as $day) {
                        $sum = isset($dailySums[$day][$component]) ? round($dailySums[$day][$component], 2) : 0.00;
                        echo "<td class='numeric-value'>" . number_format($sum, 2) . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
                echo "</div>";
                echo "</div>";
                
                // 4. Display consolidated daily breakdown table
                echo "<div class='table-container animate-on-scroll'>";
                echo "<div class='card-header'>";
                echo "<i class='fas fa-list-alt'></i>";
                echo "Daily Production Breakdown";
                echo "</div>";
                echo "<div class='table-wrapper'>";
                echo "<table class='data-table'>";
                echo "<tr>";
                echo "<th><i class='fas fa-id-card'></i> ID</th>";
                echo "<th><i class='fas fa-barcode'></i> Item Code</th>";
                echo "<th><i class='fas fa-calendar-day'></i> Production Day</th>";
                echo "<th><i class='fas fa-tire'></i> Tires Per Mold</th>";
                echo "</tr>";
                
                $currentId = null;
                $idRowCount = [];
                
                // Count rows per ID for rowspan
                foreach ($allDailyData as $dataItem) {
                    if (!isset($idRowCount[$dataItem['id']])) {
                        $idRowCount[$dataItem['id']] = 0;
                    }
                    $idRowCount[$dataItem['id']]++;
                }
                
                $idRowCounters = [];
                foreach ($allDailyData as $dataItem) {
                    if (!isset($idRowCounters[$dataItem['id']])) {
                        $idRowCounters[$dataItem['id']] = 0;
                    }
                    
                    echo "<tr>";
                    
                    // Display ID only for the first row of each ID group
                    if ($idRowCounters[$dataItem['id']] == 0) {
                        echo "<td class='id-cell' rowspan='" . $idRowCount[$dataItem['id']] . "'>" . $dataItem['id'] . "</td>";
                    }
                    
                    echo "<td>" . htmlspecialchars($dataItem['icode']) . "</td>";
                    
                    // Extract the first date from the day_boundary
                    $firstDate = explode(" to ", $dataItem['day_boundary'])[0];
                    $firstDate = substr($firstDate, 0, 10); // Get only the date part (YYYY-MM-DD)
                    echo "<td>" . $firstDate . "</td>";
                    
                    echo "<td class='numeric-value'>" . number_format($dataItem['tires_per_mold'], 2) . "</td>";
                    
                    echo "</tr>";
                    
                    $idRowCounters[$dataItem['id']]++;
                }
                
                echo "</table>";
                echo "</div>";
                echo "</div>";
                
                
                
            
                
                // 6. Additional Excel-Ready Data Table
                echo "<div class='table-container animate-on-scroll'>";
                echo "<div class='card-header'>";
                echo "<i class='fas fa-table'></i>";
                echo "Excel Import Ready - Pivot Table Format";
                echo "</div>";
                echo "<div class='card-body' style='padding: 20px; background: #f8f9fa;'>";
                echo "<p style='color: #666; margin-bottom: 20px; font-size: 16px;'>";
                echo "<i class='fas fa-info-circle'></i> ";
                echo "This table format is optimized for Excel Pivot Tables and data analysis:";
                echo "</p>";
                echo "</div>";
                echo "<div class='table-wrapper'>";
                echo "<table class='data-table'>";
                echo "<tr>";
                echo "<th><i class='fas fa-building'></i> ERP</th>";
                echo "<th><i class='fas fa-id-card'></i> ID</th>";
                echo "<th><i class='fas fa-barcode'></i> Item Code</th>";
                echo "<th><i class='fas fa-calendar'></i> Production Date</th>";
                echo "<th><i class='fas fa-tire'></i> Tires Per Mold</th>";
                echo "<th><i class='fas fa-clock'></i> Day Start</th>";
                echo "<th><i class='fas fa-clock'></i> Day End</th>";
                echo "</tr>";
                
                // Get ERP for each data point
                $placeholders = implode(',', array_fill(0, count($selectedErps), '?'));
                $sqlWithErp = "SELECT *, erp FROM plannew WHERE erp IN ($placeholders) ORDER BY erp, start_date";
                $stmtWithErp = $pdo->prepare($sqlWithErp);
                $stmtWithErp->execute($selectedErps);
                $resultsWithErp = $stmtWithErp->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($resultsWithErp as $row) {
                    $dailyData = distributeTiresPerDay($row['start_date'], $row['end_date'], $row['tires_per_mold']);
                    
                    foreach ($dailyData as $day) {
                        $dayDate = substr($day['day_start'], 0, 10);
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['erp']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['icode']) . "</td>";
                        echo "<td>" . htmlspecialchars($dayDate) . "</td>";
                        echo "<td class='numeric-value'>" . number_format($day['tires_per_mold'], 2) . "</td>";
                        echo "<td style='font-size: 12px;'>" . htmlspecialchars($day['day_start']) . "</td>";
                        echo "<td style='font-size: 12px;'>" . htmlspecialchars($day['day_end']) . "</td>";
                        echo "</tr>";
                    }
                }
                
                echo "</table>";
                echo "</div>";
                echo "</div>";
            }
        }

        // Alternative function for simple day-by-day distribution (7AM to 7AM cycle)
        function distributeTiresEquallyPerDay($startDate, $endDate, $tiresPerMold) {
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            
            // Find the first 7AM boundary
            $current = clone $start;
            if ($start->format('H') < 7) {
                $current->setTime(7, 0, 0);
                $current->sub(new DateInterval('P1D'));
            } else {
                $current->setTime(7, 0, 0);
            }
            
            $totalDays = 0;
            $tempCurrent = clone $current;
            
            // Count total day periods (7AM to 7AM)
            while ($tempCurrent < $end) {
                $totalDays++;
                $tempCurrent->add(new DateInterval('P1D'));
            }
            
            $tiresPerDay = $tiresPerMold / $totalDays;
            
            $dailyDistribution = [];
            $current = clone $start;
            
            if ($start->format('H') < 7) {
                $current->setTime(7, 0, 0);
                $current->sub(new DateInterval('P1D'));
            } else {
                $current->setTime(7, 0, 0);
            }
            
            for ($i = 0; $i < $totalDays; $i++) {
                $dailyDistribution[] = [
                    'date' => $current->format('Y-m-d') . ' (7AM-7AM)',
                    'tires_per_mold' => round($tiresPerDay, 2)
                ];
                $current->add(new DateInterval('P1D'));
            }
            
            return $dailyDistribution;
        }

        $pdo = null; // Close connection
        ?>

        <!-- Footer with Instructions -->
        <div class="footer-instructions animate-on-scroll">
            <h4 class="footer-title">
                <i class="fas fa-file-alt"></i>
                Excel Import Instructions
            </h4>
            <ol class="instruction-list">
                <li><strong>Select your desired ERP numbers</strong> from the dropdown above</li>
                <li><strong>Click "Get Distribution"</strong> to generate the data tables</li>
                <li><strong>Use the Export buttons</strong> to download CSV files:
                    <ul>
                        <li><strong>Daily BOM Sums:</strong> Component requirements by day</li>
                        <li><strong>Daily Breakdown:</strong> Individual production entries</li>
                        <li><strong>Summary Table:</strong> Total production by ID</li>
                        <li><strong>All Data:</strong> Combined comprehensive export</li>
                    </ul>
                </li>
                <li><strong>Open the CSV files in Excel</strong> for further analysis</li>
                <li><strong>Use Excel's Pivot Tables</strong> with the "Pivot Table Format" data for advanced analysis</li>
            </ol>
            
            <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #d1ecf1 0%, #b8daff 100%); border-radius: 10px; border: 2px solid #17a2b8;">
                <div style="color: #0c5460; font-weight: bold; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-search"></i>
                    Data Analysis Tips:
                </div>
                <ul style="margin: 10px 0; color: #0c5460;">
                    <li>Use Excel's <strong>Pivot Tables</strong> to summarize data by different dimensions</li>
                    <li>Apply <strong>Conditional Formatting</strong> to highlight important values</li>
                    <li>Create <strong>Charts and Graphs</strong> from the exported data</li>
                    <li>Use <strong>Filters</strong> to analyze specific date ranges or components</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function toggleAll(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.erp-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }
        
        // Update select all checkbox based on individual selections
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const erpCheckboxes = document.querySelectorAll('.erp-checkbox');
            
            function updateSelectAll() {
                const checkedCount = document.querySelectorAll('.erp-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === erpCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < erpCheckboxes.length;
            }
            
            erpCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectAll);
            });
            
            updateSelectAll(); // Initial update

            // Animate on scroll functionality
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>