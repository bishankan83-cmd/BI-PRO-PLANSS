<?php
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete all rows from press_selections
    $sqlPressSelections = "DELETE FROM press_selections";
    $stmtPressSelections = $pdo->prepare($sqlPressSelections);
    $stmtPressSelections->execute();
    $deletedPressRows = $stmtPressSelections->rowCount();

    // Delete rows where cavity_id is NULL or 0 from process table
    $sqlProcess = "DELETE FROM process WHERE cavity_id IS NULL OR cavity_id = 0";
    $stmtProcess = $pdo->prepare($sqlProcess);
    $stmtProcess->execute();
    $deletedProcessRows = $stmtProcess->rowCount();

    // Redirect to another page with the number of deleted rows from both tables
    header("Location: plannew562new.php?deleted_process=$deletedProcessRows&deleted_press=$deletedPressRows");
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$pdo = null;
?>




