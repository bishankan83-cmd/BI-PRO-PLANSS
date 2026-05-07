<?php
use SimpleExcel\SimpleExcel;

// Initialize message variable
$msg = '';

// Define upload directory
$uploadDir = __DIR__ . '/uploads/';
$uploadPath = '';

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_POST['import']) && isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
    // Sanitize the file name to avoid issues with spaces or special characters
    $originalFileName = $_FILES['excel_file']['name'];
    $sanitizedFileName = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $originalFileName);
    $uploadPath = $uploadDir . $sanitizedFileName;

    // Move the uploaded file to the specified directory
    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $uploadPath)) {
        try {
            require_once('SimpleExcel/SimpleExcel.php'); 

            // Initialize SimpleExcel for CSV parsing
            $excel = new SimpleExcel('csv');
            $excel->parser->loadFile($uploadPath);

            $foo = $excel->parser->getField();

            // Database connection
            $db = new mysqli('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');

            if ($db->connect_error) {
                throw new Exception("Database connection failed: " . $db->connect_error);
            }

            // Prepare the SQL statement to prevent SQL injection
            $query = "INSERT INTO worder72 (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);

            if (!$stmt) {
                throw new Exception("Failed to prepare SQL statement: " . $db->error);
            }

            // Loop through the CSV rows, starting from the second row (skip header)
            $count = 1;
            while (isset($foo[$count])) {
                $data = $foo[$count];
                
                // Bind parameters (adjust data types as needed: 's' for string, 'd' for double, 'i' for integer)
                $stmt->bind_param(
                    'ssssssssssssddssd',
                    $data[0],  // date
                    $data[1],  // Customer
                    $data[2],  // wono
                    $data[3],  // ref
                    $data[4],  // erp
                    $data[5],  // icode
                    $data[6],  // t_size
                    $data[7],  // brand
                    $data[8],  // col
                    $data[9],  // fit
                    $data[10], // rim
                    $data[11], // cons
                    $data[12], // fweight (double)
                    $data[13], // ptv (double)
                    $data[14], // new
                    $data[15], // cbm (string)
                    $data[16]  // kgs (double)
                );

                // Execute the query
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert row $count: " . $stmt->error);
                }

                $count++;
            }

            $stmt->close();
            $db->close();

            // Clean up the uploaded file
            unlink($uploadPath);

            $msg = 'Excel file imported successfully.';
            header("Location: check_order_hold.php");
            exit();

        } catch (Exception $e) {
            $msg = 'Error processing file: ' . $e->getMessage();
        }
    } else {
        $msg = 'Error moving uploaded file. Ensure the uploads directory is writable.';
    }
} elseif (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    $msg = 'File upload failed. Error code: ' . $_FILES['excel_file']['error'];
} elseif (isset($_POST['import'])) {
    $msg = 'No file was uploaded.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Work Order</title>
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="content-panel-toggler">
        <i class="os-icon os-icon-grid-squares-22"></i>
        <span>Sidebar</span>
    </div>
    <div class="content-i">
        <div class="content-box">
            <div class="element-wrapper">
                <div class="element-box">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 style="color: blue; border-bottom: 1px solid blue; padding: 10px;">Add Work Order</h5>
                                </div>
                            </div>

                            <form method="post" action="import_hold.php" enctype="multipart/form-data">
                                <input type="file" name="excel_file" accept=".csv" required>
                                <input type="submit" name="import" value="Import Work Order">
                            </form>

                            <?php if (!empty($msg)): ?>
                                <p class="<?php echo strpos($msg, 'successfully') !== false ? 'success' : 'error'; ?>">
                                    <?php echo htmlspecialchars($msg); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>