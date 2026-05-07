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
                    <p style="margin: 10px 0 0; font-size: 16px;">The Planning Department is currently updating the Work Orders. Please check back soon for the latest information. Thank you for your patience</p>
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



<!DOCTYPE html>
<html>
<head>

<script>
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                window.location.href = 'planbuttoon.php';
            }
        });
    </script>


    <title>Display Data</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
            background-color: #ffffff;
        }

        h3 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
        }

        h6 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            font-size: 12px;
        }

        h4 {
    font-family: 'Cantarell', sans-serif;
    font-weight: bold; /* Makes text bold */
    color: #000000;
    font-size: 16px; /* Increased font size */
    text-align: left; /* Aligns text to the left */
    padding-left: 10px; /* Adds space from the left */
    /* Or use margin-left: 10px; if you prefer */
}


        h5 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 15px;
            padding: 5px;
            background-color: light black;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% {
                opacity: 1;
            }
            25%, 75% {
                opacity: 0;
            }
        }

        h8 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
        }

        .cargo-loading-date {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
            color: #F28018;
            padding: 5px;
            font-size: 16px;
            background-color: black;
            border: 1px dashed gray;
            border-radius: 10px;
        }

        .button-container {
            text-align: left;
        }

        .top-button {
            background-color: #F28018;
            color: #000000;
            padding: 10px 20px;
            border: none;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .label-container {
            text-align: center;
            background-color: #F28018;
            color: #000000;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        .production-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .production-table th, .production-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .production-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
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

        @keyframes blink {
            0% { visibility: visible; }
            50% { visibility: hidden; }
            100% { visibility: visible; }
        }

        .blinking-text {
            animation: blink 1s infinite;
        }
    
    </style>
 
 <div class="button-container">
        <button><a href="planbuttoon.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a></button>
    </div>

 <!-- Button container in the top-left corner -->






<!DOCTYPE html>
<html>
<head>
    <title>Button Redirect</title>
</head>
<body>

<!-- Create a button that redirects to another page -->
<form method="post">
    <input type="submit" name="submit" value="Check Date Range ">
</form>

<?php
// Check if the form has been submitted
if(isset($_POST['submit'])){
    // Redirect to another page
    header("Location: testing789.php");
    exit; // Make sure that subsequent code is not executed
}
?>

</body>


</html>

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

// Step 1: Delete entries from complete_date where erp exists in both complete_date and plannew
$deleteSQL = "DELETE cd
FROM complete_date cd
JOIN plannew pn ON cd.erp = pn.erp";

// Execute the delete query
if ($conn->query($deleteSQL) === TRUE) {
   // echo "Records deleted successfully from complete_date.<br>";
} else {
    //echo "Error deleting records: " . $conn->error . "<br>";
}

// Optional Step 2: Insert the latest end_date for each ERP into the complete_date table
$insertSQL = "INSERT INTO `complete_date` (`erp`, `com_date`)
SELECT `erp`, MAX(`end_date`) AS `last_end_date`
FROM `plannew`
GROUP BY `erp`
ON DUPLICATE KEY UPDATE `com_date` = VALUES(`com_date`)";

// Execute the insert query
if ($conn->query($insertSQL) === TRUE) {
    //echo "Records inserted/updated successfully into complete_date.<br>";
} else {
   // echo "Error inserting/updating records: " . $conn->error . "<br>";
}

// Close connection
$conn->close();
?>












<?php

$servername = "localhost"; // Assuming the default port for MySQL is 3306
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

try {
    // Connect to your database
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the SQL query
    $sql = "UPDATE plannew
            JOIN press_cavity ON plannew.cavity_id = press_cavity.cavity_id
            JOIN press ON press_cavity.press_id = press.press_id
            SET plannew.press_name = press.press_name";

    // Execute the SQL query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
   // echo "Update successful!";
} catch (PDOException $e) {
    // Handle database errors
    echo "Error: " . $e->getMessage();
}
?>

<?php
// Database connection parameters
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

// SQL statement to update the kgs column
$sql = "UPDATE `worder72` SET `kgs` = REPLACE(`kgs`, ',', '')";

// Execute SQL statement
if ($conn->query($sql) === TRUE) {
    //echo "Kgs column updated successfully";
} else {
    //echo "Error updating kgs column: " . $conn->error;
}

// Close connection
$conn->close();
?>


 
<?php
// Database connection parameters
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

// SQL statement to update the kgs column
$sql = "UPDATE `worder` SET `kgs` = REPLACE(`kgs`, ',', '')";

// Execute SQL statement
if ($conn->query($sql) === TRUE) {
    //echo "Kgs column updated successfully";
} else {
    //echo "Error updating kgs column: " . $conn->error;
}

// Close connection
$conn->close();
?>

<?php
// Database connection parameters
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

// SQL statement to update the kgs column
$sql = "UPDATE `dwork2` SET `kgs` = REPLACE(`kgs`, ',', '')";

// Execute SQL statement
if ($conn->query($sql) === TRUE) {
    //echo "Kgs column updated successfully";
} else {
    //echo "Error updating kgs column: " . $conn->error;
}

// Close connection
$conn->close();
?>
 





















 


<!DOCTYPE html>
<html>
<head>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 20px auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Enable horizontal scrolling */
        }

        table {
            
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed; /* Fixed layout to enable fixed table headers */
        }

        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
            white-space: nowrap; /* Prevent text wrapping */
        }

        th {
            background-color: #f28018;
            color: #ffffff;
            font-weight: bold;
            position: sticky; /* Sticky position to fix headers */
            top: 0; /* Position headers at the top */
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f2f2f2;
        }
