<?php
// Connect to the database
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = mysqli_connect($servername, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for unmatched records
$sql = "SELECT t1.icode, t1.col ,t1.brand FROM worder t1 LEFT JOIN selectpress t2 ON t1.icode = t2.icode AND t1.col = t2.col AND t1.brand = t2.brand WHERE t2.icode";
$result = $conn->query($sql);

if ($result === false) {
    // Handle SQL error
    echo "SQL error: " . $conn->error;
} elseif ($result->num_rows > 0) {
    // Output any unmatched records
    echo "Unmatched records found in table1:<br>";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["icode"] . " colour: " . $row["col"] . "brand: " . $row["brand"] . "<br>";
    }
} else {
    echo "No unmatched records found in table1.";
}

// Close database connection
$conn->close();
?>




