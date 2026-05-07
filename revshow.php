<?php

// Database connection parameters
$servername = "localhost"; // Change to your MySQL server hostname
$username = "planatir_task_managemen"; // Change to your MySQL username
$password = "Bishan@1919"; // Change to your MySQL password
$database = "planatir_task_managemen"; // Change to your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query
$sql = "SELECT w.*
        FROM worder w
        LEFT JOIN tobeplan1 t ON w.erp = t.erp AND w.icode = t.icode
        WHERE t.erp IS NULL";

// Execute query
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Prepare insert statement
    $insert_sql = "INSERT INTO rworder (id, date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);

    // Bind parameters
    $stmt->bind_param("isssssssssssssiiis", $id, $date, $Customer, $wono, $ref, $erp, $icode, $t_size, $brand, $col, $fit, $rim, $cons, $fweight, $ptv, $new, $cbm, $kgs);

    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Assign values to variables
        $id = $row['id'];
        $date = $row['date'];
        $Customer = $row['Customer'];
        $wono = $row['wono'];
        $ref = $row['ref'];
        $erp = $row['erp'];
        $icode = $row['icode'];
        $t_size = $row['t_size'];
        $brand = $row['brand'];
        $col = $row['col'];
        $fit = $row['fit'];
        $rim = $row['rim'];
        $cons = $row['cons'];
        $fweight = $row['fweight'];
        $ptv = $row['ptv'];
        $new = $row['new'];
        $cbm = $row['cbm'];
        $kgs = $row['kgs'];

        // Execute the insert statement
        $stmt->execute();
    }

    // Close statement
    $stmt->close();
    
    // Close connection
    $conn->close();
    
    // Redirect to another page
    header("Location: rsubtract.php");
    exit();
} else {
    // Close connection
    $conn->close();
    // Redirect to import.php to display message
    header("Location: message123.php");
    exit();
}

?>   
