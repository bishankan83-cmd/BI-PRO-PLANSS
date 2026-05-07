<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// ✅ Step 1: Delete all existing data from 'filtered_data' table
$deleteSQL = "DELETE FROM filtered_data";
if (!$conn->query($deleteSQL)) {
    die("Error deleting data: " . $conn->error);
}
echo "✅ All data deleted from 'filtered_data'.<br>";

// ✅ Step 2: Create Table (If Not Exists)
$createTableSQL = "
CREATE TABLE IF NOT EXISTS filtered_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icode VARCHAR(50),
    c_cat INT
)";
if (!$conn->query($createTableSQL)) {
    die("Error creating table: " . $conn->error);
}

// ✅ Step 3: Fetch Data based on matching columns (a, b, c, ..., q)
$sql = "
SELECT DISTINCT
    bn.icode,
    cn.c_cat
FROM 
    bom_new bn
JOIN
    Compound_name cn ON
    CASE cn.cat
        WHEN 'a' THEN bn.a
        WHEN 'b' THEN bn.b
        WHEN 'c' THEN bn.c
        WHEN 'd' THEN bn.d
        WHEN 'e' THEN bn.e
        WHEN 'f' THEN bn.f
        WHEN 'g' THEN bn.g
        WHEN 'h' THEN bn.h
        WHEN 'i' THEN bn.i
        WHEN 'j' THEN bn.j
        WHEN 'k' THEN bn.k
        WHEN 'l' THEN bn.l
        WHEN 'm' THEN bn.m
        WHEN 'n' THEN bn.n
        WHEN 'o' THEN bn.o
        WHEN 'p' THEN bn.p
        WHEN 'q' THEN bn.q
    END = cn.c_cat
";

$result = $conn->query($sql);

// ✅ Step 4: Insert Data into filtered_data
if ($result->num_rows > 0) {
    // Prepare insert statement
    $insertSQL = $conn->prepare("INSERT INTO filtered_data (icode, c_cat) VALUES (?, ?)");
    if (!$insertSQL) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters
    $insertSQL->bind_param("si", $icode, $c_cat);
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $icode = $row["icode"];
        $c_cat = $row["c_cat"];
        
        // Execute insert
        if (!$insertSQL->execute()) {
            echo "Insert Error: " . $insertSQL->error . "<br>";
        } else {
            $count++;
        }
    }
    
    $insertSQL->close();
    echo "✅ $count records inserted successfully into 'filtered_data'.<br>";
} else {
    echo "❌ No matching data found.<br>";
}

// ✅ Step 5: Display ALL Inserted Data with better formatting
$displaySQL = "SELECT * FROM filtered_data"; 
$displayResult = $conn->query($displaySQL);

if ($displayResult->num_rows > 0) {
    echo "<h3>Inserted Records (" . $displayResult->num_rows . " total):</h3>";
    
    // Create a table for better display
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>ICODE</th><th>C_CAT</th></tr>";
    
    while ($row = $displayResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["icode"] . "</td>";
        echo "<td>" . $row["c_cat"] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "No data found in 'filtered_data' table.";
}

// Close the connection
$conn->close();
?>
