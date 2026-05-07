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
    $query = "SELECT cstock FROM stockr WHERE icode = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $icode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result)) {
        $row = mysqli_fetch_assoc($result);
        return $row['cstock'] ?? 0;
    }

    return 0;
}

// Function to get tire details
function getTireDetails($icode)
{
    global $con;
    $query = "SELECT fweight, stgreenweight FROM tire_details WHERE icode = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $icode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result)) {
        $row = mysqli_fetch_assoc($result);
        return [
            'fweight' => is_numeric($row['fweight']) ? floatval($row['fweight']) : 0,
            'stgreenweight' => is_numeric($row['stgreenweight']) ? floatval($row['stgreenweight']) : 0
        ];
    }

    return ['fweight' => 0, 'stgreenweight' => 0];
}

// Start Excel output
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
echo '<Styles>';
echo '<Style ss:ID="header">';
echo '<Font ss:Bold="1" ss:Color="#000000"/>';
echo '<Interior ss:Color="#F28018" ss:Pattern="Solid"/>';
echo '<Borders>';
echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '</Borders>';
echo '</Style>';
echo '<Style ss:ID="data">';
echo '<Borders>';
echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>';
echo '</Borders>';
echo '</Style>';
echo '</Styles>';
echo '<Worksheet ss:Name="Stock Report">';
echo '<Table>';

echo '<Row>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Item Code</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Description</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Brand</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Colour</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Type</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Stock On Hand</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Requirement</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Free Stock</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Tire Weight</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Stock On Hand Weight</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Requirement Weight</Data></Cell>';
echo '<Cell ss:StyleID="header"><Data ss:Type="String">Free Stock Weight</Data></Cell>';
echo '</Row>';

// Get data from database
$query = "SELECT * FROM realstock ORDER BY icode";
$query_run = mysqli_query($con, $query);

if (!$query_run) {
    echo '<Row><Cell ss:MergeAcross="11" ss:StyleID="data"><Data ss:Type="String">Error in stock query: ' . htmlspecialchars(mysqli_error($con)) . '</Data></Cell></Row>';
} else {
    while ($items = mysqli_fetch_assoc($query_run)) {
        $tireDetails = getTireDetails($items['icode']);
        $freeStock = getActualStock($items['icode']);
        $requirement = getRequirementSum($items['icode']);

        // Determine which weight to use
        $useGreenWeight = ($items['cstock'] > 0 && $requirement > 0 && $freeStock !== null);
        $tireWeight = $useGreenWeight ? $tireDetails['stgreenweight'] : $tireDetails['fweight'];

        // If tireWeight is 0, use stgreenweight
        if ($tireWeight == 0) {
            $tireWeight = $tireDetails['stgreenweight'];
        }

        // Calculate weights using the selected tireWeight
        $stockOnHandWeight = $items['cstock'] * $tireWeight;
        $freeStockWeight = $freeStock * $tireWeight;
        $requirementWeight = $requirement * $tireWeight; // Updated calculation

        echo '<Row>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="String">' . htmlspecialchars($items['icode']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="String">' . htmlspecialchars($items['t_size']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="String">' . htmlspecialchars($items['brand']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="String">' . htmlspecialchars($items['col']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="String">' . htmlspecialchars($items['rim']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="Number">' . number_format($items['cstock']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="Number">' . number_format($requirement) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="Number">' . number_format($freeStock) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="Number">' . number_format($tireWeight, 2) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="Number">' . number_format($stockOnHandWeight, 2) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="Number">' . number_format($requirementWeight, 2) . '</Data></Cell>';
        echo '<Cell ss:StyleID="data"><Data ss:Type="Number">' . number_format($freeStockWeight, 2) . '</Data></Cell>';
        echo '</Row>';
    }
}

echo '</Table>';
echo '</Worksheet>';
echo '</Workbook>';

// Close the database connection
mysqli_close($con);
?>