





<?php
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

// SQL to delete all data
$sql_delete = "DELETE FROM `worder56`";

// SQL to reset auto-increment counter
$sql_reset_auto_increment = "ALTER TABLE `worder56` AUTO_INCREMENT = 1";

// Execute delete query
if ($conn->query($sql_delete) === TRUE) {
    echo "All records deleted successfully.";
    
    // Execute reset auto-increment query
    if ($conn->query($sql_reset_auto_increment) === TRUE) {
        echo " Auto-increment counter reset successfully.";
    } else {
        echo " Error resetting auto-increment counter: " . $conn->error;
    }
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <style>
        /* Primary typeface - Cantarell */
        body {
            font-family: 'Cantarell Regular', sans-serif;
        }

        h1, h2, h3 {
            font-family: 'Cantarell Bold', sans-serif;
        }

        /* Secondary typeface - Open Sans */
        p {
            font-family: 'Open Sans Regular', sans-serif;
        }

        /* Import button styles */
        input[type="file"] {
            background-color: #F28018;
            color: #000000;
            padding: 10px;
            border: none;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }


        .centered-form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('atire3.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <?php
    include './includes/data_base_save_update.php';
    $msg = '';
    $AppCodeObj = new databaseSave();
    if (isset($_POST['submit'])) {
        $msg = $AppCodeObj->addw("worder");
    }
    ?>
 <div class="centered-form">
    <div class="container">
       
            <h2>Please Import Work Order</h2> <!-- Centered heading -->
            <form method="post" action="import_new.php" enctype="multipart/form-data">
                <input type="file" name="excel_file" accept=".csv">
                <input type="submit" name="import" value="Import work order">
            </form>
        </div>
    </div>
</body>
</html>
