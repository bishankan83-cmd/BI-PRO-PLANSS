<?php
// Database connection details remain the same
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch unique MRN Numbers
$mrnNumbersQuery = "SELECT DISTINCT `mrn_number` FROM `material_request` ORDER BY `mrn_number`";
$mrnStmt = $pdo->prepare($mrnNumbersQuery);
$mrnStmt->execute();
$mrnNumbers = $mrnStmt->fetchAll(PDO::FETCH_COLUMN);

$results = [];
$message = '';
$messageType = '';

$mrn_number = isset($_GET['mrn_number']) ? $_GET['mrn_number'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bands'])) {
    try {
        $mrn_number = $_POST['mrn_number'];
        $created_at = date('Y-m-d H:i:s');
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Get all records for this MRN number
        $recordsQuery = "SELECT * FROM `material_request` WHERE `mrn_number` = :mrn_number";
        $recordsStmt = $pdo->prepare($recordsQuery);
        $recordsStmt->bindParam(':mrn_number', $mrn_number);
        $recordsStmt->execute();
        $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalProcessed = 0;
        $anyPartialUpdates = false;
        
        foreach ($records as $record) {
            $record_id = $record['id'];
            $RM_code = $record['RM_code'];
            $band_size = $record['band_size'];
            $original_num_of_bands = $record['num_of_bands'];
            $new_num_of_bands = $_POST['num_of_bands_' . $record_id];
            
            if ($new_num_of_bands > $original_num_of_bands) {
                throw new Exception("New number of bands cannot exceed original amount");
            }
            
            // Process the update
            if ($new_num_of_bands == $original_num_of_bands) {
                // Delete the record if exact match
                $deleteQuery = "DELETE FROM `material_request` WHERE `id` = :id";
                $deleteStmt = $pdo->prepare($deleteQuery);
                $deleteStmt->bindParam(':id', $record_id);
                $deleteStmt->execute();
            } else {
                // Update with remaining bands
                $remainingBands = $original_num_of_bands - $new_num_of_bands;
                $updateQuery = "UPDATE `material_request` SET `num_of_bands` = :remaining_bands WHERE `id` = :id";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->bindParam(':remaining_bands', $remainingBands);
                $updateStmt->bindParam(':id', $record_id);
                $updateStmt->execute();
                $anyPartialUpdates = true;
            }
            
            // Insert into history
            $historyQuery = "INSERT INTO `material_request_history` 
                           (`mrn_number`, `RM_code`, `band_size`, `num_of_bands`, `previous_bands`, `created_at`, `status`) 
                           VALUES (:mrn_number, :RM_code, :band_size, :num_of_bands, :previous_bands, :created_at, :status)";
            
            $status = ($new_num_of_bands == $original_num_of_bands) ? 'Completed' : 'Partial';
            
            $historyStmt = $pdo->prepare($historyQuery);
            $historyStmt->bindParam(':mrn_number', $mrn_number);
            $historyStmt->bindParam(':RM_code', $RM_code);
            $historyStmt->bindParam(':band_size', $band_size);
            $historyStmt->bindParam(':num_of_bands', $new_num_of_bands);
            $historyStmt->bindParam(':previous_bands', $original_num_of_bands);
            $historyStmt->bindParam(':created_at', $created_at);
            $historyStmt->bindParam(':status', $status);
            $historyStmt->execute();
            
            // Update stock
            $updateStockQuery = "UPDATE `steel_band_stock` 
                               SET `current_quantity` = `current_quantity` - :num_of_bands
                               WHERE `rm_code` = :RM_code";
            $updateStockStmt = $pdo->prepare($updateStockQuery);
            $updateStockStmt->bindParam(':num_of_bands', $new_num_of_bands, PDO::PARAM_INT);
            $updateStockStmt->bindParam(':RM_code', $RM_code);
            $updateStockStmt->execute();
            
            $totalProcessed += $new_num_of_bands;
        }
        
        // Commit transaction
        $pdo->commit();
        
        $status = $anyPartialUpdates ? "partially" : "fully";
        $message = "MRN number $mrn_number has been $status processed. Total bands processed: $totalProcessed";
        $messageType = $anyPartialUpdates ? "info" : "success";
        
        // Refresh MRN numbers
        $mrnStmt->execute();
        $mrnNumbers = $mrnStmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch results if MRN number is provided
if (!empty($mrn_number)) {
    $query = "SELECT * FROM `material_request` WHERE `mrn_number` = :mrn_number";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':mrn_number', $mrn_number);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Request Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Previous styles remain the same */
        :root {
            --primary-color: #F28018;
            --secondary-color: #6c757d;
            --background-color: #f4f6f9;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-custom {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #d4651a);
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .table-custom {
            margin-top: 20px;
        }

        .btn-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #d4651a;
            transform: translateY(-2px);
        }

        .form-select, .form-control {
            background-color: #f9f9f9;
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 container-custom">
                <div class="page-header">
                    <h2 class="mb-0">Material Request Management</h2>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Filter Form -->
                <form action="" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="mrn_number" class="form-label">Select MRN Number</label>
                            <select id="mrn_number" name="mrn_number" class="form-select" required>
                                <option value="">-- Select MRN Number --</option>
                                <?php foreach ($mrnNumbers as $number): ?>
                                    <option value="<?php echo htmlspecialchars($number); ?>" 
                                            <?php echo ($mrn_number == $number) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($number); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-custom w-100">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Results Table -->
                <?php if (!empty($results)): ?>
                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-custom">
                                <thead class="table-light">
                                    <tr>
                                        <th>MRN Number</th>
                                        <th>RM Code</th>
                                        <th>Band Size</th>
                                        <th>Number of Bands</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['mrn_number']); ?></td>
                                            <td>
                                                <input type="text" name="RM_code_<?php echo $row['id']; ?>" 
                                                       class="form-control" 
                                                       value="<?php echo htmlspecialchars($row['RM_code']); ?>" 
                                                       readonly />
                                            </td>
                                            <td>
                                                <input type="text" name="band_size_<?php echo $row['id']; ?>" 
                                                       class="form-control" 
                                                       value="<?php echo htmlspecialchars($row['band_size']); ?>" 
                                                       readonly />
                                            </td>
                                            <td>
                                                <input type="number" name="num_of_bands_<?php echo $row['id']; ?>" 
                                                       class="form-control" 
                                                       value="<?php echo htmlspecialchars($row['num_of_bands']); ?>" 
                                                       min="1" 
                                                       max="<?php echo htmlspecialchars($row['num_of_bands']); ?>" 
                                                       required />
                                            </td>
                                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="mrn_number" value="<?php echo htmlspecialchars($mrn_number); ?>" />
                        <div class="d-grid gap-2 col-md-4 mx-auto mt-3">
                            <button type="submit" name="update_bands" class="btn btn-success btn-custom">
                                Update All Records
                            </button>
                        </div>
                    </form>
                <?php elseif ($mrn_number): ?>
                    <div class="alert alert-info">
                        No data found for MRN Number: <?php echo htmlspecialchars($mrn_number); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>