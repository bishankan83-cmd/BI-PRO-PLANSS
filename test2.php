<?php
// Database connection parameters
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

// Function to check if data exists in the tobeplan table
function checkTobeplanData() {
    global $conn;
    $tobeplan_query = "SELECT COUNT(*) AS count FROM tobeplan";
    $tobeplan_result = $conn->query($tobeplan_query);
    $tobeplan_row = $tobeplan_result->fetch_assoc();
    $tobeplan_count = $tobeplan_row['count'];
    return $tobeplan_count;
}

// Function to check if data exists in the process table
function checkProcessData() {
    global $conn;
    $process_query = "SELECT COUNT(*) AS count FROM process";
    $process_result = $conn->query($process_query);
    $process_row = $process_result->fetch_assoc();
    $process_count = $process_row['count'];
    return $process_count;
}

// Function to delete all data from the tobeplan table
function deleteTobeplanData() {
    global $conn;
    $delete_query = "DELETE FROM tobeplan";
    if ($conn->query($delete_query) === TRUE) {
        echo "All data deleted from the tobeplan table.";
    } else {
        echo "Error deleting data: " . $conn->error;
    }
}

// Function to delete all data from the process table
function deleteProcessData() {
    global $conn;
    $delete_query = "DELETE FROM process";
    if ($conn->query($delete_query) === TRUE) {
        echo "All data deleted from the process table.";
    } else {
        echo "Error deleting data: " . $conn->error;
    }
}

// Function to import work order data from a CSV file
function importWorkOrderData() {
    global $conn;
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
}

// Function to copy data from one table to another
function copyData() {
    global $conn;
    $sql = "INSERT INTO worder (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs)
            SELECT date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs
            FROM worder56";
    if ($conn->query($sql) === TRUE) {
        header("Location: import2.php");
        exit();
    } else {
        echo "Error copying data: " . $conn->error;
    }
}

// Function to update dates in the worder table
function updateDates() {
    global $conn;
    $sql = "UPDATE worder
            JOIN work_order ON worder.erp = work_order.erp
            SET worder.date = work_order.datetime";
    if ($conn->query($sql) === TRUE) {
        echo "Dates updated successfully!";
    } else {
        echo "Error updating dates: " . $conn->error;
    }
}

// Function to add work order data
function addWorkOrderData() {
    global $conn;
    if (isset($_POST['submit'])) {
        $Datetime = $_POST['datetime'];
        $takeDatetime = $_POST['take_datetime'];
        $erp = $_POST['erp'];
        $formatDatetime = date("Y-m-d H:i:s", strtotime($Datetime));
        $formattedTakeDatetime = date("Y-m-d H:i:s", strtotime($takeDatetime));
        $result = $conn->query("INSERT INTO work_order (datetime, take_datetime, erp) VALUES ('$formatDatetime', '$formattedTakeDatetime', '$erp')");
        if ($result) {
            header('Location:convertstock.php');
            exit();
        } else {
            echo "Error adding work order data: " . $conn->error;
        }
    }
}

// Function to copy data from realstock to stock
function copyStockData() {
    global $conn;
    $copyQuery = "INSERT INTO stock SELECT * FROM realstock";
    if ($conn->query($copyQuery) === TRUE) {
        header("Location: subtract.php");
        exit();
    } else {
        echo "Error copying stock data: " . $conn->error;
    }
}

// Function to subtract data from stock
function subtractData() {
    global $conn;
    if (isset($_POST['submit'])) {
        $erp = $_POST['erp'];
        $sql = "INSERT INTO tobeplan_plan (icode, tobe, erp, stockonhand)
                SELECT t1.icode, t1.new - t2.cstock, t1.erp, t2.cstock
                FROM worder t1
                INNER JOIN stock t2 ON t1.icode = t2.icode
                WHERE t1.erp = '$erp'";
        if ($conn->query($sql) === TRUE) {
            $updateSql = "UPDATE stock t2
                          INNER JOIN worder t1 ON t1.icode = t2.icode
                          SET t2.cstock = CASE
                              WHEN t1.new <= t2.cstock THEN t2.cstock - t1.new
                              ELSE 0
                          END
                          WHERE t1.erp = '$erp'";
            if ($conn->query($updateSql) === TRUE) {
                header("Location: display.php?erp=$erp");
                exit;
            } else {
                echo "Error updating stock: " . $conn->error;
            }
        } else {
            echo "Error subtracting data: " . $conn->error;
        }
    }
}

// Main program
if (checkTobeplanData() > 0 && checkProcessData() > 0) {
    header("Location: plannew45new2.php");
    exit();
} elseif (checkTobeplanData() > 0 && checkProcessData() == 0) {
    deleteTobeplanData();
} elseif (checkProcessData() > 0 && checkTobeplanData() == 0) {
    deleteProcessData();
} else {
    echo "No data available in both tables.";
}

importWorkOrderData();
copyData();
updateDates();
addWorkOrderData();
copyStockData();
subtractData();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Work Order Management System</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 50px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        h1 {
            color: #000000;
            font-weight: bold;
            font-size: 24px;
        }

        form {
            margin-top: 20px;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Cantarell Bold', sans-serif;
        }

        input[type="submit"]:hover {
            background-color: #FFA726;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Work Order Management System</h1>
        <form method="POST" action="subtract.php">
            <label for="erp"></label>
            <input type="text" name="erp" id="erp" required>
            <input type="submit" name="submit" value="Click Next">
        </form>
        <form method="post" action="import_new.php" enctype="multipart/form-data">
            <input type="file" name="excel_file" accept=".csv">
            <input type="submit" name="import" value="Import work order">
        </form>
        <form method="POST">
            <label for="datetime">Work Order Insert Date</label>
            <input name="datetime" id="datetime" type="datetime-local" required>
            <label for="take_datetime">Work Order Take Date</label>
            <input name="take_datetime" id="take_datetime" type="datetime-local" required>
            <label for="erp">Ref. ERP CO.No</label>
            <input name="erp" id="erp" placeholder="" type="text" required>
            <input type="submit" value="Submit Now" name="submit">
        </form>
    </div>
</body>
</html>
