<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet autoload file

// Database connection parameters
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
    // Get start date, end date from the form
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Create a connection to the MySQL database
    $conn = new mysqli($servername, $username, $password, $database);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT dpd.*, td.greenweight AS TireWeight, td.stgreenweight, td.description AS TireDescription
                            FROM daily_plan_data dpd
                            LEFT JOIN tire_details td ON dpd.Icode = td.icode
                            WHERE dpd.Date BETWEEN ? AND ?
                            ORDER BY dpd.id");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Convert string values to numeric types, with fallback to 0 if NULL or empty
            $plan = !empty($row['Plan']) ? intval($row['Plan']) : 0;
            $actual = !empty($row['AdditionalData']) ? intval($row['AdditionalData']) : 0;
            $tireWeight = !empty($row['TireWeight']) ? floatval($row['TireWeight']) : 0;
            $stGreenWeight = !empty($row['stgreenweight']) ? floatval($row['stgreenweight']) : 0;

            // Handle the case where the loss reason is "Not Matching The Unloading time" or "Over Production"
            if ($row["LossReason"] === "Not Matching The Unloading time" || $row["LossReason"] === "Over Production") {
                // In this case, set Actual Tires as Plan Tires
                $plan = $actual;
            }

            // Calculate Loss Tires and ensure it's positive
            $lossTires = max(0, $plan - $actual);
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
            $allTotal['totalPlan'] += $plan;
            $allTotal['totalActual'] += $actual;
            $allTotal['totalLoss'] += $lossTires;
            $allTotal['totalPlanCompound'] += $plan * $tireWeight;
            $allTotal['totalPlanSteel'] += $plan * $stGreenWeight;
            $allTotal['totalActualCompound'] += $actual * $tireWeight;
            $allTotal['totalActualSteel'] += $actual * $stGreenWeight;
        }
    } else {
        echo "0 results";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        /* CSS styles */
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
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
            <input type="submit" value="Retrieve Data">
        </form>
    </div>

    <!-- Display the retrieved data -->
    <?php if (!empty($data)): ?>
        <?php foreach ($shiftData as $shiftName => $shiftRows): ?>
            <h2><?php echo "Shift: " . htmlspecialchars($shiftName); ?></h2>
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
                </tr>
                <?php 
                $totalPlan = $totalActual = $totalLoss = $totalPlanCompound = $totalPlanSteel = $totalActualCompound = $totalActualSteel = 0;
                foreach ($shiftRows as $row): 
                    // Convert values for calculations
                    $plan = !empty($row['Plan']) ? intval($row['Plan']) : 0;
                    $actual = !empty($row['AdditionalData']) ? intval($row['AdditionalData']) : 0;
                    $tireWeight = !empty($row['TireWeight']) ? floatval($row['TireWeight']) : 0;
                    $stGreenWeight = !empty($row['stgreenweight']) ? floatval($row['stgreenweight']) : 0;

                    $totalPlan += $plan;
                    $totalActual += $actual;
                    $totalLoss += $row['LossTires'];
                    $totalPlanCompound += $plan * $tireWeight;
                    $totalPlanSteel += $plan * $stGreenWeight;
                    $totalActualCompound += $actual * $tireWeight;
                    $totalActualSteel += $actual * $stGreenWeight;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Date']); ?></td>
                        <td><?php echo htmlspecialchars($row['CavityName']); ?></td>
                        <td><?php echo htmlspecialchars($row['Icode']); ?></td>
                        <td><?php echo htmlspecialchars($row['TireDescription']); ?></td>
                        <td><?php echo htmlspecialchars($row['Plan']); ?></td>
                        <td><?php echo htmlspecialchars($row['AdditionalData']); ?></td>
                        <td><?php echo htmlspecialchars($row['LossTires']); ?></td>
                        <td><?php echo htmlspecialchars($row['LossReason']); ?></td>
                        <td><?php echo htmlspecialchars($row['Remark']); ?></td>
                        <td><?php echo htmlspecialchars($row['TireWeight']); ?></td>
                        <td><?php echo htmlspecialchars($row['stgreenweight']); ?></td>
                        <td><?php echo $plan * $tireWeight; ?></td>
                        <td><?php echo $plan * $stGreenWeight; ?></td>
                        <td><?php echo $actual * $tireWeight; ?></td>
                        <td><?php echo $actual * $stGreenWeight; ?></td>
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
            </tr>
        </table>
    <?php endif; ?>
</body>
</html>