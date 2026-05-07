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
        $sql = "INSERT INTO `pbcompound_copy` (`iid`, `id`, `inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `created_at`, `weight`, `serial_number`)
                SELECT `iid`, `id`, `inputDate`, `shift`, `compound_name`, `description`, `cstock`, `batch`, `pallet`, `created_at`, `weight`, `serial_number`
                FROM `bcompound_copy`
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




<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to delete matching records from bcompound
$sql = "
    DELETE b
    FROM bcompound_copy AS b
    JOIN pbcompound_copy AS p
    ON b.iid = p.iid
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    // Success message and redirection using JavaScript
    echo '<script type="text/javascript">
            alert("Records Insert successfully");
            window.location.href = "dashboard.php";
          </script>';
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close connection
$conn->close();
?>





