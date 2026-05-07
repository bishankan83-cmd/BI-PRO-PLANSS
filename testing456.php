<!DOCTYPE html>
<html>
<head>
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

        h6 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            font-size: 12px;
        }

        h5 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 15px;
            padding: 5px;
            background-color: light black;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% {
                opacity: 1;
            }
            25%, 75% {
                opacity: 0;
            }
        }

        h8 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
        }

        .cargo-loading-date {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
            color: #F28018;
            padding: 5px;
            font-size: 16px;
            background-color: black;
            border: 1px dashed gray;
            border-radius: 10px;
        }

        .button-container {
            text-align: left;
        }

        .top-button {
            background-color: #F28018;
            color: #000000;
            padding: 10px 20px;
            border: none;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .label-container {
            text-align: center;
            background-color: #F28018;
            color: #000000;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
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
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
        }

        @keyframes blink {
            0% { visibility: visible; }
            50% { visibility: hidden; }
            100% { visibility: visible; }
        }

        .blinking-text {
            animation: blink 1s infinite;
        }
    
    </style>
</head>
<body>
    <!-- Button container in the top-left corner -->
    <div class="button-container">
        <button><a href="report.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a></button>
    </div>

    <!-- PHP code for displaying production data -->
    <?php
    // Establish database connection
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Check if the connection is successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Check if the ERP filter is set and not empty
    if (isset($_GET['erp']) && !empty($_GET['erp'])) {
        $erp = $_GET['erp'];
        // Modify the SQL query to include the ERP condition
        $erpSql = "SELECT erp, customer, MAX(end_date) as last_completion_date FROM new_plan_data WHERE erp = '$erp' GROUP BY erp";
    } else {
        // If the filter is not set or empty, retrieve all data
        $erpSql = "SELECT erp, customer, MAX(end_date) as last_completion_date FROM new_plan_data GROUP BY erp";
    }

    $erpResult = mysqli_query($conn, $erpSql);

    // Check if the query was successful
    if ($erpResult) {
        if (mysqli_num_rows($erpResult) > 0) {
            // Retrieve the results from the first code block
            $sumQuery = "SELECT `erp`, SUM(CASE WHEN `tobe` > 0 THEN `tobe` ELSE 0 END) AS `total_positive_amount` FROM `new_tobeplan_data` GROUP BY `erp`";
            $result = $conn->query($sumQuery);

            // Store the results in an associative array for easier access
            $totalPositiveAmounts = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $totalPositiveAmounts[$row["erp"]] = $row["total_positive_amount"];
                }
            }

            // Iterate through each ERP number
            while ($erpRow = mysqli_fetch_assoc($erpResult)) {
                $erp = $erpRow['erp'];
                $customerName = $erpRow['customer'];
                $lastCompletionDate = $erpRow['last_completion_date'];
                // Add 3 days to the last completion date for cargo loading date
                $cargoLoadingDate = date('Y-m-d', strtotime($lastCompletionDate . ' +3 days'));

                // Retrieve production plan details for the current ERP number
                if (isset($_GET['erp']) && !empty($_GET['erp'])) {
                    $sql = "SELECT * FROM new_plan_data WHERE erp = '$erp'";
                } else {
                    $sql = "SELECT * FROM new_plan_data WHERE erp = '$erp'";
                }
                $result = mysqli_query($conn, $sql);

                // Check if the query was successful
                if ($result) {
                    // Check if any production plan entries exist
                    if (mysqli_num_rows($result) > 0) {
                        // Retrieve one worder ref for the current ERP number
                        $worderSql = "SELECT ref, wono, date FROM worder WHERE erp = '$erp' LIMIT 1";
                        $worderResult = mysqli_query($conn, $worderSql);

                        if ($worderResult) {
                            if (mysqli_num_rows($worderResult) > 0) {
                                $worderRow = mysqli_fetch_assoc($worderResult);
                                $worderRef = $worderRow['ref'];
                                $wonoRef = $worderRow['wono'];
                                $dateRef = $worderRow['date'];
                            } else {
                                echo "<p>No worder details found for ERP number: $erp.</p>";
                            }
                        } else {
                            echo "Error executing worder query: " . mysqli_error($conn);
                        }

                        // Display the "Total Positive Amount" from the first code block
                        $totalPositiveAmount = isset($totalPositiveAmounts[$erp]) ? $totalPositiveAmounts[$erp] : 0;

                        // Display the ERP information along with Total Positive Amount
                        echo "<h3>Worder Ref: $worderRef - WO NO: $wonoRef</h3>";
                        echo "<h6>ERP Number: $erp<br>Work Order Release Date: $dateRef - Last Completion Date: $lastCompletionDate<br><br>";
                        echo "<span class='cargo-loading-date'>Cargo Loading Date: $cargoLoadingDate</span></h6>";
                        // Assuming $totalPositiveAmount is a PHP variable containing the amount
                        echo "<h5 id='blinkingText'>Total To Be Produced Amount: $totalPositiveAmount</h5>";
                        echo "<table class='production-table'>";
                        echo "<tr><th>Tire ID</th><th>Description</th><th>Curing Group</th><th>Press Name</th><th>Mold Name</th>
                            <th>Cavity Name</th><th>Start Date</th><th>End Date</th><th>Order Quantity</th>
                            <th>Stock On Hand</th><th>To Be Produced</th></tr>";

                        $totalOrderQuantity = 0;
                        $totalTobeValue = 0;

                        while ($row = mysqli_fetch_assoc($result)) {
                            $icode = $row['icode'];
                            $moldId = $row['mold_id'];
                            $cavityId = $row['cavity_id'];
                            $start_date = $row['start_date'];
                            $end_date = $row['end_date'];

                            // Retrieve the press name for the given press ID
                            $pressSql = "SELECT press_name FROM press WHERE press_id IN (SELECT cavity_group_id FROM cavity WHERE cavity_id = '$cavityId')";
                            $pressResult = mysqli_query($conn, $pressSql);
                            $pressName = '';

                            if ($pressResult && mysqli_num_rows($pressResult) > 0) {
                                $pressRow = mysqli_fetch_assoc($pressResult);
                                $pressName = $pressRow['press_name'];
                            }

                            // Retrieve the mold name for the given mold ID
                            $moldSql = "SELECT mold_name FROM mold WHERE mold_id = '$moldId'";
                            $moldResult = mysqli_query($conn, $moldSql);
                            $moldName = '';

                            if ($moldResult && mysqli_num_rows($moldResult) > 0) {
                                $moldRow = mysqli_fetch_assoc($moldResult);
                                $moldName = $moldRow['mold_name'];
                            }

                            // Retrieve the cavity name for the given cavity ID
                            $cavitySql = "SELECT cavity_name FROM cavity WHERE cavity_id = '$cavityId'";
                            $cavityResult = mysqli_query($conn, $cavitySql);
                            $cavityName = '';

                            if ($cavityResult && mysqli_num_rows($cavityResult) > 0) {
                                $cavityRow = mysqli_fetch_assoc($cavityResult);
                                $cavityName = $cavityRow['cavity_name'];
                            }

                            // Retrieve the description of the tire from the tire table
                            $tireSql = "SELECT description, cuing_group_name FROM tire WHERE icode = '$icode'";
                            $tireResult = mysqli_query($conn, $tireSql);
                            $description = '';
                            $curingGroup = '';

                            if ($tireResult && mysqli_num_rows($tireResult) > 0) {
                                $tireRow = mysqli_fetch_assoc($tireResult);
                                $description = $tireRow['description'];
                                $curingGroup = $tireRow['cuing_group_name'];
                            }

                            // Retrieve the tobe value for the given tire type
                            $tobeSql = "SELECT tobe FROM new_tobeplan_data WHERE icode = '$icode' AND erp = '$erp'";
                            $tobeResult = mysqli_query($conn, $tobeSql);
                            $tobeValue = '';

                            if ($tobeResult && mysqli_num_rows($tobeResult) > 0) {
                                $tobeRow = mysqli_fetch_assoc($tobeResult);
                                $tobeValue = $tobeRow['tobe'];
                            }

                            // Retrieve the cstock value for the given tire type
                            $cstockSql = "SELECT stockonhand FROM new_tobeplan_data WHERE icode = '$icode' AND erp = '$erp'";
                            $cstockResult = mysqli_query($conn, $cstockSql);
                            $cstockValue = '';

                            if ($cstockResult && mysqli_num_rows($cstockResult) > 0) {
                                $cstockRow = mysqli_fetch_assoc($cstockResult);
                                $cstockValue = $cstockRow['stockonhand'];
                            }

                            // Retrieve the new value for the given tire type
                            $newSql = "SELECT new FROM worder WHERE icode = '$icode' AND erp = '$erp'";
                            $newResult = mysqli_query($conn, $newSql);
                            $newValue = '';

                            if ($newResult && mysqli_num_rows($newResult) > 0) {
                                $newRow = mysqli_fetch_assoc($newResult);
                                $newValue = $newRow['new'];
                            }

                            // Update the total order quantity and total tobe value
                            $totalOrderQuantity += $newValue; // $newValue is the order quantity for the current entry
                            $totalTobeValue += $tobeValue; // $tobeValue is the "to be produced" value for the current entry
                            if ($tobeValue > 0) {
                                echo "<tr>";
                                echo "<td>$icode</td>";
                                echo "<td>$description</td>";
                                echo "<td>$curingGroup</td>";
                                echo "<td>$pressName</td>";
                                echo "<td>$moldName</td>";
                                echo "<td>$cavityName</td>";
                                echo "<td>$start_date</td>";
                                echo "<td>$end_date</td>";
                                echo "<td>$newValue</td>";
                                echo "<td>$cstockValue</td>";
                                echo "<td>$tobeValue</td>";
                                echo "</tr>";
                            }
                        }

                        echo "</table>";
                    } else {
                        echo "No production plan details found for ERP number: $erp.";
                    }
                } else {
                    echo "Error executing query: " . mysqli_error($conn);
                }
            }
        }
    }

    mysqli_close($conn);
    ?>