</style>
</head>
<body>
<script>
        // Function to highlight the screen in green if the value in the "To be Produce (Nos)" column is 0
        function highlightIfZeroOrIncomplete() {
            var table = document.querySelector('table'); // Select the table element
            var rows = table.querySelectorAll('tr'); // Select all rows of the table

            // Loop through each row (start from 1 to skip the header row)
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].querySelectorAll('td'); // Select all cells of the current row
                //var toBeProduceValue = parseInt(cells[6].innerText); // Get the value of "To be Produce (Nos)"
                var productionCompleteDate = cells[11].innerText; // Get the value of "Production Complete Date"


                // If the production complete date is "0000-00-00", apply yellow background to the entire row
                if (productionCompleteDate === '0000-00-00') {
                    rows[i].style.backgroundColor = '#FFFF00'; // Yellow color
                }
            }
        }

        // Call the function when the page is fully loaded
        window.onload = function() {
            highlightIfZeroOrIncomplete();
        };
</script>


<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);



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

// Updated SQL query to include the WO Release Date from the dwork2 table, the Dispatch Date from the pros table, the com_date from the complete_date table, and the country from the country table
$sql5 = "SELECT pros.erp_number, 
                GROUP_CONCAT(DISTINCT dwork2.wono SEPARATOR ', ') AS wonos,
                GROUP_CONCAT(DISTINCT dwork2.ref SEPARATOR ', ') AS refs,
                SUM(dwork2.quantity) AS total_quantity,
                SUM(dwork2.kgs) AS total_quantity_kgs,
                MAX(dwork2.date) AS wo_release_date,


                

                pros.dispatch_date,
                complete_date.com_date AS production_complete_date,
                country.country AS country
         FROM pros 
         LEFT JOIN dwork2 ON pros.erp_number = dwork2.erp
         LEFT JOIN complete_date ON pros.erp_number = complete_date.erp
         LEFT JOIN country ON pros.erp_number = country.erp
         WHERE MONTH(pros.dispatch_date) = MONTH(CURRENT_DATE()) 
         AND YEAR(pros.dispatch_date) = YEAR(CURRENT_DATE())
         GROUP BY pros.erp_number, pros.dispatch_date, complete_date.com_date, country.country
         ORDER BY pros.dispatch_date ASC";
$result5 = $conn->query($sql5);

$currentMonthData = array();
if ($result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        $currentMonthData[] = $row;
    }
}

// Display ERP, corresponding Work Order Numbers, Total Quantities, References, Quantity in Kgs, WO Release Date, Production Complete Date, Dispatch Date, and Country related to this month


// Variable to keep track of row numbers
$rowNumber = 1;

// Display ERPs, Work Order Numbers, Total Quantities, References, Quantity in Kgs, WO Release Date, Production Complete Date, Dispatch Date, and Country related to this month
foreach ($currentMonthData as $data) {
    // Increment row number
}

$sql1 = "SELECT 
            wo.erp, 
            wr.ref, 
            wr.wono,
            SUM(wr.new) AS total_new,
            DATE(wo.take_datetime) AS date,
            c.country
        FROM 
            work_order wo
            INNER JOIN worder wr ON wo.erp = wr.erp
            LEFT JOIN country c ON wo.erp = c.erp
        GROUP BY 
            wo.erp, 
            wr.ref";

