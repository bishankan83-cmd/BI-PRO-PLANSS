<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tyre Code Inventory Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
        }
        .summary-box {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 18px;
        }
        .summary-value {
            font-weight: bold;
            font-size: 24px;
            color: #2c5282;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            font-weight: bold;
            background-color: #e6e6e6;
        }
        .description {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <h1>Tyre Code Inventory Report</h1>
    
    <?php
    // Database connection parameters
    $host = "localhost";
    $user = "planatir_task_managemen";
    $pass = "Bishan@1919";
    $dbname = "planatir_task_managemen";
    
    // Create connection
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // First get the total sum of all quantities
    $total_sql = "SELECT SUM(qty) as grand_total FROM stock_erp";
    $total_result = $conn->query($total_sql);
    $total_row = $total_result->fetch_assoc();
    $grand_total = $total_row["grand_total"];
    
    // Display the summary box
    echo "<div class='summary-box'>";
    echo "Total Inventory Quantity: <span class='summary-value'>" . number_format($grand_total) . "</span>";
    echo "</div>";
    
    // Query to get quantities by tyre_code with description
    // Using JOIN instead of subquery for better performance
    $sql = "SELECT 
                s1.tyre_code, 
                SUM(s1.qty) as total_quantity,
                s2.description,
                MAX(s1.date) as last_date
            FROM 
                stock_erp s1
            JOIN 
                (SELECT tyre_code, description, MAX(date) as max_date
                 FROM stock_erp
                 GROUP BY tyre_code) s2 
            ON 
                s1.tyre_code = s2.tyre_code
            GROUP BY 
                s1.tyre_code, s2.description
            ORDER BY 
                s1.tyre_code";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>Tyre Code</th>
                <th>Description</th>
                <th>Total Quantity</th>
                <th>Last Updated</th>
              </tr>";
        
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["tyre_code"]) . "</td>";
            echo "<td class='description'>" . htmlspecialchars($row["description"]) . "</td>";
            echo "<td>" . number_format($row["total_quantity"]) . "</td>";
            echo "<td>" . $row["last_date"] . "</td>";
            echo "</tr>";
        }
        
        // Add a total row
        echo "<tr class='total-row'>";
        echo "<td>Grand Total</td>";
        echo "<td></td>";
        echo "<td>" . number_format($grand_total) . "</td>";
        echo "<td></td>";
        echo "</tr>";
        
        echo "</table>";
    } else {
        echo "<p>No records found</p>";
    }
    
    // Close connection
    $conn->close();
    ?>
    
    
</html>