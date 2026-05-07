<!DOCTYPE html>
<html>
<head>
    <title>Delete ERP Data</title>
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
        <h1>Delete ERP Data</h1>
        <?php
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $success_message = "";
        $error_message = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $erp_to_delete = $_POST["erp"];

            $sql_old_process = "DELETE FROM `old_process` WHERE `erp` = '$erp_to_delete'";
            $sql_wcopy = "DELETE FROM `wcopy` WHERE `erp` = '$erp_to_delete'";

            $conn->begin_transaction();

            if ($conn->query($sql_old_process) === TRUE && $conn->query($sql_wcopy) === TRUE) {
                $conn->commit();
                $success_message = "Data from both old_process and wcopy tables for ERP number $erp_to_delete deleted successfully.";
                header("Location: convertstockR.php");
                exit();
            } else {
                $conn->rollback();
                $error_message = "Error deleting data: " . $conn->error;
            }
        }

        $conn->close();
        ?>

        <?php
        if (!empty($success_message)) {
            echo "<p style='color: green;'>$success_message</p>";
        }
        if (!empty($error_message)) {
            echo "<p style='color: red;'>$error_message</p>";
        }
        ?>
        <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            ERP Number: <input type="text" name="erp" required>
            <input type="submit" value="Delete">
        </form>
    </div>
</body>
</html>
