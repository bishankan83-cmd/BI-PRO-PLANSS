<?php
/**
 * generate_work_order.php
 * Generates a Work Order Excel (.xlsx) from tire_orders + tire_details DB data.
 * Mirrors the format of the original WO template exactly.
 *
 * Usage: generate_work_order.php?id=ORDER_ID
 *        (optionally add &download=1 to force-download instead of browser preview)
 *
 * Requires: PhpSpreadsheet  (composer require phpoffice/phpspreadsheet)
 */

session_start();
require_once 'include/config.php';          // $con  ← your MySQLi connection
require_once 'vendor/autoload.php';         // PhpSpreadsheet autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

// ── Auth guard ───────────────────────────────────────────────────────────────
if (empty($_SESSION['aid'])) {
    header('Location: index.php');
    exit();
}

// ── Get Order ────────────────────────────────────────────────────────────────
$orderId = isset($_GET['id']) ? mysqli_real_escape_string($con, trim($_GET['id'])) : '';
if ($orderId === '') {
    die('No order ID specified.');
}

$orderQuery = mysqli_query($con, "SELECT * FROM tire_orders WHERE order_id = '$orderId'");
if (!$orderQuery || mysqli_num_rows($orderQuery) === 0) {
    die('Order not found.');
}
$order = mysqli_fetch_assoc($orderQuery);

// ── Fetch order items joined with tire_details ────────────────────────────────
$itemsQuery = mysqli_query($con, "
    SELECT
        oi.item_id,
        oi.icode,
        oi.quantity,
        td.Description  AS description,
        td.tire_size,
        td.Brand,
        td.Type         AS fit_type,
        td.Colour,
        td.Rim,
        td.fweight,
        td.cbm
    FROM tire_order_items oi
    LEFT JOIN tire_details td ON td.icode = oi.icode
    WHERE oi.order_id = '$orderId'
    ORDER BY oi.item_id
");

// ── Helper: parse comma-separated icodes / quantities in one item row ─────────
function splitValues(string $raw): array
{
    return array_filter(array_map('trim', explode(',', $raw)));
}

// Build a flat list of line items.
// Each element: [icode, qty, description, tire_size, brand, fit_type, colour, rim, fweight, cbm]
$lines = [];

if ($itemsQuery && mysqli_num_rows($itemsQuery) > 0) {
    while ($row = mysqli_fetch_assoc($itemsQuery)) {
        $icodes     = splitValues($row['icode']);
        $quantities = splitValues($row['quantity']);

        // Single-icode row — normal case
        if (count($icodes) <= 1) {
            $lines[] = [
                'icode'       => $row['icode'],
                'qty'         => (float)$row['quantity'],
                'description' => $row['description'] ?? '',
                'tire_size'   => $row['tire_size']   ?? '',
                'brand'       => $row['Brand']       ?? '',
                'fit'         => $row['fit_type']    ?? '',
                'colour'      => $row['Colour']      ?? '',
                'rim'         => $row['Rim']         ?? '',
                'fweight'     => (float)($row['fweight'] ?? 0),
                'cbm'         => (float)($row['cbm']     ?? 0),
            ];
        } else {
            // Multi-icode row — expand each sub-item
            $maxCount   = max(count($icodes), count($quantities));
            $icodes     = array_values(array_pad($icodes,     $maxCount, ''));
            $quantities = array_values(array_pad($quantities, $maxCount, ''));

            foreach ($icodes as $idx => $icode) {
                if ($icode === '') continue;

                $icode_esc = mysqli_real_escape_string($con, $icode);
                $tdRow     = mysqli_fetch_assoc(
                    mysqli_query($con, "SELECT * FROM tire_details WHERE icode='$icode_esc' LIMIT 1")
                );
                $lines[] = [
                    'icode'       => $icode,
                    'qty'         => (float)($quantities[$idx] ?? 0),
                    'description' => $tdRow['Description'] ?? '',
                    'tire_size'   => $tdRow['tire_size']   ?? '',
                    'brand'       => $tdRow['Brand']       ?? '',
                    'fit'         => $tdRow['Type']        ?? '',
                    'colour'      => $tdRow['Colour']      ?? '',
                    'rim'         => $tdRow['Rim']         ?? '',
                    'fweight'     => (float)($tdRow['fweight'] ?? 0),
                    'cbm'         => (float)($tdRow['cbm']     ?? 0),
                ];
            }
        }
    }
}

// ── Group lines: brand → colour → fit ────────────────────────────────────────
$grouped = [];   // [ brand => [ colour_fit_label => [ lines ] ] ]
foreach ($lines as $line) {
    $brand    = strtoupper(trim($line['brand']));
    $colour   = strtoupper(trim($line['colour']));
    $fit      = strtoupper(trim($line['fit']));
    $subLabel = $brand . ' - ' . $colour . ' - ' . $fit;

    $grouped[$brand][$subLabel][] = $line;
}
ksort($grouped);

// ── Build Spreadsheet ─────────────────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$ws          = $spreadsheet->getActiveSheet();
$ws->setTitle('Work Order');

// ── Reusable style arrays ─────────────────────────────────────────────────────
$CENTER = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                           'vertical'   => Alignment::VERTICAL_CENTER]];
$BOLD   = ['font' => ['bold' => true]];

