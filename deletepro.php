
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

               
                 // Delete all data from the stock table
               $deleteStockSql = "DELETE FROM stock";
               $conn->query($deleteStockSql);

            
        
                 // Delete all data from the stock table
                 $deleteStockSql = "DELETE FROM tobeplan1";
                 $conn->query($deleteStockSql);
         
                  // Delete all data from the stock table
                  $deleteStockSql = "DELETE FROM wcopy";
                  $conn->query($deleteStockSql);
          

                // Commit the transaction if all queries are successful
                $conn->commit();

                header("Location: wcopy1.php");
                exit();
        ?>
    </div>
</body>
</html>