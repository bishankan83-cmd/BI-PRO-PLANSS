<?php
// retrieve_data1234.php

// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the start_date from the form
$start_date = $_POST['start_date'];

// SQL query to retrieve data based on start_date
$sql = "SELECT * FROM new_process WHERE DATE(start_date) = '$start_date'";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Retrieve Data by Date</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 50px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        h1 {
            color: #000000;
            font-weight: bold;
            font-size: 24px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            text-align: left;
            padding: 8px;
            border: 1px solid #dddddd;
        }

        th {
            background-color: #f2f2f2;
            color: #000000;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Cantarell Bold', sans-serif;
        }

        input[type="submit"]:hover {
            background-color: #FFA726;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Data for Start Date: <?php echo htmlspecialchars($start_date); ?></h1>

    <?php
    if ($result->num_rows > 0) {
        echo "<table><tr><th>ID</th><th>Item Code</th><th>Mold ID</th><th>Tires per Mold</th><th>Cavity ID</th><th>Mold Name</th><th>Cavity Name</th><th>Press Name</th><th>Press ID</th><th>ERP</th><th>Serial</th><th>Is Completed</th><th>Is Highlighted</th><th>First To Be</th><th>Start Date</th></tr>";
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["id"]."</td><td>".$row["icode"]."</td><td>".$row["mold_id"]."</td><td>".$row["tires_per_mold"]."</td><td>".$row["cavity_id"]."</td><td>".$row["mold_name"]."</td><td>".$row["cavity_name"]."</td><td>".$row["press_name"]."</td><td>".$row["press_id"]."</td><td>".$row["erp"]."</td><td>".$row["serial"]."</td><td>".$row["is_completed"]."</td><td>".$row["is_highlighted"]."</td><td>".$row["first_tobe"]."</td><td>".$row["start_date"]."</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found for the selected date.</p>";
    }

    $conn->close();
    ?>
</div>
</body>
</html>