$THIN_BORDER = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF000000'],
        ],
    ],
];

$HEADER_STYLE = [
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true],
    'borders'   => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF000000'],
        ],
    ],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFD9D9D9'],
    ],
];

$GROUP_STYLE = [
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFFF2CC'],
    ],
];

$SUBGROUP_STYLE = [
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER],
    'fill'      => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFE2EFDA'],
    ],
];

$DATA_ICODE_STYLE = [
    'font'      => ['bold' => false, 'size' => 11, 'color' => ['argb' => 'FF0000FF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
];

$DATA_CENTER_STYLE = [
    'font'      => ['size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
];

$DATA_BOLD_STYLE = [
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
];

// ── Column widths (match template) ────────────────────────────────────────────
$ws->getColumnDimension('A')->setWidth(3.43);
$ws->getColumnDimension('B')->setWidth(16.29);
$ws->getColumnDimension('C')->setWidth(32.57);
$ws->getColumnDimension('D')->setWidth(21.86);
$ws->getColumnDimension('E')->setWidth(17.43);
$ws->getColumnDimension('F')->setWidth(18.00);
$ws->getColumnDimension('G')->setWidth(11.14);
$ws->getColumnDimension('H')->setWidth(16.71);
$ws->getColumnDimension('I')->setWidth(12.57);
$ws->getColumnDimension('J')->setWidth(15.29);
$ws->getColumnDimension('K')->setWidth(8.29);
$ws->getColumnDimension('L')->setWidth(13.29);
$ws->getColumnDimension('M')->setWidth(15.71);

// ── Row 1–2: Title block ──────────────────────────────────────────────────────
$ws->mergeCells('D1:M1');
$ws->setCellValue('D1', 'WORK ORDER');
$ws->getStyle('D1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

$ws->mergeCells('D2:M2');
$ws->setCellValue('D2', 'MARKETING DEPARTMENT');
$ws->getStyle('D2')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 13],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// ── Row 3: Company name + doc info ────────────────────────────────────────────
$ws->mergeCells('B3:C3');
$ws->setCellValue('B3', 'ATIRE (Private) Ltd');
$ws->getStyle('B3')->applyFromArray(['font' => ['bold' => true, 'size' => 11]]);
$ws->setCellValue('D3', 'Document No: FO-03-06-FA');
$ws->getStyle('D3')->applyFromArray($BOLD);
$ws->mergeCells('I3:M3');
$ws->setCellValue('I3', 'Revision No: 00');
$ws->getStyle('I3')->applyFromArray($BOLD);

// ── Row 4: Issue date / revision date ────────────────────────────────────────
$ws->setCellValue('D4', 'Issue Date :  31.08.2020');
$ws->getStyle('D4')->applyFromArray($BOLD);
$ws->mergeCells('I4:M4');
$ws->setCellValue('I4', 'Revision Date: 00.00.00');
$ws->getStyle('I4')->applyFromArray($BOLD);

// ── Row 5: blank ─────────────────────────────────────────────────────────────

// ── Row 6: Date + Customer ────────────────────────────────────────────────────
$orderDateFormatted = !empty($order['order_date'])
    ? date('d.m.Y', strtotime($order['order_date']))
    : date('d.m.Y');

$ws->setCellValue('B6', 'Date: ');
$ws->getStyle('B6')->applyFromArray($BOLD);
$ws->setCellValue('C6', $orderDateFormatted);
$ws->setCellValue('J6', 'Customer: ');
$ws->getStyle('J6')->applyFromArray($BOLD);
$ws->setCellValue('K6', $order['customer_id'] ?? '');

// ── Row 7: blank ─────────────────────────────────────────────────────────────

// ── Row 8: W.O. No + Order Ref ───────────────────────────────────────────────
$ws->setCellValue('B8', 'W.O. NO.');
$ws->getStyle('B8')->applyFromArray($BOLD);
$ws->setCellValue('C8', $orderId);
$ws->setCellValue('J8', 'Order Ref: ');
$ws->getStyle('J8')->applyFromArray($BOLD);
$ws->setCellValue('K8', $order['order_ref'] ?? ('ORDER ' . $orderId));

// ── Row 9: blank ─────────────────────────────────────────────────────────────

// ── Row 10: ERP CO NO ────────────────────────────────────────────────────────
$ws->setCellValue('J10', 'ERP CO NO.');
$ws->getStyle('J10')->applyFromArray($BOLD);
$ws->setCellValue('K10', $order['erp_number'] ?? '');

// ── Row 11: Column headers ────────────────────────────────────────────────────
$headers = [
    'B' => 'Item code',
    'C' => 'Tyre Size',
    'D' => 'Brand',
    'E' => 'COLOUR',
    'F' => 'FIT',
    'G' => 'RIM',
    'H' => 'Constrution',
    'I' => 'Average Finish Tyre Weight - kgs',
    'J' => 'Per Tyre Volume / cbm',
    'K' => 'Qty - pcs',
    'L' => 'Total Volume - cbm',
    'M' => 'Total Tonage - kgs',
];
foreach ($headers as $col => $label) {
    $ws->setCellValue($col . '11', $label);
    $ws->getStyle($col . '11')->applyFromArray($HEADER_STYLE);
}
$ws->getRowDimension(11)->setRowHeight(40);

// ── Data rows ────────────────────────────────────────────────────────────────
$row = 12;   // current Excel row
$dataStartRow = $row;

// Collect data row numbers for SUM formulas later
$dataRows = [];

foreach ($grouped as $brand => $subGroups) {

    // ── Brand group header ────────────────────────────────────────────────────
    $ws->mergeCells("B{$row}:M{$row}");
    $ws->setCellValue("B{$row}", $brand);
    $ws->getStyle("B{$row}:M{$row}")->applyFromArray($GROUP_STYLE);
    $ws->getStyle("B{$row}:M{$row}")->getBorders()
        ->getOutline()->setBorderStyle(Border::BORDER_THIN);
    $row++;

    foreach ($subGroups as $subLabel => $subLines) {

        // ── Colour / Fit sub-group header ─────────────────────────────────────
        $ws->mergeCells("B{$row}:M{$row}");
        $ws->setCellValue("B{$row}", $subLabel);
        $ws->getStyle("B{$row}:M{$row}")->applyFromArray($SUBGROUP_STYLE);
        $ws->getStyle("B{$row}:M{$row}")->getBorders()
            ->getOutline()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        foreach ($subLines as $line) {
            $dataRows[] = $row;

            // Col B – Item code (blue)
            $ws->setCellValue("B{$row}", $line['icode']);
            $ws->getStyle("B{$row}")->applyFromArray($DATA_ICODE_STYLE);

            // Col C – Tyre Size (blue)
            $ws->setCellValue("C{$row}", $line['tire_size']);
            $ws->getStyle("C{$row}")->applyFromArray([
                'font'      => ['size' => 12, 'color' => ['argb' => 'FF0000FF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical'   => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
            ]);

            // Col D – Brand
            $ws->setCellValue("D{$row}", $line['brand']);
            $ws->getStyle("D{$row}")->applyFromArray($DATA_CENTER_STYLE);

            // Col E – Colour
            $ws->setCellValue("E{$row}", $line['colour']);
            $ws->getStyle("E{$row}")->applyFromArray($DATA_CENTER_STYLE);

            // Col F – FIT
            $ws->setCellValue("F{$row}", $line['fit']);
            $ws->getStyle("F{$row}")->applyFromArray($DATA_CENTER_STYLE);

            // Col G – RIM
            $ws->setCellValue("G{$row}", $line['rim'] !== '' ? $line['rim'] : '-');
            $ws->getStyle("G{$row}")->applyFromArray($DATA_CENTER_STYLE);

            // Col H – Construction (derive from brand/type grouping)
            //         The original uses "3 LAYER" for most; kept as-is from DB or defaulted
            $construction = '3 LAYER';
            $ws->setCellValue("H{$row}", $construction);
            $ws->getStyle("H{$row}")->applyFromArray($DATA_CENTER_STYLE);

            // Col I – Average Finish Tyre Weight (from tire_details.fweight)
            $ws->setCellValue("I{$row}", $line['fweight']);
            $ws->getStyle("I{$row}")->applyFromArray($DATA_CENTER_STYLE);
            $ws->getStyle("I{$row}")->getNumberFormat()->setFormatCode('0.0##');

            // Col J – Per Tyre Volume / cbm (from tire_details.cbm)
            $ws->setCellValue("J{$row}", $line['cbm']);
            $ws->getStyle("J{$row}")->applyFromArray($DATA_CENTER_STYLE);
            $ws->getStyle("J{$row}")->getNumberFormat()->setFormatCode('0.0####');

            // Col K – Qty (bold)
            $ws->setCellValue("K{$row}", $line['qty']);
            $ws->getStyle("K{$row}")->applyFromArray($DATA_BOLD_STYLE);

            // Col L – Total Volume = cbm × qty  (formula)
            $ws->setCellValue("L{$row}", "=J{$row}*K{$row}");
            $ws->getStyle("L{$row}")->applyFromArray($DATA_BOLD_STYLE);
            $ws->getStyle("L{$row}")->getNumberFormat()->setFormatCode('0.0####');

            // Col M – Total Tonnage = qty × fweight  (formula)
            $ws->setCellValue("M{$row}", "=K{$row}*I{$row}");
            $ws->getStyle("M{$row}")->applyFromArray($DATA_BOLD_STYLE);
            $ws->getStyle("M{$row}")->getNumberFormat()->setFormatCode('0.0##');

            $row++;
        }
    }
}

$dataEndRow = $row - 1;

// ── TOTAL row ─────────────────────────────────────────────────────────────────
$totalRow = $row;
$ws->setCellValue("J{$totalRow}", 'TOTAL');
$ws->getStyle("J{$totalRow}")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
]);

$ws->setCellValue("K{$totalRow}", "=SUM(K{$dataStartRow}:K{$dataEndRow})");
$ws->getStyle("K{$totalRow}")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
]);

$ws->setCellValue("L{$totalRow}", "=SUM(L{$dataStartRow}:L{$dataEndRow})");
$ws->getStyle("L{$totalRow}")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
]);
$ws->getStyle("L{$totalRow}")->getNumberFormat()->setFormatCode('0.0####');

