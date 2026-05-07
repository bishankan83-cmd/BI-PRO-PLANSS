<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data to Excel</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
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
            font-family: 'Arial', sans-serif;
            text-align: right;
        }

        th:first-child,
        td:first-child {
            text-align: left;
        }
    </style>
</head>
<body>
    <form id="exportForm" method="get" action="export13.php">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
        <input type="button" value="Export to Excel" onclick="exportToExcel()">
    </form>

    <table id="dataTable">
        <tr>
            <th></th>
            <?php foreach ($result as $row) : ?>
            
            <?php endforeach; ?>
           
        </tr>

        <?php
        $columnNames = array(
            'plan' => 'plan sum',
            'a_plan' => 'ATPRS',
            'b_plan' => 'B-ATS 15',
            'c_plan' => 'B-BNS 24',
            'd_plan' => 'BG-BLS 12',
            'e_plan' => 'CG - BS 901',
            'f_plan' => 'C - SMS 501',
            'g_plan' => 'C-ATS 20',
            'h_plan' => 'C-SMS 702',
            'i_plan' => 'T - TRS 102',
            'j_plan' => 'T-ATNM S',
            'k_plan' => 'T-ATS 30',
            'l_plan' => 'T-ATS 35',
            'm_plan' => 'T-KS 40',
            'n_plan' => 'T-TRNMS 402',
            'o_plan' => 'T-TRNMS 402G',
            'p_plan' => 'T-TRS 202',
            'q_plan' => 'WC0001',
        );

        $columns = range('a', 'q');

        foreach ($columns as $column) {
           // echo "<tr><th>{$columnNames["{$column}_plan"]}</th>";
            $columnTotal = 0;

            foreach ($result as $row) {
                $sumColumn = "sum_{$column}_plan";
                $cellValue = $row[$sumColumn] !== null ? $row[$sumColumn] : '-';
                //echo "<td>{$cellValue}</td>";

                if ($cellValue !== '-') {
                    $columnTotal += $row[$sumColumn];
                }
            }

           // echo "<td><strong>{$columnTotal}</strong></td></tr>";
        }

       // echo "<tr><th>Total</th>";
        $totalOverall = 0;

        foreach ($result as $row) {
            $dayTotal = array_sum(array_map(function ($column) use ($row) {
                return $row["sum_{$column}_plan"];
            }, $columns));

            $dayTotalRounded = number_format($dayTotal, 2);
          //  echo "<td><strong>{$dayTotalRounded}</strong></td>";
            $totalOverall += $dayTotal;
        }

        $totalOverallRounded = number_format($totalOverall, 2);
      //  echo "<td><strong>{$totalOverallRounded}</strong></td></tr></table>";
        ?>
        
    <script>
        function exportToExcel() {
            document.getElementById("exportForm").submit();
        }
    </script>
</body>
</html>
