<!DOCTYPE html>
<html>
<head>
    <title>Date Range Query</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Enter Date Range</h2>
    <form method="post" action="">
        Start Date: <input type="date" name="start_date" required>
        End Date: <input type="date" name="end_date" required>
        <input type="submit" name="submit" value="Submit">
    </form>

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

    // SQL query to retrieve data from dwork2 table based on erp numbers in the given date range from the pros table
    $sql = "SELECT *
            FROM dwork2
            WHERE erp IN (SELECT DISTINCT erp_number FROM pros WHERE dispatch_date BETWEEN '$start_date' AND '$end_date')";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Initialize an array to store grouped data and sum of quantity for each ERP
        $grouped_data = array();
        $erp_total_quantity = array();

        // Group data by 'erp'
        while ($row = $result->fetch_assoc()) {
            $erp = $row['erp'];
            $grouped_data[$erp][] = $row;
            // Initialize sum of quantity for each ERP if not already set
            if (!isset($erp_total_quantity[$erp])) {
                $erp_total_quantity[$erp] = 0;
            }
            // Add quantity to sum
            $erp_total_quantity[$erp] += $row['quantity'];
        }

        // Output grouped data and sum of quantity
        echo "<h2>Data:</h2>";
        foreach ($grouped_data as $erp => $rows) {
            echo "<h3>ERP: $erp - Total Quantity: " . $erp_total_quantity[$erp] . "</h3>";
            echo "<table>";
            echo "<tr>";
            // Output column headers
            echo "<th>Customer</th>";
            echo "<th>wono</th>";
            echo "<th>ref</th>";
            echo "<th>icode</th>";
            echo "<th>t_size</th>";
            echo "<th>brand</th>";
            echo "<th>col</th>";
            echo "<th>fit</th>";
            echo "<th>rim</th>";
            echo "<th>cons</th>";
            echo "<th>fweight</th>";
            echo "<th>ptv</th>";
            echo "<th>new</th>";
            echo "<th>cbm</th>";
            echo "<th>kgs</th>";
            echo "<th>quantity</th>";
            echo "</tr>";
            // Output data rows
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>" . $row['Customer'] . "</td>";
                echo "<td>" . $row['wono'] . "</td>";
                echo "<td>" . $row['ref'] . "</td>";
                echo "<td>" . $row['icode'] . "</td>";
                echo "<td>" . $row['t_size'] . "</td>";
                echo "<td>" . $row['brand'] . "</td>";
                echo "<td>" . $row['col'] . "</td>";
                echo "<td>" . $row['fit'] . "</td>";
                echo "<td>" . $row['rim'] . "</td>";
                echo "<td>" . $row['cons'] . "</td>";
                echo "<td>" . $row['fweight'] . "</td>";
                echo "<td>" . $row['ptv'] . "</td>";
                echo "<td>" . $row['new'] . "</td>";
                echo "<td>" . $row['cbm'] . "</td>";
                echo "<td>" . $row['kgs'] . "</td>";
                echo "<td>" . $row['quantity'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            // Button to export data for each ERP
            echo '<form method="post" action="export_work.php">';
            echo '<input type="hidden" name="erp" value="' . $erp . '">';
            echo '<input type="hidden" name="start_date" value="' . $start_date . '">';
            echo '<input type="hidden" name="end_date" value="' . $end_date . '">';
            echo '<input type="submit" value="Export ' . $erp . ' to Excel">';
            echo '</form>';
        }
    } else {
        echo "<p>No data found within the specified date range.</p>";
    }

    $conn->close();
}
?>

</body>
</html>
