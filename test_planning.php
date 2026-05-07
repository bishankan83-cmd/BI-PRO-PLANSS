<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if there is any data in the tables
$sql_check_tobeplan1 = "SELECT COUNT(*) AS count FROM tobeplan1";
$sql_check_plannew = "SELECT COUNT(*) AS count FROM plannew";
$sql_check_stock = "SELECT COUNT(*) AS count FROM stock";

$result_tobeplan1 = $conn->query($sql_check_tobeplan1);
$result_plannew = $conn->query($sql_check_plannew);
$result_stock = $conn->query($sql_check_stock);

// Check if any of the tables are empty
if ($result_tobeplan1 && $result_plannew && $result_stock) {
    $row_tobeplan1 = $result_tobeplan1->fetch_assoc();
    $row_plannew = $result_plannew->fetch_assoc();
    $row_stock = $result_stock->fetch_assoc();
    
    // If any of the tables are empty, delete all data from all tables
    if ($row_tobeplan1['count'] == 0 || $row_plannew['count'] == 0 || $row_stock['count'] == 0) {
        $sql_delete_tobeplan1 = "DELETE FROM tobeplan1";
        $sql_delete_plannew = "DELETE FROM plannew";
        $sql_delete_stock = "DELETE FROM stock";
        
        $conn->query($sql_delete_tobeplan1);
        $conn->query($sql_delete_plannew);
        $conn->query($sql_delete_stock);
        
      //  echo "All data deleted successfully.";
    } else {
        //echo "Data exists in all tables. No action taken.";
    }
} else {
    //echo "Error checking data in tables: " . $conn->error;
}

// Close connection
$conn->close();
?>





<?php


include 'includes/checkauthenticator.php';

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if plannew table has data
$sql = "SELECT COUNT(*) AS count FROM plannew";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$count_plannew = $row['count'];

// Check if stock table has data
$sql = "SELECT COUNT(*) AS count FROM stock";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$count_stock = $row['count'];

// Check if tobeplan1 table has data
$sql = "SELECT COUNT(*) AS count FROM tobeplan1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$count_tobeplan1 = $row['count'];

// If plannew, stock, and tobeplan1 have data, delete data from new_plan_data, new_stock_data, new_tobeplan_data
if ($count_plannew > 0 && $count_stock > 0 && $count_tobeplan1 > 0) {
    // Delete data from new_plan_data
    $sql = "DELETE FROM new_plan_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data deleted from new_plan_data successfully<br>";
    } else {
        //echo "Error deleting data from new_plan_data: " . $conn->error;
    }
    
    // Delete data from new_stock_data
    $sql = "DELETE FROM new_stock_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data deleted from new_stock_data successfully<br>";
    } else {
        //echo "Error deleting data from new_stock_data: " . $conn->error;
    }
    
    // Delete data from new_tobeplan_data
    $sql = "DELETE FROM new_tobeplan_data";
    if ($conn->query($sql) === TRUE) {
      //  echo "Data deleted from new_tobeplan_data successfully<br>";
    } else {
       // echo "Error deleting data from new_tobeplan_data: " . $conn->error;
    }
} else {
    // Transfer data from new_plan_data to plannew
    $sql = "INSERT INTO plannew SELECT * FROM new_plan_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data transferred from new_plan_data to plannew successfully<br>";
        // Truncate new_plan_data
        $sql = "TRUNCATE TABLE new_plan_data";
        $conn->query($sql);
    } else {
       // echo "Error transferring data from new_plan_data to plannew: " . $conn->error;
    }
    
    // Transfer data from new_stock_data to stock
    $sql = "INSERT INTO stock SELECT * FROM new_stock_data";
    if ($conn->query($sql) === TRUE) {
        //echo "Data transferred from new_stock_data to stock successfully<br>";
        // Truncate new_stock_data
        $sql = "TRUNCATE TABLE new_stock_data";
        $conn->query($sql);
    } else {
       // echo "Error transferring data from new_stock_data to stock: " . $conn->error;
    }
    
    // Transfer data from new_tobeplan_data to tobeplan1
    $sql = "INSERT INTO tobeplan1 SELECT * FROM new_tobeplan_data";
    if ($conn->query($sql) === TRUE) {
       // echo "Data transferred from new_tobeplan_data to tobeplan1 successfully<br>";
        // Truncate new_tobeplan_data
        $sql = "TRUNCATE TABLE new_tobeplan_data";
        $conn->query($sql);
    } else {
      //  echo "Error transferring data from new_tobeplan_data to tobeplan1: " . $conn->error;
    }
}

