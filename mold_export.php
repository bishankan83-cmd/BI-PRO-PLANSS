<?php
require 'vendor/autoload.php'; // Include the PHPSpreadsheet library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Replace these variables with your actual database connection details
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Collect the user-provided date from the form
$user_date = $_POST['start_date'];

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();

// Create a new worksheet
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'icode');
$sheet->setCellValue('B1', 'Description');
$sheet->setCellValue('C1', 'Mold Name');
$sheet->setCellValue('D1', 'Cavity Name');
$sheet->setCellValue('E1', 'Start Date');
$sheet->setCellValue('F1', 'End Date');
$sheet->setCellValue('G1', 'Removing Mold');
$sheet->setCellValue('H1', 'Removing Tire Id');
$sheet->setCellValue('I1', 'Removing Tire Description');

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to retrieve data based on the user-provided date (ignoring time)
$sql = "SELECT plannew.icode, plannew.mold_id, plannew.cavity_id, plannew.start_date, plannew.end_date, tire.description, mold.mold_name, cavity.cavity_name
        FROM plannew
        LEFT JOIN tire ON plannew.icode = tire.icode
        LEFT JOIN mold ON plannew.mold_id = mold.mold_id
        LEFT JOIN cavity ON plannew.cavity_id = cavity.cavity_id
        WHERE DATE(plannew.start_date) = '$user_date'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $rowNumber = 2; // Start from row 2

    while ($row = $result->fetch_assoc()) {
        // Write data to the worksheet
        $sheet->setCellValue('A' . $rowNumber, $row["icode"]);
        $sheet->setCellValue('B' . $rowNumber, $row["description"]);
        $sheet->setCellValue('C' . $rowNumber, $row["mold_name"]);
        $sheet->setCellValue('D' . $rowNumber, $row["cavity_name"]);
        $sheet->setCellValue('E' . $rowNumber, $row["start_date"]);
        $sheet->setCellValue('F' . $rowNumber, $row["end_date"]);

        // Fetch additional data related to removing mold and tire
        $cavity_id = $row["cavity_id"];
        $cavityQuery = "SELECT mold.mold_name AS removing_mold_name, plannew.icode AS removing_tire_id, tire.description AS removing_tire_description FROM plannew
                        LEFT JOIN mold ON plannew.mold_id = mold.mold_id
                        LEFT JOIN tire ON plannew.icode = tire.icode
                        WHERE plannew.cavity_id = '$cavity_id' AND DATE(plannew.end_date) = '$user_date'";
        $cavityResult = $conn->query($cavityQuery);

        if ($cavityResult->num_rows > 0) {
            $cavityRow = $cavityResult->fetch_assoc();
            $sheet->setCellValue('G' . $rowNumber, $cavityRow["removing_mold_name"]);
            $sheet->setCellValue('H' . $rowNumber, $cavityRow["removing_tire_id"]);
            $sheet->setCellValue('I' . $rowNumber, $cavityRow["removing_tire_description"]);
        } else {
            $sheet->setCellValue('G' . $rowNumber, 'No data');
            $sheet->setCellValue('H' . $rowNumber, 'No data');
            $sheet->setCellValue('I' . $rowNumber, 'No description');
        }

        $rowNumber++;
    }

    // Freeze the first row
    $sheet->freezePane('A2');

    // Auto size columns
    foreach (range('A', 'I') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Save the Excel file
    $writer = new Xlsx($spreadsheet);
    $excelFileName = 'exported_data_' . $user_date . '.xlsx'; // File name with date
    $writer->save($excelFileName);

    // Provide download link
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $excelFileName . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} else {
    echo "No data found in the plannew table.";
}

// Close the database connection
$conn->close();
?>
