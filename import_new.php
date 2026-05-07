

<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Establish a database connection
$connection = mysqli_connect($host, $username, $password, $database);

// Check for a successful connection
if (mysqli_connect_errno()) {
    die('Failed to connect to the database: ' . mysqli_connect_error());
}

// Copy data query (assuming $copyDataQuery is defined somewhere)
// Uncomment this line if $copyDataQuery is set.
// mysqli_query($connection, $copyDataQuery);

// Array of delete queries
$deleteQueries = [
    "DELETE FROM production_plan",
    "DELETE FROM tire_cavity",
    "DELETE FROM tire_molddd",
    "DELETE FROM quick_plan",
    "DELETE FROM process_plan",
    "DELETE FROM tobeplan_plan",
    "DELETE FROM tobeplan_tem",
    "DELETE FROM process_plan_tem",
];

// Execute each delete query
foreach ($deleteQueries as $query) {
    if (mysqli_query($connection, $query)) {
        echo "Successfully executed: $query\n";
    } else {
        echo "Error executing query: $query - " . mysqli_error($connection) . "\n";
    }
}

// Close the database connection
mysqli_close($connection);
?>




<?php
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

// SQL to delete all data
$sql_delete = "DELETE FROM `worder56`";

// SQL to reset auto-increment counter
$sql_reset_auto_increment = "ALTER TABLE `worder56` AUTO_INCREMENT = 1";

// Execute delete query
if ($conn->query($sql_delete) === TRUE) {
    echo "All records deleted successfully.";
    
    // Execute reset auto-increment query
    if ($conn->query($sql_reset_auto_increment) === TRUE) {
        echo " Auto-increment counter reset successfully.";
    } else {
        echo " Error resetting auto-increment counter: " . $conn->error;
    }
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close connection
$conn->close();
?>




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

            $query = "INSERT INTO worder56 (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs) ";
            $query .= "VALUES ('$date', '$Customer', '$wono', '$ref', '$erp', '$icode', '$t_size', '$brand', '$col', '$fit', '$rim', '$cons', '$fweight', '$ptv', '$new', '$cbm', '$kgs')";
            mysqli_query($db, $query);
            $count++;
        }

        $msg = 'Excel file imported successfully.';
        header("Location: check_order.php");
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

                            <form method="post" action="import_new.php" enctype="multipart/form-data">
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
