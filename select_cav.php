<?php
// Database connection configuration
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Begin transaction
    $pdo->beginTransaction();

    // Query to find records with empty or null press_name
    $selectSql = "SELECT ps.id, ps.icode, ps.mold_id 
                  FROM press_selections ps 
                  WHERE (ps.press_name IS NULL OR ps.press_name = '')";
    
    $selectStmt = $pdo->prepare($selectSql);
    $selectStmt->execute();
    $recordsToUpdate = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

    $updatedRecords = 0;
    $failedRecords = 0;
    $updatedDetails = [];

    foreach ($recordsToUpdate as $record) {
        // Query to find matching press_name through plannew, press_cavity, and press tables
        $matchSql = "SELECT p.press_name 
                     FROM plannew pn
                     JOIN press_cavity pc ON pn.cavity_id = pc.cavity_id
                     JOIN press p ON pc.press_id = p.press_id
                     WHERE pn.icode = :icode AND pn.mold_id = :mold_id 
                     LIMIT 1";
        
        $matchStmt = $pdo->prepare($matchSql);
        $matchStmt->execute([
            ':icode' => $record['icode'],
            ':mold_id' => $record['mold_id']
        ]);
        
        $matchResult = $matchStmt->fetch(PDO::FETCH_ASSOC);

        if ($matchResult && !empty($matchResult['press_name'])) {
            // Update press_selections with matched press_name and set is_hidden = 1
            $updateSql = "UPDATE press_selections 
                         SET press_name = :press_name, 
                             is_hidden = 1,
                             updated_at = NOW() 
                         WHERE id = :id";
            
            $updateStmt = $pdo->prepare($updateSql);
            $result = $updateStmt->execute([
                ':press_name' => $matchResult['press_name'],
                ':id' => $record['id']
            ]);

            if ($result) {
                $updatedRecords++;
                $updatedDetails[] = [
                    'id' => $record['id'],
                    'icode' => $record['icode'],
                    'mold_id' => $record['mold_id'],
                    'new_press_name' => $matchResult['press_name']
                ];
            } else {
                $failedRecords++;
            }
        } else {
            $failedRecords++;
        }
    }

    // Commit transaction
    $pdo->commit();

} catch(PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Operation failed: " . $e->getMessage();
    exit;
}
?>





