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
            echo '<script>window.location.href = "import22bnew3.php";</script>';
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
