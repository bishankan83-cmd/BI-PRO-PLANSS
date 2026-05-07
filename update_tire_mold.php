<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $updates = $_POST['updates'];
        
        foreach ($updates as $icode => $mold_id) {
            // Prepare SQL statement to update mold_id for the given icode
            $sql = "UPDATE tire_mold SET mold_id=:mold_id WHERE icode=:icode";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':mold_id', $mold_id);
            $stmt->bindParam(':icode', $icode);
            $stmt->execute();
        }
        
        echo "Mold IDs updated successfully!";
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Existing Mold IDs</title>
</head>
<body>
    <h2>Update Existing Mold IDs</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <?php
        // Fetch all existing mold IDs
        $sql = "SELECT icode, mold_id FROM tire_mold";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($results) {
            foreach ($results as $row) {
                echo '<label for="' . $row['icode'] . '">Icode ' . $row['icode'] . ':</label>';
                echo '<input type="text" name="updates[' . $row['icode'] . ']" id="' . $row['icode'] . '" value="' . $row['mold_id'] . '">';
                echo '<br>';
            }
            echo '<br>';
            echo '<input type="submit" value="Update Mold IDs">';
        } else {
            echo "No records found.";
        }
        ?>
    </form>
</body>
</html>
