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

    // Check if 'suppliers_code' or 'suppliers_name' is provided in the URL
    if (isset($_GET['suppliers_code'])) {
        // Query to get supplier name based on code
        $suppliers_code = $_GET['suppliers_code'];
        $stmt = $pdo->prepare("SELECT suppliers_name FROM loan_outward_suppliers WHERE suppliers_code = :suppliers_code");
        $stmt->bindParam(':suppliers_code', $suppliers_code);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($result);

    } elseif (isset($_GET['suppliers_name'])) {
        // Query to get supplier code based on name
        $suppliers_name = $_GET['suppliers_name'];
        $stmt = $pdo->prepare("SELECT suppliers_code FROM loan_outward_suppliers WHERE suppliers_name = :suppliers_name");
        $stmt->bindParam(':suppliers_name', $suppliers_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($result);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
}
?>
