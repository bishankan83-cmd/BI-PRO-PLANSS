<?php
// ══════════════════════════════════════════════════════════════════════════════
//  STEP 1 — EXCEL EXPORT
// ══════════════════════════════════════════════════════════════════════════════

$columnNames = [
    'a_plan' => 'ATPRS',
    'b_plan' => 'B-ATS 15',
    'c_plan' => 'B-BNS 24',
    'd_plan' => 'BG-BLS 12',
    'e_plan' => 'CG-BS 901',
    'f_plan' => 'C-SMS 501',
    'g_plan' => 'C-ATS 20',
    'h_plan' => 'C-SMS 702',
    'i_plan' => 'C-ATNMS 20',
    'j_plan' => 'T-TRS 102',
    'k_plan' => 'T-ATNM S',
    'l_plan' => 'T-ATS 30',
    'm_plan' => 'T-ATS 35',
    'n_plan' => 'T-KS 40',
    'o_plan' => 'T-TRNMS 402',
    'p_plan' => 'T-TRNMS 402G',
    'q_plan' => 'T-TRS 202',
    'r_plan' => 'WC0001',
];

define('DB_HOST', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_task_managemen');

$mainSQL = "
    SELECT
        dp.Icode,
        SUM(dp.AdditionalData)            AS TotalQty,
        SUM(dp.AdditionalData) * bn.a     AS Plan_times_a,
        SUM(dp.AdditionalData) * bn.b     AS Plan_times_b,
        SUM(dp.AdditionalData) * bn.c     AS Plan_times_c,
        SUM(dp.AdditionalData) * bn.d     AS Plan_times_d,
        SUM(dp.AdditionalData) * bn.e     AS Plan_times_e,
        SUM(dp.AdditionalData) * bn.f     AS Plan_times_f,
        SUM(dp.AdditionalData) * bn.g     AS Plan_times_g,
        SUM(dp.AdditionalData) * bn.h     AS Plan_times_h,
        SUM(dp.AdditionalData) * bn.i     AS Plan_times_i,
        SUM(dp.AdditionalData) * bn.j     AS Plan_times_j,
        SUM(dp.AdditionalData) * bn.k     AS Plan_times_k,
        SUM(dp.AdditionalData) * bn.l     AS Plan_times_l,
        SUM(dp.AdditionalData) * bn.m     AS Plan_times_m,
        SUM(dp.AdditionalData) * bn.n     AS Plan_times_n,
        SUM(dp.AdditionalData) * bn.o     AS Plan_times_o,
        SUM(dp.AdditionalData) * bn.p     AS Plan_times_p,
        SUM(dp.AdditionalData) * bn.q     AS Plan_times_q,
        SUM(dp.AdditionalData) * bn.r     AS Plan_times_r
    FROM daily_plan_data dp
    INNER JOIN bom_new bn ON dp.Icode = bn.icode
    WHERE dp.Date BETWEEN ? AND ?
    GROUP BY dp.Icode,
             bn.a, bn.b, bn.c, bn.d, bn.e, bn.f, bn.g, bn.h, bn.i,
             bn.j, bn.k, bn.l, bn.m, bn.n, bn.o, bn.p, bn.q, bn.r
    ORDER BY dp.Icode
";

// ── SQL: Icodes in daily_plan_data that have NO entry in bom_new ────────────
$missingBomSQL = "
    SELECT
        dp.Icode,
        SUM(dp.AdditionalData) AS TotalQty,
        COUNT(*)               AS RecordCount,
        MIN(dp.Date)           AS FirstDate,
        MAX(dp.Date)           AS LastDate
    FROM daily_plan_data dp
    LEFT JOIN bom_new bn ON dp.Icode = bn.icode
    WHERE dp.Date BETWEEN ? AND ?
      AND bn.icode IS NULL
    GROUP BY dp.Icode
    ORDER BY dp.Icode
";

// ── EXCEL DOWNLOAD ──────────────────────────────────────────────────────────
if (
    $_SERVER["REQUEST_METHOD"] === "POST"   &&
    isset($_POST['action'])                 &&
    $_POST['action'] === 'excel'            &&
    !empty($_POST['start_date'])            &&
    !empty($_POST['end_date'])
) {
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) { die("DB error: " . $conn->connect_error); }

    $stmt = $conn->prepare($mainSQL);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="Production_Report_' . $start_date . '_to_' . $end_date . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "\xEF\xBB\xBF";

    $colCount = 2 + count($columnNames);

    echo '<table border="1">';

    echo '<tr>
            <th colspan="' . $colCount . '" style="background:#F28018;color:white;font-size:15px;font-weight:bold;text-align:center;">
              Production Planning Report &nbsp;|&nbsp; '
              . htmlspecialchars($start_date) . ' to ' . htmlspecialchars($end_date) .
            '</th>
          </tr>';

    echo '<tr>';
    echo '<th style="background:#F28018;color:white;font-weight:bold;">Icode</th>';
    echo '<th style="background:#F28018;color:white;font-weight:bold;">Total Qty (AdditionalData)</th>';
    foreach ($columnNames as $col) {
        echo '<th style="background:#F28018;color:white;font-weight:bold;">' . htmlspecialchars($col) . '</th>';
    }
    echo '</tr>';

    $sums = array_fill_keys(array_keys($columnNames), 0);
    $grandTotalQty = 0;

    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['Icode'])    . '</td>';
        echo '<td>' . number_format($row['TotalQty'], 2) . '</td>';
        $grandTotalQty += $row['TotalQty'];

        for ($i = 'a'; $i <= 'r'; $i++) {
            $key    = 'Plan_times_' . $i;
            $mapKey = $i . '_plan';
            echo '<td>' . number_format($row[$key], 2) . '</td>';
            $sums[$mapKey] += $row[$key];
        }
        echo '</tr>';
    }

    echo '<tr style="background:#fff3e6;font-weight:bold;">';
    echo '<td>Grand Total</td>';
    echo '<td>' . number_format($grandTotalQty, 2) . '</td>';
    foreach ($sums as $s) {
        echo '<td>' . number_format($s, 2) . '</td>';
    }
    echo '</tr>';

    echo '</table>';

    // ── Missing BOM section in the same Excel file ──────────────────────────
    $stmt2 = $conn->prepare($missingBomSQL);
    $stmt2->bind_param("ss", $start_date, $end_date);
    $stmt2->execute();
    $missingResult = $stmt2->get_result();

    if ($missingResult->num_rows > 0) {
        echo '<br><table border="1">';
        echo '<tr>
                <th colspan="5" style="background:#c0392b;color:white;font-size:14px;font-weight:bold;text-align:center;">
                  ⚠ Icodes NOT Found in BOM New &nbsp;|&nbsp; '
                  . htmlspecialchars($start_date) . ' to ' . htmlspecialchars($end_date) .
                '</th>
              </tr>';
        echo '<tr>
                <th style="background:#c0392b;color:white;font-weight:bold;">Icode</th>
                <th style="background:#c0392b;color:white;font-weight:bold;">Total Qty</th>
                <th style="background:#c0392b;color:white;font-weight:bold;">Record Count</th>
                <th style="background:#c0392b;color:white;font-weight:bold;">First Date</th>
                <th style="background:#c0392b;color:white;font-weight:bold;">Last Date</th>
              </tr>';
        while ($mRow = $missingResult->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($mRow['Icode'])          . '</td>';
            echo '<td>' . number_format($mRow['TotalQty'], 2)       . '</td>';
            echo '<td>' . (int)$mRow['RecordCount']                 . '</td>';
            echo '<td>' . htmlspecialchars($mRow['FirstDate'])       . '</td>';
            echo '<td>' . htmlspecialchars($mRow['LastDate'])        . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        $stmt2->close();
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Compound Tire Wise</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Barlow:wght@400;500;600&display=swap');

        :root {
            --brand:       #F28018;
            --brand-dark:  #D96B16;
            --brand-light: #fff3e6;
            --bg:          #f0f2f5;
            --white:       #ffffff;
            --border:      #e0e0e0;
            --text:        #2c2c2c;
            --muted:       #888;
            --green:       #1D6F42;
            --green-dark:  #155634;
            --red:         #c0392b;
            --red-dark:    #a93226;
            --red-light:   #fdecea;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 30px 20px;
        }

        .container { max-width: 1500px; margin: 0 auto; }

        /* ── Header ── */
        .header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }
        .header-icon {
            width: 52px; height: 52px;
            background: var(--brand);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .header-icon svg { width: 28px; height: 28px; fill: #fff; }
        .header h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2.1rem;
            color: var(--brand);
            letter-spacing: 0.4px;
            line-height: 1;
        }
        .header p { font-size: 0.83rem; color: var(--muted); margin-top: 4px; }

        /* ── Controls card ── */
        .controls {
            background: var(--white);
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            padding: 24px 28px;
            margin-bottom: 24px;
        }
        .controls h2 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 18px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 16px;
        }
        .form-field { display: flex; flex-direction: column; gap: 6px; }
        .form-field label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--muted);
            letter-spacing: 0.6px;
        }
        .date-input {
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Barlow', sans-serif;
            color: var(--text);
            transition: border-color .2s, box-shadow .2s;
            min-width: 170px;
        }
        .date-input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(242,128,24,.13);
        }

        /* ── Buttons ── */
        .btn {
            padding: 10px 22px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Barlow', sans-serif;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color .2s, transform .1s, box-shadow .2s;
            white-space: nowrap;
        }
        .btn:active { transform: scale(.97); }
        .btn svg { width: 16px; height: 16px; fill: #fff; flex-shrink: 0; }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-primary:hover { background: var(--brand-dark); box-shadow: 0 4px 12px rgba(242,128,24,.35); }
        .btn-excel   { background: var(--green); color: #fff; }
        .btn-excel:hover { background: var(--green-dark); box-shadow: 0 4px 12px rgba(29,111,66,.3); }

        /* ── Result card ── */
        .result-card {
            background: var(--white);
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            padding: 22px 26px;
            margin-bottom: 24px;
        }
        .result-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 18px;
        }
        .result-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.3rem;
            color: var(--text);
        }
        .result-title span { color: var(--brand); }
        .result-meta {
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── Missing BOM card ── */
        .missing-card {
            background: var(--white);
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            padding: 22px 26px;
            border-top: 4px solid var(--red);
        }
        .missing-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }
        .missing-badge {
            background: var(--red);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .missing-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.2rem;
            color: var(--red);
        }
        .missing-meta {
            font-size: 0.8rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── Table (shared) ── */
        .table-scroll {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border);
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13px;
        }
        thead th {
            background: var(--brand);
            color: #fff;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.4px;
            padding: 12px 13px;
            text-align: center;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        thead th:first-child { text-align: left; border-radius: 10px 0 0 0; }
        thead th:last-child  { border-radius: 0 10px 0 0; }

        /* Red header variant for missing-BOM table */
        .missing-card thead th {
            background: var(--red);
        }

        tbody td {
            padding: 10px 13px;
            border-bottom: 1px solid #f0f0f0;
            text-align: right;
            white-space: nowrap;
        }
        tbody td:first-child,
        tbody td:nth-child(2) { text-align: left; }
        tbody tr:hover td { background: #fdf5ec; }
        tbody tr:last-child td { border-bottom: none; }

        .missing-card tbody tr:hover td { background: var(--red-light); }

        .total-row td {
            background: var(--brand-light) !important;
            font-weight: 700;
            border-top: 2px solid var(--brand);
            color: var(--brand-dark);
        }

        /* ── Alerts ── */
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            font-size: 14px;
            border-left: 4px solid var(--brand);
            background: var(--brand-light);
            color: #7a3c00;
        }
        .error-box {
            padding: 16px 20px;
            border-radius: 10px;
            background: #fdecea;
            border-left: 4px solid #e53935;
            color: #c62828;
            font-size: 14px;
        }
        .no-missing {
            padding: 16px 20px;
            border-radius: 10px;
            background: #eafaf1;
            border-left: 4px solid #27ae60;
            color: #1e8449;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* main table needs min-width for many columns */
        .main-table { min-width: 1000px; }
    </style>
</head>
<body>
<div class="container">

    <!-- ── Page header ── -->
    <div class="header">
        <div class="header-icon">
            <svg viewBox="0 0 24 24"><path d="M20 6h-2.18c.07-.44.18-.88.18-1.34C18 2.54 15.96.5 13.5.5c-1.3 0-2.43.56-3.26 1.44L9 3.17l-1.24-1.24C6.93 1.06 5.8.5 4.5.5 2.04.5 0 2.54 0 4.66c0 .46.11.9.18 1.34H0l-2 7h24l-2-7h-2zm-6.5-4c1.38 0 2.5 1.12 2.5 2.16 0 .37-.1.73-.28 1.06L12 8.5 8.28 5.22C8.1 4.89 8 4.53 8 4.16 8 3.12 9.12 2 10.5 2H13.5z"/><path d="M1 15h22v2H1zm2 4h18v2H3z"/></svg>
        </div>
        <div>
            <h1>Production Planning Report</h1>
            <p>Compound Tire Wise — AdditionalData × BOM Ratios per Icode</p>
        </div>
    </div>

    <!-- ── Filter form ── -->
    <div class="controls">
        <h2>Report Filters</h2>
        <form method="post" action="">
            <div class="form-row">
                <div class="form-field">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="date-input"
                        value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>"
                        required>
                </div>
                <div class="form-field">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="date-input"
                        value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>"
                        required>
                </div>
                <button type="submit" name="action" value="generate" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    Generate Report
                </button>
            </div>
        </form>
    </div>

<?php
// ── STEP 2 — GENERATE HTML REPORT ──────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'generate') {

    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        echo "<div class='error-box'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>";
    } else {

        // ── TABLE 1: Main BOM-matched report ───────────────────────────────
        $stmt = $conn->prepare($mainSQL);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $rows = [];
            while ($row = $result->fetch_assoc()) { $rows[] = $row; }
            $rowCount = count($rows);

            echo "<div class='result-card'>";

            echo "
            <div class='result-toolbar'>
                <div>
                    <div class='result-title'>
                        Results: <span>" . htmlspecialchars($start_date) . " → " . htmlspecialchars($end_date) . "</span>
                    </div>
                    <div class='result-meta'>{$rowCount} Icode(s) found &nbsp;·&nbsp; Values = SUM(AdditionalData) × BOM ratio</div>
                </div>
                <form method='post' action='' style='margin:0;'>
                    <input type='hidden' name='start_date' value='" . htmlspecialchars($start_date) . "'>
                    <input type='hidden' name='end_date'   value='" . htmlspecialchars($end_date)   . "'>
                    <button type='submit' name='action' value='excel' class='btn btn-excel'>
                        <svg viewBox='0 0 24 24'>
                            <path d='M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9
                                     2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5
                                     9H13z'/>
                        </svg>
                        Download Excel
                    </button>
                </form>
            </div>";

            echo "<div class='table-scroll'><table class='main-table'>";

            echo "<thead><tr>";
            echo "<th>Icode</th>";
            echo "<th>Total Qty<br><small style='font-weight:400;font-size:11px;'>(AdditionalData)</small></th>";
            foreach ($columnNames as $col) {
                echo "<th>" . htmlspecialchars($col) . "</th>";
            }
            echo "</tr></thead>";

            echo "<tbody>";

            $sums = array_fill_keys(array_keys($columnNames), 0);
            $grandTotalQty = 0;

            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Icode'])    . "</td>";
                echo "<td>" . number_format($row['TotalQty'], 2) . "</td>";
                $grandTotalQty += $row['TotalQty'];

                for ($i = 'a'; $i <= 'r'; $i++) {
                    $key    = 'Plan_times_' . $i;
                    $mapKey = $i . '_plan';
                    echo "<td>" . number_format($row[$key], 2) . "</td>";
                    $sums[$mapKey] += $row[$key];
                }
                echo "</tr>";
            }

            echo "<tr class='total-row'>";
            echo "<td>Grand Total</td>";
            echo "<td>" . number_format($grandTotalQty, 2) . "</td>";
            foreach ($sums as $s) {
                echo "<td>" . number_format($s, 2) . "</td>";
            }
            echo "</tr>";

            echo "</tbody></table></div>";
            echo "</div>"; // end result-card

        } else {
            echo "<div class='result-card'><div class='alert'>No results found for the selected date range.</div></div>";
        }

        $stmt->close();

        // ── TABLE 2: Icodes in daily_plan_data NOT in bom_new ──────────────
        $stmt2 = $conn->prepare($missingBomSQL);
        $stmt2->bind_param("ss", $start_date, $end_date);
        $stmt2->execute();
        $missingResult = $stmt2->get_result();

        echo "<div class='missing-card'>";
        echo "<div class='missing-header'>
                <span class='missing-badge'>⚠ Action Required</span>
                <div>
                    <div class='missing-title'>Icodes Not Found in BOM New</div>
                    <div class='missing-meta'>
                        These Icodes appear in <strong>daily_plan_data</strong> for the selected
                        period but have <strong>no matching entry</strong> in <strong>bom_new</strong>.
                        They are excluded from the report above.
                    </div>
                </div>
              </div>";

        if ($missingResult->num_rows > 0) {
            $missingRows = [];
            while ($mRow = $missingResult->fetch_assoc()) { $missingRows[] = $mRow; }
            $missingCount = count($missingRows);

            echo "<div class='result-meta' style='margin-bottom:12px;'>
                    {$missingCount} Icode(s) missing from BOM New
                  </div>";

            echo "<div class='table-scroll'><table>";

            echo "<thead><tr>
                    <th style='text-align:left;'>#</th>
                    <th style='text-align:left;'>Icode</th>
                    <th>Total Qty</th>
                    <th>Record Count</th>
                    <th>First Date in Range</th>
                    <th>Last Date in Range</th>
                  </tr></thead>";

            echo "<tbody>";
            $idx = 1;
            foreach ($missingRows as $mRow) {
                echo "<tr>";
                echo "<td style='text-align:left;color:#aaa;'>" . $idx++ . "</td>";
                echo "<td style='text-align:left;font-weight:600;color:var(--red);'>" . htmlspecialchars($mRow['Icode'])    . "</td>";
                echo "<td>" . number_format($mRow['TotalQty'], 2)   . "</td>";
                echo "<td>" . (int)$mRow['RecordCount']             . "</td>";
                echo "<td>" . htmlspecialchars($mRow['FirstDate'])   . "</td>";
                echo "<td>" . htmlspecialchars($mRow['LastDate'])    . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table></div>";

        } else {
            echo "<div class='no-missing'>
                    <svg viewBox='0 0 24 24' width='20' height='20' fill='#27ae60'><path d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z'/></svg>
                    All Icodes in <strong>daily_plan_data</strong> for this date range have matching entries in <strong>bom_new</strong>. No missing mappings.
                  </div>";
        }

        echo "</div>"; // end missing-card

        $stmt2->close();
        $conn->close();
    }
}
?>

</div><!-- /.container -->
</body>
</html>