<?php include 'includes/checkauthenticator.php'; ?>

<!DOCTYPE html>
<html>
<head>
<script>
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            window.location.href = 'planbuttoon.php';
        }
    });
</script>
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
        background-color: #000000; /* Fixed 'light black' to valid color */
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

    .cargo-loading-date {
        font-family: 'Open Sans', sans-serif;
        font-weight: normal;
        color: #F28018;
        padding: 5px;
        font-size: 16px;
        background-color: #000000;
        border: 1px dashed gray;
        border-radius: 10px;
    }

    .button-container {
        text-align: left;
        margin: 10px;
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

    .button-container button {
        background-color: #000000;
        color: #FFFFFF;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        border-radius: 40px;
    }

    .button-container-2 {
        text-align: center;
        margin-top: 20px;
    }

    .button-container-2 button {
        background-color: #F28018;
        color: #ffffff;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-family: 'Cantarell', sans-serif;
        font-weight: bold;
        font-size: 16px;
    }

    .button-container-2 button:hover {
        background-color: #ff9933;
    }
</style>
</head>
<body>
    <div class="button-container">
        <button onclick="window.location.href = 'dashboard.php';">Click to dashboard</button>
    </div>

    <div class="button-container-2">
        <button onclick="window.location.href = 'set_dis.php';">Check full order view</button>
    </div>

    <div class="button-container-2">
        <button onclick="window.location.href = 'set_dis_all.php';">Check Full Summary</button>
    </div>

    <?php
    include './includes/data_base_save_update.php';
    include 'includes/App_Code.php';

    // Establish database connection
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Check if the connection is successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Retrieve all unique ERP numbers with customer name
    $erpSql = "SELECT erp, customer FROM dwork2 GROUP BY erp";
    $erpResult = mysqli_query($conn, $erpSql);

    // Check if the query was successful
    if ($erpResult) {
        if (mysqli_num_rows($erpResult) > 0) {
            // Iterate through each ERP number
            while ($erpRow = mysqli_fetch_assoc($erpResult)) {
                $erp = mysqli_real_escape_string($conn, $erpRow['erp']);
                $customerName = htmlspecialchars($erpRow['customer']);

                // Retrieve production plan details for the current ERP number
                $sql = "SELECT * FROM dwork2 WHERE erp = '$erp'";
                $result = mysqli_query($conn, $sql);

                // Check if the query was successful
                if ($result) {
                    // Check if any production plan entries exist
                    if (mysqli_num_rows($result) > 0) {
                        // Retrieve one worder ref for the current ERP number
                        $worderSql = "SELECT ref, wono, date FROM dwork2 WHERE erp = '$erp' LIMIT 1";
                        $worderResult = mysqli_query($conn, $worderSql);

                        if ($worderResult && mysqli_num_rows($worderResult) > 0) {
                            $worderRow = mysqli_fetch_assoc($worderResult);
                            $worderRef = htmlspecialchars($worderRow['ref']);
                            $wonoRef = htmlspecialchars($worderRow['wono']);
                            $dateRef = htmlspecialchars($worderRow['date']);
                        } else {
                            $worderRef = "N/A";
                            $wonoRef = "N/A";
                            $dateRef = "N/A";
                        }

                        // Retrieve dispatch date from pros table for each ERP number
                        $dispatchDateSql = "SELECT dispatch_date FROM pros WHERE erp_number = '$erp'";
                        $dispatchDateResult = mysqli_query($conn, $dispatchDateSql);

                        if ($dispatchDateResult && mysqli_num_rows($dispatchDateResult) > 0) {
                            $dispatchDateRow = mysqli_fetch_assoc($dispatchDateResult);
                            $cargoLoadingDate = htmlspecialchars($dispatchDateRow['dispatch_date']);
                        } else {
                            $cargoLoadingDate = "Dispatch date not available";
                        }

                        // Display ERP information
                        echo "<h3>Worder Ref: $worderRef - WO NO: $wonoRef</h3>";
                        echo "<h6>ERP Number: $erp<br>Work Order Release Date: $dateRef</h6>";
                        echo "<span class='cargo-loading-date'>Date of shipment: $cargoLoadingDate</span>";

                        echo "<table class='production-table'>";
                        echo "<tr>
                            <th>Item Code</th>
                            <th>Tire Size</th>
                            <th>Brand</th>
                            <th>Colour</th>
                            <th>FIT</th>
                            <th>Rim</th>
                            <th>Construction</th>
                            <th>Avg Finish Tyre Weight (kgs)</th>
                            <th>Per Volume (cbm)</th>
                            <th>Qty New pcs</th>
                            <th>Total Volume (cbm)</th>
                            <th>Total Tones (kgs)</th>
                            <th>Actual Pcs</th>
                        </tr>";

                        // Loop through the production plan entries and display data in the table
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Convert numeric fields to float and format
                            $fweight = is_numeric($row['fweight']) ? number_format((float)$row['fweight'], 2) : $row['fweight'];
                            $ptv = is_numeric($row['ptv']) ? number_format((float)$row['ptv'], 4) : $row['ptv'];
                            $cbm = is_numeric($row['cbm']) ? number_format((float)$row['cbm'], 4) : $row['cbm'];
                            $kgs = is_numeric($row['kgs']) ? number_format((float)$row['kgs'], 2) : $row['kgs'];

                            echo "<tr>
                                <td>" . htmlspecialchars($row['icode']) . "</td>
                                <td>" . htmlspecialchars($row['t_size']) . "</td>
                                <td>" . htmlspecialchars($row['brand']) . "</td>
                                <td>" . htmlspecialchars($row['col']) . "</td>
                                <td>" . htmlspecialchars($row['fit']) . "</td>
                                <td>" . htmlspecialchars($row['rim']) . "</td>
                                <td>" . htmlspecialchars($row['cons']) . "</td>
                                <td>$fweight</td>
                                <td>$ptv</td>
                                <td>" . htmlspecialchars($row['new']) . "</td>
                                <td>$cbm</td>
                                <td>$kgs</td>
                                <td>" . htmlspecialchars($row['quantity']) . "</td>
                            </tr>";
                        }

                        echo "</table>";
                    } else {
                        echo "<p>No production plan entries found for ERP number: $erp.</p>";
                    }
                } else {
                    echo "<p>Error executing production plan query: " . mysqli_error($conn) . "</p>";
                }
            }
        } else {
            echo "<p>No ERP numbers found.</p>";
        }
    } else {
        echo "<p>Error executing ERP query: " . mysqli_error($conn) . "</p>";
    }

    // Close the database connection
    mysqli_close($conn);
    ?>
</body>
</html>