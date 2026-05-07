<!DOCTYPE html>
<html>
<head>

<script>
        // Function to highlight the screen in green if the value in the "To be Produce (Nos)" column is 0
        function highlightIfZero() {
            var table = document.querySelector('table'); // Select the table element
            var rows = table.querySelectorAll('tr'); // Select all rows of the table

            // Loop through each row (start from 1 to skip the header row)
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].querySelectorAll('td'); // Select all cells of the current row
                var toBeProduceValue = parseInt(cells[6].innerText); // Get the value of "To be Produce (Nos)"

                // If the value is 0, apply green background to the entire row
                if (toBeProduceValue === 0) {
                    rows[i].style.backgroundColor = '#00FF00'; // Green color
                }
            }
        }

        // Call the function when the page is fully loaded
        window.onload = function() {
            highlightIfZero();
        };
       
    </script>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 20px auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Enable horizontal scrolling */
        }

        table {
            
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed; /* Fixed layout to enable fixed table headers */
        }

        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
            white-space: nowrap; /* Prevent text wrapping */
        }

        th {
            background-color: #f28018;
            color: #ffffff;
            font-weight: bold;
            position: sticky; /* Sticky position to fix headers */
            top: 0; /* Position headers at the top */
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f2f2f2;
        }
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

        // Check if there are results from either query and display them in a single table
        if (($result5->num_rows > 0) || ($result6->num_rows > 0)) {
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

           // Display data rows from the first query
while ($row = $result5->fetch_assoc()) {
    echo "<tr>
            <td style='background-color: #FFCCCC;'></td> <!-- This should display the row number -->
            <td style='background-color: #FFCCCC;'>{$row['erp_number']}</td>
            <td style='background-color: #FFCCCC;'>{$row['wonos']}</td>
            <td style='background-color: #FFCCCC;'>{$row['refs']}</td>
            <td style='background-color: #FFCCCC;'>{$row['country']}</td>
            <td style='background-color: #FFCCCC;'>{$row['total_quantity']}</td> 
            <td style='background-color: #FFCCCC;'>0</td>
            <td style='background-color: #FFCCCC;'>{$row['total_quantity']}</td> 
            <td style='background-color: #FFCCCC;'>" . number_format($row['total_quantity_kgs']) . "</td> 
            <td style='background-color: #FFCCCC;'>{$row['wo_release_date']}</td> 
            <td style='background-color: #FFCCCC;'>{$row['production_complete_date']}</td>
            <td style='background-color: #FFCCCC;'></td>
            <td style='background-color: #FFCCCC;'>{$row['dispatch_date']}</td>
            <td style='background-color: #FFCCCC;'><button onclick='redirectToAnotherPage(\"{$row['erp_number']}\")'>check</button></td> <!-- Add a button for action -->
        </tr>";
}

            // Display data rows from the second query
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
            echo "No results found in either table.";
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
