<?php
// Database connection settings
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_datetime = $_POST["start_datetime"];
    $end_datetime = $_POST["end_datetime"];

    // Retrieve data from the merged_data table within the specified time range
    $sql = "SELECT * FROM merged_data 
            WHERE start_datetime >= '$start_datetime' AND end_datetime <= '$end_datetime'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h2>Events within the specified time range:</h2>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>Start Date and Time: " . $row["start_datetime"] . "<br>";
            echo "End Date and Time: " . $row["end_datetime"] . "<br></li>";
        }
        echo "</ul>";
    } else {
        echo "No events found within the specified time range.";
    }
}

// Close the database connection
$conn->close();
?>
