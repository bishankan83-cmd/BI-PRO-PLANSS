
<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to insert cavity_id that matches the press_id
    $sql = "
        INSERT INTO production_plan (erp, icode, description, press_id, press_name, mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name, start_date, end_date)
        SELECT 
            pp.erp,
            pp.icode,
            pp.description,
            pc.press_id,
            pp.press_name,
            pp.mold_id,
            pp.mold_name,
            pc.cavity_id,
            pp.cavity_name,
            pp.cuing_group_id,
            pp.cuing_group_name,
            pp.start_date,
            pp.end_date
        FROM 
            production_plan pp
        JOIN 
            press_cavity pc
        ON 
            pp.press_id = pc.press_id
        WHERE 
            pp.cavity_id = 0;
    ";

    // Execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    echo "Cavity IDs successfully inserted into the production_plan table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>


<?php
// MySQL database credentials
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

$sql = "
    SELECT `icode`, GROUP_CONCAT(`cavity_id` ORDER BY `plan_id` ASC) AS `matching_cavity_ids`
    FROM `production_plan`
    GROUP BY `icode`
    ORDER BY `plan_id`;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $icode = $row["icode"];
        $matchingCavityIds = explode(',', $row["matching_cavity_ids"]);

        foreach ($matchingCavityIds as $cavityId) {
            $insertSql = "INSERT INTO tire_cavity (icode, cavity_id) VALUES ('$icode', '$cavityId')";
            if ($conn->query($insertSql) !== true) {
                echo "Error inserting data: " . $conn->error;
            }
        }
    }
} else {
    echo "No results found.";
}

$conn->close();

header("Location: tire_mold.php");
exit();
?>
