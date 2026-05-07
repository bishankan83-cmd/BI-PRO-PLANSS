

<!DOCTYPE html>
<html>

<head>
    <title>Shift Plan Table</title>
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

        input[type="date"],
        select {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
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

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        .blink {
            animation: blink 1s infinite;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Shift Plan Table</h1>

        <?php
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        // Create a connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // SQL query to select data from the shift_plan table
        $sql = "SELECT * FROM shift_plan";

        // Execute the query
        $result = $conn->query($sql);
        ?>

        <form method="post" action="export.php">
            <input type="submit" name="export_excel" value="Export to Excel">
        </form>
<br></br>

        <form action="copy_data.php">
            <input type="submit" value="Please Click This Button After Export excel" class="blink">
        </form>


    <?php
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr>";

        echo "<th>Cavity Name";
        echo "</th><th>Mold Name";
        echo "</th><th>ICode";
        echo "</th><th>Description";
        echo "</th><th>Rim";
        echo "</th><th>Brand";
        echo "</th><th>Type";
        echo "</th><th>Colour";
        echo "</th><th>Green Tire Weight";
        echo "</th><th>Order";
        echo "</th><th>Plan Pcs";
        echo "</th><th>Plan Weight";
        echo "</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";

            echo "<td>" . $row["cavity_name"] . "</td>";
            echo "<td>" . $row["mold_name"] . "</td>";
            echo "<td>" . $row["icode"] . "</td>";

            // Query the tire_details table to get additional details
            $icode = $row["icode"];
            $tireDetailsSql = "SELECT Description, Brand, Type, Colour, Rim, greenweight FROM tire_details WHERE icode = '$icode'";
            $tireDetailsResult = $conn->query($tireDetailsSql);

            if ($tireDetailsResult->num_rows > 0) {
                $tireDetails = $tireDetailsResult->fetch_assoc();
                echo "<td>" . $tireDetails["Description"] . "</td>";
                echo "<td>" . $tireDetails["Rim"] . "</td>";
                echo "<td>" . $tireDetails["Brand"] . "</td>";
                echo "<td>" . $tireDetails["Type"] . "</td>";
                echo "<td>" . $tireDetails["Colour"] . "</td>";
                echo "<td>" . $tireDetails["greenweight"] . "</td>";
            } else {
                echo "<td>N/A</td>";
                echo "<td>N/A</td>";
                echo "<td>N/A</td>";
                echo "<td>N/A</td>";
                echo "<td>N/A</td>";
                echo "<td>N/A</td>";
            }

            // Calculate and display the sum of positive icode tobes
            $positiveIcodeTobesSql = "SELECT SUM(tobe) AS sum_positive_tobes FROM tobeplan1 WHERE icode = '$icode' AND tobe > 0";
            $positiveIcodeTobesResult = $conn->query($positiveIcodeTobesSql);

            if ($positiveIcodeTobesResult->num_rows > 0) {
                $positiveIcodeTobesData = $positiveIcodeTobesResult->fetch_assoc();
                echo "<td>(" . $positiveIcodeTobesData["sum_positive_tobes"] . ")</td>";
            } else {
                echo "<td>0</td>";
            }

            // Get Plan Pcs
            $planPcs = $row["tobe"];
            echo "<td>" . $planPcs . "</td>";

            // Calculate and display the product of Green Tire Weight and Plan Pcs
            $greenTireWeight = $tireDetails["greenweight"];
            $product = $greenTireWeight * $planPcs;
            echo "<td>" . $product . "</td>";

            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "No records found in the database.";
    }
    ?>

</body>
</html>
