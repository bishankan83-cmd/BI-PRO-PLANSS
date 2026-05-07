

<?php
// Database connection
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

// Query to check the number of unique `erp` values
$sql = "SELECT COUNT(DISTINCT erp) as unique_erp_count FROM tobeplan";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the result
    $row = $result->fetch_assoc();
    if ($row['unique_erp_count'] > 1) {
        // If there is more than one unique `erp`, redirect to plannew45new2 page and show the message
        echo "<script>
            alert('Please generate before planning');
            window.location.href = 'plannew45new2.php';
        </script>";
    } elseif ($row['unique_erp_count'] == 1) {
        // If there is exactly one unique `erp`, check if there's any data
        $sql_data = "SELECT COUNT(*) as count FROM tobeplan";
        $result_data = $conn->query($sql_data);
        if ($result_data->num_rows > 0) {
            $row_data = $result_data->fetch_assoc();
            if ($row_data['count'] > 0) {
                // If there is data in the table, redirect to plannew45 page and show the message
                echo "<script>
                    alert('Please generate before planning');
                    window.location.href = 'plannew45.php';
                </script>";
            } else {
                // If there is no data, proceed with the normal flow
                echo "No data in the table. Proceeding as normal.";
            }
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // If there are no `erp` values, proceed with the normal flow
        echo "No data in the table. Proceeding as normal.";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>









<?php

// MySQL database connection details
$servername = "localhost"; // Replace with your MySQL server name
$username = "planatir_task_managemen"; // Replace with your MySQL username
$password = "Bishan@1919"; // Replace with your MySQL password
$dbname = "planatir_task_managemen"; // Replace with your MySQL database name

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to delete data from the rword table
    $sql = "DELETE FROM `rworder`";

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$conn = null;

?>




<!DOCTYPE html>
<html>
<head>
    <title>Delete Information</title>
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

        h2 {
            color: #000000;
            font-weight: bold;
            font-size: 24px;
        }

        form {
            margin-top: 20px;
        }

        input[type="submit"] {
            background-color: #F28018;
            background-color: ;
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
    


    <?php
    // Assuming you have already established a MySQL database connection

    $servername = "localhost"; // Replace with your MySQL server name
    $username = "planatir_task_managemen"; // Replace with your MySQL username
    $password = "Bishan@1919"; // Replace with your MySQL password
    $dbname = "planatir_task_managemen"; // Replace with your MySQL database name

    // Create a new PDO instance
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }


    // Check if the ERP number is provided
    if (isset($_POST['erp'])) {
        $erpNumber = $_POST['erp'];

        // Prepare the delete statements for both tables
        $stmt1 = $pdo->prepare("DELETE FROM worder WHERE erp = :erpNumber");
        $stmt2 = $pdo->prepare("DELETE FROM work_order WHERE erp = :erpNumber");

        // Bind the parameter for both statements
        $stmt1->bindParam(':erpNumber', $erpNumber);
        $stmt2->bindParam(':erpNumber', $erpNumber);

        // Execute the delete statements
        $deleteSuccessful = $stmt1->execute() && $stmt2->execute();

        if ($deleteSuccessful) {
            // Deletion successful
            echo '<div class="message success">Information deleted successfully.</div>';
            echo '<script>window.location.href = "rimport.php";</script>';
            exit; // Exit to prevent further execution
        } else {
            // Deletion failed
            echo '<div class="message error">An error occurred while deleting the information.</div>';
        }
    }
    ?>
 <div class="container">
 <h2>Enter ERP Number:</h2>
    <!-- HTML form to input the ERP number -->
    <form method="POST" action="">
    
    <input type="text" name="erp" id="erp">
    <br> 
    <br><!-- Add a line break to move the button to the next line -->
    <input type="submit" value="Delete"> <!-- Delete button below the input field -->
</form>

    </div>
</body>
</html>
