<?php
// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch unique MRN Numbers to populate the dropdown
$mrnNumbersQuery = "SELECT DISTINCT `mrn_number` FROM `material_request` ORDER BY `mrn_number`";
$mrnStmt = $pdo->prepare($mrnNumbersQuery);
$mrnStmt->execute();
$mrnNumbers = $mrnStmt->fetchAll(PDO::FETCH_COLUMN);

// Initialize variables
$results = [];
$message = '';
$messageType = '';

// Check if 'mrn_number' is passed via GET
$mrn_number = isset($_GET['mrn_number']) ? $_GET['mrn_number'] : '';

// Handle form submission for updating and inserting into material_request_history table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_band'])) {
    try {
        $mrn_number = $_POST['mrn_number'];
        $record_id = $_POST['record_id']; // Use the specific record ID
        $created_at = date('Y-m-d H:i:s');
        $RM_code = $_POST['RM_code'];

        // Start a transaction
        $pdo->beginTransaction();

        // Get the specific record by ID
        $originalQuery = "SELECT * FROM `material_request` WHERE `id` = :record_id";
        $originalStmt = $pdo->prepare($originalQuery);
        $originalStmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
        $originalStmt->execute();
        $originalData = $originalStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$originalData) {
            throw new Exception("Record not found.");
        }
        
        $original_num_of_bands = $originalData['num_of_bands'];
        $band_size = $originalData['band_size'];
        $description = $originalData['description'];

        // New number of bands from form submission
        $new_num_of_bands = $_POST['num_of_bands'];

        // Check if the number of bands is valid
        if ($new_num_of_bands <= 0 || $new_num_of_bands > $original_num_of_bands) {
            throw new Exception("Invalid number of bands.");
        }

        // Determine the action based on the number of bands
        if ($new_num_of_bands == $original_num_of_bands) {
            // If exact match, delete only this specific record
            $deleteQuery = "DELETE FROM `material_request` WHERE `id` = :record_id";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Insert into material_request_history
            $historyQuery = "INSERT INTO `material_request_history` 
                             (`mrn_number`, `RM_code`, `band_size`, `num_of_bands`, `previous_bands`, `created_at`, `status`) 
                             VALUES (:mrn_number, :RM_code, :band_size, :num_of_bands, :previous_bands, :created_at, 'Completed')";
            
            $historyStmt = $pdo->prepare($historyQuery);
            $historyStmt->bindParam(':mrn_number', $mrn_number);
            $historyStmt->bindParam(':RM_code', $RM_code);
            $historyStmt->bindParam(':band_size', $band_size);
            $historyStmt->bindParam(':num_of_bands', $new_num_of_bands);
            $historyStmt->bindParam(':previous_bands', $original_num_of_bands);
            $historyStmt->bindParam(':created_at', $created_at);
            $historyStmt->execute();

            $message = "Material request completed and record deleted. Bands processed: $original_num_of_bands";
            $messageType = "success";

        } else {
            // If partial, update only this specific record
            $remainingBands = $original_num_of_bands - $new_num_of_bands;

            // Update only this specific record
            $updateRequestQuery = "UPDATE `material_request` 
                                   SET `num_of_bands` = :remaining_bands 
                                   WHERE `id` = :record_id";
            $updateRequestStmt = $pdo->prepare($updateRequestQuery);
            $updateRequestStmt->bindParam(':remaining_bands', $remainingBands);
            $updateRequestStmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
            $updateRequestStmt->execute();

            // Insert into material_request_history (only for the processed portion)
            $historyQuery = "INSERT INTO `material_request_history` 
                             (`mrn_number`, `RM_code`, `band_size`, `num_of_bands`, `previous_bands`, `created_at`, `status`) 
                             VALUES (:mrn_number, :RM_code, :band_size, :num_of_bands, :previous_bands, :created_at, 'Partial')";
            
            $historyStmt = $pdo->prepare($historyQuery);
            $historyStmt->bindParam(':mrn_number', $mrn_number);
            $historyStmt->bindParam(':RM_code', $RM_code);
            $historyStmt->bindParam(':band_size', $band_size);
            $historyStmt->bindParam(':num_of_bands', $new_num_of_bands); // Only processed bands
            $historyStmt->bindParam(':previous_bands', $original_num_of_bands);
            $historyStmt->bindParam(':created_at', $created_at);
            $historyStmt->execute();

            $message = "Partial material request processed. Previous bands: $original_num_of_bands, Processed: $new_num_of_bands, Remaining: $remainingBands";
            $messageType = "info";
        }

        // Update stock (for both complete and partial processing)
        $updateStockQuery = "UPDATE `steel_band_stock` 
                             SET `current_quantity` = `current_quantity` - :num_of_bands
                             WHERE `rm_code` = :RM_code";
        $updateStockStmt = $pdo->prepare($updateStockQuery);
        $updateStockStmt->bindParam(':num_of_bands', $new_num_of_bands, PDO::PARAM_INT);
        $updateStockStmt->bindParam(':RM_code', $RM_code);
        $updateStockStmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Refresh the MRN numbers
        $mrnNumbersQuery = "SELECT DISTINCT `mrn_number` FROM `material_request` ORDER BY `mrn_number`";
        $mrnStmt = $pdo->prepare($mrnNumbersQuery);
        $mrnStmt->execute();
        $mrnNumbers = $mrnStmt->fetchAll(PDO::FETCH_COLUMN);

        // If MRN number is still valid, fetch the updated results
        if (!empty($mrn_number)) {
            $query = "SELECT * FROM `material_request` WHERE `mrn_number` = :mrn_number";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':mrn_number', $mrn_number, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (Exception $e) {
        // Rollback the transaction
        $pdo->rollBack();

        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
} elseif (!empty($mrn_number)) {
    // If MRN number is provided, fetch the results
    $query = "SELECT * FROM `material_request` WHERE `mrn_number` = :mrn_number";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':mrn_number', $mrn_number, PDO::PARAM_STR);
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            position: relative;
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
        
        .back-button {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 container-custom">
                <div class="page-header">
                    <a href="dashboard.php" class="btn btn-light back-button">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <h2 class="mb-0">Material Request Management</h2>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Filter Form with Dropdown -->
                <form action="" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="mrn_number" class="form-label">Select MRN Number</label>
                            <select id="mrn_number" name="mrn_number" class="form-select" required>
                                <option value="">-- Select MRN Number --</option>
                                <?php
                                foreach ($mrnNumbers as $number) {
                                    $selected = ($mrn_number == $number) ? "selected" : "";
                                    echo "<option value=\"" . htmlspecialchars($number) . "\" $selected>"
                                         . htmlspecialchars($number) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-custom w-100">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Results Table -->
                <?php if (!empty($results)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-custom">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>MRN Number</th>
                                    <th>RM Code</th>
                                    <th>Band Size</th>
                                    <th>Number of Bands</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['mrn_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['RM_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['band_size']); ?></td>
                                        <td><?php echo htmlspecialchars($row['num_of_bands']); ?></td>
                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-success btn-custom"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#updateModal<?php echo $row['id']; ?>">
                                                Update
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal for each row -->
                                    <div class="modal fade" id="updateModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="updateModalLabel<?php echo $row['id']; ?>">Update Material Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="mrn_number<?php echo $row['id']; ?>" class="form-label">MRN Number:</label>
                                                            <input type="text" class="form-control" id="mrn_number<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['mrn_number']); ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="RM_code<?php echo $row['id']; ?>" class="form-label">RM Code:</label>
                                                            <input type="text" class="form-control" id="RM_code<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['RM_code']); ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="band_size<?php echo $row['id']; ?>" class="form-label">Band Size:</label>
                                                            <input type="text" class="form-control" id="band_size<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['band_size']); ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="num_of_bands<?php echo $row['id']; ?>" class="form-label">Number of Bands to Process:</label>
                                                            <input type="number" name="num_of_bands" class="form-control" id="num_of_bands<?php echo $row['id']; ?>" 
                                                                  value="<?php echo htmlspecialchars($row['num_of_bands']); ?>" 
                                                                  min="1" max="<?php echo htmlspecialchars($row['num_of_bands']); ?>" required>
                                                            <small class="form-text text-muted">Maximum available: <?php echo htmlspecialchars($row['num_of_bands']); ?></small>
                                                        </div>
                                                        
                                                        <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                                        <input type="hidden" name="mrn_number" value="<?php echo htmlspecialchars($row['mrn_number']); ?>">
                                                        <input type="hidden" name="RM_code" value="<?php echo htmlspecialchars($row['RM_code']); ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_band" class="btn btn-success btn-custom">Process</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($mrn_number): ?>
                    <div class="alert alert-info">
                        No data found for MRN Number: <?php echo htmlspecialchars($mrn_number); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch data
$sql = "SELECT 
       *
    FROM material_request";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h2 class="mb-0">Material Request History</h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="materialRequestTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>MRN Number</th>
                                    <th>RM Code</th>
                                    <th>Description</th>
                                    <th>Number of Bands</th>
                                    <th>Created At</th>
                                 
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    // Output data of each row
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                            <td>" . htmlspecialchars($row['mrn_number']) . "</td>
                                            <td>" . htmlspecialchars($row['RM_code']) . "</td>
                                            <td>" . htmlspecialchars($row['band_size']) . "</td>
                                            <td>" . htmlspecialchars($row['num_of_bands']) . "</td>
                                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                                            
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No data found</td></tr>";
                                }

                                // Close the connection
                                $conn->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable with advanced filtering
        $('#materialRequestTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            columnDefs: [
                { 
                    targets: -1, 
                    orderable: false 
                }
            ],
            language: {
                searchPlaceholder: "Search in all columns...",
                // Additional language customizations can be added here
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    });
    </script>
</body>
</html>