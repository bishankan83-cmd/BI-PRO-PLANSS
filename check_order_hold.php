<?php
// Database connection settings
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Connect to the database
    $dsn = "mysql:host=$hostname;dbname=$database";
    $db = new PDO($dsn, $username, $password);

    // Set error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Array of table names and headers
    $tables = [
        'tire_mold' => "Item Codes without Mold (tire_mold):",
        'tire' => "Item Codes without tire details (tire):",
        'tire_details' => "Item Codes without tire details (tire_details):",
        'realstock' => "Item Codes without Stock (realstock):",
        'stock' => "Item Codes without on hand stock:"
    ];

    // Set header for the output
    header('Content-Type: text/plain');

    // Flag to track if any data is found
    $dataFound = false;

    // Iterate through each table and execute the query
    foreach ($tables as $table => $header) {
        // SQL query
        $sql = "SELECT w.icode AS 'icode_no_mold'
                FROM worder72 w
                LEFT JOIN $table tm ON w.icode = tm.icode
                WHERE tm.icode IS NULL";

        // Prepare and execute the query
        $stmt = $db->prepare($sql);
        $stmt->execute();

        // Fetch all rows
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if there are results
        if (!empty($results)) {
            $dataFound = true;

            // Output header
            echo "$header\n\n";

            // Output results
            foreach ($results as $row) {
                echo $row['icode_no_mold'] . "\n";
            }

            // Print a newline for better readability between sections
            echo "\n";
        }
    }

    // Redirect to check2 page if no data was found
    if (!$dataFound) {
      header('Location: check2_hold.php');
       exit();
    }

} catch (PDOException $e) {
    // Handle database connection errors
    echo "Connection failed: " . $e->getMessage();
}
?>
