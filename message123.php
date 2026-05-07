<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        p {
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #F28018;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Revise Work Order</h2>
        <?php
        // Your PHP code goes here...

        // Database connection parameters
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $database = "planatir_task_managemen";

        // Create a database connection
        $connection = mysqli_connect($servername, $username, $password, $database);

        // Check the connection
        if (!$connection) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Define the template table and realstock table names
        $templateTable = 'daily_plan_data1';
        $realstockTable = 'realstock';
        $backupTable = 'daily_plan_data'; // New table to store backup data

        // Copy data from daily_plan_data1 to backup table
        $copyQuery = "INSERT INTO $backupTable SELECT * FROM $templateTable";
        $copyResult = mysqli_query($connection, $copyQuery);

        if (!$copyResult) {
            echo "Error copying data to $backupTable: " . mysqli_error($connection);
            mysqli_close($connection);
            exit();
        }

        // Update cstock in the realstock table based on icode from the template table
        $updateQuery = "UPDATE $realstockTable r
                        JOIN (
                            SELECT t.icode, SUM(t.AdditionalData) AS TotalAdditionalData
                            FROM $templateTable t
                            GROUP BY t.icode
                        ) t ON r.icode = t.icode
                        SET r.cstock = r.cstock + t.TotalAdditionalData";

        $updateResult = mysqli_query($connection, $updateQuery);

        if ($updateResult) {
            // Update successful

            // Now, delete all data in the daily_plan_data1 table
            $deleteQuery = "DELETE FROM $templateTable";
            $deleteResult = mysqli_query($connection, $deleteQuery);

            if ($deleteResult) {
                // Deletion successful
               
                // Message to be echoed
                $message = "This work order does not contain new tire code, the previous tire code is the same, there is a future plan related to the relevant transition.";
                
                // Echo the message within a paragraph element
                echo "<p>$message</p>";

                // Add a button to go to the dashboard
                echo '<a href="get.php"><button>Click To Next</button></a>';
            } else {
                echo "Error deleting data from $templateTable: " . mysqli_error($connection);
            }
        } else {
            echo "Error updating cstock: " . mysqli_error($connection);
        }

        // Close the database connection
        mysqli_close($connection);
        ?>
    </div>
</body>
</html>
