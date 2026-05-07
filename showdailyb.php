<!DOCTYPE html>
<html>
<head>
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
    </style>
</head>
<body>
<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT template2.id, template2.icode, template2.cstock, template2.date, template2.shift, tire.description
        FROM template2
        JOIN tire ON template2.icode = tire.icode";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ICode</th><th>Description</th><th>CStock</th><th>Date</th><th>Shift</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        
        echo "<td>" . $row['icode'] . "</td>";
        echo "<td>" . $row['description'] . "</td>";
       
        echo "<td>" . $row['cstock'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['shift'] . "</td>";
        
        echo "</tr>";
    }
    
    // Add a button in the middle of the table
    echo "<tr class='button-container'><td colspan='5'>";
    echo "<form action='dashboard.php' method='GET'>";
    //echo "<form action='showdaily2b.php' method='GET'>";
    echo "<input type='hidden' name='parameter_name' value='1'>";
    echo "<button type='submit'>OK</button>";
    echo "</form>";
    echo "</td></tr>";
    
    echo "</table>";
} else {
    echo "0 results";
}

$conn->close();
?>
</body>
</html>
