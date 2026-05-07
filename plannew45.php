<?php

// Database connection details for both databases
$host = 'localhost';
$dbname_plann = 'planatir_plann'; // Database containing 'process_plan' table
$dbname_task = 'planatir_task_managemen'; // Database containing 'percentage' table
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create a connection to the 'planatir_plann' database
$conn_plann = new mysqli($host, $username, $password, $dbname_plann);

// Check connection
if ($conn_plann->connect_error) {
    die("Connection to planatir_plann failed: " . $conn_plann->connect_error);
}

// Create a separate connection to the 'planatir_task_managemen' database
$conn_task = new mysqli($host, $username, $password, $dbname_task);

// Check connection
if ($conn_task->connect_error) {
    die("Connection to planatir_task_managemen failed: " . $conn_task->connect_error);
}

// Check if there is data in the 'process_plan' table in 'planatir_task_managemen'
$checkSQL = "SELECT COUNT(*) as count FROM `process_plan`";
$result = $conn_task->query($checkSQL);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];

    if ($count > 0) {
        // If there is data in the 'process_plan' table, delete records from 'process_plan' in 'planatir_plann'
        $deleteSQL = "DELETE FROM `process_plan`";
        
        if ($conn_plann->query($deleteSQL) === TRUE) {
            echo "All records deleted successfully from `process_plan`.";
        } else {
            echo "Error deleting records: " . $conn_plann->error;
        }
    } else {
        // If 'process_plan' in 'planatir_task_managemen' is empty, insert data from 'planatir_plann'
        $insertSQL = "INSERT INTO `process_plan` (icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date)
                       SELECT icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date
                       FROM `planatir_plann`.`process_plan`";
        
        if ($conn_task->query($insertSQL) === TRUE) {
            echo "Records inserted successfully from `process_plan` in planatir_plann to process_plan in planatir_task_managemen.";
        } else {
            echo "Error inserting records: " . $conn_task->error;
        }
    }
} else {
    echo "Error checking 'process_plan' table: " . $conn_task->error;
}

// Close both connections
$conn_plann->close();
$conn_task->close();
?>



<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details for both databases
$host = 'localhost';
$dbname_plann = 'planatir_plann'; // Database containing 'tobeplan_plan' table
$dbname_task = 'planatir_task_managemen'; // Database containing 'percentage' table
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

// Create a connection to the 'planatir_plann' database
$conn_plann = new mysqli($host, $username, $password, $dbname_plann);

// Check connection
if ($conn_plann->connect_error) {
    die("Connection to planatir_plann failed: " . $conn_plann->connect_error);
}

// Create a separate connection to the 'planatir_task_managemen' database
$conn_task = new mysqli($host, $username, $password, $dbname_task);

// Check connection
if ($conn_task->connect_error) {
    die("Connection to planatir_task_managemen failed: " . $conn_task->connect_error);
}

// Check if there is data in the 'tobeplan_plan' table in 'planatir_task_managemen'
$checkSQL = "SELECT COUNT(*) as count FROM `tobeplan_plan`";
$result = $conn_task->query($checkSQL);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];

    if ($count > 0) {
        // If there is data in the 'tobeplan_plan' table, delete records from 'tobeplan_plan' in 'planatir_plann'
        $deleteSQL = "DELETE FROM `tobeplan_plan`";
        
        if ($conn_plann->query($deleteSQL) === TRUE) {
            echo "All records deleted successfully from `tobeplan_plan`.";
        } else {
            echo "Error deleting records: " . $conn_plann->error;
        }
    } else {
        // If 'tobeplan_plan' in 'planatir_task_managemen' is empty, insert data from 'planatir_plann'
        $insertSQL = "INSERT INTO `tobeplan_plan` (id, icode, tobe, erp, stockonhand)
                       SELECT id, icode, tobe, erp, stockonhand
                       FROM `planatir_plann`.`tobeplan_plan`";
        
        if ($conn_task->query($insertSQL) === TRUE) {
            echo "Records inserted successfully from `tobeplan_plan` in planatir_plann to tobeplan_plan in planatir_task_managemen.";
        } else {
            echo "Error inserting records: " . $conn_task->error;
        }
    }
} else {
    echo "Error checking 'tobeplan_plan' table: " . $conn_task->error;
}

// Close both connections
$conn_plann->close();
$conn_task->close();
?>












<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details for the source database
$source_host = 'localhost';
$source_dbname = 'planatir_task_managemen';
$source_username = 'planatir_task_managemen'; // Update if different
$source_password = 'Bishan@1919'; // Update if different

// Database connection details for the destination database
$dest_host = 'localhost';
$dest_dbname = 'planatir_plann';
$dest_username = 'planatir_task_managemen'; // Update if different
$dest_password = 'Bishan@1919'; // Update if different

// Create connection for source database
$source_conn = new mysqli($source_host, $source_username, $source_password, $source_dbname);

// Check connection for source database
if ($source_conn->connect_error) {
    die("Source Connection failed: " . $source_conn->connect_error);
}

// Create connection for destination database
$dest_conn = new mysqli($dest_host, $dest_username, $dest_password, $dest_dbname);

// Check connection for destination database
if ($dest_conn->connect_error) {
    die("Destination Connection failed: " . $dest_conn->connect_error);
}

// Fetch data from the source database
$fetchDataSQL = "SELECT `id`,`icode`, `mold_id`, `tires_per_mold`, `cavity_id`, `mold_name`, 
                        `cavity_name`, `press_name`, `press_id`, `erp`, `serial`, 
                        `is_completed`, `is_highlighted`, `first_tobe`, `start_date` 
                 FROM `process_plan`";

