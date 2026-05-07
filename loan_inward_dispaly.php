<?php
// Database connection details
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

// SQL query to fetch data from the loan_inward_details table
$sql = "SELECT loan_number, loan_date, expected_delivery_date, suppliers_code, suppliers_name, rm_code, descriptions, number_of_bands FROM loan_inward_details";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row in an HTML table
    echo "<table border='1'>
            <tr>
                <th>Loan Number</th>
                <th>Loan Date</th>
                <th>Expected Delivery Date</th>
                <th>Suppliers Code</th>
                <th>Suppliers Name</th>
                <th>RM Code</th>
                <th>Descriptions</th>
                <th>Number of Bands</th>
            </tr>";

    // Fetch and display each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["loan_number"] . "</td>
                <td>" . $row["loan_date"] . "</td>
                <td>" . $row["expected_delivery_date"] . "</td>
                <td>" . $row["suppliers_code"] . "</td>
                <td>" . $row["suppliers_name"] . "</td>
                <td>" . $row["rm_code"] . "</td>
                <td>" . $row["descriptions"] . "</td>
                <td>" . $row["number_of_bands"] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 results found";
}

// Close connection
$conn->close();
?>
