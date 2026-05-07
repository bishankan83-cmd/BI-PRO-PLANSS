

<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch records where CavityName is F-1 to F-9
$sql = "SELECT ID, CavityName FROM daily_plan_data WHERE CavityName IN ('F-1', 'F-2', 'F-3', 'F-4', 'F-5', 'F-6', 'F-7', 'F-8', 'F-9')";
$result = $conn->query($sql);

// Check if any records are found
if ($result->num_rows > 0) {
    // Iterate over each row and update the CavityName
    while ($row = $result->fetch_assoc()) {
        // Extract the number part after 'F-'
        $cavityNumber = substr($row['CavityName'], 2);

        // Pad the number with leading zeros to make it two digits
        $newCavityName = 'F-' . str_pad($cavityNumber, 2, '0', STR_PAD_LEFT);

        // Update the record in the database
        $updateSql = "UPDATE daily_plan_data SET CavityName = ? WHERE ID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $newCavityName, $row['ID']);
        $stmt->execute();
    }
    echo "Cavity names updated successfully.";
} else {
    //echo "No records found.";
}

// Close the connection
$conn->close();
?>




<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";  // Replace with your database username
$password = "Bishan@1919";  // Replace with your database password
$dbname = "planatir_task_managemen";  // Replace with your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// First, delete all data from the `serial_data` table
$delete_sql = "DELETE FROM serial_data";

if (!mysqli_query($conn, $delete_sql)) {
    die("Error deleting data: " . mysqli_error($conn));
}

// SQL query to fetch unique Icode and CavityName combinations with serial number, grouped by Icode
$sql = "
    SELECT 
        @serial := IF(@prev_icode = Icode COLLATE utf8mb4_unicode_ci, @serial + 1, 1) AS SerialNumber,  -- Reset serial for each Icode
        Icode,
        CavityName,
        @prev_icode := Icode AS CurrentIcode,  -- Track the current Icode for comparison
        @prev_cavity := CavityName AS CurrentCavity
    FROM 
        (SELECT DISTINCT Icode, CavityName FROM daily_plan_data) AS distinct_data
    CROSS JOIN 
        (SELECT @serial := 0, @prev_icode := '', @prev_cavity := '') AS vars
    ORDER BY 
        Icode COLLATE utf8mb4_unicode_ci ASC, CavityName COLLATE utf8mb4_unicode_ci ASC;
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("SQL Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>
            <tr>
                <th>SerialNumber</th>
                <th>Icode</th>
                <th>CavityName</th>
            </tr>";

    // Loop through the result and display data
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>" . $row["SerialNumber"] . "</td>
                <td>" . $row["Icode"] . "</td>
                <td>" . $row["CavityName"] . "</td>
              </tr>";

        // Insert the data into the database table `serial_data`
        $serial_number = $row["SerialNumber"];
        $icode = $row["Icode"];
        $cavity_name = $row["CavityName"];
        
        // SQL query to insert the data into `serial_data` table
        $insert_sql = "
            INSERT INTO serial_data (SerialNumber, Icode, CavityName)
            VALUES ('$serial_number', '$icode', '$cavity_name')
        ";
        
        if (!mysqli_query($conn, $insert_sql)) {
            echo "Error inserting data: " . mysqli_error($conn);
        }
    }

    echo "</table>";
} else {
    echo "No records found.";
}

// Close the connection
mysqli_close($conn);
?>
