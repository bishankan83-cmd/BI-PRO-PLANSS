
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputDate = $_POST["inputDate"];
    $shift = $_POST["shift"];
    $description = $_POST["description"]; // Updated to include description
    $serialNumberArray = $_POST["serialNumber"]; // Added serialNumberArray
    $icodeArray = $_POST["icode"];
    $cstockArray = $_POST["cstock"];
    $batchArray = $_POST["batch"];
    $batch2Array = $_POST["batch2"]; // Added batch2Array 
    $palletArray = $_POST["pallet"];
    $weightArray = $_POST["weight"];


    // Prepare the INSERT statement for bcompound2 table
    $stmt1 = $conn->prepare("INSERT INTO bcompound2 (inputDate, shift, compound_name, description, cstock, batch, batch2, pallet, weight, serial_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters for bcompound2 table
    $stmt1->bind_param("ssssssssss", $inputDate, $shift, $icode, $description, $cstock, $batch, $batch2, $pallet, $weight, $serialNumber);

    // Prepare the INSERT statement for bcompound3 table
    $stmt2 = $conn->prepare("INSERT INTO bcompound3 (inputDate, shift, compound_name, description, cstock, batch, batch2, pallet, weight, serial_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters for bcompound3 table
    $stmt2->bind_param("ssssssssss", $inputDate, $shift, $icode, $description, $cstock, $batch, $batch2, $pallet, $weight, $serialNumber);

    // Execute the statements for each set of data
    for ($i = 0; $i < count($icodeArray); $i++) {
        $icode = !empty($icodeArray[$i]) ? $icodeArray[$i] : null;
        $cstock = !empty($cstockArray[$i]) ? $cstockArray[$i] : null;
        $batch = !empty($batchArray[$i]) ? $batchArray[$i] : null;
        $batch2 = !empty($batch2Array[$i]) ? $batch2Array[$i] : null;
        $pallet = !empty($palletArray[$i]) ? $palletArray[$i] : null;
        $weight = !empty($weightArray[$i]) ? $weightArray[$i] : null;
        $serialNumber = !empty($serialNumberArray[$i]) ? $serialNumberArray[$i] : null;

        // Execute the prepared statement for bcompound2 table
        if (!$stmt1->execute()) {
            echo "Error: " . $stmt1->error;
            break;
        }

        // Execute the prepared statement for bcompound3 table
        if (!$stmt2->execute()) {
            echo "Error: " . $stmt2->error;
            break;
        }
    }

    // Close the statements
    $stmt1->close();
    $stmt2->close();
    $conn->close();

    // echo "Records inserted successfully.";
    // exit();
}

// Redirect if not a POST request
header("Location: admix2.php");
exit();
?>