$result = $source_conn->query($fetchDataSQL);

// Check if data was fetched
if ($result->num_rows > 0) {
    // Loop through the results and insert into destination database
    while ($row = $result->fetch_assoc()) {
        // Assign values to variables
        $id = $row['id'];
        $icode = $row['icode'];
        $mold_id = $row['mold_id'];
        $tires_per_mold = $row['tires_per_mold'];
        $cavity_id = $row['cavity_id'];
        $mold_name = $row['mold_name'];
        $cavity_name = $row['cavity_name'];
        $press_name = $row['press_name'];
        $press_id = $row['press_id'];
        $erp = $row['erp'];
        $serial = $row['serial'];
        $is_completed = $row['is_completed'];
        $is_highlighted = $row['is_highlighted'];
        $first_tobe = $row['first_tobe'];
        $start_date = $row['start_date'];

        // Create an insert SQL statement
        $insertDataSQL = "INSERT INTO `process_plan` (
                            `id`, `icode`, `mold_id`, `tires_per_mold`, `cavity_id`, `mold_name`, 
                            `cavity_name`, `press_name`, `press_id`, `erp`, `serial`, 
                            `is_completed`, `is_highlighted`, `first_tobe`, `start_date`
                          ) VALUES (
                            '$id', '$icode', '$mold_id', $tires_per_mold, $cavity_id, '$mold_name', 
                            '$cavity_name', '$press_name', $press_id, '$erp', '$serial', 
                            $is_completed, $is_highlighted, $first_tobe, '$start_date'
                          )";

        // Execute insert statement
        if (!$dest_conn->query($insertDataSQL)) {
            echo "Error inserting data: " . $dest_conn->error . "<br>";
        }
    }
    
    echo "Data transferred successfully from `process_splan` to `process_plan`.<br>";
} else {
    echo "No data found in `process_splan`.<br>";
}

// Close both database connections
$source_conn->close();
$dest_conn->close();
?>






<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details for the source database
$source_host = 'localhost';
$source_dbname = 'planatir_task_managemen';
$source_username = 'planatir_task_managemen'; // Update if different
$source_password = 'Bishan@1919'; // Update if different

// Database connection details for the destination database
$dest_host = 'localhost';
$dest_dbname = 'planatir_plann';
$dest_username = 'planatir_task_managemen'; // Update if different
$dest_password = 'Bishan@1919'; // Update if different

// Create connection for source database
$source_conn = new mysqli($source_host, $source_username, $source_password, $source_dbname);

// Check connection for source database
if ($source_conn->connect_error) {
    die("Source Connection failed: " . $source_conn->connect_error);
}

// Create connection for destination database
$dest_conn = new mysqli($dest_host, $dest_username, $dest_password, $dest_dbname);

// Check connection for destination database
if ($dest_conn->connect_error) {
    die("Destination Connection failed: " . $dest_conn->connect_error);
}

// Fetch data from the source database
$fetchDataSQL = "SELECT `id`, `icode`, `tobe`, `erp`, `stockonhand` 
                 FROM `tobeplan_plan`"; // Correct table name here

$result = $source_conn->query($fetchDataSQL);

// Check if data was fetched
if ($result->num_rows > 0) {
    // Loop through the results and insert into destination database
    while ($row = $result->fetch_assoc()) {
        // Assign values to variables
        $id = $row['id'];
        $icode = $row['icode'];
        $tobe = $row['tobe']; // Fetching 'tobe' from source
        $erp = $row['erp']; // Fetching 'erp' from source
        $stockonhand = $row['stockonhand']; // Fetching 'stockonhand' from source

        // Create an insert SQL statement
        $insertDataSQL = "INSERT INTO `tobeplan_plan` (
                            `id`, `icode`, `tobe`, `erp`, `stockonhand`
                          ) VALUES (
                            '$id', '$icode', '$tobe', '$erp', '$stockonhand'
                          )";

        // Execute insert statement
        if (!$dest_conn->query($insertDataSQL)) {
            echo "Error inserting data for ID $id: " . $dest_conn->error . "<br>";
        }
    }
    
    echo "Data transferred successfully from `tobeplan_plan` source to destination.<br>";
} else {
    echo "No data found in `tobeplan_plan`.<br>";
}

// Close both database connections
$source_conn->close();
$dest_conn->close();
?>
























