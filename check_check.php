<?php
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

// Check for data in the tobeplan table
$tobeplan_query = "SELECT COUNT(*) AS count FROM tobeplan";
$tobeplan_result = $conn->query($tobeplan_query);
$tobeplan_row = $tobeplan_result->fetch_assoc();
$tobeplan_count = $tobeplan_row['count'];

// Check for data in the process table
$process_query = "SELECT COUNT(*) AS count FROM process";
$process_result = $conn->query($process_query);
$process_row = $process_result->fetch_assoc();
$process_count = $process_row['count'];

// Check if both tables have data
if ($tobeplan_count > 0 && $process_count > 0) {
    // Redirect to another page only if both tables have data
    header("Location: plannew45new2.php");
    exit();
} elseif ($tobeplan_count > 0 && $process_count == 0) {
    // If only tobeplan has data, delete all rows from tobeplan
    $delete_query = "DELETE FROM tobeplan";
    if ($conn->query($delete_query) === TRUE) {
        echo "All data deleted from the tobeplan table.";
    } else {
        echo "Error deleting data: " . $conn->error;
    }
} elseif ($process_count > 0 && $tobeplan_count == 0) {
    // If only process has data, delete all rows from process
    $delete_query = "DELETE FROM process";
    if ($conn->query($delete_query) === TRUE) {
        echo "All data deleted from the process table.";
    } else {
        echo "Error deleting data: " . $conn->error;
    }
} else {
    // Handle the case where both tables have no data
    echo "No data available in both tables.";
}

// Close the connection
$conn->close();
?>






<?php
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

// Check for data in the tobeplan table
$tobeplan_query = "SELECT COUNT(*) AS count FROM tobeplan_plan";
$tobeplan_result = $conn->query($tobeplan_query);
$tobeplan_row = $tobeplan_result->fetch_assoc();
$tobeplan_count = $tobeplan_row['count'];

// Check for data in the process table
$process_query = "SELECT COUNT(*) AS count FROM process_plan";
$process_result = $conn->query($process_query);
$process_row = $process_result->fetch_assoc();
$process_count = $process_row['count'];

// Check for data in the new tobeplan table
$tobeplan_new_query = "SELECT COUNT(*) AS count FROM tobeplan_tem";
$tobeplan_new_result = $conn->query($tobeplan_new_query);
$tobeplan_new_row = $tobeplan_new_result->fetch_assoc();
$tobeplan_new_count = $tobeplan_new_row['count'];

// Check for data in the new process table
$process_new_query = "SELECT COUNT(*) AS count FROM process_plan_tem";
$process_new_result = $conn->query($process_new_query);
$process_new_row = $process_new_result->fetch_assoc();
$process_new_count = $process_new_row['count'];

// Check if all tables have data
if ($tobeplan_count > 0 && $process_count > 0 && $tobeplan_new_count > 0 && $process_new_count > 0) {
    // Redirect to another page only if all tables have data
    header("Location: plannew45.php");
    exit();
} else {
    // Handle the case where one or more tables have no data
    echo "Please Try Again";
}

// Close the connection
$conn->close();
?>









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

