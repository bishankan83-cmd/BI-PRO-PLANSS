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

    // Check if the supplier_code or supplier_name is passed via GET
    if (isset($_GET['supplier_code'])) {
        $supplier_code = $_GET['supplier_code'];
        $sql = "SELECT suppliers_name FROM po_suppliers WHERE suppliers_code = :supplier_code";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':supplier_code', $supplier_code);
        $stmt->execute();
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($supplier);  // Send supplier name as JSON response
    } elseif (isset($_GET['supplier_name'])) {
        $supplier_name = $_GET['supplier_name'];
        $sql = "SELECT suppliers_code FROM po_suppliers WHERE suppliers_name = :supplier_name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':supplier_name', $supplier_name);
        $stmt->execute();
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($supplier);  // Send supplier code as JSON response
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
