<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a connection to the database
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputDate = $_POST["inputDate"];
    $shift = $_POST["shift"];
    $icodeArray = $_POST["icode"];
    $cstockArray = $_POST["cstock"];
    $cavityNameArray = $_POST["cavity_name"];
   
    // Iterate through the submitted data and insert it into the database
    for ($i = 0; $i < count($icodeArray); $i++) {
        $icode = $conn->real_escape_string($icodeArray[$i]);
        $cstock = $conn->real_escape_string($cstockArray[$i]);
        $cavityName = $conn->real_escape_string($cavityNameArray[$i]);
        

        // Prepare and execute the SQL query to insert data
        $sql = "INSERT INTO daily_plan_data1 (Icode, Plan, date, shift, CavityName, AdditionalData) VALUES ('$icode', '$cstock', '$inputDate', '$shift', '$cavityName','$cstock')";

        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Close the database connection
    $conn->close();

    // Redirect to a new page after processing the form
    header("Location:confirm_daily.php");
    exit();
}
?>
