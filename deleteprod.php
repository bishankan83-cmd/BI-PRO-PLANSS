<?php
// Establish a connection to the MySQL database
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Delete all data from the production_plan table
    $deleteProductionPlan = "DELETE FROM production_plan";
    $conn->exec($deleteProductionPlan);

    // Delete all data from the selected_data table
    $deleteSelectedData = "DELETE FROM selected_data";
    $conn->exec($deleteSelectedData);

    // Redirect to another page
    header("Location: planning.php");
    exit;

    echo "Data deleted successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$conn = null;
?>
