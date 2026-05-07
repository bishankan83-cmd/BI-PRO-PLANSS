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

$query = "SELECT plan_id, icode, start_date, end_date, TIMESTAMPDIFF(MINUTE, start_date, end_date) / creation_time AS id_count FROM new_table";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Create connection to a new database
    $newDbname = "planatir_task_managemen";
    $newConn = new mysqli($servername, $username, $password, $newDbname);

    // Check connection to the new database
    if ($newConn->connect_error) {
        die("New Connection failed: " . $newConn->connect_error);
    }



    while ($row = $result->fetch_assoc()) {
        $id = $row['plan_id'];
        $icode = $row['icode'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $id_count = floor($row['id_count']);

        // Insert data into the new table
        $insertQuery = "INSERT INTO new_table2 (id, icode, start_date, end_date, id_count) VALUES ('$id', '$icode', '$start_date', '$end_date', '$id_count')";
        $newConn->query($insertQuery);

       
    }

    $newConn->close();
} else {
    echo "No results found.";
}

$conn->close();
header("Location: idcount.php");
exit();
?>
