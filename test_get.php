<?php
// Database connection settings
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL query to insert data
    $sql = "INSERT INTO tobeplan_plan (`erp`, `icode`, `tobe`, `stockonhand`)
            SELECT tp.`erp`, tp.`icode`, tp.`tobe`, tp.`stockonhand`
            FROM tobeplan1 tp
            LEFT JOIN new_plan_data pn ON tp.`erp` = pn.`erp` AND tp.`icode` = pn.`icode`
            WHERE pn.`id` IS NULL
            AND tp.`tobe` > 0";
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Check if any rows were affected (inserted)
    $rowCount = $stmt->rowCount();
    
    if ($rowCount > 0) {
        // Data was inserted successfully
        echo "Data inserted successfully into the tobeplan_plan table.";
        //header("Location: get2.php");
        //exit();
    } else {
        // No data was inserted
       // header("Location: dashboard.php");
        //exit();
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    // In case of error, redirect to dashboard
 //   header("Location: dashboard.php");
    //exit();
}

// Close the database connection
$conn = null;
?>