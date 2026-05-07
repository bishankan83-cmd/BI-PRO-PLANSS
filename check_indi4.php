<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT md.icode, md.id_count, t.description, cv.cavity_name, ml.mold_name, pc.press_id, p.press_name, md.end_date, md.start_time
        FROM merged_data md
        LEFT JOIN cavity cv ON md.cavity_id = cv.cavity_id
        LEFT JOIN mold ml ON md.mold_id = ml.mold_id
        LEFT JOIN press_cavity pc ON md.cavity_id = pc.cavity_id
        LEFT JOIN press p ON pc.press_id = p.press_id
        LEFT JOIN tire t ON md.icode = t.icode"; // Adjust the join condition as needed



$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'icode');
    $sheet->setCellValue('B1', 'Description');
    $sheet->setCellValue('C1', 'Cavity name');
    $sheet->setCellValue('D1', 'Mold name');
    $sheet->setCellValue('E1', 'Press Name');
    $sheet->setCellValue('F1', 'To be');
    $sheet->setCellValue('G1', 'Start Time');
    $sheet->setCellValue('H1', 'End Time');
    $sheet->setCellValue('I1', 'Total To Be');

    $rowNumber = 2; // Start from the second row for data

    while ($row = $result->fetch_assoc()) {
        // Make sure 'description' key exists in $row before accessing it
        $description = isset($row['description']) ? $row['description'] : '';
// Calculate total to be using the function
$total_tobe = calculateTotalTobe($conn, $row['icode']);
        // Set cell values
        $sheet->setCellValue('A' . $rowNumber, $row['icode']);
        $sheet->setCellValue('B' . $rowNumber, $description);
        $sheet->setCellValue('C' . $rowNumber, $row['cavity_name']);
        $sheet->setCellValue('D' . $rowNumber, $row['mold_name']);
        $sheet->setCellValue('E' . $rowNumber, $row['press_name']);
        $sheet->setCellValue('F' . $rowNumber, $row['id_count']);
        $sheet->setCellValue('G' . $rowNumber, $row['start_time']);
        $sheet->setCellValue('H' . $rowNumber, $row['end_date']);
 // Set other cell values...
 $sheet->setCellValue('I' . $rowNumber, $total_tobe); // Set total to be value
        $rowNumber++; // Move to the next row
    }

    // Create Excel writer and save the file
    $writer = new Xlsx($spreadsheet);
    $excelFileName = 'exported_data.xlsx';
    $writer->save($excelFileName);

    // Download the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $excelFileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
}

$conn->close();

function calculateTotalTobe($conn, $icode) {
    $sql = "SELECT icode,SUM(tobe) AS total_tobe
            FROM tobeplan1
            WHERE icode = ? AND tobe > 0 
            GROUP BY icode";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $icode);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_tobe = 0;
    if ($row = $result->fetch_assoc()) {
        $total_tobe = $row['total_tobe'];
    }

    $stmt->close();

    return $total_tobe;
}

?>
