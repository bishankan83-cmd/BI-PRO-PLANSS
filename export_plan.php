<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete all existing data from cal_report table
    $deleteStmt = $pdo->prepare("DELETE FROM cal_report");
    $deleteStmt->execute();
    
    // Fetch data from the 'cal' table and join with necessary tables
    $stmt = $pdo->prepare("
        SELECT cal.*, tire_details.description, tire_details.rim, tire_details.brand, tire_details.type, tire_details.colour,
               press_cavity.press_id, press.press_name, tobe.total_tobe, bom_new.`grand totalcompound weight`, bom_new.`green tire weight`,
               DATE(new_process.start_date) AS new_process_start_date, new_process.erp
        FROM cal
        LEFT JOIN tire_details ON cal.icode = tire_details.icode
        LEFT JOIN press_cavity ON cal.cavity_id = press_cavity.cavity_id
        LEFT JOIN press ON press_cavity.press_id = press.press_id
        LEFT JOIN alp ON press.press_name = alp.press_name
        LEFT JOIN tobe ON cal.icode = tobe.icode
        LEFT JOIN bom_new ON cal.icode = bom_new.icode
        LEFT JOIN new_process ON DATE(cal.start_date) = DATE(new_process.start_date)
                              AND cal.icode = new_process.icode 
                              AND cal.mold_id = new_process.mold_id 
                              AND cal.tires_per_mold = new_process.tires_per_mold 
                              AND cal.cavity_id = new_process.cavity_id
        ORDER BY alp.id ASC, cal.cavity_id ASC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the insert statement
    $insertStmt = $pdo->prepare("
        INSERT INTO cal_report (press_name, icode, description, rim, brand, type, colour, green_weight, mold_id, total_tobe, plan, plan_weight, black, nm, prod, loss, remark)
        VALUES (:press_name, :icode, :description, :rim, :brand, :type, :colour, :green_weight, :mold_id, :total_tobe, :plan, :plan_weight, :black, :nm, :prod, :loss, :remark)
    ");

    // Variables to store the total plan and unique cavity_ids
    $totalPlan = 0; 
    $uniqueCavityIds = [];

    foreach ($result as $row) {
        if ($row['plan'] == 0) {
            continue; // Skip rows where the plan value is 0
        }

        // Determine the remark value
        $remark = '';
        if ($row['new_process_start_date'] && $row['erp']) {
            $remark = 'tobe started';
        }

        // Calculate Plan * Green Tire Weight
        $planGreenTireWeight = ($row['plan'] * $row['grand totalcompound weight']);

        // Bind the parameters and execute the insert statement
        $insertStmt->execute([
            ':press_name' => $row['press_name'],
            ':icode' => $row['icode'],
            ':description' => $row['description'],
            ':rim' => $row['rim'],
            ':brand' => $row['brand'],
            ':type' => $row['type'],
            ':colour' => $row['colour'],
            ':green_weight' => $row['grand totalcompound weight'],
            ':mold_id' => $row['mold_id'],
            ':total_tobe' => $row['total_tobe'],
            ':plan' => $row['plan'],
            ':plan_weight' => $planGreenTireWeight,
            ':black' => '',
            ':nm' => '',
            ':prod' => '',
            ':loss' => '',
            ':remark' => $remark
        ]);

        // Add the plan value to the total
        $totalPlan += ($row['plan']);

        // Track unique cavity_ids
        $uniqueCavityIds[$row['cavity_id']] = true;
    }

    // Fetch data from the cal_report table after insertion
    $fetchStmt = $pdo->prepare("SELECT * FROM cal_report");
    $fetchStmt->execute();
    $reportData = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);

    // Create a new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add headers to the sheet
    $headers = ['Press Name', 'Item Code', 'Description', 'Rim', 'Brand', 'Type', 'Color', 'Green Weight', 'Mold ID', 'Total ToBe', 'Plan', 'Plan Weight', 'Black', 'NM', 'prod', 'loss', 'Remark'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Add data to the sheet
    $rowNumber = 2;
    foreach ($reportData as $row) {
        $sheet->setCellValue('A' . $rowNumber, $row['press_name']);
        $sheet->setCellValue('B' . $rowNumber, $row['icode']);
        $sheet->setCellValue('C' . $rowNumber, $row['description']);
        $sheet->setCellValue('D' . $rowNumber, $row['rim']);
        $sheet->setCellValue('E' . $rowNumber, $row['brand']);
        $sheet->setCellValue('F' . $rowNumber, $row['type']);
        $sheet->setCellValue('G' . $rowNumber, $row['colour']);
        $sheet->setCellValue('H' . $rowNumber, $row['green_weight']);
        $sheet->setCellValue('I' . $rowNumber, $row['mold_id']);
        $sheet->setCellValue('J' . $rowNumber, $row['total_tobe']);
        $sheet->setCellValue('K' . $rowNumber, $row['plan']);
        $sheet->setCellValue('L' . $rowNumber, $row['plan_weight']);
        $sheet->setCellValue('M' . $rowNumber, $row['black']);
        $sheet->setCellValue('N' . $rowNumber, $row['nm']);
        $sheet->setCellValue('O' . $rowNumber, $row['prod']);
        $sheet->setCellValue('P' . $rowNumber, $row['loss']);
        $sheet->setCellValue('Q' . $rowNumber, $row['remark']);

        $rowNumber++;
    }

    // Add total row
    $sheet->setCellValue('J' . $rowNumber, 'Total');
    $sheet->setCellValue('K' . $rowNumber, $totalPlan);

    // Add unique cavity IDs count
    $sheet->setCellValue('J' . ($rowNumber + 1), 'Unique Cavity IDs Count');
    $sheet->setCellValue('K' . ($rowNumber + 1), count($uniqueCavityIds));

    // Save spreadsheet to a file
    $file_name = 'cal_report_' . date('Ymd_His') . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($file_name);

    // Redirect to download the generated file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $file_name . '"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    readfile($file_name);
    unlink($file_name); // Delete the file after download

    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$pdo = null;
?>
