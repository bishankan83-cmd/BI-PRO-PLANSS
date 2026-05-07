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

        #chart-container {
            margin-top: 40px;
        }

        canvas {
            width: 100%;
            max-width: 800px;
            height: 400px;
        }
    </style>
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        $query = "SELECT reason, SUM(cstock) as total_reject, SUM(cstock * stgreenweight) as total_weight
                  FROM reject123 r
                  LEFT JOIN tire_details td ON r.icode = td.icode
                  WHERE 1";

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

        $query .= " GROUP BY reason";

        // Execute the query
        $result = $mysqli->query($query);

        // Initialize totals
        $totalReject = 0;
        $totalWeight = 0;

        // Prepare data for chart
        $labels = [];
        $rejectData = [];
        $weightData = [];

        if ($result) {
            echo "<table border='1'>";
            echo "<tr><th>Reason</th><th>Total No Of Reject</th><th>Total Weight</th></tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['reason'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['total_reject'], ENT_QUOTES) . "</td>";
                echo "<td>" . htmlspecialchars($row['total_weight'], ENT_QUOTES) . "</td>";
                echo "</tr>";

                // Accumulate totals
                $totalReject += $row['total_reject'];
                $totalWeight += $row['total_weight'];

                // Collect data for the chart
                $labels[] = htmlspecialchars($row['reason'], ENT_QUOTES);
                $rejectData[] = (int)$row['total_reject'];
                $weightData[] = (int)$row['total_weight'];
            }

            echo "</table>";

            // Display overall totals
            echo "<p><strong>Total No Of Reject:</strong> " . htmlspecialchars($totalReject, ENT_QUOTES) . "</p>";
            echo "<p><strong>Total Weight:</strong> " . htmlspecialchars($totalWeight, ENT_QUOTES) . "</p>";

            // Free the result set
            $result->free();
        } else {
            echo "Error: " . $mysqli->error;
        }

        // Close the database connection
        $mysqli->close();
    }
    ?>

    <!-- Chart Container -->
    <div id="chart-container">
        <canvas id="rejectChart"></canvas>
    </div>

    <script>
        // JavaScript for rendering the chart
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('rejectChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'Total No Of Reject',
                        data: <?php echo json_encode($rejectData); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</div>

</body>
</html>