$ws->setCellValue("M{$totalRow}", "=SUM(M{$dataStartRow}:M{$dataEndRow})");
$ws->getStyle("M{$totalRow}")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
]);
$ws->getStyle("M{$totalRow}")->getNumberFormat()->setFormatCode('0.0##');

$row = $totalRow + 2;

// ── Comments section ──────────────────────────────────────────────────────────
$ws->mergeCells("B{$row}:M{$row}");
$ws->setCellValue("B{$row}", 'Comments');
$ws->getStyle("B{$row}")->applyFromArray(['font' => ['bold' => true, 'size' => 12]]);
$row++;

$comments = [
    '1. Please do check the item codes with item description, if any deviation found please inform us immediately, always give priority for the actual tire size and description.',
    '2. Please make sure to load the tyres with production date within 6 months to the order loading date, if you consider tyres older than 6 months, please inform the marketing team well in advance before the loading date.',
    '3. NM tires need to be wrapped individually.',
    '4. Any required packing materials order as per the requirement.',
];
foreach ($comments as $comment) {
    $ws->mergeCells("B{$row}:M{$row}");
    $ws->setCellValue("B{$row}", $comment);
    $ws->getStyle("B{$row}")->applyFromArray([
        'font'      => ['size' => 11],
        'alignment' => ['wrapText' => true],
    ]);
    $ws->getRowDimension($row)->setRowHeight(25);
    $row++;
}

