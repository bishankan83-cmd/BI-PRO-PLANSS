<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['insertData'])) {
    // Database connection parameters
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the form data from POST parameters
    $date = $_POST['date'];
    $shift = $_POST['shift'];
    $icode = $_POST['icode'];
    $moldName = $_POST['moldName'];
    $cavityName = $_POST['cavityName'];
    $plan = $_POST['plan'];
    $additionalData = $_POST['additionalData'];
    $lossReason = $_POST['lossReason'];
    $remark = $_POST['remark'];

    // SQL statement to insert data into the daily_plan_data1 table
    $sql = "INSERT INTO daily_plan_data1 (Date, Shift, Icode, MoldName, CavityName, Plan, AdditionalData, LossReason, Remark) 
            VALUES ('$date', '$shift', '$icode', '$moldName', '$cavityName', '$plan', '$additionalData', '$lossReason', '$remark')";

    if ($conn->query($sql) === TRUE) {
        echo "New record inserted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
} else {
    echo "Error: No data to insert.";
}
?>
