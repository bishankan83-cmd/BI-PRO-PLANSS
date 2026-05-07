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

// SQL query to delete duplicate rows
$sql = "
DELETE t1
FROM `bcompound2` t1
INNER JOIN `bcompound2` t2 
ON 
    t1.`inputDate` = t2.`inputDate` AND
    t1.`shift` = t2.`shift` AND
    t1.`compound_name` = t2.`compound_name` AND
    t1.`description` = t2.`description` AND
    t1.`cstock` = t2.`cstock` AND
    t1.`batch` = t2.`batch` AND
    t1.`batch2` = t2.`batch2` AND
    t1.`pallet` = t2.`pallet` AND
    t1.`weight` = t2.`weight` AND
    t1.`serial_number` = t2.`serial_number` AND
    t1.`id` > t2.`id`;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Duplicate records deleted successfully";
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close the connection
$conn->close();
?>


<?php
// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert data into bcompound76
    $insertSQL = "
        INSERT INTO `bcompound76` (
          `id`,
          `inputDate`,
          `shift`,
          `compound_name`,
          `description`,
          `cstock`,
          `batch`,
          `batch2`,
          `pallet`,
          `created_at`,
          `weight`,
          `serial_number`
        )
        SELECT 
          `id`,
          `inputDate`,
          `shift`,
          `compound_name`,
          `description`,
          `cstock`,
          `batch`,
          `batch2`,
          `pallet`,
          `created_at`,
          `weight`,
          `serial_number`
        FROM `bcompound2`
        WHERE `inputDate` < CURDATE() - INTERVAL 8 DAY
    ";
    // Execute the insert query
    $conn->exec($insertSQL);

    // Delete the old data from bcompound2
    $deleteSQL = "
        DELETE FROM `bcompound2`
        WHERE `inputDate` < CURDATE() - INTERVAL 8 DAY
    ";
    // Execute the delete query
    $conn->exec($deleteSQL);

    //echo "Data successfully transferred and old records deleted.";
} catch(PDOException $e) {
    //echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>



<?php
// Database connection details
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

// SQL to truncate the table
$sql = "TRUNCATE TABLE bcompound";

// Execute the query
if ($conn->query($sql) === TRUE) {
  //  echo "All data deleted successfully";
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close the connection
$conn->close();
?>



<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// MySQL database connection parameters
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

// SQL query
$sql = "
    WITH RECURSIVE BatchRange AS (
      SELECT `id`, `batch`, `batch2`
      FROM `bcompound2`
      UNION ALL
      SELECT `id`, `batch` + 1, `batch2`
      FROM BatchRange
      WHERE `batch` < `batch2`
    )
    SELECT b.`id`, b.`inputDate`, b.`shift`, b.`compound_name`, b.`description`, b.`cstock`, br.`batch`, b.`pallet`, b.`created_at`, b.`weight`, b.`serial_number`
    FROM BatchRange br
    JOIN `bcompound2` b ON br.`id` = b.`id` AND br.`batch` BETWEEN b.`batch` AND b.`batch2`
    ORDER BY b.`id`, br.`batch`;
";

// Execute query
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Construct insert statement
        $insertSql = "INSERT INTO bcompound (id, inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, serial_number) VALUES (";
        $insertSql .= "'" . $row["id"] . "', ";
        $insertSql .= "'" . $row["inputDate"] . "', ";
        $insertSql .= "'" . $row["shift"] . "', ";
        $insertSql .= "'" . $row["compound_name"] . "', ";
        $insertSql .= "'" . $row["description"] . "', ";
        $insertSql .= "'" . $row["cstock"] . "', ";
        $insertSql .= "'" . $row["batch"] . "', ";
        $insertSql .= "'" . $row["pallet"] . "', ";
        $insertSql .= "'" . $row["created_at"] . "', ";
        $insertSql .= "'" . $row["weight"] . "', ";
        $insertSql .= "'" . $row["serial_number"] . "'";
        $insertSql .= ")";

        // Execute the insert statement
        $conn->query($insertSql);
    }

    echo "Data inserted successfully.";

    // Redirect to another page
    //header("Location: dashboard.php");
    //exit(); // Ensure that no other output is sent before redirection
} else {
    echo "0 results";
}

// Close connection
$conn->close();

?>

<?php

// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL query to delete rows with identical data
    $sql = "
        DELETE b1
        FROM bcompound b1
        JOIN bcompound b2 ON 
            b1.iid > b2.iid
            AND b1.id = b2.id
            AND b1.inputDate = b2.inputDate
            AND b1.shift = b2.shift
            AND b1.compound_name = b2.compound_name
            AND b1.description = b2.description
            AND b1.cstock = b2.cstock
            AND b1.batch = b2.batch
            AND b1.pallet = b2.pallet
            AND b1.weight = b2.weight
            AND b1.serial_number = b2.serial_number
    ";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Redirect to another PHP page
  //  header("Location: dashboard.php");
    //exit(); // Ensure that subsequent code is not executed after redirect
} catch (PDOException $e) {
    // Handle any database errors
    echo "Error: " . $e->getMessage();
}
?>




<?php

// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL query to delete rows with identical data
    $sql = "
        DELETE b1
        FROM bcompound b1
        JOIN bcompound b2 ON 
            b1.iid > b2.iid
            AND b1.id = b2.id
            AND b1.inputDate = b2.inputDate
            AND b1.shift = b2.shift
            AND b1.compound_name = b2.compound_name
            AND b1.description = b2.description
            AND b1.cstock = b2.cstock
            AND b1.batch = b2.batch
            AND b1.pallet = b2.pallet
            AND b1.weight = b2.weight
            AND b1.serial_number = b2.serial_number
    ";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
   
} catch (PDOException $e) {
    // Handle any database errors
    echo "Error: " . $e->getMessage();
}
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

// SQL query to delete all data from the table
$sql = "DELETE FROM another_table_name3";

if ($conn->query($sql) === TRUE) {
   
} else {
    echo "Error deleting data: " . $conn->error;
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

// SQL query to delete all data from the table
$sql = "DELETE FROM another_table_name";

if ($conn->query($sql) === TRUE) {
   
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close connection  
$conn->close();
?>

<?php
// MySQLi configuration
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

// SQL query to update batch column
$sql = "UPDATE `bcompound` SET `batch` = CONCAT('0', `batch`) WHERE `batch` REGEXP '^[1-9]$'";

// Execute the query
if ($conn->query($sql) === TRUE) {
    //echo "Batch column updated successfully.";
} else {
    //echo "Error updating batch column: " . $conn->error;
}

// Close connection
$conn->close();
?>



<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the search inputs from the form
    $date = isset($_POST['date']) ? $_POST['date'] : null;
   // $batch = isset($_POST['batch']) ? $_POST['batch'] : null;
    $compound_name = isset($_POST['compound_name']) ? $_POST['compound_name'] : null;
    $serial_number = isset($_POST['serial_number']) ? $_POST['serial_number'] : null;


    $batch_from = isset($_POST['batch_from']) ? $_POST['batch_from'] : null;
    $batch_to = isset($_POST['batch_to']) ? $_POST['batch_to'] : null;
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

  // Prepare base SQL statement
$sql_select = "SELECT * FROM bcompound WHERE 1";

// Add ORDER BY clause to sort by batch number
//$sql_select .= " ORDER BY batch ASC";

// Prepare parameters array for binding
$params = array();

    // Prepare SQL statement based on individual search criteria
    if (!empty($date)) {
        $sql_select .= " AND inputDate = ?";
        $params[] = $date;
    }
    if (!empty($batch_from) && !empty($batch_to)) {
        $sql_select .= " AND batch BETWEEN ? AND ?";
        $params[] = $batch_from;
        $params[] = $batch_to;
    } elseif (!empty($batch_from)) {
        $sql_select .= " AND batch >= ?";
        $params[] = $batch_from;
    } elseif (!empty($batch_to)) {
        $sql_select .= " AND batch <= ?";
        $params[] = $batch_to;
    }

    if (!empty($compound_name)) {
        $sql_select .= " AND compound_name = ?";
        $params[] = $compound_name;
    }
    if (!empty($serial_number)) {
        $sql_select .= " AND serial_number = ?";
        $params[] = $serial_number;
    }

    // Prepare and bind parameters
    $stmt_select = $conn->prepare($sql_select);
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Generate type string dynamically
        $stmt_select->bind_param($types, ...$params);
    }

    // Execute the query
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    // Check if there are any rows
    if ($result->num_rows > 0) {
        // Prepare SQL statement to insert data into another_table_data
        $sql_insert = "INSERT INTO another_table_name3 (inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, serial_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        // Bind parameters for insertion
        $stmt_insert->bind_param("ssssssssss", $inputDate, $shift, $compound_name, $description, $cstock, $batch, $pallet, $created_at, $weight, $serial_number);

        // Fetch data from result set and insert into another_table_data
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $inputDate = $row['inputDate'];
            $shift = $row['shift'];
            $compound_name = $row['compound_name'];
            $description = $row['description'];
            $cstock = $row['cstock'];
            $batch = $row['batch'];
            $pallet = $row['pallet'];
            $created_at = $row['created_at'];
            $weight = $row['weight'];
            $serial_number = $row['serial_number']; // Get serial_number from bcompound table

            // Execute insertion
            $stmt_insert->execute();
        }

        echo "Data inserted successfully into another_table_data.";

        // Redirect to another page
       header("Location: lab67.php");
    exit();
    } else {
        echo "No data found for the given criteria.";
    }

    // Close statements and connection
    $stmt_select->close();
    $conn->close();
}
?>





