
<form method="POST" action="process2.php">
  <label for="erp_number">ERP Number:</label>
  <input type="text" name="erp_number" id="erp_number">
  <button type="submit" name="submit">Get Press and Molds</button>
</form>
<?php


ob_start();

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

    // Prepare the SQL statement to fetch the distinct press, tire, and mold combinations with availability dates
    $query = "SELECT DISTINCT p.press_id, p.press_name, press.availability_date AS press_availability_date,
    m.mold_id, m.mold_name, m.description, m.icode, mold.availability_date AS mold_availability_date
    FROM production_plan p
    INNER JOIN production_plan m ON p.press_id = m.press_id
    INNER JOIN press ON p.press_id = press.press_id
    INNER JOIN mold ON m.mold_id = mold.mold_id
    INNER JOIN tire t ON p.icode = t.icode
    WHERE p.erp = '$erpNumber'
    ORDER BY press.availability_date ASC";

    $result = mysqli_query($connection, $query);

    // Check if the query execution was successful
    if ($result) {
        // Check if any combinations match the ERP number
        if (mysqli_num_rows($result) > 0) {
            echo "Distinct Press, Tire, and Mold Combinations (Ordered by Availability Date):<br><br>";

            // Create a new table to store the data
            $tableName = 'new_table'; // Replace with your desired table name

            // Create the table if it doesn't exist
            $createTableQuery = "CREATE TABLE IF NOT EXISTS $tableName (
                erp_number VARCHAR(255),
                press_id INT,
                press_name VARCHAR(255),
                press_availability_date DATE,
                mold_id INT,
                mold_name VARCHAR(255),
                description VARCHAR(255),
                icode VARCHAR(255),
                mold_availability_date DATE,
                tire_type VARCHAR(255)
            )";
            mysqli_query($connection, $createTableQuery);

            // Truncate the table to remove any existing data
            $truncateTableQuery = "TRUNCATE TABLE $tableName";
            mysqli_query($connection, $truncateTableQuery);

            // Prepare the insert statement
            $insertStatement = mysqli_prepare($connection, "INSERT INTO $tableName (erp_number, press_id, press_name, press_availability_date, mold_id, mold_name, description, icode, mold_availability_date, tire_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insertStatement, 'sissssssss', $erpNumber, $pressId, $pressName, $pressAvailabilityDate, $moldId, $moldName, $tireDescription, $icode, $moldAvailabilityDate, $tireType);

            // Fetch and display the press, tire, mold, and availability date details
            while ($row = mysqli_fetch_assoc($result)) {
                $pressId = $row['press_id'];
                $pressName = $row['press_name'];
                $pressAvailabilityDate = $row['press_availability_date'];
                $moldId = $row['mold_id'];
                $moldName = $row['mold_name'];
                $tireDescription = $row['description'];
                $icode = $row['icode'];
                $moldAvailabilityDate = $row['mold_availability_date'];
              

                // Insert the data into the new table
                mysqli_stmt_execute($insertStatement);

                // Display the details
                echo "Press ID: $pressId<br>";
                echo "Press Name: $pressName<br>";
                echo "Press Availability Date: $pressAvailabilityDate<br>";
                echo "Mold ID: $moldId<br>";
                echo "Mold Name: $moldName<br>";
                echo "Tire Description: $tireDescription<br>";
                echo "icode: $icode<br>";
                echo "Mold Availability Date: $moldAvailabilityDate<br><br>";
               
            }

            // Close the insert statement
            mysqli_stmt_close($insertStatement);
        } else {
            echo "No distinct press, tire, and mold combinations found for the ERP number.";
        }
    } else {
        echo "Error executing the query: " . mysqli_error($connection);
    }

    // Close the MySQL connection
    mysqli_close($connection);

    // Redirect to another page
    header("Location: tire_cavity.php");
    exit();
}

ob_end_flush();
?>
