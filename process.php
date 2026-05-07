
<form method="POST" action="process.php">
  <label for="erp_number">ERP Number:</label>
  <input type="text" name="erp_number" id="erp_number">
  <button type="submit" name="submit">Get Press and Molds</button>
</form>
<?php

$host = 'localhost'; // Replace with your host name
$username = 'planatir_task_managemen'; // Replace with your MySQL username
$password = 'Bishan@1919'; // Replace with your MySQL password
$database = 'planatir_task_managemen'; // Replace with your database name

// Create a connection
$connection = mysqli_connect($host, $username, $password, $database);

// Check if the connection was successful
if (!$connection) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
}




// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get the ERP number from the form input (you need to sanitize and validate this input)
    $erpNumber = $_POST['erp_number'];

    // Prepare the SQL statement to fetch the distinct press, tire, and mold combinations
    $query = "SELECT DISTINCT press_id, press_name, mold_id, mold_name, icode, description FROM production_plan WHERE erp = '$erpNumber'";
    $result = mysqli_query($connection, $query);

    // Check if any combinations match the ERP number
    if (mysqli_num_rows($result) > 0) {
        // Create a new table to store the resulting combinations
        $tableName = "resulting_combinations"; // Replace with your desired table name

        $createTableQuery = "CREATE TABLE IF NOT EXISTS $tableName (
            `press_id` int NOT NULL,
            `press_name` varchar(50) NOT NULL,
            `mold_id` int NOT NULL,
            `mold_name` varchar(50) NOT NULL,
            `icode` varchar(50) NOT NULL,
            `description` varchar(100) NOT NULL
        )";
        mysqli_query($connection, $createTableQuery);

        // Insert the distinct combinations into the new table
        while ($row = mysqli_fetch_assoc($result)) {
            $pressId = $row['press_id'];
            $pressName = $row['press_name'];
            $moldId = $row['mold_id'];
            $moldName = $row['mold_name'];
            $icode = $row['icode'];
            $tireDescription = $row['description'];

            $insertQuery = "INSERT INTO $tableName (press_id, press_name, mold_id, mold_name, icode, description)
                            VALUES ('$pressId', '$pressName', '$moldId', '$moldName', '$icode', '$tireDescription')";
            mysqli_query($connection, $insertQuery);
        }

        echo "Distinct press, tire, and mold combinations have been saved in the '$tableName' table.";
    } else {
        echo "No distinct press, tire, and mold combinations found for the ERP number.";
    }

    // Close the MySQL connection
    mysqli_close($connection);
}
?>
