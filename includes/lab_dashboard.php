<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
    
        .message {
    margin-bottom: 20px;
    padding: 10px; /* Adjust padding as needed */
    border-radius: 4px;
    animation: blink-animation 1s steps(5, start) infinite;
    text-align: center; /* Center text horizontally inside .message */
    /* OR */
    display: flex;
    justify-content: center; /* Center .message horizontally if content allows */
}

.message.success {
    background-color: red;
    border-color: #c3e6cb;
    color: black;
    font-weight: bold; /* Make the text bold */
    font-size: 18px; /* Adjust the font size as needed */
}

        .message.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        @keyframes blink-animation {
            to {
                visibility: hidden;
            }
        }
    </style>
</head>
<body>
    <div class="container">
       
        <?php
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            echo '<div class="message error">Connection failed: ' . $conn->connect_error . '</div>';
        } else {
            // SQL query to check if there is data in the table
            $sql_check_data = "SELECT COUNT(*) AS count FROM `bcompound98`";
            $result = $conn->query($sql_check_data);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['count'] > 0) {
                    echo '<div class="message success">Please Check Re Generate QR Code</div>';
                } 
            } 

            // Close connection
            $conn->close();
        }
        ?>
    </div>
</body>
</html>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Styling for the Dashboard elements */
        .element-content {
    background-color: #F28018;
    padding: 0;
    text-align: center;
    box-shadow: 2px 2px 4px rgba(0, 0, 10, 100);
    
    /* Make the element fill the full page */
    position: right;
    top: 0;
    left: 5px;
    width: 100%;
    height: 100%;
}



        .element-box {
            background-color: #FFFFFF;
            padding: 20px;
            border-radius: 60px;
            margin: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .element-header {
            color: #000000;
            font-size: 24px;
            font-weight: bold;
        }

        .element-box a {
            text-decoration: none;
            color: #F28018;
        }

        /* Styling for the specific elements with id="myDIV" */
        #myDIV {
            color: #000000;
            font-weight: bold;
            font-size: 20px;
            margin-top: 10px;
        }
    </style>
    <title>Your Dashboard</title>
</head>
<body>
    <div class="element-content">
        <h6 class="element-header">Dashboard</h6>
        <div class="row">
  

            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="show_mixing.php">
                    <div id="myDIV">Compound Production Details</div>
                </a>

                <a class="element-box el-tablo" href="lab_qr_details.php">
                    <div id="myDIV">QR Code Details</div>
                </a>
                   
                <a class="element-box el-tablo" href="lab_rep_show.php">
                    <div id="myDIV">Test Report</div>
                </a>
            <p class="red-text">ddddddddddddddddddd</p>
  <p class="red-text">ddddddddd</p>
  <p class="blue-text">ddddddd</p>
  <p class="red-text">dddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddd</p>
  <p class="red-text">dddddddddddddddddddddddddddddddddddddd</p>

  <p class="red-text">dddddddddddddddddddddddddddddddddddddd</p>


            </div>

            
        </div>
    </div>
</body>
</html>