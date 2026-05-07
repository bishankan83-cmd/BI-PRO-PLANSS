<?php

require 'vendor/autoload.php';

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$database   = "planatir_task_managemen";

$startDate = $endDate = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['start_date'];
    $endDate   = $_POST['end_date'];

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT dpd.*, td.greenweight AS TireWeight, td.stgreenweight, td.fweight, td.description AS TireDescription
            FROM daily_plan_data dpd
            LEFT JOIN tire_details td ON dpd.Icode = td.icode
            WHERE dpd.Date BETWEEN '$startDate' AND '$endDate'
            ORDER BY dpd.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ─────────────────────────────────────────────
        // SHEET 1 – Daily Production Details
        // ─────────────────────────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daily Production Details');

        $headerFontStyle = ['font' => ['bold' => true]];

        $headers = [
            'A1' => 'Date',
            'B1' => 'Shift',
            'C1' => 'Press',
            'D1' => 'Tire Code',
            'E1' => 'Tire Description',
            'F1' => 'Plan Tires',
            'G1' => 'Actual Tires',
            'H1' => 'Loss Tires',
            'I1' => 'LossReason',
            'J1' => 'Root cause for loss',
            'K1' => 'Per Tire Compound Weight',
            'L1' => 'Per Tire Weight with Steel',
            'M1' => 'Per Tire Finished Weight',
            'N1' => 'Total Plan Compound Weight',
            'O1' => 'Total Plan Steel Weight',
            'P1' => 'Total Plan Finished Weight',
            'Q1' => 'Total Actual Compound Weight',
            'R1' => 'Total Actual Steel Weight',
            'S1' => 'Total Actual Finished Weight',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label)->getStyle($cell)->applyFromArray($headerFontStyle);
        }

        // Build shift data
        $shiftData   = [];
        $monthlyData = []; // [YYYY-MM] => [...]

        while ($row = $result->fetch_assoc()) {
            if ($row["Shift"] === '0') continue;

            $row["Plan"]           = floatval($row["Plan"]);
            $row["AdditionalData"] = floatval($row["AdditionalData"]);
            $row["TireWeight"]     = floatval($row["TireWeight"]);
            $row["stgreenweight"]  = floatval($row["stgreenweight"]);
            $row["fweight"]        = floatval($row["fweight"]);

            $overrideReasons = [
                "Not Matching The Unloading time",
                "Over Production",
                "Planning Stop",
                "Black Tire Prodution",
            ];
            if (in_array($row["LossReason"], $overrideReasons)) {
                $row["Plan"] = $row["AdditionalData"];
            }

            $shiftName = $row["Shift"];
            if (!isset($shiftData[$shiftName])) $shiftData[$shiftName] = [];
            $shiftData[$shiftName][] = $row;

            // Accumulate monthly data
            $monthKey = date('Y-m', strtotime($row["Date"]));
            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'actualTires'           => 0,
                    'actualGreenWeight'     => 0,
                    'actualSteelWeight'     => 0,
                    'actualFinishedWeight'  => 0,
                ];
            }
            $monthlyData[$monthKey]['actualTires']          += $row["AdditionalData"];
            $monthlyData[$monthKey]['actualGreenWeight']    += $row["AdditionalData"] * $row["TireWeight"];
            $monthlyData[$monthKey]['actualSteelWeight']    += $row["AdditionalData"] * $row["stgreenweight"];
            $monthlyData[$monthKey]['actualFinishedWeight'] += $row["AdditionalData"] * $row["fweight"];
        }

        // Write daily rows + shift totals
        $rowIndex = 2;
        foreach ($shiftData as $shift => $data) {
            foreach ($data as $row) {
                $lossTires = max(0, $row["Plan"] - $row["AdditionalData"]);
                $sheet->setCellValue('A' . $rowIndex, $row["Date"]);
                $sheet->setCellValue('B' . $rowIndex, $row["Shift"]);
                $sheet->setCellValue('C' . $rowIndex, $row["CavityName"]);
                $sheet->setCellValue('D' . $rowIndex, $row["Icode"]);
                $sheet->setCellValue('E' . $rowIndex, $row["TireDescription"]);
                $sheet->setCellValue('F' . $rowIndex, $row["Plan"]);
                $sheet->setCellValue('G' . $rowIndex, $row["AdditionalData"]);
                $sheet->setCellValue('H' . $rowIndex, $lossTires);
                $sheet->setCellValue('I' . $rowIndex, $row["LossReason"]);
                $sheet->setCellValue('J' . $rowIndex, $row["Remark"]);
                $sheet->setCellValue('K' . $rowIndex, $row["TireWeight"]);
                $sheet->setCellValue('L' . $rowIndex, $row["stgreenweight"]);
                $sheet->setCellValue('M' . $rowIndex, $row["fweight"]);
                $sheet->setCellValue('N' . $rowIndex, $row["Plan"] * $row["TireWeight"]);
                $sheet->setCellValue('O' . $rowIndex, $row["Plan"] * $row["stgreenweight"]);
                $sheet->setCellValue('P' . $rowIndex, $row["Plan"] * $row["fweight"]);
                $sheet->setCellValue('Q' . $rowIndex, $row["AdditionalData"] * $row["TireWeight"]);
                $sheet->setCellValue('R' . $rowIndex, $row["AdditionalData"] * $row["stgreenweight"]);
                $sheet->setCellValue('S' . $rowIndex, $row["AdditionalData"] * $row["fweight"]);
                $rowIndex++;
            }

            // Shift total row
            $shiftRowIndex = $rowIndex + 1;
            $boldFont = ['font' => ['bold' => true]];

            $sheet->setCellValue('B' . $shiftRowIndex, $shift . ' Total')->getStyle('B' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('F' . $shiftRowIndex, array_sum(array_column($data, "Plan")))->getStyle('F' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('G' . $shiftRowIndex, array_sum(array_column($data, "AdditionalData")))->getStyle('G' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('H' . $shiftRowIndex, array_sum(array_map(fn($r) => max(0, $r["Plan"] - $r["AdditionalData"]), $data)))->getStyle('H' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('N' . $shiftRowIndex, array_sum(array_map(fn($r) => $r["Plan"] * $r["TireWeight"], $data)))->getStyle('N' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('O' . $shiftRowIndex, array_sum(array_map(fn($r) => $r["Plan"] * $r["stgreenweight"], $data)))->getStyle('O' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('P' . $shiftRowIndex, array_sum(array_map(fn($r) => $r["Plan"] * $r["fweight"], $data)))->getStyle('P' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('Q' . $shiftRowIndex, array_sum(array_map(fn($r) => $r["AdditionalData"] * $r["TireWeight"], $data)))->getStyle('Q' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('R' . $shiftRowIndex, array_sum(array_map(fn($r) => $r["AdditionalData"] * $r["stgreenweight"], $data)))->getStyle('R' . $shiftRowIndex)->applyFromArray($boldFont);
            $sheet->setCellValue('S' . $shiftRowIndex, array_sum(array_map(fn($r) => $r["AdditionalData"] * $r["fweight"], $data)))->getStyle('S' . $shiftRowIndex)->applyFromArray($boldFont);

            $rowIndex = $shiftRowIndex + 1;
        }

        // Grand Total
        $grandRow = $rowIndex + 1;
        $boldFont = ['font' => ['bold' => true]];
        $totalPlanTires = $totalActualTires = $totalLossTires = 0;
        $totalPlanCW = $totalPlanSW = $totalPlanFW = 0;
        $totalActualCW = $totalActualSW = $totalActualFW = 0;

        foreach ($shiftData as $data) {
            $totalPlanTires   += array_sum(array_column($data, "Plan"));
            $totalActualTires += array_sum(array_column($data, "AdditionalData"));
            $totalLossTires   += array_sum(array_map(fn($r) => max(0, $r["Plan"] - $r["AdditionalData"]), $data));
            $totalPlanCW      += array_sum(array_map(fn($r) => $r["Plan"] * $r["TireWeight"], $data));
            $totalPlanSW      += array_sum(array_map(fn($r) => $r["Plan"] * $r["stgreenweight"], $data));
            $totalPlanFW      += array_sum(array_map(fn($r) => $r["Plan"] * $r["fweight"], $data));
            $totalActualCW    += array_sum(array_map(fn($r) => $r["AdditionalData"] * $r["TireWeight"], $data));
            $totalActualSW    += array_sum(array_map(fn($r) => $r["AdditionalData"] * $r["stgreenweight"], $data));
            $totalActualFW    += array_sum(array_map(fn($r) => $r["AdditionalData"] * $r["fweight"], $data));
        }

        $sheet->setCellValue('B' . $grandRow, 'Grand Total')->getStyle('B' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('F' . $grandRow, $totalPlanTires)->getStyle('F' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('G' . $grandRow, $totalActualTires)->getStyle('G' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('H' . $grandRow, $totalLossTires)->getStyle('H' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('N' . $grandRow, $totalPlanCW)->getStyle('N' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('O' . $grandRow, $totalPlanSW)->getStyle('O' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('P' . $grandRow, $totalPlanFW)->getStyle('P' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('Q' . $grandRow, $totalActualCW)->getStyle('Q' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('R' . $grandRow, $totalActualSW)->getStyle('R' . $grandRow)->applyFromArray($boldFont);
        $sheet->setCellValue('S' . $grandRow, $totalActualFW)->getStyle('S' . $grandRow)->applyFromArray($boldFont);

        // ─────────────────────────────────────────────
        // SHEET 2 – Monthly Summary
        // ─────────────────────────────────────────────
        $monthSheet = $spreadsheet->createSheet();
        $monthSheet->setTitle('Monthly Summary');

        // Headers
        $monthHeaders = [
            'A1' => 'Year',
            'B1' => 'Month',
            'C1' => 'Actual Tires',
            'D1' => 'Total Actual Green Weight',
            'E1' => 'Total Actual Steel Weight',
            'F1' => 'Total Actual Finished Weight',
        ];
        foreach ($monthHeaders as $cell => $label) {
            $monthSheet->setCellValue($cell, $label);
        }

        // Style header row
        $headerFill = [
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F28018'],
            ],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        ];
        $monthSheet->getStyle('A1:F1')->applyFromArray($headerFill);

        // Sort monthly data by key (YYYY-MM)
        ksort($monthlyData);

        $mRow = 2;
        foreach ($monthlyData as $monthKey => $values) {
            list($year, $month) = explode('-', $monthKey);
            $monthName = date('F', mktime(0, 0, 0, (int)$month, 1));

            $monthSheet->setCellValue('A' . $mRow, (int)$year);
            $monthSheet->setCellValue('B' . $mRow, $monthName);
            $monthSheet->setCellValue('C' . $mRow, $values['actualTires']);
            $monthSheet->setCellValue('D' . $mRow, round($values['actualGreenWeight'], 4));
            $monthSheet->setCellValue('E' . $mRow, round($values['actualSteelWeight'], 4));
            $monthSheet->setCellValue('F' . $mRow, round($values['actualFinishedWeight'], 4));
            $mRow++;
        }

        // Grand Total row for monthly sheet
        $boldFont = ['font' => ['bold' => true]];
        $monthSheet->setCellValue('A' . $mRow, 'Grand Total')->getStyle('A' . $mRow)->applyFromArray($boldFont);
        $monthSheet->setCellValue('C' . $mRow, '=SUM(C2:C' . ($mRow - 1) . ')')->getStyle('C' . $mRow)->applyFromArray($boldFont);
        $monthSheet->setCellValue('D' . $mRow, '=SUM(D2:D' . ($mRow - 1) . ')')->getStyle('D' . $mRow)->applyFromArray($boldFont);
        $monthSheet->setCellValue('E' . $mRow, '=SUM(E2:E' . ($mRow - 1) . ')')->getStyle('E' . $mRow)->applyFromArray($boldFont);
        $monthSheet->setCellValue('F' . $mRow, '=SUM(F2:F' . ($mRow - 1) . ')')->getStyle('F' . $mRow)->applyFromArray($boldFont);

        // Auto-width for monthly sheet columns
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
            $monthSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Auto-width for daily sheet columns
        foreach (range('A', 'S') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ─────────────────────────────────────────────
        // Save & Output
        // ─────────────────────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);

        $writer   = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Daily_Production_Details_'
                  . date('Y-m-d', strtotime($startDate))
                  . '_to_'
                  . date('Y-m-d', strtotime($endDate))
                  . '.xlsx';

        $writer->save($filename);

        echo '
        <style>
            .download-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #FF0000;
                color: #FFFFFF;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                transition: background-color 0.3s ease;
            }
            .download-button:hover { background-color: #CC0000; }
        </style>
        <p>Successful! <a href="' . $filename . '" class="download-button">Download Excel</a></p>';

    } else {
        echo "0 results";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }
        h5 {
            font-weight: bold;
            font-size: 24px;
            font-family: 'Cantarell Bold', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }
        form { text-align: left; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; }
        input[type="date"] {
            width: 91%;
            padding: 10px;
            border: 1px solid #000000;
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
        }
        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h5>Export Excel Production Date Range</h5>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Export Excel">
            </div>
        </form>
    </div>
</body>
</html>