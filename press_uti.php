<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter and Export to Excel</title>
</head>
<body>
    <h1>Filter Data</h1>
    <form method="POST" action="">
        <label for="cavityName">Press Name:</label>
        <select name="cavityName[]" id="cavityName" multiple>
            <?php
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

            // Fetch cavity names from the database
            $cavityQuery = "SELECT DISTINCT CavityName FROM daily_plan_data";
            $cavityResult = $conn->query($cavityQuery);
            while ($row = $cavityResult->fetch_assoc()) {
                echo "<option value=\"" . htmlspecialchars($row['CavityName']) . "\">" . htmlspecialchars($row['CavityName']) . "</option>";
            }
            ?>
        </select><br>

        <label for="startDate">Start Date:</label>
        <input type="date" name="startDate" id="startDate"><br>

        <label for="endDate">End Date:</label>
        <input type="date" name="endDate" id="endDate"><br>

        <button type="submit" name="filter">Filter Data</button>
        <button type="submit" name="export">Export to Excel</button>
    </form>

    <?php
    require 'vendor/autoload.php'; // Load the PhpSpreadsheet library

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Initialize variables
    $cavityNames = $_POST['cavityName'] ?? [];
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';
    $results = [];

    // Handle filtering and exporting
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        if (!empty($cavityNames)) {
            // Prepare placeholders for the IN clause
            $placeholders = implode(',', array_fill(0, count($cavityNames), '?'));
            $sql .= " AND d.CavityName IN ($placeholders)";
            $params = array_merge($params, $cavityNames);
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
            $stmt->bind_param(str_repeat('s', count($params)), ...$params); // Bind parameters based on types
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch results
        if (isset($_POST['filter'])) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row; // Store the results in an array
            }
        }

        // Export to Excel if the button is pressed
        if (isset($_POST['export'])) {
            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set the headers for the spreadsheet
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Date');
            $sheet->setCellValue('C1', 'Shift');
            $sheet->setCellValue('D1', 'Icode');
            $sheet->setCellValue('E1', 'Description');
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
                $sheet->setCellValue("E$rowCount", $row['description']);
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
            
            // Clean the output buffer
            ob_end_clean(); 
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$excelFileName\"");
            header('Cache-Control: max-age=0');
            header('Pragma: public'); // Necessary for older browsers

            // Save the file to the output
            $writer->save('php://output');
            exit; // Stop further script execution after export
        }

        // Close the statement
        $stmt->close();
    }
    ?>

    <?php if (!empty($results)): ?>
        <h2>Filtered Results</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Shift</th>
                <th>Icode</th>
                <th>Description</th>
                <th>Mold Name</th>
                <th>Cavity Name</th>
                <th>Plan</th>
                <th>Additional Data</th>
                <th>Loss Reason</th>
                <th>Remark</th>
                <th>Green Weight</th>
                <th>Calculated Weight</th>
            </tr>
            <?php foreach ($results as $row): 
                // Calculate the weight here to show in the table as well
                $stgreenweight = (float)$row['stgreenweight'];
                $additionalData = (float)$row['AdditionalData'];
                $calculatedWeight = $stgreenweight * $additionalData; // Calculate the multiplied value
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['ID']) ?></td>
                    <td><?= htmlspecialchars($row['Date']) ?></td>
                    <td><?= htmlspecialchars($row['Shift']) ?></td>
                    <td><?= htmlspecialchars($row['Icode']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['MoldName']) ?></td>
                    <td><?= htmlspecialchars($row['CavityName']) ?></td>
                    <td><?= htmlspecialchars($row['Plan']) ?></td>
                    <td><?= htmlspecialchars($row['AdditionalData']) ?></td>
                    <td><?= htmlspecialchars($row['LossReason']) ?></td>
                    <td><?= htmlspecialchars($row['Remark']) ?></td>
                    <td><?= htmlspecialchars($row['stgreenweight']) ?></td>
                    <td><?= htmlspecialchars($calculatedWeight) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>
</html>
