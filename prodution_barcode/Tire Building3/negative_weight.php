<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$dbname = "planatir_task_managemen";
$username = "planatir_task_managemen";
$password = "Bishan@1919";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get negative weight difference records
function getNegativeWeightRecords($conn) {
    $tables = ['qr_scanned_data', 'qr_scanned_data2', 'qr_scanned_data3'];
    $records = [];
    
    foreach ($tables as $table) {
        $sql = "SELECT *, category_weight - (-weight_difference) AS balance 
                FROM $table 
                WHERE weight_difference < 0 
                ORDER BY id DESC";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $row['source_table'] = $table;
            $records[] = $row;
        }
    }
    
    return $records;
}

// Get current negative records
$negativeRecords = getNegativeWeightRecords($conn);

// Handle the QR code scan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['qrData'])) {
    $qrData = $_POST['qrData'];
    
    // Extract batch number and job number from QR code
    $pattern = "/\|BN-([a-zA-Z0-9]+)\|JN-([a-zA-Z0-9]+)/";
    if (preg_match($pattern, $qrData, $matches)) {
        $newBatchNumber = $matches[1];
        $newJobNumber = $matches[2];
        

        
        // Retrieve tire code, compound name, serial number, and compound weight from database
        $sql = "SELECT tire_code, compound_name, serial_number, category_weight 
                FROM qr_scanned_data 
                WHERE batch_number = '$newBatchNumber' AND job_number = '$newJobNumber'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        $_SESSION['newBatchNumber'] = $newBatchNumber;
        $_SESSION['newJobNumber'] = $newJobNumber;
        $_SESSION['newTireCode'] = $row['tire_code'];
        $_SESSION['newCompoundName'] = $row['compound_name'];
        $_SESSION['newSerialNumber'] = $row['serial_number'];
        $_SESSION['newCompoundWeight'] = $row['category_weight'];
        
        header("Location: negative_weight.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid QR code format. Please try again.";
        
        header("Location: negative_weight.php");
        exit();
    }
}

