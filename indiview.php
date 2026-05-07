<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve id, icode, id_count, start_date, end_date, cavity_id, mold_id, and erp from both tables
$sqlRetrieveData = "SELECT n.id, n.icode, n.id_count, n.start_date, n.end_date, p.cavity_id, p.mold_id, p.erp
                   FROM new_table2 n
                   JOIN plannew p ON n.id = p.plan_id";

$result = $conn->query($sqlRetrieveData);

if ($result->num_rows > 0) {
    // Insert data into the merged_data table
    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];
        $icode = $row["icode"];
        $id_count = $row["id_count"];
        $start_date = $row["start_date"];
        $start_time = $row["start_date"];
        $end_date = $row["end_date"];
        $cavity_id = $row["cavity_id"];
        $mold_id = $row["mold_id"];
        $erp = $row["erp"];

        $sqlInsertData = "INSERT INTO merged_data (id, icode, id_count, start_date, start_time ,end_date, cavity_id, mold_id, erp)
                         VALUES ('$id', '$icode', '$id_count', '$start_date', '$start_time','$end_date', '$cavity_id', '$mold_id', '$erp')";

        if ($conn->query($sqlInsertData) === TRUE) {
            //echo "Data inserted successfully.<br>";
        } else {
           // echo "Error inserting data: " . $conn->error . "<br>";
        }
    }
} else {
    echo "No matching records found";
}

// Close the connection
$conn->close();
header("Location: match.php");
exit();

?>