<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 1: Check if there is data in process_plan
    $checkSql = "SELECT COUNT(*) FROM process_plan";
    $stmt = $pdo->query($checkSql);
    $rowCount = $stmt->fetchColumn();

    // Step 2: If there are records in process_plan, delete existing data in process_plan_tem and insert new data
    if ($rowCount > 0) {
        // Delete existing data in process_plan_tem
        $deleteSql = "DELETE FROM process_plan_tem";
        $pdo->exec($deleteSql);
        
        // Insert data from process_plan to process_plan_tem
        $insertSql = "
            INSERT INTO process_plan_tem (icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date)
            SELECT icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date
            FROM process_plan
        ";

        // Execute the insert query
        $pdo->exec($insertSql);
        echo "Data successfully transferred from process_plan to process_plan_tem after clearing the destination table.";
    } else {
        echo "No data found in process_plan. No action taken.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$pdo = null;
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

// Step 1: Delete all data from tobeplan_plan
$delete_plan_sql = "DELETE FROM tobeplan_plan";
if ($conn->query($delete_plan_sql) === TRUE) {
    echo "All data deleted from tobeplan_plan successfully.<br>";
} else {
    echo "Error deleting data from tobeplan_plan: " . $conn->error . "<br>";
}

// Step 2: Insert data from tobeplan_tem into tobeplan_plan
$insert_plan_sql = "INSERT INTO tobeplan_plan (id, icode, tobe, erp, stockonhand)
                    SELECT id, icode, tobe, erp, stockonhand
                    FROM tobeplan_tem";

if ($conn->query($insert_plan_sql) === TRUE) {
    echo "Data inserted into tobeplan_plan successfully.";
} else {
    echo "Error inserting data into tobeplan_plan: " . $conn->error;
}

// Close connection
$conn->close();
?>















<?php
// Database configuration
$host = 'localhost';       // Database host with port
$db   = 'planatir_task_managemen'; // Database name
$user = 'planatir_task_managemen'; // Database username
$pass = 'Bishan@1919';            // Database password
$charset = 'utf8mb4';             // Database charset

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options for the PDO object
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);

    // SQL query to delete all rows from mold_mapping table
    $sql = "DELETE FROM mold_mapping2";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Execute the statement
    $stmt->execute();

    //echo "All records have been deleted from the mold_mapping table.";
} catch (PDOException $e) {
    // If there is an error, display the error message
    //echo "Error: " . $e->getMessage();
}
?>


<?php
// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to update mold table with the latest end_date from plannew table
$sql = "
    UPDATE mold m
    JOIN (
        SELECT mold_id, MAX(end_date) AS latest_end_date
        FROM plannew
        GROUP BY mold_id
    ) p ON m.mold_id = p.mold_id
    SET m.availability_date = p.latest_end_date
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Mold availability dates updated successfully";
} else {
    echo "Error updating mold availability dates: " . $conn->error;
}

// Close connection
$conn->close();
?>











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

// Retrieve the largest end_date for each cavity_id from the plannew table
$sql = "SELECT cavity_id, MAX(end_date) AS largest_end_date
        FROM plannew
        GROUP BY cavity_id";
$result = $conn->query($sql);

// Update the availability_date in the cavity table
while ($row = $result->fetch_assoc()) {
    $cavity_id = $row['cavity_id'];
    $largest_end_date = $row['largest_end_date'];

    $update_sql = "UPDATE cavity
                   SET availability_date = '$largest_end_date'
                   WHERE cavity_id = '$cavity_id'";
    if ($conn->query($update_sql) === TRUE) {
       // echo "Availability date updated successfully for cavity_id: $cavity_id<br>";
    } else {
        echo "Error updating availability date: " . $conn->error . "<br>";
    }
}

// Close connection
$conn->close();
?>













<?php

$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Create mold_mapping table
$sqlCreateTable = "CREATE TABLE IF NOT EXISTS mold_mapping (
                    mold_id INT,
                    mold_name VARCHAR(255),
                    cavity_id INT,
                    cavity_name VARCHAR(255),
                    icode VARCHAR(255),
                    start_date DATE,
                    end_date DATE,
                    erp VARCHAR(255)
                  )";

if ($conn->query($sqlCreateTable) === TRUE) {
   

    // Delete existing records in the mold_mapping table
    $deleteQuery = "DELETE FROM mold_mapping";
    $conn->query($deleteQuery);

    // Retrieve and display mold_name, along with cavity_id, cavity_name, icode, start_date, end_date, and erp, matching each mold_id
    $sqlSelect = "SELECT m.mold_id, m.mold_name, c.cavity_id, c.cavity_name, p.icode, p.start_date, p.end_date, p.erp
                  FROM mold m
                  JOIN plannew p ON m.mold_id = p.mold_id
                  JOIN cavity c ON p.cavity_id = c.cavity_id";

    $resultSelect = $conn->query($sqlSelect);

    if ($resultSelect->num_rows > 0) {
        // Output data of each row
        while ($rowSelect = $resultSelect->fetch_assoc()) {
          

            // Insert data into the mold_mapping table
            $sqlInsert = "INSERT INTO mold_mapping (mold_id, mold_name, cavity_id, cavity_name, icode, start_date, end_date, erp)
                          VALUES ('" . $rowSelect["mold_id"] . "', '" . $rowSelect["mold_name"] . "', '" . $rowSelect["cavity_id"] . "', '" . $rowSelect["cavity_name"] . "', '" . $rowSelect["icode"] . "', '" . $rowSelect["start_date"] . "', '" . $rowSelect["end_date"] . "', '" . $rowSelect["erp"] . "')";

            if ($conn->query($sqlInsert) === TRUE) {
                // Insertion successful
            } else {
                // Error in insertion
            }
        }
    } else {
        echo "0 results";
    }
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Close the connection
$conn->close();
?>



<?php
// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a new MySQLi connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// The SQL query to be executed
$sql = "INSERT INTO `mold_mapping` (mold_id, mold_name, cavity_id, cavity_name, icode, start_date, erp)
        SELECT mold_id, mold_name, cavity_id, cavity_name, icode, DATE(start_date), erp
        FROM `process_plan`
        WHERE `is_completed` = 1";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo 'Records inserted successfully';
} else {
    echo 'Error: ' . $sql . '<br>' . $conn->error;
}

// Close the database connection
$conn->close();
?>


