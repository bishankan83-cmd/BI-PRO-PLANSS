

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

// Get the loan number from the URL
$loan_number = isset($_GET['loan_number']) ? $_GET['loan_number'] : '';

// Prepare the SQL query to fetch the inserted data
$sql = "SELECT * FROM loan_outward_details_settle WHERE loan_number = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $loan_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $inserted_data = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserted Loan Inward Details</title>
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
        <div class="row justify-content-center">
            <div class="col-md-10 details-container">
                <div class="page-header">
                    <h2 class="mb-0">Inserted Loan Inward Details</h2>
                </div>

                <?php if ($inserted_data): ?>
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Loan Number</th>
                                <th>Loan Date</th>
                                <th>Expected Delivery Date</th>
                                <th>Suppliers Code</th>
                                <th>Suppliers Name</th>
                                <th>RM Code</th>
                                <th>Descriptions</th>
                                <th>Number of Bands</th>
                                <th>Previous Number of Bands</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($inserted_data['loan_number'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['loan_date'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['expected_delivery_date'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['suppliers_code'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['suppliers_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['rm_code'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['descriptions'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['number_of_bands'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['previous_number_of_bands'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($inserted_data['created_at'] ?? ''); ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No data found for Loan Number: <?php echo htmlspecialchars($loan_number); ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="loan_given_settlement.php" class="btn btn-custom">Back to Loan Inward Management</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>