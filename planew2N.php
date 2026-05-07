

<?php

// Establish a database connection
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Example: Delete all data from the 'plannew1' table
    $deleteQuery = "DELETE FROM new_process";
    $pdo->exec($deleteQuery);

    echo "Table 'plannew1' deleted successfully.";

    

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>





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

// SQL query to insert data from `process` to `new_process` where `first_tobe` is 1
$sql = "
    INSERT INTO `new_process` (
        `icode`, `mold_id`, `tires_per_mold`, `cavity_id`, `mold_name`, `cavity_name`, `press_name`, `press_id`, `erp`, `serial`, `is_completed`, `is_highlighted`, `first_tobe`, `start_date`
    )
    SELECT 
        `icode`, `mold_id`, `tires_per_mold`, `cavity_id`, `mold_name`, `cavity_name`, `press_name`, `press_id`, `erp`, `serial`, `is_completed`, `is_highlighted`, `first_tobe`, `start_date`
    FROM 
        `process`
    WHERE 
        `first_tobe` = 1
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records inserted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>






<?php

// Establish a database connection
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Example: Delete all data from the 'plannew1' table
    $deleteQuery = "DELETE FROM plannew1";
    $pdo->exec($deleteQuery);

    echo "Table 'plannew1' deleted successfully.";

    // Redirect to another page
    header("Location: plannew56212N.php");
    exit(); // Make sure to call exit after header to prevent further execution

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>
