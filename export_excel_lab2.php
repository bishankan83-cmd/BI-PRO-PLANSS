<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Include PhpSpreadsheet
require 'vendor/autoload.php'; // Make sure this points to the correct path where Composer's autoload file is

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create a PDO connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Initialize variables for filtering
$start_date = $end_date = $compound_name = $serial_number = '';
$records = [];

// Check if the form is submitted and the date range is provided
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the date range, compound name, and serial number
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $compound_name = $_POST['compound_name'];
    $serial_number = $_POST['serial_number'];

    // Prepare SQL query based on the selected filters
    try {
        if (!empty($compound_name)) {
            // Filter by compound_name and date range
            $sql = "SELECT * FROM another_table_name1 
                    WHERE inputDate BETWEEN :start_date AND :end_date 
                    AND compound_name = :compound_name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'start_date' => $start_date,
                'end_date' => $end_date,
                'compound_name' => $compound_name
            ]);
        } elseif (!empty($serial_number)) {
            // Filter by serial_number and date range
            $sql = "SELECT * FROM another_table_name1 
                    WHERE inputDate BETWEEN :start_date AND :end_date 
                    AND serial_number = :serial_number";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'start_date' => $start_date,
                'end_date' => $end_date,
                'serial_number' => $serial_number
            ]);
        } else {
            // If no compound name or serial number selected, just filter by date range
            $sql = "SELECT * FROM another_table_name1 
                    WHERE inputDate BETWEEN :start_date AND :end_date";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
        }

        // Fetch the filtered records
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Query failed: " . $e->getMessage();
        exit();
    }
}

// Get distinct compound names and serial numbers for the dropdown based on the date range
try {
    $compound_sql = "SELECT DISTINCT compound_name 
                     FROM another_table_name1 
                     WHERE inputDate BETWEEN :start_date AND :end_date";
    $compound_stmt = $pdo->prepare($compound_sql);
    $compound_stmt->execute([
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
    $compound_names = $compound_stmt->fetchAll(PDO::FETCH_ASSOC);

    $serial_sql = "SELECT DISTINCT serial_number 
                   FROM another_table_name1 
                   WHERE inputDate BETWEEN :start_date AND :end_date";
    $serial_stmt = $pdo->prepare($serial_sql);
    $serial_stmt->execute([
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
    $serial_numbers = $serial_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching dropdown options: " . $e->getMessage();
    exit();
}

// Handle export to Excel
if (isset($_POST['export'])) {
    try {
        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the header for the Excel file
        $sheet->setCellValue('A1', 'Input Date')
        ->setCellValue('B1', 'Serial Number')
              
            
              ->setCellValue('C1', 'Batch')
              
              ->setCellValue('D1', 'MH')
              ->setCellValue('E1', 'ML')
              ->setCellValue('F1', 'T10')
              ->setCellValue('G1', 'T90')
             
              ->setCellValue('H1', 'T52')
              ->setCellValue('I1', 'Hardness')
              ->setCellValue('J1', 'SG Value')
              
              ->setCellValue('K1', 'Rebound');

        // Fill the data rows
        $rowNum = 2;
        foreach ($records as $row) {
            $sheet ->setCellValue('A' . $rowNum, $row['inputDate'])
            
            ->setCellValue('B' . $rowNum, $row['serial_number'])
                 
                
                  ->setCellValue('C' . $rowNum, $row['batch'])
                  
                 
                  ->setCellValue('D' . $rowNum, $row['mh'])
                  ->setCellValue('E' . $rowNum, $row['ml'])
                  ->setCellValue('F' . $rowNum, $row['t10'])
                  ->setCellValue('G' . $rowNum, $row['t90'])
                  
                  ->setCellValue('H' . $rowNum, $row['T52'])
                  ->setCellValue('I' . $rowNum, $row['hardness'])
                  ->setCellValue('J' . $rowNum, $row['sg_value'])
                  ->setCellValue('K' . $rowNum, $row['rebound']);
            $rowNum++;
        }

        // Write the file to the browser
        $writer = new Xlsx($spreadsheet);
        $filename = "filtered_data.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer->save('php://output');
        exit();
    } catch (Exception $e) {
        echo "Error exporting to Excel: " . $e->getMessage();
        exit();
    }
}
?>

<!-- HTML Form to accept date range and selection filters -->
<form method="post">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
    
    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
    
    <label for="compound_name">Compound Name:</label>
    <select name="compound_name" id="compound_name">
        <option value="">Select Compound Name (Optional)</option>
        <?php foreach ($compound_names as $compound) : ?>
            <option value="<?= htmlspecialchars($compound['compound_name']) ?>" <?= $compound['compound_name'] == $compound_name ? 'selected' : '' ?>>
                <?= htmlspecialchars($compound['compound_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="serial_number">Serial Number:</label>
    <select name="serial_number" id="serial_number">
        <option value="">Select Serial Number (Optional)</option>
        <?php foreach ($serial_numbers as $serial) : ?>
            <option value="<?= htmlspecialchars($serial['serial_number']) ?>" <?= $serial['serial_number'] == $serial_number ? 'selected' : '' ?>>
                <?= htmlspecialchars($serial['serial_number']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="submit" value="Filter Data">
    <button type="submit" name="export">Export to Excel</button>
</form>

<!-- Display filtered data in a table -->
<?php if (!empty($records)) : ?>
    <table border="1">
        <thead>
            <tr>
            <th>ID</th>
                <th>Serial Number</th>
                <th>Input Date</th>
                <th>Shift</th>
                <th>Compound Name</th>
                <th>Description</th>
                <th>Stock</th>
                <th>Batch</th>
                <th>Pallet</th>
                <th>Created At</th>
                <th>Weight</th>
                <th>Quality Approved</th>
                <th>Expire Date</th>
                <th>Staff Name</th>
                <th>SG Value</th>
                <th>Hardness</th>
                <th>MH</th>
                <th>ML</th>
                <th>T10</th>
                <th>T90</th>
                <th>Rebound</th>
                <th>T52</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record) : ?>
                <tr>
                <td><?= htmlspecialchars($record['id']) ?></td>
                    <td><?= htmlspecialchars($record['serial_number']) ?></td>
                    <td><?= htmlspecialchars($record['inputDate']) ?></td>
                    <td><?= htmlspecialchars($record['shift']) ?></td>
                    <td><?= htmlspecialchars($record['compound_name']) ?></td>
                    <td><?= htmlspecialchars($record['description']) ?></td>
                    <td><?= htmlspecialchars($record['cstock']) ?></td>
                    <td><?= htmlspecialchars($record['batch']) ?></td>
                    <td><?= htmlspecialchars($record['pallet']) ?></td>
                    <td><?= htmlspecialchars($record['created_at']) ?></td>
                    <td><?= htmlspecialchars($record['weight']) ?></td>
                    <td><?= htmlspecialchars($record['quality_approved']) ?></td>
                    <td><?= htmlspecialchars($record['expire_date']) ?></td>
                    <td><?= htmlspecialchars($record['staff_name']) ?></td>
                    <td><?= htmlspecialchars($record['sg_value']) ?></td>
                    <td><?= htmlspecialchars($record['hardness']) ?></td>
                    <td><?= htmlspecialchars($record['mh']) ?></td>
                    <td><?= htmlspecialchars($record['ml']) ?></td>
                    <td><?= htmlspecialchars($record['t10']) ?></td>
                    <td><?= htmlspecialchars($record['t90']) ?></td>
                    <td><?= htmlspecialchars($record['rebound']) ?></td>
                    <td><?= htmlspecialchars($record['T52']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>No records found for the selected filters.</p>
<?php endif; ?>
