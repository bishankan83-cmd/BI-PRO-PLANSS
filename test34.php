<?php
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

// Fetch data ordered by increasing width of batch numbers
$sql = "SELECT * FROM another_table_name ORDER BY LENGTH(batch), batch";
$result = $conn->query($sql);

// Update each row
if ($result->num_rows > 0) {
    // Loop through each row
    while ($row = $result->fetch_assoc()) {
        // Example of update query, replace with your own update logic
        $update_sql = "UPDATE another_table_name SET column_name = 'new_value' WHERE id = " . $row['id'];
        $conn->query($update_sql);
        
        // Example output to demonstrate update process
        echo "Updated row with ID: " . $row['id'] . "<br>";
    }
    echo "Data updated successfully.";
} else {
    echo "No rows found.";
}

// Close connection
$conn->close();
?>
