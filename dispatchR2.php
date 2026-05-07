<!DOCTYPE html>
<html>

<head>
    <title>ERP Data Search Results</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
            text-align: center;
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
        }

        form {
            margin: 20px;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
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

        .total {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>ERP Data Search Results</h1>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $erp_number = $_POST["erp_number"];

        $host = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $database = "planatir_task_managemen";

        $conn = new mysqli($host, $username, $password, $database);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $query = "SELECT * FROM dwork2 WHERE erp = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $erp_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $totalNewColumn = 0;

            echo "<table border='1'>";
            echo "<tr><th>Date</th><th>Customer</th><th>Work Order No</th><th>Ref</th><th>ERP</th><th>Item Code</th><th>Total Size</th><th>Brand</th><th>Color</th><th>Fit</th><th>Rim</th><th>Cons</th><th>Finished Weight</th><th>PTV</th><th>New</th><th>CBM</th><th>KGS</th></tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                          <td>" . $row["date"] . "</td>
                          <td>" . $row["Customer"] . "</td>
                          <td>" . $row["wono"] . "</td>
                          <td>" . $row["ref"] . "</td>
                          <td>" . $row["erp"] . "</td>
                          <td>" . $row["icode"] . "</td>
                          <td>" . $row["t_size"] . "</td>
                          <td>" . $row["brand"] . "</td>
                          <td>" . $row["col"] . "</td>
                          <td>" . $row["fit"] . "</td>
                          <td>" . $row["rim"] . "</td>
                          <td>" . $row["cons"] . "</td>
                          <td>" . $row["fweight"] . "</td>
                          <td>" . $row["ptv"] . "</td>
                          <td>" . $row["new"] . "</td>
                          <td>" . $row["cbm"] . "</td>
                          <td>" . $row["kgs"] . "</td>
                       </tr>";

                $totalNewColumn += floatval($row["new"]);
            }

            // Display the total row below the "New" column
            echo "<tr class='total'>
                      <td colspan='14'></td>
                     
                      <td>$totalNewColumn</td>
                      <td colspan='2'></td>
                  </tr>";
            
            echo "</table>";

            echo "<form method='post' action='copy_dataR.php'>
                      <input type='hidden' name='erp_number' value='" . $erp_number . "'>
                      <input type='submit' value='Click To Next'>
                  </form>";
        } else {
            echo "No data found for ERP Number: " . $erp_number;
        }

        $stmt->close();
        $conn->close();
    }
    ?>
</body>

</html>
