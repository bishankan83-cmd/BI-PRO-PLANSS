<!DOCTYPE html>
<html>
<head>
<style>
    body {
        background-color: #f0f0f0; /* Light gray background for the page */
    }
    
    .container {
        text-align: center;
        margin-top: 20px;
        background-color: #ffffff; /* White background for the container */
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
    }

    table {
        border-collapse: collapse;
        width: 50%;
        margin: auto;
        background-color: #f9f9f9; /* Light gray background for the table */
        border-radius: 8px;
        overflow: hidden; /* To clip the border-radius on the table */
    }

    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #3498db;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #ddd;
    }
</style>
</head>
<body>

<?php
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

// Retrieve and display data from the table
$sqlRetrieve = "SELECT * FROM dailypro_imported_data";
$result = $conn->query($sqlRetrieve);

echo '<div class="container">

<h2>Daily Production</h2>';
if ($result->num_rows > 0) {

    
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Amount</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row["id"]."</td>
                <td>".$row["icode"]."</td>
                <td>".$row["amount"]."</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No data found.";
}
echo '</div>';

// Close the connection
$conn->close();
?>

</body>
</html>
