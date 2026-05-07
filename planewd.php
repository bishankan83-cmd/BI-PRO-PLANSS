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
    header("Location: plannew5621.php");
    exit(); // Make sure to call exit after header to prevent further execution

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>
