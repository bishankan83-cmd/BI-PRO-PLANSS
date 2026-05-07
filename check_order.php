<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worder Check</title>
</head>
<body>
    <h1>Worder Check Sections</h1>

    <?php
    // Database connection settings
    $hostname = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    // Mapping of table names to page names
    $pages = [
        'tire_mold' => 'mold_list.php?filter_mold_id=M606&filter_icode=&filter_press_id=&filter_mold_name=&filter_mold_size=',
        'tire' => 'edit_data/Tire',
        'tire_details' => 'edit_data/TireDetails',
        'realstock' => 'edit_data/Realstock',
        'stock' => 'edit_data/Realstock'  // Add this entry if you have a page for the 'stock' table
    ];

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

        // Initialize a variable to track if any data was found
        $dataFound = false;

        // Iterate through each table and execute the query
        foreach ($tables as $table => $header) {
            // SQL query
            $sql = "SELECT w.icode AS 'icode_no'
                    FROM worder56 w
                    LEFT JOIN $table t ON w.icode = t.icode
                    WHERE t.icode IS NULL";

            // Prepare and execute the query
            $stmt = $db->prepare($sql);
            $stmt->execute();

            // Fetch all rows
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if there are results
            if (!empty($results)) {
                $dataFound = true; // Data found, set flag to true

                // Output header
                echo "<h2>$header</h2>";

                // Output results
                echo '<ul>';
                foreach ($results as $row) {
                    echo '<li>' . $row['icode_no'] . '</li>';
                }
                echo '</ul>';

                // Add a button for each section
                $page = isset($pages[$table]) ? $pages[$table] : 'default.php';
                echo '<form action="' . htmlspecialchars($page) . '" method="post">';
                echo '<input type="hidden" name="table" value="' . htmlspecialchars($table) . '">';
                echo '<button type="submit">Go to ' . htmlspecialchars($header) . ' details</button>';
                echo '</form>';
            }
        }

        // If no data was found for any table, redirect to another page
        if (!$dataFound) {
            echo "<script>window.location.href = 'check2.php';</script>";
           exit(); // Ensure no further code is executed
        }
    
    } catch (PDOException $e) {
        // Handle database connection errors
        echo "Connection failed: " . $e->getMessage();
    }
    ?>

</body>
</html>
