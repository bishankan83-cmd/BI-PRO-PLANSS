<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Display</title>
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
    <h1>Data Display From Pending Reject</h1>

    <?php
    // Database connection settings
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    try {
        // Establish database connection
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL query to select data from the table and join with tire_details
        $sql = "SELECT t2.id, t2.icode, t2.cstock, t2.date, t2.shift, t2.reason, t2.reject, td.stgreenweight, td.description
                FROM template2b t2
                LEFT JOIN tire_details td ON t2.icode = td.icode";

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        // Execute the query
        $stmt->execute();

        // Fetch all rows from the result set
        $rows = $stmt->fetchAll();

        // Initialize totals
        $totalStock = 0;
        $totalWeight = 0;

        // Check if there are rows returned
        if (count($rows) > 0) {
            // Output the data in a table format
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Item Code</th><th>Description</th><th>Current Stock</th><th>Weight per Item</th><th>Total Weight</th><th>Date</th><th>Shift</th><th>Reason</th><th>Reject</th></tr>";
            foreach ($rows as $row) {
                $totalWeightForRow = $row['cstock'] * $row['stgreenweight'];

                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['icode']."</td>";
                echo "<td>".$row['description']."</td>";
                echo "<td>".$row['cstock']."</td>";
                echo "<td>".$row['stgreenweight']."</td>";
                echo "<td>".$totalWeightForRow."</td>";
                echo "<td>".$row['date']."</td>";
                echo "<td>".$row['shift']."</td>";
                echo "<td>".$row['reason']."</td>";
                echo "<td>".$row['reject']."</td>";
                echo "</tr>";

                // Accumulate totals
                $totalStock += $row['cstock'];
                $totalWeight += $totalWeightForRow;
            }
            echo "</table>";

            // Display totals
            echo "<p><strong>Total Current Stock:</strong> " . htmlspecialchars($totalStock, ENT_QUOTES) . "</p>";
            echo "<p><strong>Total Weight:</strong> " . htmlspecialchars($totalWeight, ENT_QUOTES) . "</p>";
        } else {
            echo "No data found";
        }
    } catch(PDOException $e) {
        // Display error message if something goes wrong with the database connection or query
        echo "Connection failed: " . $e->getMessage();
    }

    // Close the database connection
    $conn = null;
    ?>
</div>

</body>
</html>
