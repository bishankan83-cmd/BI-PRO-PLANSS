



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order</title>
    <style>
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stock-table th,
        .stock-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .stock-table th {
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
            transition: background-color 0.3s;
        }

        .button-container button:hover {
            background-color: #333333;
        }

        .search-form {
            text-align: center;
            margin: 10px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
        }

        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        body {
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
            text-align: center;
        }

        h4 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .table .header {
            background-color: #F28018;
            padding: 10px;
        }

        .table td {
            padding-top: 30px;
        }

        .table td:nth-child(9),
        .table td:nth-child(10),
        .table td:nth-child(11),
        .table td:nth-child(12),
        .table td:nth-child(13) {
            text-align: right;
        }

        .highlight td {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .center-enlarge {
            font-size: 1.2em;
        }

        .filter-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            margin: 20px auto;
            max-width: 600px;
        }

        .filter-container h1 {
            margin-bottom: 20px;
            color: #333;
        }

        .filter-container button {
            background-color: orange;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .filter-container button:hover {
            background-color: black;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="button-container">
        <button>
            <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To Dashboard</a>
        </button>
    </div>

    <div class="filter-container">
        <h1>Filter Date Range And Export Excel</h1>
        <form action="work_order_result.php" method="post">
            <button type="submit">Filter Date</button>
        </form>
    </div>

    <h4>Work Order</h4>
    <form action="" method="GET" class="search-form">
        <input type="text" name="search" required value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="form-control" placeholder="Enter ERP Number">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <div class="container">
        <table class="table">
            <thead>
                <tr class="header">
                    <th>NO</th>
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
                </tr>
            </thead>
            <tbody>
                <?php
                $con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

                if (!$con) {
                    die("Connection failed: " . mysqli_connect_error());
                }

                // Function to safely format numbers
                function safe_number_format($value, $decimals = 2) {
                    // Check if the value is numeric or can be converted to a float
                    if (is_numeric($value) && !is_null($value)) {
                        return number_format((float)$value, $decimals);
                    }
                    // Return 0.00 or 0.0000 if the value is invalid
                    return number_format(0, $decimals);
                }

                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $filtervalues = mysqli_real_escape_string($con, $_GET['search']);
                    $query = "SELECT * FROM worder WHERE CONCAT(erp) LIKE '%$filtervalues%'";
                    $query_run = mysqli_query($con, $query);

                    if (mysqli_num_rows($query_run) > 0) {
                        $columnNumber = 1;

                        foreach ($query_run as $items) {
                            ?>
                            <tr>
                                <td><?= $columnNumber++; ?></td>
                                <td><?= htmlspecialchars($items['icode']); ?></td>
                                <td><?= htmlspecialchars($items['t_size']); ?></td>
                                <td><?= htmlspecialchars($items['brand']); ?></td>
                                <td><?= htmlspecialchars($items['col']); ?></td>
                                <td><?= htmlspecialchars($items['fit']); ?></td>
                                <td><?= htmlspecialchars($items['rim']); ?></td>
                                <td><?= htmlspecialchars($items['cons']); ?></td>
                                <td><?= safe_number_format($items['fweight'], 2); ?></td>
                                <td><?= safe_number_format($items['ptv'], 4); ?></td>
                                <td><?= htmlspecialchars($items['new']); ?></td>
                                <td><?= safe_number_format($items['cbm'], 4); ?></td>
                                <td><?= safe_number_format($items['kgs'], 2); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="13">No Record Found</td>
                        </tr>
                        <?php
                    }
                } else {
                    $query = "SELECT DISTINCT erp FROM worder";
                    $query_run = mysqli_query($con, $query);

                    if (mysqli_num_rows($query_run) > 0) {
                        while ($erpRow = mysqli_fetch_assoc($query_run)) {
                            $erp = mysqli_real_escape_string($con, $erpRow['erp']);
                            $subQuery = "SELECT * FROM worder WHERE erp = '$erp'";
                            $subQuery_run = mysqli_query($con, $subQuery);

                            if (mysqli_num_rows($subQuery_run) > 0) {
                                $columnNumber = 1;
                                echo '<tr class="highlight"><td colspan="13" class="center-enlarge">ERP: ' . htmlspecialchars($erp) . '</td></tr>';

                                foreach ($subQuery_run as $items) {
                                    ?>
                                    <tr>
                                        <td><?= $columnNumber++; ?></td>
                                        <td><?= htmlspecialchars($items['icode']); ?></td>
                                        <td><?= htmlspecialchars($items['t_size']); ?></td>
                                        <td><?= htmlspecialchars($items['brand']); ?></td>
                                        <td><?= htmlspecialchars($items['col']); ?></td>
                                        <td><?= htmlspecialchars($items['fit']); ?></td>
                                        <td><?= htmlspecialchars($items['rim']); ?></td>
                                        <td><?= htmlspecialchars($items['cons']); ?></td>
                                        <td><?= safe_number_format($items['fweight'], 2); ?></td>
                                        <td><?= safe_number_format($items['ptv'], 4); ?></td>
                                        <td><?= htmlspecialchars($items['new']); ?></td>
                                        <td><?= safe_number_format($items['cbm'], 4); ?></td>
                                        <td><?= safe_number_format($items['kgs'], 2); ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="13">No Record Found</td>
                        </tr>
                        <?php
                    }
                }

                mysqli_close($con);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>