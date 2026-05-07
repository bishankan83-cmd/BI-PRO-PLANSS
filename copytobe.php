<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Transfer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            text-align: center;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            margin: 50px auto;
            width: 400px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        .success-message {
            color: #0a8e00;
            font-weight: bold;
            margin-top: 20px;
        }
        .error-message {
            color: #ff0000;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Data Transfer</h2>
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $servername = "localhost"; // Change to your MySQL server hostname
        $username = "planatir_task_managemen"; // Change to your MySQL username
        $password = "Bishan@1919"; // Change to your MySQL password
        $database = "planatir_task_managemen"; // Change to your MySQL database name

        // Create a connection to the MySQL database
        $conn = new mysqli($servername, $username, $password, $database);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // SQL query to copy all data from 'tobeplan' to 'tobeplan1'
        $copySql = "INSERT INTO `tobeplan1` SELECT * FROM `tobeplan`";

        if ($conn->query($copySql) === TRUE) {
            // Output a success message
            echo '<p class="success-message">Data copied successfully</p>';
        } else {
            echo '<p class="error-message">Error copying data: ' . $conn->error . '</p>';
        }

        // Close the database connection
        $conn->close();
        header("Location: daily_production3.php");
          exit();
        ?>
    </div>
</body>
</html>
