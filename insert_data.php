<?php
$selectedIcode = $_POST['icode'];
$baseBatchNo = $_POST['baseBatchNo'];
$cussionBatchNo = $_POST['cussionBatchNo'];
$treadBatchNo = $_POST['treadBatchNo'];

// Establish a database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current day
$currentDay = date("d");

if ($currentDay === "01") {
    // Reset the last 4 digits to "0001" on the 1st day of every month
    $nextSerial = date("md") . "0001";
} else {
    // Find the maximum serial number for the current month
    $sql = "SELECT MAX(SUBSTRING(serial_number, 5)) AS max_serial FROM bom_new3 WHERE serial_number LIKE '" . date("m") . "%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $maxSerial = $row["max_serial"];

    if ($maxSerial === null) {
        $nextSerial = date("md") . "0001";
    } else {
        // Increment the last 4 digits based on the existing serial numbers in the database for the current month
        $nextSerial = date("md") . str_pad((intval($maxSerial) + 1), 4, '0', STR_PAD_LEFT);
    }
}


// Insert the data into the database with the generated serial number
$insertSql = "INSERT INTO bom_new3 (Item, icode, serial_number, base_batch_no, cussion_batch_no, tread_batch_no) VALUES ('$selectedIcode', '$selectedIcode', '$nextSerial', '$baseBatchNo', '$cussionBatchNo', '$treadBatchNo')";
if ($conn->query($insertSql) === TRUE) {
    echo "Data inserted successfully. Serial Number: $nextSerial";
} else {
    echo "Error: " . $insertSql . "<br>" . $conn->error;
}

$conn->close();
?>