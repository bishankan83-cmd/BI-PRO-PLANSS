<!DOCTYPE html>
<html>
<head>
    <title>Production Plan Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 20px;
        }

        /* ... your existing styles ... */
    </style>
</head>
<body>
    <h1>Production Plan Details</h1>
    <form action="erprange2.php" method="get">
        <label for="icode">Enter iCode:</label>
        <input type="text" id="icode" name="icode">
        <button type="submit">Submit</button>
    </form>
    <?php
    // Establish database connection
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Check if the connection is successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Retrieve all unique ERP numbers from the "worder" table
    $erpSql = "SELECT DISTINCT erp FROM worder";
    $erpResult = mysqli_query($conn, $erpSql);

    // Check if the query was successful
    if ($erpResult) {
        // Check if any ERP numbers exist
        if (mysqli_num_rows($erpResult) > 0) {
            // Store ERP numbers in an array
            $erpNumbers = array();
            while ($erpRow = mysqli_fetch_assoc($erpResult)) {
                $erpNumbers[] = $erpRow['erp'];
            }

            // Retrieve corresponding "ref" values from the "worder" table
            $refSql = "SELECT erp, ref FROM worder";
            $refResult = mysqli_query($conn, $refSql);

            // Check if the query was successful
            if ($refResult) {
                // Store "ref" values in an associative array where "erp" is the key
                $erpRefMap = array();
                while ($refRow = mysqli_fetch_assoc($refResult)) {
                    $erpRefMap[$refRow['erp']] = $refRow['ref'];
                }

                // ... Your existing code ...

                // Display the ERP numbers horizontally along with their corresponding REF values
                echo "<table class='production-table'>";
                echo "<tr><th>Tire ID</th>";
                echo "<th>Stock on Hand</th>";
                echo "<th>Total Tobe</th>";
                echo "<th>Total Requirement</th>";

                // Display the ERP numbers and their corresponding REF values
                foreach ($erpNumbers as $erp) {
                    $ref = isset($erpRefMap[$erp]) ? $erpRefMap[$erp] : "";
                    echo "<th>ERP Number: $erp (REF: $ref)</th>";
                }

                echo "</tr>";

                // ... Your existing code ...

                echo "</table>";
            } else {
                echo "Error executing REF query: " . mysqli_error($conn);
            }
        } else {
            echo "No ERP numbers found in the database.";
        }
    } else {
        echo "Error executing ERP query: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
    ?>
</body>
</html>
