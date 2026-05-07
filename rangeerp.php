<!DOCTYPE html>
<html>
<head>
    <title>Production Plan Details</title>
    <style>
             body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
        }

        h1 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
        }

        th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #ECECEC;
        }

        .erp-window {
            text-align: left;
        }

        .erp-window span {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
        }

        .erp-window .green {
            color: #000000;
            font-weight: bold;
        }

        .erp-window .red {
            color: #F28018;
            font-weight: bold;
        }

        

        @keyframes blink {
    0% {
        opacity: 0;
    }
    50% {
        opacity: 2;
    }
    100% {
        opacity: 1;
    }
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

    </style>
    
</head>
<body>
<div class="button-container">
        <button><a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To dashboard</a></button>
    </div>
    <form method="get" action="">
        ERP Range: <input type="text" name="start_erp" placeholder="Start ERP">
        to <input type="text" name="end_erp" placeholder="End ERP">
        <input type="submit" value="Submit">
    </form>

  

    <?php

error_reporting(E_ERROR | E_PARSE);
    // Check if the form is submitted with the ERP range
    if (isset($_GET['start_erp']) && isset($_GET['end_erp'])) {
        // Establish database connection
        $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

        // Check if the connection is successful
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $start_erp = $_GET['start_erp'];
        $end_erp = $_GET['end_erp'];

        // Modify the ERP query to get ERP numbers within the specified range
        $erpSql = "SELECT DISTINCT erp FROM plannew WHERE erp BETWEEN '$start_erp' AND '$end_erp'";
        $erpResult = mysqli_query($conn, $erpSql);

        // Check if the query was successful
        if ($erpResult) {

             // Check if any ERP numbers exist
        if (mysqli_num_rows($erpResult) > 0) {
            // Retrieve all work orders
            $workOrderSql = "SELECT DISTINCT erp, icode, new FROM worder";
            $workOrderResult = mysqli_query($conn, $workOrderSql);

            // Check if the query was successful
            if ($workOrderResult) {
                // Create an array to store work order data
                $workOrders = [];

                // Iterate through each work order
                while ($workOrderRow = mysqli_fetch_assoc($workOrderResult)) {
                    $erp = $workOrderRow['erp'];
                    $icode = $workOrderRow['icode'];
                    $new = $workOrderRow['new'];

                    // Set the new value related to each tire type
                    if (!isset($workOrders[$icode])) {
                        $workOrders[$icode] = [];
                    }
                    $workOrders[$icode][$erp]['new'] = $new;
                }

               

                foreach ($workOrders as $icode => $workOrderData) {
                    $total = 0;
                    foreach ($workOrderData as $erpData) {
                        $total += $erpData['new'];
                    }
                    $totals[$icode] = $total;
                }

                // Display the production plan details in a table
                echo "<table class='production-table'>";
                echo "<tr><th>Tire ID</th>";
                echo "<th>Description</th>";
            
                echo "<th>Stock on Hand</th>"; // New column for Stock on Hand

                // Add a header for the total requirement column
                echo "<th>Total Requirement</th>";
                // Display the ERP numbers horizontally
                while ($erpRow = mysqli_fetch_assoc($erpResult)) {
                    $erp = $erpRow['erp'];
                    echo "<th>ERP Number: $erp</th>";
                }

                echo "</tr>";

                // Display the work order data vertically
                foreach ($workOrders as $icode => $workOrderData) {
                    echo "<tr>";
                    echo "<td>$icode</td>";

                    // Fetch the ERP numbers again for the inner loop
                    $erpResult = mysqli_query($conn, $erpSql);

                    // Retrieve Brand, Color, Curing Time, and Curing Group from the selectpress table
                    $selectPressSql = "SELECT description FROM tire WHERE icode = '$icode'";
                    $selectPressResult = mysqli_query($conn, $selectPressSql);

                    if ($selectPressResult) {
                        $selectPressRow = mysqli_fetch_assoc($selectPressResult);
                     
                       
                      
                        $description = $selectPressRow['description'];

                        // Display the tire description, brand, color, curing time, and curing group in separate columns
                        echo "<td>$description</td>";
                        

                        // Retrieve the suitable amount of cstock from the realstock table
                        $realStockSql = "SELECT cstock FROM realstock WHERE icode = '$icode'";
                        $realStockResult = mysqli_query($conn, $realStockSql);

                        if ($realStockResult) { 
                            $realStockRow = mysqli_fetch_assoc($realStockResult);
                            $stockOnHand = $realStockRow['cstock'];

                            
                            // Display the stock on hand in a separate column
                            echo "<td>$stockOnHand</td>";

                            // Display the total requirement in a separate column
                            $totalRequirement = isset($totals[$icode]) ? $totals[$icode] : "";
                            echo "<td>$totalRequirement</td>";

                            foreach ($erpResult as $erpRow) {
                                $erp = $erpRow['erp'];
                                $new = isset($workOrderData[$erp]['new']) ? $workOrderData[$erp]['new'] : "";
                                $tobe = "";
                            
                                // Retrieve the "tobe" value from the tobeplan1 table
                                $tobeSql = "SELECT tobe FROM tobeplan1 WHERE erp = '$erp' AND icode = '$icode'";
                                $tobeResult = mysqli_query($conn, $tobeSql);
                            
                                if ($tobeResult && mysqli_num_rows($tobeResult) > 0) {
                                    $tobeRow = mysqli_fetch_assoc($tobeResult);
                                    $tobe = $tobeRow['tobe'];
                                }
                            
                                echo "<td>";
                                echo "<div class='erp-window'>";
                                echo "<span class='" . ($new > 0 ? 'green' : '') . "'>Order Quantity: $new</span><br>";
                                echo "<span class='" . ($tobe > 0 ? 'red' : '') . "'>Tobe: $tobe</span><br>";
                                echo "</div>";
                                echo "</td>";
                            }
                            
                        } else {
                            echo "Error executing realstock query: " . mysqli_error($conn);
                        }
                    } else {
                        echo "Error executing selectpress query: " . mysqli_error($conn);
                    }

                    echo "</tr>";
                }

                echo "</table>";
            // Close the database connection
            mysqli_close($conn);
        } else {
            echo "No ERP numbers found in the database for the specified range.";
        }
    } else {
        echo "Error executing ERP query: " . mysqli_error($conn);
    }
}}
?>
</body>
</html>
  