$result1 = $conn->query($sql1);

// Fetching data from new_tobeplan_data table
$sql2 = "SELECT 
            erp, 
            SUM(CASE WHEN tobe > 0 THEN tobe ELSE 0 END) AS total_positive_tobe
        FROM 
            tobeplan1
        GROUP BY 
            erp";

$result2 = $conn->query($sql2);

// Fetching sum of kgs values corresponding to each erp in the worder table
$sql3 = "SELECT 
            erp, 
            SUM(kgs) AS total_kgs
        FROM 
            worder
        GROUP BY 
            erp";

$result3 = $conn->query($sql3);

// Fetching last end_date for each erp from new_plan_data table
$sql4 = "SELECT erp, MAX(end_date) AS last_end_date FROM plannew GROUP BY erp";
$result4 = $conn->query($sql4);

// Combine results into one array
$data = array();

// Processing result from first query
if ($result1->num_rows > 0) {
    while ($row = $result1->fetch_assoc()) {
        $erp = $row['erp'];
        if (!isset($data[$erp])) {
            $data[$erp] = array(
                'ref' => $row['ref'],
                'wono' => $row['wono'],
                'total_new' => $row['total_new'],
                'total_positive_tobe' => 0,
                'total_kgs' => 0,
                'date' => $row['date'],
                'last_end_date' => '',
                'cargo_ready_date' => '',
                'country' => $row['country']
            );
        }
    }
}

// Function to process result from queries 2, 3, and 4
function processData($result, $data, $key) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $erp = $row['erp'];
            if (isset($data[$erp])) {
                $data[$erp][$key] = $row[$key];
            } else {
                $data[$erp] = array(
                    'ref' => '',
                    'wono' => '',
                    'total_new' => 0,
                    'total_positive_tobe' => 0,
                    'total_kgs' => 0,
                    'last_end_date' => '',
                    'cargo_ready_date' => '',
                    'country' => ''
                );
                $data[$erp][$key] = $row[$key];
            }
        }
    }
    return $data;
}

$data = processData($result2, $data, 'total_positive_tobe');
$data = processData($result3, $data, 'total_kgs');
$data = processData($result4, $data, 'last_end_date');

// Fetch completion dates for each ERP
$sql5 = "SELECT erp, com_date FROM complete_date";
$result5 = $conn->query($sql5);
$completion_dates = array();

if ($result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        $erp = $row['erp'];
        $completion_dates[$erp] = $row['com_date'];
    }
}

// Further processing for cargo_ready_date
foreach ($data as $erp => $row) {
    // Fetch completion date for current ERP if available
    $completion_date = isset($completion_dates[$erp]) ? $completion_dates[$erp] : '';

    if (!empty($completion_date)) {
        // Calculate cargo ready date based on completion date + 3 days
        $cargo_ready_date = date('Y-m-d', strtotime($completion_date . ' +3 days'));

        // Update the cargo_ready_date in the data array
        $data[$erp]['cargo_ready_date'] = $cargo_ready_date;
    }
}

// Function to reorder the data array based on the "To be Produce (Nos)" column
function sortByToBeProduce($a, $b) {
    if ($a['total_positive_tobe'] == 0 && $b['total_positive_tobe'] != 0) {
        return -1; // $a comes first if its "To be Produce (Nos)" is 0
    } elseif ($a['total_positive_tobe'] != 0 && $b['total_positive_tobe'] == 0) {
        return 1; // $b comes first if its "To be Produce (Nos)" is 0
    } else {
        return 0; // maintain the current order for other cases
    }
}

// Sort the data array using the custom sorting function
uasort($data, 'sortByToBeProduce');

$rowNumber = 1; // Initialize row number

foreach ($data as $erp => $row) {
    // Calculate completed nos
    $completed_nos = $row['total_new'] - $row['total_positive_tobe'];
    // Format total kgs with commas
    $total_kgs_formatted = number_format($row['total_kgs']);
    
    // Fetch completion date for current ERP if available
    $completion_date = isset($completion_dates[$erp]) ? $completion_dates[$erp] : '';
    
     // Increment row number
}

echo "</table>";

$conn->close();
?>

