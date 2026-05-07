<!DOCTYPE html>
<html>
<head>
    <title>Table with Bootstrap styles</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
</head>

<body>



    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Check work Order</h4>

                        <form action="comparee.php" method="POST">
    <label for="erp">Enter ERP Number:</label>
    <input type="text" id="erp" name="erp" required>
    <button type="submit" name="compare">Compare Data</button>
</form>

  

</a>
</html>


<style>
  .mismatched {
    background-color: lightblue;
  }
</style>
                        <?php
                        // Establish a connection to the MySQL database
                        $servername = "localhost";
                        $username = "planatir_task_managemen";
                        $password = "Bishan@1919";
                        $database = "planatir_task_managemen";

                        $conn = mysqli_connect($servername, $username, $password, $database);

                        if (!$conn) {
                            die("Connection failed: " . mysqli_connect_error());
                        }

                        // Compare and display mismatched data
                        if (isset($_POST['compare'])) {
                            // Retrieve the ERP number from the form
                            $erpNumber = $_POST['erp'];

                            // Query the order table to fetch the relevant data matching the ERP number
                            $orderQuery = "SELECT * FROM worder WHERE erp = '$erpNumber'";
                            $orderResult = mysqli_query($conn, $orderQuery);

                            if (!$orderResult) {
                                die("Error executing order query: " . mysqli_error($conn));
                            }

                            echo '<table class="table table-bordered">';
                            echo '<thead class="thead-light">';
                            echo '<tr>';
                            echo '<th>Tire ID</th>';
                            echo '<th>Tire Size</th>';
                            echo '<th>Colour</th>';
                            echo '<th>Brand</th>';
                            echo '<th>Rim</th>';
                     
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            while ($orderRow = mysqli_fetch_assoc($orderResult)) {
                                // Fetch the corresponding row from the data table based on Tireid
                                if (!empty($orderRow['icode'])) {
                                    $dataQuery = "SELECT * FROM selectpress WHERE icode = " . $orderRow['icode'];
                                    $dataResult = mysqli_query($conn, $dataQuery);

                                    if (!$dataResult) {
                                        die("Error executing data query: " . mysqli_error($conn));
                                    }

                                    $dataRow = mysqli_fetch_assoc($dataResult);

                                    // Compare each column and highlight mismatches
                                    $mismatch = false;
                                    if (
                                        $orderRow['icode'] !== $dataRow['icode'] ||
                                        $orderRow['rim'] !== $dataRow['rim'] ||
                                        $orderRow['col'] !== $dataRow['col'] ||
                                        $orderRow['t_size'] !== $dataRow['t_size'] 
                                    ) {
                                        $mismatch = true;
                                    }

                                    // Add table row with data
                                    echo '<tr' . ($mismatch ? ' class="mismatched"' : '') . '>';
                                    echo '<td>' . $orderRow['icode'] . '</td>';
                                    echo '<td>' . $orderRow['t_size'] . '</td>';
                                    echo '<td>' . $orderRow['col'] . '</td>';
                                    echo '<td>' . $orderRow['brand'] . '</td>';
                                    echo '<td>' . $orderRow['rim'] . '</td>';
                                    
                                    echo '</tr>';

                                    mysqli_free_result($dataResult);
                                }
                            }

                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            // ...
                        }

                        // Close the database connection
                        mysqli_close($conn);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
