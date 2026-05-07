<?php
// Configuration and Database Connection
$config = [
    'host' => 'localhost',
    'dbname' => 'planatir_task_managemen',
    'username' => 'planatir_task_managemen',
    'password' => 'Bishan@1919'
];

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']}", 
        $config['username'], 
        $config['password']
    );
    
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simplified query for shift allocation
    $sql = "
    SELECT 
        p.id,
        p.plan_id,
        p.start_date,
        p.end_date,
        p.erp,
        p.Customer,
        p.icode,
        p.description,
        p.tobe,
        p.press,
        p.press_name,
        p.mold_id,
        p.mold_name,
        p.cavity_id,
        p.cavity_name,
        p.cuing_group_id,
        p.cuing_group_name,
        p.tires_per_mold,
        CASE 
            WHEN TIME(date_series.date) >= '07:00' AND TIME(date_series.date) < '19:00' THEN 'DAY'
            ELSE 'NIGHT'
        END AS shift_type,
        date_series.date AS expanded_date,
        (
            SELECT per_day 
            FROM mold_list ml2 
            WHERE ml2.icode = p.icode 
            ORDER BY ml2.id 
            LIMIT 1
        ) AS mold_per_day
    FROM 
        plannew p
    JOIN 
        (
            SELECT 
                id,
                CONCAT(
                    DATE(start_date + INTERVAL (t.seq) DAY), 
                    ' ', 
                    CASE 
                        WHEN (t.seq = 0 AND TIME(p.start_date) >= '19:00') THEN '23:59:59'
                        WHEN (t.seq = 0 AND TIME(p.start_date) < '07:00') THEN '06:59:59'
                        WHEN (t.seq = DATEDIFF(p.end_date, p.start_date) AND TIME(p.end_date) >= '19:00') THEN '23:59:59'
                        WHEN (t.seq = DATEDIFF(p.end_date, p.start_date) AND TIME(p.end_date) < '07:00') THEN '06:59:59'
                        ELSE '12:00:00'
                    END
                ) AS date
            FROM 
                plannew p,
                (
                    SELECT 
                        @row := @row + 1 AS seq
                    FROM 
                        (SELECT @row := -1) r,
                        information_schema.columns
                    LIMIT 366
                ) t
            WHERE 
                DATE(start_date + INTERVAL (t.seq) DAY) <= DATE(p.end_date)
                AND DATE(start_date + INTERVAL (t.seq) DAY) >= DATE(p.start_date)
        ) date_series ON p.id = date_series.id
    ORDER BY 
        p.id, 
        expanded_date
    ";
    
    // Prepare and execute the query with error handling
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Start HTML output
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Shift Allocation Planning</title>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 100%; 
                margin: 0 auto; 
                padding: 10px; 
                line-height: 1.6;
            }
            .table-container {
                overflow-x: auto;
                width: 100%;
            }
            table {
                border-collapse: collapse;
                width: 100%;
                margin-bottom: 20px;
                font-size: 12px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 6px;
                text-align: left;
                white-space: nowrap;
            }
            .day-shift { background-color: #e6f2ff; }
            .night-shift { background-color: #f2e6ff; }
        </style>
    </head>
    <body>
        <h1>Shift Allocation Planning</h1>
        
        <?php
        // Group results by original record ID
        $grouped_results = [];
        foreach ($results as $row) {
            $grouped_results[$row['id']][] = $row;
        }
        
        // Display results for each original record
        if (!empty($grouped_results)) {
            foreach ($grouped_results as $id => $record_dates) {
                echo "<h2>Record ID: " . htmlspecialchars($id) . "</h2>";
                echo "<div class='table-container'><table>
                    <thead>
                        <tr>";
                
                // Print table headers dynamically
                $headers = array_keys($record_dates[0]);
                foreach ($headers as $header) {
                    echo "<th>" . htmlspecialchars(str_replace('_', ' ', ucwords($header))) . "</th>";
                }
                echo "</tr></thead><tbody>";
                
                // Print table rows for this record
                foreach ($record_dates as $row) {
                    $shift_class = ($row['shift_type'] == 'DAY') ? 'day-shift' : 'night-shift';
                    echo "<tr class='" . $shift_class . "'>";
                    foreach ($row as $key => $value) {
                        echo "<td>" . (($value === null) ? "N/A" : htmlspecialchars($value)) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody></table></div>";
            }
        } else {
            echo "<p>No records found.</p>";
        }
        ?>
    </body>
    </html>
    <?php
    
} catch(PDOException $e) {
    // Detailed error handling
    error_log("Database Error: " . $e->getMessage());
    
    header('HTTP/1.1 500 Internal Server Error');
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Server Error</title>
    </head>
    <body>
        <h1>Server Error</h1>
        <p>An error occurred while processing your request.</p>
        <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
    </body>
    </html>";
    exit();
} catch(Exception $e) {
    // Catch any other unexpected errors
    error_log("Unexpected Error: " . $e->getMessage());
    
    header('HTTP/1.1 500 Internal Server Error');
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Unexpected Error</title>
    </head>
    <body>
        <h1>Unexpected Error</h1>
        <p>An unexpected error occurred while processing your request.</p>
    </body>
    </html>";
    exit();
}
?>