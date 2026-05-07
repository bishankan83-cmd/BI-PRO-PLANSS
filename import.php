<?php
use SimpleExcel\SimpleExcel;

$msg = '';

if (isset($_POST['import'])) {
    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $_FILES['excel_file']['name'])) {
        require_once('SimpleExcel/SimpleExcel.php'); 
    
        $excel = new SimpleExcel('csv');                  
    
        $excel->parser->loadFile($_FILES['excel_file']['name']);           
    
        $foo = $excel->parser->getField(); 

        $count = 1;
        $db = mysqli_connect('localhost','planatir_task_managemen','Bishan@1919','planatir_task_managemen');

        while (count($foo) > $count) {
            $date = $foo[$count][0];
            $Customer = $foo[$count][1];
            $wono = $foo[$count][2];
            $ref = $foo[$count][3];
            $erp = $foo[$count][4];
            $icode = $foo[$count][5];
            $t_size = $foo[$count][6];
            $brand = $foo[$count][7];
            $col = $foo[$count][8];
            $fit = $foo[$count][9];
            
            $rim = $foo[$count][10];
            $cons = $foo[$count][11];
            $fweight = $foo[$count][12];
            $ptv = $foo[$count][13];
            $new = $foo[$count][14];
            $cbm = $foo[$count][15];
            $kgs = $foo[$count][16];

            $query = "INSERT INTO worder (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs) ";
            $query .= "VALUES ('$date', '$Customer', '$wono', '$ref', '$erp', '$icode', '$t_size', '$brand', '$col', '$fit', '$rim', '$cons', '$fweight', '$ptv', '$new', '$cbm', '$kgs')";
            mysqli_query($db, $query);
            $count++;
        }

        $msg = 'Excel file imported successfully.';
        header("Location: import2.php");
        exit();
       
    } else {
        $msg = 'Error importing file.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Work Order</title>
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

                            <form method="post" action="import.php" enctype="multipart/form-data">
                                <input type="file" name="excel_file" accept=".csv">
                                <input type="submit" name="import" value="Import work order">
                            </form>

                            <?php
                            // Display the success message if it exists
                            if (!empty($msg)) {
                                echo '<p>' . $msg . '</p>';
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
