
<?php
// Database connection
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if data exists in the process table
$sql = "SELECT COUNT(*) as count FROM process";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    // If data exists, display the message with improved styling
    if ($count > 0) {
        echo '
        <div style="max-width: 600px; margin: 20px auto; background-color: #f8f9fa; border-left: 5px solid #F28018; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center;">
                <div style="margin-right: 15px;">
                    <i class="fas fa-sync fa-spin" style="font-size: 24px; color:rgb(0, 13, 15);"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: #F28018; font-weight: 600;">System Notice</h4>
                    <p style="margin: 10px 0 0; font-size: 16px;">The Planning Department is currently updating the Work Order Plan. Please check back soon for the latest information. Thank you for your patience</p>
                </div>
            </div>
        </div>';
    }
}

// Close connection
$conn->close();
?>

<!-- Include Font Awesome for the spinning icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">







<?php
// Database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to delete all data from `new_plan_table`
$deleteDataSql = "DELETE FROM `new_plan_table`";

// Execute the query to delete data
if ($conn->query($deleteDataSql) === TRUE) {
    echo "All data deleted successfully.\n";
} else {
    echo "Error deleting data: " . $conn->error . "\n";
}

// Close connection
$conn->close();
?>


<?php
// Database connection details
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

// SQL query to update the plannew table
$sql = "
    UPDATE plannew pn
    JOIN press_cavity pc ON pn.cavity_id = pc.cavity_id
    JOIN press p ON pc.press_id = p.press_id
    SET pn.press = p.press_id, 
        pn.press_name = p.press_name;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully.";
} else {
    echo "Error updating record: " . $conn->error;
}

// Close connection
$conn->close();
?>


<?php
// Database connection details
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

// SQL query to update the plannew table
$sql = "
    UPDATE new_process nn
    JOIN press_cavity pc ON nn.cavity_id = pc.cavity_id
    JOIN press p ON pc.press_id = p.press_id
    SET nn.press_id = p.press_id, 
        nn.press_name = p.press_name;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully.";
} else {
    echo "Error updating record: " . $conn->error;
}

// Close connection
$conn->close();
?>


<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Task 1: Update last_icode and Date in press_last
$sql_update_last_icode = "
    UPDATE press_last pl
    JOIN (
        SELECT 
            dpd.CavityName, 
            dpd.Icode,
            dpd.Date
        FROM daily_plan_data dpd
        WHERE dpd.ID = (
            SELECT MAX(ID)
            FROM daily_plan_data
            WHERE CavityName = dpd.CavityName
        )
    ) AS last_data
    ON pl.press_name = last_data.CavityName
    SET 
        pl.last_icode = last_data.Icode,
        pl.date = last_data.Date; -- Update the Date field as well
";

if ($conn->query($sql_update_last_icode) === TRUE) {
    echo "last_icode and Date updated successfully!<br>";
} else {
    echo "Error updating last_icode and Date: " . $conn->error . "<br>";
}

$conn->close();
?>






<?php
// Database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create the new table with `icode` column
$createTableSql = "CREATE TABLE IF NOT EXISTS `new_plan_table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `end_date` datetime DEFAULT NULL,
    `press_name` varchar(50) NOT NULL,
    `icode` varchar(50) NOT NULL,  -- Added `icode` column
    PRIMARY KEY (`id`)
)";

