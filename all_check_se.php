<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Serial Search</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .header {
            background: #343a40;
            color: #FFFFFF;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .search-section {
            background: #FFFFFF;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        
        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input, .form-group select {
            padding: 12px 15px;
            border: 1px solid #CCCCCC;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #F28018;
            box-shadow: 0 0 0 2px rgba(242, 128, 24, 0.2);
        }
        
        .search-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #F28018;
            color: #FFFFFF;
        }
        
        .btn-secondary {
            background: #CCCCCC;
            color: #333333;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        
        .results-section {
            background: #FFFFFF;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        
        .section-title {
            color: #333333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #CCCCCC;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .results-table th,
        .results-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #CCCCCC;
        }
        
        .results-table th {
            background: #343a40;
            color: #FFFFFF;
            font-weight: 600;
        }
        
        .results-table tr:hover {
            background-color: #f0f0f0;
        }
        
        .format-info {
            background: #f0f0f0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .format-info h3 {
            color: #333333;
            margin-bottom: 10px;
        }
        
        .format-info p {
            color: #333333;
            line-height: 1.6;
        }
        
        .no-results {
            text-align: center;
            color: #333333;
            font-style: italic;
            padding: 40px;
        }
        
        .match-badge {
            background: #F28018;
            color: #FFFFFF;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .source-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .source-production {
            background: #F28018;
            color: #FFFFFF;
        }
        
        .source-reverse {
            background: #333333;
            color: #FFFFFF;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-approved {
            background: #28a745;
            color: #FFFFFF;
        }
        
        .status-pending {
            background: #F28018;
            color: #FFFFFF;
        }
        
        .status-rejected {
            background: red;
            color: #FFFFFF;
        }
        
        .status-partial {
            background: #ffc107;
            color: #333333;
        }
        
        .status-disabled {
            background: #333333;
            color: #FFFFFF;
        }
        
        .rejected-row {
            background-color: #ffe6e6 !important;
        }
        
        .approved-row {
            background-color: #e6f4e6 !important;
        }
        
        .pending-row {
            background-color: #fff3e6 !important;
        }
        
        .partial-row {
            background-color: #fffbe6 !important;
        }
        
        .disabled-row {
            background-color: #f0f0f0 !important;
            opacity: 0.7;
        }
        
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .search-buttons {
                flex-direction: column;
            }
            
            .results-table {
                font-size: 14px;
            }
            
            .results-table th,
            .results-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Production Serial Search</h1>
            <p>Search and manage tire production serial numbers - Production Only Mode</p>
        </div>
        
        <div class="content">
            <div class="format-info">
                <h3>📋 Serial Number Format & Status Legend</h3>
                <p><strong>Format:</strong> MMYYYYNNNNN or MMYY-NNNNN</p>
                <p><strong>Example:</strong> 062503395 displays as 062025-03395 = June 2025, Tire #03395</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-approved">Approved</span> = Found in both dwork_ser & stock_erp | 
                    <span class="status-badge status-pending">Pending</span> = Production date not found in stock_erp |
                    <span class="status-badge status-rejected">Rejected</span> = Not found in both dwork_ser AND stock_erp |
                    <span class="status-badge status-disabled">Disabled</span> = Reverse serial data (not processed)
                </p>
                <p><strong>Note:</strong> Production serials are validated based on presence in validation tables. Reverse serial entries are shown but marked as disabled.</p>
            </div>
            
            <div class="search-section">
                <h2>🔎 Search Serial Numbers</h2>
                <form method="GET" class="search-form" id="search-form">
                    <div class="form-group">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" id="serial_number" name="serial_number" 
                               placeholder="e.g., 062503395 or 062025-03395" 
                               value="<?php echo htmlspecialchars($_GET['serial_number'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="icode">Item Code</label>
                        <input type="text" id="icode" name="icode" 
                               placeholder="Enter item code" 
                               value="<?php echo htmlspecialchars($_GET['icode'] ?? ''); ?>">
                    </div>
                </form>
                
                <div class="search-buttons">
                    <button type="submit" class="btn btn-primary" onclick="searchSerials()">🔍 Search</button>
                    <button type="button" class="btn btn-secondary" onclick="clearForm()">🗑️ Clear</button>
                </div>
            </div>
            
            <div class="results-section">
                <h2 class="section-title">📊 Search Results <span class="match-badge">Production Serial Validation</span></h2>
                <div id="results-container">
                    <?php
                    // Function to format serial number for display
                    function formatSerialNumber($serial) {
                        // Remove any existing dashes
                        $clean = str_replace('-', '', $serial);
                        
                        // Check if it's the expected format (9 digits: MMYYNNNNN or 11 digits: MMYYYYNNNNN)
                        if (preg_match('/^(\d{2})(\d{2})(\d{5})$/', $clean, $matches)) {
                            // Format: MMYYNNNNN -> MM20YY-NNNNN
                            return $matches[1] . '20' . $matches[2] . '-' . $matches[3];
                        } elseif (preg_match('/^(\d{2})(\d{4})(\d{5})$/', $clean, $matches)) {
                            // Format: MMYYYYNNNNN -> MMYYYY-NNNNN
                            return $matches[1] . $matches[2] . '-' . $matches[3];
                        }
                        
                        // If doesn't match expected format, return as is
                        return $serial;
                    }
                    
                    // Function to check if two serial numbers match (considering format variations)
                    function serialsMatch($serial1, $serial2) {
                        $clean1 = str_replace('-', '', $serial1);
                        $clean2 = str_replace('-', '', $serial2);
                        
                        // Direct match
                        if ($clean1 === $clean2) return true;
                        
                        // Convert 9-digit to 11-digit format for comparison
                        if (strlen($clean1) == 9 && preg_match('/^(\d{2})(\d{2})(\d{5})$/', $clean1, $matches1)) {
                            $expanded1 = $matches1[1] . '20' . $matches1[2] . $matches1[3];
                            if ($expanded1 === $clean2) return true;
                        }
                        
                        if (strlen($clean2) == 9 && preg_match('/^(\d{2})(\d{2})(\d{5})$/', $clean2, $matches2)) {
                            $expanded2 = $matches2[1] . '20' . $matches2[2] . $matches2[3];
                            if ($clean1 === $expanded2) return true;
                        }
                        
                        // Convert 11-digit to 9-digit format for comparison
                        if (strlen($clean1) == 11 && preg_match('/^(\d{2})(\d{4})(\d{5})$/', $clean1, $matches1)) {
                            $shortYear1 = substr($matches1[2], -2);
                            $compressed1 = $matches1[1] . $shortYear1 . $matches1[3];
                            if ($compressed1 === $clean2) return true;
                        }
                        
                        if (strlen($clean2) == 11 && preg_match('/^(\d{2})(\d{4})(\d{5})$/', $clean2, $matches2)) {
                            $shortYear2 = substr($matches2[2], -2);
                            $compressed2 = $matches2[1] . $shortYear2 . $matches2[3];
                            if ($clean1 === $compressed2) return true;
                        }
                        
                        return false;
                    }
                    
                    // Function to check if production date exists in stock_erp for any serial
                    function productionDateExistsInStock($productionDate, $allStockResults) {
                        if (empty($productionDate)) return false;
                        
                        foreach ($allStockResults as $stockRow) {
                            $stockDate = $stockRow['date'] ?? null;
                            if (!empty($stockDate)) {
                                // Convert both dates to Y-m-d format for comparison
                                $prodTimestamp = strtotime($productionDate);
                                $stockTimestamp = strtotime($stockDate);
                                
                                if ($prodTimestamp !== false && $stockTimestamp !== false) {
                                    if (date('Y-m-d', $prodTimestamp) === date('Y-m-d', $stockTimestamp)) {
                                        return true;
                                    }
                                }
                            }
                        }
                        
                        return false;
                    }
                    
                    // Database connection (modify with your credentials)
                    $host = 'localhost';
                    $dbname = 'planatir_task_managemen';
                    $username = 'planatir_task_managemen';
                    $password = 'Bishan@1919';
                    
                    $hasSearch = !empty($_GET['serial_number']) || !empty($_GET['icode']);
                    
                    try {
                        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        // Function to build search conditions and parameters
                        function buildSearchConditions($serialInput, $icodeInput) {
                            $conditions = [];
                            $params = [];
                            
                            if (!empty($serialInput)) {
                                $searchSerial = str_replace('-', '', $serialInput);
                                
                                // Handle both formats: 062503395 and 062025-03395
                                if (strlen($searchSerial) == 9) {
                                    // Input is 9 digits (MMYYNNNNN) - search as is and converted format
                                    $conditions[] = "(REPLACE(serial_number, '-', '') = :serial_number OR REPLACE(formatted_serial, '-', '') = :serial_number_formatted)";
                                    $params['serial_number'] = $searchSerial;
                                    $params['serial_number_formatted'] = $searchSerial;
                                } elseif (strlen($searchSerial) == 11) {
                                    // Input is 11 digits (MMYYYYNNNNN) - convert to 9 digit format for search
                                    if (preg_match('/^(\d{2})(\d{4})(\d{5})$/', $searchSerial, $matches)) {
                                        $shortYear = substr($matches[2], -2); // Get last 2 digits of year
                                        $searchSerial9 = $matches[1] . $shortYear . $matches[3]; // MMYYNNNNN
                                        
                                        $conditions[] = "(REPLACE(serial_number, '-', '') = :serial_number OR REPLACE(formatted_serial, '-', '') = :serial_number_formatted OR REPLACE(serial_number, '-', '') = :serial_number_9)";
                                        $params['serial_number'] = $searchSerial;
                                        $params['serial_number_formatted'] = $searchSerial;
                                        $params['serial_number_9'] = $searchSerial9;
                                    } else {
                                        $conditions[] = "(REPLACE(serial_number, '-', '') = :serial_number OR REPLACE(formatted_serial, '-', '') = :serial_number_formatted)";
                                        $params['serial_number'] = $searchSerial;
                                        $params['serial_number_formatted'] = $searchSerial;
                                    }
                                } else {
                                    // Fallback for other lengths
                                    $conditions[] = "(REPLACE(serial_number, '-', '') = :serial_number OR REPLACE(formatted_serial, '-', '') = :serial_number_formatted)";
                                    $params['serial_number'] = $searchSerial;
                                    $params['serial_number_formatted'] = $searchSerial;
                                }
                            }
                            
                            if (!empty($icodeInput)) {
                                $conditions[] = "icode LIKE :icode";
                                $params['icode'] = "%" . $icodeInput . "%";
                            }
                            
                            return [$conditions, $params];
                        }
                        
                        $allResults = [];
                        
                        // Query production_serial table
                        list($conditions, $params) = buildSearchConditions($_GET['serial_number'] ?? '', $_GET['icode'] ?? '');
                        
                        $productionSql = "SELECT *, 'production' as source FROM production_serial";
                        if (!empty($conditions)) {
                            $productionSql .= " WHERE " . implode(" AND ", $conditions);
                        }
                        $productionSql .= " ORDER BY id DESC LIMIT 100";
                        
                        $stmt = $pdo->prepare($productionSql);
                        $stmt->execute($params);
                        $productionResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Query reverse_serial table with same conditions (but mark as disabled)
                        $reverseSql = "SELECT *, 'reverse' as source FROM reverse_serial";
                        if (!empty($conditions)) {
                            $reverseSql .= " WHERE " . implode(" AND ", $conditions);
                        }
                        $reverseSql .= " ORDER BY id DESC LIMIT 100";
                        
                        $stmt = $pdo->prepare($reverseSql);
                        $stmt->execute($params);
                        $reverseResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Combine results
                        $allResults = array_merge($productionResults, $reverseResults);
                        
                        // Sort combined results by id DESC
                        usort($allResults, function($a, $b) {
                            return $b['id'] - $a['id'];
                        });
                        
                        // Get all dwork_ser records to match against
                        $dworkSql = "SELECT * FROM dwork_ser ORDER BY id DESC";
                        $dworkStmt = $pdo->prepare($dworkSql);
                        $dworkStmt->execute();
                        $allDworkResults = $dworkStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Get all stock_erp records to match against
                        $stockSql = "SELECT * FROM stock_erp ORDER BY id DESC";
                        $stockStmt = $pdo->prepare($stockSql);
                        $stockStmt->execute();
                        $allStockResults = $stockStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if ($allResults) {
                            echo "<table class='results-table'>";
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>Source</th>";
                            echo "<th>Serial Number</th>";
                            echo "<th>Item Code</th>";
                            echo "<th>Production Date</th>";
                            echo "<th>Stock ERP Date</th>";
                            echo "<th>Dispatch Order Reference</th>";
                            echo "<th>Dispatch Order ERP</th>";
                            echo "<th>Status</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            
                            $approvedCount = 0;
                            $pendingCount = 0;
                            $rejectedCount = 0;
                            $disabledCount = 0;
                            
                            foreach ($allResults as $row) {
                                // Format serial number for display
                                $displaySerial = formatSerialNumber($row['serial_number']);
                                
                                // Check if this is from reverse_serial - if so, mark as disabled
                                if ($row['source'] === 'reverse') {
                                    $status = 'disabled';
                                    $statusClass = 'status-disabled';
                                    $rowClass = 'disabled-row';
                                    $disabledCount++;
                                    
                                    echo "<tr class='$rowClass'>";
                                    
                                    // Source column with badge
                                    echo "<td><span class='source-badge source-reverse'>Rev</span></td>";
                                    echo "<td>" . htmlspecialchars($displaySerial) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['icode'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['production_date'] ?? 'N/A') . "</td>";
                                    echo "<td style='color: #999; font-style: italic;'>Not Processed</td>";
                                    echo "<td style='color: #999; font-style: italic;'>Not Processed</td>";
                                    echo "<td style='color: #999; font-style: italic;'>Not Processed</td>";
                                    echo "<td><span class='status-badge $statusClass'>$status</span>";
                                    echo "<br><small style='color: #666;'>Reverse serial data disabled</small>";
                                    echo "</td>";
                                    echo "</tr>";
                                    
                                    continue;
                                }
                                
                                // Only process production_serial records for validation
                                // Look for matching record in dwork_ser
                                $matchingDwork = null;
                                foreach ($allDworkResults as $dworkRow) {
                                    if (serialsMatch($row['serial_number'], $dworkRow['serial_number'])) {
                                        $matchingDwork = $dworkRow;
                                        break;
                                    }
                                }
                                
                                // Look for matching record in stock_erp
                                $matchingStock = null;
                                foreach ($allStockResults as $stockRow) {
                                    if (serialsMatch($row['serial_number'], $stockRow['serial_number']) || 
                                        serialsMatch($row['serial_number'], $stockRow['prev_serial'])) {
                                        $matchingStock = $stockRow;
                                        break;
                                    }
                                }
                                
                                // NEW VALIDATION LOGIC
                                $productionDate = $row['production_date'] ?? null;
                                
                                // Check if production date exists in stock_erp (for any serial)
                                $productionDateInStock = productionDateExistsInStock($productionDate, $allStockResults);
                                
                                // Updated validation rules:
                                // 1. PENDING: Production date not found in stock_erp
                                // 2. REJECTED: Not found in both dwork_ser AND stock_erp
                                // 3. APPROVED: Found in both dwork_ser AND stock_erp
                                
                                if (!$productionDateInStock) {
                                    // Production date doesn't exist in stock_erp - PENDING
                                    $status = 'pending';
                                    $statusClass = 'status-pending';
                                    $rowClass = 'pending-row';
                                    $pendingCount++;
                                } elseif (!$matchingDwork && !$matchingStock) {
                                    // Not found in both tables - REJECTED
                                    $status = 'rejected';
                                    $statusClass = 'status-rejected';
                                    $rowClass = 'rejected-row';
                                    $rejectedCount++;
                                } else {
                                    // Found in at least one table and production date exists in stock_erp - APPROVED
                                    $status = 'approved';
                                    $statusClass = 'status-approved';
                                    $rowClass = 'approved-row';
                                    $approvedCount++;
                                }
                                
                                echo "<tr class='$rowClass'>";
                                
                                // Source column with badge
                                echo "<td><span class='source-badge source-production'>Prod</span></td>";
                                
                                echo "<td>" . htmlspecialchars($displaySerial) . "</td>";
                                echo "<td>" . htmlspecialchars($row['icode'] ?? 'N/A') . "</td>";
                                echo "<td>" . htmlspecialchars($productionDate ?? 'N/A') . "</td>";
                                
                                // Stock ERP Date column
                                if ($matchingStock) {
                                    $stockDate = $matchingStock['date'] ?? 'N/A';
                                    echo "<td><strong style='color: #28a745;'>" . htmlspecialchars($stockDate) . "</strong></td>";
                                } else {
                                    echo "<td style='color: #999; font-style: italic;'>Not Found</td>";
                                }
                                
                                if ($matchingDwork) {
                                    echo "<td><strong style='color: #28a745;'>" . htmlspecialchars($matchingDwork['ref'] ?? 'N/A') . "</strong></td>";
                                    echo "<td><strong style='color: #28a745;'>" . htmlspecialchars($matchingDwork['erp'] ?? 'N/A') . "</strong></td>";
                                } else {
                                    echo "<td style='color: #999; font-style: italic;'>Not Found</td>";
                                    echo "<td style='color: #999; font-style: italic;'>Not Found</td>";
                                }
                                
                                // Status column with detailed info
                                echo "<td><span class='status-badge $statusClass'>$status</span>";
                                if ($status === 'rejected') {
                                    echo "<br><small style='color: red;'>Not found in both dwork_ser AND stock_erp</small>";
                                } elseif ($status === 'pending') {
                                    echo "<br><small style='color: #F28018;'>Production date not found in stock_erp</small>";
                                } elseif ($status === 'approved') {
                                    $foundIn = [];
                                    if ($matchingDwork) $foundIn[] = 'dwork_ser';
                                    if ($matchingStock) $foundIn[] = 'stock_erp';
                                    echo "<br><small style='color: #28a745;'>Found in: " . implode(', ', $foundIn);
                                    echo " | Production date exists in stock_erp";
                                    echo "</small>";
                                }
                                echo "</td>";
                                
                                echo "</tr>";
                            }
                            
                            echo "</tbody>";
                            echo "</table>";
                            
                            $productionCount = count($productionResults);
                            $reverseCount = count($reverseResults);
                            $totalCount = count($allResults);
                            
                            $statusMessage = $hasSearch ? 
                                "Showing $totalCount search result(s) ($productionCount from production_serial, $reverseCount from reverse_serial)" :
                                "Showing all $totalCount records ($productionCount from production_serial, $reverseCount from reverse_serial) (limited to 100 per table)";
                            
                            echo "<div style='margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 6px;'>";
                            echo "<p style='color: #333333; margin-bottom: 10px;'>$statusMessage</p>";
                            echo "<p style='color: #333333; font-weight: 600;'>Validation Summary (Production Serial Only):</p>";
                            echo "<p style='color: #333333;'>";
                            echo "Approved: <span style='color: #28a745;'>$approvedCount</span> | ";
                            echo "Pending: <span style='color: #F28018;'>$pendingCount</span> | ";
                            echo "Rejected: <span style='color: red;'>$rejectedCount</span> | ";
                            echo "Disabled (Reverse): <span style='color: #333333;'>$disabledCount</span>";
                            echo "</p>";
                            echo "</div>";
                            
                        } else {
                            echo "<div class='no-results'>";
                            echo $hasSearch ? "No results found for the specified criteria." : "No records available. Please perform a search or check the database.";
                            echo "</div>";
                        }
                        
                    } catch (PDOException $e) {
                        echo "<div class='no-results' style='color: red;'>";
                        echo "Database Error: " . htmlspecialchars($e->getMessage());
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function searchSerials() {
            document.getElementById('search-form').submit();
        }

        function clearForm() {
            document.getElementById('search-form').reset();
            window.location.href = window.location.pathname;
        }
    </script>
</body>
</html>