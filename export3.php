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

// Export filtered data to TSV when the form is submitted
if (isset($_POST['export']) && $_POST['export'] == 'true') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $compoundName = $_POST['compound_name'];
    $serialNumber = $_POST['serial_number'];
    $batchFrom = $_POST['batch_from'];
    $batchTo = $_POST['batch_to'];

    // Query to filter the data based on user input including the date range
    $sql = "SELECT * FROM another_table_name1 
            WHERE inputDate BETWEEN '$startDate' AND '$endDate'
            AND compound_name = '$compoundName' 
            AND serial_number = '$serialNumber' 
            AND batch BETWEEN '$batchFrom' AND '$batchTo'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Set headers to indicate the response is a downloadable TSV file
        header('Content-Type: text/tab-separated-values');
        header('Content-Disposition: attachment;filename="filtered_data.tsv"');
        header('Cache-Control: max-age=0');

        // Output column headers in TSV format
        echo "Input Date\tSerial Number\tBatch\tMH\tML\tT10\tT90\tT52\tHardness\tSG Value\tRebound\n";

        // Output data rows in TSV format
        while ($row = $result->fetch_assoc()) {
            echo "{$row['inputDate']}\t{$row['serial_number']}\t{$row['batch']}\t{$row['mh']}\t{$row['ml']}\t{$row['t10']}\t{$row['t90']}\t{$row['t52']}\t{$row['hardness']}\t{$row['sg_value']}\t{$row['rebound']}\n";
        }
    } else {
        echo "No records found for the selected criteria.";
    }
} else {
    // Display the form to allow users to filter the data
    echo '<form method="POST" action="">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required><br>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required><br>

            <label for="compound_name">Compound Name:</label>
            <input type="text" id="compound_name" name="compound_name" required><br>

            <label for="serial_number">Serial Number:</label>
            <input type="number" id="serial_number" name="serial_number" required><br>

            <label for="batch_from">Batch From:</label>
            <input type="text" id="batch_from" name="batch_from" required><br>

            <label for="batch_to">Batch To:</label>
            <input type="text" id="batch_to" name="batch_to" required><br>

            <input type="hidden" name="export" value="true">
            <input type="submit" value="Export Data">
        </form>';
}

// Close the connection
$conn->close();
?>
