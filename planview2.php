<?php
// Replace these variables with your own database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a MySQL connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to insert data from tobeplan into tobeplan1234
$insertDataSQL = "INSERT INTO tobeplan1234 (id, icode, tobe, erp, stockonhand)
SELECT t.id, t.icode, t.tobe, t.erp, t.stockonhand
FROM tobeplan t
LEFT JOIN quick_plan q ON t.icode = q.icode
WHERE q.icode IS NULL AND t.tobe > 0";

if ($conn->query($insertDataSQL) === TRUE) {
    echo "Data inserted into tobeplan1234 successfully.<br>";
} else {
    echo "Error inserting data into tobeplan1234: " . $conn->error . "<br>";
}

// SQL query to copy data from tobeplan to tobeplan1
$copyDataQuery = "INSERT INTO tobeplan12345 SELECT * FROM tobeplan";
if ($conn->query($copyDataQuery) === TRUE) {
    echo "Data deleted from tobeplan successfully.<br>";
} else {
    echo "Error deleting data from tobeplan: " . $conn->error . "<br>";
}


// SQL query to delete data from tobeplan
$deletetobe = "DELETE FROM tobeplan";

if ($conn->query($deletetobe) === TRUE) {
    echo "Data deleted from tobeplan successfully.<br>";
} else {
    echo "Error deleting data from tobeplan: " . $conn->error . "<br>";
}

// SQL query to delete data from quick_plan
$deleteQuickPlan = "DELETE FROM quick_plan";

if ($conn->query($deleteQuickPlan) === TRUE) {
    echo "Data deleted from quick_plan successfully.<br>";
} else {
    echo "Error deleting data from quick_plan: " . $conn->error . "<br>";
}

// Check if there is any data in the tobeplan1234 table
$selectDataSQL = "SELECT COUNT(*) AS count FROM tobeplan1234";
$result = $conn->query($selectDataSQL);

if ($result === FALSE) {
    die("Error checking data in tobeplan1234: " . $conn->error);
}

$row = $result->fetch_assoc();
$count = $row['count'];

if ($count > 0) {
    // Copy data from tobeplan1234 to tobeplan
    $copyDataToTobeplanSQL = "INSERT INTO tobeplan (id, icode, tobe, erp, stockonhand)
    SELECT t.id, t.icode, t.tobe, t.erp, t.stockonhand
    FROM tobeplan1234 t";

    if ($conn->query($copyDataToTobeplanSQL) === TRUE) {
        echo "Data copied from tobeplan1234 to tobeplan successfully.<br>";
    } else {
        echo "Error copying data from tobeplan1234 to tobeplan: " . $conn->error . "<br>";
    }

    // Delete data from tobeplan1234
    $deleteTobeplan1234 = "DELETE FROM tobeplan1234";
    if ($conn->query($deleteTobeplan1234) === TRUE) {
        echo "Data deleted from tobeplan1234 successfully.<br>";
    } else {
        echo "Error deleting data from tobeplan1234: " . $conn->error . "<br>";
    }

    // Redirect to QuickPlan.php
    header("Location: quickplan12.php");
    exit; // Ensure that no further code is executed after the redirection
} else {
    // No data in tobeplan1234, redirect to Plannew5.php
    header("Location: planrecordid2.php");
    exit; // Ensure that no further code is executed after the redirection
}

// Close the MySQL connection
$conn->close();
?>
