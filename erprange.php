


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



<?php include 'includes/checkauthenticator.php';






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




    </style>
    </style>
</head>
<body>
<div class="button-container">
        <button><a href="planbuttoon.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a></button>
    </div>

    <h1>Production Plan Details</h1>
    <form action="erprange2.php" method="get">
        <label for="icode">Enter iCode:</label>
        <input type="text" id="icode" name="icode">
        <button type="submit">Submit</button>
    </form>
    <?php

error_reporting(E_ERROR | E_PARSE);

    // Establish database connection
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Check if the connection is successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    
    // Retrieve all unique ERP numbers
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
// Assuming you have the $workOrders array and a database connection $conn

// Assuming you have the $workOrders array and a database connection $conn

$tobeTotals = [];

foreach ($workOrders as $icode => $workOrderData) {
    $totalTobe = 0;

    foreach ($workOrderData as $erp => $erpData) {
        $tobe = isset($erpData['new']) ? $erpData['new'] : "";

        // Sanitize variables before using in SQL query to prevent SQL injection
        $icodeSafe = mysqli_real_escape_string($conn, $icode);
        $erpSafe = mysqli_real_escape_string($conn, $erp);

        // Retrieve the "tobe" value from the tobeplan1 table for positive transitions
        $tobeSql = "SELECT SUM(tobe) AS totalTobe FROM tobeplan1 WHERE erp = '$erpSafe' AND icode = '$icodeSafe' AND tobe > 0";
        $tobeResult = mysqli_query($conn, $tobeSql);

 

        if ($tobeResult && mysqli_num_rows($tobeResult) > 0) {
            $tobeRow = mysqli_fetch_assoc($tobeResult);
            $tobe = $tobeRow['totalTobe'];
        } else {
            $tobe = 0; // Set to 0 if no positive transitions found
        }

        $totalTobe += $tobe;
    }

    $tobeTotals[$icode] = $totalTobe;
}

// Now $tobeTotals array contains the total "tobe" values with positive transitions for each "icode"


// Now $tobeTotals array contains the total "tobe" values for each "icode"


               // Calculate the total requirement for each "icode"
$totalRequirements = [];
foreach ($workOrders as $icode => $workOrderData) {
    $totalRequirement = 0;
    foreach ($workOrderData as $erpData) {
        $totalRequirement += $erpData['new'];
    }
    $totalRequirements[$icode] = $totalRequirement;
}

    // Calculate the sum of all "Total Requirement" values
    $totalRequirementSum = array_sum($totalRequirements);

    

 // Display the sum of all "Total Requirement" values above the "Total Requirement" column
 echo "<th colspan='" . (count($erpNumbers) + 1) . "'>Total Requirement Sum: $totalRequirementSum</th>";


 // Calculate the sum of all "Total Requirement" values
$totalRequirementSum = array_sum($totalRequirements);

// Calculate the sum of all "Total Tobe" values
$totalTobeSum = array_sum($tobeTotals);



// Display the sum of all "Total Tobe" values above the "Total Tobe" column
echo "<th><br>Total Tobe Sum: $totalTobeSum</th>";

echo "<table class='production-table'>";
echo "<tr><th>Tire ID</th>";
echo "<th>Description <span style='color: #F28018;'>ggggggggggggggggggggggggggggggg</span></th>";

//echo "<th>Brand</th>";
//echo "<th>Color</th>";
//echo "<th>Curing Time</th>";
//echo "<th>Curing Group</th>";
                echo "<th>Stock on Hand</th>";
                echo "<th>Total Tobe</th>"; // New column for Total Tobe
                echo "<th>Total Requirement </th>"; // New column for Total Requirement
                foreach ($erpNumbers as $erp) {
                    // Retrieve the reference for the current ERP number
                    $referenceSql = "SELECT ref FROM worder WHERE erp = '$erp'";
                    $referenceResult = mysqli_query($conn, $referenceSql);
                
                    if ($referenceResult && mysqli_num_rows($referenceResult) > 0) {
                        $referenceRow = mysqli_fetch_assoc($referenceResult);
                        $reference = $referenceRow['ref'];
                
                        $totalTobe = 0;
                
                        foreach ($workOrders as $icode => $workOrderData) {
                            $tobe = isset($workOrderData[$erp]['new']) ? $workOrderData[$erp]['new'] : 0;
                            $totalTobe += $tobe;
                        }
                
                        // SQL query to check if the ERP exists in the tobeplan1 table
                        $checkSql = "SELECT COUNT(*) AS count FROM tobeplan1 WHERE erp = '$erp'";
                        $checkResult = $conn->query($checkSql);
                
                        if ($checkResult && $checkResult->num_rows > 0) {
                            $checkRow = $checkResult->fetch_assoc();
                            $count = $checkRow["count"];
                
                            if ($count > 0) {
                                // SQL query to get the sum of positive 'tobe' assets related to each 'erp' from the 'tobeplan1' table
                                $sql = "SELECT SUM(CASE WHEN tobe > 0 THEN tobe ELSE 0 END) AS total_tobe_assets
                                        FROM tobeplan1
                                        WHERE erp = '$erp'";
                
                                $result = $conn->query($sql);
                
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $totalTobeAssets = $row["total_tobe_assets"];
                                } else {
                                    $totalTobeAssets = 0; // Set to 0 if no data found
                                }
                
                                // Apply different text colors and make "Production Complete" blink for 0 or negative values
                                if ($totalTobeAssets <= 0) {
                                    echo "<th style='color: black;'>$reference <br> <span style='color: white;'>Total Requirement: $totalTobe</span> <br> <span style='color: black;background-color: #90ee90; animation: blink 1s infinite;'>Production Complete</span></th>";
                                } else {
                                    echo "<th style='color: black;'>$reference <br> <span style='color: white;'>Total Requirement: $totalTobe</span> <br> <span style='color: black;'>Total Tobe: <span style='background-color: orange;'>$totalTobeAssets</span></th>";
                                }
                            } else {
                                echo "<th style='color: black;'>$reference <br> <span style='color: white;'>Total Requirement: $totalTobe</span> <br> <span style='color: black;background-color: #ffcc00; animation: blink 1s infinite;'>Pending for Planning</span></th>";
                            }
                        } else {
                            echo "Error checking ERP number $erp: " . mysqli_error($conn);
                        }
                    } else {
                        echo "Error retrieving reference for ERP number $erp: " . mysqli_error($conn);
                    }
                }
                
