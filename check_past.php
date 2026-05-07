<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet autoload file

// Database connection parameters (adjust these as per your configuration)
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$database = "your_database";


// Database connection parameters (adjust these as per your configuration)
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";


// Initialize variables for date and shift
$startDate = $endDate = "";
$data = array();
$shiftData = array();
$allTotal = array(
    'totalPlan' => 0,
    'totalActual' => 0,
    'totalLoss' => 0,
    'totalPlanCompound' => 0,
    'totalPlanSteel' => 0,
    'totalActualCompound' => 0,
    'totalActualSteel' => 0
);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get start date, end date, and shift from the form
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Create a connection to the MySQL database
    $conn = new mysqli($servername, $username, $password, $database);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to select data based on Date range, joining with tire_details and bom_new, ordering by id
    $sql = "SELECT dpd.*, td.greenweight AS TireWeight, td.stgreenweight, td.description AS TireDescription,
                   bn.a, bn.b, bn.c, bn.d, bn.e, bn.f, bn.g, bn.h, bn.i, bn.j, bn.k, bn.l, bn.m, bn.n, bn.o, bn.p, bn.q
            FROM daily_plan_data dpd
            LEFT JOIN tire_details td ON dpd.Icode = td.icode
            LEFT JOIN bom_new bn ON dpd.SomeColumn = bn.SomeColumn
            WHERE dpd.Date BETWEEN '$startDate' AND '$endDate'
            ORDER BY dpd.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Handle the case where the loss reason is "Not Matching The Unloading time" or "Over Production"
            if ($row["LossReason"] === "Not Matching The Unloading time" || $row["LossReason"] === "Over Production") {
                // In this case, set Actual Tires as Plan Tires
                $row["Plan"] = $row["AdditionalData"];
            }
            // Calculate Loss Tires and ensure it's positive
            $lossTires = max(0, $row["Plan"] - $row["AdditionalData"]);
            $row['LossTires'] = $lossTires;
            // Add row to data array
            $data[] = $row;
            
            // Organize data by shift
            $shiftName = $row["Shift"];
            if (!isset($shiftData[$shiftName])) {
                $shiftData[$shiftName] = array();
            }
            $shiftData[$shiftName][] = $row;
            
            // Calculate totals for all shifts
            $allTotal['totalPlan'] += $row['Plan'];
            $allTotal['totalActual'] += $row['AdditionalData'];
            $allTotal['totalLoss'] += $row['LossTires'];
            $allTotal['totalPlanCompound'] += $row['Plan'] * $row['TireWeight'];
            $allTotal['totalPlanSteel'] += $row['Plan'] * $row['stgreenweight'];
            $allTotal['totalActualCompound'] += $row['AdditionalData'] * $row['TireWeight'];
            $allTotal['totalActualSteel'] += $row['AdditionalData'] * $row['stgreenweight'];
        }
    } else {
        echo "0 results";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management Report</title>
    <style>
        /* Your CSS styles */
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        td {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
        }

        /* Style the form */
        form {
            text-align: center;
            margin: 10px;
        }

        label {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
        }

        select,
        input[type="date"],
        input[type="text"] {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
        }

        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #333333;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            /* Distribute space evenly between buttons */
        }

        button {
            background-color: black;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #F28018;
        }

        .export-button {
            background-color: #e74c3c;
        }

        .export-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body>

    <!-- Form for input fields -->
    <div class="container">
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>" required>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>" required>
            <input type="submit" value="Retrieve Data">
        </form>
    </div>

    <!-- Display the retrieved data -->
    <?php if (!empty($data)): ?>
        <?php foreach ($shiftData as $shiftName => $shiftRows): ?>
            <h2><?php echo "Shift: $shiftName"; ?></h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Press</th>
                    <th>Tire Code</th>
                    <th>Tire Description</th>
                    <th>Plan Tires</th>
                    <th>Actual Tires</th>
                    <th>Loss Tires</th>
                    <th>Loss Reason</th>
                    <th>Root cause for loss</th>
                    <th>Per Tire Compound Weight</th>
                    <th>Per Tire Weight with Steel</th>
                    <th>Total Plan Compound Weight</th>
                    <th>Total Plan Steel Weight</th>
                    <th>Total Actual Compound Weight</th>
                    <th>Total Actual Steel Weight</th>
                    <!-- Add columns for bom_new fields -->
                    <th>a</th>
                    <th>b</th>
                    <th>c</th>
                    <th>d</th>
                    <th>e</th>
                    <th>f</th>
                    <th>g</th>
                    <th>h</th>
                    <th>i</th>
                    <th>j</th>
                    <th>k</th>
                    <th>l</th>
                    <th>m</th>
                    <th>n</th>
                    <th>o</th>
                    <th>p</th>
                    <th>q</th>
                </tr>
                <?php 
                $totalPlan = $totalActual = $totalLoss = $totalPlanCompound = $totalPlanSteel = $totalActualCompound = $totalActualSteel = 0;
                foreach ($shiftRows as $row): 
                    $totalPlan += $row['Plan'];
                    $totalActual += $row['AdditionalData'];
                    $totalLoss += $row['LossTires'];
                    $totalActualCompound += $row['AdditionalData'] * $row['TireWeight'];
                    $totalActualSteel += $row['AdditionalData'] * $row['stgreenweight'];
                ?>
                    <tr>
                        <td><?php echo $row['Date']; ?></td>
                        <td><?php echo $row['CavityName']; ?></td>
                        <td><?php echo $row['Icode']; ?></td>
                        <td><?php echo $row['TireDescription']; ?></td>
                        <td><?php echo $row['Plan']; ?></td>
                        <td><?php echo $row['AdditionalData']; ?></td>
                        <td><?php echo $row['LossTires']; ?></td>
                        <td><?php echo $row['LossReason']; ?></td>
                        <td><?php echo $row['Remark']; ?></td>
                        <td><?php echo $row['TireWeight']; ?></td>
                        <td><?php echo $row['stgreenweight']; ?></td>
                        <td><?php echo $row['Plan'] * $row['TireWeight']; ?></td>
                        <td><?php echo $row['Plan'] * $row['stgreenweight']; ?></td>
                        <td><?php echo $row['AdditionalData'] * $row['TireWeight']; ?></td>
                        <td><?php echo $row['AdditionalData'] * $row['stgreenweight']; ?></td>
                        <!-- Display bom_new fields -->
                        <td><?php echo $row['a']; ?></td>
                        <td><?php echo $row['b']; ?></td>
                        <td><?php echo $row['c']; ?></td>
                        <td><?php echo $row['d']; ?></td>
                        <td><?php echo $row['e']; ?></td>
                        <td><?php echo $row['f']; ?></td>
                        <td><?php echo $row['g']; ?></td>
                        <td><?php echo $row['h']; ?></td>
                        <td><?php echo $row['i']; ?></td>
                        <td><?php echo $row['j']; ?></td>
                        <td><?php echo $row['k']; ?></td>
                        <td><?php echo $row['l']; ?></td>
                        <td><?php echo $row['m']; ?></td>
                        <td><?php echo $row['n']; ?></td>
                        <td><?php echo $row['o']; ?></td>
                        <td><?php echo $row['p']; ?></td>
                        <td><?php echo $row['q']; ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4">Shift Total</td>
                    <td><?php echo $totalPlan; ?></td>
                    <td><?php echo $totalActual; ?></td>
                    <td><?php echo $totalLoss; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $totalPlanCompound; ?></td>
                    <td><?php echo $totalPlanSteel; ?></td>
                    <td><?php echo $totalActualCompound; ?></td>
                    <td><?php echo $totalActualSteel; ?></td>
                    <!-- Leave columns for bom_new fields empty for totals -->
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        <?php endforeach; ?>
        <h2>All Shifts Summary</h2>
        <table>
            <tr>
                <th>Total Plan Tires</th>
                <th>Total Actual Tires</th>
                <th>Total Loss Tires</th>
                <th></th>
                <th></th>
                <th></th>
                <th>Total Plan Compound Weight</th>
                <th>Total Plan Steel Weight</th>
                <th>Total Actual Compound Weight</th>
                <th>Total Actual Steel Weight</th>
                <!-- Add columns for bom_new fields -->
                <th>a</th>
                <th>b</th>
                <th>c</th>
                <th>d</th>
                <th>e</th>
                <th>f</th>
                <th>g</th>
                <th>h</th>
                <th>i</th>
                <th>j</th>
                <th>k</th>
                <th>l</th>
                <th>m</th>
                <th>n</th>
                <th>o</th>
                <th>p</th>
                <th>q</th>
            </tr>
            <tr class="total-row">
                <td><?php echo $allTotal['totalPlan']; ?></td>
                <td><?php echo $allTotal['totalActual']; ?></td>
                <td><?php echo $allTotal['totalLoss']; ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?php echo $allTotal['totalPlanCompound']; ?></td>
                <td><?php echo $allTotal['totalPlanSteel']; ?></td>
                <td><?php echo $allTotal['totalActualCompound']; ?></td>
                <td><?php echo $allTotal['totalActualSteel']; ?></td>
                <!-- Leave columns for bom_new fields empty for totals -->
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    <?php endif; ?>

</body>

</html>

