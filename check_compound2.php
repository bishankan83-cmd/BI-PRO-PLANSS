<!DOCTYPE html>
<html>
<head>
    <title>Date Range Input</title>
</head>
<body>
    <form method="post" action="check_compound2.php">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date">
        <input type="submit" value="Submit">
    </form>

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

// SQL query with updated field names
$sql = "SELECT dp.Date,
               SUM(dp.AdditionalData) AS total_plan,
               SUM(dp.AdditionalData * bn.a) AS a_plan,
               SUM(dp.AdditionalData * bn.b) AS b_plan,
               SUM(dp.AdditionalData * bn.c) AS c_plan,
               SUM(dp.AdditionalData * bn.d) AS d_plan,
               SUM(dp.AdditionalData * bn.e) AS e_plan,
               SUM(dp.AdditionalData * bn.f) AS f_plan,
               SUM(dp.AdditionalData * bn.g) AS g_plan,
               SUM(dp.AdditionalData * bn.h) AS h_plan,
               SUM(dp.AdditionalData * bn.i) AS i_plan,
               SUM(dp.AdditionalData * bn.j) AS j_plan,
               SUM(dp.AdditionalData * bn.k) AS k_plan,
               SUM(dp.AdditionalData * bn.l) AS l_plan,
               SUM(dp.AdditionalData * bn.m) AS m_plan,
               SUM(dp.AdditionalData * bn.n) AS n_plan,
               SUM(dp.AdditionalData * bn.o) AS o_plan,
               SUM(dp.AdditionalData * bn.p) AS p_plan,
               SUM(dp.AdditionalData * bn.q) AS q_plan,
               SUM(dp.AdditionalData * bn.r) AS r_plan
        FROM daily_plan_data dp
        INNER JOIN bom_new bn ON dp.Icode = bn.icode
        WHERE dp.Date BETWEEN '$start_date' AND '$end_date'
        GROUP BY dp.Date";

// Execute query
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Output table header
    echo "<table border='1'>";
    echo "<tr><th>Date</th><th>Total Plan</th><th>ATPRS</th><th>Total B-ATS 15</th><th>Total B-BNS 24</th><th>Total BG-BLS 12</th><th>Total CG - BS 901</th><th>Total C - SMS 501</th><th>Total C-ATS 20</th><th>Total C-SMS 702</th><th>Total C-ATNMS 20</th><th>Total T - TRS 102</th><th>Total T-ATNM S</th><th>Total T-ATS 30</th><th>Total T-ATS 35</th><th>Total T-KS 40</th><th>Total T-TRNMS 402</th><th>Total T-TRNMS 402G</th><th>Total T-TRS 202</th><th>WC0001</th></tr>";
    
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Date"] . "</td>";
        echo "<td>" . $row["total_plan"] . "</td>";
        echo "<td>" . $row["a_plan"] . "</td>";
        echo "<td>" . $row["b_plan"] . "</td>";
        echo "<td>" . $row["c_plan"] . "</td>";
        echo "<td>" . $row["d_plan"] . "</td>";
        echo "<td>" . $row["e_plan"] . "</td>";
        echo "<td>" . $row["f_plan"] . "</td>";
        echo "<td>" . $row["g_plan"] . "</td>";
        echo "<td>" . $row["h_plan"] . "</td>";
        echo "<td>" . $row["i_plan"] . "</td>";
        echo "<td>" . $row["j_plan"] . "</td>";
        echo "<td>" . $row["k_plan"] . "</td>";
        echo "<td>" . $row["l_plan"] . "</td>";
        echo "<td>" . $row["m_plan"] . "</td>";
        echo "<td>" . $row["n_plan"] . "</td>";
        echo "<td>" . $row["o_plan"] . "</td>";
        echo "<td>" . $row["p_plan"] . "</td>";
        echo "<td>" . $row["q_plan"] . "</td>";
        echo "<td>" . $row["r_plan"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No data available.";
}

// Close connection
$conn->close();

?>
</body>
</html>
