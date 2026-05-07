<?php
require 'vendor/autoload.php'; // Load the PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters from the POST request
$cavityName = $_POST['cavityName'] ?? '';
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';

// Prepare the SQL statement
$sql = "
    SELECT d.ID, d.Date, d.Shift, d.Icode, d.MoldName, d.CavityName, d.Plan, d.AdditionalData, d.LossReason, d.Remark,
           t.description, t.stgreenweight
    FROM daily_plan_data d
    LEFT JOIN tire_details t ON d.Icode = t.Icode
    WHERE 1=1"; // Base condition for dynamic query construction

// Initialize an array for the parameters
$params = [];

// Apply filters based on user input
if (!empty($cavityName)) {
    $sql .= " AND d.CavityName = ?";
    $params[] = $cavityName;
}

if (!empty($startDate)) {
    $sql .= " AND d.Date >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $sql .= " AND d.Date <= ?";
    $params[] = $endDate;
}

// Prepare and execute the SQL statement
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params); // bind parameters based on types
}
$stmt->execute();
$result = $stmt->get_result();

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the headers for the spreadsheet
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Date');
$sheet->setCellValue('C1', 'Shift');
$sheet->setCellValue('D1', 'Icode');
$sheet->setCellValue('E1', 'Description'); // Added Description here
$sheet->setCellValue('F1', 'Mold Name');
$sheet->setCellValue('G1', 'Cavity Name');
$sheet->setCellValue('H1', 'Plan');
$sheet->setCellValue('I1', 'Additional Data');
$sheet->setCellValue('J1', 'Loss Reason');
$sheet->setCellValue('K1', 'Remark');
$sheet->setCellValue('L1', 'Green Weight');
$sheet->setCellValue('M1', 'Calculated Weight');

// Output the data to the spreadsheet
$rowCount = 2; // Start from the second row
while ($row = $result->fetch_assoc()) {
    $stgreenweight = (float)$row['stgreenweight'];
    $additionalData = (float)$row['AdditionalData'];
    $calculatedWeight = $stgreenweight * $additionalData; // Calculate the multiplied value

    $sheet->setCellValue("A$rowCount", $row['ID']);
    $sheet->setCellValue("B$rowCount", $row['Date']);
    $sheet->setCellValue("C$rowCount", $row['Shift']);
    $sheet->setCellValue("D$rowCount", $row['Icode']);
    $sheet->setCellValue("E$rowCount", $row['description']); // Set Description here
    $sheet->setCellValue("F$rowCount", $row['MoldName']);
    $sheet->setCellValue("G$rowCount", $row['CavityName']);
    $sheet->setCellValue("H$rowCount", $row['Plan']);
    $sheet->setCellValue("I$rowCount", $row['AdditionalData']);
    $sheet->setCellValue("J$rowCount", $row['LossReason']);
    $sheet->setCellValue("K$rowCount", $row['Remark']);
    $sheet->setCellValue("L$rowCount", $row['stgreenweight']);
    $sheet->setCellValue("M$rowCount", $calculatedWeight);

    $rowCount++;
}

// Set the Excel file name
$excelFileName = 'filtered_data.xlsx';

// Write the file to output
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$excelFileName\"");
header('Cache-Control: max-age=0');

// Save the file to the output
$writer->save('php://output');
exit; // Stop further script execution after export

// Close the database connection
$stmt->close();
$conn->close();
?>
