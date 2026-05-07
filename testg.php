<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a new PDO instance
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Your SQL query with the calculated column
    $sql = "SELECT
                rs.id,
                rs.icode,
                rs.t_size,
                rs.brand,
                rs.col,
                rs.rim,
                rs.gweight,
                rs.cstock,
                td.greenweight AS tire_gweight,
                (td.greenweight * rs.cstock) AS calculated_column
            FROM
                realstock rs
            JOIN
                tire_details td ON rs.icode = td.icode";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all the results as an associative array
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the results in a table
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>ICode</th><th>T Size</th><th>Brand</th><th>Color</th><th>Rim</th><th>GWeight</th><th>CStock</th><th>Tire GWeight</th><th> Total Tire GWeight</th></tr>";
    
    $totalCalculatedColumn = 0;

    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['icode']}</td>";
        echo "<td>{$row['t_size']}</td>";
        echo "<td>{$row['brand']}</td>";
        echo "<td>{$row['col']}</td>";
        echo "<td>{$row['rim']}</td>";
        echo "<td>{$row['gweight']}</td>";
        echo "<td>{$row['cstock']}</td>";
        echo "<td>{$row['tire_gweight']}</td>";
        echo "<td>{$row['calculated_column']}</td>";
        echo "</tr>";

        // Accumulate the total of the calculated column
        $totalCalculatedColumn += $row['calculated_column'];
    }

    echo "</table>";

    // Display the total of the calculated column
    echo "<p>Total Green Tire Weight: $totalCalculatedColumn</p>";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Close the database connection
$conn = null;
?>