// Execute the query to create the table
if ($conn->query($createTableSql) === TRUE) {
    echo "Table created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// SQL to insert data into the new table from `plannew` (including `icode`)
$insertDataSql = "INSERT INTO `new_plan_table` (`end_date`, `press_name`, `icode`)
                  SELECT `end_date`, `press_name`, `icode` FROM `plannew`";

// Execute the query to insert the data
if ($conn->query($insertDataSql) === TRUE) {
    echo "Data inserted successfully.\n";
} else {
    echo "Error inserting data: " . $conn->error . "\n";
}

// Close connection
$conn->close();
?>



<?php
// Database connection configuration
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = "Bishan@1919";
$database = 'planatir_task_managemen';

// Establish a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to insert data from press_last into new_plan_table
$sql = "
    INSERT INTO new_plan_table (end_date, press_name, icode)
    SELECT Date, press_name, last_icode
    FROM press_last
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data successfully inserted from press_last to new_plan_table.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the database connection
$conn->close();
?>













<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_date = $_POST['date']; // Get user-input date

    // Query `press_selections_copy` table to get all data for the user-entered date where is_completed = 1
    $query1 = "SELECT * FROM press_selections_copy WHERE DATE(start_date) = ? AND is_completed = 1";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("s", $user_date);
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    if ($result1->num_rows > 0) {
        echo "<h3>Mold Changing Plan $user_date:</h3>";

        // Start table
        echo "<table class='stockr-table'>";
        echo "<thead>
                <tr class='header'>
                    <th>icode</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Rim</th>
                    <th>Mold ID</th>
                    <th>Press Name</th>
                    <th>Start Date</th>
                    <th>Removing Mold</th>
                </tr>
              </thead>
              <tbody>";

        // Loop through all the results from `press_selections_copy`
        while ($row1 = $result1->fetch_assoc()) {
            // Get press_name for second query
            $press_name = $row1['press_name'];

            // Query `new_plan_table` to find `icode`, `end_date`, and `press_name` for the closest date before the entered date
            // Ensure the press_name matches the one from press_selections_copy
            $query2 = "SELECT npt.icode, npt.end_date, npt.press_name 
                       FROM new_plan_table npt 
                       WHERE npt.press_name = ? 
                       AND npt.end_date < ? 
                       ORDER BY npt.end_date ASC
                       LIMIT 1";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bind_param("ss", $press_name, $user_date);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            // Fetch result for the second query
            $icode_from_plan = "";
            $end_date_from_plan = "";
            $press_name_from_plan = "";
            $description_plan = "";
            $brand_plan = "";
            $type_plan = "";
            $rim_plan = "";
            if ($result2->num_rows > 0) {
                $row2 = $result2->fetch_assoc();
                $icode_from_plan = $row2['icode'];
                $end_date_from_plan = $row2['end_date'];
                $press_name_from_plan = $row2['press_name'];

                // Query `tire_details` table to get the details based on the `icode` from `new_plan_table`
                $query4 = "SELECT description, brand, type, rim FROM tire_details WHERE icode = ?";
                $stmt4 = $conn->prepare($query4);
                $stmt4->bind_param("s", $icode_from_plan);
                $stmt4->execute();
                $result4 = $stmt4->get_result();

                // Fetch the details for the given icode from `new_plan_table`
                if ($result4->num_rows > 0) {
                    $row4 = $result4->fetch_assoc();
                    $description_plan = $row4['description'];
                    $brand_plan = $row4['brand'];
                    $type_plan = $row4['type'];
                    $rim_plan = $row4['rim'];
                }
            }

            // Query `tire_details` table to get the details based on the `icode` from `press_selections_copy`
            $query3 = "SELECT description, brand, type, rim FROM tire_details WHERE icode = ?";
            $stmt3 = $conn->prepare($query3);
            $stmt3->bind_param("s", $row1['icode']);
            $stmt3->execute();
            $result3 = $stmt3->get_result();

            // Fetch the details for the given icode from `press_selections_copy`
            $description_process = "";
            $brand_process = "";
            $type_process = "";
            $rim_process = "";
            if ($result3->num_rows > 0) {
                $row3 = $result3->fetch_assoc();
                $description_process = $row3['description'];
                $brand_process = $row3['brand'];
                $type_process = $row3['type'];
                $rim_process = $row3['rim'];
            }

            // Output data in table rows
            echo "<tr class='stockr-row'>
                    <td>" . htmlspecialchars($row1['icode']) . "</td>
                    <td>" . htmlspecialchars($description_process) . "</td>
                    <td>" . htmlspecialchars($brand_process) . "</td>
                    <td>" . htmlspecialchars($type_process) . "</td>
                    <td>" . htmlspecialchars($rim_process) . "</td>
                    <td>" . htmlspecialchars($row1['mold_id']) . "</td>
                    <td>" . htmlspecialchars($row1['press_name']) . "</td>
                    <td>" . htmlspecialchars($row1['start_date']) . "</td>
                    <td>" . htmlspecialchars(($description_plan && $press_name_from_plan === $row1['press_name']) ? $description_plan : "No previous mold") . "</td>
                  </tr>";

            // Close statements for this iteration
            if (isset($stmt2)) $stmt2->close();
            if (isset($stmt4)) $stmt4->close();
            if (isset($stmt3)) $stmt3->close();
        }

        // End table
        echo "</tbody></table>";
    } else {
        echo "<p>No matching date found in <strong>press_selections_copy</strong> with is_completed = 1.</p>";
    }

    // Close statement and connection
    $stmt1->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Press Selections Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell&family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .stockr-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stockr-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
        }

        .search-form {
            text-align: center;
            margin: 10px;
        }

        .search-form input[type="date"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }

        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .button-container button:hover {
            background-color: #333333;
        }

        .stockr-row td {
            font-family: 'Open Sans', sans-serif;
            font-weight: regular;
        }

        .stockr-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .stockr-table .header {
            background-color: #F28018;
            padding: 10px;
        }

        .stockr-table td {
            padding-top: 30px;
        }

        .select-container {
            margin: 10px;
            text-align: center;
        }

        select {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f0f0f0;
        }

        h1, h3 {
            font-family: 'Cantarell', sans-serif;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Search by Date</h1>
        <form method="POST" class="search-form">
            <label for="date">Enter Date:</label>
            <input type="date" id="date" name="date" required>
            <button type="submit">Search</button>
        </form>
    </div>
</body>
</html>