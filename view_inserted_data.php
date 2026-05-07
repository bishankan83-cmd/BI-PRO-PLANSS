
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
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if PO number is passed via GET
$po_number = isset($_GET['po_number']) ? $_GET['po_number'] : '';

// If no PO number is provided, redirect back to the main page
if (empty($po_number)) {
    header('Location: purchase_order_management.php');
    exit();
}

// Prepare SQL to fetch all records for the given po_number, including multiple ids
$sql = "SELECT id, po_number, rm_code, po_date, expected_deliver_inhouse_date, supplier_code, supplier_name, descriptions, number_of_bands, previous_number_of_bands, created_at FROM purchase_orders2 WHERE po_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $po_number);
$stmt->execute();
$result = $stmt->get_result();

// Check if data exists
if ($result->num_rows === 0) {
    die("No data found for the selected Purchase Order.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserted Purchase Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
            --background-color: #f0f2f5;
            --card-background: #FFFFFF;
            --text-dark: #333;
            --text-light: #FFFFFF;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .details-container {
            background: var(--card-background);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #d4651a);
            color: var(--text-light);
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-light);
        }

        .btn-custom:hover {
            background-color: #d4651a;
            border-color: #d4651a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h2 class="mb-0">Inserted Purchase Order Details</h2>
                </div>
            </div>
        </div>

        <div class="details-container">
            <h3>Purchase Order Information</h3>

            <?php 
            // Initialize a flag to indicate whether we are showing records for the first PO number
            $current_po_number = '';
            
            while ($row = $result->fetch_assoc()) { 
                // Check if we are still on the same PO number
                if ($row['po_number'] !== $current_po_number) {
                    // If a new PO number, display it
                    if ($current_po_number !== '') {
                        echo "<hr>"; // Separate previous PO section with a horizontal line
                    }
                    $current_po_number = $row['po_number'];
                    echo "<h4>PO Number: " . htmlspecialchars($row['po_number']) . "</h4>";
                }
                ?>
                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                    </tr>
                    <tr>
                        <th>RM Code</th>
                        <td><?php echo htmlspecialchars($row['rm_code']); ?></td>
                    </tr>
                    <tr>
                        <th>PO Date</th>
                        <td><?php echo htmlspecialchars($row['po_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Expected Delivery Date</th>
                        <td><?php echo htmlspecialchars($row['expected_deliver_inhouse_date']); ?></td>
                    </tr>
                    <tr>
                        <th>Supplier Code</th>
                        <td><?php echo htmlspecialchars($row['supplier_code']); ?></td>
                    </tr>
                    <tr>
                        <th>Supplier Name</th>
                        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Descriptions</th>
                        <td><?php echo htmlspecialchars($row['descriptions']); ?></td>
                    </tr>
                    <tr>
                        <th>Number of Bands</th>
                        <td><?php echo htmlspecialchars($row['number_of_bands']); ?></td>
                    </tr>
                    <tr>
                        <th>Previous Number of Bands</th>
                        <td><?php echo htmlspecialchars($row['previous_number_of_bands']); ?></td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                </table>
            <?php } ?>

            <div class="text-center mt-4">
                <a href="s_order_inward.php" class="btn btn-custom">Back to Purchase Order Management</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
