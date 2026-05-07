
<?php
// Database connection
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if data exists in the process table
$sql = "SELECT COUNT(*) as count FROM process";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    // If data exists, display the message with improved styling
    if ($count > 0) {
        echo '
        <div style="max-width: 600px; margin: 20px auto; background-color: #f8f9fa; border-left: 5px solid #F28018; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center;">
                <div style="margin-right: 15px;">
                    <i class="fas fa-sync fa-spin" style="font-size: 24px; color:rgb(0, 13, 15);"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: #F28018; font-weight: 600;">System Notice</h4>
                    <p style="margin: 10px 0 0; font-size: 16px;">The Planning Department is currently updating the Work Order Plan. Please check back soon for the latest information. Thank you for your patience</p>
                </div>
            </div>
        </div>';
    }
}

// Close connection
$conn->close();
?>

<!-- Include Font Awesome for the spinning icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">













<?php

$servername = "localhost"; // Assuming the default port for MySQL is 3306
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Connect to your database
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the SQL query
    $sql = "UPDATE plannew
            JOIN press_cavity ON plannew.cavity_id = press_cavity.cavity_id
            JOIN press ON press_cavity.press_id = press.press_id
            SET plannew.press_name = press.press_name";

    // Execute the SQL query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo "Update successful!";
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . $e->getMessage();
}
?>








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
            background-color: light black;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0; }
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
    </style>
</head>
<body>
    <!-- Button container in the top-left corner -->
    <div class="button-container">
        <button><a href="planbuttoon.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a></button>
    </div>

    <!-- Updated form for filtering by press_name, mold_id, and tire_id -->
    <div class="label-container">
        <form method="GET" action="">
            <label for="press_name">Select Press Name:</label>
            <select name="press_name" id="press_name">
                <option value="">All Press Names</option>
                <?php
                // Establish database connection
                $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");
                // Retrieve and display press names from the database
                $sql = "SELECT DISTINCT press_name FROM plannew";
                $result = mysqli_query($conn, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = isset($_GET['press_name']) && $_GET['press_name'] == $row['press_name'] ? 'selected' : '';
                        echo "<option value='" . $row['press_name'] . "' $selected>" . $row['press_name'] . "</option>";
                    }
                }
                ?>
            </select>

            <label for="mold_id">Select Mold ID:</label>
            <select name="mold_id" id="mold_id">
                <option value="">All Mold IDs</option>
                <?php
                // Retrieve and display mold IDs from the database
                $sql = "SELECT DISTINCT mold_id FROM plannew";
                $result = mysqli_query($conn, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = isset($_GET['mold_id']) && $_GET['mold_id'] == $row['mold_id'] ? 'selected' : '';
                        echo "<option value='" . $row['mold_id'] . "' $selected>" . $row['mold_id'] . "</option>";
                    }
                }
                ?>
            </select>

            <label for="tire_id">Select Tire ID:</label>
            <select name="tire_id" id="tire_id">
                <option value="">All Tire IDs</option>
                <?php
                // Retrieve and display tire IDs from the database
                $sql = "SELECT DISTINCT icode FROM plannew";
                $result = mysqli_query($conn, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = isset($_GET['tire_id']) && $_GET['tire_id'] == $row['icode'] ? 'selected' : '';
                        echo "<option value='" . $row['icode'] . "' $selected>" . $row['icode'] . "</option>";
                    }
                }
                ?>
            </select>

            <input type="submit" value="Filter">
        </form>
    </div>

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Styled Button Navigation</title>
    <style>
        

        .button {
            background-color: #F28018; /* Green background */
            color: white; /* White text */
            padding: 15px 30px; /* Top and bottom, left and right padding */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            font-size: 18px; /* Larger font size */
            font-weight: bold; /* Bold text */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s, transform 0.2s; /* Smooth transition */
            text-align: center; /* Center text */
            text-decoration: none; /* Remove underline for link */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow effect */
        }

        .button:hover {
            background-color: black; /* Darker green on hover */
            transform: translateY(-2px); /* Lift effect on hover */
        }
    </style>
