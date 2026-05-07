<!DOCTYPE html>
<html>
<head>
    <title>Copy Data</title>
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
        <h1>Please press the Next button</h1>
        <form action="rimport5.php" method="post">
            <input type="submit" name="copy_data" value="Next">
        </form>
    </div>
</body>
</html>

<?php
// Establish a connection to the MySQL database
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the "Copy Data" button is clicked
if (isset($_POST['copy_data'])) {
    // Check if there is data in the "stock" table
    $checkQuery = "SELECT COUNT(*) as count FROM stock";
    $result = $conn->query($checkQuery);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $rowCount = $row['count'];
        
        if ($rowCount > 0) {
            // Handle the case where there is data in the "stock" table
            // You can redirect or display a message as needed
            header("Location: revshow.php");
            exit();
        }
    }

    // Copy data from "realstock" table to "stock" table
    $copyQuery = "INSERT INTO stock SELECT * FROM realstock";
    
    if ($conn->query($copyQuery) === TRUE) {
        // Data copied successfully, redirect to "subtract.php"
        header("Location: revshow.php");
        exit();
    } else {
        // Handle any errors that occur during the data copy operation
        echo "Error: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

