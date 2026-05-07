<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Table</title>
</head>

<body>
    <table id="stockr-table" class="stockr-table">
        <tr class="header">
            <th>Item Code</th>
            <th>Description</th>
            <th>Brand</th>
            <th>Colour</th>
            <th>Stock On Hand</th>
            <th>Requirement</th>
            <th>Free Stock</th>
        </tr>
        <tbody>
            <?php
            // Database connection
            $con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

            if (!$con) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // Function to get the sum of requirements from 'worder'
            function getRequirementSum($icode, $con)
            {
                $query = "SELECT SUM(new) AS requirement_sum FROM worder WHERE icode = '$icode'";
                $result = mysqli_query($con, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    return $row['requirement_sum'];
                }

                return "N/A";
            }

            // Function to get the actual stock from 'stockr'
            function getActualStock($icode, $con)
            {
                $query = "SELECT cstock FROM stockr WHERE icode = '$icode'";
                $result = mysqli_query($con, $query);

                if ($result && mysqli_num_rows($result)) {
                    $row = mysqli_fetch_assoc($result);
                    return $row['cstock'];
                }

                return "N/A";
            }

            // Query to fetch data from 'realstock'
            $query = "SELECT * FROM realstock";
            $query_run = mysqli_query($con, $query);

            if (!$query_run) {
                echo "Error in stockr query: " . mysqli_error($con);
            } else {
                while ($items = mysqli_fetch_assoc($query_run)) {
            ?>
                    <tr class="stockr-row">
                        <td><?= $items['icode']; ?></td>
                        <td><?= $items['t_size']; ?></td>
                        <td><?= $items['brand']; ?></td>
                        <td><?= $items['col']; ?></td>
                        <td><?= $items['cstock']; ?></td>
                        <td><?= getRequirementSum($items['icode'], $con); ?></td>
                        <td><?= getActualStock($items['icode'], $con); ?></td>
                    </tr>
            <?php
                }
            }
            mysqli_close($con);
            ?>
        </tbody>
    </table>

    <!-- Button to download data as Excel -->
    <form action="download_excel.php" method="post">
        <button type="submit">Download Excel</button>
    </form>

</body>

</html>
