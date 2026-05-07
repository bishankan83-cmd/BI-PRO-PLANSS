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

    // Check if supplier code or supplier name is provided in the GET request
    if (isset($_GET['suppliers_code'])) {
        // Fetch supplier name based on supplier code
        $suppliers_code = $_GET['suppliers_code'];
        $sql = "SELECT suppliers_name FROM loan_inward_suppliers WHERE suppliers_code = :suppliers_code";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':suppliers_code', $suppliers_code);
        $stmt->execute();
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($supplier) {
            echo json_encode(['suppliers_name' => $supplier['suppliers_name']]);
        } else {
            echo json_encode(['suppliers_name' => '']);
        }
    } elseif (isset($_GET['suppliers_name'])) {
        // Fetch supplier code based on supplier name
        $suppliers_name = $_GET['suppliers_name'];
        $sql = "SELECT suppliers_code FROM loan_inward_suppliers WHERE suppliers_name = :suppliers_name";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':suppliers_name', $suppliers_name);
        $stmt->execute();
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($supplier) {
            echo json_encode(['suppliers_code' => $supplier['suppliers_code']]);
        } else {
            echo json_encode(['suppliers_code' => '']);
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
