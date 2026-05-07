<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

// Database configuration
$config = [
    'servername' => 'localhost',
    'username' => 'planatir_task_managemen',
    'password' => 'Bishan@1919',
    'dbname' => 'planatir_task_managemen'
];

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-d');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    // Create database connection
    $pdo = new PDO(
        "mysql:host={$config['servername']};dbname={$config['dbname']}", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch data
    $sql = "
        SELECT 
            date,
            SUM(plan) as total_plan,
            SUM(a_plan) as a_plan,
            SUM(b_plan) as b_plan,
            SUM(c_plan) as c_plan,
            SUM(d_plan) as d_plan,
            SUM(e_plan) as e_plan,
            SUM(f_plan) as f_plan,
            SUM(g_plan) as g_plan,
            SUM(h_plan) as h_plan,
            SUM(i_plan) as i_plan,
            SUM(j_plan) as j_plan,
            SUM(k_plan) as k_plan,
            SUM(l_plan) as l_plan,
            SUM(m_plan) as m_plan,
            SUM(n_plan) as n_plan,
            SUM(o_plan) as o_plan,
            SUM(p_plan) as p_plan,
            SUM(q_plan) as q_plan,
            SUM(r_plan) as r_plan
        FROM stored_data
        WHERE date BETWEEN :start_date AND :end_date
        GROUP BY date
        ORDER BY date
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create new Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set page orientation and size
    $sheet->getPageSetup()
          ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
          ->setPaperSize(PageSetup::PAPERSIZE_A4);

    // Title
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'Production Planning Compound Report');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Date Range
    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', "Period: {$startDate} to {$endDate}");
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Column definitions
    $columnNames = [
        'plan' => 'Plan Sum',
        'a_plan' => 'ATPRS',
        'b_plan' => 'B-ATS 15',
        'c_plan' => 'B-BNS 24',
        'd_plan' => 'BG-BLS 12',
        'e_plan' => 'CG -BS 901',
        'f_plan' => 'C-SMS 501',
        'g_plan' => 'C-ATS 20',
        'h_plan' => 'C-SMS 702',
        'i_plan' => 'C-ATNMS 20',
        'j_plan' => 'T - TRS 102',
        'k_plan' => 'T-ATNM S',
        'l_plan' => 'T-ATS 30',
        'm_plan' => 'T-ATS 35',
        'n_plan' => 'T-KS 40',
        'o_plan' => 'T-TRNMS 402',
        'p_plan' => 'T-TRNMS 402G',
        'q_plan' => 'T-TRS 202',
        'r_plan' => 'WC0001'
    ];

    // Set header styles
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F28018']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];

    // Data cell styles
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_RIGHT
        ]
    ];

    // Set up column headers - Dates across the top
    $sheet->setCellValue('A4', 'Product Type');
    $colIndex = 1;
    foreach ($data as $row) {
        $sheet->setCellValueByColumnAndRow($colIndex + 1, 4, $row['date']);
        $colIndex++;
    }
    $sheet->setCellValueByColumnAndRow($colIndex + 1, 4, 'Total');

    // Apply header styles
    $lastCol = $sheet->getHighestColumn();
    $sheet->getStyle("A4:{$lastCol}4")->applyFromArray($headerStyle);

    // Populate data
    $rowIndex = 5;
    foreach ($columnNames as $key => $name) {
        $colIndex = 1;
        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $name);
        
        $rowTotal = 0;
        foreach ($data as $row) {
            $colIndex++;
            $value = $row[$key] ?? 0;
            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, number_format($value, 2));
            $rowTotal += $value;
        }
        
        // Add row total
        $colIndex++;
        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, number_format($rowTotal, 2));
        $rowIndex++;
    }

    // Add column totals
    $sheet->setCellValueByColumnAndRow(1, $rowIndex, 'Total');
    $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
    
    $colIndex = 2;
    $grandTotal = 0;
    foreach ($data as $row) {
        $colTotal = 0;
        foreach ($columnNames as $key => $name) {
            $colTotal += ($row[$key] ?? 0);
        }
        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, number_format($colTotal, 2));
        $grandTotal += $colTotal;
        $colIndex++;
    }
    
    // Add grand total
    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, number_format($grandTotal, 2));

    // Apply data styles
    $lastRow = $sheet->getHighestRow();
    $lastCol = $sheet->getHighestColumn();
    $sheet->getStyle("A5:{$lastCol}{$lastRow}")->applyFromArray($dataStyle);

    // Highlight totals row
    $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getFont()->setBold(true);
    $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getFill()
          ->setFillType(Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F0F0F0');

    // Auto-size columns
    foreach (range('A', $lastCol) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Freeze panes
    $sheet->freezePane('B5');

    // Set print area
    $sheet->getPageSetup()->setPrintArea("A1:{$lastCol}{$lastRow}");

    // Output Excel file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Production_Planning_Report.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    echo "An error occurred during export. Please check the error logs.";
}
?>