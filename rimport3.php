<?php
use SimpleExcel\SimpleExcel;

session_start();

// Define upload directory with absolute path
$uploadDir = '/home/planatir/public_html/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$msg = '';
$success = false;

if (isset($_POST['import'])) {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES['excel_file']['tmp_name'];
        $fileName = preg_replace('/[^A-Za-z0-9\-_\.]/', '', basename($_FILES['excel_file']['name'])) . '_' . time() . '.csv'; // Sanitize and add timestamp
        $destination = $uploadDir . $fileName;

        // Move uploaded file to a secure directory
        if (move_uploaded_file($fileTmpName, $destination)) {
            require_once('SimpleExcel/SimpleExcel.php');

            try {
                $excel = new SimpleExcel('csv');
                $excel->parser->loadFile($destination);
                $data = $excel->parser->getField();

                $count = 1;
                $db = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');

                if (!$db) {
                    throw new Exception('Database connection failed: ' . mysqli_connect_error());
                }

                while (count($data) > $count) {
                    $date = mysqli_real_escape_string($db, $data[$count][0]);
                    $Customer = mysqli_real_escape_string($db, $data[$count][1]);
                    $wono = mysqli_real_escape_string($db, $data[$count][2]);
                    $ref = mysqli_real_escape_string($db, $data[$count][3]);
                    $erp = mysqli_real_escape_string($db, $data[$count][4]);
                    $icode = mysqli_real_escape_string($db, $data[$count][5]);
                    $t_size = mysqli_real_escape_string($db, $data[$count][6]);
                    $brand = mysqli_real_escape_string($db, $data[$count][7]);
                    $col = mysqli_real_escape_string($db, $data[$count][8]);
                    $fit = mysqli_real_escape_string($db, $data[$count][9]);
                    $rim = mysqli_real_escape_string($db, $data[$count][10]);
                    $cons = mysqli_real_escape_string($db, $data[$count][11]);
                    $fweight = mysqli_real_escape_string($db, $data[$count][12]);
                    $ptv = mysqli_real_escape_string($db, $data[$count][13]);
                    $new = mysqli_real_escape_string($db, $data[$count][14]);
                    $cbm = mysqli_real_escape_string($db, $data[$count][15]);
                    $kgs = mysqli_real_escape_string($db, $data[$count][16]);

                    $stmt = mysqli_prepare($db, "INSERT INTO worder (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, "sssssssssssssssss", $date, $Customer, $wono, $ref, $erp, $icode, $t_size, $brand, $col, $fit, $rim, $cons, $fweight, $ptv, $new, $cbm, $kgs);

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception('Error inserting row: ' . mysqli_error($db));
                    }
                    mysqli_stmt_close($stmt);
                    $count++;
                }

                $msg = 'Excel file imported successfully.';
                $success = true;
                // Uncomment the next line if you want to redirect after success
                 header("Location: rimport4.php");
                 exit();
            } catch (Exception $e) {
                $msg = 'Error: ' . $e->getMessage();
            } finally {
                if (isset($db) && $db) {
                    mysqli_close($db);
                }
                // Clean up uploaded file
                if (file_exists($destination)) {
                    unlink($destination);
                }
            }
        } else {
            $msg = 'Error moving uploaded file. Check directory permissions or path.';
        }
    } else {
        $msg = 'Error: No file uploaded or upload failed. Error code: ' . $_FILES['excel_file']['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Work Order</title>
    <style>
        .success { color: green; }
        .error { color: red; }
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
                                    <h5 style="color: blue; border-bottom: 1px solid blue; padding: 10px;">Add work order</h5>
                                </div>
                            </div>

                            <form method="post" action="rimport3.php" enctype="multipart/form-data">
                                <input type="file" name="excel_file" accept=".csv">
                                <input type="submit" name="import" value="Import work order">
                            </form>

                            <?php
                            if (!empty($msg)) {
                                echo '<p class="' . ($success ? 'success' : 'error') . '">' . htmlspecialchars($msg) . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>