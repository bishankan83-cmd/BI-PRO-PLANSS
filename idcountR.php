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

// SQL query to retrieve total id count for each id
$sql = "SELECT `id`, SUM(`id_count`) AS `total_id_count` FROM `new_table2` GROUP BY `id`";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];
        $total_id_count = $row["total_id_count"];

        // Query to fetch additional details from icode_table based on id
        $icode_query = "SELECT `icode` FROM `new_table` WHERE `plan_id` = $id";
        $icode_result = $conn->query($icode_query);

        if ($icode_result->num_rows > 0) {
            $icode_row = $icode_result->fetch_assoc();
            $icode = $icode_row["icode"];
        } else {
            $icode = "No ICode found";
        }

        // Insert data into the result_table
        $insert_query = "INSERT INTO `result_table` (`id`, `total_id_count`, `icode`) VALUES ('$id', '$total_id_count', '$icode')";
        $insert_result = $conn->query($insert_query);

        if (!$insert_result) {
            echo "Error inserting data: " . $conn->error;
        }
    }
} else {
    echo "No results found.";
}

// Close the connection
$conn->close();
header("Location: idcount2R.php");
exit();
?>
