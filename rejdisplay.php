






<?php


// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// If form is submitted, update the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    // Escape user inputs for security
    $item = $conn->real_escape_string($_POST['item']);
    $icode = $conn->real_escape_string($_POST['icode']);
    $t_size = $conn->real_escape_string($_POST['t_size']);
    $item_description = $conn->real_escape_string($_POST['item_description']);
    $a = $conn->real_escape_string($_POST['a']);
    $b = $conn->real_escape_string($_POST['b']);
    $c = $conn->real_escape_string($_POST['c']);
    $d = $conn->real_escape_string($_POST['d']);
    $e = $conn->real_escape_string($_POST['e']);
    $f = $conn->real_escape_string($_POST['f']);
    $g = $conn->real_escape_string($_POST['g']);
    $h = $conn->real_escape_string($_POST['h']);
    $i = $conn->real_escape_string($_POST['i']);
    $j = $conn->real_escape_string($_POST['j']);
    $k = $conn->real_escape_string($_POST['k']);
    $l = $conn->real_escape_string($_POST['l']);
    $m = $conn->real_escape_string($_POST['m']);
    $n = $conn->real_escape_string($_POST['n']);
    $o = $conn->real_escape_string($_POST['o']);
    $p = $conn->real_escape_string($_POST['p']);
    $grand_total_compound_weight = $conn->real_escape_string($_POST['grand_total_compound_weight']);
    $color = $conn->real_escape_string($_POST['color']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $green_tire_weight = $conn->real_escape_string($_POST['green_tire_weight']);
    $pb_weight = $conn->real_escape_string($_POST['pb_weight']);

    $sql = "UPDATE bom_new45 SET
            a='$a', b='$b', c='$c', d='$d', e='$e', f='$f', g='$g', h='$h', i='$i', j='$j', 
            k='$k', l='$l', m='$m', n='$n', o='$o', p='$p', `Grand Totalcompound weight`='$grand_total_compound_weight', 
            Color='$color', Brand='$brand', `Green Tire weight`='$green_tire_weight', PBweight='$pb_weight' 
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch data from the bom_new table
$sql = "SELECT * FROM bom_new45";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOM Editor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        input[type="text"] {
            border: none; /* Remove border */
            
            width: 100%;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>BOM Editor</h2>

<table>
    <tr>
    <th> icode</th>
        <th>t_size</th>
        <th>ATPRS</th>
        <th>B-ATS 15</th>
        <th>B-BNS 24</th>
        <th>BG-BLS 12</th>
        <th>CG - BS 901</th>
        <th>C - SMS 501</th>
        <th>C-ATS 20</th>
        <th>C-SMS 702</th>
        <th>T - TRS 102</th>
        <th>T-ATNM S</th>
        <th>T-ATS 30</th>
        <th>T-ATS 35</th>
        <th>T-KS 40</th>
        <th>T-TRNMS 402</th>
        <th>T-TRNMS 402G</th>
        <th>T-TRS 202</th>
        <th>Grand Totalcompound weight</th>
        
        <th>Green Tire weight</th>
        <th>PBweight</th>
        <th>Action</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
            echo "<input type='hidden' name='id' value='" . $row['id'] . "'>"; // Hidden input for ID
            // Display other fields
            echo "<td>" . $row['icode'] . "</td>";
            echo "<td>" . $row['t_size'] . "</td>";


            echo "<td><input type='text' name='a' value='" . $row['a'] . "'></td>";
            echo "<td><input type='text' name='b' value='" . $row['b'] . "'></td>";
            echo "<td><input type='text' name='c' value='" . $row['c'] . "'></td>";
            echo "<td><input type='text' name='d' value='" . $row['d'] . "'></td>";
            echo "<td><input type='text' name='e' value='" . $row['e'] . "'></td>";
            echo "<td><input type='text' name='f' value='" . $row['f'] . "'></td>";
            echo "<td><input type='text' name='g' value='" . $row['g'] . "'></td>";
            echo "<td><input type='text' name='h' value='" . $row['h'] . "'></td>";
            echo "<td><input type='text' name='i' value='" . $row['i'] . "'></td>";
            echo "<td><input type='text' name='j' value='" . $row['j'] . "'></td>";
            echo "<td><input type='text' name='k' value='" . $row['k'] . "'></td>";
            echo "<td><input type='text' name='l' value='" . $row['l'] . "'></td>";
            echo "<td><input type='text' name='m' value='" . $row['m'] . "'></td>";
            echo "<td><input type='text' name='n' value='" . $row['n'] . "'></td>";
            echo "<td><input type='text' name='o' value='" . $row['o'] . "'></td>";
            echo "<td><input type='text' name='p' value='" . $row['p'] . "'></td>";
            echo "<td><input type='text' name='grand_total_compound_weight' value='" . $row['Grand Totalcompound weight'] . "'></td>";
           // echo "<td><input type='text' name='color' value='" . $row['Color'] . "'></td>";
           // echo "<td><input type='text' name='brand' value='" . $row['Brand'] . "'></td>";
            echo "<td><input type='text' name='green_tire_weight' value='" . $row['Green Tire weight'] . "'></td>";
            echo "<td><input type='text' name='pb_weight' value='" . $row['PBweight'] . "'></td>";
            echo "<td><input type='submit' value='Update'></td>";
            echo "</form>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='24'>No data found</td></tr>"; // Adjusted colspan
    }
    ?>
</table>

</body>
</html>



