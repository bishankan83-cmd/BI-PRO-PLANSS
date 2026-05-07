<?php
/*
 * Shipping Data Entry Page - Step 2
 * Place this file in: /home/planatir/public_html/cms/admin/enter_shipping_data.php
 * Now supports viewing and editing existing shipping data
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================
class Database {
    private $host = "localhost";
    private $db_name = "planatir_cms";
    private $username = "planatir_task_managemen";
    private $password = "Bishan@1919";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// ============================================
// SHIPMENT CLASS
// ============================================
class Shipment {
    private $conn;
    private $table_name = "shipments";

    public $id;
    public $order_id;
    public $inco_term;
    public $loading_date;
    public $freight_forwarder;
    public $freight_cost;
    public $vessel_voy;
    public $bl_number;
    public $container_no;
    public $on_board_date;
    public $port_of_discharge;
    public $final_destination;
    public $eta;
    public $ddp_expected_date;
    public $insurance_cert_status;
    public $co_origin_status;
    public $copy_docs_inform_date;
    public $final_docs_inform_date;
    public $original_docs_dispatch;
    public $awb_no_date;
    public $payment_due_date;
    public $payment_status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create table if it doesn't exist
    public function createTableIfNotExists() {
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id VARCHAR(100) NOT NULL,
            inco_term VARCHAR(10) NOT NULL,
            loading_date DATE NOT NULL,
            freight_forwarder VARCHAR(255) NOT NULL,
            freight_cost DECIMAL(10,2),
            vessel_voy VARCHAR(255),
            bl_number VARCHAR(255),
            container_no VARCHAR(255),
            on_board_date DATE,
            port_of_discharge VARCHAR(255),
            final_destination VARCHAR(255),
            eta DATE,
            ddp_expected_date DATE,
            insurance_cert_status VARCHAR(50) DEFAULT 'Pending',
            co_origin_status VARCHAR(50) DEFAULT 'Pending',
            copy_docs_inform_date DATE,
            final_docs_inform_date DATE,
            original_docs_dispatch VARCHAR(255),
            awb_no_date VARCHAR(255),
            payment_due_date DATE,
            payment_status VARCHAR(50) DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES tire_orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX idx_order_id (order_id)
        )";
        
        try {
            $this->conn->exec($query);
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }

    // Get tire order details
    public function getTireOrderDetails($order_id) {
        $query = "SELECT * FROM tire_orders WHERE order_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get existing shipment data by order_id
    public function getByOrderId($order_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE order_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create shipment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET order_id=:order_id,
                    inco_term=:inco_term, 
                    loading_date=:loading_date,
                    freight_forwarder=:freight_forwarder,
                    freight_cost=:freight_cost,
                    vessel_voy=:vessel_voy,
                    bl_number=:bl_number,
                    container_no=:container_no,
                    on_board_date=:on_board_date,
                    port_of_discharge=:port_of_discharge,
                    final_destination=:final_destination,
                    eta=:eta,
                    ddp_expected_date=:ddp_expected_date,
                    insurance_cert_status=:insurance_cert_status,
                    co_origin_status=:co_origin_status,
                    copy_docs_inform_date=:copy_docs_inform_date,
                    final_docs_inform_date=:final_docs_inform_date,
                    original_docs_dispatch=:original_docs_dispatch,
                    awb_no_date=:awb_no_date,
                    payment_due_date=:payment_due_date,
                    payment_status=:payment_status";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":inco_term", $this->inco_term);
        $stmt->bindParam(":loading_date", $this->loading_date);
        $stmt->bindParam(":freight_forwarder", $this->freight_forwarder);
        $stmt->bindParam(":freight_cost", $this->freight_cost);
        $stmt->bindParam(":vessel_voy", $this->vessel_voy);
        $stmt->bindParam(":bl_number", $this->bl_number);
        $stmt->bindParam(":container_no", $this->container_no);
        $stmt->bindParam(":on_board_date", $this->on_board_date);
        $stmt->bindParam(":port_of_discharge", $this->port_of_discharge);
        $stmt->bindParam(":final_destination", $this->final_destination);
        $stmt->bindParam(":eta", $this->eta);
        $stmt->bindParam(":ddp_expected_date", $this->ddp_expected_date);
        $stmt->bindParam(":insurance_cert_status", $this->insurance_cert_status);
        $stmt->bindParam(":co_origin_status", $this->co_origin_status);
        $stmt->bindParam(":copy_docs_inform_date", $this->copy_docs_inform_date);
        $stmt->bindParam(":final_docs_inform_date", $this->final_docs_inform_date);
        $stmt->bindParam(":original_docs_dispatch", $this->original_docs_dispatch);
        $stmt->bindParam(":awb_no_date", $this->awb_no_date);
        $stmt->bindParam(":payment_due_date", $this->payment_due_date);
        $stmt->bindParam(":payment_status", $this->payment_status);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update shipment
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET inco_term=:inco_term, 
                    loading_date=:loading_date,
                    freight_forwarder=:freight_forwarder,
                    freight_cost=:freight_cost,
                    vessel_voy=:vessel_voy,
                    bl_number=:bl_number,
                    container_no=:container_no,
                    on_board_date=:on_board_date,
                    port_of_discharge=:port_of_discharge,
                    final_destination=:final_destination,
                    eta=:eta,
                    ddp_expected_date=:ddp_expected_date,
                    insurance_cert_status=:insurance_cert_status,
                    co_origin_status=:co_origin_status,
                    copy_docs_inform_date=:copy_docs_inform_date,
                    final_docs_inform_date=:final_docs_inform_date,
                    original_docs_dispatch=:original_docs_dispatch,
                    awb_no_date=:awb_no_date,
                    payment_due_date=:payment_due_date,
                    payment_status=:payment_status
                WHERE order_id=:order_id";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":inco_term", $this->inco_term);
        $stmt->bindParam(":loading_date", $this->loading_date);
        $stmt->bindParam(":freight_forwarder", $this->freight_forwarder);
        $stmt->bindParam(":freight_cost", $this->freight_cost);
        $stmt->bindParam(":vessel_voy", $this->vessel_voy);
        $stmt->bindParam(":bl_number", $this->bl_number);
        $stmt->bindParam(":container_no", $this->container_no);
        $stmt->bindParam(":on_board_date", $this->on_board_date);
        $stmt->bindParam(":port_of_discharge", $this->port_of_discharge);
        $stmt->bindParam(":final_destination", $this->final_destination);
        $stmt->bindParam(":eta", $this->eta);
        $stmt->bindParam(":ddp_expected_date", $this->ddp_expected_date);
        $stmt->bindParam(":insurance_cert_status", $this->insurance_cert_status);
        $stmt->bindParam(":co_origin_status", $this->co_origin_status);
        $stmt->bindParam(":copy_docs_inform_date", $this->copy_docs_inform_date);
        $stmt->bindParam(":final_docs_inform_date", $this->final_docs_inform_date);
        $stmt->bindParam(":original_docs_dispatch", $this->original_docs_dispatch);
        $stmt->bindParam(":awb_no_date", $this->awb_no_date);
        $stmt->bindParam(":payment_due_date", $this->payment_due_date);
        $stmt->bindParam(":payment_status", $this->payment_status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

// ============================================
// INITIALIZE
// ============================================
$database = new Database();
$db = $database->getConnection();
$shipment = new Shipment($db);
$shipment->createTableIfNotExists();

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: data_enter_mar.php");
    exit();
}

$order_id = $_GET['order_id'];
$orderDetails = $shipment->getTireOrderDetails($order_id);

if (!$orderDetails) {
    header("Location: data_enter_mar.php");
    exit();
}

// Check if shipping data already exists for this order
$existingShipment = $shipment->getByOrderId($order_id);
$isEditMode = !empty($existingShipment);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $shipment->order_id = $_POST['order_id'];
    $shipment->inco_term = $_POST['inco_term'];
    $shipment->loading_date = $_POST['loading_date'];
    $shipment->freight_forwarder = $_POST['freight_forwarder'];
    $shipment->freight_cost = !empty($_POST['freight_cost']) ? $_POST['freight_cost'] : null;
    $shipment->vessel_voy = $_POST['vessel_voy'] ?? '';
    $shipment->bl_number = $_POST['bl_number'] ?? '';
    $shipment->container_no = $_POST['container_no'] ?? '';
    $shipment->on_board_date = !empty($_POST['on_board_date']) ? $_POST['on_board_date'] : null;
    $shipment->port_of_discharge = $_POST['port_of_discharge'] ?? '';
    $shipment->final_destination = $_POST['final_destination'] ?? '';
    $shipment->eta = !empty($_POST['eta']) ? $_POST['eta'] : null;
    $shipment->ddp_expected_date = !empty($_POST['ddp_expected_date']) ? $_POST['ddp_expected_date'] : null;
    $shipment->insurance_cert_status = $_POST['insurance_cert_status'] ?? 'Pending';
    $shipment->co_origin_status = $_POST['co_origin_status'] ?? 'Pending';
    $shipment->copy_docs_inform_date = !empty($_POST['copy_docs_inform_date']) ? $_POST['copy_docs_inform_date'] : null;
    $shipment->final_docs_inform_date = !empty($_POST['final_docs_inform_date']) ? $_POST['final_docs_inform_date'] : null;
    $shipment->original_docs_dispatch = $_POST['original_docs_dispatch'] ?? '';
    $shipment->awb_no_date = $_POST['awb_no_date'] ?? '';
    $shipment->payment_due_date = !empty($_POST['payment_due_date']) ? $_POST['payment_due_date'] : null;
    $shipment->payment_status = $_POST['payment_status'] ?? 'Pending';

    if ($_POST['action'] === 'create') {
        if ($shipment->create()) {
            $message = "✓ Shipment data saved successfully!";
            $messageType = "success";
            // Reload to show edit mode
            $existingShipment = $shipment->getByOrderId($order_id);
            $isEditMode = true;
        } else {
            $message = "✗ Failed to save shipment data. Please try again.";
            $messageType = "error";
        }
    } elseif ($_POST['action'] === 'update') {
        if ($shipment->update()) {
            $message = "✓ Shipment data updated successfully!";
            $messageType = "success";
            // Reload the data
            $existingShipment = $shipment->getByOrderId($order_id);
        } else {
            $message = "✗ Failed to update shipment data. Please try again.";
            $messageType = "error";
        }
    }
}

// Helper function to get form value (existing data or empty)
function getFormValue($fieldName, $existingData, $defaultValue = '') {
    if (!empty($existingData) && isset($existingData[$fieldName])) {
        return htmlspecialchars($existingData[$fieldName]);
    }
    return $defaultValue;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditMode ? 'Edit' : 'Enter'; ?> Shipping Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --medium-gray: #343a40;
            --black: #000000;
            --red: #FF0000;
            --red-accent: #ff4757;
            --border-gray: #e0e0e0;
            --light-border: #CCCCCC;
            --bg-light: #f9f9f9;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #555555;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(45deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-active: 0 12px 40px rgba(242, 128, 24, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, rgba(242, 128, 24, 0.05) 100%);
            min-height: 100vh;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--border-gray);
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: var(--dark-gray);
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }
        
        .header h1 i {
            color: var(--primary-orange);
        }
        
        .edit-mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--warning-light);
            color: var(--warning);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            margin-left: 15px;
            border: 2px solid var(--warning);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-orange);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: var(--orange-light);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: var(--primary-orange);
            color: var(--white);
            transform: translateX(-5px);
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            padding: 30px;
            background: var(--bg-light);
            border-radius: 15px;
            border: 1px solid var(--border-gray);
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-1);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            box-shadow: var(--shadow-soft);
        }
        
        .step-number.completed {
            background: var(--gradient-2);
        }
        
        .step-text {
            font-weight: 700;
            color: var(--primary-orange);
            font-size: 1.1rem;
        }
        
        .step-arrow {
            margin: 0 30px;
            color: var(--primary-orange);
            font-size: 28px;
            font-weight: bold;
        }
        
        .message {
            padding: 18px 24px;
            margin-bottom: 25px;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-soft);
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message.success {
            background: var(--success-light);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .message.success::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 1.5rem;
        }
        
        .message.error {
            background: var(--error-light);
            color: var(--error);
            border-left: 4px solid var(--error);
        }
        
        .message.error::before {
            content: '\f06a';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 1.5rem;
        }
        
        .order-info-box {
            background: var(--gradient-1);
            color: var(--white);
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow-active);
            position: relative;
            overflow: hidden;
        }
        
        .order-info-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        
        .order-info-box h3 {
            margin-bottom: 20px;
            font-size: 1.4rem;
            font-weight: 800;
            position: relative;
            z-index: 1;
        }
        
        .order-info-box h3 i {
            margin-right: 10px;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            position: relative;
            z-index: 1;
        }
        
        .order-info-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .order-info-item:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        
        .order-info-item strong {
            display: block;
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .order-info-item span {
            font-size: 1.1rem;
            font-weight: 800;
        }
        
        .form-section {
            margin-bottom: 35px;
            padding: 30px;
            background: var(--bg-light);
            border-radius: 15px;
            border: 1px solid var(--border-gray);
        }
        
        .form-section h3 {
            color: var(--dark-gray);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary-orange);
            font-size: 1.3rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .form-section h3 i {
            color: var(--primary-orange);
            font-size: 1.5rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark-gray);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        label i {
            color: var(--text-gray);
            font-size: 0.9rem;
        }
        
        label.required::after {
            content: " *";
            color: var(--error);
            font-weight: 900;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select {
            padding: 14px 16px;
            border: 2px solid var(--border-gray);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: var(--white);
            color: var(--dark-gray);
            font-weight: 500;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px var(--orange-light);
        }
        
        input:hover, select:hover {
            border-color: var(--secondary-orange);
        }
        
        .form-actions {
            margin-top: 40px;
            display: flex;
            gap: 20px;
            justify-content: flex-end;
            padding: 30px;
            background: var(--bg-light);
            border-radius: 15px;
            border: 1px solid var(--border-gray);
        }
        
        .btn {
            padding: 16px 36px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-soft);
        }
        
        .btn i {
            font-size: 1.1rem;
        }
        
        .btn-primary {
            background: var(--gradient-2);
            color: var(--white);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--white);
            color: var(--text-gray);
            border: 2px solid var(--border-gray);
        }
        
        .btn-secondary:hover {
            background: var(--light-gray);
            border-color: var(--text-gray);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 25px;
            }
            
            .header h1 {
                font-size: 1.6rem;
            }
            
            .edit-mode-badge {
                display: block;
                margin-left: 0;
                margin-top: 10px;
            }
            
            .step-indicator {
                flex-direction: column;
                gap: 20px;
            }
            
            .step-arrow {
                transform: rotate(90deg);
                margin: 0;
            }
            
            .order-info-grid {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Loading animation for form submission */
        .btn-primary.loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="data_enter_mar.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Order Selection
        </a>
        
        <div class="header">
            <h1>
                <i class="fas fa-shipping-fast"></i> 
                <?php echo $isEditMode ? 'Edit' : 'Enter'; ?> Shipping Data
                <?php if ($isEditMode): ?>
                    <span class="edit-mode-badge">
                        <i class="fas fa-edit"></i> Editing Existing Data
                    </span>
                <?php endif; ?>
            </h1>
        </div>
        
        <div class="step-indicator">
            <div class="step">
                <div class="step-number completed"><i class="fas fa-check"></i></div>
                <span class="step-text">Select Order</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step">
                <div class="step-number">2</div>
                <span class="step-text"><?php echo $isEditMode ? 'Update' : 'Enter'; ?> Shipping Data</span>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="order-info-box">
            <h3><i class="fas fa-clipboard-list"></i> Selected Order Information</h3>
            <div class="order-info-grid">
                <div class="order-info-item">
                    <strong><i class="fas fa-hashtag"></i> Order ID</strong>
                    <span><?php echo htmlspecialchars($orderDetails['order_id']); ?></span>
                </div>
                <div class="order-info-item">
                    <strong><i class="fas fa-car"></i> Plate</strong>
                    <span><?php echo htmlspecialchars($orderDetails['plate']); ?></span>
                </div>
                <div class="order-info-item">
                    <strong><i class="fas fa-calendar-alt"></i> Order Date</strong>
                    <span><?php echo date('M d, Y', strtotime($orderDetails['order_date'])); ?></span>
                </div>
                <div class="order-info-item">
                    <strong><i class="fas fa-map-marker-alt"></i> Destination Port</strong>
                    <span><?php echo htmlspecialchars($orderDetails['destination_port']); ?></span>
                </div>
                <div class="order-info-item">
                    <strong><i class="fas fa-box"></i> Container Size</strong>
                    <span><?php echo htmlspecialchars($orderDetails['container_size']); ?></span>
                </div>
                <div class="order-info-item">
                    <strong><i class="fas fa-dollar-sign"></i> Total Payment</strong>
                    <span>$<?php echo number_format(floatval($orderDetails['total_payment'] ?? 0), 2); ?></span>
                </div>
            </div>
        </div>
        
        <form method="POST" action="" id="shippingForm">
            <input type="hidden" name="action" value="<?php echo $isEditMode ? 'update' : 'create'; ?>">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            
            <!-- Basic Shipping Information -->
            <div class="form-section">
                <h3><i class="fas fa-ship"></i> Basic Shipping Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="inco_term" class="required"><i class="fas fa-handshake"></i> Inco Term</label>
                        <select name="inco_term" id="inco_term" required>
                            <option value="">Select...</option>
                            <option value="EXW" <?php echo getFormValue('inco_term', $existingShipment) == 'EXW' ? 'selected' : ''; ?>>EXW - Ex Works</option>
                            <option value="FCA" <?php echo getFormValue('inco_term', $existingShipment) == 'FCA' ? 'selected' : ''; ?>>FCA - Free Carrier</option>
                            <option value="FOB" <?php echo getFormValue('inco_term', $existingShipment) == 'FOB' ? 'selected' : ''; ?>>FOB - Free on Board</option>
                            <option value="CFR" <?php echo getFormValue('inco_term', $existingShipment) == 'CFR' ? 'selected' : ''; ?>>CFR - Cost and Freight</option>
                            <option value="CIF" <?php echo getFormValue('inco_term', $existingShipment) == 'CIF' ? 'selected' : ''; ?>>CIF - Cost, Insurance & Freight</option>
                            <option value="DDP" <?php echo getFormValue('inco_term', $existingShipment) == 'DDP' ? 'selected' : ''; ?>>DDP - Delivered Duty Paid</option>
                            <option value="DAP" <?php echo getFormValue('inco_term', $existingShipment) == 'DAP' ? 'selected' : ''; ?>>DAP - Delivered at Place</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="loading_date" class="required"><i class="fas fa-calendar-check"></i> Loading Date</label>
                        <input type="date" name="loading_date" id="loading_date" value="<?php echo getFormValue('loading_date', $existingShipment); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="freight_forwarder" class="required"><i class="fas fa-truck"></i> Freight Forwarder</label>
                        <input type="text" name="freight_forwarder" id="freight_forwarder" value="<?php echo getFormValue('freight_forwarder', $existingShipment); ?>" placeholder="Enter freight forwarder name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="freight_cost"><i class="fas fa-dollar-sign"></i> Freight Cost (US$)</label>
                        <input type="number" step="0.01" name="freight_cost" id="freight_cost" value="<?php echo getFormValue('freight_cost', $existingShipment); ?>" placeholder="0.00">
                    </div>
                </div>
            </div>
            
            <!-- Vessel and Container Details -->
            <div class="form-section">
                <h3><i class="fas fa-anchor"></i> Vessel and Container Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="vessel_voy"><i class="fas fa-ship"></i> Vessel/Voyage</label>
                        <input type="text" name="vessel_voy" id="vessel_voy" value="<?php echo getFormValue('vessel_voy', $existingShipment); ?>" placeholder="Enter vessel/voyage number">
                    </div>
                    
                    <div class="form-group">
                        <label for="bl_number"><i class="fas fa-file-invoice"></i> B/L Number</label>
                        <input type="text" name="bl_number" id="bl_number" value="<?php echo getFormValue('bl_number', $existingShipment); ?>" placeholder="Enter bill of lading number">
                    </div>
                    
                    <div class="form-group">
                        <label for="container_no"><i class="fas fa-box"></i> Container Number</label>
                        <input type="text" name="container_no" id="container_no" value="<?php echo getFormValue('container_no', $existingShipment, htmlspecialchars($orderDetails['container_size'])); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="on_board_date"><i class="fas fa-calendar"></i> On Board Date</label>
                        <input type="date" name="on_board_date" id="on_board_date" value="<?php echo getFormValue('on_board_date', $existingShipment); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Port and Destination Information -->
            <div class="form-section">
                <h3><i class="fas fa-globe-americas"></i> Port and Destination Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="port_of_discharge"><i class="fas fa-anchor"></i> Port of Discharge</label>
                        <input type="text" name="port_of_discharge" id="port_of_discharge" 
                               value="<?php echo getFormValue('port_of_discharge', $existingShipment, htmlspecialchars($orderDetails['destination_port'])); ?>" placeholder="Enter port of discharge">
                    </div>
                    
                    <div class="form-group">
                        <label for="final_destination"><i class="fas fa-map-marked-alt"></i> Final Destination</label>
                        <input type="text" name="final_destination" id="final_destination" value="<?php echo getFormValue('final_destination', $existingShipment); ?>" placeholder="Enter final destination">
                    </div>
                    
                    <div class="form-group">
                        <label for="eta"><i class="fas fa-clock"></i> ETA (Estimated Time of Arrival)</label>
                        <input type="date" name="eta" id="eta" value="<?php echo getFormValue('eta', $existingShipment); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="ddp_expected_date"><i class="fas fa-calendar-day"></i> DDP Expected Date</label>
                        <input type="date" name="ddp_expected_date" id="ddp_expected_date" value="<?php echo getFormValue('ddp_expected_date', $existingShipment); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Documentation Status -->
            <div class="form-section">
                <h3><i class="fas fa-file-alt"></i> Documentation Status</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="insurance_cert_status"><i class="fas fa-shield-alt"></i> Insurance Certificate Status</label>
                        <select name="insurance_cert_status" id="insurance_cert_status">
                            <option value="Pending" <?php echo getFormValue('insurance_cert_status', $existingShipment, 'Pending') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Issued" <?php echo getFormValue('insurance_cert_status', $existingShipment) == 'Issued' ? 'selected' : ''; ?>>Issued</option>
                            <option value="N/A" <?php echo getFormValue('insurance_cert_status', $existingShipment) == 'N/A' ? 'selected' : ''; ?>>N/A</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="co_origin_status"><i class="fas fa-certificate"></i> Certificate of Origin Status</label>
                        <select name="co_origin_status" id="co_origin_status">
                            <option value="Pending" <?php echo getFormValue('co_origin_status', $existingShipment, 'Pending') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Issued" <?php echo getFormValue('co_origin_status', $existingShipment) == 'Issued' ? 'selected' : ''; ?>>Issued</option>
                            <option value="N/A" <?php echo getFormValue('co_origin_status', $existingShipment) == 'N/A' ? 'selected' : ''; ?>>N/A</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="copy_docs_inform_date"><i class="fas fa-copy"></i> Copy Docs Inform Date</label>
                        <input type="date" name="copy_docs_inform_date" id="copy_docs_inform_date" value="<?php echo getFormValue('copy_docs_inform_date', $existingShipment); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="final_docs_inform_date"><i class="fas fa-file-signature"></i> Final Docs Inform Date</label>
                        <input type="date" name="final_docs_inform_date" id="final_docs_inform_date" value="<?php echo getFormValue('final_docs_inform_date', $existingShipment); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="original_docs_dispatch"><i class="fas fa-shipping-fast"></i> Original Docs Dispatch</label>
                        <input type="text" name="original_docs_dispatch" id="original_docs_dispatch" value="<?php echo getFormValue('original_docs_dispatch', $existingShipment); ?>" placeholder="Enter dispatch details">
                    </div>
                    
                    <div class="form-group">
                        <label for="awb_no_date"><i class="fas fa-plane"></i> AWB Number and Date</label>
                        <input type="text" name="awb_no_date" id="awb_no_date" value="<?php echo getFormValue('awb_no_date', $existingShipment); ?>" placeholder="Enter AWB number and date">
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="form-section">
                <h3><i class="fas fa-money-check-alt"></i> Payment Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="payment_due_date"><i class="fas fa-calendar-times"></i> Payment Due Date</label>
                        <input type="date" name="payment_due_date" id="payment_due_date" value="<?php echo getFormValue('payment_due_date', $existingShipment); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_status"><i class="fas fa-credit-card"></i> Payment Status</label>
                        <select name="payment_status" id="payment_status">
                            <option value="Pending" <?php echo getFormValue('payment_status', $existingShipment, 'Pending') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Paid" <?php echo getFormValue('payment_status', $existingShipment) == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="Overdue" <?php echo getFormValue('payment_status', $existingShipment) == 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="data_enter_mar.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> <?php echo $isEditMode ? 'Update' : 'Save'; ?> Shipping Data
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Form submission loading state
        document.getElementById('shippingForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $isEditMode ? "Updating" : "Saving"; ?>...';
            submitBtn.disabled = true;
        });
        
        // Auto-fill current date for loading date if empty (only for new entries)
        window.addEventListener('DOMContentLoaded', function() {
            const loadingDateInput = document.getElementById('loading_date');
            <?php if (!$isEditMode): ?>
            if (!loadingDateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                loadingDateInput.value = today;
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>