<?php
// Set the default timezone to Sri Lanka
date_default_timezone_set('Asia/Colombo');

// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Set the DSN (Data Source Name)
$dsn = "mysql:host=$hostname;dbname=$database;charset=utf8mb4";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);

    // Step 1: Ensure the mold_mapping2 table exists
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS `mold_mapping2` (
      `mold_id` int DEFAULT NULL,
      `mold_name` varchar(255) DEFAULT NULL,
      `cavity_id` int DEFAULT NULL,
      `cavity_name` varchar(255) DEFAULT NULL,
      `icode` varchar(255) DEFAULT NULL,
      `start_date` date DEFAULT NULL,
      `end_date` datetime DEFAULT NULL,
      `erp` varchar(255) DEFAULT NULL
    )";
    $pdo->exec($createTableSQL);

    // Step 2: Copy rows to mold_mapping2
    $copyRowsSQL = "
    INSERT INTO `mold_mapping2` (
      `mold_id`, `mold_name`, `cavity_id`, `cavity_name`, `icode`, `start_date`, `end_date`, `erp`
    )
    SELECT 
      `mold_id`, `mold_name`, `cavity_id`, `cavity_name`, `icode`, `start_date`, `end_date`, `erp`
    FROM `mold_mapping`
    WHERE `end_date` < NOW()";
    $pdo->exec($copyRowsSQL);

    // Step 3: Delete moved rows from mold_mapping
    $deleteRowsSQL = "
    DELETE FROM `mold_mapping`
    WHERE `end_date` < NOW()";
    $pdo->exec($deleteRowsSQL);

    echo "Records Purple successfully.";

} catch (PDOException $e) {
    // Handle any errors
    echo 'Connection failed: ' . $e->getMessage();
}
?>












<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Button Click Example</title>
    <style>
        /* Styling for the modal */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1;
            max-height: 80vh; /* Set a maximum height for the modal */
            overflow-y: auto; /* Enable vertical scrolling if content exceeds the height */
        }

        /* Style for the overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 0;
        }

        /* Style for the search box */
        #searchBox {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<!-- Search Box -->
<input type="text" id="searchBox" placeholder="Search for a user">

<!-- Button to trigger the modal -->
<button onclick="openModal()">Show matching press and mold</button>

<!-- The Modal -->
<div id="myModal" class="modal">
    <span onclick="closeModal()" style="cursor: pointer;">&times;</span>
    <div id="modalContent"></div>
</div>
<!-- The overlay -->
<div id="overlay" class="overlay" onclick="closeModal()"></div>

<script>
    // Function to open the modal
    function openModal() {
        var searchTerm = document.getElementById('searchBox').value;
        document.getElementById('myModal').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';

        // Fetch data from the server based on the search term and display in the modal
        fetch('fetch_date.php?search=' + searchTerm)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modalContent').innerHTML = data;
            })
            .catch(error => console.error('Error:', error));
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById('myModal').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }


    function confirmGenerate() {
        // Display confirmation message
        var confirmation = confirm("Are you sure you want to generate the plan?");

        // If user confirms, proceed with generating the plan
        if (confirmation) {
            return true;
        } else {
            return false; // Prevent the default action (following the link)
        }
    }


    function deleteRow(processId) {
        var confirmation = confirm("Are you sure you want to delete this row?");
        if (confirmation) {
            // User clicked OK, proceed with deletion
            // Make an AJAX request to delete the row from the database
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'deleter_row.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle the response if needed
                    // Reload the page or update the table after deletion
                    window.location.reload();
                }
            };
            // Send the process_plan ID to the server for deletion
            xhr.send('id=' + processId);
        } else {
            // User clicked Cancel, do nothing
            // Optionally, you can add an alert or perform other actions here
            console.log("Deletion canceled");
        }
    }
</script>

</body>
</html>

<div class="container">
    <div style="text-align: right;">
        <a href="plannew56.php" onclick="return confirmGenerate()">
            <button class="custom-button">Generate Plan</button>
        </a>
    </div>
</div>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Open Link in New Tab</title>
</head>
<body>

<!-- Button to trigger opening link in new tab -->
<button id="openLinkBtn" style="color: red;" title="Click this button to open a new tab">Curing Time Specification</button>

<script>
document.getElementById("openLinkBtn").addEventListener("click", function() {
    // Define the URL you want to open in a new tab
    var url = "https://plan.atire.com/curing.php";

    // Open the URL in a new tab
    window.open(url, '_blank');
});
</script>

</body>
</html>















