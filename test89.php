<!DOCTYPE html>
<html>
<head>
    <style>
        /* Your CSS styles */
    </style>
</head>
<body>
<div class="container">
    <!-- Date range input -->
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date">
        <input type="submit" value="Submit">
    </form>
    
    <?php
    // Error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Database connection parameters
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

    // Check if start and end dates are provided
    if(isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];

        // SQL query with date range filter for pros and related tables
        $sql5 = "SELECT pros.erp_number, 
                        GROUP_CONCAT(DISTINCT dwork2.wono SEPARATOR ', ') AS wonos,
                        GROUP_CONCAT(DISTINCT dwork2.ref SEPARATOR ', ') AS refs,
                        SUM(dwork2.quantity) AS total_quantity,
                        SUM(dwork2.kgs) AS total_quantity_kgs,
                        MAX(dwork2.date) AS wo_release_date,
                        pros.dispatch_date,
                        complete_date.com_date AS production_complete_date,
                        country.country AS country
                FROM pros 
                LEFT JOIN dwork2 ON pros.erp_number = dwork2.erp
                LEFT JOIN complete_date ON pros.erp_number = complete_date.erp
                LEFT JOIN country ON pros.erp_number = country.erp
                WHERE complete_date.com_date BETWEEN '$start_date' AND '$end_date'
                GROUP BY pros.erp_number, pros.dispatch_date, complete_date.com_date, country.country
                ORDER BY pros.dispatch_date ASC";
        
        // Execute the query
        $result5 = $conn->query($sql5);

        // Check if there are results and display them
        if ($result5->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>#</th> 
                        <th>ERP</th>
                        <th>Work Order No</th>
                        <th>Customer Order <br> Reference</th>
                        <th>Country</th>
                        <th>Quantity  <br>(Nos)</th>
                        <th>To be  <br> Produce <br>(Nos)</th>
                        <th>Completed <br>(Nos)</th>
                        <th>Quantity <br>(Kgs) </th>
                        <th>WO Release <br> Date</th>
                        <th>Production <br>Complete <br>Date </th>
                        <th>Cargo Ready <br> Date</th>
                        <th>Dispatch <br> Date</th>
                        <th>Check Order</th>
                    </tr>";

            // Display data rows
            while ($row = $result5->fetch_assoc()) {
                echo "<tr>
                        <td></td> <!-- This should display the row number -->
                        <td>{$row['erp_number']}</td>
                        <td>{$row['wonos']}</td>
                        <td>{$row['refs']}</td>
                        <td>{$row['country']}</td>
                        <td>{$row['total_quantity']}</td> 
                        <td>0</td>
                        <td>{$row['total_quantity']}</td> 
                        <td>" . number_format($row['total_quantity_kgs']) . "</td> 
                        <td>{$row['wo_release_date']}</td> 
                        <td>{$row['production_complete_date']}</td>
                        <td></td>
                        <td>{$row['dispatch_date']}</td>
                        <td><button onclick='redirectToAnotherPage(\"{$row['erp_number']}\")'>check</button></td> <!-- Add a button for action -->
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "No results found in pros and related tables.";
        }

        // SQL query with date range filter for production_data table
        $sql6 = "SELECT erp, 
                        work_order_no, 
                        customer_order_reference, 
                        country, 
                        quantity_nos, 
                        to_be_produced_nos, 
                        completed_nos, 
                        quantity_kgs, 
                        wo_release_date, 
                        production_complete_date, 
                        cargo_ready_date
                FROM production_data
                WHERE production_complete_date BETWEEN '$start_date' AND '$end_date'
                ORDER BY production_complete_date ASC";
        
        // Execute the query
        $result6 = $conn->query($sql6);

        // Check if there are results and display them
        if ($result6->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>#</th> 
                        <th>ERP</th>
                        <th>Work Order No</th>
                        <th>Customer Order <br> Reference</th>
                        <th>Country</th>
                        <th>Quantity  <br>(Nos)</th>
                        <th>To be  <br> Produce <br>(Nos)</th>
                        <th>Completed <br>(Nos)</th>
                        <th>Quantity <br>(Kgs) </th>
                        <th>WO Release <br> Date</th>
                        <th>Production <br>Complete <br>Date </th>
                        <th>Cargo Ready <br> Date</th>
                        <th>Dispatch <br> Date</th>
                        <th>Check Order</th>
                    </tr>";

            // Display data rows
            while ($row = $result6->fetch_assoc()) {
                echo "<tr>
                        <td></td> <!-- This should display the row number -->
                        <td>{$row['erp']}</td>
                        <td>{$row['work_order_no']}</td>
                        <td>{$row['customer_order_reference']}</td>
                        <td>{$row['country']}</td>
                        <td>{$row['quantity_nos']}</td> 
                        <td>{$row['to_be_produced_nos']}</td>
                        <td>{$row['completed_nos']}</td> 
                        <td>" . number_format($row['quantity_kgs']) . "</td> 
                        <td>{$row['wo_release_date']}</td> 
                        <td>{$row['production_complete_date']}</td>
                        <td>{$row['cargo_ready_date']}</td>
                        <td></td>
                        <td><button onclick='redirectToAnotherPage(\"{$row['erp']}\")'>check</button></td> <!-- Add a button for action -->
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "No results found in production_data table.";
        }
    }

    // Close connection
    $conn->close();

    // JavaScript function to redirect to another page
    echo "<script>
            function redirectToAnotherPage(erpNumber) {
                // Redirect to another page with the ERP number as a parameter
                window.location.href = 'planning3.php?erp=' + encodeURIComponent(erpNumber);
            }
          </script>";
    ?>
</div>
</body>
</html>
