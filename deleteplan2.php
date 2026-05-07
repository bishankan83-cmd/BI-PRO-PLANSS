
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

                // Delete all data from the plannew table
                $deletePlannewSql = "DELETE FROM plannew";
                $conn->query($deletePlannewSql);

            // Update the availability_date of all presses in the 'press' table
$updatePressSql = "UPDATE press SET availability_date = DATE_FORMAT(NOW(), '%Y-%m-%d 07:00:00')";
$conn->query($updatePressSql);

// Update the availability_date of all molds in the 'mold' table
$updateMoldSql = "UPDATE mold SET availability_date = DATE_FORMAT(NOW(), '%Y-%m-%d 07:00:00')";
$conn->query($updateMoldSql);

// Update the availability_date of all cavities in the 'cavity' table
$updateCavitySql = "UPDATE cavity SET availability_date = DATE_FORMAT(NOW(), '%Y-%m-%d 07:00:00')";
$conn->query($updateCavitySql);

  
                 // Delete all data from the stock table
               $deleteStockSql = "DELETE FROM stock";
               $conn->query($deleteStockSql);

                // Delete all data from the merge table
                $deleteStockSql = "DELETE FROM merged_data";
                $conn->query($deleteStockSql);

                // Delete all data from the merge table
                $deleteStockSql = "DELETE FROM match_table";
                $conn->query($deleteStockSql);
        
                 // Delete all data from the stock table
                 $deleteStockSql = "DELETE FROM tobeplan1";
                 $conn->query($deleteStockSql);
         
                  // Delete all data from the stock table
                  $deleteStockSql = "DELETE FROM wcopy";
                  $conn->query($deleteStockSql);
          

                // Commit the transaction if all queries are successful
                $conn->commit();

                header("Location: wcopy.php");
                exit();
        ?>
    </div>
</body>
</html>