<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection parameters
    $hostname = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    // Create connection
    $conn = new mysqli($hostname, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get start date and end date from the form submission
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // SQL query to retrieve data for all ERPs in the specified date range
    $sql = "SELECT *
            FROM dwork2
            WHERE erp IN (SELECT DISTINCT erp_number FROM pros WHERE dispatch_date BETWEEN '$start_date' AND '$end_date')";

    $result = $conn->query($sql);

    // Set headers for Excel download
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"erp_data.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output headers
    echo "ERP\tReference\tCustomer\tWono\tIcode\tT_size\tBrand\tCol\tFit\tRim\tCons\tFweight\tPtv\tNew\tCbm\tKgs\tQuantity\n";

    // Output data rows
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output = array();
            $output[] = $row['erp'];
            $output[] = $row['ref'];
            $output[] = $row['Customer'];
            $output[] = $row['wono'];
            $output[] = $row['icode'];
            $output[] = $row['t_size'];
            $output[] = $row['brand'];
            $output[] = $row['col'];
            $output[] = $row['fit'];
            $output[] = $row['rim'];
            $output[] = $row['cons'];
            $output[] = $row['fweight'];
            $output[] = $row['ptv'];
            $output[] = $row['new'];
            $output[] = $row['cbm'];
            $output[] = $row['kgs'];
            $output[] = $row['quantity'];

            // Output the row as a tab-separated string
            echo implode("\t", $output) . "\n";
        }
    } else {
        echo "No data found for the selected date range.";
    }

    $conn->close();
}
?>
