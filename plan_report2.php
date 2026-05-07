<!DOCTYPE html>
<html>
<head>
    <title>Shift Plan Table</title>
</head>
<body>
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
    $sql = "SELECT * FROM daily_plan";

    // Execute the query
    $result = $conn->query($sql);
    ?>
    
    <form method="post" action="export.php">
        <input type="submit" name="export_excel" value="Export to Excel">
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

            echo "<td>" . $row["CavityName"] . "</td>";
            echo "<td>" . $row["MoldName"] . "</td>";
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
