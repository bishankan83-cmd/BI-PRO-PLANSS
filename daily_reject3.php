<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the date from dates table for id 1
$sql_get_date = "SELECT dates_c FROM dates WHERE date_id = 1";
$result = $conn->query($sql_get_date);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $date_to_update = $row['dates_c'];

    // Update daily_reject table with the fetched date
    $sql_update = "UPDATE template SET dates_c = '$date_to_update'";

    if ($conn->query($sql_update) === TRUE) {
        echo "Update successful";

        // Insert the updated data into another_table with the same data
        // Check if icode and amount are both not 0 before inserting
        $sql_insert = "INSERT INTO daily_reject SELECT * FROM template WHERE NOT (icode = 0 AND amount = 0)";
        
        if ($conn->query($sql_insert) === TRUE) {
            echo "Data inserted into another_table";
        } else {
            echo "Error inserting data into another_table: " . $conn->error;
        }
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    echo "Date not found in dates table.";
}

// Close the connection
$conn->close();

header("Location: daily_reject4.php");
exit();
?>