<script>
function redirectToAnotherPage(erpNumber) {
    // Redirect to another page with the ERP number as a parameter
    window.location.href = 'planning3.php?erp=' + encodeURIComponent(erpNumber);
}
</script>

</body>
</html>








<?php
// Database connection parameters
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

// SQL query to delete all data from the production_data table
$sql = "DELETE FROM production_data";

// Execute the query
if ($conn->query($sql) === TRUE) {
   // echo "All data deleted successfully.";
} else {
    //echo "Error deleting data: " . $conn->error;
}

// Close the connection
$conn->close();
?>

 











<!DOCTYPE html>
<html>
<head>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 20px auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Enable horizontal scrolling */
        }

        table {
            
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed; /* Fixed layout to enable fixed table headers */
        }

        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
            white-space: nowrap; /* Prevent text wrapping */
        }

        th {
            background-color: #f28018;
            color: #ffffff;
            font-weight: bold;
            position: sticky; /* Sticky position to fix headers */
            top: 0; /* Position headers at the top */
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f2f2f2;
        }
</style>
</head>
<body>
<script>
        // Function to highlight the screen in green if the value in the "To be Produce (Nos)" column is 0
        function highlightIfZeroOrIncomplete() {
            var table = document.querySelector('table'); // Select the table element
            var rows = table.querySelectorAll('tr'); // Select all rows of the table

            // Loop through each row (start from 1 to skip the header row)
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].querySelectorAll('td'); // Select all cells of the current row
                var toBeProduceValue = parseInt(cells[6].innerText); // Get the value of "To be Produce (Nos)"
                var productionCompleteDate = cells[10].innerText; // Get the value of "Production Complete Date"


                /// If the value is 0, apply green background to the entire row
                if (toBeProduceValue === 0) {
                    rows[i].style.backgroundColor = '#00FF00'; // Green color
                }

                // If the production complete date is "0000-00-00", apply yellow background to the entire row
              //  if (productionCompleteDate === '') {
                 //   rows[i].style.backgroundColor = '#FFFF00'; // Yellow color
                //}
            }
        }

        // Call the function when the page is fully loaded
        window.onload = function() {
            highlightIfZeroOrIncomplete();
        };
</script>
<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);



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

// Updated SQL query to include the WO Release Date from the dwork2 table, the Dispatch Date from the pros table, the com_date from the complete_date table, and the country from the country table
$sql5 = "SELECT pros.erp_number, 
                GROUP_CONCAT(DISTINCT dwork2.wono SEPARATOR ', ') AS wonos,
                GROUP_CONCAT(DISTINCT dwork2.ref SEPARATOR ', ') AS refs,
                SUM(dwork2.quantity) AS total_quantity,
                SUM(dwork2.kgs) AS total_quantity_kgs,
                MAX(dwork2.date) AS wo_release_date,


                

                pros.dispatch_date,
                complete_date.com_date AS production_complete_date,
                country.country AS country
         FROM pros 
         LEFT JOIN dwork2 ON pros.erp_number = dwork2.erp
         LEFT JOIN complete_date ON pros.erp_number = complete_date.erp
         LEFT JOIN country ON pros.erp_number = country.erp
         WHERE MONTH(pros.dispatch_date) = MONTH(CURRENT_DATE()) 
         AND YEAR(pros.dispatch_date) = YEAR(CURRENT_DATE())
         GROUP BY pros.erp_number, pros.dispatch_date, complete_date.com_date, country.country
         ORDER BY pros.dispatch_date ASC";
$result5 = $conn->query($sql5);

$currentMonthData = array();
if ($result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        $currentMonthData[] = $row;
    }
}



// Variable to keep track of row numbers
$rowNumber = 1;



// Define the SQL queries to fetch data
// Define the SQL queries to fetch data
$sql1 = "SELECT 
            wo.erp, 
            wr.ref, 
            wr.wono,
            SUM(wr.new) AS total_new,
            DATE(wo.take_datetime) AS date,
            c.country
        FROM 
            work_order wo
            INNER JOIN worder wr ON wo.erp = wr.erp
            LEFT JOIN country c ON wo.erp = c.erp
        GROUP BY 
            wo.erp, 
            wr.ref";

$sql2 = "SELECT 
            erp, 
            SUM(CASE WHEN tobe > 0 THEN tobe ELSE 0 END) AS total_positive_tobe
        FROM 
            tobeplan1
        GROUP BY 
            erp";

