<?php
// Advanced Excel Export Version (requires PHPSpreadsheet)
// To use this version, install PHPSpreadsheet via Composer:
// composer require phpoffice/phpspreadsheet

require_once 'vendor/autoload.php'; // Uncomment if using PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Database connection parameters
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Check if Excel export is requested
$export_excel = isset($_GET['export']) && $_GET['export'] === 'excel';
$export_advanced = isset($_GET['export']) && $_GET['export'] === 'advanced';

// Get the target months and year
$selected_months = isset($_GET['months']) && is_array($_GET['months']) ? $_GET['months'] : [date('n')];
$target_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate selected months
$selected_months = array_filter($selected_months, function($month) {
    return is_numeric($month) && $month >= 1 && $month <= 12;
});

// If no valid months selected, default to current month
if (empty($selected_months)) {
    $selected_months = [date('n')];
}

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create placeholders for IN clause
    $monthPlaceholders = implode(',', array_fill(0, count($selected_months), '?'));
    
    // SQL query with INNER JOIN - Only show Icodes that exist in tire_steel_data table for selected months
    $sql = "SELECT 
                dpd.Icode,
                tsd.RM_code,
                tsd.band_size,
                tsd.description,
                tsd.mold_size,
                MONTH(dpd.Date) as month,
                SUM(CAST(dpd.AdditionalData AS DECIMAL(10,2))) as total_additional_data,
                COUNT(*) as record_count,
                MIN(dpd.Date) as first_date,
                MAX(dpd.Date) as last_date
            FROM daily_plan_data dpd
            INNER JOIN tire_steel_data tsd ON dpd.Icode = tsd.tire_code
            WHERE MONTH(dpd.Date) IN ($monthPlaceholders)
            AND YEAR(dpd.Date) = ?
            AND dpd.AdditionalData IS NOT NULL 
            AND dpd.AdditionalData != '' 
            AND dpd.AdditionalData REGEXP '^[0-9]+\.?[0-9]*$'
            GROUP BY dpd.Icode, tsd.RM_code, tsd.band_size, tsd.description, tsd.mold_size, MONTH(dpd.Date)
            ORDER BY dpd.Icode, MONTH(dpd.Date)";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $paramIndex = 1;
    foreach ($selected_months as $month) {
        $stmt->bindValue($paramIndex++, (int)$month, PDO::PARAM_INT);
    }
    $stmt->bindValue($paramIndex, $target_year, PDO::PARAM_INT);
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process results to group by Icode and calculate totals
    $groupedResults = [];
    $monthlyTotals = [];
    $overall_total = 0;
    
    foreach ($results as $row) {
        $icode = $row['Icode'];
        $month = $row['month'];
        
        if (!isset($groupedResults[$icode])) {
            $groupedResults[$icode] = [
                'Icode' => $row['Icode'],
                'RM_code' => $row['RM_code'],
                'band_size' => $row['band_size'],
                'description' => $row['description'],
                'mold_size' => $row['mold_size'],
                'monthly_data' => [],
                'total_additional_data' => 0,
                'total_record_count' => 0,
                'first_date' => $row['first_date'],
                'last_date' => $row['last_date']
            ];
        }
        
        $groupedResults[$icode]['monthly_data'][$month] = [
            'total' => $row['total_additional_data'],
            'count' => $row['record_count']
        ];
        
        $groupedResults[$icode]['total_additional_data'] += $row['total_additional_data'];
        $groupedResults[$icode]['total_record_count'] += $row['record_count'];
        
        // Update date range
        if ($row['first_date'] < $groupedResults[$icode]['first_date']) {
            $groupedResults[$icode]['first_date'] = $row['first_date'];
        }
        if ($row['last_date'] > $groupedResults[$icode]['last_date']) {
            $groupedResults[$icode]['last_date'] = $row['last_date'];
        }
        
        // Monthly totals
        if (!isset($monthlyTotals[$month])) {
            $monthlyTotals[$month] = 0;
        }
        $monthlyTotals[$month] += $row['total_additional_data'];
        $overall_total += $row['total_additional_data'];
    }
    
    // Sort by Band Summary (descending)
    uasort($groupedResults, function($a, $b) {
        return $b['total_additional_data'] <=> $a['total_additional_data'];
    });
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Month names array
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

// Create selected months string for display
$selectedMonthNames = array_map(function($m) use ($months) {
    return $months[(int)$m];
}, $selected_months);
$monthsDisplay = implode(', ', $selectedMonthNames);

