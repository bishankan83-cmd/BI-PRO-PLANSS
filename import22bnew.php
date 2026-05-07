<?php
// Step 1: Connect to the database
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check the value of 'in' column for id = 1 in checks table
    $stmt = $conn->prepare("SELECT `in` FROM checks WHERE id = ?");
    $id_to_check = 1;
    $stmt->execute([$id_to_check]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['in'] == 1) {
        // If 'in' is already 1, update checks2 and redirect to dashboard.php
        $stmt = $conn->prepare("UPDATE checks2 SET `in` = 1 WHERE id = ?");
        $stmt->execute([$id_to_check]);
        echo "Record updated successfully in checks table";

        // Redirect to dashboard.php
        header("Location: dashboard.php");
        exit(); // Stop further execution after redirection
    } else {
        // If 'in' is 0, check checks2 table for id = 1
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM checks2 WHERE id = ? AND `in` = 0");
        $stmt->execute([$id_to_check]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        
        if ($count > 0) {
            // If 'in' is 0 in checks2, update checks table 'in' to 1
            $stmt = $conn->prepare("UPDATE checks SET `in` = 1 WHERE id = ?");
            $stmt->execute([$id_to_check]);
            echo "Record updated successfully in checks table";

             // Redirect to dashboard.php
        header("Location: import22bnew3.php");
        exit(); 
        } else {
              // Redirect to dashboard.php
              header("Location: dashboard.php");
              exit(); 
        }
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Step 3: Close the connection
$conn = null;
?>


