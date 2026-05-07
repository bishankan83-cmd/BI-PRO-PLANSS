<?php
require 'vendor/autoload.php'; // Include the Composer autoloader for PhpSpreadsheet

$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$holidaysQuery = "SELECT holiday_date FROM holidays";
$holidaysResult = $conn->query($holidaysQuery);

$holidays = [];
while ($holidayRow = $holidaysResult->fetch_assoc()) {
    $holidays[] = $holidayRow['holiday_date'];
}

$sql = "SELECT date,
               SUM(plan) AS total_data_plan_amount,
               COUNT(DISTINCT cavity_id) AS unique_cavity_id_quantity,
               SUM(calculated_green_tire_weight) AS total_green_tire_weight
        FROM calculated_data";

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
    $sql .= " WHERE date BETWEEN '$startDate' AND '$endDate'";

    if (!empty($holidays)) {
        $sql .= " AND date NOT IN ('" . implode("','", $holidays) . "')";
    }
} else {
    if (!empty($holidays)) {
        $sql .= " WHERE date NOT IN ('" . implode("','", $holidays) . "')";
    }
}

$sql .= " GROUP BY date";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Create a new Spreadsheet object
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set the column headers
    $sheet->setCellValue('A1', 'Date');
    $sheet->setCellValue('B1', 'Total Plan tires Nos');
    $sheet->setCellValue('C1', 'Utilized/Plan Cavity Nos');
    $sheet->setCellValue('D1', 'Total Cavity');
    $sheet->setCellValue('E1', 'Average Utilization (%)');
    $sheet->setCellValue('F1', 'Total Green Tire Weight');

    $rowNumber = 2;
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNumber, $row["date"]);
        $sheet->setCellValue('B' . $rowNumber, $row["total_data_plan_amount"]);
        $sheet->setCellValue('C' . $rowNumber, $row["unique_cavity_id_quantity"]);

        $totalCavity = 130;
        $sheet->setCellValue('D' . $rowNumber, $totalCavity);

        $percentage = ($row["unique_cavity_id_quantity"] / $totalCavity) * 100;
        $sheet->setCellValue('E' . $rowNumber, number_format($percentage, 2) . "%");

        $sheet->setCellValue('F' . $rowNumber, $row["total_green_tire_weight"]);
        $rowNumber++;
    }

    // Save the spreadsheet to a file
    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $excelFileName = 'exported_data.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $excelFileName . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit();
} else {
    echo "No results found.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Data Display</title>
    <!-- Include any additional CSS styles if needed -->
</head>
<body>
    <div>
        <!-- Your existing HTML content goes here -->
    </div>
</body>
</html>
