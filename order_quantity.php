<!DOCTYPE html>
<html>
<head>
<div class="button-container">
    
</div>
    <title>Retrieve Data by Icode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
        }

        h1 {
            color: #333;
        }

        form {
            margin: 20px;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #333;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #000000;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #FFFFFF;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px;
        }

        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #F28018;
            color: #fff;
            font-weight: bold;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s; /* Add a smooth transition for the background color change */
        }

        .button-container button:hover {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .stock-row td {
            font-family: 'Open Sans', sans-serif;
            font-weight: regular;
        }

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
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
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }

        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s; /* Add a smooth transition for the background color change */
        }


        .stock-row td {
            font-family: 'Open Sans', sans-serif;
            font-weight: regular;
        }
    </style>


</head>


<body>
<div class="button-container">
    <button>
        <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To dashboard</a>
    </button>
</div>
    <h1>Retrieve Data by Icode</h1>
    <div class="container">
    <form method="post">
        <label for="icode">Enter Icode:</label>
        <input type="text" id="icode" name="icode" required>
        <input type="submit" class="btn btn-primary" value="Retrieve Data">
    </form>


    <?php
    // Database connection details
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create a connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if the form has been submitted

        // Get the user-provided icode
        $desired_icode = $_POST['icode'];

        // Query to retrieve work orders data, calculate total quantity, and fetch description for the specified icode
        $sql = "SELECT w.icode, SUM(w.new) AS total_quantity, d.description AS description
                FROM worder w
                LEFT JOIN tire d ON w.icode = d.icode
                WHERE w.icode = '$desired_icode'
                GROUP BY w.icode";
        $result = $conn->query($sql);

        // Check if any records were found
        if ($result->num_rows > 0) {
            // Output table header
          
            echo "<table>
                    <tr>
                        <th>Work Order</th>
                        <th>Description</th>
                        <th>Total Quantity (pcs)</th>
                    </tr>";

            // Output data for the specified icode
            while ($row = $result->fetch_assoc()) {
                $icode = $row['icode'];
                $description = $row['description'];
                $totalQuantity = $row['total_quantity'];

                echo "<tr>
                        <td>$icode</td>
                        <td>$description</td>
                        <td>$totalQuantity</td>
                    </tr>";
            }

            // Close table
            echo "</table>";
        } else {
            echo "No work orders found for the specified icode.";
        }
    }

    // Close the connection
    $conn->close();
    ?>
    </div>
</body>
</html>


<!DOCTYPE html>
<html>
<head>
    <title>Retrieve All Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
        }

        h1 {
            color: #333;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
        }

        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #F28018;
            color: #fff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Retrieve All Data</h1>

    <?php
    // Database connection details
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create a connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to retrieve all work orders data and descriptions
    $sql = "SELECT w.icode, SUM(w.new) AS total_quantity, d.description AS description
            FROM worder w
            LEFT JOIN tire d ON w.icode = d.icode
            GROUP BY w.icode";
    $result = $conn->query($sql);

    // Check if any records were found
    if ($result->num_rows > 0) {
        // Output table header
        

        echo "<table>
                <tr>
                    <th>Work Order</th>
                    <th>Description</th>
                    <th>Total Quantity (pcs)</th>
                </tr>";

        // Output data for all records
        while ($row = $result->fetch_assoc()) {
            $icode = $row['icode'];
            $description = $row['description'];
            $totalQuantity = $row['total_quantity'];

            echo "<tr>
                    <td>$icode</td>
                    <td>$description</td>
                    <td>$totalQuantity</td>
                </tr>";
        }

        // Close table
        echo "</table>";
    } else {
        echo "No work orders found in the database.";
    }

    // Close the connection
    $conn->close();
    ?>
</body>
</html>

