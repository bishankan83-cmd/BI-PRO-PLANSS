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

// Check if the form is submitted and get the user input
$po_number = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $po_number = $_POST['po_number'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Search</title>
</head>
<body>

<h2>Search for Purchase Order</h2>
<form method="post" action="">
    <label for="po_number">Enter PO Number:</label>
    <input type="text" id="po_number" name="po_number" required>
    <button type="submit">Search</button>
</form>

<?php
if ($po_number) {
    // Prepare the SQL query
    $sql = "SELECT po_number, po_date, expected_deliver_inhouse_date, supplier_code, supplier_name, rm_code, descriptions, number_of_bands, created_at
            FROM purchase_orders
            WHERE po_number = ?";

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $po_number);

    // Execute the query
    $stmt->execute();

    // Bind the result to variables
    $stmt->bind_result($po_number, $po_date, $expected_deliver_inhouse_date, $supplier_code, $supplier_name, $rm_code, $descriptions, $number_of_bands, $created_at);

    // Fetch and display data in a table if found
    if ($stmt->fetch()) {
        echo "<h2>Purchase Order Details</h2>";
        echo "<table border='1' cellpadding='8' cellspacing='0'>";
        echo "<tr>
                <th>PO Number</th>
                <th>PO Date</th>
                <th>Expected In-house Delivery Date</th>
                <th>Supplier Code</th>
                <th>Supplier Name</th>
                <th>RM Code</th>
                <th>Descriptions</th>
                <th>Number of Bands</th>
                <th>Created At</th>
              </tr>";
        echo "<tr>
                <td>" . htmlspecialchars($po_number) . "</td>
                <td>" . htmlspecialchars($po_date) . "</td>
                <td>" . htmlspecialchars($expected_deliver_inhouse_date) . "</td>
                <td>" . htmlspecialchars($supplier_code) . "</td>
                <td>" . htmlspecialchars($supplier_name) . "</td>
                <td>" . htmlspecialchars($rm_code) . "</td>
                <td>" . htmlspecialchars($descriptions) . "</td>
                <td>" . htmlspecialchars($number_of_bands) . "</td>
                <td>" . htmlspecialchars($created_at) . "</td>
              </tr>";
        echo "</table>";
    } else {
        echo "No record found for PO Number: " . htmlspecialchars($po_number);
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>

</body>
</html>
