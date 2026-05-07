<?php
// Display errors for debugging purposes
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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_stock') {
        addStock($conn, $_POST['band_code'], $_POST['description'], $_POST['rm_code'], $_POST['band_size'], $_POST['mold_size'], $_POST['initial_quantity'], $_POST['minimum_quantity']);
    }
}

// Fetch existing stock data for display
$stock_items = $conn->query("SELECT * FROM steel_band_stock ORDER BY band_code");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Steel Band Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; }
        .container { width: 90%; margin: auto; max-width: 1200px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 2em; color: #333; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .card { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }
        .section-title { font-size: 1.5em; color: #555; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #f4f4f4; }
    </style>
</head>
<body>

<div class="container">
    <header class="header">
        <h1>Add New Steel Band Stock</h1>
    </header>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <h2 class="section-title">Add New Stock</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_stock">
            <label>Band Code:</label><input type="text" name="band_code" required><br>
            <label>Description:</label><input type="text" name="description" required><br>
            <label>RM Code:</label><input type="text" name="rm_code" required><br>
            <label>Band Size:</label><input type="text" name="band_size" required><br>
            <label>Mold Size:</label><input type="text" name="mold_size" required><br>
            <label>Initial Quantity:</label><input type="number" name="initial_quantity" required><br>
            <label>Minimum Quantity:</label><input type="number" name="minimum_quantity" required><br>
            <button type="submit">Add Stock</button>
        </form>
    </div>

    <div class="card">
        <h2 class="section-title">Existing Stock</h2>
        <table>
            <tr>
                <th>Band Code</th>
                <th>Description</th>
                <th>RM Code</th>
                <th>Band Size</th>
                <th>Mold Size</th>
                <th>Current Quantity</th>
                <th>Minimum Quantity</th>
            </tr>
            <?php while ($row = $stock_items->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['band_code'] ?></td>
                    <td><?= $row['description'] ?></td>
                    <td><?= $row['rm_code'] ?></td>
                    <td><?= $row['band_size'] ?></td>
                    <td><?= $row['mold_size'] ?></td>
                    <td><?= $row['current_quantity'] ?></td>
                    <td><?= $row['minimum_quantity'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>