<?php
// Database connection configuration
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create press_selections table if it doesn't exist
    $createTableSql = "CREATE TABLE IF NOT EXISTS press_selections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        icode VARCHAR(50) NOT NULL,
        mold_id VARCHAR(50) DEFAULT NULL,
        press_name VARCHAR(100) NOT NULL,
        mold_count INT DEFAULT 0,
        tobe_sum DECIMAL(10,2) DEFAULT 0,
        description TEXT DEFAULT NULL,
        start_date DATETIME DEFAULT NULL,
        is_hidden TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_icode (icode),
        INDEX idx_mold_id (mold_id),
        INDEX idx_press_name (press_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSql);
    
    // Query to get unique icode with mold_id, description, mold count, and sum of positive tobe values
    $sql = "SELECT p.icode, 
                   COALESCE(tm.mold_id, 'No mold_id found') as mold_id,
                   COALESCE(s.description, 'No description found') as description,
                   (SELECT COUNT(*) FROM tire_mold tm2 WHERE tm2.icode = p.icode) as mold_count,
                   (SELECT SUM(tobe) FROM tobeplan1 tp WHERE tp.icode = p.icode AND tp.tobe > 0) as tobe_sum
            FROM plannew p
            LEFT JOIN tire_details s ON p.icode = s.icode
            LEFT JOIN tire_mold tm ON p.icode = tm.icode
            GROUP BY p.icode, tm.mold_id, s.description
            ORDER BY COALESCE(tm.mold_id, 'No mold_id found'), p.icode";  
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query to get all available presses with press_name only
    $pressSql = "SELECT DISTINCT press_name 
                 FROM press 
                 WHERE press_name IS NOT NULL AND press_name != '' 
                 ORDER BY press_name";
    $pressStmt = $pdo->prepare($pressSql);
    $pressStmt->execute();
    $presses = $pressStmt->fetchAll(PDO::FETCH_ASSOC);

    // Load existing selections from database, including is_hidden
    $existingSelectionsSql = "SELECT icode, press_name, is_hidden FROM press_selections";
    $existingSelectionsStmt = $pdo->prepare($existingSelectionsSql);
    $existingSelectionsStmt->execute();
    $existingSelections = $existingSelectionsStmt->fetchAll(PDO::FETCH_ASSOC);
    $selectionsMap = [];
    foreach ($existingSelections as $selection) {
        $selectionsMap[$selection['icode']] = [
            'press_name' => $selection['press_name'],
            'is_hidden' => $selection['is_hidden']
        ];
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

$message = '';
$excelData = [];

// Handle Excel file upload
if ($_FILES && isset($_FILES['excel_file'])) {
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = $_FILES['excel_file']['name'];
    $fileTmpName = $_FILES['excel_file']['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (in_array($fileExtension, ['xlsx', 'xls'])) {
        $uploadPath = $uploadDir . uniqid() . '_' . $fileName;
        
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            require_once 'vendor/autoload.php';
            
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($fileExtension));
                $spreadsheet = $reader->load($uploadPath);
                $worksheet = $spreadsheet->getActiveSheet();
                $data = $worksheet->toArray();
                
                $headers = array_shift($data);
                $icodeIndex = array_search('icode', $headers);
                $pressNameIndex = array_search('press_name', $headers);
                
                if ($icodeIndex !== false && $pressNameIndex !== false) {
                    foreach ($data as $row) {
                        if (!empty($row[$icodeIndex]) && !empty($row[$pressNameIndex])) {
                            $excelData[trim($row[$icodeIndex])] = trim($row[$pressNameIndex]);
                        }
                    }
                    $message = "Excel file uploaded successfully! Found " . count($excelData) . " icode-press mappings.";
                } else {
                    $message = "Error: Excel file must contain 'icode' and 'press_name' columns.";
                }
                
                unlink($uploadPath);
                
            } catch (Exception $e) {
                $message = "Error reading Excel file: " . $e->getMessage();
            }
        } else {
            $message = "Error uploading file.";
        }
    } else {
        $message = "Please upload a valid Excel file (.xlsx or .xls).";
    }
}

// Handle form submission for saving selections
if ($_POST && isset($_POST['selections'])) {
    try {
        $pdo->beginTransaction();
        
        // First, delete all existing data
        $deleteSql = "DELETE FROM press_selections";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute();
        $deletedCount = $deleteStmt->rowCount();
        
        $savedCount = 0;
        $errorCount = 0;
        
        // Get today's date at 7:00 AM
        $today7am = date('Y-m-d') . ' 07:00:00';
        
        // Then insert ALL data from results with their selected press names
        foreach ($results as $row) {
            $icode = $row['icode'];
            $pressName = isset($_POST['selections'][$icode]) ? $_POST['selections'][$icode] : '';
            $isHidden = isset($selectionsMap[$icode]) && $selectionsMap[$icode]['is_hidden'] ? 1 : 0;
            
            // Insert record regardless of whether press is selected or not
            $insertSql = "INSERT INTO press_selections 
                (icode, mold_id, press_name, mold_count, tobe_sum, description, start_date, is_hidden) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insertStmt = $pdo->prepare($insertSql);
            $success = $insertStmt->execute([
                $icode,
                $row['mold_id'] == 'No mold_id found' ? null : $row['mold_id'],
                $pressName,
                $row['mold_count'],
                $row['tobe_sum'] ?? 0,
                $row['description'] == 'No description found' ? null : $row['description'],
                $today7am,
                $isHidden
            ]);
            
            if ($success) {
                $savedCount++;
            } else {
                $errorCount++;
            }
        }
        
        $pdo->commit();
        
        $message = "Successfully saved $savedCount records to database.";
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        $message = "Error saving to database: " . $e->getMessage();
    }
}

