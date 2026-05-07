<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 1: Check if there is data in process_plan
    $checkSql = "SELECT COUNT(*) FROM process_plan";
    $stmt = $pdo->query($checkSql);
    $rowCount = $stmt->fetchColumn();

    // Step 2: If there are records in process_plan, delete existing data in process_plan_tem and insert new data
    if ($rowCount > 0) {
        // Delete existing data in process_plan_tem
        $deleteSql = "DELETE FROM process_plan_tem";
        $pdo->exec($deleteSql);
        
        // Insert data from process_plan to process_plan_tem
        $insertSql = "
            INSERT INTO process_plan_tem (id, icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date)
            SELECT id, icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date
            FROM process_plan
        ";

        // Execute the insert query
        $pdo->exec($insertSql);
        echo "Data successfully transferred from process_plan to process_plan_tem after clearing the destination table.";
    } else {
        echo "No data found in process_plan. No action taken.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>



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
        FROM plannew1 p
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
            header("Location: check_date34.php");
              exit();
        }
    } else {
        // No results found, redirect to another PHP page
        header("Location: plannew45.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Close the connection
$pdo = null;
?>
