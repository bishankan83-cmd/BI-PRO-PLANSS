<?php
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

// Query to fetch plan_id where the time range between start_date and end_date is exactly one day
$sql = "SELECT p.plan_id, p.erp, p.Customer, p.start_date, p.end_date, p.icode
        FROM plannew p
        WHERE DATEDIFF(p.end_date, p.start_date) = 0";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Create a new connection for inserting data
    $insertConn = new mysqli($servername, $username, $password, $dbname);

    // Prepare an INSERT statement for the new_table
    $insertStatement = $insertConn->prepare("INSERT INTO new_table (plan_id, erp, Customer, start_date, end_date, icode) VALUES (?, ?, ?, ?, ?, ?)");

    // Bind parameters
    $insertStatement->bind_param("isssss", $plan_id, $erp, $customer, $start_date, $end_date, $icode);

    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $plan_id = $row["plan_id"];
        $erp = $row["erp"];
        $customer = $row["Customer"];
        $start_date = $row["start_date"];
        $end_date = $row["end_date"];
        $icode = $row["icode"];

        // Execute the INSERT statement
        $insertStatement->execute();
    }

    // Close the INSERT statement and connection
    $insertStatement->close();
    $insertConn->close();

    echo "Data inserted into new_table successfully!";
} else {
    echo "No results found";
}

$conn->close();
header("Location: countdailyR.php");
exit();
?>