// Handle clear all selections
if ($_POST && isset($_POST['clear_all_selections'])) {
    try {
        $clearSql = "DELETE FROM press_selections";
        $clearStmt = $pdo->prepare($clearSql);
        $clearStmt->execute();
        
        // Clear the existing selections array
        $selectionsMap = [];
        
        $message = "All press selections have been cleared from the database.";
    } catch(PDOException $e) {
        $message = "Error clearing selections: " . $e->getMessage();
    }
}

// Reload existing selections to display current state
$existingSelectionsStmt = $pdo->prepare($existingSelectionsSql);
$existingSelectionsStmt->execute();
$existingSelections = $existingSelectionsStmt->fetchAll(PDO::FETCH_ASSOC);
$selectionsMap = [];
foreach ($existingSelections as $selection) {
    $selectionsMap[$selection['icode']] = [
        'press_name' => $selection['press_name'],
        'is_hidden' => $selection['is_hidden']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Press Selection Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f0f0;
            color: #333333;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 20px auto;
            background-color: #FFFFFF;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        h1 {
            color: #343a40;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2rem;
            font-weight: 600;
        }

        .form-container {
            margin-bottom: 30px;
        }

        .upload-section {
            background-color: #f0f0f0;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #CCCCCC;
        }

        .upload-section h3 {
            color: #343a40;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .file-input-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .file-input {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 6px;
            background-color: #ffffff;
            transition: border-color 0.3s;
        }

        .file-input:focus {
            border-color: #F28018;
            outline: none;
        }

        .button-group {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            background-color: #F28018;
            color: #ffffff;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.2s;
            margin: 0 10px;
        }

        .btn:hover {
            background-color: #d96f15;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #343a40;
        }

        .btn-secondary:hover {
            background-color: #2c3035;
        }

        .btn-danger {
            background-color: red;
        }

        .btn-danger:hover {
            background-color: #cc0000;
        }

        .btn-upload {
            background-color: #F28018;
        }

        .btn-upload:hover {
            background-color: #d96f15;
        }

        .btn-next {
            background-color: #28a745;
        }

        .btn-next:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #CCCCCC;
        }

        th {
            background-color: #343a40;
            color: #ffffff;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        tr.hidden-row {
            background-color:rgb(224, 224, 11);
        }

        .no-data {
            text-align: center;
            color: #333333;
            font-style: italic;
            padding: 30px;
            font-size: 1.1rem;
        }

        .record-count {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
            font-size: 0.9rem;
        }

        .no-description, .no-mold, .no-tobe {
            color: #CCCCCC;
            font-style: italic;
        }

        .mold-icode {
            font-weight: 600;
            color: #343a40;
        }

        .press-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #CCCCCC;
            border-radius: 6px;
            font-size: 0.9rem;
            background-color: #ffffff;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .press-select:focus {
            outline: none;
            border-color: #F28018;
        }

        .press-select.auto-filled {
            background-color: #f0f0f0;
            border-color: #F28018;
        }

        .press-select.saved {
            background-color: #ffffff;
            border-color: #CCCCCC;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.15);
        }

        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .success-message {
            background-color: #f0f0f0;
            color: #000000;
            border: 1px solid #F28018;
        }

        .error-message {
            background-color: #f0f0f0;
            color: red;
            border: 1px solid red;
        }

        .select-all-container {
            margin: 15px 0;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mold-count, .tobe-sum {
            color: red;
            font-weight: 600;
        }

        .excel-instructions {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #F28018;
        }

        .excel-instructions h4 {
            color: #343a40;
            margin-bottom: 10px;
        }

        .database-info {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #CCCCCC;
        }

        .database-info h4 {
            color: #343a40;
            margin-bottom: 10px;
        }

        .saved-indicator {
            color: #F28018;
            font-weight: 600;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .auto-gen-indicator {
            color: #F28018;
            font-weight: 600;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .start-date-info {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #F28018;
            font-size: 0.9rem;
        }

        .start-date-info strong {
            color: #343a40;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            table {
                font-size: 0.8rem;
            }

            th, td {
                padding: 10px;
            }

            .btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }

            .select-all-container {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    <script>
        function selectAllPress() {
            const masterSelect = document.getElementById('masterPress');
            const allSelects = document.querySelectorAll('.press-select');
            
            if (masterSelect.value) {
                allSelects.forEach(select => {
                    select.value = masterSelect.value;
                    select.classList.remove('auto-filled', 'saved');
                });
            }
        }
        
        function clearAllSelections() {
            const allSelects = document.querySelectorAll('.press-select');
            allSelects.forEach(select => {
                select.value = '';
                select.classList.remove('auto-filled', 'saved');
            });
            document.getElementById('masterPress').value = '';
        }
        
        function applyExcelData() {
            const excelData = <?php echo json_encode($excelData); ?>;
            const allSelects = document.querySelectorAll('.press-select[name^="selections"]');
            let matchedCount = 0;
            
            allSelects.forEach(select => {
                const icode = select.name.match(/\[(.*?)\]/)[1];
                if (excelData[icode]) {
                    const options = select.querySelectorAll('option');
                    for (let option of options) {
                        if (option.value === excelData[icode]) {
                            select.value = option.value;
                            select.classList.remove('saved');
                            select.classList.add('auto-filled');
                            matchedCount++;
                            break;
                        }
                    }
                }
            });
            
            if (matchedCount > 0) {
                alert(`Successfully applied ${matchedCount} press selections from Excel data.`);
            } else {
                alert('No matching press names found in the uploaded Excel data.');
            }
        }
        
        function confirmClearDatabase() {
            if (confirm('Are you sure you want to clear ALL press selections from the database? This action cannot be undone.')) {
                document.getElementById('clearAllForm').submit();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const excelData = <?php echo json_encode($excelData); ?>;
            if (Object.keys(excelData).length > 0) {
                applyExcelData();
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Press Selection Dashboard</h1>
        
        <div class="database-info">
            <h4>⚠️ Database Storage Information:</h4>
            <p><strong>Important:</strong> Saving will <strong>delete</strong> all existing data in the <strong>press_selections</strong> table and replace it with your current selections.</p>
            <p>Selections highlighted in the table are loaded from the database.</p>
            <p>Rows highlighted in yellow indicate records with is_hidden = 1, with assigned presses marked as "Auto Gen".</p>
        </div>
        
        <div class="start-date-info">
            <strong>🕰️ Start Date Assignment:</strong> All records will have their start_date set to <strong>today at 7:00 AM (<?php echo date('Y-m-d'); ?> 07:00:00)</strong> upon saving.
        </div>
        
        <div class="upload-section">
            <h3>Upload Excel File for Auto Press Selection</h3>
            <div class="excel-instructions">
                <h4>Excel File Requirements:</h4>
                <ul>
                    <li>File must be in .xlsx or .xls format</li>
                    <li>First row should contain headers: <strong>icode</strong> and <strong>press_name</strong></li>
                    <li>Each row should contain the icode and corresponding press_name</li>
                    <li>Press selections will be automatically applied after upload</li>
                </ul>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="file-input-container">
                    <input type="file" name="excel_file" class="file-input" accept=".xlsx,.xls" required>
                    <button type="submit" class="btn btn-upload">Upload & Apply</button>
                </div>
            </form>
            
            <?php if (!empty($excelData)): ?>
                <div style="margin-top: 15px;">
                    <button type="button" class="btn btn-secondary" onclick="applyExcelData()">
                        Re-apply Excel Data (<?php echo count($excelData); ?> mappings)
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === 0 ? 'error-message' : 'success-message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($results)): ?>
            <div class="record-count">
                Total unique icodes: <?php echo count(array_unique(array_column($results, 'icode'))); ?>
                | Records in database: <?php echo count($selectionsMap); ?>
                <?php if (!empty($excelData)): ?>
                    | Excel mappings loaded: <?php echo count($excelData); ?>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="form-container">
                <div class="select-all-container">
                    <label for="masterPress"><strong>Select press for all items:</strong></label>
                    <select id="masterPress" class="press-select" style="width: 200px;">
                        <option value="">-- Select Press --</option>
                        <?php foreach ($presses as $press): ?>
                            <option value="<?php echo htmlspecialchars($press['press_name']); ?>">
                                <?php echo htmlspecialchars($press['press_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-secondary" onclick="selectAllPress()">Apply to All</button>
                    <button type="button" class="btn" onclick="clearAllSelections()">Clear All</button>
                    <button type="button" class="btn btn-danger" onclick="confirmClearDatabase()">Clear Database</button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Press Selection</th>
                            <th>Mold ID - icode</th>
                            <th>Mold ID</th>
                            <th>icode</th>
                            <th>Mold Count</th>
                            <th>Sum of Positive Tobe</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr class="<?php echo isset($selectionsMap[$row['icode']]) && $selectionsMap[$row['icode']]['is_hidden'] ? 'hidden-row' : ''; ?>">
                                <td>
                                    <select name="selections[<?php echo htmlspecialchars($row['icode']); ?>]" 
                                            class="press-select <?php echo isset($selectionsMap[$row['icode']]) && !empty($selectionsMap[$row['icode']]['press_name']) ? 'saved' : ''; ?>">
                                        <option value="">-- Select Press --</option>
                                        <?php foreach ($presses as $press): ?>
                                            <option value="<?php echo htmlspecialchars($press['press_name']); ?>"
                                                    <?php echo (isset($selectionsMap[$row['icode']]) && 
                                                             $selectionsMap[$row['icode']]['press_name'] == $press['press_name']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($press['press_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($selectionsMap[$row['icode']]) && !empty($selectionsMap[$row['icode']]['press_name'])): ?>
                                        <div class="<?php echo $selectionsMap[$row['icode']]['is_hidden'] ? 'auto-gen-indicator' : 'saved-indicator'; ?>">
                                            <?php echo $selectionsMap[$row['icode']]['is_hidden'] ? 'Auto Gen' : 'Assigned'; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="mold-icode">
                                    <?php 
                                    if ($row['mold_id'] != 'No mold_id found') {
                                        echo htmlspecialchars($row['mold_id']) . ' - ' . htmlspecialchars($row['icode']);
                                    } else {
                                        echo htmlspecialchars($row['icode']);
                                    }
                                    ?>
                                </td>
                                <td class="<?php echo ($row['mold_id'] == 'No mold_id found') ? 'no-mold' : ''; ?>">
                                    <?php echo htmlspecialchars($row['mold_id']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['icode']); ?></td>
                                <td class="mold-count"><?php echo htmlspecialchars($row['mold_count']); ?></td>
                                <td class="<?php echo (is_null($row['tobe_sum']) || $row['tobe_sum'] == 0) ? 'no-tobe' : 'tobe-sum'; ?>">
                                    <?php echo htmlspecialchars($row['tobe_sum'] ?? '0'); ?>
                                </td>
                                <td class="<?php echo ($row['description'] == 'No description found') ? 'no-description' : ''; ?>">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="button-group">
                    <button type="submit" class="btn">Save Selections</button>
                    <a href="date_update12R.php" class="btn btn-next">Next</a>
                </div>
            </form>
            
            <form id="clearAllForm" method="POST" style="display: none;">
                <input type="hidden" name="clear_all_selections" value="1">
            </form>
            
        <?php else: ?>
            <div class="no-data">
                No data found in the plannew table.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>






<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to delete records from press_selections
$sql = "DELETE FROM press_selections
        WHERE NOT EXISTS (
            SELECT 1
            FROM tobeplan1 tp
            WHERE tp.icode = press_selections.icode
            AND tp.tobe > 0
        )";

if ($conn->query($sql) === TRUE) {
    echo "Records deleted successfully. Affected rows: " . $conn->affected_rows;
} else {
    echo "Error executing query: " . $conn->error;
}

$conn->close();
?>