// Close connection
$conn->close();

?>

<?php

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Transfer data from plannew to new_plan_data
$sql = "INSERT INTO new_plan_data SELECT * FROM plannew";
if ($conn->query($sql) === TRUE) {
    //echo "Data transferred from plannew to new_plan_data successfully<br>";
} else {
    echo "Error transferring data: " . $conn->error;
}

// Transfer data from stock to new_stock_data
$sql = "INSERT INTO new_stock_data SELECT * FROM stock";
if ($conn->query($sql) === TRUE) {
    //echo "Data transferred from stock to new_stock_data successfully<br>";
} else {
   // echo "Error transferring data: " . $conn->error;
}

// Transfer data from tobeplan1 to new_tobeplan_data
$sql = "INSERT INTO new_tobeplan_data SELECT * FROM tobeplan1";
if ($conn->query($sql) === TRUE) {
   // echo "Data transferred from tobeplan1 to new_tobeplan_data successfully<br>";
} else {
   // echo "Error transferring data: " . $conn->error;
}

// Close connection
$conn->close();

?>














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
    background-color:light black;
   
    animation: blink 1s infinite; /* Adjust the duration as needed */
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
    font-size: 16px; /* You can adjust the size to your preference */
    background-color: black; /* Add a background color for emphasis */
    border: 1px dashed gray; /* Dashed border for emphasis */
    border-radius: 10px; /* Adjust the border-radius to your preference */
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
            animation: blink 1s infinite; /* Adjust the duration as needed */
        }
    
    </style>
</head>
<body>
    <!-- Button container in the top-left corner -->
    <div class="button-container">
        <button><a href="planbuttoon.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a></button>
    </div>

    <!-- Form for filtering by press_name -->
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
            <input type="submit" value="Filter">

            
        </form>
    </div>

    <form method="GET" action="planning2.php">
        <label for="mold_id">Select Mold ID:</label>
        <select name="mold_id" id="mold_id">
            <option value="">All Mold IDs</option>
            <?php
            // Establish database connection
            $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");
            // Retrieve and display mold IDs from the database
            $sql = "SELECT DISTINCT mold_id FROM plannew";
            $result = mysqli_query($conn, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $row['mold_id'] . "'>" . $row['mold_id'] . "</option>";
                }
            }
            ?>
        </select>
        <input type="submit" value="Filter">
    </form>
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

// Check if the press_name filter is set and not empty
if (isset($_GET['press_name']) && !empty($_GET['press_name'])) {
    $press_name = $_GET['press_name'];
    // Modify the SQL query to include the press_name condition
    $erpSql = "SELECT erp, customer, MAX(end_date) as last_completion_date FROM plannew WHERE press_name = '$press_name' GROUP BY erp";
} else {
    // If the filter is not set or empty, retrieve all data
    $erpSql = "SELECT erp, customer, MAX(end_date) as last_completion_date FROM plannew GROUP BY erp";
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
            if (isset($_GET['press_name']) && !empty($_GET['press_name'])) {
                $sql = "SELECT * FROM plannew WHERE erp = '$erp' AND press_name = '$press_name'";
            } else {
                $sql = "SELECT * FROM plannew WHERE erp = '$erp'";
            }
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
