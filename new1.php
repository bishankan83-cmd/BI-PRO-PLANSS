
<!DOCTYPE html>
<html>
<head>
    <title>Export to Excel</title>
</head>
<body>
    <h1>Export Data to Excel</h1>
    <form action="export_plan.php" method="post">
        <button type="submit">Export to Excel</button>
    </form>
</body>
</html>



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





















<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


     // Delete all existing data from cal_report table
     $deleteStmt = $pdo->prepare("DELETE FROM cal_report");
     $deleteStmt->execute();
    
    // Fetch data from the 'cal' table, join with 'tire_details' to get descriptions, rim, brand, type, color,
    // 'press_cavity' to get press_id, 'press' to get press_name, 'tobe' to get total_tobe, and 'bom_new' to get grand totalcompound weight
    $stmt = $pdo->prepare("
        SELECT cal.*, tire_details.description, tire_details.rim, tire_details.brand, tire_details.type, tire_details.colour,
               press_cavity.press_id, press.press_name, tobe.total_tobe, bom_new.`grand totalcompound weight`, bom_new.`green tire weight`
        FROM cal
        LEFT JOIN tire_details ON cal.icode = tire_details.icode
        LEFT JOIN press_cavity ON cal.cavity_id = press_cavity.cavity_id
        LEFT JOIN press ON press_cavity.press_id = press.press_id
        LEFT JOIN alp ON press.press_name = alp.press_name
        LEFT JOIN tobe ON cal.icode = tobe.icode
        LEFT JOIN bom_new ON cal.icode = bom_new.icode
        ORDER BY alp.id ASC, cal.cavity_id ASC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the insert statement
    $insertStmt = $pdo->prepare("
        INSERT INTO cal_report (press_name, icode, description, rim, brand, type, colour, green_weight, mold_id, total_tobe, plan, plan_weight, black, nm, prod, loss, remark)
        VALUES (:press_name, :icode, :description, :rim, :brand, :type, :colour, :green_weight, :mold_id, :total_tobe, :plan, :plan_weight, :black, :nm, :prod, :loss, :remark)
    ");

    // Variables to store the total plan and unique cavity_ids
    $totalPlan = 0; 
    $uniqueCavityIds = [];

    foreach ($result as $row) {
        if ($row['plan'] == 0) {
            continue; // Skip rows where the plan value is 0
        }

        // Calculate Plan * Green Tire Weight
        $planGreenTireWeight = ($row['plan'] * $row['grand totalcompound weight']);

        // Bind the parameters and execute the insert statement
        $insertStmt->execute([
            ':press_name' => $row['press_name'],
            ':icode' => $row['icode'],
            ':description' => $row['description'],
            ':rim' => $row['rim'],
            ':brand' => $row['brand'],
            ':type' => $row['type'],
            ':colour' => $row['colour'],
            ':green_weight' => $row['grand totalcompound weight'],
            ':mold_id' => $row['mold_id'],
            ':total_tobe' => $row['total_tobe'],
            ':plan' => $row['plan'],
            ':plan_weight' => $planGreenTireWeight,
            ':black' => '',
            ':nm' => '',
            ':prod' => '',
            ':loss' => '',
            ':remark' => ''
        ]);

        // Add the plan value to the total
        $totalPlan += ($row['plan']);

        // Track unique cavity_ids
        $uniqueCavityIds[$row['cavity_id']] = true;
    }

    // Display the data in an HTML table
    echo '<table border="1">';
    echo '<tr><th>Press Name</th><th>Item Code</th><th>Description</th><th>Rim</th><th>Brand</th><th>Type</th><th>Color</th><th>Green Weight</th><th>Mold ID</th><th>Total ToBe</th><th>Plan</th><th>Plan Weight</th><th>Black</th><th>NM</th><th>prod</th><th>loss</th><th>Remark</th></tr>';
    
    foreach ($result as $row) {
        if ($row['plan'] == 0) {
            continue; // Skip rows where the plan value is 0
        }

        echo '<tr>';
        echo '<td>' . $row['press_name'] . '</td>'; // Display the press_name
        echo '<td>' . $row['icode'] . '</td>';
        echo '<td>' . $row['description'] . '</td>'; // Display the description
        echo '<td>' . $row['rim'] . '</td>'; // Display the rim
        echo '<td>' . $row['brand'] . '</td>'; // Display the brand
        echo '<td>' . $row['type'] . '</td>'; // Display the type
        echo '<td>' . $row['colour'] . '</td>'; // Display the colour
        echo '<td>' . (isset($row['grand totalcompound weight']) ? $row['grand totalcompound weight'] : '') . '</td>'; // Display grand totalcompound weight
        echo '<td>' . $row['mold_id'] . '</td>';
        echo '<td>' . (isset($row['total_tobe']) ? '(' . $row['total_tobe'] . ')' : '') . '</td>'; // Display total_tobe in brackets
        echo '<td>' . ($row['plan']) . '</td>';
        
        // Calculate Plan * Green Tire Weight and display the result
        $planGreenTireWeight = ($row['plan'] * $row['grand totalcompound weight']);
        echo '<td>' . $planGreenTireWeight . '</td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';

        echo '</tr>';
    }

    // Display the total row
    echo '<tr><td colspan="9"></td><td>Total</td><td>' . $totalPlan . '</td></tr>';

    // Display the count of unique cavity_ids
    echo '<tr><td colspan="9"></td><td>Unique Cavity IDs Count</td><td>' . count($uniqueCavityIds) . '</td></tr>';
    
    echo '</table>';

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$pdo = null;
?>
