<?php

// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs for security
    $icode = $conn->real_escape_string($_POST['icode']);
    $mold_id = $conn->real_escape_string($_POST['mold_id']);
    
    // Attempt insert query execution
    $sql = "INSERT INTO tire_mold (icode, mold_id) VALUES ('$icode', '$mold_id')";
    if ($conn->query($sql) === TRUE) {
        echo "New record inserted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data into tire_mold</title>
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
            background-color: rgba(255, 255, 255, 0.8); /* Add a semi-transparent white background to the container */
            padding: 50px;
            border-radius: 20px; /* Add rounded corners to the container */
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

        form {
            margin-top: 20px;
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
        <h2>Insert Data into tire_mold</h2>
        <form method="post">
            <label for="icode">Icode:</label><br>
            <input type="text" id="icode" name="icode"><br>
            <label for="mold_id">Mold ID:</label><br>
            <input type="text" id="mold_id" name="mold_id"><br><br>
            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>
