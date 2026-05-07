<?php
// Establish database connection (same as in the original file)
$conn = mysqli_connect("localhost:3306", "root", "", "task_management");

// Check if the connection is successful (same as in the original file)
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve the ERP number from the query parameter
$erp = isset($_GET['erp']) ? $_GET['erp'] : '';

// Validate the ERP number (you can add your own validation logic here)
if (empty($erp)) {
    die("Invalid ERP ID");
}

// Retrieve the production plan details for the ERP number
$sql = "SELECT * FROM production_plan WHERE erp = '$erp'";
$result = mysqli_query($conn, $sql);

// Check if any production plan entries exist
if (mysqli_num_rows($result) > 0) {
    // Display the production plan details in a table
    echo "<h2>Production Plan Details for ERP ID: $erp</h2>";
    echo "<table>";
    echo "<tr><th>Tire ID</th><th>Press Name</th><th>Mold Name</th><th>Start Date</th><th>End Date</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        $icode = $row['icode'];
        $press_name = $row['press_name'];
        $mold_name = $row['mold_name'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];

        echo "<tr>";
        echo "<td>$icode</td>";
        echo "<td>$press_name</td>";
        echo "<td>$mold_name</td>";
        echo "<td>$start_date</td>";
        echo "<td>$end_date</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No production plan details found for the provided ERP ID.";
}

// Close the database connection
mysqli_close($conn);
?>
