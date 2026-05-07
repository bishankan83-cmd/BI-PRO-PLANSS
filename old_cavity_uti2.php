<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to get the last CavityName, corresponding mold_ids, date, and description from tire_details
    $sql = "
        WITH RankedCavities AS (
            SELECT 
                ID,
                Icode,
                CavityName,
                Date,  -- Assume there's a Date column in daily_plan_data
                ROW_NUMBER() OVER (PARTITION BY Icode ORDER BY ID DESC) AS rn
            FROM 
                daily_plan_data
        ),
        IcodesNotInDailyPlan AS (
            SELECT DISTINCT 
                dp1.Icode,
                dp1.CavityName,
                dp1.Date
            FROM 
                daily_plan_data1 dp1
            WHERE 
                dp1.Icode NOT IN (SELECT rc.Icode FROM RankedCavities rc WHERE rc.rn = 1)
        )
        SELECT 
            rc.Icode,
            rc.CavityName,
            rc.Date,  -- Include the date in the results
            ml.mold_size,
            ml.mold_id,  -- Get each mold_id individually
            td.description AS tire_description -- Select the description from tire_details
        FROM 
            RankedCavities rc
        JOIN 
            mold_list ml ON rc.Icode = ml.Icode
        LEFT JOIN 
            tire_details td ON rc.Icode = td.Icode -- Join with tire_details to get the description
        WHERE 
            rc.rn = 1

        UNION ALL

        SELECT 
            ic.Icode,
            ic.CavityName,
            ic.Date,
            NULL AS mold_size,
            NULL AS mold_id,
            NULL AS tire_description -- No description for Icodes not in daily_plan
        FROM 
            IcodesNotInDailyPlan ic
    ";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display results with styling
    echo "<style>
            /* CSS styles here */
        </style>";

    // Container for the output
    echo "<div class='container'>";
    echo "<h1>Last Press Utilization</h1>"; // Title of the output

    if ($results) {
        echo "<div class='table-responsive'>"; // Responsive table container
        echo "<table>";
        echo "<tr><th>Icode</th><th>Tire Description</th><th>Cavity Name</th><th>Date</th><th>Mold Size</th><th>Mold IDs</th></tr>"; // Added Tire Description
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Icode']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tire_description'] ?? 'N/A') . "</td>"; // Handle null tire description
            echo "<td>" . htmlspecialchars($row['CavityName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Date']) . "</td>"; // Displaying the date
            echo "<td>" . htmlspecialchars($row['mold_size'] ?? 'N/A') . "</td>"; // Handle null mold_size
            
            // Display each mold_id on a new line
            $mold_ids = htmlspecialchars($row['mold_id'] ?? 'N/A');
            echo "<td style='white-space: pre-wrap;'>" . str_replace(",", "\n", $mold_ids) . "</td>";
            
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>"; // End of the table-responsive
    } else {
        echo "<p class='no-results'>No results found.</p>"; // Improved message
    }
    echo "</div>"; // End of the container

} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage()); // Sanitizing error messages for safety
}

// Close the connection
$pdo = null;
?>