$sql3 = "SELECT 
            erp, 
            SUM(kgs) AS total_kgs
        FROM 
            worder
        GROUP BY 
            erp";

$sql4 = "SELECT erp, MAX(end_date) AS last_end_date FROM plannew GROUP BY erp";

$sql5 = "SELECT erp, com_date FROM complete_date";



// Execute SQL queries
$result1 = $conn->query($sql1);
$result2 = $conn->query($sql2);
$result3 = $conn->query($sql3);
$result4 = $conn->query($sql4);
$result5 = $conn->query($sql5);

// Combine results into one array
$data = array();

// Processing result from the first query
if ($result1->num_rows > 0) {
    while ($row = $result1->fetch_assoc()) {
        $erp = $row['erp'];
        if (!isset($data[$erp])) {
            $data[$erp] = array(
                'ref' => $row['ref'],
                'wono' => $row['wono'],
                'total_new' => $row['total_new'],
                'total_positive_tobe' => 0,
                'total_kgs' => 0,
                'date' => $row['date'],
                'last_end_date' => '',
                'cargo_ready_date' => '',
                'country' => $row['country']
            );
        }
    }
}



// Process results from queries 2, 3, and 4
$data = processData($result2, $data, 'total_positive_tobe');
$data = processData($result3, $data, 'total_kgs');
$data = processData($result4, $data, 'last_end_date');
// Fetch completion dates for each ERP
// Fetch completion dates for each ERP
$completion_dates = array();
if ($result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        $erp = @$row['erp']; // Suppress the warning
        $com_date = @$row['com_date']; // Suppress the warning
        if (isset($erp) && isset($com_date)) {
            $completion_dates[$erp] = $com_date;
        }
    }
}


// Further processing for cargo_ready_date
foreach ($data as $erp => $row) {
    // Fetch completion date for current ERP if available
    $completion_date = isset($completion_dates[$erp]) ? $completion_dates[$erp] : '';

    if (!empty($completion_date)) {
        // Calculate cargo ready date based on completion date + 3 days
        $cargo_ready_date = date('Y-m-d', strtotime($completion_date . ' +3 days'));

        // Update the cargo_ready_date in the data array
        $data[$erp]['cargo_ready_date'] = $cargo_ready_date;
    }
}


// Sort the data array using the custom sorting function
uasort($data, 'sortByToBeProduce');

