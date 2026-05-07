<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Your SQL query with DATE() function
    $sql = "
        SELECT p.*
        FROM plannew p
        JOIN holidays h ON (DATE(p.start_date) BETWEEN DATE(h.holiday_date) AND DATE(h.holiday_date) OR
                           DATE(p.end_date) BETWEEN DATE(h.holiday_date) AND DATE(h.holiday_date))
    ";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if results are found
    if ($results) {
        // Display the results
        foreach ($results as $result) {
            // Redirect to check_date4.php
            header("Location: check_date4.php");
            exit();
        }
    } else {
        // No results found, redirect to test12345.php
        header("Location: test12345.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} finally {
    // Close the connection
    $pdo = null;
}
?>
