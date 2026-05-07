<?php
require 'vendor/autoload.php'; // Make sure PhpSpreadsheet is installed via Composer
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function connectDatabase() {
    $host = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';
    
    // Create a new connection
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function fetchRecords($conn, $startDate, $endDate) {
    $sql = "SELECT w.*, c.com_date
            FROM dwork2 w
            JOIN complete_date c ON w.erp = c.erp
            WHERE c.com_date BETWEEN ? AND ?
            ORDER BY w.erp";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result();
}

function createExcel($data) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $currentErp = null;
    $rowIndex = 1;

    // Check if there are results
    if ($data->num_rows > 0) {
        while ($row = $data->fetch_assoc()) {
            if ($currentErp !== $row['erp']) {
                if ($currentErp !== null) {
                    $rowIndex++; // Add an empty row between different ERP groups
                }
                $currentErp = $row['erp'];

                // Output header for the current ERP group
                $sheet->setCellValue("A$rowIndex", 'ERP Number');
                $sheet->setCellValue("B$rowIndex", 'Date');
                $sheet->setCellValue("C$rowIndex", 'Customer');
                $sheet->setCellValue("D$rowIndex", 'WO No');
                $sheet->setCellValue("E$rowIndex", 'Reference');
                $rowIndex++;

                // Apply header styles
                $sheet->getStyle("A$rowIndex:E$rowIndex")->getFont()->setBold(true);
                $sheet->getStyle("A$rowIndex:E$rowIndex")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle("A$rowIndex:E$rowIndex")->getFill()->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_YELLOW);

                // Output ERP details
                $sheet->setCellValue("A$rowIndex", $currentErp);
                $sheet->setCellValue("B$rowIndex", $row['date']);
                $sheet->setCellValue("C$rowIndex", $row['Customer']);
                $sheet->setCellValue("D$rowIndex", $row['wono']);
                $sheet->setCellValue("E$rowIndex", $row['ref']);
                $rowIndex++;

                // Add label for remaining data
                $sheet->setCellValue("A$rowIndex", 'Remaining Data');
                $rowIndex++;

                // Output remaining data headers
                $headers = ['ICODE', 'T Size', 'Brand', 'Col', 'Fit', 'Rim', 'Cons', 'FWeight', 'PTV', 'New', 'CBM', 'KGS'];
                foreach ($headers as $col => $header) {
                    $sheet->setCellValueByColumnAndRow($col + 1, $rowIndex, $header);
                }

                // Apply styles for remaining data headers
                $sheet->getStyle("A$rowIndex:L$rowIndex")->getFont()->setBold(true);
                $sheet->getStyle("A$rowIndex:L$rowIndex")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle("A$rowIndex:L$rowIndex")->getFill()->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_YELLOW);
                $rowIndex++;
            }

            // Output remaining data
            $sheet->setCellValue("A$rowIndex", $row['icode']);
            $sheet->setCellValue("B$rowIndex", $row['t_size']);
            $sheet->setCellValue("C$rowIndex", $row['brand']);
            $sheet->setCellValue("D$rowIndex", $row['col']);
            $sheet->setCellValue("E$rowIndex", $row['fit']);
            $sheet->setCellValue("F$rowIndex", $row['rim']);
            $sheet->setCellValue("G$rowIndex", $row['cons']);
            $sheet->setCellValue("H$rowIndex", $row['fweight']);
            $sheet->setCellValue("I$rowIndex", $row['ptv']);
            $sheet->setCellValue("J$rowIndex", $row['new']);
            $sheet->setCellValue("K$rowIndex", $row['cbm']);
            $sheet->setCellValue("L$rowIndex", $row['kgs']);
            $rowIndex++;
        }

        // Save the file to output
        $writer = new Xlsx($spreadsheet);
        $filename = 'Work Order.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer->save('php://output');
        exit();
    } else {
        return false; // No records found
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    $conn = connectDatabase();
    $data = fetchRecords($conn, $startDate, $endDate);
    
    if ($data) {
        createExcel($data);
    } else {
        echo "No records found.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Input</title>
</head>
<body>
    <h1>Filter Records by Date Range</h1>
    <form method="POST" action="">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <br><br>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <br><br>
        <input type="submit" value="Export Data">
    </form>
</body>
</html>
