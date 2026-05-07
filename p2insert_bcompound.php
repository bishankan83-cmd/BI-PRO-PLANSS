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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_rows'])) {
    $selected_rows = $_POST['selected_rows'];

    foreach ($selected_rows as $iid) {
        $sql = "INSERT INTO `pbcompound_copy2` (`iid`, `id`, `inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `created_at`, `weight`, `serial_number`)
                SELECT `iid`, `id`, `inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `created_at`, `weight`, `serial_number`
                FROM `pbcompound_copy`
                WHERE `iid` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $iid);
        $stmt->execute();
        $stmt->close();
    }

    echo "Selected rows have been inserted successfully.";
} else {
    echo "No rows selected.";
}



$conn->close();
?>