</body>
</html>



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

        h6 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            font-size: 12px;
        }

        h5 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 15px;
            padding: 5px;
            background-color: light black;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% {
                opacity: 1;
            }
            25%, 75% {
                opacity: 0;
            }
        }

        h8 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
        }

        .cargo-loading-date {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
            color: #F28018;
            padding: 5px;
            font-size: 16px;
            background-color: black;
            border: 1px dashed gray;
            border-radius: 10px;
        }

        .button-container {
            text-align: left;
        }

        .top-button {
            background-color: #F28018;
            color: #000000;
            padding: 10px 20px;
            border: none;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .label-container {
            text-align: center;
            background-color: #F28018;
            color: #000000;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
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
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
        }

        @keyframes blink {
            0% { visibility: visible; }
            50% { visibility: hidden; }
            100% { visibility: visible; }
        }

        .blinking-text {
            animation: blink 1s infinite;
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

        // Check if the ERP filter is set and not empty
        if (isset($_GET['erp']) && !empty($_GET['erp'])) {
            $erp = $_GET['erp'];
            // Retrieve data from worder table for the specified ERP
            $worderSql = "SELECT * FROM worder WHERE erp = '$erp'";
            $worderResult = mysqli_query($conn, $worderSql);

            // Check if any worder entries exist
            if ($worderResult && mysqli_num_rows($worderResult) > 0) {
                // Display worder details in a table
                echo "<h3>Worder Details for ERP: $erp</h3>";
                echo "<table class='production-table'>";
                echo "<tr>
                          <th>ERP</th>
                          <th>ICode</th>
                          <th>Size</th>
                          <th>Brand</th>
                          <th>Color</th>
                          <th>Fit</th>
                          <th>Rim</th>
                          <th>Cons</th>
                          <th>Weight</th>
                          <th>PTV</th>
                          <th>New</th>
                          <th>CBM</th>
                          <th>KGS</th>
                      </tr>";

                // Loop through worder results and display each row
                while ($row = mysqli_fetch_assoc($worderResult)) {
                    echo "<tr>";
                    echo "<td>{$row['erp']}</td>";
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
            } else {
                echo "<p class='blinking-text'>No worder details found for ERP number: $erp.</p>";
            }
        } else {
            echo "<p>No ERP number provided.</p>";
        }

        // Close database connection
        mysqli_close($conn);
        ?>
    </div>
</body>
</html>


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

        h6 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            font-size: 12px;
        }

        h5 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 15px;
            padding: 5px;
            background-color: light black;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% {
                opacity: 1;
            }
            25%, 75% {
                opacity: 0;
            }
        }

        h8 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
        }

        .cargo-loading-date {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
            color: #F28018;
            padding: 5px;
            font-size: 16px;
            background-color: black;
            border: 1px dashed gray;
            border-radius: 10px;
        }

        .button-container {
            text-align: left;
        }

        .top-button {
            background-color: #F28018;
            color: #000000;
            padding: 10px 20px;
            border: none;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .label-container {
            text-align: center;
            background-color: #F28018;
            color: #000000;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
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
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
        }

        @keyframes blink {
            0% { visibility: visible; }
            50% { visibility: hidden; }
            100% { visibility: visible; }
        }

        .blinking-text {
            animation: blink 1s infinite;
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

        // Check if the ERP filter is set and not empty
        if (isset($_GET['erp']) && !empty($_GET['erp'])) {
            $erp = $_GET['erp'];
            // Retrieve data from worder table for the specified ERP
            $worderSql = "SELECT * FROM dwork2 WHERE erp = '$erp'";
            $worderResult = mysqli_query($conn, $worderSql);

            // Check if any worder entries exist
            if ($worderResult && mysqli_num_rows($worderResult) > 0) {
                // Display worder details in a table
                echo "<h3>Worder Details for ERP: $erp</h3>";
                echo "<table class='production-table'>";
                echo "<tr>
                          <th>ERP</th>
                          <th>ICode</th>
                          <th>Size</th>
                          <th>Brand</th>
                          <th>Color</th>
                          <th>Fit</th>
                          <th>Rim</th>
                          <th>Cons</th>
                          <th>Weight</th>
                          <th>PTV</th>
                          <th>New</th>
                          <th>CBM</th>
                          <th>KGS</th>
                      </tr>";

                // Loop through worder results and display each row
                while ($row = mysqli_fetch_assoc($worderResult)) {
                    echo "<tr>";
                    echo "<td>{$row['erp']}</td>";
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
            } else {
                echo "<p class='blinking-text'>No worder details found for ERP number: $erp.</p>";
            }
        } else {
            echo "<p>No ERP number provided.</p>";
        }

        // Close database connection
        mysqli_close($conn);
        ?>
    </div>
</body>
</html>