// Insert data into the database table
foreach ($data as $erp => $row) {
    // Calculate completed nos
    $completed_nos = $row['total_new'] - $row['total_positive_tobe'];
    // Construct the SQL insert statement
    $insertQuery = "INSERT INTO production_data (erp, work_order_no, customer_order_reference, country, quantity_nos, to_be_produced_nos, completed_nos, quantity_kgs, wo_release_date, production_complete_date, cargo_ready_date) VALUES ('{$erp}', '{$row['wono']}', '{$row['ref']}', '{$row['country']}', '{$row['total_new']}', '{$row['total_positive_tobe']}', '{$completed_nos}', '{$row['total_kgs']}', '{$row['date']}', '{$completion_dates[$erp]}', '{$row['cargo_ready_date']}')";
    
    // Perform the database insertion
    if ($conn->query($insertQuery) !== TRUE) {
       // echo "Error: " . $insertQuery . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>







 










<?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get all ERPs from process_plan table
$processPlansQuery = "SELECT DISTINCT erp FROM process_plan";
$processPlansResult = $conn->query($processPlansQuery);
$processPlannedErps = [];

if ($processPlansResult->num_rows > 0) {
    while ($row = $processPlansResult->fetch_assoc()) {
        $processPlannedErps[] = $row['erp'];
    }
}

// First SQL query to fetch data from multiple tables
$sql5 = "SELECT pros.erp_number, 
                GROUP_CONCAT(DISTINCT dwork2.wono SEPARATOR ', ') AS wonos,
                GROUP_CONCAT(DISTINCT dwork2.ref SEPARATOR ', ') AS refs,
                SUM(dwork2.quantity) AS total_quantity,
                SUM(dwork2.kgs) AS total_quantity_kgs,
                MAX(dwork2.date) AS wo_release_date,
                pros.dispatch_date,
                complete_date.com_date AS production_complete_date,
                country.country AS country
         FROM pros 
         LEFT JOIN dwork2 ON pros.erp_number = dwork2.erp
         LEFT JOIN complete_date ON pros.erp_number = complete_date.erp
         LEFT JOIN country ON pros.erp_number = country.erp
         WHERE MONTH(pros.dispatch_date) = MONTH(CURRENT_DATE()) 
         AND YEAR(pros.dispatch_date) = YEAR(CURRENT_DATE())
         GROUP BY pros.erp_number, pros.dispatch_date, complete_date.com_date, country.country
         ORDER BY pros.dispatch_date ASC";

$result5 = $conn->query($sql5);

// Second SQL query to select data ordered by production_complete_date
$sql = "SELECT * FROM production_data ORDER BY production_complete_date ASC";
$result = $conn->query($sql);

// Combined results array
$combinedResults = [];

// Fetch results from the first query
if ($result5->num_rows > 0) {
    while ($row = $result5->fetch_assoc()) {
        // Calculate cargo ready date as 3 days after production_complete_date
        $cargo_ready_date = '';
        if (!empty($row['production_complete_date'])) {
            $cargo_ready_date = date('Y-m-d', strtotime($row['production_complete_date'] . ' +3 days'));
        }
        
        $combinedResults[] = [
            'erp_number' => $row['erp_number'],
            'wonos' => $row['wonos'],
            'refs' => $row['refs'],
            'country' => $row['country'],
            'total_quantity' => $row['total_quantity'],
            'total_quantity_kgs' => $row['total_quantity_kgs'],
            'wo_release_date' => $row['wo_release_date'],
            'dispatch_date' => $row['dispatch_date'],
            'production_complete_date' => $row['production_complete_date'],
            'cargo_ready_date' => $cargo_ready_date,
            'to_be_produced_nos' => '',
            'completed_nos' => $row['total_quantity'],
        ];
    }
}

// Fetch results from the second query and include new columns
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $combinedResults[] = [
            'erp_number' => $row['erp'],
            'wonos' => $row['work_order_no'],
            'refs' => $row['customer_order_reference'],
            'country' => $row['country'],
            'total_quantity' => $row['quantity_nos'],
            'total_quantity_kgs' => $row['quantity_kgs'],
            'wo_release_date' => $row['wo_release_date'],
            'dispatch_date' => 'Pending',
            'production_complete_date' => $row['production_complete_date'],
            'cargo_ready_date' => $row['cargo_ready_date'],
            'to_be_produced_nos' => $row['to_be_produced_nos'],
            'completed_nos' => $row['completed_nos'],
        ];
    }
}

// Initialize total counters
$totalQuantityNos = 0;
$totalToBeProducedNos = 0;
$totalCompletedNos = 0;
$totalQuantityKgs = 0;

// Calculate totals from combined results
foreach ($combinedResults as $row) {
    $totalQuantityNos += (int)$row['total_quantity'];
    $totalToBeProducedNos += (int)($row['to_be_produced_nos'] ?? 0);
    $totalCompletedNos += (int)($row['completed_nos'] ?? 0);
    $totalQuantityKgs += (int)$row['total_quantity_kgs'];
}

// Button to export data to Excel
echo '<button onclick="exportToExcel()" class="export-button">Export to Excel</button>';

// Display totals in a styled section
echo "<div class='total-summary'>
        <h2>Total Summary</h2>
        <div class='summary-grid'>
            <div class='summary-item'><strong>Total Quantity (Nos):</strong> " . number_format($totalQuantityNos) . "</div>
            <div class='summary-item'><strong>Total To be Produced (Nos):</strong> " . number_format($totalToBeProducedNos) . "</div>
            <div class='summary-item'><strong>Total Completed (Nos):</strong> " . number_format($totalCompletedNos) . "</div>
            <div class='summary-item'><strong>Total Quantity (Kgs):</strong> " . number_format($totalQuantityKgs) . "</div>
        </div>
      </div>";

// Output data in a single HTML table
echo "<table border='1'>";
echo "<tr>
        <th>#</th>
        <th>ERP</th>
        <th>Work Order No</th>
        <th>Customer Order <br> Reference</th>
        <th>Country</th>
        <th>Quantity <br>(Nos)</th>
        <th>To be <br> Produce <br>(Nos)</th>
        <th>Completed <br>(Nos)</th>
        <th>Quantity <br>(Kgs)</th>
        <th>WO Release <br> Date</th>
        <th>Production <br> Complete <br>Date</th>
        <th>Cargo Ready <br> Date</th>
        <th>Dispatch <br> Date</th>
        <th>Dispatch Month</th>
        <th>Check Order</th>
      </tr>";

