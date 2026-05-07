<!DOCTYPE html>
<html>
<head>
    <title>Month-wise Totals</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <form method="post" action="">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        <input type="submit" value="Submit">
    </form>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Prepare and bind the query to fetch plan data
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(dp.Date, '%Y-%m') AS month,
               SUM(dp.AdditionalData) AS plan,
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
        WHERE dp.Date BETWEEN ? AND ?
        GROUP BY month
        ORDER BY month
    ");

    $stmt->bind_param("ss", $start_date, $end_date);

    // Execute query
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Prepare data for Chart.js and table
    $months = [];
    $totals = [
        'plan' => [],
        'a_plan' => [],
        'b_plan' => [],
        'c_plan' => [],
        'd_plan' => [],
        'e_plan' => [],
        'f_plan' => [],
        'g_plan' => [],
        'h_plan' => [],
        'i_plan' => [],
        'j_plan' => [],
        'k_plan' => [],
        'l_plan' => [],
        'm_plan' => [],
        'n_plan' => [],
        'o_plan' => [],
        'p_plan' => [],
        'q_plan' => [],
        'r_plan' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $months[] = $row["month"];
        foreach ($totals as $key => &$array) {
            $array[] = $row[$key];
        }
    }

    // Calculate totals and averages
    $overall_totals = array_map(function($key) use ($totals) {
        return array_sum($totals[$key]);
    }, array_keys($totals));

    $averages = array_map(function($total) use ($months) {
        return count($months) ? $total / count($months) : 0;
    }, $overall_totals);

    $data = [
        'months' => $months,
        'totals' => $totals,
        'overall_totals' => $overall_totals,
        'averages' => $averages
    ];

    // Close connection
    $stmt->close();
    $conn->close();
?>

    <h2>Month-wise Totals</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Month</th>
                <th>Total Plan</th>
                <th>ATPRS</th>
                <th>B-ATS 15</th>
                <th>B-BNS 24</th>
                <th>BG-BLS 12</th>
                <th>CG-BS 901</th>
                <th>C-SMS 501</th>
                <th>C-ATS 20</th>
                <th>C-SMS 702</th>
                <th>C-ATNMS 20</th>
                <th>T - TRS 102</th>
                <th>T-ATNM S</th>
                <th>T-ATS 30</th>
                <th>T-ATS 35</th>
                <th>T-KS 40</th>
                <th>T-TRNMS 402</th>
                <th>T-TRNMS 402G</th>
                <th>T-TRS 202</th>
                <th>WC0001</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($months as $index => $month) { ?>
                <tr>
                    <td><?php echo $month; ?></td>
                    <td><?php echo $data['totals']['plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['a_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['b_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['c_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['d_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['e_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['f_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['g_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['h_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['i_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['j_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['k_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['l_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['m_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['n_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['o_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['p_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['q_plan'][$index]; ?></td>
                    <td><?php echo $data['totals']['r_plan'][$index]; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td><strong>Total</strong></td>
                <?php foreach ($data['overall_totals'] as $total) { ?>
                    <td><strong><?php echo $total; ?></strong></td>
                <?php } ?>
            </tr>
            <tr>
                <td><strong>Average</strong></td>
                <?php foreach ($data['averages'] as $average) { ?>
                    <td><strong><?php echo number_format($average, 2); ?></strong></td>
                <?php } ?>
            </tr>
        </tbody>
    </table>

    <h2>Bar Chart</h2>
    <canvas id="myChart" width="800" height="400"></canvas>
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        const data = <?php echo json_encode($data); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.months,
                datasets: [
                    { label: 'ATPRS', data: data.totals.a_plan, backgroundColor: 'rgba(255, 99, 132, 0.2)', borderColor: 'rgba(255, 99, 132, 1)', borderWidth: 1 },
                    { label: 'B-ATS 15', data: data.totals.b_plan, backgroundColor: 'rgba(54, 162, 235, 0.2)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 },
                    { label: 'B-BNS 24', data: data.totals.c_plan, backgroundColor: 'rgba(255, 206, 86, 0.2)', borderColor: 'rgba(255, 206, 86, 1)', borderWidth: 1 },
                    { label: 'BG-BLS 12', data: data.totals.d_plan, backgroundColor: 'rgba(75, 192, 192, 0.2)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 1 },
                    
                    { label: 'CG-BS 901', data: data.totals.e_plan, backgroundColor: 'rgba(153, 102, 255, 0.2)', borderColor: 'rgba(153, 102, 255, 1)', borderWidth: 1 },
                    { label: 'C-SMS 501', data: data.totals.f_plan, backgroundColor: 'rgba(255, 159, 64, 0.2)', borderColor: 'rgba(255, 159, 64, 1)', borderWidth: 1 },
                    { label: 'C-ATS 20', data: data.totals.g_plan, backgroundColor: 'rgba(255, 99, 132, 0.2)', borderColor: 'rgba(255, 99, 132, 1)', borderWidth: 1 },
                    { label: 'C-SMS 702', data: data.totals.h_plan, backgroundColor: 'rgba(54, 162, 235, 0.2)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 },
                    { label: 'C-ATNMS 20', data: data.totals.i_plan, backgroundColor: 'rgba(75, 192, 192, 0.2)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 1 },
                    { label: 'T - TRS 102', data: data.totals.j_plan, backgroundColor: 'rgba(153, 102, 255, 0.2)', borderColor: 'rgba(153, 102, 255, 1)', borderWidth: 1 },
                    { label: 'T-ATNM S', data: data.totals.k_plan, backgroundColor: 'rgba(255, 159, 64, 0.2)', borderColor: 'rgba(255, 159, 64, 1)', borderWidth: 1 },
                    { label: 'T-ATS 30', data: data.totals.l_plan, backgroundColor: 'rgba(255, 99, 132, 0.2)', borderColor: 'rgba(255, 99, 132, 1)', borderWidth: 1 },
                    { label: 'T-ATS 35', data: data.totals.m_plan, backgroundColor: 'rgba(54, 162, 235, 0.2)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 },
                    { label: 'T-KS 40', data: data.totals.n_plan, backgroundColor: 'rgba(75, 192, 192, 0.2)', borderColor: 'rgba(75, 192, 192, 1)', borderWidth: 1 },
                    { label: 'T-TRNMS 402', data: data.totals.o_plan, backgroundColor: 'rgba(153, 102, 255, 0.2)', borderColor: 'rgba(153, 102, 255, 1)', borderWidth: 1 },
                    { label: 'T-TRNMS 402G', data: data.totals.p_plan, backgroundColor: 'rgba(255, 159, 64, 0.2)', borderColor: 'rgba(255, 159, 64, 1)', borderWidth: 1 },
                    { label: 'T-TRS 202', data: data.totals.q_plan, backgroundColor: 'rgba(255, 99, 132, 0.2)', borderColor: 'rgba(255, 99, 132, 1)', borderWidth: 1 },
                    { label: 'WC0001', data: data.totals.r_plan, backgroundColor: 'rgba(54, 162, 235, 0.2)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
<?php
}
?>
</body>
</html>

