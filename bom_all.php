<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOM Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:    #E68415;
            --dark:       #502904;
            --bg:         #F4F7F9;
            --text:       #2C3E50;
            --accent:     #27AE60;
            --white:      #FFFFFF;
            --border:     #e0e6ed;
            --row-hover:  rgba(230,132,21,0.06);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Mono', monospace;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .dashboard {
            max-width: 1600px;
            margin: 0 auto;
            padding: 24px;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: var(--white);
            text-align: center;
            padding: 32px 24px;
            border-radius: 14px;
            margin-bottom: 28px;
            box-shadow: 0 12px 28px rgba(80,41,4,0.25);
        }

        .header h1 {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: -1px;
        }

        .header p {
            opacity: 0.8;
            font-size: 0.85rem;
            margin-top: 6px;
        }

        /* ── Search ── */
        .search-container {
            background: var(--white);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.07);
            padding: 28px;
            margin-bottom: 28px;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .search-group {
            flex: 1 1 200px;
        }

        .search-label {
            display: block;
            margin-bottom: 7px;
            font-weight: 500;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--dark);
        }

        .search-input,
        .search-select {
            width: 100%;
            padding: 11px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-family: 'DM Mono', monospace;
            font-size: 0.9rem;
            color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }

        .search-input:focus,
        .search-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(230,132,21,0.15);
            background: var(--white);
        }

        .search-btn {
            padding: 11px 28px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.03em;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            white-space: nowrap;
        }

        .search-btn:hover {
            background: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(80,41,4,0.3);
        }

        .search-btn:active {
            transform: translateY(0);
        }

        /* ── Table wrapper ── */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.07);
        }

        /* ── Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            font-size: 0.82rem;
            white-space: nowrap;
        }

        .data-table thead tr {
            background: var(--dark);
            color: var(--white);
        }

        .data-table th {
            padding: 13px 14px;
            text-align: left;
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            position: sticky;
            top: 0;
            z-index: 10;
            border-right: 1px solid rgba(255,255,255,0.08);
        }

        .data-table th:last-child { border-right: none; }

        /* Colour-coded header groups */
        .data-table th.col-id     { background: #3d2003; }
        .data-table th.col-info   { background: #502904; }
        .data-table th.col-comp   { background: #7a3f08; }
        .data-table th.col-meta   { background: #9c5210; }

        .data-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border);
            border-right: 1px solid #f0f0f0;
            color: var(--text);
            transition: background 0.15s;
        }

        .data-table td:last-child { border-right: none; }

        .data-table tbody tr:hover td {
            background: var(--row-hover);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Zebra stripe */
        .data-table tbody tr:nth-child(even) td {
            background: #fafbfc;
        }
        .data-table tbody tr:nth-child(even):hover td {
            background: var(--row-hover);
        }

        /* ── No results ── */
        .no-results {
            text-align: center;
            padding: 48px 30px;
            background: var(--white);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.07);
            color: #888;
            font-size: 0.95rem;
        }

        .no-results span {
            display: block;
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        /* ── Result count badge ── */
        .result-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 0.82rem;
            color: #666;
        }

        .result-badge {
            background: var(--primary);
            color: var(--white);
            padding: 3px 10px;
            border-radius: 20px;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.78rem;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .search-group { flex: 1 1 100%; }
            .search-btn   { width: 100%; }
            .header h1    { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
<div class="dashboard">

    <!-- Header -->
    <div class="header">
        <h1>BOM Management System</h1>
        <p>Bill of Materials · Compound Weight Reference</p>
    </div>

    <!-- Search -->
    <div class="search-container">
        <form method="GET" action="" class="search-form">

            <div class="search-group">
                <label for="search_icode" class="search-label">Search by icode</label>
                <input
                    type="text"
                    id="search_icode"
                    name="search_icode"
                    class="search-input"
                    placeholder="Enter icode…"
                    value="<?php echo isset($_GET['search_icode']) ? htmlspecialchars($_GET['search_icode']) : ''; ?>"
                >
            </div>

            <div class="search-group">
                <label for="search_tsize" class="search-label">Search by t_size</label>
                <select id="search_tsize" name="search_tsize" class="search-select">
                    <option value="">Select t_size…</option>
                    <?php
                    $servername = "localhost";
                    $username   = "planatir_task_managemen";
                    $password   = "Bishan@1919";
                    $database   = "planatir_task_managemen";

                    $conn = new mysqli($servername, $username, $password, $database);
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql_sizes    = "SELECT DISTINCT t_size FROM bom_new ORDER BY t_size";
                    $result_sizes = $conn->query($sql_sizes);
                    while ($row_size = $result_sizes->fetch_assoc()) {
                        $selected = (isset($_GET['search_tsize']) && $_GET['search_tsize'] == $row_size['t_size']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row_size['t_size']) . '" ' . $selected . '>'
                            . htmlspecialchars($row_size['t_size']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="search-btn">&#128269; Search</button>

        </form>
    </div>

    <?php
    /* ── Second connection for results ── */
    $conn2 = new mysqli($servername, $username, $password, $database);
    if ($conn2->connect_error) {
        die("Connection failed: " . $conn2->connect_error);
    }

    $sql = "SELECT * FROM `bom_new` WHERE 1=1";

    if (!empty($_GET['search_icode'])) {
        $si   = $conn2->real_escape_string($_GET['search_icode']);
        $sql .= " AND icode LIKE '%$si%'";
    }
    if (!empty($_GET['search_tsize'])) {
        $st   = $conn2->real_escape_string($_GET['search_tsize']);
        $sql .= " AND t_size = '$st'";
    }

    $result = $conn2->query($sql);

    if ($result && $result->num_rows > 0):
    ?>

    <div class="result-meta">
        <span class="result-badge"><?php echo $result->num_rows; ?> record<?php echo $result->num_rows !== 1 ? 's' : ''; ?></span>
        found
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <!-- Identity columns -->
                    <th class="col-id">ID</th>
                    <th class="col-info">Item</th>
                    <th class="col-info">icode</th>
                    <th class="col-info">t_size</th>
                    <th class="col-info">Item Description</th>

                    <!-- Compound columns a–w -->
                    <th class="col-comp">PC-ATPRS</th>          <!-- a -->
                    <th class="col-comp">CC-PC-ATPRS</th>       <!-- b -->
                    <th class="col-comp">B-ATS 15</th>          <!-- c -->
                    <th class="col-comp">CC-B-ATS 15</th>       <!-- d -->
                    <th class="col-comp">B-BNS 25</th>          <!-- e -->
                    <th class="col-comp">BG-BLS 12</th>         <!-- f -->
                    <th class="col-comp">CG - BS 901</th>       <!-- g -->
                    <th class="col-comp">C - SMS 501</th>       <!-- h -->
                    <th class="col-comp">C-ATS 20</th>          <!-- i -->
                    <th class="col-comp">C-ATS 20-H</th>        <!-- j -->
                    <th class="col-comp">C-SMS 703</th>         <!-- k -->
                    <th class="col-comp">C-ATS 20(O)</th>       <!-- l -->
                    <th class="col-comp">T - TRS 102</th>       <!-- m -->
                    <th class="col-comp">T-ATNM S</th>          <!-- n -->
                    <th class="col-comp">CC-T-ATNMS</th>        <!-- o -->
                    <th class="col-comp">T-ATS 30(O)</th>       <!-- p -->
                    <th class="col-comp">T-ATS 35</th>          <!-- q -->
                    <th class="col-comp">T-ATS 35-H</th>        <!-- r -->
                    <th class="col-comp">T-KS 40</th>           <!-- s -->
                    <th class="col-comp">T-TRNMS 402</th>       <!-- t -->
                    <th class="col-comp">T-TRNMS 402G</th>      <!-- u -->
                    <th class="col-comp">T-TRS 103</th>         <!-- v -->
                    <th class="col-comp">WC0001</th>            <!-- w -->

                    <!-- Summary / meta columns -->
                    <th class="col-meta">Grand Total Compound Weight</th>
                    <th class="col-meta">Color</th>
                    <th class="col-meta">Brand</th>
                    <th class="col-meta">Green Tire Weight</th>
                    <th class="col-meta">Profile/Bead Weight</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['Item']); ?></td>
                    <td><?php echo htmlspecialchars($row['icode']); ?></td>
                    <td><?php echo htmlspecialchars($row['t_size']); ?></td>
                    <td><?php echo htmlspecialchars($row['Item Description']); ?></td>

                    <!-- a – w -->
                    <td><?php echo htmlspecialchars($row['a']); ?></td>
                    <td><?php echo htmlspecialchars($row['b']); ?></td>
                    <td><?php echo htmlspecialchars($row['c']); ?></td>
                    <td><?php echo htmlspecialchars($row['d']); ?></td>
                    <td><?php echo htmlspecialchars($row['e']); ?></td>
                    <td><?php echo htmlspecialchars($row['f']); ?></td>
                    <td><?php echo htmlspecialchars($row['g']); ?></td>
                    <td><?php echo htmlspecialchars($row['h']); ?></td>
                    <td><?php echo htmlspecialchars($row['i']); ?></td>
                    <td><?php echo htmlspecialchars($row['j']); ?></td>
                    <td><?php echo htmlspecialchars($row['k']); ?></td>
                    <td><?php echo htmlspecialchars($row['l']); ?></td>
                    <td><?php echo htmlspecialchars($row['m']); ?></td>
                    <td><?php echo htmlspecialchars($row['n']); ?></td>
                    <td><?php echo htmlspecialchars($row['o']); ?></td>
                    <td><?php echo htmlspecialchars($row['p']); ?></td>
                    <td><?php echo htmlspecialchars($row['q']); ?></td>
                    <td><?php echo htmlspecialchars($row['r']); ?></td>
                    <td><?php echo htmlspecialchars($row['s']); ?></td>
                    <td><?php echo htmlspecialchars($row['t']); ?></td>
                    <td><?php echo htmlspecialchars($row['u']); ?></td>
                    <td><?php echo htmlspecialchars($row['v']); ?></td>
                    <td><?php echo htmlspecialchars($row['w']); ?></td>

                    <!-- Summary -->
                    <td><?php echo htmlspecialchars($row['Grand Totalcompound weight']); ?></td>
                    <td><?php echo htmlspecialchars($row['Color']); ?></td>
                    <td><?php echo htmlspecialchars($row['Brand']); ?></td>
                    <td><?php echo htmlspecialchars($row['Green Tire weight']); ?></td>
                    <td><?php echo htmlspecialchars($row['PBweight']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <div class="no-results">
        <span>🔍</span>
        No records found. Try adjusting your search criteria.
    </div>
    <?php endif; ?>

    <?php
    $conn->close();
    $conn2->close();
    ?>

</div>
</body>
</html>