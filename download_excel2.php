<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a database connection
$con = mysqli_connect($servername, $username, $password, $dbname);

// Check the connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=stock_report_with_weights.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Function to get requirement sum
function getRequirementSum($icode)
{
    global $con;
    $query = "SELECT SUM(new) AS requirement_sum FROM worder WHERE icode = ? AND erp != 1";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $icode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['requirement_sum'] ?? 0;
    }

    return 0;
}

// Function to get actual stock (free stock)
function getActualStock($icode)
{
    global $con;
    $query = "SELECT cstock FROM stockr WHERE icode = '$icode'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result)) {
        $row = mysqli_fetch_assoc($result);
        return $row['cstock'];
    }

    return 0;
}

// Function to get tire details
function getTireDetails($icode)
{
    global $con;
    $query = "SELECT greenweight, stgreenweight FROM tire_details WHERE icode = '$icode'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result)) {
        $row = mysqli_fetch_assoc($result);
        return array(
            'greenweight' => is_numeric($row['greenweight']) ? $row['greenweight'] : 0,
            'stgreenweight' => is_numeric($row['stgreenweight']) ? $row['stgreenweight'] : 0
        );
    }

    return array('greenweight' => 0, 'stgreenweight' => 0);
}

// Function to calculate weighted values
function calculateWeightedValues($freeStock, $greenWeight, $stGreenWeight)
{
    $freeStockNum = is_numeric($freeStock) ? $freeStock : 0;
    $greenWeightNum = is_numeric($greenWeight) ? $greenWeight : 0;
    $stGreenWeightNum = is_numeric($stGreenWeight) ? $stGreenWeight : 0;
    
    return array(
        'freestock_x_greenweight' => $freeStockNum * $greenWeightNum,
        'freestock_x_stgreenweight' => $freeStockNum * $stGreenWeightNum
    );
}

// Start Excel output
echo "<table border='1'>";
echo "<tr style='background-color: #F28018; font-weight: bold;'>";
echo "<td>Item Code</td>";
echo "<td>Description</td>";
echo "<td>Brand</td>";
echo "<td>Colour</td>";
echo "<td>Stock On Hand</td>";
echo "<td>Requirement</td>";
echo "<td>Free Stock</td>";

echo "<td>Free Stock × Green Weight</td>";
echo "<td>Free Stock × ST Green Weight</td>";
echo "</tr>";

// Get data from database
$query = "SELECT * FROM realstock ORDER BY icode";
$query_run = mysqli_query($con, $query);

if (!$query_run) {
    echo "<tr><td colspan='11'>Error in stock query: " . mysqli_error($con) . "</td></tr>";
} else {
    while ($items = mysqli_fetch_assoc($query_run)) {
        $tireDetails = getTireDetails($items['icode']);
        $freeStock = getActualStock($items['icode']);
        $requirement = getRequirementSum($items['icode']);
        $weightedValues = calculateWeightedValues($freeStock, $tireDetails['greenweight'], $tireDetails['stgreenweight']);
        
        echo "<tr>";
        echo "<td>" . $items['icode'] . "</td>";
        echo "<td>" . $items['t_size'] . "</td>";
        echo "<td>" . $items['brand'] . "</td>";
        echo "<td>" . $items['col'] . "</td>";
        echo "<td>" . $items['cstock'] . "</td>";
        echo "<td>" . $requirement . "</td>";
        echo "<td>" . $freeStock . "</td>";
       
        echo "<td>" . number_format($weightedValues['freestock_x_greenweight'], 2) . "</td>";
        echo "<td>" . number_format($weightedValues['freestock_x_stgreenweight'], 2) . "</td>";
        echo "</tr>";
    }
}

// Add summary row
echo "<tr style='background-color: #CCCCCC; font-weight: bold;'>";
echo "<td colspan='11'>Summary calculations can be added here if needed</td>";
echo "</tr>";

echo "</table>";

// Close the database connection
mysqli_close($con);
?>