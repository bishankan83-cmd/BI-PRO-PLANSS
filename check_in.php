<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
include 'includes/App_Code.php';
$AppCodeObj = new App_Code();

// Initialize variables
$totalCStock = 0;
$totalCStockk = 0;
$totalcount = 0;
$totalcountt = 0;
$totaltobe = 0;
$totalnew = 0;
$totalCavityID = 0;

// Execute a SQL query to calculate the sum of cstock in realstock
$query = "SELECT SUM(cstock) AS total_cstock FROM realstock";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCStock = $row['total_cstock'];
    mysqli_free_result($result);
}

// Execute a SQL query to calculate the sum of cstock in stock
$query = "SELECT SUM(cstock) AS total_cstockk FROM stock";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCStockk = $row['total_cstockk'];
    mysqli_free_result($result);
}

// Execute a SQL query to count the number of work orders
$query = "SELECT count(id) AS total_count FROM work_order";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalcount = $row['total_count'];
    mysqli_free_result($result);
}

// SQL query to get the total number of distinct erp for the current month
$sql = "SELECT COUNT(DISTINCT erp_number) AS total_erp
        FROM pros
        WHERE MONTH(dispatch_date) = MONTH(CURDATE()) AND YEAR(dispatch_date) = YEAR(CURDATE())";
$result = $connection->query($sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalcountt = $row['total_erp'];
    mysqli_free_result($result);
}

// Execute a SQL query to calculate the sum of tobe in new_tobeplan_data
$query = "SELECT SUM(tobe) AS total_tobe FROM new_tobeplan_data WHERE tobe > 0";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totaltobe = $row['total_tobe'];
    mysqli_free_result($result);
}

// Execute a SQL query to calculate the sum of new in worder
$query = "SELECT SUM(new) AS total_new FROM worder";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalnew = $row['total_new'];
    mysqli_free_result($result);
}

// Calculate the difference between total count and ERP count
$result = $totalcount - $totalcountt;

// SQL query to count the number of different cavity_id corresponding to today's date in calculated_data
$query = "SELECT COUNT(DISTINCT cavity_id) AS total_cavity_id
          FROM calculated_data
          WHERE DATE(date) = CURDATE()";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCavityID = $row['total_cavity_id'];
    mysqli_free_result($result);
}

?>

<!-- Display the results -->
<div>
    <p>Total CStock (realstock): <?php echo $totalCStock; ?></p>
    <p>Total CStock (stock): <?php echo $totalCStockk; ?></p>
    <p>Total Work Orders: <?php echo $totalcount; ?></p>
    <p>Total ERP for current month: <?php echo $totalcountt; ?></p>
    <p>Total Tobe (new_tobeplan_data): <?php echo $totaltobe; ?></p>
    <p>Total New (worder): <?php echo $totalnew; ?></p>
    
    <p>Total Cavity IDs for today's date (calculated_data): <?php echo $totalCavityID; ?></p>
</div>
