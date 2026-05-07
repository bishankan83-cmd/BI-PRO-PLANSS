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

    // SQL query to select data based on Date range, joining with tire_details and ordering by id
    $sql = "SELECT dpd.*, td.greenweight AS TireWeight, td.stgreenweight, td.description AS TireDescription
            FROM daily_plan_data dpd
            LEFT JOIN tire_details td ON dpd.Icode = td.icode
            WHERE dpd.Date BETWEEN '$startDate' AND '$endDate'
            ORDER BY dpd.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Create a new PhpSpreadsheet object
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers to the Excel sheet and increase font size
        $headerFontStyle = [
            'font' => [
                'bold' => true,
            ],
        ];
        $sheet->setCellValue('A1', 'Date')->getStyle('A1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('B1', 'Shift')->getStyle('B1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('C1', 'Press')->getStyle('C1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('D1', 'Tire Code')->getStyle('D1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('E1', 'Tire Description')->getStyle('E1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('F1', 'Plan Tires')->getStyle('F1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('G1', 'Actual Tires')->getStyle('G1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('H1', 'Loss Tires')->getStyle('H1')->applyFromArray($headerFontStyle); // New column for Loss Tires
        $sheet->setCellValue('I1', 'LossReason')->getStyle('I1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('J1', 'Root cause for loss')->getStyle('J1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('K1', 'Per Tire Compound Weight')->getStyle('K1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('L1', 'Per Tire Weight with Steel')->getStyle('L1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('M1', 'Total Plan Compound Weight')->getStyle('M1')->applyFromArray($headerFontStyle); // New column for Total Plan Compound Weight
        $sheet->setCellValue('N1', 'Total Plan Steel Weight')->getStyle('N1')->applyFromArray($headerFontStyle); // New column for Total Plan Steel Weight
        $sheet->setCellValue('O1', 'Total Actual Compound Weight')->getStyle('O1')->applyFromArray($headerFontStyle);
        $sheet->setCellValue('P1', 'Total Actual Steel Weight')->getStyle('P1')->applyFromArray($headerFontStyle);

        // Initialize array to store shift data
        $shiftData = array();

        // Fetch data from the result set and add it to the shiftData array
        while ($row = $result->fetch_assoc()) {
            // Check if the shift name is not "0" before adding the data
            if ($row["Shift"] !== '0') {
                // Convert string values to numbers to prevent type errors
                $row["Plan"] = floatval($row["Plan"]);
                $row["AdditionalData"] = floatval($row["AdditionalData"]);
                $row["TireWeight"] = floatval($row["TireWeight"]);
                $row["stgreenweight"] = floatval($row["stgreenweight"]);
                
                // Handle multiple loss reasons where Actual Tires are set as Plan Tires
                if ($row["LossReason"] === "Not Matching The Unloading time" || 
                    $row["LossReason"] === "Over Production" ||
                    $row["LossReason"] === "Planning Stop" ||
                    $row["LossReason"] === "Black Tire Prodution") {
                    // In these cases, set Actual Tires as Plan Tires
                    $row["Plan"] = $row["AdditionalData"];
                }
                // Add data to shiftData array
                $shiftName = $row["Shift"];
                if (!isset($shiftData[$shiftName])) {
                    $shiftData[$shiftName] = array();
                }
                $shiftData[$shiftName][] = $row;
            }
        }

        // Rearrange the data and calculate shift totals
        $rowIndex = 2;
        foreach ($shiftData as $shift => $data) {
            // Add data for the current shift
            foreach ($data as $row) {
                $sheet->setCellValue('A' . $rowIndex, $row["Date"]);
                $sheet->setCellValue('B' . $rowIndex, $row["Shift"]);
                $sheet->setCellValue('C' . $rowIndex, $row["CavityName"]);
                $sheet->setCellValue('D' . $rowIndex, $row["Icode"]);
                $sheet->setCellValue('E' . $rowIndex, $row["TireDescription"]);
                $sheet->setCellValue('F' . $rowIndex, $row["Plan"]);
                $sheet->setCellValue('G' . $rowIndex, $row["AdditionalData"]);
                // Calculate Loss Tires and ensure it's positive
                $lossTires = max(0, $row["Plan"] - $row["AdditionalData"]);
                $sheet->setCellValue('H' . $rowIndex, $lossTires);
                $sheet->setCellValue('I' . $rowIndex, $row["LossReason"]);
                $sheet->setCellValue('J' . $rowIndex, $row["Remark"]);
                $sheet->setCellValue('K' . $rowIndex, $row["TireWeight"]);
                $sheet->setCellValue('L' . $rowIndex, $row["stgreenweight"]);

                $sheet->setCellValue('M' . $rowIndex, $row["Plan"] * $row["TireWeight"]);
                $sheet->setCellValue('N' . $rowIndex, $row["Plan"] * $row["stgreenweight"]);

                $sheet->setCellValue('O' . $rowIndex, $row["AdditionalData"] * $row["TireWeight"]);
                $sheet->setCellValue('P' . $rowIndex, $row["AdditionalData"] * $row["stgreenweight"]);
                $rowIndex++;
            }

            // Add shift totals for the current shift and make them bold
            $shiftRowIndex = $rowIndex + 1;
            $sheet->setCellValue('B' . $shiftRowIndex, $shift . ' Total')->getStyle('B' . $shiftRowIndex)->getFont()->setBold(true);
            $sheet->setCellValue('F' . $shiftRowIndex, array_sum(array_column($data, "Plan")))->getStyle('F' . $shiftRowIndex)->getFont()->setBold(true);
            $sheet->setCellValue('G' . $shiftRowIndex, array_sum(array_column($data, "AdditionalData")))->getStyle('G' . $shiftRowIndex)->getFont()->setBold(true);
            $sheet->setCellValue('H' . $shiftRowIndex, array_sum(array_map(function($row) {
                return max(0, $row["Plan"] - $row["AdditionalData"]);
            }, $data)))->getStyle('H' . $shiftRowIndex)->getFont()->setBold(true);
            $sheet->setCellValue('M' . $shiftRowIndex, array_sum(array_map(function($row) {
                return $row["Plan"] * $row["TireWeight"];
            }, $data)))->getStyle('M' . $shiftRowIndex)->getFont()->setBold(true);
            $sheet->setCellValue('N' . $shiftRowIndex, array_sum(array_map(function($row) {
                return $row["Plan"] * $row["stgreenweight"];
            }, $data)))->getStyle('N' . $shiftRowIndex)->getFont()->setBold(true);
            $sheet->setCellValue('O' . $shiftRowIndex, array_sum(array_map(function($row) {
                return $row["AdditionalData"] * $row["TireWeight"];
            }, $data)))->getStyle('O' . $shiftRowIndex)->getFont()->setBold(true);
            $sheet->setCellValue('P' . $shiftRowIndex, array_sum(array_map(function($row) {
                return $row["AdditionalData"] * $row["stgreenweight"];
            }, $data)))->getStyle('P' . $shiftRowIndex)->getFont()->setBold(true);
            $rowIndex = $shiftRowIndex + 1;
        }

        // Initialize variables to store totals
        $totalPlanTires = $totalActualTires = $totalLossTires = $totalPlanCompoundWeight = $totalPlanSteelWeight = $totalActualCompoundWeight = $totalActualSteelWeight = 0;

        // Calculate totals
        foreach ($shiftData as $shift => $data) {
            $totalPlanTires += array_sum(array_column($data, "Plan"));
            $totalActualTires += array_sum(array_column($data, "AdditionalData"));
            $totalLossTires += array_sum(array_map(function($row) {
                return max(0, $row["Plan"] - $row["AdditionalData"]);
            }, $data));
            $totalPlanCompoundWeight += array_sum(array_map(function($row) {
                return $row["Plan"] * $row["TireWeight"];
            }, $data));
            $totalPlanSteelWeight += array_sum(array_map(function($row) {
                return $row["Plan"] * $row["stgreenweight"];
            }, $data));
            $totalActualCompoundWeight += array_sum(array_map(function($row) {
                return $row["AdditionalData"] * $row["TireWeight"];
            }, $data));
            $totalActualSteelWeight += array_sum(array_map(function($row) {
                return $row["AdditionalData"] * $row["stgreenweight"];
            }, $data));
        }

        // Add totals to the Excel sheet
        $sheet->setCellValue('B' . ($rowIndex + 1), 'Grand Total')->getStyle('B' . ($rowIndex + 1))->getFont()->setBold(true);
        $sheet->setCellValue('F' . ($rowIndex + 1), $totalPlanTires)->getStyle('F' . ($rowIndex + 1))->getFont()->setBold(true);
        $sheet->setCellValue('G' . ($rowIndex + 1), $totalActualTires)->getStyle('G' . ($rowIndex + 1))->getFont()->setBold(true);
        $sheet->setCellValue('H' . ($rowIndex + 1), $totalLossTires)->getStyle('H' . ($rowIndex + 1))->getFont()->setBold(true);
        $sheet->setCellValue('M' . ($rowIndex + 1), $totalPlanCompoundWeight)->getStyle('M' . ($rowIndex + 1))->getFont()->setBold(true);
        $sheet->setCellValue('N' . ($rowIndex + 1), $totalPlanSteelWeight)->getStyle('N' . ($rowIndex + 1))->getFont()->setBold(true);
        $sheet->setCellValue('O' . ($rowIndex + 1), $totalActualCompoundWeight)->getStyle('O' . ($rowIndex + 1))->getFont()->setBold(true);
        $sheet->setCellValue('P' . ($rowIndex + 1), $totalActualSteelWeight)->getStyle('P' . ($rowIndex + 1))->getFont()->setBold(true);

        // Create a new Excel writer and save the file
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Daily_Production_Details_' . date('Y-m-d', strtotime($startDate)) . '_to_' . date('Y-m-d', strtotime($endDate)) . '.xlsx';

        $writer->save($filename);
        // Provide a download link for the generated Excel file with a red button
        echo '
        <style>
            .download-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #FF0000;
                color: #FFFFFF;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                transition: background-color 0.3s ease;
            }

            .download-button:hover {
                background-color: #CC0000; /* Darker red on hover */
            }
        </style>

        <p>successfull <a href="' . $filename . '" class="download-button">Download Excel</a></p>';

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
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h5 {
            font-weight: bold;
            font-size: 24px;
            font-family: 'Cantarell Bold', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        .alert {
            background-color: #FFD700;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="datetime-local"],
        input[type="text"],
        input[type="date"] {
            width: 91%;
            padding: 10px;
            border: 1px solid #000000;
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

<body>

    <div class="container">
        <h5>Export Excel Production Date Range</h5>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Export Excel">
            </div>
        </form>
    </div>

</body>

</html>