<?php
// Connection details
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




    
function fetchData($conn) {
    $sql = "SELECT 
        process_plan.id, 
        process_plan.icode, 
        process_plan.mold_name, 
        process_plan.tires_per_mold, 
        process_plan.cavity_name, 
        process_plan.is_completed, 
        process_plan.is_highlighted, 
        process_plan.first_tobe, 
        tire.description, 
        tire.cuing_group_name,  
        tobeplan_plan.tobe AS tobe_amount,
        MAX(tire.availability_date) AS max_tire_date,
        MAX(cavity.availability_date) AS max_cavity_date,
        MAX(mold.availability_date) AS max_mold_date,
        MAX(plannew1.end_date) AS end_date,
        MAX(process_plan.start_date) AS start_date,
        MAX(plannew1.start_date) AS start_date_plannew1
    FROM process_plan
    LEFT JOIN tire_details ON process_plan.icode = tire_details.icode
    LEFT JOIN tobeplan_plan ON process_plan.icode = tobeplan_plan.icode
    LEFT JOIN tire ON process_plan.icode = tire.icode  
    LEFT JOIN cavity ON process_plan.cavity_name = cavity.cavity_name
    LEFT JOIN mold ON process_plan.mold_name = mold.mold_name
    LEFT JOIN plannew1 ON process_plan.id = plannew1.id
    GROUP BY process_plan.id, process_plan.icode, process_plan.mold_name, process_plan.tires_per_mold, process_plan.cavity_name, process_plan.is_completed, process_plan.is_highlighted, process_plan.first_tobe, tire.description, tire.cuing_group_name, tobeplan_plan.tobe";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

function fetchMoldMappingData($conn) {
    $sql = "SELECT cavity_name FROM mold_mapping";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}
function getCavityId($conn, $cavityName) {
    // Fetch cavity_id based on the provided cavity_name
    $sql = "SELECT cavity_id FROM cavity WHERE cavity_name='$cavityName'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['cavity_id'];
    } else {
        return null; // Handle the case where the cavity is not found
    }
}


// Fetch production plan data
function fetchProductionPlanData($conn, $table) {
    $column = ($table == 'mold') ? 'mold_name' : 'cavity_name';
    $sql = "SELECT process_plan.icode, process_plan.$column, $table.availability_date
            FROM process_plan
            JOIN $table ON process_plan.$column = $table.$column";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $productionPlanData = [];
        while ($row = $result->fetch_assoc()) {
            // Use a composite key of icode and column to ensure uniqueness
            $key = $row['icode'] . '_' . $row[$column];
            $productionPlanData[$key] = $row;
        }
        return $productionPlanData;
    } else {
        return [];
    }
}


function fetchCavityDetails($conn) {
    $sql = "SELECT cavity_name, availability_date FROM cavity";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

function updateData($conn, $id, $moldName, $tiresPerMold, $cavityName, $isCompleted, $isHighlighted, $startDate, $firstTobe) {
    $moldId = getMoldId($conn, $moldName);
    $cavityId = getCavityId($conn, $cavityName);

    $sql = "UPDATE `process_plan` SET
            `mold_name`='$moldName',
            `mold_id`='$moldId',
            `tires_per_mold`='$tiresPerMold',
            `cavity_name`='$cavityName',
            `cavity_id`='$cavityId',
            `is_completed`='$isCompleted',
            `is_highlighted`='$isHighlighted',
            `start_date`='$startDate',
            `first_tobe`='$firstTobe'
            WHERE `id`='$id'";

    $result = $conn->query($sql);

    if ($result === TRUE) {
        // Update successful
    } else {
        // Update failed
    }
}



function getMoldId($conn, $moldName) {
    // Fetch mold_id based on the provided mold_name
    $sql = "SELECT mold_id FROM mold WHERE mold_name='$moldName'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['mold_id'];
    } else {
        return null; // Handle the case where the mold is not found
    }
}


// Function to calculate the total tires_per_mold for each icode
function calculateTotalTiresPerMold($data) {
    $totals = [];

    foreach ($data as $row) {
        $icode = $row['icode'];
        $tiresPerMold = $row['tires_per_mold'];

        if (!isset($totals[$icode])) {
            $totals[$icode] = 0;
        }

        $totals[$icode] += $tiresPerMold;
    }

    return $totals;
}






// Function to get cavity_name by cavity_id from the cavity table
function getCavityNameById($conn, $cavityId) {
    $sql = "SELECT cavity_name FROM cavity WHERE cavity_id = '$cavityId'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['cavity_name'];
    }

    return null;
}









if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ids = $_POST['id'];
    $moldNames = array_map(function ($value) use ($conn) {
        return mysqli_real_escape_string($conn, $value);
    }, $_POST['moldName']);
    $tiresPerMolds = $_POST['tiresPerMold'];
    $cavityNames = array_map(function ($value) use ($conn) {
        return mysqli_real_escape_string($conn, $value);
    }, $_POST['cavityName']);
    $isCompleted = isset($_POST['isCompleted']) ? $_POST['isCompleted'] : [];
    $isHighlighted = isset($_POST['isHighlighted']) ? $_POST['isHighlighted'] : [];
    $firstTobes = isset($_POST['firstTobe']) ? $_POST['firstTobe'] : [];  // Added line for first_tobe

    for ($i = 0; $i < count($ids); $i++) {
        $isChecked = isset($isCompleted[$i]) && $isCompleted[$i] == '1' ? 1 : 0;
        $isHighlightedValue = isset($isHighlighted[$i]) && $isHighlighted[$i] == '1' ? 1 : 0;
        $firstTobeValue = isset($firstTobes[$i]) && $firstTobes[$i] == '1' ? 1 : 0;

        $startDate = $_POST['start_date'][$i] ?? null;
        updateData($conn, $ids[$i], $moldNames[$i], $tiresPerMolds[$i], $cavityNames[$i], $isChecked, $isHighlightedValue, $startDate, $firstTobeValue);
    }
}

$data = fetchData($conn);
$cavityDetails = fetchCavityDetails($conn);


// Fetch production plan data for mold
$productionPlanMoldData = fetchProductionPlanData($conn, 'mold');

// Fetch production plan data for cavity
$productionPlanCavityData = fetchProductionPlanData($conn, 'cavity');

$moldMappingData = fetchMoldMappingData($conn);


// Calculate the total tires_per_mold for each icode
$totalTiresPerMold = calculateTotalTiresPerMold($data);