// Get next month
$nextMonth = date('n', strtotime('first day of next month'));
$nextYear = date('Y', strtotime('first day of next month'));

// Check if combined results are available
if (!empty($combinedResults)) {
    foreach ($combinedResults as $index => $row) {
        // Determine row highlighting
        $highlight = '';
        
        // Check if ERP exists in process_plan table
        if (in_array($row['erp_number'], $processPlannedErps)) {
            $highlight = 'background-color:rgb(253, 255, 153);'; // Yellow
        }
        // Check for tobeplan1
        elseif ($row['erp_number'] === 'process_plan') {
            $highlight = 'background-color: #FFFF99;'; // Yellow
        }
        // Check for next month
        elseif (date('n', strtotime($row['production_complete_date'])) == $nextMonth && 
                date('Y', strtotime($row['production_complete_date'])) == $nextYear) {
            $highlight = 'background-color: #ADD8E6;'; // Light blue
        }
        // Check for missing to_be_produced_nos
        elseif (empty($row['to_be_produced_nos'])) {
            $highlight = 'background-color: #FFCCCC;'; // Light red
        }
    
        echo "<tr style='$highlight'>
                <td>" . ($index + 1) . "</td>
                <td>" . $row['erp_number'] . "</td>
                <td>" . $row['wonos'] . "</td>
                <td>" . $row['refs'] . "</td>
                <td>" . $row['country'] . "</td>
                <td>" . $row['total_quantity'] . "</td>
                <td>" . $row['to_be_produced_nos'] . "</td>
                <td>" . $row['completed_nos'] . "</td>
                <td>" . number_format($row['total_quantity_kgs']) . "</td> 
                <td>" . $row['wo_release_date'] . "</td>
                <td>" . $row['production_complete_date'] . "</td>
                <td>" . $row['cargo_ready_date'] . "</td>
                <td>" . $row['dispatch_date'] . "</td>
                <td>" . date('F', strtotime($row['production_complete_date'] ?? '')) . "</td>
                <td><button onclick='redirectToAnotherPage(\"{$row['erp_number']}\")'>Check</button></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='15'>No results found.</td></tr>";
}

echo "</table>";
?>

<style>
/* Global Styles */
.export-button {
    padding: 10px 20px;
    background-color: #F28018;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin: 20px 0;
}

.total-summary {
    margin: 20px 0;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: gray;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.summary-item {
    padding: 10px;
    background-color: #e0f7fa;
    border: 1px solid orange;
    border-radius: 5px;
}
</style>

<script>
function redirectToAnotherPage(erpNumber) {
    window.location.href = 'planning3.php?erp=' + erpNumber;
}

function exportToExcel() {
    let table = document.querySelector('table');
    let tableHtml = table.outerHTML;
    let blob = new Blob([tableHtml], { type: 'application/vnd.ms-excel' });
    let url = URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    a.download = 'production_data.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>





<?php
// Database connection parameters (RECOMMENDED: Use environment variables or secure configuration)
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

// Prepare the aggregation query
$query = "SELECT 
    erp,
    ref,
    wono,
    Customer,
    SUM(COALESCE(new, 0)) AS total_new,
    SUM(COALESCE(CAST(REPLACE(kgs, ',', '') AS DECIMAL), 0)) AS total_kgs 
FROM 
    worder72 
GROUP BY 
    erp,
    ref,
    wono,
    Customer 
ORDER BY 
    total_new DESC,
    total_kgs DESC";

// Execute the query
$result = $conn->query($query);

// Check if query was successful
if ($result) {
    // Display headline
    echo "<h4>HOLD WORK ORDERS</h4>";

    // Display results in a table format
    echo "<table border='1'>
            <tr>
                <th>ERP</th>
                <th>Reference</th>
                <th>Work Order</th>
                <th>Customer</th>
                <th>Total New</th>
                <th>Total KGS</th>
            </tr>";

    // Fetch and display results
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['erp'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['ref'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['wono'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['Customer'] ?? 'N/A') . "</td>";
        echo "<td>" . number_format($row['total_new'], 0) . "</td>";
        echo "<td>" . number_format($row['total_kgs'], 2) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    // Free result set
    $result->free();
} else {
    echo "Error: " . $conn->error;
}

// Close connection
$conn->close();
?>