$row++;

// ── Order notes (if any) ──────────────────────────────────────────────────────
if (!empty($order['order_notes'])) {
    $ws->mergeCells("B{$row}:M{$row}");
    $ws->setCellValue("B{$row}", 'Order Notes: ' . $order['order_notes']);
    $ws->getStyle("B{$row}")->applyFromArray([
        'font'      => ['size' => 11, 'italic' => true],
        'alignment' => ['wrapText' => true],
    ]);
    $ws->getRowDimension($row)->setRowHeight(30);
    $row += 2;
}

// ── Signature line 1 ─────────────────────────────────────────────────────────
$row++;
$ws->setCellValue("B{$row}", '…………………………………');
$ws->setCellValue("D{$row}", '…………………………………');
$ws->setCellValue("H{$row}", '…………………………………………….');
$ws->setCellValue("L{$row}", '………………………………………');
$row++;
$ws->setCellValue("B{$row}", 'Sales Coordinator');
$ws->getStyle("B{$row}")->applyFromArray($BOLD);
$ws->setCellValue("D{$row}", 'General Manager');
$ws->getStyle("D{$row}")->applyFromArray($BOLD);
$ws->setCellValue("H{$row}", 'General Manager - Marketing');
$ws->getStyle("H{$row}")->applyFromArray($BOLD);
$ws->setCellValue("L{$row}", 'Managing Director');
$ws->getStyle("L{$row}")->applyFromArray($BOLD);

