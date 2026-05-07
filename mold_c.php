<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Plan Data - Last 10 Dates per Cavity</title>
    
</head>
<body>
    <div class="container">
        <h1>📊 Daily Plan Data - Last 10 Dates per Cavity</h1>
        
        <?php
        // Database configuration
        $servername = "localhost"; // Change as needed
        $username = "planatir_task_managemen"; // Change to your DB username
        $password = "Bishan@1919"; // Change to your DB password
        $dbname = "planatir_task_managemen"; // Change to your DB name
        



        try {
            // Create connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // SQL Query
            $sql = "
                SELECT 
                    rd.CavityName,
                    rd.Date,
                    rd.Shift,
                    rd.Icode,
                    rd.MoldName,
                    rd.Plan,
                    rd.LossReason,
                    rd.Remark,
                    tp.tobe as ToBePlan
                FROM (
                    SELECT 
                        *,
                        ROW_NUMBER() OVER (
                            PARTITION BY CavityName 
                            ORDER BY Date DESC, ID DESC
                        ) as rn
                    FROM daily_plan_data
                    WHERE Date IS NOT NULL
                ) rd
                LEFT JOIN tobeplan1 tp ON rd.Icode = tp.Icode AND tp.tobe > 0
                WHERE rd.rn <= 10
                ORDER BY rd.CavityName, rd.Date DESC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($results) {
                // Calculate statistics
                $totalRecords = count($results);
                $uniqueCavities = count(array_unique(array_column($results, 'CavityName')));
                $totalPlan = array_sum(array_column($results, 'Plan'));
                $avgPlan = $totalPlan > 0 ? round($totalPlan / $totalRecords, 2) : 0;
                
                echo "
                <div class='stats'>
                    <div class='stat-card'>
                        <div class='stat-number'>$totalRecords</div>
                        <div>Total Records</div>
                    </div>
                    <div class='stat-card'>
                        <div class='stat-number'>$uniqueCavities</div>
                        <div>Unique Cavities</div>
                    </div>
                    <div class='stat-card'>
                        <div class='stat-number'>$totalPlan</div>
                        <div>Total Plan</div>
                    </div>
                    <div class='stat-card'>
                        <div class='stat-number'>$avgPlan</div>
                        <div>Avg Plan</div>
                    </div>
                </div>
                ";
                
                echo "<div class='table-container'>";
                echo "<table>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Cavity Name</th>";
                echo "<th>Date</th>";
                echo "<th>Shift</th>";
                echo "<th>I-Code</th>";
                echo "<th>Mold Name</th>";
                echo "<th>Plan</th>";
                echo "<th>To Be Plan</th>";
                echo "<th>Loss Reason</th>";
                echo "<th>Remark</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                
                $currentCavity = '';
                foreach ($results as $row) {
                    $isNewCavity = ($currentCavity !== $row['CavityName']);
                    $currentCavity = $row['CavityName'];
                    
                    $rowClass = $isNewCavity ? 'cavity-group' : '';
                    
                    echo "<tr class='$rowClass'>";
                    echo "<td class='cavity-name'>" . htmlspecialchars($row['CavityName'] ?? '') . "</td>";
                    echo "<td class='date'>" . htmlspecialchars($row['Date'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Shift'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Icode'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['MoldName'] ?? '') . "</td>";
                    echo "<td class='plan-value'>" . htmlspecialchars($row['Plan'] ?? '0') . "</td>";
                    echo "<td class='tobe-value'>" . 
                         ($row['ToBePlan'] ? htmlspecialchars($row['ToBePlan']) : '-') . "</td>";
                    echo "<td>" . htmlspecialchars($row['LossReason'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Remark'] ?? '') . "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                
            } else {
                echo "<div class='no-data'>No data found matching the criteria.</div>";
            }
            
        } catch(PDOException $e) {
            echo "<div class='error'>";
            echo "<h3>Database Error:</h3>";
            echo "<p>Connection failed: " . $e->getMessage() . "</p>";
            echo "<p><strong>Please check:</strong></p>";
            echo "<ul style='text-align: left; display: inline-block;'>";
            echo "<li>Database connection settings</li>";
            echo "<li>Table names and structure</li>";
            echo "<li>Database permissions</li>";
            echo "</ul>";
            echo "</div>";
        }
        ?>
        
        <script>
            // Add some interactive features
            document.addEventListener('DOMContentLoaded', function() {
                // Add click-to-highlight functionality
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    row.addEventListener('click', function() {
                        // Remove previous highlights
                        rows.forEach(r => r.style.backgroundColor = '');
                        // Highlight clicked row
                        this.style.backgroundColor = '#fff3cd';
                    });
                });
                
                // Add loading animation
                const table = document.querySelector('table');
                if (table) {
                    table.style.opacity = '0';
                    table.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        table.style.transition = 'all 0.5s ease';
                        table.style.opacity = '1';
                        table.style.transform = 'translateY(0)';
                    }, 100);
                }
            });
        </script>
    </div>
</body>
</html>