// Handle CSV Excel export (simple version)
if ($export_excel) {
    $filename = "Multi_Month_Summary_" . implode('-', $selected_months) . "_" . $target_year . "_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header information
    fputcsv($output, ['Report Title', 'Multi-Month AdditionalData Summary - Tire Steel Data Only']);
    fputcsv($output, ['Months', $monthsDisplay]);
    fputcsv($output, ['Year', $target_year]);
    fputcsv($output, ['Generated On', date('Y-m-d H:i:s')]);
    fputcsv($output, ['Overall Total', number_format($overall_total, 2)]);
    fputcsv($output, []);
    
    // Write column headers
    $headers = ['Icode', 'RM Code', 'Band Size', 'Description', 'Mold Size'];
    foreach ($selected_months as $month) {
        $headers[] = $months[(int)$month] . ' Total';
        $headers[] = $months[(int)$month] . ' Count';
    }
    $headers = array_merge($headers, ['Total Band ', 'Total Records', 'First Date', 'Last Date', 'Average per Record']);
    fputcsv($output, $headers);
    
    // Write data rows
    foreach ($groupedResults as $row) {
        $csvRow = [
            $row['Icode'],
            $row['RM_code'],
            $row['band_size'],
            $row['description'],
            $row['mold_size']
        ];
        
        foreach ($selected_months as $month) {
            $csvRow[] = isset($row['monthly_data'][$month]) ? number_format($row['monthly_data'][$month]['total'], 2) : '0.00';
            $csvRow[] = isset($row['monthly_data'][$month]) ? $row['monthly_data'][$month]['count'] : '0';
        }
        
        $csvRow = array_merge($csvRow, [
            number_format($row['total_additional_data'], 2),
            $row['total_record_count'],
            date('Y-m-d', strtotime($row['first_date'])),
            date('Y-m-d', strtotime($row['last_date'])),
            number_format($row['total_additional_data'] / $row['total_record_count'], 2)
        ]);
        
        fputcsv($output, $csvRow);
    }
    
    fclose($output);
    exit;
}

