<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Establish MySQL connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (isset($_POST['icode']) && isset($_POST['t_size']) && isset($_POST['a']) && isset($_POST['b']) && isset($_POST['c']) && isset($_POST['d']) && isset($_POST['e']) && isset($_POST['f']) && isset($_POST['g']) && isset($_POST['h']) && isset($_POST['i']) && isset($_POST['j']) && isset($_POST['k']) && isset($_POST['l']) && isset($_POST['m']) && isset($_POST['n']) && isset($_POST['o']) && isset($_POST['p']) && isset($_POST['Grand_Totalcompound_weight']) && isset($_POST['Color']) && isset($_POST['Brand']) && isset($_POST['Green_Tire_weight']) && isset($_POST['PB_weight'])) {

        // Assuming the form data is submitted as arrays
        $itemCodes = $_POST['icode'];
        $sizes = $_POST['t_size'];
        $atprs = $_POST['a'];
        $b_ats15s = $_POST['b'];
        $b_bns24s = $_POST['c'];
        $bg_bls12s = $_POST['d'];
        $cg_bs901s = $_POST['e'];
        $c_sms501s = $_POST['f'];
        $c_ats20s = $_POST['g'];
        $c_sms702s = $_POST['h'];
        $t_trs102s = $_POST['i'];
        $t_atnms = $_POST['j'];
        $t_ats30s = $_POST['k'];
        $t_ats35s = $_POST['l'];
        $t_ks40s = $_POST['m'];
        $t_trnms402s = $_POST['n'];
        $t_trnms402gs = $_POST['o'];
        $t_trs202s = $_POST['p'];
        $grandTotals = $_POST['Grand_Totalcompound_weight'];
        $colors = $_POST['Color'];
        $brands = $_POST['Brand'];
        $greenTireWeights = $_POST['Green_Tire_weight'];
        $pbWeights = $_POST['PB_weight'];

        // Update database records
        for ($i = 0; $i < count($itemCodes); $i++) {
            // Escape user inputs for security
            $itemCode = mysqli_real_escape_string($conn, $itemCodes[$i]);
            $size = mysqli_real_escape_string($conn, $sizes[$i]);
            $atpr = mysqli_real_escape_string($conn, $atprs[$i]);
            $b_ats15 = mysqli_real_escape_string($conn, $b_ats15s[$i]);
            $b_bns24 = mysqli_real_escape_string($conn, $b_bns24s[$i]);
            $bg_bls12 = mysqli_real_escape_string($conn, $bg_bls12s[$i]);
            $cg_bs901 = mysqli_real_escape_string($conn, $cg_bs901s[$i]);
            $c_sms501 = mysqli_real_escape_string($conn, $c_sms501s[$i]);
            $c_ats20 = mysqli_real_escape_string($conn, $c_ats20s[$i]);
            $c_sms702 = mysqli_real_escape_string($conn, $c_sms702s[$i]);
            $t_trs102 = mysqli_real_escape_string($conn, $t_trs102s[$i]);
            $t_atnm = mysqli_real_escape_string($conn, $t_atnms[$i]);
            $t_ats30 = mysqli_real_escape_string($conn, $t_ats30s[$i]);
            $t_ats35 = mysqli_real_escape_string($conn, $t_ats35s[$i]);
            $t_ks40 = mysqli_real_escape_string($conn, $t_ks40s[$i]);
            $t_trnms402 = mysqli_real_escape_string($conn, $t_trnms402s[$i]);
            $t_trnms402g = mysqli_real_escape_string($conn, $t_trnms402gs[$i]);
            $t_trs202 = mysqli_real_escape_string($conn, $t_trs202s[$i]);
            $grandTotal = mysqli_real_escape_string($conn, $grandTotals[$i]);
            $color = mysqli_real_escape_string($conn, $colors[$i]);
            $brand = mysqli_real_escape_string($conn, $brands[$i]);
            $greenTireWeight = mysqli_real_escape_string($conn, $greenTireWeights[$i]);
            $pbWeight = mysqli_real_escape_string($conn, $pbWeights[$i]);

            // SQL update query
            $sql = "UPDATE bom_new45 SET t_size='$size', a='$atpr', b='$b_ats15', c='$b_bns24', d='$bg_bls12', e='$cg_bs901', f='$c_sms501', g='$c_ats20', h='$c_sms702', i='$t_trs102', j='$t_atnm', k='$t_ats30', l='$t_ats35', m='$t_ks40', n='$t_trnms402', o='$t_trnms402g', p='$t_trs202', Grand_Totalcompound_weight='$grandTotal', Color='$color', Brand='$brand', Green_Tire_weight='$greenTireWeight', PB_weight='$pbWeight' WHERE icode='$itemCode'";
            // Execute the query
            if (mysqli_query($conn, $sql)) {
                echo "Record updated successfully";
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
        }
    } else {
        echo "Some form fields are missing.";
    }
} else {
    echo "No data submitted.";
}

// Close the connection
mysqli_close($conn);
?>