$conn->close();
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit process_plan Data</title>


    <style>
    body {
        font-family: 'Cantarell';
        background-color: #F28018;
        transition: background-color 1s;
    }

    h1 {
        font-family: 'Cantarell Bold', sans-serif;
    }

    .custom-button {
        background-color: #000000;
        color: #FFFFFF;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-family: 'Cantarell Bold', sans-serif;
        transition: background-color 1s, color 1s;
    }

    .custom-button:hover {
        background-color: #000000;
        color: #F28018;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        background-color: #f4f4f4;
        color: #444444;
        transition: background-color 1s, color 1s;
    }

    .additional-columns {
        display: none; /* Initially hide additional columns */
    }

    table, th, td {
        border: 0.1px solid #6C6565;
        padding: 10px;
        text-align: left;
    }

    .select-box select {
        font-family: 'Open Sans', sans-serif;
        font-weight: normal;
    }

    /* Add a new style for highlighted rows */
    .highlight {
        background-color: gray;
        color: white;
    }



    .custom-checkbox {
    display: inline-block;
    position: relative;
    padding-left: 30px; /* Adjust the space between the checkbox and label */
    cursor: pointer;
  }

  .custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
  }

  .checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 20px; /* Adjust the size of the checkbox */
    width: 20px; /* Adjust the size of the checkbox */
    background-color: #eee; /* Background color of the checkbox */
    border: 1px solid #ccc; /* Border color of the checkbox */
    border-radius: 5px; /* Border radius for rounded corners */
  }

  .custom-checkbox input:checked ~ .checkmark {
    background-color: #2196F3; /* Change the background color when checkbox is checked */
    border: 1px solid #2196F3; /* Change the border color when checkbox is checked */
  }

  .checkmark:after {
    content: "";
    position: absolute;
    display: none;
  }

  .custom-checkbox input:checked ~ .checkmark:after {
    display: block;
  }

  .custom-checkbox .checkmark:after {
    left: 7px; /* Adjust the position of the checkmark inside the checkbox */
    top: 3px; /* Adjust the position of the checkmark inside the checkbox */
    width: 5px; /* Adjust the size of the checkmark */
    height: 10px; /* Adjust the size of the checkmark */
    border: solid white; /* Color of the checkmark */
    border-width: 0 3px 3px 0;
    transform: rotate(45deg); /* Rotate the checkmark to make it diagonal */

    
  /* Hide the input element visually (still takes up space) */
  input[name="isHighlighted[]"] {
    display: none;
  }

  /* OR hide the input element completely (won't take up space) */
   input[name="isHighlighted[]"] {
    display: none;
    visibility: hidden;
  } 
  }

  /* Styles for 'Yes' (highlighted) */
tr.highlight-yes {
    background-color: gray;
    color: white;
}

/* Styles for 'No' (not highlighted) */
tr.highlight-no {
    
}
.custom-dropdown {
    position: relative;
  }

  .custom-dropdown select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding: 5px;
    border: 1px solid #ccc;
    background-color: white;
    cursor: pointer;
  }

  .custom-dropdown select::after {
    content: '\25BC'; /* Unicode character for a downward arrow */
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
  }

  .custom-dropdown select option[value="1"] {
    background-color: green;
    color: white;
    background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, #fff 10px, #fff 20px);
  }

  .custom-dropdown select option[value="0"] {
    background-color: red;
    color: white;
  }

  .custom-completed-yes {
    background-color: green;
    color: white;
  }

  .custom-completed-no {
    background-color: red;
    color: white;
  }
</style>

</head>

<script>
    function redirectToAnotherPage() {
        // Replace 'another_page.php' with the actual filename of your target PHP page
        window.location.href = 'planewd.php';
    }

    function redirectToAnotherPages() {
        // Replace 'another_page.php' with the actual filename of your target PHP page
        window.location.href = 'plannew45new.php';
    }




        function highlightRow(button) {
    var row = button.closest('tr');
    row.classList.toggle('highlight');

    // Get the process_plan ID associated with the row
    var processId = row.querySelector('input[name="id[]"]').value;

    // Make an AJAX request to update the is_highlighted value in the database
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_highlight.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Handle the response if needed
        }
    };

    // Send the process_plan ID and is_highlighted status to the server
    var isHighlighted = row.classList.contains('highlight') ? 1 : 0;
    xhr.send('id=' + processId + '&is_highlighted=' + isHighlighted);
}

function deleteRow(processId) {
        // Make an AJAX request to delete the row from the database
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_row.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Handle the response if needed
                // Reload the page or update the table after deletion
                window.location.reload();
            }
        };

        // Send the process_plan ID to the server for deletion
        xhr.send('id=' + processId);
    }
</script>

<body>
    <h1>Edit process_plan Data</h1>
    
    <form method="POST">
        <table border="1">
            <thead>
            <th>Delete</th>
                    <th>Highlight Row</th>       
                    <th>Icode</th>
                    <th>Description</th>
                    <th>Curing Group</th>
                    <th>Tires per Mold</th>
                    <th>Total tire per mold</th>
                    <th>Tobe Amount</th>
                    <th>Mold Name</th>
                    <th>Cavity Name</th>
                    <th>Availability Date</th>
                    <th>End Date</th>
                    <th>Completed</th>
                    <th>First Tobe</th> <!-- New column header -->
                    <th>Action</th>
                    <th>Date Change</th
      
        <label for="searchDescription">Search Description:</label>
