<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Establish a connection to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the SQL statement
    $stmt = $conn->prepare("
        UPDATE process p
        JOIN tobeplan t ON p.icode = t.icode AND p.erp = t.erp
        SET p.tires_per_mold = CEIL(t.tobe / (SELECT COUNT(*) FROM process p2 WHERE p2.icode = t.icode AND p2.erp = t.erp))
        WHERE EXISTS (SELECT 1 FROM process WHERE icode = t.icode AND erp = t.erp)
    ");

    // Execute the statement
    $stmt->execute();
    
    header("Location: plannew56new3.php");
    exit();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>