</head>
<body>

    <button class="button" onclick="window.location.href='kan.php';">Multi-Filter Master</button>

</body>
</html>



    <!-- PHP code for displaying production data -->
    <?php
    //include './includes/admin_header.php';
    include './includes/data_base_save_update.php';
    include 'includes/App_Code.php';

    // Establish database connection
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Check if the connection is successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Initialize the WHERE clause
    $whereClause = "";

    // Check if the press_name filter is set and not empty
    if (isset($_GET['press_name']) && !empty($_GET['press_name'])) {
        $press_name = mysqli_real_escape_string($conn, $_GET['press_name']);
        $whereClause .= " AND press_name = '$press_name'";
    }

    // Check if the mold_id filter is set and not empty
    if (isset($_GET['mold_id']) && !empty($_GET['mold_id'])) {
        $mold_id = mysqli_real_escape_string($conn, $_GET['mold_id']);
        $whereClause .= " AND mold_id = '$mold_id'";
    }

    // Check if the tire_id filter is set and not empty
    if (isset($_GET['tire_id']) && !empty($_GET['tire_id'])) {
        $tire_id = mysqli_real_escape_string($conn, $_GET['tire_id']);
        $whereClause .= " AND icode = '$tire_id'";
    }

    // Modify the SQL query to include the WHERE clause
    $erpSql = "SELECT erp, customer, MAX(end_date) as last_completion_date FROM plannew WHERE 1=1 $whereClause GROUP BY erp";

    $erpResult = mysqli_query($conn, $erpSql);

    // Check if the query was successful
    if ($erpResult) {
        if (mysqli_num_rows($erpResult) > 0) {
            // Retrieve the results from the first code block
            $sumQuery = "SELECT `erp`, SUM(CASE WHEN `tobe` > 0 THEN `tobe` ELSE 0 END) AS `total_positive_amount` FROM `tobeplan1` GROUP BY `erp`";
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
                $sql = "SELECT * FROM plannew WHERE erp = '$erp' $whereClause";
                $result = mysqli_query($conn, $sql);

                // Check if the query was successful
                if ($result) {
                    // Check if any production plan entries exist
                    if (mysqli_num_rows($result) > 0) {
                        // Retrieve one worder ref for the current ERP number
                        $worderSql = "SELECT ref,wono,date FROM worder WHERE erp = '$erp' LIMIT 1";
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
                        echo "<h3>Worder Ref: $worderRef - WO NO: $wonoRef<h6>ERP Number: $erp<br>Work Order Release Date: $dateRef - Last Completion Date: $lastCompletionDate <br><br>";
                        echo "<span class='cargo-loading-date'>Cargo Loading Date: $cargoLoadingDate</span></h6>";
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
                            $tobeSql = "SELECT tobe FROM tobeplan1 WHERE icode = '$icode' AND erp = '$erp'";
                            $tobeResult = mysqli_query($conn, $tobeSql);
                            $tobeValue = '';

                            if ($tobeResult && mysqli_num_rows($tobeResult) > 0) {
                                $tobeRow = mysqli_fetch_assoc($tobeResult);
                                $tobeValue = $tobeRow['tobe'];
                            }


                           // Retrieve the cstock value for the given tire type
                           $cstockSql = "SELECT stockonhand FROM tobeplan1 WHERE icode = '$icode' AND erp = '$erp'";
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
                       
                       // Display total order quantity and total to be produced
                       echo "<h6>Total Order Quantity: $totalOrderQuantity</h6>";
                       echo "<h6>Total To Be Produced: $totalTobeValue</h6>";
                   } else {
                       echo "No production plan details found for ERP number: $erp.";
                   }
               } else {
                   echo "Error executing query: " . mysqli_error($conn);
               }
           }
       } else {
           echo "No data found for the selected filters.";
       }
   } else {
       echo "Error executing ERP query: " . mysqli_error($conn);
   }

   mysqli_close($conn);
   ?>
</body>
</html> 
               