<!DOCTYPE html>
<html>
<head>
    <title>Insert Data on Button Click</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h2 {
            font-weight: bold;
            font-size: 24px;
            font-family: 'Cantarell Bold', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        .alert {
            background-color: #FFD700;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="date"],
        input[type="text"], /* Added */
        select,
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #000000;
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Enter Search Criteria</h2>
    <form id="searchForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">


        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div class="form-group">
    <label for="batch">batch</label>
    <select id="batch" name="batch" readonly>
        <option value="">Show Batch</option>
    </select>
</div>

        
        <div class="form-group">
            <label for="batch_from">Batch Range From:</label>
            <input type="text" id="batch_from" name="batch_from">
        </div>
        <div class="form-group">
            <label for="batch_to">Batch Range To:</label>
            <input type="text" id="batch_to" name="batch_to">
        </div>

        <div class="form-group"> <!-- Added -->
            <label for="serial_number">Job Number:</label>
            <select id="serial_number" name="serial_number">
                <option value="">Select Serial Number</option>
            </select>
        </div>
        <div class="form-group">
            <label for="compound_name">Compound Name:</label>
            <select id="compound_name" name="compound_name">
                <option value="">Select Compound Name</option>
            </select>
        </div>
      
        <input type="submit" value="Fetch Data">
    </form>
</div>

<script>
    // Function to fetch and populate serial number options based on selected date
    function populateSerialNumberOptions(selectedDate) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "get_batch1.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var serialNumbers = JSON.parse(xhr.responseText);
                var serialNumberSelect = document.getElementById("serial_number");
                serialNumberSelect.innerHTML = "<option value=''>Select Serial Number</option>";
                serialNumbers.forEach(function(serialNumber) {
                    var option = document.createElement("option");
                    option.value = serialNumber;
                    option.text = serialNumber;
                    serialNumberSelect.appendChild(option);
                });
            }
        };
        xhr.send("date=" + selectedDate);
    }



    document.getElementById("date").addEventListener("change", function() {
    var selectedDate = this.value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "get_compound_names.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var compoundNames = JSON.parse(xhr.responseText);
            var compoundNameSelect = document.getElementById("compound_name");
            compoundNameSelect.innerHTML = "<option value=''>Select Compound Name</option>";
            compoundNames.forEach(function(compoundName) {
                var option = document.createElement("option");
                option.value = compoundName;
                option.text = compoundName;
                compoundNameSelect.appendChild(option);
            });
        }
    };
    xhr.send("date=" + selectedDate);
});


// Function to fetch and populate serial_number options based on selected date
function populateBatchOptions(selectedDate) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "get_batch.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var batches = JSON.parse(xhr.responseText);
            var batchSelect = document.getElementById("batch");
            batchSelect.innerHTML = "<option value=''>Select Batch</option>";
            batches.forEach(function(batch) {
                var option = document.createElement("option");
                option.value = batch;
                option.text = batch;
                batchSelect.appendChild(option);
            });
        }
    };
    xhr.send("date=" + selectedDate);
}  
   
 
// Event listener for input date change
document.getElementById("date").addEventListener("change", function() {
    var selectedDate = this.value;
    // Call the function to populate serial_number options based on selected date
    populateBatchOptions(selectedDate);
});

    // Event listener for input date change
    document.getElementById("date").addEventListener("change", function() {
        var selectedDate = this.value;
        // Call the function to populate serial number options based on selected date
        populateSerialNumberOptions(selectedDate);
    });


   
</script>

</body>
</html>




