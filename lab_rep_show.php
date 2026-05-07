<?php

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL statement to select data from importmix table
$sql = "SELECT * FROM importmix";

// Execute query
$result = $conn->query($sql);

// Check if there are any rows returned
if ($result->num_rows > 0) {
    // Start HTML table
    echo "<table border='1'>";
    echo "<tr>";
    // Output table headers
    while ($row = $result->fetch_assoc()) {
        foreach ($row as $column => $value) {
            echo "<th>$column</th>";
        }
        break; // Break after outputting headers from the first row
    }
    echo "</tr>";

    // Output data rows
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    // End HTML table
    echo "</table>";
} else {
    echo "0 results";
}

// Close connection
$conn->close();
?>
