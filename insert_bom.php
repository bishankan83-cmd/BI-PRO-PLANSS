<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data</title>

    <style>
        /* Your CSS styles */
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

        .stock-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
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

        .button-container button:hover {
            background-color: #333333; /* Change the background color on hover */
        }

        .stock-row td {
            font-family: 'Open Sans', sans-serif;
            font-weight: regular;
        }

        /* Add a fixed position to the table header */
        .stock-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Add a background color and some padding to the header row */
        .stock-table .header {
            background-color: #F28018;
            padding: 10px;
        }

        /* Adjust the position of the body cells to make room for the fixed header */
        .stock-table td {
            padding-top: 30px; /* Adjust this value based on your header height */
        }

        /* Style the select box */
        .select-container {
            margin: 10px;
            text-align: center;
        }

        select {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }
    </style>
</head>
<body>
    <h2>Insert Data</h2>

    <?php
    // Database credentials
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    try {
        // Create a new PDO instance
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get data from the form
            $item = $_POST['item'];
            $icode = $_POST['icode'];
            $t_size = $_POST['t_size'];
        
            $a = $_POST['a'];
            $b = $_POST['b'];
            $c = $_POST['c'];
            $d = $_POST['d'];
            $e = $_POST['e'];
            $f = $_POST['f'];
            $g = $_POST['g'];
            $h = $_POST['h'];
            $i = $_POST['i'];
            $j = $_POST['j'];
            $k = $_POST['k'];
            $l = $_POST['l'];
            $m = $_POST['m'];
            $n = $_POST['n'];
            $o = $_POST['o'];
            $p = $_POST['p'];
            $grand_total_compound_weight = $_POST['grand_total_compound_weight'];
            $color = $_POST['color'];
            $brand = $_POST['brand'];
            $green_tire_weight = $_POST['green_tire_weight'];

            // Prepare SQL statement for execution
            $stmt = $conn->prepare("INSERT INTO bom_new46 (Item, icode, t_size, `Item Description`, 
                a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p 
                `Grand Totalcompound weight`, Color, Brand, `Green Tire weight`, `PBweight`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Bind parameters
            $stmt->bindParam(1, $item);
            $stmt->bindParam(2, $icode);
            $stmt->bindParam(3, $t_size);
            $stmt->bindParam(4, $item_description);
            $stmt->bindParam(5, $a);
            $stmt->bindParam(6, $b);
            $stmt->bindParam(7, $c);
            $stmt->bindParam(8, $d);
            $stmt->bindParam(9, $e);
            $stmt->bindParam(10, $f);
            $stmt->bindParam(11, $g);
            $stmt->bindParam(12, $h);
            $stmt->bindParam(13, $i);
            $stmt->bindParam(14, $j);
            $stmt->bindParam(15, $k);
            $stmt->bindParam(16, $l);
            $stmt->bindParam(17, $m);
            $stmt->bindParam(18, $n);
            $stmt->bindParam(19, $o);
            $stmt->bindParam(20, $p);
            $stmt->bindParam(21, $grand_total_compound_weight);
            $stmt->bindParam(22, $color);
            $stmt->bindParam(23, $brand);
            $stmt->bindParam(24, $green_tire_weight);

            // Execute the statement
            $stmt->execute();

            echo "Record inserted successfully";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Close the database connection
    $conn = null;
    ?>

    <!-- HTML form -->
    <form method="post">
        <!-- Added table header -->
        <table border="1">
            <tr>



           <tr><th>ID</th><th>Item</th><th>icode</th><th>Tire Description</th><th>Item Description</th><th>ATPRS</th><th>B-ATS 15</th><th>B-BNS 24</th><th>BG-BLS 12</th><th>CG - BS 901</th><th>C - SMS 501</th><th>C-ATS 20</th><th>C-SMS 702</th><th>T - TRS 102</th><th>T-ATNM S</th><th>T-ATS 30</th><th>T-ATS 35</th><th>T-KS 40</th><th>T-TRNMS 402</th><th>T-TRNMS 402G</th><th>T-TRS 202</th><th>Grand Totalcompound weight</th><th>Color</th><th>Brand</th><th>Green Tire weight</th>
              
            </tr>
            <!-- End of table header -->

            <!-- Form fields for user input -->
            <tr>
                <td>Auto-incremented ID</td>
                <td><input type="text" name="item" required></td>
                <td><input type="text" name="icode" required></td>
                <td><input type="text" name="t_size" required></td>
                <td><input type="text" name="item_description" required></td>
                <td><input type="text" name="a"></td>
                <td><input type="text" name="b"></td>
                <td><input type="text" name="c"></td>
                <td><input type="text" name="d"></td>
                <td><input type="text" name="e"></td>
                <td><input type="text" name="f"></td>
                <td><input type="text" name="g"></td>
                <td><input type="text" name="h"></td>
                <td><input type="text" name="i"></td>
                <td><input type="text" name="j"></td>
                <td><input type="text" name="k"></td>
                <td><input type="text" name="l"></td>
                <td><input type="text" name="m"></td>
                <td><input type="text" name="n"></td>
                <td><input type="text" name="o"></td>
                <td><input type="text" name="p"></td>
                <td><input type="text" name="grand_total_compound_weight" required></td>
                <td><input type="text" name="color" required></td>
                <td><input type="text" name="brand" required></td>
                <td><input type="text" name="green_tire_weight" required></td>
            </tr>
        </table>

        <input type="submit" value="Insert">
    </form>
</body>
</html>
