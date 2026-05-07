


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Button</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard-button {
            background-color: #000000;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .dashboard-button i {
            margin-right: 10px;
        }

        .dashboard-button:hover {
            background-color: #333333;
            transform: scale(1.05);
        }

        .dashboard-button:active {
            background-color: #666666;
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <button class="dashboard-button" onclick="goToDashboard()">
        <i class="fas fa-home"></i>
        Back to Dashboard
    </button>

    <script>
        function goToDashboard() {
            // Redirect to dashboard.php
            window.location.href = 'dashboard.php';
        }
    </script>
</body>
</html>






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

    // SQL query to get suppliers data
    $sql = "SELECT suppliers_code, suppliers_name FROM loan_outward_suppliers";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // SQL query to get RM_code values from steel_band_stock table
    $sql_rm = "SELECT RM_code FROM steel_band_stock";
    $stmt_rm = $pdo->prepare($sql_rm);
    $stmt_rm->execute();
    $rm_codes = $stmt_rm->fetchAll(PDO::FETCH_ASSOC);

    $message = '';
    $messageType = '';

    // Insert data if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form data
        $loan_date = htmlspecialchars($_POST['loan_date']);
        $expected_delivery_date = htmlspecialchars($_POST['expected_delivery_date']);
        $suppliers_code = htmlspecialchars($_POST['suppliers_code']);
        $suppliers_name = htmlspecialchars($_POST['suppliers_name']);
        $rm_code = htmlspecialchars($_POST['rm_code']);
        $descriptions = htmlspecialchars($_POST['descriptions']);
        $number_of_bands = (int)$_POST['number_of_bands'];

        // Prepare SQL for inserting data
        $sql_insert = "INSERT INTO loan_outward_details (loan_date, expected_delivery_date, suppliers_code, suppliers_name, rm_code, descriptions, number_of_bands) 
                       VALUES (:loan_date, :expected_delivery_date, :suppliers_code, :suppliers_name, :rm_code, :descriptions, :number_of_bands)";
        $stmt_insert = $pdo->prepare($sql_insert);

        // Bind parameters to the query
        $stmt_insert->bindParam(':loan_date', $loan_date);
        $stmt_insert->bindParam(':expected_delivery_date', $expected_delivery_date);
        $stmt_insert->bindParam(':suppliers_code', $suppliers_code);
        $stmt_insert->bindParam(':suppliers_name', $suppliers_name);
        $stmt_insert->bindParam(':rm_code', $rm_code);
        $stmt_insert->bindParam(':descriptions', $descriptions);
        $stmt_insert->bindParam(':number_of_bands', $number_of_bands);

        // Execute the query
        if ($stmt_insert->execute()) {
            $message = "Loan outward record created successfully!";
            $messageType = "success";

            header("Location: loan_outward_display.php");
            exit();
        } else {
            $message = "Error inserting loan outward record.";
            $messageType = "error";
        }
    }
} catch (PDOException $e) {
    $message = "Connection failed: " . $e->getMessage();
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan outward Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
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
            <h2>Loan outward</h2>
            <p>Create and manage your loan outward records with ease. Fill out the form to submit a new loan outward entry.</p>
            <i class="fas fa-file-invoice-dollar" style="font-size: 4rem; opacity: 0.7;"></i>
        </div>
        
        <div class="input-section">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loanoutwardForm">
                <div class="form-group">
                    <label for="loan_date">Loan Date</label>
                    <input type="date" id="loan_date" name="loan_date" required>
                </div>

                <div class="form-group">
                    <label for="expected_delivery_date">Expected Delivery Date</label>
                    <input type="date" id="expected_delivery_date" name="expected_delivery_date" required>
                </div>

                <div class="form-group">
                    <label for="suppliers_code">Supplier Code</label>
                    <select id="suppliers_code" name="suppliers_code" required onchange="updateSupplierName()">
                        <option value="">Select Supplier Code</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo htmlspecialchars($supplier['suppliers_code']); ?>">
                                <?php echo htmlspecialchars($supplier['suppliers_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="suppliers_name">Supplier Name</label>
                    <select id="suppliers_name" name="suppliers_name" required onchange="updateSupplierCode()">
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
                        <?php foreach ($rm_codes as $rmCode): ?>
                            <option value="<?php echo htmlspecialchars($rmCode['RM_code']); ?>">
                                <?php echo htmlspecialchars($rmCode['RM_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descriptions">Descriptions</label>
                    <textarea id="descriptions" name="descriptions" rows="4" required placeholder="Enter loan outward details"></textarea>
                </div>

                <div class="form-group">
                    <label for="number_of_bands">Number of Bands</label>
                    <input type="number" id="number_of_bands" name="number_of_bands" required placeholder="Enter number of bands">
                </div>

                <button type="submit" class="submit-btn">
                    Submit Loan outward
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
            const supplierCode = document.getElementById('suppliers_code').value;
            const supplierNameSelect = document.getElementById('suppliers_name');

            const matchedSupplier = suppliers.find(supplier => supplier.code === supplierCode);
            if (matchedSupplier) {
                supplierNameSelect.value = matchedSupplier.name;
            }
        }

        function updateSupplierCode() {
            const supplierName = document.getElementById('suppliers_name').value;
            const supplierCodeSelect = document.getElementById('suppliers_code');

            const matchedSupplier = suppliers.find(supplier => supplier.name === supplierName);
            if (matchedSupplier) {
                supplierCodeSelect.value = matchedSupplier.code;
            }
        }

        // Form Validation
        document.getElementById('loanoutwardForm').addEventListener('submit', function(e) {
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