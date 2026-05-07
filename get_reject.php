<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Selection</title>
    <style>
        /* Your CSS styles */
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        select,
        input[type="date"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            margin-bottom: 10px;
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

        table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
        }

        .btn-container {
            margin-top: 20px;
            text-align: center;
        }

        input[type="button"],
        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="button"]:hover,
        input[type="submit"]:hover {
            background-color: #333333;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Reject Selection</h1>

    <!-- Form for user input -->
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="reject">Reject Type</label>
        <select name="reject" id="reject">
            <option value="">Select Reject Type</option>
            <?php
            $itemCodes = ["REJECT", "BGRADE", "HOLD", "CUT"];
            foreach ($itemCodes as $code) {
                echo "<option value='$code'>$code</option>";
            }
            ?>
        </select>

        <label for="reason">Reason</label>
        <select name="reason" id="reason">
            <option value="">Select Reason</option>
            <?php
            // Fetch unique reasons from the database
            $mysqli = new mysqli('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');

            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            $reasonQuery = "SELECT DISTINCT reason FROM reject123";
            $reasonResult = $mysqli->query($reasonQuery);

            if ($reasonResult) {
                while ($row = $reasonResult->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['reason'], ENT_QUOTES) . "'>" . htmlspecialchars($row['reason'], ENT_QUOTES) . "</option>";
                }
                $reasonResult->free();
            } else {
                echo "<option value=''>No reasons available</option>";
            }

            $mysqli->close();
            ?>
        </select>

        <label for="startDate">Start Date</label>
        <input type="date" name="startDate" id="startDate">

        <label for="endDate">End Date</label>
        <input type="date" name="endDate" id="endDate">
      
        <div class="btn-container">
            <input type="submit" name="submit" value="Submit">
        </div>
    </form>

    <?php
    // PHP code for handling user input and constructing the SQL query
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve user inputs
        $userReject = $_POST["reject"];
        $userReason = $_POST["reason"];
        $startDate = $_POST["startDate"];
        $endDate = $_POST["endDate"];
       
        // Create a database connection
        $mysqli = new mysqli('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');

        // Check the connection
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        // Modify your SQL query based on user input
        $query = "SELECT * FROM reject123 WHERE 1";

        if (!empty($userReject)) {
            $query .= " AND reject = '$userReject'";
        }

        if (!empty($userReason)) {
            $query .= " AND reason = '$userReason'";
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query .= " AND date BETWEEN '$startDate' AND '$endDate'";
        } elseif (!empty($startDate)) {
            $query .= " AND date >= '$startDate'";
        } elseif (!empty($endDate)) {
            $query .= " AND date <= '$endDate'";
        }

        // Execute the query
        $result = $mysqli->query($query);

        // Initialize totals
        $totalReject = 0;
        $totalWeight = 0;

        if ($result) {
            echo "<table border='1'>";
            echo "<tr><th>Item Code</th><th>Description</th><th>Date</th><th>Shift</th><th>Reason</th><th>Type</th><th>No Of Reject</th><th>Total Weight</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['icode'], ENT_QUOTES) . "</td>";

                // Query the tire table for the description based on the icode
                $tireQuery = "SELECT description FROM tire_details WHERE icode = '{$row['icode']}'";
                $tireResult = $mysqli->query($tireQuery);

                if ($tireResult && $tireRow = $tireResult->fetch_assoc()) {
                    echo "<td>" . htmlspecialchars($tireRow['description'], ENT_QUOTES) . "</td>";
                } else {
                    echo "<td>No description found</td>";
                }

                echo "<td>" . htmlspecialchars($row['date'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['shift'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['reason'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['reject'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['cstock'], ENT_QUOTES) . "</td>";

                // Query the rweight table for the weight based on the icode
                $rweightQuery = "SELECT stgreenweight FROM tire_details WHERE icode = '{$row['icode']}'";
                $rweightResult = $mysqli->query($rweightQuery);

                if ($rweightResult && $rweightRow = $rweightResult->fetch_assoc()) {
                    $weight = $rweightRow['stgreenweight'];
                    $totalWeightForRow = $weight * $row['cstock'];
                    echo "<td>" . htmlspecialchars($totalWeightForRow, ENT_QUOTES) . "</td>";

                    // Add to total weight
                    $totalWeight += $totalWeightForRow;
                } else {
                    // Set total weight to 0 if no weight found
                    echo "<td>0</td>";
                }

                // Add to total reject
                $totalReject += $row['cstock'];

                echo "</tr>";
            }

            echo "</table>";

            // Display totals
            echo "<p><strong>Total No Of Reject:</strong> " . htmlspecialchars($totalReject, ENT_QUOTES) . "</p>";
            echo "<p><strong>Total Weight:</strong> " . htmlspecialchars($totalWeight, ENT_QUOTES) . "</p>";

            // Free the result sets
            $result->free();
        } else {
            echo "Error: " . $mysqli->error;
        }

        // Close the database connection
        $mysqli->close();
    }
    ?>

</div>

</body>
</html>
