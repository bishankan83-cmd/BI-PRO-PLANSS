<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders Management</title>
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
                    <h2 class="mb-0">Purchase Orders Management</h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <?php
                    // Database connection settings
                    $host = 'localhost';
                    $username = 'planatir_task_managemen';
                    $password = 'Bishan@1919';
                    $database = 'planatir_task_managemen';

                    // Create database connection
                    $conn = new mysqli($host, $username, $password, $database);

                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Fetch data from purchase_orders table
                    $result = $conn->query("SELECT * FROM purchase_orders2");
                    ?>

                    <table id="purchaseOrdersTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PO Number</th>
                                <th>PO Date</th>
                                <th>Expected Delivery</th>
                                <th>Supplier Code</th>
                                <th>Supplier Name</th>
                                <th>RM Code</th>
                                <th>Descriptions</th>
                                <th>Number of Bands</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['po_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['po_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['expected_deliver_inhouse_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['supplier_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['rm_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['descriptions']); ?></td>
                                        <td><?php echo htmlspecialchars($row['number_of_bands']); ?></td>
                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="no-data">
                                            <h2>No Purchase Orders Found</h2>
                                            <p>There are currently no purchase orders in the system.</p>
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
        // Initialize DataTable with advanced filtering and custom features
        $('#purchaseOrdersTable').DataTable({
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
            },
            // Add custom filtering for specific columns if needed
            initComplete: function () {
                this.api().columns().every(function () {
                    var column = this;
                    var select = $('<select class="form-control"><option value=""></option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );

                            column
                                .search(val ? '^' + val + '$' : '', true, false)
                                .draw();
                        });

                    column.data().unique().sort().each(function (d, j) {
                        select.append('<option value="' + d + '">' + d + '</option>')
                    });
                });
            }
        });
    });
    </script>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>
</html>