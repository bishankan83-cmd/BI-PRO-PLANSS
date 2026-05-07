<?php
// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to retrieve mold_id, press_id, and id from mold_press_name and insert into mold_press
    $sql = "INSERT INTO mold_press (id, mold_id, press_id)
            SELECT mp.id, m.mold_id, p.press_id
            FROM mold_press_name mp
            INNER JOIN mold m ON mp.mold_name = m.mold_name
            INNER JOIN press p ON mp.press_name = p.press_name
            ORDER BY mp.id"; // Order by the id column of mold_press_name

    // Execute the INSERT query
    $pdo->exec($sql);

    echo "Data inserted successfully into mold_press.";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
