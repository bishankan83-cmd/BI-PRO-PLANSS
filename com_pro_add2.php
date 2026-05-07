<?php
// Database connection parameters
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

// Function to insert data into compound_ent table
function insertData($conn, $row) {
    $sql_insert = "INSERT INTO compound_ent (id, serial_number, inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, rebound) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iisssssisssssssssssss", 
        $row["id"], 
        $row["serial_number"], 
        $row["inputDate"], 
        $row["shift"], 
        $row["compound_name"], 
        $row["description"], 
        $row["cstock"], 
        $row["batch"], 
        $row["pallet"], 
        $row["created_at"], 
        $row["weight"], 
        $row["quality_approved"], 
        $row["expire_date"], 
        $row["staff_name"], 
        $row["sg_value"], 
        $row["hardness"], 
        $row["mh"], 
        $row["ml"], 
        $row["t10"], 
        $row["t90"], 
        $row["rebound"]
    );

    $stmt_insert->execute();
    $stmt_insert->close();
}

// Retrieve form data
$serial_number = $_POST['serial_number'];
$batch = $_POST['batch'];

// Prepare the SQL query to select data
$sql_select = "SELECT * FROM another_table_name1 WHERE serial_number = ? AND batch = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("is", $serial_number, $batch);
$stmt_select->execute();
$result = $stmt_select->get_result();

echo "<h1>Filtered Results</h1>";
if ($result->num_rows > 0) {
    echo "<form action='com_pro_add2.php' method='post'>";
    echo "<table border='1'><tr><th>ID</th><th>Serial Number</th><th>Input Date</th><th>Shift</th><th>Compound Name</th><th>Description</th><th>Current Stock</th><th>Batch</th><th>Pallet</th><th>Created At</th><th>Weight</th><th>Quality Approved</th><th>Expire Date</th><th>Staff Name</th><th>SG Value</th><th>Hardness</th><th>MH</th><th>ML</th><th>T10</th><th>T90</th><th>Rebound</th><th>Action</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["id"]."</td><td>".$row["serial_number"]."</td><td>".$row["inputDate"]."</td><td>".$row["shift"]."</td><td>".$row["compound_name"]."</td><td>".$row["description"]."</td><td>".$row["cstock"]."</td><td>".$row["batch"]."</td><td>".$row["pallet"]."</td><td>".$row["created_at"]."</td><td>".$row["weight"]."</td><td>".$row["quality_approved"]."</td><td>".$row["expire_date"]."</td><td>".$row["staff_name"]."</td><td>".$row["sg_value"]."</td><td>".$row["hardness"]."</td><td>".$row["mh"]."</td><td>".$row["ml"]."</td><td>".$row["t10"]."</td><td>".$row["t90"]."</td><td>".$row["rebound"]."</td><td><input type='submit' name='submit' value='Insert'></td></tr>";
    }
    echo "</table>";
    echo "</form>";

    // Handle form submission
    if(isset($_POST['submit'])) {
        // Insert selected data into compound_ent table
        insertData($conn, $row);
        echo "<p>Data inserted into compound_ent table successfully!</p>";
    }
} else {
    echo "0 results";
}

// Close connections
$stmt_select->close();
$conn->close();
?>
