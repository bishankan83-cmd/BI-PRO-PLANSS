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

// Close the connection
$conn->close();
?>
