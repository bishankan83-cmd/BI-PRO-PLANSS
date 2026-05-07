<?php

// Display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_rows'])) {
    $selected_rows = $_POST['selected_rows'];

    foreach ($selected_rows as $iid) {
        // Insert into bcompound_copy
        $sql1 = "INSERT INTO `bcompound_copy` (`inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `weight`, `serial_number`)
                 SELECT `inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `weight`, `serial_number`
                 FROM `bcompound`
                 WHERE `iid` = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $iid);
        $stmt1->execute();
        $stmt1->close();

        // Insert into bcompound_copy2
        $sql2 = "INSERT INTO `bcompound_copy2` (`inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `weight`, `serial_number`)
                 SELECT `inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `weight`, `serial_number`
                 FROM `bcompound`
                 WHERE `iid` = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $iid);
        $stmt2->execute();
        $stmt2->close();
    }

    // Display success message and redirect after "OK" click
    echo '<script>
            alert("Selected rows have been inserted into both tables successfully.");
            window.location.href = "get_email3.php";
          </script>';
} else {
    echo "No rows selected.";
}

$conn->close();
?>
