<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan outward Suppliers Management</title>
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

        .no-data {
            text-align: center;
            padding: 20px;
            background-color: rgba(242, 128, 24, 0.05);
            border-radius: 10px;
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
                    <h2 class="mb-0">Loan outward Suppliers Management</h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <?php
                    // Database credentials
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

                    // Query to fetch data from the table
                    $sql = "SELECT * FROM loan_outward_suppliers";
                    $result = $conn->query($sql);
                    ?>

                    <table id="loanoutwardSuppliersTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier Code</th>
                                <th>Supplier Name</th>
                                <th>Description</th>
                                <th>Created At</th>
                                <th>Production Category</th>
                                <th>Contact Person</th>
                                <th>Contact No</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row["id"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["suppliers_code"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["suppliers_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["description"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["production_category"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["contact_person"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["contact_no"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["address"]); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="no-data">
                                            <h2>No Loan outward Suppliers Found</h2>
                                            <p>There are currently no loan outward suppliers in the system.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
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
        $('#loanoutwardSuppliersTable').DataTable({
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

    <?php
    // Close the connection
    $conn->close();
    ?>
</body>
</html>
