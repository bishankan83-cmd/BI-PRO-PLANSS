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

        table, th, td {
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

        .styled-button {
    background-color: #000000;
    color: #FFFFFF;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-family: 'Cantarell', sans-serif;
    text-decoration: none;
}

.styled-button a {
    text-decoration: none;
    color: #FFFFFF;
}

.styled-button:hover {
    background-color: #FFA500; /* Change the background color on hover */
}


    </style>


<?php
// Connect to the MySQL database
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}



// Fetch data from the 'dwork' table
$sql = "SELECT * FROM dwork";
$result = $conn->query($sql);

$totalNewColumn = 0;
$totalNewColumnn = 0;
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Work Order Number</th>
        <th>Reference</th>
        <th>ERP</th>
        <th>Item Code</th>
        <th>Size</th>
        <th>Brand</th>
        <th>Color</th>
        <th>Fit</th>
        <th>Rim</th>
        <th>Consistency</th>
        <th>Freight Weight</th>
        <th>Packed TV</th>
        <th>New</th>
        <th>CBM</th>
        <th>Weight (kgs)</th>
        <th>Quantity</th>
        <th>Action</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
   echo "<tr>";
   echo "<td>" . $row['id'] . "</td>";
   echo "<td>" . $row['date'] . "</td>";
   echo "<td>" . $row['Customer'] . "</td>";
   echo "<td>" . $row['wono'] . "</td>";
   echo "<td>" . $row['ref'] . "</td>";
   echo "<td>" . $row['erp'] . "</td>";
   echo "<td>" . $row['icode'] . "</td>";
   echo "<td>" . $row['t_size'] . "</td>";
   echo "<td>" . $row['brand'] . "</td>";
   echo "<td>" . $row['col'] . "</td>";
   echo "<td>" . $row['fit'] . "</td>";
   echo "<td>" . $row['rim'] . "</td>";
   echo "<td>" . $row['cons'] . "</td>";
   echo "<td>" . $row['fweight'] . "</td>";
   echo "<td>" . $row['ptv'] . "</td>";
   echo "<td>" . $row['new'] . "</td>";
   echo "<td>" . $row['cbm'] . "</td>";
   echo "<td>" . $row['kgs'] . "</td>";
   echo "<td>" . $row['quantity'] . "</td>";
   echo "<td><a href='edit1.php?id=" . $row['id'] . "'>Edit</a></td>";
   echo "</tr>";
   $totalNewColumn += floatval($row["new"]);
   $totalNewColumnn += floatval($row["quantity"]);
}

// Display the total row below the "New" column
echo "<tr class='total'>
<td colspan='15'></td>

<td>$totalNewColumn</td>
<td colspan='2'></td>
<td>$totalNewColumnn</td>
<td colspan='2'></td>
</tr>";

echo "</table>";
echo '<button class="styled-button"><a href="d_add.php">NEXT</a></button>';

$conn->close();
?>
