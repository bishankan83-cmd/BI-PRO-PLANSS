<!DOCTYPE html>
<html>
<head>
    <title>Remove Data for ERP</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #333;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"] {
            padding: 5px;
            width: 250px;
        }

        input[type="submit"] {
            padding: 8px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Remove Data for ERP</h1>
    <form method="post" action="deleteerp.php">
        <label>Enter ERP Number:</label>
        <input type="text" name="erp" required>
        <input type="submit" value="Remove Data">
    </form>
</body>
</html>
<?php
// Retrieve the ERP number from the form submission
if (isset($_POST['erp'])) {
    $erpNumber = $_POST['erp'];

    // Database connection settings
    $servername = "localhost"; // Change this to your MySQL server address
    $username = "planatir_task_managemen"; // Change this to your MySQL username
    $password = "Bishan@1919"; // Change this to your MySQL password
    $dbname = "planatir_task_managemen";    // Change this to your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL query to remove data for the given ERP number and move it to another table
    $sql = "INSERT INTO `derp` SELECT * FROM `plannew` WHERE `erp` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $erpNumber);

    // Execute the query
    if ($stmt->execute()) {
        // Data moved successfully, now delete data from the original table
        $sql = "DELETE FROM `plannew` WHERE `erp` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $erpNumber);

        // Execute the delete query
        if ($stmt->execute()) {
            echo "Data for ERP number " . $erpNumber . " has been removed and moved to another table.";
        } else {
            echo "Error removing data: " . $conn->error;
        }
    } else {
        echo "Error moving data: " . $conn->error;
    }

    // Close the database connection
    $conn->close();

    
            // Redirect to confirmation page
            header("Location: deleteerp2.php");
            exit; // Make sure to add 'exit' to prevent further script execution
}
?>
