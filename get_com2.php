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

// Get ERP number from user input
$erpNumber = isset($_GET['erp']) ? $_GET['erp'] : '10'; // Default to '10' for testing

// Fetch data for the specified ERP
$sql = "SELECT * FROM plannew WHERE erp = :erp ORDER BY start_date";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':erp', $erpNumber);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo "<p>No data found for ERP: $erpNumber</p>";
} else {
    echo "<h2>Daily Tire Production Distribution for ERP: $erpNumber</h2>";
    
    // Group results by ID and collect unique icodes
    $groupedResults = [];
    $icodes = [];
    foreach ($results as $row) {
        $groupedResults[$row['id']][] = $row;
        $icodes[] = $row['icode'];
    }
    $icodes = array_unique($icodes);
    
    // Fetch BOM data for all icodes
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
    
    // Collect all daily distribution data
    foreach ($groupedResults as $id => $rows) {
        $idTotalTires = 0;
        
        foreach ($rows as $row) {
            // Get daily distribution
            $dailyData = distributeTiresPerDay($row['start_date'], $row['end_date'], $row['tires_per_mold']);
            
            foreach ($dailyData as $day) {
                $allDailyData[] = [
                    'id' => $id,
                    'icode' => $row['icode'],
                    'plan_id' => $row['plan_id'],
                    'day_boundary' => $day['day_start'] . " to " . $day['day_end'],
                    'tires_per_mold' => $day['tires_per_mold']
                ];
                $idTotalTires += $day['tires_per_mold'];
            }
        }
        
        $idSummary[$id] = round($idTotalTires, 2);
        $grandTotalTires += $idTotalTires;
    }
    
    // Display consolidated daily breakdown table with BOM columns
    echo "<div style='margin: 20px 0;'>";
    echo "<h3>Daily Breakdown Table</h3>";
    echo "<table style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background-color: #007acc; color: white;'>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>ID</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>Item Code</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>Day (7AM to 7AM)</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>Tires Per Mold</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>a</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>b</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>c</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>d</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>e</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>f</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>g</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>h</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>i</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>j</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>k</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>l</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>m</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>n</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>o</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>p</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>q</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>r</th>";
    echo "</tr>";
    
    $currentId = null;
    $idRowCount = [];
    
    // Count rows per ID for rowspan
    foreach ($allDailyData as $data) {
        if (!isset($idRowCount[$data['id']])) {
            $idRowCount[$data['id']] = 0;
        }
        $idRowCount[$data['id']]++;
    }
    
    $idRowCounters = [];
    foreach ($allDailyData as $data) {
        if (!isset($idRowCounters[$data['id']])) {
            $idRowCounters[$data['id']] = 0;
        }
        
        echo "<tr>";
        
        // Display ID only for the first row of each ID group
        if ($idRowCounters[$data['id']] == 0) {
            echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center; vertical-align: middle; font-weight: bold; background-color: #f8f9fa;' rowspan='" . $idRowCount[$data['id']] . "'>" . $data['id'] . "</td>";
        }
        
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . htmlspecialchars($data['icode']) . "</td>";
        
        // Extract the first date from the day_boundary
        $firstDate = explode(" to ", $data['day_boundary'])[0];
        $firstDate = substr($firstDate, 0, 10); // Get only the date part (YYYY-MM-DD)
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . $firstDate . "</td>";
        
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;'>" . round($data['tires_per_mold'], 2) . "</td>";
        
        // Display BOM data for the icode
        $icode = $data['icode'];
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['a']) ? htmlspecialchars($bomData[$icode]['a']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['b']) ? htmlspecialchars($bomData[$icode]['b']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['c']) ? htmlspecialchars($bomData[$icode]['c']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['d']) ? htmlspecialchars($bomData[$icode]['d']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['e']) ? htmlspecialchars($bomData[$icode]['e']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['f']) ? htmlspecialchars($bomData[$icode]['f']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['g']) ? htmlspecialchars($bomData[$icode]['g']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['h']) ? htmlspecialchars($bomData[$icode]['h']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['i']) ? htmlspecialchars($bomData[$icode]['i']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['j']) ? htmlspecialchars($bomData[$icode]['j']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['k']) ? htmlspecialchars($bomData[$icode]['k']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['l']) ? htmlspecialchars($bomData[$icode]['l']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['m']) ? htmlspecialchars($bomData[$icode]['m']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['n']) ? htmlspecialchars($bomData[$icode]['n']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['o']) ? htmlspecialchars($bomData[$icode]['o']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['p']) ? htmlspecialchars($bomData[$icode]['p']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['q']) ? htmlspecialchars($bomData[$icode]['q']) : 'N/A') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>" . (isset($bomData[$icode]['r']) ? htmlspecialchars($bomData[$icode]['r']) : 'N/A') . "</td>";
        
        echo "</tr>";
        
        $idRowCounters[$data['id']]++;
    }
    
    echo "</table>";
    echo "</div>";
    
    // Display Summary Table
    echo "<div style='border: 3px solid #28a745; margin: 20px 0; padding: 20px; border-radius: 10px; background-color: #d4edda;'>";
    echo "<h3 style='color: #155724; margin-top: 0; text-align: center;'>Summary Table for ERP: $erpNumber</h3>";
    
    echo "<table style='border-collapse: collapse; width: 100%; margin-bottom: 15px;'>";
    echo "<tr style='background-color: #28a745; color: white;'>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: center;'>ID</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: center;'>Total Tires Per Mold</th>";
    echo "</tr>";
    
    foreach ($idSummary as $id => $total) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;'>$id</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px; text-align: center; font-weight: bold;'>$total</td>";
        echo "</tr>";
    }
    
    echo "<tr style='background-color: #155724; color: white; font-size: 18px;'>";
    echo "<td style='border: 1px solid #ddd; padding: 15px; text-align: center; font-weight: bold;'>GRAND TOTAL</td>";
    echo "<td style='border: 1px solid #ddd; padding: 15px; text-align: center; font-weight: bold;'>" . round($grandTotalTires, 2) . "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tire Production Daily Distribution</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-container { background-color: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .plan-container { border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>Enter ERP Number</h3>
        <form method="GET">
            <label for="erp">ERP Number:</label>
            <input type="text" id="erp" name="erp" value="<?php echo htmlspecialchars($erpNumber); ?>" required>
            <button type="submit">Get Distribution</button>
        </form>
    </div>
</body>
</html>

<?php
$pdo = null; // Close connection
?>