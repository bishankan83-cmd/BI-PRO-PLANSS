<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Database configuration
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create database connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$message = '';
$error = '';

// Function to add new stock
function addStock($conn, $band_code, $description, $rm_code, $band_size, $mold_size, $initial_quantity, $minimum_quantity) {
    global $message, $error;

    $check = $conn->prepare("SELECT id FROM steel_band_stock WHERE band_code = ?");
    $check->bind_param("s", $band_code);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Band code already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO steel_band_stock (band_code, description, rm_code, band_size, mold_size, current_quantity, minimum_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $band_code, $description, $rm_code, $band_size, $mold_size, $initial_quantity, $minimum_quantity);

        if ($stmt->execute()) {
            $stmt2 = $conn->prepare("INSERT INTO stock_transactions (band_code, transaction_type, quantity, reference_number, remarks) VALUES (?, 'IN', ?, 'INITIAL STOCK', 'Initial stock entry')");
            $stmt2->bind_param("si", $band_code, $initial_quantity);
            $stmt2->execute();
            $message = "New steel band stock added successfully!";
        } else {
            $error = "Error adding stock: " . $stmt->error;
        }
    }
}

// Function to process stock transaction
function processTransaction($conn, $band_code, $transaction_type, $quantity, $reference_number, $remarks) {
    global $message, $error;

    $conn->begin_transaction();

    try {
        $check_stock = $conn->prepare("SELECT current_quantity FROM steel_band_stock WHERE band_code = ?");
        $check_stock->bind_param("s", $band_code);
        $check_stock->execute();
        $current_stock = $check_stock->get_result()->fetch_assoc()['current_quantity'];

        if ($transaction_type == 'OUT' && $quantity > $current_stock) {
            throw new Exception("Insufficient stock! Available: " . $current_stock);
        }

        $stmt = $conn->prepare("INSERT INTO stock_transactions (band_code, transaction_type, quantity, reference_number, remarks) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiis", $band_code, $transaction_type, $quantity, $reference_number, $remarks);
        $stmt->execute();

        $quantity_change = ($transaction_type == 'IN') ? $quantity : -$quantity;
        $update = $conn->prepare("UPDATE steel_band_stock SET current_quantity = current_quantity + ? WHERE band_code = ?");
        $update->bind_param("is", $quantity_change, $band_code);
        $update->execute();

        $conn->commit();
        $message = "Stock transaction recorded successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_stock') {
            addStock($conn, $_POST['band_code'], $_POST['description'], $_POST['rm_code'], $_POST['band_size'], $_POST['mold_size'], $_POST['initial_quantity'], $_POST['minimum_quantity']);
        } elseif ($_POST['action'] == 'stock_transaction') {
            processTransaction($conn, $_POST['band_code'], $_POST['transaction_type'], $_POST['quantity'], $_POST['reference_number'], $_POST['remarks']);
        }
    }
}

// Fetch data for display
$stock_items = $conn->query("SELECT * FROM steel_band_stock ORDER BY band_code");
$recent_transactions = $conn->query("
    SELECT t.*
    FROM stock_transactions t
    JOIN steel_band_stock s ON t.band_code = s.band_code
    ORDER BY t.transaction_date DESC 
    LIMIT 10
");
$low_stock_items = $conn->query("
    SELECT * FROM steel_band_stock 
    WHERE current_quantity <= minimum_quantity 
    ORDER BY band_code
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steel Band Stock Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; }
        .container { width: 90%; margin: auto; max-width: 1200px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 2em; color: #333; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .dashboard { display: flex; flex-wrap: wrap; gap: 20px; }
        .card { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); flex: 1; }
        .table-responsive { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #f4f4f4; }
        .section-title { font-size: 1.5em; color: #555; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <header class="header">
        <h1>Steel Band Stock Management</h1>
    </header>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="dashboard">
       
        <div class="card">
    <h2 class="section-title">Record Transaction</h2>
    <form method="POST">
        <input type="hidden" name="action" value="stock_transaction">
        <label>Band Code:</label>
        <select name="band_code" required>
            <option value="">Select Band Code</option>
            <?php
            // Fetch band codes for the dropdown
            $band_codes = $conn->query("SELECT band_code FROM steel_band_stock ORDER BY band_code");
            while ($row = $band_codes->fetch_assoc()):
            ?>
                <option value="<?= $row['band_code'] ?>"><?= $row['band_code'] ?></option>
            <?php endwhile; ?>
        </select><br>
        <label>Transaction Type:</label>
        <select name="transaction_type" required>
            <option value="IN">IN</option>
            <option value="OUT">OUT</option>
        </select><br>
        <label>Quantity:</label><input type="number" name="quantity" required><br>
        <label>Reference Number:</label><input type="text" name="reference_number" required><br>
        <label>Remarks:</label><input type="text" name="remarks"><br>
        <button type="submit">Record Transaction</button>
    </form>
</div>

    </div>

    <div class="table-responsive">
        <h2 class="section-title">Current Stock</h2>
        <table>
            <thead>
                <tr>
                    <th>Band Code</th>
                    <th>Description</th>
                    <th>Current Quantity</th>
                    <th>Minimum Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stock_items->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['band_code'] ?></td>
                        <td><?= $row['description'] ?></td>
                        <td><?= $row['current_quantity'] ?></td>
                        <td><?= $row['minimum_quantity'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="table-responsive">
        <h2 class="section-title">Recent Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Transaction Date</th>
                    <th>Band Code</th>
                    <th>Transaction Type</th>
                    <th>Quantity</th>
                    <th>Reference Number</th>
                    <th>Remarks</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?= $transaction['transaction_date'] ?></td>
                        <td><?= $transaction['band_code'] ?></td>
                        <td><?= $transaction['transaction_type'] ?></td>
                        <td><?= $transaction['quantity'] ?></td>
                        <td><?= $transaction['reference_number'] ?></td>
                        <td><?= $transaction['remarks'] ?></td>
                        <td><?= $transaction['description'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="table-responsive">
        <h2 class="section-title">Low Stock Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Band Code</th>
                    <th>Description</th>
                    <th>Current Quantity</th>
                    <th>Minimum Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($low_stock = $low_stock_items->fetch_assoc()): ?>
                    <tr>
                        <td><?= $low_stock['band_code'] ?></td>
                        <td><?= $low_stock['description'] ?></td>
                        <td><?= $low_stock['current_quantity'] ?></td>
                        <td><?= $low_stock['minimum_quantity'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
