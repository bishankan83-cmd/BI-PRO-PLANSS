<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Report</title>
</head>
<body>
    <h1>Date Range Report</h1>
    <form method="post" action="">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <input type="submit" value="Generate Report">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // MySQLi connection
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $database = "planatir_task_managemen";

        // Get start and end dates from the form
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT dp.Date,
                       dp.Icode,
                       SUM(dp.AdditionalData) AS total_plan,
                       SUM(CASE WHEN bn.icode IS NULL THEN dp.AdditionalData ELSE 0 END) AS not_in_bom
                FROM daily_plan_data dp
                LEFT JOIN bom_new bn ON dp.Icode = bn.icode
                WHERE dp.Date BETWEEN '$start_date' AND '$end_date'
                GROUP BY dp.Date, dp.Icode
                ORDER BY dp.Date, dp.Icode";

        // Execute query
        $result = $conn->query($sql);

        // Check if there are any results
        if ($result->num_rows > 0) {
            // Set header for CSV file
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="data_export.csv"');

            // Create a file pointer connected to the output stream
            $output = fopen('php://output', 'w');

            // Output CSV column headers
            fputcsv($output, array('Date', 'Icode', 'Total Plan', 'Not in BOM'));

            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }

            // Close file pointer
            fclose($output);
            
            // Stop script execution
            exit();
        } else {
            echo "No data available for the selected date range.";
        }

        // Close connection
        $conn->close();
    }
    ?>
</body>
</html>
