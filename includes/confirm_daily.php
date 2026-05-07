<?php
// Include your database connection code here
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$connection = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch data for the specified ID from the database
    $query = "SELECT * FROM daily_plan_data1 WHERE ID = $id";
    $result = mysqli_query($connection, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);

        // Check if the form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update the record in the database with the submitted data
            $date = $_POST['date'];
            $shift = $_POST['shift'];
            $icode = $_POST['icode'];
            $moldName = $_POST['moldName'];
            $cavityName = $_POST['cavityName'];
            $plan = $_POST['plan'];
            $additionalData = $_POST['additionalData'];
            $lossReason = $_POST['lossReason'];
            $remark = $_POST['remark'];

            $updateQuery = "UPDATE daily_plan_data1 SET
                Date = '$date',
                Shift = '$shift',
                Icode = '$icode',
                MoldName = '$moldName',
                CavityName = '$cavityName',
                Plan = $plan,
                AdditionalData = '$additionalData',
                LossReason = '$lossReason',
                Remark = '$remark'
                WHERE ID = $id";

            $updateResult = mysqli_query($connection, $updateQuery);

            if ($updateResult) {
                echo "Record updated successfully!";
            } else {
                echo "Error updating record: " . mysqli_error($connection);
            }
        }
    } else {
        echo "Error fetching data: " . mysqli_error($connection);
    }
} else {
    echo "No ID specified.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Daily Plan Data</title>
</head>
<body>

    <h2>Edit Daily Plan Data</h2>
    <form method="POST" action="">
    <!-- Add input fields for each column in your table -->
    <label for="date">Date:</label>
    <input type="date" name="date" value="<?php echo $row['Date']; ?>" required>

    <label for="shift">Shift:</label>
    <input type="text" name="shift" value="<?php echo $row['Shift']; ?>" required>

    <label for="icode">Icode:</label>
    <input type="text" name="icode" value="<?php echo $row['Icode']; ?>" required>

    <label for="moldName">Mold Name:</label>
    <input type="text" name="moldName" value="<?php echo $row['MoldName']; ?>" required>

    <label for="cavityName">Cavity Name:</label>
    <input type="text" name="cavityName" value="<?php echo $row['CavityName']; ?>" required>

    <label for="plan">Plan:</label>
    <input type="number" name="plan" value="<?php echo $row['Plan']; ?>" required>

    <label for="additionalData">Additional Data:</label>
    <textarea name="additionalData"><?php echo $row['AdditionalData']; ?></textarea>

    <label for="lossReason">Loss Reason:</label>
    <input type="text" name="lossReason" value="<?php echo $row['LossReason']; ?>">

    <label for="remark">Remark:</label>
    <input type="text" name="remark" value="<?php echo $row['Remark']; ?>" required>

    <input type="submit" value="Update">
</form>


</body>
</html>

<?php
// Close the database connection
mysqli_close($connection);
?>
