<?php
// Replace these database credentials with your own
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect the form data from the submitted arrays
    $items = $_POST["item"];
    $icodes = $_POST["icode"];
    $t_sizes = $_POST["t_size"];
    $item_descriptions = $_POST["item_description"];
    $b_ats_15 = $_POST["b_ats_15"];
    $b_bns_24 = $_POST["b_bns_24"];
    $bg_bls_12 = $_POST["bg_bls_12"];
    $cg_bs_901 = $_POST["cg_bs_901"];
    $c_sms_501 = $_POST["c_sms_501"];
    $c_ats_20 = $_POST["c_ats_20"];
    $c_sms_702 = $_POST["c_sms_702"];
    $t_trs_102 = $_POST["t_trs_102"];
    $t_atnm_s = $_POST["t_atnm_s"];
    $t_ats_30 = $_POST["t_ats_30"];
    $t_ats_35 = $_POST["t_ats_35"];
    $t_ks_40 = $_POST["t_ks_40"];
    $t_trnms_402 = $_POST["t_trnms_402"];
    $t_trnms_402g = $_POST["t_trnms_402g"];
    $t_trs_202 = $_POST["t_trs_202"];
    $grand_total_compound_weight = $_POST["grand_total_compound_weight"];
    $colors = $_POST["color"];
    $brands = $_POST["brand"];
    $green_tire_weights = $_POST["green_tire_weight"];
    
    // Prepare and execute an SQL INSERT statement for each row of data
    for ($i = 0; $i < count($items); $i++) {
        $item = $conn->real_escape_string($items[$i]);
        $icode = $conn->real_escape_string($icodes[$i]);
        $t_size = $conn->real_escape_string($t_sizes[$i]);
        $item_description = $conn->real_escape_string($item_descriptions[$i]);
        $b_ats_15_value = $conn->real_escape_string($b_ats_15[$i]);
        $b_bns_24_value = $conn->real_escape_string($b_bns_24[$i]);
        $bg_bls_12_value = $conn->real_escape_string($bg_bls_12[$i]);
        $cg_bs_901_value = $conn->real_escape_string($cg_bs_901[$i]);
        $c_sms_501_value = $conn->real_escape_string($c_sms_501[$i]);
        $c_ats_20_value = $conn->real_escape_string($c_ats_20[$i]);
        $c_sms_702_value = $conn->real_escape_string($c_sms_702[$i]);
        $t_trs_102_value = $conn->real_escape_string($t_trs_102[$i]);
        $t_atnm_s_value = $conn->real_escape_string($t_atnm_s[$i]);
        $t_ats_30_value = $conn->real_escape_string($t_ats_30[$i]);
        $t_ats_35_value = $conn->real_escape_string($t_ats_35[$i]);
        $t_ks_40_value = $conn->real_escape_string($t_ks_40[$i]);
        $t_trnms_402_value = $conn->real_escape_string($t_trnms_402[$i]);
        $t_trnms_402g_value = $conn->real_escape_string($t_trnms_402g[$i]);
        $t_trs_202_value = $conn->real_escape_string($t_trs_202[$i]);
        $grand_total_compound_weight_value = $conn->real_escape_string($grand_total_compound_weight[$i]);
        $color_value = $conn->real_escape_string($colors[$i]);
        $brand_value = $conn->real_escape_string($brands[$i]);
        $green_tire_weight_value = $conn->real_escape_string($green_tire_weights[$i]);
        
        // Create and execute the SQL INSERT query
        $sql = "INSERT INTO bom_new2 (item, icode, t_size, item_description, 
                b_ats_15, b_bns_24, bg_bls_12, cg_bs_901, c_sms_501, c_ats_20, c_sms_702, 
                t_trs_102, t_atnm_s, t_ats_30, t_ats_35, t_ks_40, t_trnms_402, t_trnms_402g, 
                t_trs_202, grand_total_compound_weight, color, brand, green_tire_weight) 
                VALUES ('$item', '$icode', '$t_size', '$item_description', 
                '$b_ats_15_value', '$b_bns_24_value', '$bg_bls_12_value', '$cg_bs_901_value', 
                '$c_sms_501_value', '$c_ats_20_value', '$c_sms_702_value', '$t_trs_102_value', 
                '$t_atnm_s_value', '$t_ats_30_value', '$t_ats_35_value', '$t_ks_40_value', 
                '$t_trnms_402_value', '$t_trnms_402g_value', '$t_trs_202_value', 
                '$grand_total_compound_weight_value', '$color_value', '$brand_value', '$green_tire_weight_value')";
        
        if ($conn->query($sql) !== true) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Close the database connection
    $conn->close();

    echo "<p>Data inserted into the database successfully!</p>";
} else {
    echo "<p>No data submitted.</p>";
}
?>
