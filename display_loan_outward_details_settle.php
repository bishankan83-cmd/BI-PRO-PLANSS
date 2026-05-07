







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Outward Details</title>
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
                    <h2 class="mb-0">Loan Outward Details</h2>
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

                    // SQL query to fetch data from the loan_outward_details table
                    $sql = "SELECT loan_number, loan_date, expected_delivery_date, suppliers_code, suppliers_name, rm_code, descriptions, number_of_bands FROM loan_outward_details_settle";
                    $result = $conn->query($sql);
                    ?>

                    <table id="loanOutwardDetailsTable" class="table table-striped table-hover">
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
                                echo "<tr><td colspan='8' class='text-center'>0 results found</td></tr>";
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
        $('#loanOutwardDetailsTable').DataTable({
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
    });
    </script>
</body>
</html>
