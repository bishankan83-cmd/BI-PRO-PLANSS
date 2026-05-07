<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First SQL query to fetch data from multiple tables
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
         WHERE MONTH(pros.dispatch_date) = MONTH(CURRENT_DATE()) 
         AND YEAR(pros.dispatch_date) = YEAR(CURRENT_DATE())
         GROUP BY pros.erp_number, pros.dispatch_date, complete_date.com_date, country.country
         ORDER BY pros.dispatch_date ASC";

$result5 = $conn->query($sql5);



// Second SQL query to select data ordered by production_complete_date
$sql = "SELECT * FROM production_data ORDER BY production_complete_date ASC";
$result = $conn->query($sql);

// Combined results array
$combinedResults = [];

// Fetch results from the first query
if ($result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        $combinedResults[] = [
            'erp_number' => $row['erp_number'],
            'wonos' => $row['wonos'],
            'refs' => $row['refs'],
            'country' => $row['country'],
            'total_quantity' => $row['total_quantity'],
            'total_quantity_kgs' => $row['total_quantity_kgs'],
            'wo_release_date' => $row['wo_release_date'],
            'dispatch_date' => $row['dispatch_date'],
            'production_complete_date' => $row['production_complete_date'],
            'cargo_ready_date' => '', // Placeholder for cargo_ready_date
            'to_be_produced_nos' => '', // Placeholder for to_be_produced_nos
            'completed_nos' => '', // Placeholder for completed_nos
        ];
    }
}

// Fetch results from the second query and include new columns
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $combinedResults[] = [
            'erp_number' => $row['erp'],
            'wonos' => $row['work_order_no'],
            'refs' => $row['customer_order_reference'],
            'country' => $row['country'],
            'total_quantity' => $row['quantity_nos'],
            'total_quantity_kgs' => $row['quantity_kgs'],
            'wo_release_date' => $row['wo_release_date'],
            'dispatch_date' => $row['production_complete_date'],
            'production_complete_date' => $row['production_complete_date'],
            'cargo_ready_date' => $row['cargo_ready_date'],
            'to_be_produced_nos' => $row['to_be_produced_nos'], // Add this line
            'completed_nos' => $row['completed_nos'], // Add this line
        ];
    }
}

// Output data in a single HTML table
echo "<h2>Combined Production Data</h2>";
// Output data in a single HTML table
echo "<h2>Combined Production Data</h2>";
echo "<table border='1'>";
echo "<tr>
        <th>#</th>
        <th>ERP</th>
        <th>Work Order No</th>
        <th>Customer Order <br> Reference</th>
        <th>Country</th>
        <th>Quantity <br>(Nos)</th>
        <th>To be <br> Produce <br>(Nos)</th>
        <th>Completed <br>(Nos)</th>
        <th>Quantity <br>(Kgs)</th>
        <th>WO Release <br> Date</th>
        <th>Production <br> Complete <br>Date</th>
        <th>Cargo Ready <br> Date</th>
        <th>Dispatch <br> Date</th>
        <th>Dispatch Month</th>
        <th>Check Order</th>
      </tr>";

// Check if combined results are available
if (!empty($combinedResults)) {
    // Display each row in the combined results
    foreach ($combinedResults as $index => $row) {
        // Check if both columns are empty
        $highlight = (empty($row['to_be_produced_nos']) && empty($row['completed_nos'])) ? 'background-color: #FFCCCC;' : '';

        echo "<tr style='$highlight'>
                <td>" . ($index + 1) . "</td>
                <td>" . $row['erp_number'] . "</td>
                <td>" . $row['wonos'] . "</td>
                <td>" . $row['refs'] . "</td>
                <td>" . $row['country'] . "</td>
                <td>" . $row['total_quantity'] . "</td>
                <td>" . $row['to_be_produced_nos'] . "</td> <!-- To be Produced (Nos) -->
                <td>" . $row['completed_nos'] . "</td> <!-- Completed (Nos) -->
                
                <td>" . number_format($data['total_quantity_kgs']) . "</td> 
                <td>" . $row['wo_release_date'] . "</td>
                <td>" . $row['production_complete_date'] . "</td>
                <td>" . $row['cargo_ready_date'] . "</td>
                <td>" . $row['dispatch_date'] . "</td>
                <td>" . date('F', strtotime($row['dispatch_date'])) . "</td> <!-- Dispatch Month -->
                <td><input type='checkbox' name='check_order[]' value='" . $row['erp_number'] . "'></td> <!-- Check Order -->
              </tr>";
    }
} else {
    echo "<tr><td colspan='15'>No results found.</td></tr>";
}

echo "</table>";



// Close the connection
$conn->close();
