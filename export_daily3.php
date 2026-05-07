<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet autoload file

// Database connection parameters (same as your existing code)
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Initialize variables for date and shift
$startDate = $endDate = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get start date, end date, and shift from the form
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Create a connection to the MySQL database
    $conn = new mysqli($servername, $username, $password, $database);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to select data based on Date range, joining with tire_details
    $sql = "SELECT dpd.*, td.stgreenweight AS TireWeight 
            FROM daily_plan_data dpd
            LEFT JOIN tire_details td ON dpd.Icode = td.icode
            WHERE dpd.Date BETWEEN '$startDate' AND '$endDate'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Create a new PhpSpreadsheet object
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers to the Excel sheet
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Shift');
        $sheet->setCellValue('C1', 'Icode');
        $sheet->setCellValue('D1', 'Plan');
        $sheet->setCellValue('E1', 'Actual');
        $sheet->setCellValue('F1', 'LossReason');
        $sheet->setCellValue('G1', 'Remark');
        $sheet->setCellValue('H1', 'TireWeight'); // TireWeight from tire_details
        $sheet->setCellValue('I1', 'TotalGWeight'); // New header for TotalGWeight

        // Fetch data from the result set and add it to the Excel sheet
        $rowIndex = 2; // Start from row 2 to leave room for headers
        while ($row = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $rowIndex, $row["Date"]);
            $sheet->setCellValue('B' . $rowIndex, $row["Shift"]);
            $sheet->setCellValue('C' . $rowIndex, $row["Icode"]);
            $sheet->setCellValue('D' . $rowIndex, $row["Plan"]);
            $sheet->setCellValue('E' . $rowIndex, $row["AdditionalData"]);
            $sheet->setCellValue('F' . $rowIndex, $row["LossReason"]);
            $sheet->setCellValue('G' . $rowIndex, $row["Remark"]);
            $sheet->setCellValue('H' . $rowIndex, $row["TireWeight"]); // TireWeight from tire_details
            
            // Calculate TotalGWeight and add it to the Excel sheet
            $totalGWeight = $row["TireWeight"] * $row["AdditionalData"];
            $sheet->setCellValue('I' . $rowIndex, $totalGWeight);

            $rowIndex++;
        }

        // Create a new Excel writer and save the file
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'daily_production Cweight.xlsx';
        $writer->save($filename);

        // Provide a download link for the generated Excel file
        echo '<p>Data has been exported to Excel. <a href="' . $filename . '">Download Excel</a></p>';
    } else {
        echo "0 results";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <style>
        /* Your existing styles */
    </style>
</head>

<body>

    <!-- Form for input fields -->
    <div class="container">
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>" required>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>" required>
            <input type="submit" value="Retrieve Data">
        </form>
    </div>

</body>

</html>
