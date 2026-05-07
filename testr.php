<?php
// Database connection configuration
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query
    $query = "
        SELECT 
            sbs.rm_code, 
            COALESCE(SUM(po.number_of_bands), 0) AS total_bands_in_purchase_orders,
            sbs.current_quantity,
            COALESCE(SUM(lid.number_of_bands), 0) AS total_bands_in_loan_inward,
            COALESCE(SUM(poo.number_of_bands), 0) AS pending_purchase_orders,
            COALESCE(SUM(lod.number_of_bands), 0) AS total_bands_in_loan_outward,
            COALESCE(SUM(lidd.number_of_bands), 0) AS pending_loan_inward,
            COALESCE(SUM(liddd.number_of_bands), 0) AS loan_inward_settlement,
            COALESCE(SUM(mrs.num_of_bands), 0) AS material_request,
            COALESCE(SUM(mrss.number_of_bands), 0) AS loan_outward_settlement,
            COALESCE(SUM(lodd.number_of_bands), 0) AS pending_loan_outward
        FROM 
            steel_band_stock sbs
        LEFT JOIN 
            purchase_orders2 po ON sbs.rm_code = po.rm_code
        LEFT JOIN 
            purchase_orders poo ON sbs.rm_code = poo.rm_code
        LEFT JOIN 
            loan_inward_details2 lid ON sbs.rm_code = lid.rm_code
        LEFT JOIN 
            loan_inward_details lidd ON sbs.rm_code = lidd.rm_code
        LEFT JOIN 
            loan_inward_details_settle liddd ON sbs.rm_code = liddd.rm_code
        LEFT JOIN 
            loan_outward_details2 lod ON sbs.rm_code = lod.rm_code
        LEFT JOIN 
            loan_outward_details lodd ON sbs.rm_code = lodd.rm_code
        LEFT JOIN 
            material_request_history mrs ON sbs.rm_code = mrs.rm_code
        LEFT JOIN 
            loan_outward_details_settle mrss ON sbs.rm_code = mrss.rm_code
        GROUP BY 
            sbs.rm_code, sbs.current_quantity
        ORDER BY 
            total_bands_in_purchase_orders DESC
    ";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Fetch all results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Start HTML output
    echo "<!DOCTYPE html>
          <html>
          <head>
              <title>Stock Summary</title>
              <style>
                  table { border-collapse: collapse; width: 100%; }
                  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                  th { background-color: #f2f2f2; }
                  body { font-family: Arial, sans-serif; margin: 20px; }
                  h2 { color: #333; }
              </style>
          </head>
          <body>
          <h2>Stock and Band Summary</h2>
          <table>
            <tr>
                <th>RM Code</th>
                <th>Current Quantity</th>
                <th>Total Purchase Orders</th>
                <th>Loan Inward Bands</th>
                <th>Combined Bands</th>
                <th>Material Request</th>
                <th>Remaining Bands</th>
                <th>Loan Inward Settlement</th>
                <th>Pending Loan Inward</th>
                <th>Loan Outward</th>
                <th>Loan Outward Settlement</th>
                <th>Pending Loan Outward</th>
                <th>Pending Purchase Orders</th>
            </tr>";

    // Loop through results and display each row
    foreach ($results as $row) {
        $combined_bands = $row['total_bands_in_purchase_orders'] + $row['total_bands_in_loan_inward'];
        $remaining_bands = $combined_bands - $row['material_request'];

        echo "<tr>
                <td>" . htmlspecialchars($row['rm_code']) . "</td>
                <td>" . htmlspecialchars($row['current_quantity']) . "</td>
                <td>" . htmlspecialchars($row['total_bands_in_purchase_orders']) . "</td>
                <td>" . htmlspecialchars($row['total_bands_in_loan_inward']) . "</td>
                <td>" . htmlspecialchars($combined_bands) . "</td>
                <td>" . htmlspecialchars($row['material_request']) . "</td>
                <td>" . htmlspecialchars($remaining_bands) . "</td>
                <td>" . htmlspecialchars($row['loan_inward_settlement']) . "</td>
                <td>" . htmlspecialchars($row['pending_loan_inward']) . "</td>
                <td>" . htmlspecialchars($row['total_bands_in_loan_outward']) . "</td>
                <td>" . htmlspecialchars($row['loan_outward_settlement']) . "</td>
                <td>" . htmlspecialchars($row['pending_loan_outward']) . "</td>
                <td>" . htmlspecialchars($row['pending_purchase_orders']) . "</td>
              </tr>";
    }

    echo "</table>
          </body>
          </html>";

} catch (PDOException $e) {
    // Handle any database connection or query errors
    die("Database Connection Error: " . $e->getMessage());
}
?>
