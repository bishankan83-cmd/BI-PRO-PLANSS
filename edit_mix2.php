<?php
// Database connection settings
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to delete all data from bcompound89
    $sql = "DELETE FROM bcompound89";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Check if rows were deleted
    $rowsDeleted = $stmt->rowCount();
    echo "Deleted $rowsDeleted rows from bcompound89.";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>


<?php
// Database connection settings
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to insert data from bcompound2 into bcompound89
    $sql = "INSERT INTO bcompound89 (id, inputDate, shift, compound_name, description, cstock, batch, batch2, pallet, created_at, weight, serial_number)
            SELECT id, inputDate, shift, compound_name, description, cstock, batch, batch2, pallet, created_at, weight, serial_number
            FROM bcompound2";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Check if rows were inserted
    $rowsInserted = $stmt->rowCount();
    echo "Inserted $rowsInserted rows from bcompound2 into bcompound89.";

    // Redirect to another page
    header("Location: edit_mix.php");
    exit(); // Ensure that no other code is executed after redirection

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>






