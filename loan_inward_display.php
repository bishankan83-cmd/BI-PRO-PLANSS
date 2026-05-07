
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






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Inward Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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

        .table-container {
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

        .filter-row {
            margin-bottom: 20px;
        }

        .dataTables_filter, .dataTables_length {
            margin-bottom: 15px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(242, 128, 24, 0.05);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(242, 128, 24, 0.1);
        }

        @media (max-width: 768px) {
            .table-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h2 class="mb-0">Loan Inward Details</h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <?php
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

                    // SQL query to fetch data from the loan_inward_details table
                    $sql = "SELECT loan_number, loan_date, expected_delivery_date, suppliers_code, suppliers_name, rm_code, descriptions, number_of_bands FROM loan_inward_details";
                    $result = $conn->query($sql);
                    ?>

                    <table id="loanDetailsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Loan Number</th>
                                <th>Loan Date</th>
                                <th>Expected Delivery Date</th>
                                <th>Suppliers Code</th>
                                <th>Suppliers Name</th>
                                <th>RM Code</th>
                                <th>Descriptions</th>
                                <th>Number of Bands</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                // Fetch and display each row
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>" . htmlspecialchars($row["loan_number"]) . "</td>
                                        <td>" . htmlspecialchars($row["loan_date"]) . "</td>
                                        <td>" . htmlspecialchars($row["expected_delivery_date"]) . "</td>
                                        <td>" . htmlspecialchars($row["suppliers_code"]) . "</td>
                                        <td>" . htmlspecialchars($row["suppliers_name"]) . "</td>
                                        <td>" . htmlspecialchars($row["rm_code"]) . "</td>
                                        <td>" . htmlspecialchars($row["descriptions"]) . "</td>
                                        <td>" . htmlspecialchars($row["number_of_bands"]) . "</td>
                                        
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>0 results found</td></tr>";
                            }

                            // Close connection
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable with advanced filtering
        $('#loanDetailsTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            columnDefs: [
                { 
                    targets: -1, 
                    orderable: false 
                }
            ],
            language: {
                searchPlaceholder: "Search in all columns..."
            }
        });

        // View button click handler
        $('.view-btn').on('click', function() {
            let loanNumber = $(this).data('loan-number');
            alert('View details for Loan Number: ' + loanNumber);
            // TODO: Implement view details functionality
        });

        // Edit button click handler
        $('.edit-btn').on('click', function() {
            let loanNumber = $(this).data('loan-number');
            alert('Edit details for Loan Number: ' + loanNumber);
            // TODO: Implement edit details functionality
        });
    });
    </script>
</body>
</html>