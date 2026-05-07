
<!DOCTYPE html>
<html>
<head>
    <title>Month-wise Totals</title>
</head>
<body>
    <form method="post" action="">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <input type="submit" value="Submit">
    </form>



<?php

require 'vendor/autoload.php'; // Include Composer's autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // MySQLi connection details
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $database = "planatir_task_managemen";

    // Get start and end dates from the form
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(dp.Date, '%Y-%m') AS month,
               SUM(dp.AdditionalData) AS plan,
               SUM(dp.AdditionalData * bn.a) AS a_plan,
               SUM(dp.AdditionalData * bn.b) AS b_plan,
               SUM(dp.AdditionalData * bn.c) AS c_plan,
               SUM(dp.AdditionalData * bn.d) AS d_plan,
               SUM(dp.AdditionalData * bn.e) AS e_plan,
               SUM(dp.AdditionalData * bn.f) AS f_plan,
               SUM(dp.AdditionalData * bn.g) AS g_plan,
               SUM(dp.AdditionalData * bn.h) AS h_plan,
               SUM(dp.AdditionalData * bn.i) AS i_plan,
               SUM(dp.AdditionalData * bn.j) AS j_plan,
               SUM(dp.AdditionalData * bn.k) AS k_plan,
               SUM(dp.AdditionalData * bn.l) AS l_plan,
               SUM(dp.AdditionalData * bn.m) AS m_plan,
               SUM(dp.AdditionalData * bn.n) AS n_plan,
               SUM(dp.AdditionalData * bn.o) AS o_plan,
               SUM(dp.AdditionalData * bn.p) AS p_plan,
               SUM(dp.AdditionalData * bn.q) AS q_plan,
               SUM(dp.AdditionalData * bn.r) AS r_plan
        FROM daily_plan_data dp
        INNER JOIN bom_new bn ON dp.Icode = bn.icode
        WHERE dp.Date BETWEEN ? AND ?
        GROUP BY month
        ORDER BY month
    ");

    $stmt->bind_param("ss", $start_date, $end_date);

    // Execute query
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set table header with updated names
    $headers = [
        'Month', 'Plan', 'ATPRS', 'B-ATS 15', 'B-BNS 24', 'BG-BLS 12', 'CG -BS 901',
        'C-SMS 501', 'C-ATS 20', 'C-SMS 702', 'C-ATNMS 20', 'T-TRS 102', 'T-ATNM S',
        'T-ATS 30', 'T-ATS 35', 'T-KS 40', 'T-TRNMS 402', 'T-TRNMS 402G', 'T-TRS 202', 'WC0001'
    ];
    
    foreach ($headers as $col => $header) {
        $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
    }

    // Add data to spreadsheet
    $rowNum = 2;
    $totals = array_fill(1, count($headers) - 1, 0);
    $rowCount = 0;

    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNum, $row["month"]);
        $sheet->setCellValue('B' . $rowNum, $row["plan"]);
        $sheet->setCellValue('C' . $rowNum, $row["a_plan"]);
        $sheet->setCellValue('D' . $rowNum, $row["b_plan"]);
        $sheet->setCellValue('E' . $rowNum, $row["c_plan"]);
        $sheet->setCellValue('F' . $rowNum, $row["d_plan"]);
        $sheet->setCellValue('G' . $rowNum, $row["e_plan"]);
        $sheet->setCellValue('H' . $rowNum, $row["f_plan"]);
        $sheet->setCellValue('I' . $rowNum, $row["g_plan"]);
        $sheet->setCellValue('J' . $rowNum, $row["h_plan"]);
        $sheet->setCellValue('K' . $rowNum, $row["i_plan"]);
        $sheet->setCellValue('L' . $rowNum, $row["j_plan"]);
        $sheet->setCellValue('M' . $rowNum, $row["k_plan"]);
        $sheet->setCellValue('N' . $rowNum, $row["l_plan"]);
        $sheet->setCellValue('O' . $rowNum, $row["m_plan"]);
        $sheet->setCellValue('P' . $rowNum, $row["n_plan"]);
        $sheet->setCellValue('Q' . $rowNum, $row["o_plan"]);
        $sheet->setCellValue('R' . $rowNum, $row["p_plan"]);
        $sheet->setCellValue('S' . $rowNum, $row["q_plan"]);
        $sheet->setCellValue('T' . $rowNum, $row["r_plan"]);

        // Update totals
        $totals[1] += $row["plan"];
        $totals[2] += $row["a_plan"];
        $totals[3] += $row["b_plan"];
        $totals[4] += $row["c_plan"];
        $totals[5] += $row["d_plan"];
        $totals[6] += $row["e_plan"];
        $totals[7] += $row["f_plan"];
        $totals[8] += $row["g_plan"];
        $totals[9] += $row["h_plan"];
        $totals[10] += $row["i_plan"];
        $totals[11] += $row["j_plan"];
        $totals[12] += $row["k_plan"];
        $totals[13] += $row["l_plan"];
        $totals[14] += $row["m_plan"];
        $totals[15] += $row["n_plan"];
        $totals[16] += $row["o_plan"];
        $totals[17] += $row["p_plan"];
        $totals[18] += $row["q_plan"];
        $totals[19] += $row["r_plan"];

        $rowNum++;
        $rowCount++;
    }

    // Calculate averages
    $averages = array_map(function($total) use ($rowCount) {
        return $rowCount > 0 ? $total / $rowCount : 0;
    }, $totals);

    // Set totals and averages in the spreadsheet
    $sheet->setCellValue('A' . $rowNum, 'Total');
    foreach ($totals as $col => $total) {
        $sheet->setCellValueByColumnAndRow($col + 1, $rowNum, $total);
    }

    // Apply style to Total row
    $totalRow = $rowNum;
    $sheet->getStyle("A$totalRow:T$totalRow")->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['argb' => 'FFFF0000'] // Red fill color
        ],
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FFFFFFFF'] // White font color
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THICK,
                'color' => ['argb' => 'FF000000'], // Black border color
            ]
        ]
    ]);

    $rowNum++;
    $sheet->setCellValue('A' . $rowNum, 'Average');
    foreach ($averages as $col => $average) {
        $sheet->setCellValueByColumnAndRow($col + 1, $rowNum, $average);
    }

    // Apply style to Average row
    $averageRow = $rowNum;
    $sheet->getStyle("A$averageRow:T$averageRow")->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['argb' => 'FF00FF00'] // Green fill color
        ],
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FF000000'] // White font color
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THICK,
                'color' => ['argb' => 'FF000000'], // Black border color
            ]
        ]
    ]);

    // Save the spreadsheet
    $fileName = 'Monthwise_Totals.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($fileName);

    // Provide download link
    echo "<p><a href='$fileName' download>Click here to download the Excel file</a></p>";

    // Close connection
    $conn->close();
}
?>


