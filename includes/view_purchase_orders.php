<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders</title>
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
            --background-color: #f5f5f5;
            --card-background: #FFFFFF;
            --text-dark: #000000;
            --text-light: #FFFFFF;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .dashboard-title {
            background-color: var(--secondary-color);
            color: var(--text-light);
            padding: 1rem;
            border-radius: 25px;
            text-align: center;
            margin: 1rem 0;
            font-weight: bold;
        }

        .table-container {
            background: var(--card-background);
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        .purchase-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .purchase-table thead {
            background-color: var(--primary-color);
            color: var(--text-light);
        }

        .purchase-table th,
        .purchase-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .purchase-table tbody tr:hover {
            background-color: rgba(242, 128, 24, 0.1);
            transition: var(--transition);
        }

        .purchase-table tbody tr {
            transition: var(--transition);
        }

        @media (max-width: 768px) {
            .purchase-table thead {
                display: none;
            }

            .purchase-table, 
            .purchase-table tbody, 
            .purchase-table tr, 
            .purchase-table td {
                display: block;
                width: 100%;
            }

            .purchase-table tr {
                margin-bottom: 1rem;
                border-bottom: 3px solid var(--primary-color);
            }

            .purchase-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }

            .purchase-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-title">
            <h1>Purchase Orders Management</h1>
        </div>

        <div class="table-container">
            <table class="purchase-table">
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
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td data-label="PO Number"><?php echo htmlspecialchars($row['po_number']); ?></td>
                            <td data-label="PO Date"><?php echo htmlspecialchars($row['po_date']); ?></td>
                            <td data-label="Expected Delivery"><?php echo htmlspecialchars($row['expected_deliver_inhouse_date']); ?></td>
                            <td data-label="Supplier Code"><?php echo htmlspecialchars($row['supplier_code']); ?></td>
                            <td data-label="Supplier Name"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                            <td data-label="RM Code"><?php echo htmlspecialchars($row['rm_code']); ?></td>
                            <td data-label="Descriptions"><?php echo htmlspecialchars($row['descriptions']); ?></td>
                            <td data-label="Number of Bands"><?php echo htmlspecialchars($row['number_of_bands']); ?></td>
                            <td data-label="Created At"><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>