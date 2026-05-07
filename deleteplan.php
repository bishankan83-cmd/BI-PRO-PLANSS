


<!DOCTYPE html>
<html>
<head>
    <title>Delete Data</title>
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
</head>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Data</title>
    <style>
        .container {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Delete Data</h1>

        <form method="post" action="">
            <input type="submit" name="delete" value="Delete All Plan">
        </form>

        <form method="post" action="deleteplan222.php">
            <input type="submit" name="delete" value="Delete one Plan">
        </form>

        <?php
        // MySQL database credentials
        $host = 'localhost';
        $username = 'planatir_task_managemen';
        $password = 'Bishan@1919';
        $database = 'planatir_task_managemen';

        // Create connection
        $conn = new mysqli($host, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the delete button is clicked
        if (isset($_POST['delete'])) {
            // Start the deletion transaction
            $conn->begin_transaction();

            try {
                // Delete all data from the plannew table
                $deletePlannewSql = "DELETE FROM plannew";
                $conn->query($deletePlannewSql);

                // Update the availability_date of all presses in the 'press' table
                $updatePressSql = "UPDATE press SET availability_date = NOW()";
                $conn->query($updatePressSql);

                
                // Update the availability_date of all presses in the 'press' table
             $updatePressSql = "UPDATE tire SET availability_date = NOW()";
            $conn->query($updatePressSql);

                // Update the availability_date of all molds in the 'mold' table
                $updateMoldSql = "UPDATE mold SET availability_date = NOW()";
                $conn->query($updateMoldSql);

                // Update the availability_date of all cavities in the 'cavity' table
                $updateCavitySql = "UPDATE cavity SET availability_date = NOW()";
                $conn->query($updateCavitySql);

                 // Delete all data from the stock table
        $deleteStockSql = "DELETE FROM stock";
        $conn->query($deleteStockSql);

              // Delete all data from the merge table
              $deleteStockSql = "DELETE FROM merged_data";
              $conn->query($deleteStockSql);
        
                 // Delete all data from the stock table
                 $deleteStockSql = "DELETE FROM tobeplan1";
                 $conn->query($deleteStockSql);

         // Delete all data from the stock table
         $deleteStockSql = "DELETE FROM old_process";
         $conn->query($deleteStockSql);

                // Commit the transaction if all queries are successful
                $conn->commit();

                echo "All data in the 'plannew' table has been deleted successfully, and the availability dates in the 'press', 'mold', and 'cavity' tables have been updated.";
            } catch (Exception $e) {
                // Rollback the transaction if an error occurs
                $conn->rollback();

                echo "Error deleting data: " . $e->getMessage();
            }
        }
        ?>
    </div>
</body>
</html>
