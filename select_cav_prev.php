<?php
// DB connection
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Check if press_selections_copy is empty
$checkSql = "SELECT COUNT(*) as count FROM press_selections_copy";
$result = $conn->query($checkSql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Table is empty: Copy from press_selections_new to press_selections_new2
    $deleteNew2 = "DELETE FROM press_selections_new2";
    $conn->query($deleteNew2); // Clear first if needed

    $insertNew2 = "
        INSERT INTO press_selections_new2 (
            id, icode, mold_id, press_name, mold_count, tobe_sum, description,
            created_at, updated_at, cavity_ids, end_date, start_date
        )
        SELECT 
            id, icode, mold_id, press_name, mold_count, tobe_sum, description,
            created_at, updated_at, cavity_ids, end_date, start_date
        FROM press_selections_new
    ";
    if (!$conn->query($insertNew2)) {
        die("Error copying to press_selections_new2: " . $conn->error);
    }

    // Now copy from new2 to copy
    $insertCopySql = "
        INSERT INTO press_selections_copy (
            id, icode, mold_id, press_name, mold_count, tobe_sum, description,
            created_at, updated_at, cavity_ids, end_date, start_date, is_completed
        )
        SELECT 
            id, icode, mold_id, press_name, mold_count, tobe_sum, description,
            created_at, updated_at, cavity_ids, end_date, start_date, is_completed
        FROM press_selections_new2
    ";
    if (!$conn->query($insertCopySql)) {
        die("Error copying to press_selections_copy: " . $conn->error);
    }
}

// Step 2: Delete from press_selections
$deletePress = "DELETE FROM press_selections";
if (!$conn->query($deletePress)) {
    die("Error deleting from press_selections: " . $conn->error);
}

// Step 3: Insert into press_selections from press_selections_copy
$insertFinal = "
    INSERT INTO press_selections (
        id, icode, mold_id, press_name, mold_count, tobe_sum, description, 
        created_at, updated_at, cavity_ids, end_date, start_date, is_completed
    )
    SELECT 
        id, icode, mold_id, press_name, mold_count, tobe_sum, description, 
        created_at, updated_at, cavity_ids, end_date, start_date, is_completed
    FROM press_selections_copy
";
if (!$conn->query($insertFinal)) {
    die("Error inserting into press_selections: " . $conn->error);
}

$conn->close();


?>





<?php
// Database connection configuration
$host = 'localhost'; // Replace with your database host
$dbname = 'planatir_task_managemen'; // Replace with your database name
$username = 'planatir_task_managemen'; // Replace with your database username
$password = 'Bishan@1919'; // Replace with your database password

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to insert records
    $sql = "
        INSERT INTO press_selections (icode, mold_id, is_new, cavity_ids, press_name, is_hidden)
        SELECT 
            p.icode, 
            p.mold_id, 
            1, 
            GROUP_CONCAT(pc.cavity_id) AS cavity_ids, 
            pr.press_name,
            1
        FROM plannew p
        LEFT JOIN press_selections_copy psc 
            ON p.icode = psc.icode AND p.mold_id = psc.mold_id
        JOIN press_cavity pc 
            ON p.cavity_id = pc.cavity_id
        JOIN press pr 
            ON pc.press_id = pr.press_id
        WHERE psc.icode IS NULL AND psc.mold_id IS NULL
        GROUP BY p.icode, p.mold_id, pr.press_name
    ";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Get the number of affected rows
    $rowCount = $stmt->rowCount();
    echo "Successfully inserted $rowCount records into press_selections table.\n";

} catch (PDOException $e) {
    // Handle any database errors
    echo "Error: " . $e->getMessage() . "\n";
}

// Close the connection
$pdo = null;

// Step 4: Redirect to another page
header("Location: select_cav.php");
exit;
?>




