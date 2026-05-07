<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get the most frequent CavityName for each Icode
$sql = "
    WITH CavityFrequency AS (
        SELECT 
            Icode,
            CavityName,
            COUNT(*) AS Frequency,
            ROW_NUMBER() OVER(PARTITION BY Icode ORDER BY COUNT(*) DESC) AS Rank
        FROM 
            daily_plan_data
        GROUP BY 
            Icode, CavityName
    )
    SELECT 
        Icode,
        CavityName,
        Frequency
    FROM 
        CavityFrequency
    WHERE 
        Rank = 1
";

// Execute the query and check for errors
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "Icode: " . $row["Icode"] . " - CavityName: " . $row["CavityName"] . " - Frequency: " . $row["Frequency"] . "<br>";
    }
} else {
    echo "No records found.";
}

// Close the connection
$conn->close();
?>
