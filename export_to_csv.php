<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters from the URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$selected_mold_id = isset($_GET['mold_id']) ? $_GET['mold_id'] : '';
$selected_mold_size = isset($_GET['mold_size']) ? $_GET['mold_size'] : '';
$selected_brand = isset($_GET['brand']) ? $_GET['brand'] : '';

// Build WHERE clause for filtering
$where_clauses = [];
if ($selected_mold_id) {
    $where_clauses[] = "mold.mold_id = '$selected_mold_id'";
}
if ($selected_mold_size) {
    $where_clauses[] = "mold_list.mold_size = '$selected_mold_size'";
}
if ($selected_brand) {
    $where_clauses[] = "realstock.brand = '$selected_brand'";
}
$where_clause = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : '';

// Query to retrieve mold data with icodes and brand
$sql = "SELECT 
            mold.id, 
            mold.mold_id, 
            plannew.erp,  -- Add ERP here
            mold_list.mold_size, 
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN mold_list.per_day > 0 AND plannew.icode = realstock.icode THEN realstock.brand 
                    ELSE NULL 
                END ORDER BY realstock.brand SEPARATOR ', ') AS brands_from_plannew,
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN mold_list.per_day = 0 THEN realstock.brand 
                    ELSE NULL 
                END ORDER BY realstock.brand SEPARATOR ', ') AS brands_from_realstock,
            GROUP_CONCAT(CONCAT(DATE(plannew.start_date), ':', DATE(plannew.end_date)) SEPARATOR '; ') AS date_ranges,
            mold_list.per_day,
            GROUP_CONCAT(DISTINCT plannew.press_name ORDER BY plannew.press_name SEPARATOR ', ') AS press_names
        FROM mold 
        LEFT JOIN plannew ON mold.mold_id = plannew.mold_id 
        LEFT JOIN mold_list ON mold.mold_id = mold_list.mold_id 
        LEFT JOIN realstock ON TRIM(mold_list.icode) = TRIM(realstock.icode) 
        $where_clause 
        GROUP BY mold.id, mold.mold_id, mold_list.mold_size, plannew.erp 
        ORDER BY mold.id";

$result = $conn->query($sql);
if (!$result) {
    die("Error in query: " . $conn->error);
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="mold_data.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV headers
$headers = ["ID", "Mold ID", "ERP", "Mold Size", "Press Name", "Brand"];
// Generate headers for each date in the range
$current_date = strtotime($start_date);
$end_timestamp = strtotime($end_date);
while ($current_date <= $end_timestamp) {
    $headers[] = date('Y-m-d', $current_date);
    $current_date = strtotime('+1 day', $current_date);
}
fputcsv($output, $headers);

// Fill data
while ($row = $result->fetch_assoc()) {
    $data_row = [
        $row['id'],
        $row['mold_id'],
        $row['erp'], // Add ERP column here
        $row['mold_size'],
        $row['press_names'],
        $row['brands_from_plannew'] . ", " . $row['brands_from_realstock'] // Combine both brand lists
    ];

    // Prepare ticks for dates
    $ticks = [];
    // Initialize ticks for each date in the range
    $current_date = strtotime($start_date);
    while ($current_date <= $end_timestamp) {
        $ticks[date('Y-m-d', $current_date)] = ''; // Default to empty
        $current_date = strtotime('+1 day', $current_date);
    }

    // Check if the mold was utilized on the specified dates
    if (!empty($row['date_ranges'])) {
        $date_ranges = explode('; ', $row['date_ranges']);
        foreach ($date_ranges as $range) {
            $parts = explode(':', $range);
            if (count($parts) === 2) {
                $record_start = strtotime($parts[0]);
                $record_end = strtotime($parts[1]);

                // Mark the dates within the range
                $current_date = strtotime($start_date);
                while ($current_date <= $end_timestamp) {
                    $current_date_str = date('Y-m-d', $current_date);
                    if ($current_date >= $record_start && $current_date <= $record_end) {
                        $ticks[$current_date_str] = 'X'; // Mark with 'X' or other indicator
                    }
                    $current_date = strtotime('+1 day', $current_date);
                }
            }
        }
    }

    // Add ticks to the data row
    foreach ($ticks as $date => $tick) {
        $data_row[] = $tick;
    }

    // Write row to CSV
    fputcsv($output, $data_row);
}

// Close the output stream
fclose($output);

// Close the database connection
$conn->close();
?>