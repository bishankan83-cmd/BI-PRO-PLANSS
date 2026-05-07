<?php

// MySQLi connection (same as in check_compound2.php)
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Get start and end dates from query parameters
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query with date range filter and sums calculation (same as in check_compound2.php)
$sql = "SELECT dp.Date,
               SUM(dp.AdditionalData) AS total_plan,
               SUM(dp.AdditionalData * bn.a) AS total_a,
               SUM(dp.AdditionalData * bn.b) AS total_b,
               SUM(dp.AdditionalData * bn.c) AS total_c,
               SUM(dp.AdditionalData * bn.d) AS total_d,
               SUM(dp.AdditionalData * bn.e) AS total_e,
               SUM(dp.AdditionalData * bn.f) AS total_f,
               SUM(dp.AdditionalData * bn.g) AS total_g,
               SUM(dp.AdditionalData * bn.h) AS total_h,
               SUM(dp.AdditionalData * bn.i) AS total_i,
               SUM(dp.AdditionalData * bn.j) AS total_j,
               SUM(dp.AdditionalData * bn.k) AS total_k,
               SUM(dp.AdditionalData * bn.l) AS total_l,
               SUM(dp.AdditionalData * bn.m) AS total_m,
               SUM(dp.AdditionalData * bn.n) AS total_n,
               SUM(dp.AdditionalData * bn.o) AS total_o,
               SUM(dp.AdditionalData * bn.p) AS total_p
        FROM daily_plan_data dp
        INNER JOIN bom_new bn ON dp.Icode = bn.icode
        WHERE dp.Date BETWEEN '$start_date' AND '$end_date'
        GROUP BY dp.Date";

// Execute query
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Set headers for Excel file download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="exported_data.xls"');

    // Output Excel file content
    echo "<table border='1'>";
    echo "<tr><th>Date</th><th>Total Plan</th><th>ATPRS</th><th>Total B-ATS 15</th><th>Total B-BNS 24</th><th>Total BG-BLS 12</th><th>Total CG - BS 901</th><th>Total C - SMS 501</th><th>Total C-ATS 20</th><th>Total C-SMS 702</th><th>Total T - TRS 102</th><th>Total T-ATNM S</th><th>Total T-ATS 30</th><th>Total T-ATS 35</th><th>Total T-KS 40</th><th>Total T-TRNMS 402</th><th>Total T-TRNMS 402G</th><th>Total T-TRS 202</th></tr>";

    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Date"] . "</td>";
        echo "<td>" . $row["total_plan"] . "</td>";
        echo "<td>" . $row["total_a"] . "</td>";
        echo "<td>" . $row["total_b"] . "</td>";
        echo "<td>" . $row["total_c"] . "</td>";
        echo "<td>" . $row["total_d"] . "</td>";
        echo "<td>" . $row["total_e"] . "</td>";
        echo "<td>" . $row["total_f"] . "</td>";
        echo "<td>" . $row["total_g"] . "</td>";
        echo "<td>" . $row["total_h"] . "</td>";
        echo "<td>" . $row["total_i"] . "</td>";
        echo "<td>" . $row["total_j"] . "</td>";
        echo "<td>" . $row["total_k"] . "</td>";
        echo "<td>" . $row["total_l"] . "</td>";
        echo "<td>" . $row["total_m"] . "</td>";
        echo "<td>" . $row["total_n"] . "</td>";
        echo "<td>" . $row["total_o"] . "</td>";
        echo "<td>" . $row["total_p"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No data available.";
}

// Close connection
$conn->close();

?>
