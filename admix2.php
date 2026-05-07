



<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// MySQL database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query
$sql = "
    WITH RECURSIVE BatchRange AS (
      SELECT `id`, `batch`, `batch2`
      FROM `bcompound3`
      UNION ALL
      SELECT `id`, `batch` + 1, `batch2`
      FROM BatchRange
      WHERE `batch` < `batch2`
    )
    SELECT b.`id`, b.`inputDate`, b.`shift`, b.`compound_name`, b.`description`, b.`cstock`, br.`batch`, b.`pallet`, b.`created_at`, b.`weight`, b.`serial_number`
    FROM BatchRange br
    JOIN `bcompound3` b ON br.`id` = b.`id` AND br.`batch` BETWEEN b.`batch` AND b.`batch2`
    ORDER BY b.`id`, br.`batch`;
";

// Execute query
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Construct insert statement
        $insertSql = "INSERT INTO bcompound (id, inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, serial_number) VALUES (";
        $insertSql .= "'" . $row["id"] . "', ";
        $insertSql .= "'" . $row["inputDate"] . "', ";
        $insertSql .= "'" . $row["shift"] . "', ";
        $insertSql .= "'" . $row["compound_name"] . "', ";
        $insertSql .= "'" . $row["description"] . "', ";
        $insertSql .= "'" . $row["cstock"] . "', ";
        $insertSql .= "'" . $row["batch"] . "', ";
        $insertSql .= "'" . $row["pallet"] . "', ";
        $insertSql .= "'" . $row["created_at"] . "', ";
        $insertSql .= "'" . $row["weight"] . "', ";
        $insertSql .= "'" . $row["serial_number"] . "'";
        $insertSql .= ")";

        // Execute the insert statement
        $conn->query($insertSql);
    }

    echo "Data inserted successfully.";

    // Redirect to another page
    //header("Location: dashboard.php");
    //exit(); // Ensure that no other output is sent before redirection
} else {
    echo "0 results";
}

// Close connection
$conn->close();

?>

<?php

// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL query to delete rows with identical data
    $sql = "
        DELETE b1
        FROM bcompound b1
        JOIN bcompound b2 ON 
            b1.iid > b2.iid
            AND b1.id = b2.id
            AND b1.inputDate = b2.inputDate
            AND b1.shift = b2.shift
            AND b1.compound_name = b2.compound_name
            AND b1.description = b2.description
            AND b1.cstock = b2.cstock
            AND b1.batch = b2.batch
            AND b1.pallet = b2.pallet
            AND b1.weight = b2.weight
            AND b1.serial_number = b2.serial_number
    ";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Redirect to another PHP page
    header("Location: dashboard.php");
    exit(); // Ensure that subsequent code is not executed after redirect
} catch (PDOException $e) {
    // Handle any database errors
    echo "Error: " . $e->getMessage();
}
?>
