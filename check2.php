

<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Establish a database connection
$connection = mysqli_connect($host, $username, $password, $database);

// Check for a successful connection
if (mysqli_connect_errno()) {
    die('Failed to connect to the database: ' . mysqli_connect_error());
}

// Copy data query (assuming $copyDataQuery is defined somewhere)
// Uncomment this line if $copyDataQuery is set.
// mysqli_query($connection, $copyDataQuery);

// Array of delete queries
$deleteQueries = [
    "DELETE FROM production_plan",
    "DELETE FROM tire_cavity",
    "DELETE FROM tire_molddd",
    "DELETE FROM quick_plan",
    "DELETE FROM process_plan",
    "DELETE FROM tobeplan_plan",
    "DELETE FROM tobeplan_tem",
];

// Execute each delete query
foreach ($deleteQueries as $query) {
    if (mysqli_query($connection, $query)) {
        echo "Successfully executed: $query\n";
    } else {
        echo "Error executing query: $query - " . mysqli_error($connection) . "\n";
    }
}

// Close the database connection
mysqli_close($connection);
?>







<?php
// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare SQL statement
    $sql = "INSERT INTO worder (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs)
            SELECT date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs
            FROM worder56";

    // Execute SQL statement
    $pdo->exec($sql);

    // Redirect to another page
    header("Location: import2.php");
    exit(); // Make sure no further code is executed after redirection
} catch (PDOException $e) {
    // Handle database connection errors
    echo "Error: " . $e->getMessage();
}
?>
