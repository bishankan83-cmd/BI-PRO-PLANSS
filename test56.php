<?php
// Database configuration
$config = [
    'servername' => 'localhost',
    'username' => 'planatir_task_managemen',
    'password' => 'Bishan@1919',
    'dbname' => 'planatir_task_managemen'
];

// Updated column definitions
$columnNames = array(
    'plan' => 'plan sum',
    'a_plan' => 'ATPRS',
    'b_plan' => 'B-ATS 15',
    'c_plan' => 'B-BNS 24',
    'd_plan' => 'BG-BLS 12',
    'e_plan' => 'CG -BS 901',
    'f_plan' => 'C-SMS 501',
    'g_plan' => 'C-ATS 20',
    'h_plan' => 'C-SMS 702',
    'i_plan' => 'C-ATNMS 20',
    'j_plan' => 'T - TRS 102',
    'k_plan' => 'T-ATNM S',
    'l_plan' => 'T-ATS 30',
    'm_plan' => 'T-ATS 35',
    'n_plan' => 'T-KS 40',
    'o_plan' => 'T-TRNMS 402',
    'p_plan' => 'T-TRNMS 402G',
    'q_plan' => 'T-TRS 202',
    'r_plan' => 'WC0001',
);

// Initialize database connection
function connectDB($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['servername']};dbname={$config['dbname']}", 
            $config['username'], 
            $config['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Clear and update stored data
function updateStoredData($pdo) {
    try {
        // Clear existing data
        $pdo->exec("DELETE FROM stored_data");

        // Fetch and insert new data
        $sql = "
            SELECT
                cd.date,
                cd.plan,
                cd.calculated_green_tire_weight,
                bn.a * cd.plan AS a_plan,
                bn.b * cd.plan AS b_plan,
                bn.c * cd.plan AS c_plan,
                bn.d * cd.plan AS d_plan,
                bn.e * cd.plan AS e_plan,
                bn.f * cd.plan AS f_plan,
                bn.g * cd.plan AS g_plan,
                bn.h * cd.plan AS h_plan,
                bn.i * cd.plan AS i_plan,
                bn.j * cd.plan AS j_plan,
                bn.k * cd.plan AS k_plan,
                bn.l * cd.plan AS l_plan,
                bn.m * cd.plan AS m_plan,
                bn.n * cd.plan AS n_plan,
                bn.o * cd.plan AS o_plan,
                bn.p * cd.plan AS p_plan,
                bn.q * cd.plan AS q_plan,
                bn.r * cd.plan AS r_plan
            FROM calculated_data cd
            JOIN bom_new bn ON cd.icode = bn.icode
            WHERE cd.plan <> 0
            ORDER BY cd.date
        ";

        $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $insertSql = "
            INSERT INTO stored_data (
                date, plan, calculated_green_tire_weight, 
                a_plan, b_plan, c_plan, d_plan, e_plan, 
                f_plan, g_plan, h_plan, i_plan, j_plan, 
                k_plan, l_plan, m_plan, n_plan, o_plan, 
                p_plan, q_plan, r_plan
            ) VALUES (
                :date, :plan, :calculated_green_tire_weight,
                :a_plan, :b_plan, :c_plan, :d_plan, :e_plan,
                :f_plan, :g_plan, :h_plan, :i_plan, :j_plan,
                :k_plan, :l_plan, :m_plan, :n_plan, :o_plan,
                :p_plan, :q_plan, :r_plan
            )
        ";

        $stmt = $pdo->prepare($insertSql);
        foreach ($results as $row) {
            // Convert nulls to 0
            foreach ($row as $key => $value) {
                $row[$key] = $value ?? 0;
            }
            $stmt->execute($row);
        }

        return true;
    } catch (PDOException $e) {
        error_log("Error updating stored data: " . $e->getMessage());
        return false;
    }
}

// Get data for display
function getData($pdo, $startDate, $endDate) {
    $sql = "
        SELECT 
            `date`,
            SUM(`a_plan`) AS `sum_a_plan`,
            SUM(`b_plan`) AS `sum_b_plan`,
            SUM(`c_plan`) AS `sum_c_plan`,
            SUM(`d_plan`) AS `sum_d_plan`,
            SUM(`e_plan`) AS `sum_e_plan`,
            SUM(`f_plan`) AS `sum_f_plan`,
            SUM(`g_plan`) AS `sum_g_plan`,
            SUM(`h_plan`) AS `sum_h_plan`,
            SUM(`i_plan`) AS `sum_i_plan`,
            SUM(`j_plan`) AS `sum_j_plan`,
            SUM(`k_plan`) AS `sum_k_plan`,
            SUM(`l_plan`) AS `sum_l_plan`,
            SUM(`m_plan`) AS `sum_m_plan`,
            SUM(`n_plan`) AS `sum_n_plan`,
            SUM(`o_plan`) AS `sum_o_plan`,
            SUM(`p_plan`) AS `sum_p_plan`,
            SUM(`q_plan`) AS `sum_q_plan`,
            SUM(`r_plan`) AS `sum_r_plan`,
            SUM(`plan`) AS `sum_plan`
        FROM `stored_data`
        WHERE `date` BETWEEN :start_date AND :end_date
        GROUP BY `date`
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Main execution
$startDate = $_GET['start_date'] ?? date('Y-m-d');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$pdo = connectDB($config);
updateStoredData($pdo);
$result = getData($pdo, $startDate, $endDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data to Excel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
        }
        
        .controls {
            margin-bottom: 20px;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 12px;
            font-family: 'Arial', sans-serif;
        }

        th {
            background-color: #F28018;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        td {
            text-align: right;
        }

        th:first-child, td:first-child {
            text-align: left;
        }

        .export-form {
            margin-bottom: 20px;
        }

        .date-input {
            padding: 5px;
            margin-right: 10px;
        }

        .button {
            padding: 8px 15px;
            background-color: #F28018;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #D96B16;
        }
    </style>
</head>
<body>
    <div class="controls">
        <form id="exportForm" method="get" action="export13.php" class="export-form">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="date-input">
            
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="date-input">
            
            <input type="button" value="Export to Excel" onclick="exportToExcel()" class="button">
        </form>

        <form method="get" action="">
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="date-input">
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="date-input">
            <input type="submit" value="Apply" class="button">
        </form>
    </div>

    <table id="dataTable">
        <tr>
            <th></th>
            <?php foreach ($result as $row): ?>
                <th>
                    Total Plan: <?= number_format($row['sum_plan'] ?? 0) ?><br>
                    <?= htmlspecialchars($row['date']) ?>
                </th>
            <?php endforeach; ?>
            <th>Total</th>
        </tr>

        <?php
        $columns = range('a', 'r'); // Updated to include 'r'
        foreach ($columns as $column):
            $columnKey = "{$column}_plan";
            echo "<tr>";
            echo "<th>" . htmlspecialchars($columnNames[$columnKey]) . "</th>";
            
            $columnTotal = 0;
            foreach ($result as $row) {
                $sumColumn = "sum_{$column}_plan";
                $value = $row[$sumColumn] ?? 0;
                $columnTotal += $value;
                echo "<td>" . number_format($value, 2) . "</td>";
            }
            
            echo "<td><strong>" . number_format($columnTotal, 2) . "</strong></td>";
            echo "</tr>";
        endforeach;
        ?>

        <tr>
            <th>Total</th>
            <?php
            $totalOverall = 0;
            foreach ($result as $row) {
                $dayTotal = array_sum(array_map(function ($column) use ($row) {
                    return $row["sum_{$column}_plan"] ?? 0;
                }, $columns));
                $totalOverall += $dayTotal;
                echo "<td><strong>" . number_format($dayTotal, 2) . "</strong></td>";
            }
            echo "<td><strong>" . number_format($totalOverall, 2) . "</strong></td>";
            ?>
        </tr>
    </table>

    <script>
        function exportToExcel() {
            document.getElementById("exportForm").submit();
        }
    </script>
</body>
</html>