<input type="text" id="searchDescription" oninput="filterTable('description')" placeholder="Type to search...">

<label for="searchIcode">Search Icode:</label>
<input type="text" id="searchIcode" oninput="filterTable('icode')" placeholder="Type to search...">



<label for="searchChuringGroup">Search Churing Group:</label>
<input type="text" id="searchChuringGroup" oninput="filterTable('churingGroup')" placeholder="Type to search...">



       
                </tr>
                
            </thead>

            <tbody>
                <?php foreach ($data as $row): ?>

                   
                    <tr class="<?php echo ($row['is_highlighted'] == 1) ? 'highlight-yes' : 'highlight-no'; ?>">

                    <td>
                            <button type="button" onclick="deleteRow(<?php echo $row['id']; ?>)">Delete</button>
                        </td>
                    


                    


 
    
                    
                    <td class="custom-dropdown">
  <select name="isHighlighted[]">
    <option value="1" <?= ($row['is_highlighted'] == 1) ? 'selected' : ''; ?>>Yes</option>
    <option value="0" <?= ($row['is_highlighted'] == 0) ? 'selected' : ''; ?>>No</option>

  </select>

  <br>
  <button type="button" onclick="redirectToAnotherPages()">Check HighlightRow</button>
</td>

        
   






                        
                        <td><?php echo $row['icode']; ?></td>
                        <td><?php echo $row['description']; ?></td>

                        <td><?php echo $row['cuing_group_name']; ?></td>

                        <td><input type="text" name="tiresPerMold[]" value="<?php echo $row['tires_per_mold']; ?>" required></td>
                        <td><?php echo $totalTiresPerMold[$row['icode']]; ?></td>
                    <td><?php echo $row['tobe_amount']; ?></td>
                        <td>
                            <?php
                            $currentIcode = $row['icode'];

                            $matchingMolds = array_filter($productionPlanMoldData, function ($plan) use ($currentIcode) {
                                return $plan['icode'] == $currentIcode;
                            });
                            ?>
                            <select name="moldName[]">
                                <?php foreach ($matchingMolds as $plan): ?>
                                    <option value="<?php echo $plan['mold_name']; ?>" <?php echo ($row['mold_name'] == $plan['mold_name']) ? 'selected' : ''; ?>>
                                        <?php echo $plan['mold_name'] . '  ' . $plan['availability_date']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <?php
$currentIcode = $row['icode'];

// Filter production plan data for the current icode
$matchingCavities = array_filter($productionPlanCavityData, function ($plan) use ($currentIcode) {
    return $plan['icode'] == $currentIcode;
});
?>
<?php
$currentIcode = $row['icode'];

// Filter production plan data for the current icode
$matchingCavities = array_filter($productionPlanCavityData, function ($plan) use ($currentIcode) {
    return $plan['icode'] == $currentIcode;
});
?>
<td>
    <select name="cavityName[]">
    <?php foreach ($matchingCavities as $plan): ?>
    <?php
    $isSelected = ($row['cavity_name'] == $plan['cavity_name']);
    $isMatchingMoldMapping = in_array(['cavity_name' => $plan['cavity_name']], $moldMappingData);
    $isPastAvailability = strtotime($plan['availability_date']) < time(); // Check if availability date is in the past

    // Set different styles based on conditions
    if ($isMatchingMoldMapping && $isPastAvailability) {
        $style = 'style="background-color: purple; color: white;"';
    } elseif ($isMatchingMoldMapping) {
        $style = 'style="background-color: #F28018;"';
    } else {
        $style = ''; // Default style when none of the conditions are met
    }
    ?>
    <option value="<?php echo $plan['cavity_name']; ?>" <?php echo ($isSelected) ? 'selected ' . $style : $style;  ?>>
        <?php echo $plan['cavity_name'] . ' - : ' . $plan['availability_date']; ?>
    </option>
<?php endforeach; ?>

    <!-- Display other cavities as "Other Cavity" below -->
    <?php foreach ($cavityDetails as $cavity): ?>
            <?php
            $isMatchingMoldMapping = in_array(['cavity_name' => $cavity['cavity_name']], $moldMappingData);
            $isSelected = ($row['cavity_name'] == $cavity['cavity_name']);
            $isPastAvailability = strtotime($cavity['availability_date']) < time();

            // Set different styles based on conditions
    if ($isMatchingMoldMapping && $isPastAvailability) {
        $style = 'style="background-color: purple; color: white;"';
    } elseif ($isMatchingMoldMapping) {
        $style = 'style="background-color: #F28018;"';
    } else {
        $style = ''; // Default style when none of the conditions are met
    }
            ?>
            <?php if (!in_array($cavity['cavity_name'], array_column($matchingCavities, 'cavity_name'))): ?>
                <option value="<?php echo $cavity['cavity_name']; ?>" <?php echo ($isSelected) ? 'selected ' . $style : $style;  ?>>
        <?php echo $cavity['cavity_name'] . ' - : ' . $cavity['availability_date']; ?>
    </option>
            <?php endif; ?>
        <?php endforeach; ?>

       

      

            
                            <?php
                               // Assuming $row contains the relevant data from the process_plan database table
// Modify the following line to match the actual column names in your database
$startDate = $row['start_date'];

$startDatee = $row['start_date_plannew1'];
// Get the highest availability date
$highestAvailabilityDate = max($startDate, $row['max_tire_date'], $row['max_cavity_date'], $row['max_mold_date']);

                            ?>
                             <td>
    <input type="datetime-local" name="start_date[]" value="<?php echo $highestAvailabilityDate; ?>" required>
