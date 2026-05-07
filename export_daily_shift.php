<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet autoload file

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Initialize variables
$startDate = $endDate = $selectedShift = "";
$message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $selectedShift = $_POST['shift_filter'];

    // Create database connection
    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Build SQL query
    $sql = "SELECT dpd.*, td.greenweight AS TireWeight, td.stgreenweight 
            FROM daily_plan_data dpd
            LEFT JOIN tire_details td ON dpd.Icode = td.icode
            WHERE dpd.Date BETWEEN '$startDate' AND '$endDate'
            AND dpd.Shift != '0'";
    
    // Add shift filter if specific shift selected
    if ($selectedShift !== "all" && $selectedShift !== "") {
        // Map the selected shift to both DAY and NIGHT shifts
        if ($selectedShift === "A") {
            $sql .= " AND (dpd.Shift = 'DAY A' OR dpd.Shift = 'NIGHT A')";
        } elseif ($selectedShift === "B") {
            $sql .= " AND (dpd.Shift = 'DAY B' OR dpd.Shift = 'NIGHT B')";
        } elseif ($selectedShift === "C") {
            $sql .= " AND (dpd.Shift = 'DAY C' OR dpd.Shift = 'NIGHT C')";
        }
    }
    
    $sql .= " ORDER BY dpd.Shift, dpd.Date";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Initialize shift totals array
        $shiftTotals = array();

        // Process data and calculate shift totals
        while ($row = $result->fetch_assoc()) {
            $row["Plan"] = floatval($row["Plan"]);
            $row["AdditionalData"] = floatval($row["AdditionalData"]);
            $row["TireWeight"] = floatval($row["TireWeight"]);
            $row["stgreenweight"] = floatval($row["stgreenweight"]);
            
            // Handle special loss reasons
            if (in_array($row["LossReason"], [
                "Not Matching The Unloading time",
                "Over Production",
                "Planning Stop",
                "Black Tire Prodution"
            ])) {
                $row["Plan"] = $row["AdditionalData"];
            }
            
            // Normalize shift names (combine DAY and NIGHT shifts)
            $shiftName = $row["Shift"];
            if (in_array($shiftName, ['DAY A', 'NIGHT A'])) {
                $shiftName = 'A';
            } elseif (in_array($shiftName, ['DAY B', 'NIGHT B'])) {
                $shiftName = 'B';
            } elseif (in_array($shiftName, ['DAY C', 'NIGHT C'])) {
                $shiftName = 'C';
            }
            
            // Initialize shift totals if not exists
            if (!isset($shiftTotals[$shiftName])) {
                $shiftTotals[$shiftName] = array(
                    'actualTires' => 0,
                    'actualCompoundWeight' => 0,
                    'actualSteelWeight' => 0
                );
            }
            
            // Calculate totals
            $shiftTotals[$shiftName]['actualTires'] += $row["AdditionalData"];
            $shiftTotals[$shiftName]['actualCompoundWeight'] += ($row["AdditionalData"] * $row["TireWeight"]);
            $shiftTotals[$shiftName]['actualSteelWeight'] += ($row["AdditionalData"] * $row["stgreenweight"]);
        }

        // Create Excel file
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'A1' => 'Shift',
            'B1' => 'Actual Tires',
           
            'C1' => 'Actual Compound Weight',
             'D1' => 'Actual Steel Weight'

            
        ];

        // Apply headers with styling
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF000000']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF0F0F0']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC']
                ]
            ]
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // Add shift totals data
        $row = 2;
        $grandTotals = array(
            'actualTires' => 0,
            'actualCompoundWeight' => 0,
            'actualSteelWeight' => 0
        );

        foreach ($shiftTotals as $shift => $totals) {
            $sheet->setCellValue('A' . $row, 'Shift ' . $shift);
            $sheet->setCellValue('B' . $row, number_format($totals['actualTires'], 2));
            $sheet->setCellValue('C' . $row, number_format($totals['actualCompoundWeight'], 2));
            $sheet->setCellValue('D' . $row, number_format($totals['actualSteelWeight'], 2));
            
            // Add to grand totals
            foreach ($grandTotals as $key => $value) {
                $grandTotals[$key] += $totals[$key];
            }
            
            $row++;
        }

        // Add grand totals row
        $grandTotalStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF000000']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF28018']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC']
                ]
            ]
        ];

        $sheet->setCellValue('A' . $row, 'GRAND TOTAL');
        $sheet->setCellValue('B' . $row, number_format($grandTotals['actualTires'], 2));
        $sheet->setCellValue('C' . $row, number_format($grandTotals['actualCompoundWeight'], 2));
        $sheet->setCellValue('D' . $row, number_format($grandTotals['actualSteelWeight'], 2));

        // Apply grand total styling
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($grandTotalStyle);

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate filename
        $shiftSuffix = ($selectedShift === "all" || $selectedShift === "") ? "AllShifts" : "Shift_" . $selectedShift;
        $filename = 'Shift_Totals_' . date('Y-m-d', strtotime($startDate)) . '_to_' . date('Y-m-d', strtotime($endDate)) . '_' . $shiftSuffix . '.xlsx';

        // Save the file
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);

        $message = '<div class="success-message">
            <p>✅ Excel file generated successfully!</p>
            <a href="' . $filename . '" class="download-btn">📥 Download Excel File</a>
        </div>';

        // Display shift totals summary
        $message .= '<div class="summary-table">
            <h3>Shift Totals Summary (' . $startDate . ' to ' . $endDate . ')</h3>
            <table>
                <tr>
                    <th>Shift</th>
                    <th>Actual Tires</th>
                     
                    <th>Actual Compound Weight</th>
                    <th>Actual Steel Weight</th>
                   
                </tr>';

        foreach ($shiftTotals as $shift => $totals) {
            $message .= '<tr>
                <td><strong>Shift ' . htmlspecialchars($shift) . '</strong></td>
                <td>' . number_format($totals['actualTires'], 2) . '</td>
                <td>' . number_format($totals['actualCompoundWeight'], 2) . '</td>
                <td>' . number_format($totals['actualSteelWeight'], 2) . '</td>
            </tr>';
        }

        $message .= '<tr class="grand-total">
            <td><strong>GRAND TOTAL</strong></td>
            <td><strong>' . number_format($grandTotals['actualTires'], 2) . '</strong></td>
            <td><strong>' . number_format($grandTotals['actualCompoundWeight'], 2) . '</strong></td>
            <td><strong>' . number_format($grandTotals['actualSteelWeight'], 2) . '</strong></td>
        </tr></table></div>';

    } else {
        $message = '<div class="error-message">❌ No data found for the selected criteria.</div>';
    }

    $conn->close();
}