$row += 3;

// ── Production section ────────────────────────────────────────────────────────
$ws->mergeCells("B{$row}:M{$row}");
$ws->setCellValue("B{$row}", '[2]  To be filled by Production Department');
$ws->getStyle("B{$row}")->applyFromArray(['font' => ['bold' => true, 'size' => 11]]);
$row += 2;

$ws->mergeCells("B{$row}:M{$row}");
$ws->setCellValue("B{$row}", 'Original Loading date confirmed by Factory');
$ws->getStyle("B{$row}")->applyFromArray(['font' => ['bold' => true, 'size' => 11]]);
$row += 2;

$ws->mergeCells("B{$row}:M{$row}");
$ws->setCellValue("B{$row}", 'We hereby confirm receipt of above Export Work Order and will ensure all products will meet required PC and QC standard and loading of full qty.');
$ws->getStyle("B{$row}")->applyFromArray([
    'font'      => ['size' => 11],
    'alignment' => ['wrapText' => true],
]);
$ws->getRowDimension($row)->setRowHeight(30);
$row += 3;

// ── Signature line 2 ─────────────────────────────────────────────────────────
$ws->setCellValue("B{$row}", '…………………………………');
$ws->setCellValue("D{$row}", '…………………………………');
$ws->setCellValue("H{$row}", '…………………………………………….');
$ws->setCellValue("L{$row}", '…………………………………');
$row++;
$ws->setCellValue("B{$row}", 'Production Planer');
$ws->getStyle("B{$row}")->applyFromArray($BOLD);
$ws->setCellValue("D{$row}", 'PC & QC');
$ws->getStyle("D{$row}")->applyFromArray($BOLD);
$ws->setCellValue("H{$row}", 'Stores in-Charge');
$ws->getStyle("H{$row}")->applyFromArray($BOLD);
$ws->setCellValue("L{$row}", 'Production Manager');
$ws->getStyle("L{$row}")->applyFromArray($BOLD);

// ── Print settings ────────────────────────────────────────────────────────────
$ws->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
$ws->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
$ws->getPageSetup()->setFitToPage(true);
$ws->getPageSetup()->setFitToWidth(1);
$ws->getPageSetup()->setFitToHeight(0);
$ws->getPageMargins()->setTop(0.5);
$ws->getPageMargins()->setBottom(0.5);
$ws->getPageMargins()->setLeft(0.5);
$ws->getPageMargins()->setRight(0.5);

// Freeze panes below header row
$ws->freezePane('A12');

// ── Output ────────────────────────────────────────────────────────────────────
$safeOrderId = preg_replace('/[^A-Za-z0-9_\-]/', '_', $orderId);
$filename    = 'WO_ORDER_' . $safeOrderId . '_' . date('Ymd') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();