</td>

                    

                        <td><?php echo $row['end_date']; ?></td> 

                        <td>
                        <select name="isCompleted[]" class="<?= ($row['is_completed'] == 1) ? 'custom-completed-yes' : 'custom-completed-no'; ?>">
    <option value="1" <?= ($row['is_completed'] == 1) ? 'selected' : ''; ?>>Yes</option>
    <option value="0" <?= ($row['is_completed'] == 0) ? 'selected' : ''; ?>>No</option>
  </select>

  
 
 

</td>
<td> <!-- New column for first_tobe -->
    <select name="firstTobe[]">
        <option value="1" <?php echo ($row['first_tobe'] == 1) ? 'selected' : ''; ?>>Yes</option>
        <option value="0" <?php echo ($row['first_tobe'] == 0) ? 'selected' : ''; ?>>No</option>
    </select>
</td>


                        <td>
                            <input type="hidden" name="id[]" value="<?php echo $row['id']; ?>">
                            <button type="submit">Save Changes</button>
                        </td>
                        

                        <td>
    <button type="button" onclick="redirectToAnotherPage()">DATE UPDATE</button>
</td>





                 
                    
                <?php endforeach; ?>
          






                






                


<!DOCTYPE html>
<html>


<body>
   
    <?php
    // Establish a database connection
    $hostname = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    $connection = mysqli_connect($hostname, $username, $password, $database);

    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }



    function getSumOfPositiveTobe($connection)
    {
        $sumQuery = "SELECT SUM(tobe) AS total_tobe FROM `tobeplan_plan` WHERE tobe > 0";
        $result = mysqli_query($connection, $sumQuery);
        $row = mysqli_fetch_assoc($result);
        return $row['total_tobe'];
    }
    
    function displayProcessData($connection)
    {
        // Initialize a variable to store the total
        $totalTiresPerMold = 0;
          // Call the function to get the sum of positive values in tobe
    $sumOfPositiveTobe = getSumOfPositiveTobe($connection);


        // Updated SQL query to join the tobeplan_plan table
        $selectQuery = "SELECT p.icode,p.id, p.tires_per_mold, p.mold_name, p.cavity_name, t.description, t.availability_date, p.is_completed,
                        tp.tobe -- Add the desired column from tobeplan_plan table
                        FROM `process_plan` p
                        LEFT JOIN `mold` m ON p.mold_name = m.mold_name
                        LEFT JOIN `tire` t ON p.icode = t.icode
                        LEFT JOIN `tobeplan_plan` tp ON p.icode = tp.icode"; // Modify this line according to your database structure
        $result = mysqli_query($connection, $selectQuery);
    

   

        if (mysqli_num_rows($result) > 0) {
            
           
                    

      
       
   
            while ($row = mysqli_fetch_assoc($result)) {
        
                
          
                


                // Accumulate the tires_per_mold value to calculate the total
            $totalTiresPerMold += $row['tires_per_mold'];
            }
            echo "</table>";
        } else {
            echo "No records found.";
        }
        // Display the total above the table
    echo "<p>Total Tires per Mold: $totalTiresPerMold</p>";
      // Display the total above the table
     
      echo "<p>Total Tobe (Positive): $sumOfPositiveTobe</p>";
  
     

        mysqli_free_result($result);
    }


    displayProcessData($connection);

    mysqli_close($connection);
    ?>

    <script>


    function filterTable(filterKey) {
        var input, filter, table, tr, td, i, txtValue;
        if (filterKey === 'description') {
            input = document.getElementById("searchDescription");
            filter = input.value.toUpperCase();
            table = document.querySelector("table");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[3]; // Assuming the description is in the third column (index 2)
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        } else if (filterKey === 'icode') {
            input = document.getElementById("searchIcode");
            filter = input.value.toUpperCase();
            table = document.querySelector("table");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[2]; // Assuming the icode is in the second column (index 1)
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }


        else if (filterKey === 'churingGroup') {
        input = document.getElementById("searchChuringGroup");
        filter = input.value.toUpperCase();
        table = document.querySelector("table");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[4]; // Assuming the Churing Group name is in the ninth column (index 8)
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }


    }




        // Function to change colors to an "easy on the eyes" scheme
        function changeColors() {
            // Change background color to a light gray
            document.body.style.backgroundColor = "#EFEFEF";
            // Change button colors to a contrasting color scheme
            const buttons = document.querySelectorAll('.custom-button');
            buttons.forEach(button => {
                button.style.backgroundColor = "#F28018"; // A shade of blue
                button.style.color = "#FFFFFF";
            });
            // Change table colors to improve readability
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                table.style.backgroundColor = "#FFFFFF";
                table.style.color = "#333333"; // Dark gray text
            });
        }

        // Schedule color change after 2 minutes
        setTimeout(changeColors,600); // 2 minutes in milliseconds (2 * 60 * 1000)
    </script>



</body>
</html>

<?php

$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query
    $sql = "
        SELECT p.icode
        FROM process_plan p
        LEFT JOIN tobeplan_plan t ON p.icode = t.icode AND t.tobe > 0
        WHERE t.icode IS NULL;
    ";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the results
    if (!empty($results)) {
        echo "Matching icodes not found in tobeplan_plan table:\n";
        foreach ($results as $result) {
            echo $result['icode'] . "\n";
        }
    } else {
        echo "No matching icodes found.\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>




