<?php

// MySQLi connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Get start and end dates from the form
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query with date range filter and sums calculation, rounded to 2 decimal places
$sql = "SELECT dp.Date,
               ROUND(SUM(dp.AdditionalData), 2) AS plan,
               ROUND(SUM(dp.AdditionalData * bn.a), 2) AS a_plan,
               ROUND(SUM(dp.AdditionalData * bn.b), 2) AS b_plan,
               ROUND(SUM(dp.AdditionalData * bn.c), 2) AS c_plan,
               ROUND(SUM(dp.AdditionalData * bn.d), 2) AS d_plan,
               ROUND(SUM(dp.AdditionalData * bn.e), 2) AS e_plan,
               ROUND(SUM(dp.AdditionalData * bn.f), 2) AS f_plan,
               ROUND(SUM(dp.AdditionalData * bn.g), 2) AS g_plan,
               ROUND(SUM(dp.AdditionalData * bn.h), 2) AS h_plan,
               ROUND(SUM(dp.AdditionalData * bn.i), 2) AS i_plan,
               ROUND(SUM(dp.AdditionalData * bn.j), 2) AS j_plan,
               ROUND(SUM(dp.AdditionalData * bn.k), 2) AS k_plan,
               ROUND(SUM(dp.AdditionalData * bn.l), 2) AS l_plan,
               ROUND(SUM(dp.AdditionalData * bn.m), 2) AS m_plan,
               ROUND(SUM(dp.AdditionalData * bn.n), 2) AS n_plan,
               ROUND(SUM(dp.AdditionalData * bn.o), 2) AS o_plan,
               ROUND(SUM(dp.AdditionalData * bn.p), 2) AS p_plan,
               ROUND(SUM(dp.AdditionalData * bn.q), 2) AS q_plan,
               ROUND(SUM(dp.AdditionalData * bn.r), 2) AS r_plan
        FROM daily_plan_data dp
        INNER JOIN bom_new bn ON dp.Icode = bn.icode
        WHERE dp.Date BETWEEN '$start_date' AND '$end_date'
        GROUP BY dp.Date";

// Execute query
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Set header for CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="data_export.csv"');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Output CSV column headers
    fputcsv($output, array(
        'Date', 'Plan', 'ATPRS', 'B-ATS 15', 'B-BNS 24', 'BG-BLS 12', 
        'CG - BS 901', 'C-SMS 501', 'C-ATS 20', 'C-SMS 702', 'C-ATNMS 20', 
        'T - TRS 102', 'T-ATNM S', 'T-ATS 30', 'T-ATS 35', 'T-KS 40', 
        'T-TRNMS 402', 'T-TRNMS 402G', 'T-TRS 202', 'WC0001'
    ));

    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Close file pointer
    fclose($output);
    
    // Stop script execution
    exit();
} else {
    echo "No data available.";
}

// Close connection
$conn->close();

?>
