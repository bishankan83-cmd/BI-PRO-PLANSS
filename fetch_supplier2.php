<?php
// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Create a PDO instance for database connection
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['supplier_code'])) {
        // Fetch supplier name based on supplier code
        $supplier_code = $_GET['supplier_code'];
        $stmt = $pdo->prepare("SELECT supplier_name FROM loan_inward_suppliers WHERE supplier_code = :supplier_code");
        $stmt->bindParam(':supplier_code', $supplier_code);
        $stmt->execute();
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($supplier);
    }

    if (isset($_GET['supplier_name'])) {
        // Fetch supplier code based on supplier name
        $supplier_name = $_GET['supplier_name'];
        $stmt = $pdo->prepare("SELECT supplier_code FROM loan_inward_suppliers WHERE supplier_name = :supplier_name");
        $stmt->bindParam(':supplier_name', $supplier_name);
        $stmt->execute();
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($supplier);
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
