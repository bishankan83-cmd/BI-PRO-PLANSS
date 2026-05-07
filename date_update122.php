<?php
        // MySQL database credentials
        $host = 'localhost';
        $username = 'planatir_task_management';
        $password = 'Bishan@1919';
        $database = 'planatir_task_management';

        // Create connection
        $conn = new mysqli($host, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

                // Update the availability_date of all presses in the 'press' table
                $updatePressSql = "UPDATE press SET availability_date = NOW()";
               $conn->query($updatePressSql);

                // Update the availability_date of all molds in the 'mold' table
                $updateMoldSql = "UPDATE mold SET availability_date = NOW()";
               $conn->query($updateMoldSql);

                // Update the availability_date of all cavities in the 'cavity' table
              $updateCavitySql = "UPDATE cavity SET availability_date = NOW()";
               $conn->query($updateCavitySql);

             
  
  
         
              
         
              
          

                // Commit the transaction if all queries are successful
                $conn->commit();

                header("Location: planewd2.php");
                exit();
        ?>
    </div>
</body>
</html>