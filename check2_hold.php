
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Data</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
            background-color: #ffffff;
        }

        h3 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
        }

        .production-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .production-table th, .production-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .production-table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Establish database connection
        $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

        // Check if the connection is successful
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Retrieve distinct ERP numbers from worder table
        $erpSql = "SELECT DISTINCT erp FROM worder72";
        $erpResult = mysqli_query($conn, $erpSql);

        // Check if any ERP numbers exist
        if ($erpResult && mysqli_num_rows($erpResult) > 0) {
            // Loop through ERP numbers
            while ($erpRow = mysqli_fetch_assoc($erpResult)) {
                $erp = $erpRow['erp'];
                // Retrieve data from worder table for the specified ERP
                $worderSql = "SELECT * FROM worder72 WHERE erp = '$erp'";
                $worderResult = mysqli_query($conn, $worderSql);

                // Display worder details in a table for each ERP
                echo "<h3>Worder Details for ERP: $erp</h3>";
                echo "<table class='production-table'>";
                echo "<tr>
                          <th>ICode</th>
                          <th>Size</th>
                          <th>Brand</th>
                          <th>Color</th>
                          <th>Fit</th>
                          <th>Rim</th>
                          <th>Cons</th>
                          <th>Weight</th>
                          <th>PTV</th>
                          <th>Oder Quantity</th>
                          <th>CBM</th>
                          <th>KGS</th>
                      </tr>";

                // Loop through worder results and display each row
                while ($row = mysqli_fetch_assoc($worderResult)) {
                    echo "<tr>";
                    echo "<td>{$row['icode']}</td>";
                    echo "<td>{$row['t_size']}</td>";
                    echo "<td>{$row['brand']}</td>";
                    echo "<td>{$row['col']}</td>";
                    echo "<td>{$row['fit']}</td>";
                    echo "<td>{$row['rim']}</td>";
                    echo "<td>{$row['cons']}</td>";
                    echo "<td>{$row['fweight']}</td>";
                    echo "<td>{$row['ptv']}</td>";
                    echo "<td>{$row['new']}</td>";
                    echo "<td>{$row['cbm']}</td>";
                    echo "<td>{$row['kgs']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p>No worder details found.</p>";
        }

        // Close database connection
        mysqli_close($conn);
        ?>
    </div>
</body>
</html>