// Function to get available shifts
function getAvailableShifts($conn) {
    $shiftQuery = "SELECT DISTINCT Shift FROM daily_plan_data WHERE Shift != '0' ORDER BY Shift";
    $shiftResult = $conn->query($shiftQuery);
    $shifts = array();
    
    if ($shiftResult->num_rows > 0) {
        while ($row = $shiftResult->fetch_assoc()) {
            $shiftName = $row['Shift'];
            // Normalize shift names for dropdown
            if (in_array($shiftName, ['DAY A', 'NIGHT A'])) {
                $shifts[] = 'A';
            } elseif (in_array($shiftName, ['DAY B', 'NIGHT B'])) {
                $shifts[] = 'B';
            } elseif (in_array($shiftName, ['DAY C', 'NIGHT C'])) {
                $shifts[] = 'C';
            }
        }
    }
    
    // Remove duplicates and sort
    $shifts = array_unique($shifts);
    sort($shifts);
    
    return $shifts;
}

// Get available shifts for dropdown
$conn = new mysqli($servername, $username, $password, $database);
$availableShifts = array();
if (!$conn->connect_error) {
    $availableShifts = getAvailableShifts($conn);
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Shift Totals Excel Export</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background-color: #f0f0f0;
            color: #333333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            color: #333333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .info-text {
            color: #333333;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333333;
        }

        input[type="date"], select {
            width: 100%;
            padding: 12px;
            border: 2px solid #CCCCCC;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
            background-color: #FFFFFF;
            color: #333333;
        }

        input[type="date"]:focus, select:focus {
            outline: none;
            border-color: #F28018;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: #F28018;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .submit-btn:hover {
            background: #d96f15;
            transform: translateY(-2px);
        }

        .download-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #F28018;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .download-btn:hover {
            background: #d96f15;
        }

        .success-message {
            background: #f0f0f0;
            color: #333333;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #CCCCCC;
        }

        .error-message {
            background: #f0f0f0;
            color: #333333;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #CCCCCC;
        }

        .summary-table {
            margin-top: 30px;
        }

        .summary-table h3 {
            color: #333333;
            margin-bottom: 15px;
        }

        .summary-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .summary-table th, .summary-table td {
            border: 1px solid #CCCCCC;
            padding: 12px;
            text-align: center;
            color: #333333;
        }

        .summary-table th {
            background: #F28018;
            color: #FFFFFF;
            font-weight: bold;
        }

        .summary-table .grand-total {
            background: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Shift Totals Excel Export</h1>
        
        <div class="info-text">
            Generate Excel reports with shift totals for production data. Select date range and shift to export customized reports.
        </div>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="start_date">📅 Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date">📅 End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>" required>
            </div>

            <div class="form-group">
                <label for="shift_filter">⚙️ Shift Filter:</label>
                <select name="shift_filter" id="shift_filter">
                    <option value="all" <?php echo ($selectedShift === "all" || $selectedShift === "") ? 'selected' : ''; ?>>All Shifts</option>
                    <?php foreach ($availableShifts as $shift): ?>
                        <option value="<?php echo htmlspecialchars($shift); ?>" <?php echo ($selectedShift === $shift) ? 'selected' : ''; ?>>
                            Shift <?php echo htmlspecialchars($shift); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="submit-btn">📋 Generate Excel Report</button>
        </form>

        <?php echo $message; ?>
    </div>
</body>
</html>