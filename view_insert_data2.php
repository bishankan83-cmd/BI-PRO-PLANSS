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

// Prepare SQL to fetch the inserted data from purchase_orders2
$sql = "SELECT * FROM purchase_orders2 WHERE po_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $po_number);
$stmt->execute();
$result = $stmt->get_result();

// Check if data exists
if ($result->num_rows === 0) {
    die("No data found for the selected Purchase Order.");
}

$row = $result->fetch_assoc();
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
            <table class="table table-bordered">
                <tr>
                    <th>PO Number</th>
                    <td><?php echo htmlspecialchars($row['po_number']); ?></td>
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
                    <th>RM Code</th>
                    <td><?php echo htmlspecialchars($row['rm_code']); ?></td>
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