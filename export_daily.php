<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet autoload file

// Database connection parameters (same as your existing code)
$servername = "localhost:3306";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Initialize variables for date and shift
$date = $shift = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get date and shift from the form
    $date = $_POST['date'];
    $shift = $_POST['shift'];

    // Create a connection to the MySQL database
    $conn = new mysqli($servername, $username, $password, $database);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to select data based on Date and Shift, joining with tire_details
    $sql = "SELECT dpd.*, td.greenweight AS TireWeight 
            FROM daily_plan_data dpd
            LEFT JOIN tire_details td ON dpd.Icode = td.icode
            WHERE dpd.Date='$date' AND dpd.Shift='$shift'";
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
        $filename = 'exported_data.xlsx';
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
            <label for="date">Date:</label>
            <input type="date" name="date" id="date" value="<?php echo $date; ?>" required>
            <label for="shift">Shift:</label>
            <select name="shift" id="shift">
                <option value="DAY A" <?php echo ($shift == 'DAY A') ? 'selected' : ''; ?>>DAY A</option>
                <option value="DAY B" <?php echo ($shift == 'DAY B') ? 'selected' : ''; ?>>DAY B</option>
                <option value="DAY C" <?php echo ($shift == 'DAY C') ? 'selected' : ''; ?>>DAY C</option>
                <option value="NIGHT A" <?php echo ($shift == 'NIGHT A') ? 'selected' : ''; ?>>NIGHT A</option>
                <option value="NIGHT B" <?php echo ($shift == 'NIGHT B') ? 'selected' : ''; ?>>NIGHT B</option>
                <option value="NIGHT C" <?php echo ($shift == 'NIGHT C') ? 'selected' : ''; ?>>NIGHT C</option>
            </select>
            <input type="submit" value="Retrieve Data">
        </form>
    </div>

</body>

</html>