echo "</tr>"; // End the table row
                

                // Display the work order data vertically
                foreach ($workOrders as $icode => $workOrderData) {
                    echo "<tr>";
                    echo "<td>$icode</td>";

                    // Fetch the ERP numbers again for the inner loop
                    mysqli_data_seek($erpResult, 0);

                    // Retrieve Brand, Color, Curing Time, and Curing Group from the selectpress table
                    $selectPressSql = "SELECT brand, col, curing_id, curing_group, description FROM selectpress WHERE icode = '$icode'";
                    $selectPressResult = mysqli_query($conn, $selectPressSql);

                    if ($selectPressResult) {
                        $selectPressRow = mysqli_fetch_assoc($selectPressResult);
                      $brand = $selectPressRow['brand'];
                        $color = $selectPressRow['col'];
                       $curingTime = $selectPressRow['curing_id'];
                        $curingGroup = $selectPressRow['curing_group'];
                        $description = $selectPressRow['description'];

                        //Display the tire description, brand, color, curing time, and curing group in separate columns
                        echo "<td class='description-cell'>$description</td>";


                     

                        // Retrieve the suitable amount of cstock from the realstock table
                        $realStockSql = "SELECT cstock FROM realstock WHERE icode = '$icode'";
                        $realStockResult = mysqli_query($conn, $realStockSql);
                    
                        if ($realStockResult) {
                            $realStockRow = mysqli_fetch_assoc($realStockResult);
                    
                            if ($realStockRow && isset($realStockRow['cstock'])) {
                                $stockOnHand = $realStockRow['cstock'];
                                // Display the stock on hand in a separate column
                                echo "<td>$stockOnHand</td>";
                            } else {
                                echo "<td>No Stock Data</td>"; // Handle the case when data is not available
                            }
                    
                            // Display the total "tobe" value for this "icode"
                            $totalTobe = isset($tobeTotals[$icode]) ? $tobeTotals[$icode] : 0; // Set a default value if the key doesn't exist
                            echo "<td>";
                            if ($totalTobe >= 0) {
                                echo $totalTobe;
                            } else {
                                echo "-";
                            }
                            echo "</td>";

                            if (isset($totalRequirements[$icode])) {
                                $totalRequirement = $totalRequirements[$icode];
                            } else {
                                $totalRequirement = 0; // Set a default value if the key doesn't exist
                            }
                            echo "<td>$totalRequirement</td>";
                            
                        

                            foreach ($erpNumbers as $erp) {
                                $new = isset($workOrderData[$erp]['new']) ? $workOrderData[$erp]['new'] : null;

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
                                echo "<span class='" . ($new > 0 ? 'green' : '') . "'>Order Quantity: " . (isset($new) ? $new : '') . "</span><br>";
                                
                                // Display the Total Tobe column specific changes here
                                if ($erp === "Total Tobe") {
                                    echo "<span class='" . ((isset($tobe) && $tobe > 0) ? 'red' : '') . "'>Tobe: " . (isset($tobe) ? $tobe : '') . "</span><br>";
                                } else {
                                    echo "<span class='" . ((isset($tobe) && $tobe > 0) ? 'red' : '') . "'>Tobe: " . (isset($tobe) ? $tobe : '') . "</span><br>";
                                }
                                
                                echo "</div>";
                                
                                echo "</td>";
                            }
                        } else {
                           // echo "Error executing realstock query: " . mysqli_error($conn);
                        }
                    } else {
                        //echo "Error executing selectpress query: " . mysqli_error($conn);
                    }

                    echo "</tr>";
                }

                echo "</table>";
            } else {
                //echo "Error executing work order query: " . mysqli_error($conn);
            }
        } else {
            //echo "No ERP numbers found in the database.";
        }
    } else {
       // echo "Error executing ERP query: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
    ?>
</body>
</html>