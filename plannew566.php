<?php
// Replace these variables with your actual MySQL database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen"; // Change this to the name of your plannew database

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch customer names for each ERP from the 'worder' table
$customerNames = array();
$sql = "SELECT erp, Customer FROM worder";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $erp = $row['erp'];
        $customer_name = $row['Customer'];
        $customerNames[$erp] = $customer_name;
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}

// SQL query to retrieve the required information with ERP number, customer names, and tires_per_mold
$query = "
SELECT 
    p.id,
    p.icode,
    p.mold_id,
    m.availability_date AS mold_availability_date,
    p.tires_per_mold,
    p.cavity_id,
    c.availability_date AS cavity_availability_date,
    t.time_taken,
    pp.erp
FROM
    process p
LEFT JOIN
    mold m ON p.mold_id = m.mold_id
LEFT JOIN
    cavity c ON p.cavity_id = c.cavity_id
LEFT JOIN
    tire t ON p.icode = t.icode
LEFT JOIN
    tobeplan pp ON p.icode = pp.icode
";

// SQL query execution with error handling
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Array to store the fetched data
$dataArray = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $icode = $row['icode'];
        $mold_availability_date = $row['mold_availability_date'];
        $tires_per_mold = $row['tires_per_mold'];
        $cavity_availability_date = $row['cavity_availability_date'];
        $time_taken = $row['time_taken'];
        $erp_number = $row['erp'];

        // Calculate start_date and end_date
        $start_date = max($mold_availability_date, $cavity_availability_date);
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' + ' . ($time_taken * $tires_per_mold) . ' minutes'));

        // Get the customer name for the current ERP
        $customer_name = isset($customerNames[$erp_number]) ? $customerNames[$erp_number] : 'Unknown Customer';

        // Add data to the dataArray
        $dataArray[] = array(
            'id' => $id,
            'icode' => $icode,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'erp' => $erp_number,
            'Customer' => $customer_name,
            'tires_per_mold' => $tires_per_mold,
            'mold_id' => $row['mold_id'],
            'cavity_id' => $row['cavity_id']
        );
    }
} else {
    echo "No data found in the process table.";
}

// Define the INSERT query for plannew table
$insertQuery = "INSERT INTO plannew (icode, start_date, end_date, erp, Customer, tires_per_mold, mold_id, cavity_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


        // Update the availability date of mold
        $sql = "UPDATE mold SET availability_date = '$end_date' WHERE mold_id = '$mold'";
        mysqli_query($conn, $sql);

        // Update the availability date of cavity
        $sql = "UPDATE cavity SET availability_date = '$end_date' WHERE cavity_id = '$cavity'";
        mysqli_query($conn, $sql);
// Prepare the INSERT statement
$insertStmt = $conn->prepare($insertQuery);

if (!$insertStmt) {
    die("Error preparing INSERT statement: " . $conn->error);
}

// Bind variables to the prepared statement
$insertStmt->bind_param("ssssssss", $icode, $start_date, $end_date, $erp_number, $customer_name, $tires_per_mold, $mold_id, $cavity_id);

// Insert data into the plannew table
foreach ($dataArray as $data) {
    $id = $data['id'];
    $icode = $data['icode'];
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];
    $erp_number = $data['erp'];
    $customer_name = $data['Customer'];
    $tires_per_mold = $data['tires_per_mold'];
    $mold_id = $data['mold_id'];
    $cavity_id = $data['cavity_id'];

    // Execute the INSERT statement
    if (!$insertStmt->execute()) {
        die("Error inserting data into plannew table: " . $insertStmt->error);
    }
}

// Close the INSERT statement
$insertStmt->close();

// Close the connection
$conn->close();
//header("Location: deleteall.php");
//exit();
?>

<!-- Display the data without going to the plannew table -->
<?php if (!empty($dataArray)): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>ICODE</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>ERP</th>
            <th>Customer</th>
            <th>Tires per Mold</th>
            <th>Mold ID</th>
            <th>Cavity ID</th>
        </tr>
        <?php foreach ($dataArray as $data): ?>
            <tr>
                <td><?php echo $data['id']; ?></td>
                <td><?php echo $data['icode']; ?></td>
                <td><?php echo $data['start_date']; ?></td>
                <td><?php echo $data['end_date']; ?></td>
                <td><?php echo $data['erp']; ?></td>
                <td><?php echo $data['Customer']; ?></td>
                <td><?php echo $data['tires_per_mold']; ?></td>
                <td><?php echo $data['mold_id']; ?></td>
                <td><?php echo $data['cavity_id']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No data found in the process table.</p>
<?php endif; ?>
