<?php
// Database connection details
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

// AJAX request to fetch options based on selected date
if (isset($_GET['fetch_options']) && $_GET['fetch_options'] == 'true') {
    $inputDate = $_GET['inputDate'];
    $response = [];

    // Fetch Compound Names for the selected date
    $sql = "SELECT DISTINCT compound_name FROM another_table_name1 WHERE inputDate = '$inputDate'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $response['compound_name'][] = $row['compound_name'];
    }

    // Fetch Serial Numbers for the selected date
    $sql = "SELECT DISTINCT serial_number FROM another_table_name1 WHERE inputDate = '$inputDate'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $response['serial_number'][] = $row['serial_number'];
    }

    // Fetch Pallets for the selected date
    $sql = "SELECT DISTINCT pallet FROM another_table_name1 WHERE inputDate = '$inputDate'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $response['pallet'][] = $row['pallet'];
    }

    echo json_encode($response);
    exit;
}

// AJAX request to fetch batch range based on filters
if (isset($_GET['fetch_batch_range']) && $_GET['fetch_batch_range'] == 'true') {
    $inputDate = $_GET['inputDate'];
    $compoundName = $_GET['compound_name'];
    $serialNumber = $_GET['serial_number'];
    $response = [];

    $whereClauses = [];
    if ($inputDate) $whereClauses[] = "inputDate = '$inputDate'";
    if ($compoundName) $whereClauses[] = "compound_name = '$compoundName'";
    if ($serialNumber) $whereClauses[] = "serial_number = '$serialNumber'";

    $whereCondition = implode(" AND ", $whereClauses);
    $sql = "SELECT MIN(batch) AS batch_from, MAX(batch) AS batch_to 
            FROM another_table_name1 
            WHERE $whereCondition";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $response['batch_from'] = $row['batch_from'] ? $row['batch_from'] : '';
        $response['batch_to'] = $row['batch_to'] ? $row['batch_to'] : '';
    }

    echo json_encode($response);
    exit;
}

// Export data to Excel
if (isset($_POST['export']) && $_POST['export'] == 'true') {
    $inputDate = $_POST['inputDate'];
    $serialNumber = $_POST['serial_number'];
    $batchFrom = $_POST['batch_from'];
    $batchTo = $_POST['batch_to'];

    // Fetch data based on selected filters
    $sql = "SELECT inputDate, serial_number, batch, mh, ml, t10, t90, t52, hardness, sg_value, rebound 
            FROM another_table_name1 
            WHERE inputDate = '$inputDate' 
            AND serial_number = '$serialNumber' 
            AND batch BETWEEN '$batchFrom' AND '$batchTo'";
    $result = $conn->query($sql);

    // Create Excel file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="filtered_data.xls"');
    header('Cache-Control: max-age=0');

    // Output Excel file content
    echo "Input Date\tSerial Number\tBatch\tMH\tML\tT10\tT90\tT52\tHardness\tSG Value\tRebound\n";
    while ($row = $result->fetch_assoc()) {
        echo "{$row['inputDate']}\t{$row['serial_number']}\t{$row['batch']}\t{$row['mh']}\t{$row['ml']}\t{$row['t10']}\t{$row['t90']}\t{$row['t52']}\t{$row['hardness']}\t{$row['sg_value']}\t{$row['rebound']}\n";
    }

    exit;
}

$conn->close();
?>
