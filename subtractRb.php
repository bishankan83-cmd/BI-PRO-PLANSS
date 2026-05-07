<?php
ob_start(); // Start output buffering

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

// Perform subtraction and insert result into tobeplan table
$subtractionSql = "INSERT INTO tobeplan123 (icode, tobe, erp, stockonhand)
                  SELECT t1.icode, t1.new - t2.cstock, t1.erp, t2.cstock
                  FROM copied_work t1
                  INNER JOIN stock t2 ON t1.icode = t2.icode";

if ($conn->query($subtractionSql) === TRUE) {
    

    
        
} else {
    echo "Error psserforming subtraction: " . $conn->error;
}


// ... Rest of the code ...

$conn->close();
// Redirect to another page to display the relevant data
header("Location: subtractR2b.php"); // No ERP parameter needed
exit;

ob_end_flush(); // Send output buffer and turn off output buffering
?>
