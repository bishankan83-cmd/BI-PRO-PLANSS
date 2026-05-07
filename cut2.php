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
        JOIN (
            SELECT t.icode, t.erp, CEIL(t.tobe / COUNT(*)) AS tires_per_mold
            FROM tobeplan t
            JOIN process p2 ON p2.icode = t.icode AND p2.erp = t.erp
            GROUP BY t.icode, t.erp
        ) AS temp ON p.icode = temp.icode AND p.erp = temp.erp
        SET p.tires_per_mold = temp.tires_per_mold
    ");

    // Execute the statement
    $stmt->execute();
    
    header("Location: plannew56new2.php");
    exit();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>