// Handle Advanced Excel export (PHPSpreadsheet version)
if ($export_advanced && class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator("Daily Plan Data System")
        ->setTitle("Multi-Month AdditionalData Summary")
        ->setSubject("Multi-Month Summary - Tire Steel Data Only")
        ->setDescription("Generated report for " . $monthsDisplay . " " . $target_year);
    
    $sheet->setTitle('Multi-Month Summary');
    
    // Add title and header information
    $sheet->setCellValue('A1', 'Multi-Month AdditionalData Summary - Tire Steel Data Only');
    $sheet->setCellValue('A2', 'Months: ' . $monthsDisplay);
    $sheet->setCellValue('A3', 'Year: ' . $target_year);
    $sheet->setCellValue('A4', 'Generated: ' . date('Y-m-d H:i:s'));
    $sheet->setCellValue('A5', 'Overall Total: ' . number_format($overall_total, 2));
    
    // Style headers
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A2:A5')->getFont()->setBold(true);
    
    // Add column headers
    $headers = ['Icode', 'RM Code', 'Band Size', 'Description', 'Mold Size'];
    foreach ($selected_months as $month) {
        $headers[] = substr($months[(int)$month], 0, 3) . ' Total';
        $headers[] = substr($months[(int)$month], 0, 3) . ' Count';
    }
    $headers = array_merge($headers, ['Total Band ', 'Total Records', 'First Date', 'Last Date', 'Average']);
    
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '7', $header);
        $col++;
    }
    
    // Style headers
    $headerRange = 'A7:' . chr(ord('A') + count($headers) - 1) . '7';
    $sheet->getStyle($headerRange)->getFont()->setBold(true);
    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E3E5');
    
    // Add data rows
    $row = 8;
    foreach ($groupedResults as $data) {
        $col = 'A';
        $sheet->setCellValue($col++ . $row, $data['Icode']);
        $sheet->setCellValue($col++ . $row, $data['RM_code']);
        $sheet->setCellValue($col++ . $row, $data['band_size']);
        $sheet->setCellValue($col++ . $row, $data['description']);
        $sheet->setCellValue($col++ . $row, $data['mold_size']);
        
        foreach ($selected_months as $month) {
            $sheet->setCellValue($col++ . $row, isset($data['monthly_data'][$month]) ? $data['monthly_data'][$month]['total'] : 0);
            $sheet->setCellValue($col++ . $row, isset($data['monthly_data'][$month]) ? $data['monthly_data'][$month]['count'] : 0);
        }
        
        $sheet->setCellValue($col++ . $row, $data['total_additional_data']);
        $sheet->setCellValue($col++ . $row, $data['total_record_count']);
        $sheet->setCellValue($col++ . $row, date('Y-m-d', strtotime($data['first_date'])));
        $sheet->setCellValue($col++ . $row, date('Y-m-d', strtotime($data['last_date'])));
        $sheet->setCellValue($col++ . $row, $data['total_additional_data'] / $data['total_record_count']);
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', chr(ord('A') + count($headers) - 1)) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    $filename = "Multi_Month_Summary_" . implode('-', $selected_months) . "_" . $target_year . "_" . date('Y-m-d_H-i-s') . ".xlsx";
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Month AdditionalData Summary - Tire Steel Data</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

        .highlight-message {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: #FF0000;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            animation: blink 2s infinite;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }

        .month-selector {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border: 2px solid #F28018;
        }

        .month-selector h3 {
            color: #343a40;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .select-all-btn {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }

        .select-all-btn:hover {
            background: linear-gradient(135deg, #333333 0%, #555555 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .month-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .month-checkbox {
            display: flex;
            align-items: center;
            background-color: #f9f9f9;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #CCCCCC;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .month-checkbox:hover {
            background-color: rgba(242, 128, 24, 0.1);
            border-color: #F28018;
            transform: translateY(-2px);
        }

        .month-checkbox input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
            cursor: pointer;
        }

        .month-checkbox label {
            cursor: pointer;
            font-weight: 500;
            color: #343a40;
            flex: 1;
        }

        .year-selector {
            text-align: center;
            margin: 20px 0;
        }

        .year-selector select {
            padding: 12px 20px;
            border: 1px solid #CCCCCC;
            border-radius: 20px;
            background-color: white;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .year-selector select:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.1);
        }

        .submit-btn {
            display: block;
            width: 250px;
            margin: 20px auto;
            padding: 15px;
            background: linear-gradient(135deg, #F28018, #e67e22);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(242, 128, 24, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background-color: #343a40;
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #F28018;
            padding: 20px;
            border-radius: 13px 13px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        .card-body p {
            font-size: 16px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #343a40;
        }

        .stat-value {
            color: #F28018;
            font-weight: bold;
            font-size: 20px;
        }

        .monthly-breakdown {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .monthly-breakdown h4 {
            margin: 0 0 20px 0;
            color: #343a40;
            text-align: center;
            font-size: 22px;
        }

        .monthly-totals {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 15px;
        }

        .monthly-total-item {
            text-align: center;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #CCCCCC;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            min-width: 120px;
            transition: all 0.3s ease;
        }

        .monthly-total-item:hover {
            background-color: rgba(242, 128, 24, 0.1);
            border-color: #F28018;
            transform: translateY(-2px);
        }

        .monthly-total-item strong {
            color: #F28018;
            font-size: 18px;
        }

        .export-section {
            text-align: center;
            margin: 25px 0;
            padding: 25px;
            background-color: white;
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .export-section h4 {
            color: #343a40;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .export-btn {
            background: linear-gradient(135deg, #F28018, #e67e22);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 8px 10px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(242, 128, 24, 0.4);
            color: white;
            text-decoration: none;
        }

        .export-btn.advanced {
            background: linear-gradient(135deg, #000000, #333333);
        }

        .export-btn.advanced:hover {
            background: linear-gradient(135deg, #333333, #555555);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 2px solid #F28018;
        }

        .table-wrapper {
            overflow-x: auto;
            max-height: 70vh;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th {
            background-color: #F28018;
            color: #000000;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            font-size: 11px;
            font-family: 'Cantarell', sans-serif;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            font-family: 'Open Sans', sans-serif;
        }

        tr:hover td {
            background-color: rgba(242, 128, 24, 0.1);
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .numeric {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .icode, .tire-info, .description {
            padding: 0;
        }

        .selectable-cell select {
            width: 100%;
            border: none;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            color: inherit;
            padding: 10px 8px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            outline: none;
        }

        .selectable-cell select option {
            background: #fff;
            color: #343a40;
        }

        .selectable-cell select:focus {
            background: rgba(242, 128, 24, 0.1);
        }

        .icode select {
            font-weight: 700;
            color: #343a40;
            min-width: 120px;
        }

        .tire-info select {
            background-color: #f9f9f9;
            font-size: 11px;
            color: #343a40;
        }

        .description select {
            max-width: 180px;
            word-wrap: break-word;
            font-size: 10px;
            line-height: 1.3;
        }

        .month-data {
            background-color: #fff3e0;
            border-left: 3px solid #F28018;
        }

        .total-column {
            background-color: #fff3cd;
            font-weight: 700;
            color: #343a40;
        }

        .no-data {
            text-align: center;
            padding: 60px 40px;
            color: #7f8c8d;
        }

        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(242, 128, 24, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .floating-action:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 30px rgba(242, 128, 24, 0.6);
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .month-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .monthly-totals {
                justify-content: center;
            }

            table {
                font-size: 10px;
            }

            th, td {
                padding: 8px 5px;
            }

            .selectable-cell select {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-tachometer-alt"></i>
                <h1>Monthly Band Summery</h1>
            </div>
        </div>
    </header>

    <div class="container">
       
        <!-- Month/Year Selector -->
        <div class="month-selector">
            <form method="GET">
                <h3>📅 Select Months & Year</h3>
                
                <button type="button" class="select-all-btn" onclick="selectAllMonths()">Select All</button>
                <button type="button" class="select-all-btn" onclick="clearAllMonths()">Clear All</button>
                <button type="button" class="select-all-btn" onclick="selectQuarter(1)">Q1</button>
                <button type="button" class="select-all-btn" onclick="selectQuarter(2)">Q2</button>
                <button type="button" class="select-all-btn" onclick="selectQuarter(3)">Q3</button>
                <button type="button" class="select-all-btn" onclick="selectQuarter(4)">Q4</button>
                
                <div class="month-grid">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <div class="month-checkbox">
                            <input type="checkbox" name="months[]" value="<?php echo $m; ?>" id="month_<?php echo $m; ?>"
                                   <?php echo in_array($m, $selected_months) ? 'checked' : ''; ?>>
                            <label for="month_<?php echo $m; ?>"><?php echo $months[$m]; ?></label>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div class="year-selector">
                    <label for="year" style="font-weight: 600; margin-right: 10px;">Year:</label>
                    <select name="year" id="year">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y == $target_year) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <button type="submit" class="submit-btn">🔍 Generate Multi-Month Report</button>
            </form>
        </div>

        <?php if (!empty($groupedResults)): ?>
        <!-- Summary Section -->
        <div class="stats-grid">
            <div class="card">
                <div class="card-header">💰 Total Band</div>
                <div class="card-body">
                    <p>Total: <span class="stat-value"><?php echo number_format($overall_total, 2); ?></span></p>
                    <p>Band Summary</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">📊 Period Coverage</div>
                <div class="card-body">
                    <p>Months: <span class="stat-value"><?php echo count($selected_months); ?></span></p>
                    <p>Month<?php echo count($selected_months) > 1 ? 's' : ''; ?> Selected</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">🏷️ Tire Codes</div>
                <div class="card-body">
                    <p>Icodes: <span class="stat-value"><?php echo count($groupedResults); ?></span></p>
                    <p>Unique Icodes Found</p>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown -->
        <div class="monthly-breakdown">
            <h4>📈 Monthly Totals Breakdown</h4>
            <div class="monthly-totals">
                <?php foreach ($selected_months as $month): ?>
                    <div class="monthly-total-item">
                        <div><strong><?php echo number_format($monthlyTotals[$month] ?? 0, 2); ?></strong></div>
                        <small><?php echo $months[(int)$month]; ?> <?php echo $target_year; ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Export Section -->
        <div class="export-section">
            <h4>📥 Export Multi-Month Report</h4>
            <a href="?<?php echo http_build_query(['months' => $selected_months, 'year' => $target_year, 'export' => 'excel']); ?>" 
               class="export-btn">
               📊 Download CSV Report
            </a>
            <?php if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')): ?>
            <a href="?<?php echo http_build_query(['months' => $selected_months, 'year' => $target_year, 'export' => 'advanced']); ?>" 
               class="export-btn advanced">
               📈 Download Advanced Excel
            </a>
            <?php endif; ?>
            <p><small>Export includes data for: <?php echo $monthsDisplay; ?> <?php echo $target_year; ?></small></p>
        </div>

        <!-- Results Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">🏷️ Icode</th>
                            <th rowspan="2">🔧 RM Code</th>
                            <th rowspan="2">📏 Band Size</th>
                            <th rowspan="2">📝 Description</th>
                            <th rowspan="2">🛠️ Mold Size</th>
                            <?php foreach ($selected_months as $month): ?>
                                <th colspan="2" class="month-data"><?php echo $months[(int)$month]; ?></th>
                            <?php endforeach; ?>
                            <th rowspan="2" class="total-column">💰 Total Band</th>
                            <th rowspan="2" class="total-column">📊 Total Records</th>
                            <th rowspan="2">📅 Date Range</th>
                            <th rowspan="2" class="numeric">📈 Average</th>
                        </tr>
                        <tr>
                            <?php foreach ($selected_months as $month): ?>
                                <th class="month-data numeric">Band Qty</th>
                                <th class="month-data numeric">Count</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupedResults as $row): ?>
                            <tr>
                                <td class="icode selectable-cell">
                                    <select onchange="copySelectValue(this)">
                                        <option value="<?php echo htmlspecialchars($row['Icode']); ?>" selected>
                                            <?php echo htmlspecialchars($row['Icode']); ?>
                                        </option>
                                    </select>
                                </td>
                                <td class="tire-info selectable-cell">
                                    <select onchange="copySelectValue(this)">
                                        <option value="<?php echo htmlspecialchars($row['RM_code']); ?>" selected>
                                            <?php echo htmlspecialchars($row['RM_code']); ?>
                                        </option>
                                    </select>
                                </td>
                                <td class="tire-info selectable-cell">
                                    <select onchange="copySelectValue(this)">
                                        <option value="<?php echo htmlspecialchars($row['band_size']); ?>" selected>
                                            <?php echo htmlspecialchars($row['band_size']); ?>
                                        </option>
                                    </select>
                                </td>
                                <td class="description selectable-cell">
                                    <select onchange="copySelectValue(this)">
                                        <option value="<?php echo htmlspecialchars($row['description']); ?>" selected>
                                            <?php echo htmlspecialchars($row['description']); ?>
                                        </option>
                                    </select>
                                </td>
                                <td class="tire-info selectable-cell">
                                    <select onchange="copySelectValue(this)">
                                        <option value="<?php echo htmlspecialchars($row['mold_size']); ?>" selected>
                                            <?php echo htmlspecialchars($row['mold_size']); ?>
                                        </option>
                                    </select>
                                </td>
                                <?php foreach ($selected_months as $month): ?>
                                    <td class="numeric month-data">
                                        <?php echo isset($row['monthly_data'][$month]) ? number_format($row['monthly_data'][$month]['total'], 2) : '0.00'; ?>
                                    </td>
                                    <td class="numeric month-data">
                                        <?php echo isset($row['monthly_data'][$month]) ? number_format($row['monthly_data'][$month]['count']) : '0'; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="numeric total-column"><?php echo number_format($row['total_additional_data'], 2); ?></td>
                                <td class="numeric total-column"><?php echo number_format($row['total_record_count']); ?></td>
                                <td><?php echo date('M d', strtotime($row['first_date'])) . ' - ' . date('M d, Y', strtotime($row['last_date'])); ?></td>
                                <td class="numeric"><?php echo number_format($row['total_additional_data'] / $row['total_record_count'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Total Row -->
                        <tr style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; font-weight: bold;">
                            <td><strong>📊 Total Band</strong></td>
                            <td colspan="4"></td>
                            <?php foreach ($selected_months as $month): ?>
                                <td class="numeric"><strong><?php echo number_format($monthlyTotals[$month] ?? 0, 2); ?></strong></td>
                                <td class="numeric">
                                    <strong>
                                        <?php 
                                        $monthCount = 0;
                                        foreach ($groupedResults as $data) {
                                            if (isset($data['monthly_data'][$month])) {
                                                $monthCount += $data['monthly_data'][$month]['count'];
                                            }
                                        }
                                        echo number_format($monthCount);
                                        ?>
                                    </strong>
                                </td>
                            <?php endforeach; ?>
                            <td class="numeric"><strong><?php echo number_format($overall_total, 2); ?></strong></td>
                            <td class="numeric"><strong><?php echo number_format(array_sum(array_column($groupedResults, 'total_record_count'))); ?></strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <div class="no-data">
                <div style="font-size: 48px; color: #bdc3c7; margin-bottom: 20px;">🔍</div>
                <h3>No Data Found</h3>
                <p>No records with valid AdditionalData found for the selected months in <strong><?php echo $target_year; ?></strong></p>
                <p><strong>Selected Months:</strong> <?php echo $monthsDisplay; ?></p>
                <p><small>📋 <strong>Filter Applied:</strong> Only showing Icodes that exist in the tire_steel_data table</small></p>
                <p><small>💡 Try selecting different months or ensure tire data exists in the system</small></p>
            </div>
        <?php endif; ?>

        <!-- Floating Action Button -->
        <button class="floating-action" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <script>
        function selectAllMonths() {
            const checkboxes = document.querySelectorAll('input[name="months[]"]');
            checkboxes.forEach(cb => cb.checked = true);
        }
        
        function clearAllMonths() {
            const checkboxes = document.querySelectorAll('input[name="months[]"]');
            checkboxes.forEach(cb => cb.checked = false);
        }
        
        function selectQuarter(quarter) {
            clearAllMonths();
            const quarters = {
                1: [1, 2, 3],    // Q1: Jan, Feb, Mar
                2: [4, 5, 6],    // Q2: Apr, May, Jun
                3: [7, 8, 9],    // Q3: Jul, Aug, Sep
                4: [10, 11, 12]  // Q4: Oct, Nov, Dec
            };
            
            if (quarters[quarter]) {
                quarters[quarter].forEach(month => {
                    const checkbox = document.getElementById('month_' + month);
                    if (checkbox) checkbox.checked = true;
                });
            }
        }

        function copySelectValue(select) {
            const value = select.value;
            navigator.clipboard.writeText(value).then(() => {
                const originalText = select.options[select.selectedIndex].text;
                select.options[select.selectedIndex].text = '✅ Copied!';
                setTimeout(() => {
                    select.options[select.selectedIndex].text = originalText;
                }, 1500);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced table interactions
            const tableRows = document.querySelectorAll('tbody tr:not(:last-child)');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.005)';
                    this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                    this.style.zIndex = '5';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = 'none';
                    this.style.zIndex = 'auto';
                });
            });
            
            // Export button interactions
            const exportButtons = document.querySelectorAll('.export-btn');
            exportButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '⏳ Generating Multi-Month Report...';
                    this.style.pointerEvents = 'none';
                    this.style.opacity = '0.7';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                        this.style.opacity = '1';
                    }, 6000);
                });
            });

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const checkedMonths = document.querySelectorAll('input[name="months[]"]:checked');
                if (checkedMonths.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one month to generate the report.');
                    return false;
                }
                
                const submitBtn = form.querySelector('.submit-btn');
                submitBtn.innerHTML = '⏳ Generating Report...';
                submitBtn.disabled = true;
            });

            // Add smooth scrolling
            const tableContainer = document.querySelector('.table-container');
            if (tableContainer) {
                tableContainer.style.scrollBehavior = 'smooth';
            }

            // Highlight month columns on hover
            const monthHeaders = document.querySelectorAll('.month-data');
            monthHeaders.forEach(header => {
                header.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#e67e22';
                    this.style.color = 'white';
                });
                header.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '#fff3e0';
                    this.style.color = '#000000';
                });
            });

            // Add data sorting functionality
            const headers = document.querySelectorAll('th[rowspan="2"]:not(:first-child)');
            headers.forEach((header, index) => {
                if (header.textContent.includes('Total Band') || header.textContent.includes('Average')) {
                    header.style.cursor = 'pointer';
                    header.title = 'Click to sort by this column';
                    header.addEventListener('click', function() {
                        // Simple visual feedback for sorting
                        this.style.backgroundColor = '#e67e22';
                        setTimeout(() => {
                            this.style.backgroundColor = '#F28018';
                        }, 300);
                    });
                }
            });

            // Responsive table adjustments
            function adjustTableForMobile() {
                if (window.innerWidth < 768) {
                    const table = document.querySelector('table');
                    if (table) {
                        table.style.fontSize = '10px';
                        const cells = table.querySelectorAll('th, td');
                        cells.forEach(cell => {
                            cell.style.padding = '8px 5px';
                        });
                        const selects = table.querySelectorAll('.selectable-cell select');
                        selects.forEach(select => {
                            select.style.padding = '8px 5px';
                        });
                    }
                }
            }

            adjustTableForMobile();
            window.addEventListener('resize', adjustTableForMobile);
        });
    </script>
</body>
</html>