// Update balance and insert new record
if (isset($_POST['updateBalance'])) {
    $tables = ['qr_scanned_data', 'qr_scanned_data2', 'qr_scanned_data3'];
    
    foreach ($tables as $table) {
        $sql = "SELECT * FROM $table WHERE weight_difference < 0";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $balance = $row['category_weight'] - (-$row['weight_difference']);
            $sqlUpdate = "UPDATE $table SET category_weight = '$balance', weight_difference = '0' WHERE id = '$row[id]'";
            $conn->query($sqlUpdate);
        }
    }
    
    // Insert new record into corresponding table
    $newRecord = [
        'compound_name' => $negativeRecords[0]['compound_name'],
        'batch_number' => $_SESSION['newBatchNumber'],
        'job_number' => $_SESSION['newJobNumber'],
        'serial_number' => $negativeRecords[0]['serial_number'],
        'tire_code' => $negativeRecords[0]['tire_code'],
        'weight_difference' => 0,
        'category_weight' => abs($negativeRecords[0]['weight_difference'])
    ];
    
    // Determine which table to insert into based on the source table of the negative record
    $sourceTable = '';
    foreach ($negativeRecords as $record) {
        $sourceTable = $record['source_table'];
        break;
    }
    
    $sqlInsert = "INSERT INTO $sourceTable (compound_name, batch_number, job_number, serial_number, tire_code, weight_difference, category_weight) VALUES ('{$newRecord['compound_name']}', '{$newRecord['batch_number']}', '{$newRecord['job_number']}', '{$newRecord['serial_number']}', '{$newRecord['tire_code']}', '0' , '{$newRecord['category_weight']}')";
    $conn->query($sqlInsert);
    
    // Get the actweight from the compound_data table
    $sqlActweight = "SELECT actweigt FROM compound_data WHERE compound_name = '{$newRecord['compound_name']}'";
    $resultActweight = $conn->query($sqlActweight);
    $rowActweight = $resultActweight->fetch_assoc();
    $actweight = $rowActweight['actweigt'];
    
    // Update the weight_difference in the newly inserted record
    $weightDifference =  $actweight - $newRecord['category_weight'];
    $sqlUpdateWeightDifference = "UPDATE $sourceTable SET weight_difference = '$weightDifference' WHERE batch_number = '{$newRecord['batch_number']}' AND job_number = '{$newRecord['job_number']}'";
    $conn->query($sqlUpdateWeightDifference);
    
    // Get the last serial number
    $sqlLastSerialNumber = "SELECT serial_number FROM $sourceTable ORDER BY id DESC LIMIT 1";
    $resultLastSerialNumber = $conn->query($sqlLastSerialNumber);
    $rowLastSerialNumber = $resultLastSerialNumber->fetch_assoc();
    $lastSerialNumber = $rowLastSerialNumber['serial_number'];
    
    header("Location: about2.php?serialNumber=$lastSerialNumber");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negative Weight Difference Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/fontawesome.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            padding: 20px;
            background-color: #F28018;
            border-radius: 8px;
        }

        .record-card {
            background-color: white;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #CCCCCC;
            border-radius: 8px;
            background-color: #ffffff;
            transition: box-shadow 0.3s;
        }

        .record-card:hover {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .scan-form {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .back-button {
            margin-bottom: 20px;
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .data-label {
            font-weight: 600;
            color: #495057;
        }

        .data-value {
            color: #212529;
        }

        .scan-input {
            border: 2px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
            width: 100%;
            margin-bottom: 10px;
        }

        .scan-button {
            background-color: #000000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .scan-button:hover {
            background-color: #333333;
        }

        .weight-diff-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .btn-primary {
            background-color: #F28018;
            border-color: #F28018;
            color: #ffffff;
            border-radius: 40px;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #d86d0f;
            border-color: #d86d0f;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn btn-primary back-button">
            <i class="fas fa-arrow-left"></i> Back to Scanner
        </a>
        
        <h1 class="page-title">
            <i class="fas fa-exclamation-triangle text-warning"></i> 
            Negative Weight Difference Records
        </h1>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($negativeRecords)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                No records with negative weight difference found.
            </div>
        <?php else: ?>
            <?php foreach ($negativeRecords as $record): ?>
                <div class="record-card">
                    <div class="data-row">
                        <span class="data-label">Compound Name:</span>
                        <span class="data-value"><?php echo htmlspecialchars($record['compound_name']); ?></span>
                        <span class="data-label"> | Serial Number:</span>
                        <span class="data-value"><?php echo htmlspecialchars($record['serial_number']); ?></span>
                        <span class="data-label"> | Batch Number:</span>
                        <span class="data-value"><?php echo htmlspecialchars($record['batch_number']); ?></span>
                        <span class="data-label"> | Job Number:</span>
                        <span class="data-value"><?php echo htmlspecialchars($record['job_number']); ?></span>
                        <span class="data-label"> | Tire Code:</span>
                        <span class="data-value"><?php echo htmlspecialchars($record['tire_code']); ?></span>
                        <span class="data-label"> | Compound Weight:</span>
                        <span class="data-value"><?php echo htmlspecialchars($record['category_weight']); ?></span>
                        <span class="data-label"> | Weight Difference:</span>
                        <span class="data-value weight-diff-negative"><?php echo htmlspecialchars($record['weight_difference']); ?></span>
                        <span class="data-label"> | Balance:</span>
                        <span class="data-value"><?php echo htmlspecialchars($record['balance']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="record-card">
            <div class="scan-form">
                <h5><i class="fas fa-qrcode"></i> Scan New QR Code</h5>
                <form method="POST" class="mt-3">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="scan-input" 
                                   name="qrData" 
                                   placeholder="Scan QR code for new batch/job numbers"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="scan-button">
                                <i class="fas fa-sync-alt"></i> Scan
                            </button>
                        </div>
                    </div>
                </form>
                
                <?php if (isset($_SESSION['newBatchNumber']) && isset($_SESSION['newJobNumber'])): ?>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="data-row">
                                <span class="data-label">Batch Number:</span>
                                <span class="data-value"><?php echo $_SESSION['newBatchNumber']; ?></span>

                            </div>
                            <div class="data-row">
                                <span class="data-label">Serial Number</span>
                                <span class="data-value"><?php echo $negativeRecords[0]['serial_number']; ?></span>
                            </div>

                            <div class="data-row">
                                <span class="data-label">Job Number:</span>
                                <span class="data-value"><?php echo $_SESSION['newJobNumber']; ?></span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Tire Code:</span>
                                <span class="data-value"><?php echo $negativeRecords[0]['tire_code']; ?></span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Compound Name:</span>
                                <span class="data-value"><?php echo $negativeRecords[0]['compound_name']; ?></span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">category Weight:</span>
                                <span class="data-value"><?php echo abs($negativeRecords[0]['category_weight']); ?></span>
                            </div>

                            <?php 
                                $weightDifference = abs($negativeRecords[0]['weight_difference']);
                            ?>

                            <div class="data-row">
                                <span class="data-label">Weight Difference:</span>
                                <span class="data-value <?php echo ($weightDifference < 0) ? 'weight-diff-negative' : ''; ?>"><?php echo $weightDifference; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>   
        
        <form method="POST">
            <button type="submit" name="updateBalance" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Update Balance and Insert New Record
            </button>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/js/fontawesome.min.js"></script>
    <script>
        // Auto-focus on the first QR input field
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('.scan-input');
            if (firstInput) {
                firstInput.focus();
            }
        });
    </script>
</body>
</html>
