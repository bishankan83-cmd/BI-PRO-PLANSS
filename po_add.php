<?php
// === DATABASE CONFIG AND CONNECTION CLASS ===
class DatabaseConnection {
    private $host = 'localhost';
    private $username = 'planatir_task_managemen';
    private $password = 'Bishan@1919';
    private $database = 'planatir_task_managemen';
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    // Fetch Suppliers
    public function getSuppliers() {
        $stmt = $this->pdo->query("SELECT suppliers_code, suppliers_name FROM po_suppliers");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch RM Codes
    public function getRMCodes() {
        $stmt = $this->pdo->query("SELECT RM_code FROM steel_band_stock");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // New method to fetch existing PO Numbers
public function getPONumbers() {
    $stmt = $this->pdo->query("SELECT PO AS po_number FROM purchase_orderss ORDER BY PO");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    // Insert Purchase Order
    public function insertPurchaseOrder($data) {
        $sql = "INSERT INTO purchase_orders (
            po_number, po_date, expected_deliver_inhouse_date, 
            supplier_code, supplier_name, rm_code, 
            descriptions, number_of_bands
        ) VALUES (
            :po_number, :po_date, :expected_deliver_inhouse_date, 
            :supplier_code, :supplier_name, :rm_code, 
            :descriptions, :number_of_bands
        )";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // Fetch All Purchase Orders
    public function getPurchaseOrders() {
        $stmt = $this->pdo->query("SELECT * FROM purchase_orders ORDER BY po_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// === PURCHASE ORDER FORM (purchase_order_form.php) ===
session_start();

try {
    $db = new DatabaseConnection();
    $suppliers = $db->getSuppliers();
    $rmCodes = $db->getRMCodes();
    $poNumbers = $db->getPONumbers(); // Fetch existing PO Numbers

    $message = '';
    $messageType = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $formData = [
            ':po_number' => $_POST['po_number'],
            ':po_date' => $_POST['po_date'],
            ':expected_deliver_inhouse_date' => $_POST['expected_deliver_inhouse_date'],
            ':supplier_code' => $_POST['supplier_code'],
            ':supplier_name' => $_POST['supplier_name'],
            ':rm_code' => $_POST['rm_code'],
            ':descriptions' => $_POST['descriptions'],
            ':number_of_bands' => $_POST['number_of_bands']
        ];

        if ($db->insertPurchaseOrder($formData)) {
            $_SESSION['message'] = "Purchase Order created successfully!";
            $_SESSION['message_type'] = "success";
            
            header("Location: view_purchase_orders.php");
            exit();
        } else {
            $message = "Failed to create Purchase Order.";
            $messageType = "error";
        }
    }
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS remains the same as in previous implementation */
        :root {
            --primary-color: #F28018;
            --secondary-color: #2C3E50;
            --background-color: #F5F6FA;
            --card-background: #FFFFFF;
            --text-primary: #2C3E50;
            --text-secondary: #7F8C8D;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--background-color);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section {
            flex: 1;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, #FF5F1F 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .form-section p {
            color: rgba(255,255,255,0.8);
            margin-bottom: 2rem;
        }

        .input-section {
            flex: 1.5;
            padding: 3rem 2.5rem;
            background: white;
            position: relative;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-weight: 500;
            transition: var(--transition);
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #E0E0E0;
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(242, 128, 24, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, #FF5F1F 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(242, 128, 24, 0.4);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(242, 128, 24, 0.5);
        }

        .alert {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 1rem;
            text-align: center;
            font-weight: bold;
            z-index: 10;
        }

        .alert-success {
            background-color: #D4EDDA;
            color: #155724;
        }

        .alert-error {
            background-color: #F8D7DA;
            color: #721C24;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .form-section, .input-section {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2>Purchase Order</h2>
            <p>Create and manage your purchase orders with ease. Fill out the form to submit a new purchase order.</p>
            <i class="fas fa-shopping-cart" style="font-size: 4rem; opacity: 0.7;"></i>
        </div>
        
        <div class="input-section">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="purchaseOrderForm">
                <div class="form-group">
                    <label for="po_number">PO Number</label>
                    <select id="po_number" name="po_number" required>
                        <option value="">Select PO Number or Enter New</option>
                        <?php foreach ($poNumbers as $poNumber): ?>
                            <option value="<?php echo htmlspecialchars($poNumber['po_number']); ?>">
                                <?php echo htmlspecialchars($poNumber['po_number']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="po_date">PO Date</label>
                    <input type="date" id="po_date" name="po_date" required>
                </div>

                <div class="form-group">
                    <label for="expected_deliver_inhouse_date">Expected Delivery Date</label>
                    <input type="date" id="expected_deliver_inhouse_date" name="expected_deliver_inhouse_date" required>
                </div>

                <div class="form-group">
                    <label for="supplier_code">Supplier Code</label>
                    <select id="supplier_code" name="supplier_code" required onchange="updateSupplierName()">
                        <option value="">Select Supplier Code</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo htmlspecialchars($supplier['suppliers_code']); ?>">
                                <?php echo htmlspecialchars($supplier['suppliers_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="supplier_name">Supplier Name</label>
                    <select id="supplier_name" name="supplier_name" required onchange="updateSupplierCode()">
                        <option value="">Select Supplier Name</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo htmlspecialchars($supplier['suppliers_name']); ?>">
                                <?php echo htmlspecialchars($supplier['suppliers_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="rm_code">RM Code</label>
                    <select id="rm_code" name="rm_code" required>
                        <option value="">Select RM Code</option>
                        <?php foreach ($rmCodes as $rmCode): ?>
                            <option value="<?php echo htmlspecialchars($rmCode['RM_code']); ?>">
                                <?php echo htmlspecialchars($rmCode['RM_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descriptions">Descriptions</label>
                    <textarea id="descriptions" name="descriptions" rows="4" required placeholder="Enter purchase order details"></textarea>
                </div>

                <div class="form-group">
                    <label for="number_of_bands">Number of Bands</label>
                    <input type="number" id="number_of_bands" name="number_of_bands" required placeholder="Enter number of bands">
                </div>

                <button type="submit" class="submit-btn">
                    Submit Purchase Order
                </button>
            </form>
        </div>
    </div>

    <script>
        // Supplier Code and Name Synchronization
        const suppliers = [
            <?php foreach ($suppliers as $supplier): ?>
            {
                code: "<?php echo htmlspecialchars($supplier['suppliers_code']); ?>",
                name: "<?php echo htmlspecialchars($supplier['suppliers_name']); ?>"
            },
            <?php endforeach; ?>
        ];

        function updateSupplierName() {
            const supplierCode = document.getElementById('supplier_code').value;
            const supplierNameSelect = document.getElementById('supplier_name');

            const matchedSupplier = suppliers.find(supplier => supplier.code === supplierCode);
            if (matchedSupplier) {
                supplierNameSelect.value = matchedSupplier.name;
            }
        }

        function updateSupplierCode() {
            const supplierName = document.getElementById('supplier_name').value;
            const supplierCodeSelect = document.getElementById('supplier_code');

            const matchedSupplier = suppliers.find(supplier => supplier.name === supplierName);
            if (matchedSupplier) {
                supplierCodeSelect.value = matchedSupplier.code;
            }
        }

        // PO Number Custom Entry
        const poNumberSelect = document.getElementById('po_number');
        poNumberSelect.addEventListener('change', function() {
            if (this.value === '') {
                const customPONumber = prompt('Enter a new PO Number:');
                if (customPONumber) {
                    // Create a new option
                    const newOption = document.createElement('option');
                    newOption.value = customPONumber;
                    newOption.text = customPONumber;
                    newOption.selected = true;
                    
                    // Add the new option to the select
                    this.add(newOption);
                }
            }
        });

// Form Validation
document.getElementById('purchaseOrderForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '#E0E0E0';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill out all required fields.');
            }
        });
    </script>
</body>
</html>

<?php
session_start();

try {
    $db = new DatabaseConnection();
    $purchaseOrders = $db->getPurchaseOrders();
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

       