<?php
// Database connection
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

// SQL to delete all data from plannew34
$sql = "DELETE FROM `plannew34`";

// Execute the query
if ($conn->query($sql) === TRUE) {
  // echo "All data successfully deleted from plannew34.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>


<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_plann';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919'; // Update this with the actual password
$targetDb = 'planatir_task_managemen';

// Connect to source database
$sourceConn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);
if ($sourceConn->connect_error) {
    die("Source connection failed: " . $sourceConn->connect_error);
}

// Connect to target database
$targetConn = new mysqli($targetHost, $targetUser, $targetPass, $targetDb);
if ($targetConn->connect_error) {
    die("Target connection failed: " . $targetConn->connect_error);
}

// Step 1: Delete existing data in the target table
$deleteQuery = "DELETE FROM plannew";
if ($targetConn->query($deleteQuery) === TRUE) {
   // echo "Existing data in target table deleted successfully.<br>";
} else {
    die("Error deleting data in target table: " . $targetConn->error);
}

// Step 2: Fetch data from source table
$selectQuery = "SELECT * FROM plannew";
$result = $sourceConn->query($selectQuery);

if ($result && $result->num_rows > 0) {
    // Prepare insert query for the target table
    while ($row = $result->fetch_assoc()) {
        $insertQuery = "INSERT INTO plannew (id, plan_id, erp, Customer, icode, description, tobe, press, press_name, mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name, start_date, end_date, tires_per_mold) 
                        VALUES (
                            '{$row['id']}', '{$row['plan_id']}', '{$row['erp']}', '{$row['Customer']}', '{$row['icode']}', '{$row['description']}', '{$row['tobe']}', '{$row['press']}', 
                            '{$row['press_name']}', '{$row['mold_id']}', '{$row['mold_name']}', '{$row['cavity_id']}', '{$row['cavity_name']}', '{$row['cuing_group_id']}', 
                            '{$row['cuing_group_name']}', '{$row['start_date']}', '{$row['end_date']}', '{$row['tires_per_mold']}'
                        )";

        if (!$targetConn->query($insertQuery)) {
            echo "Error inserting data: " . $targetConn->error . "<br>";
        }
    }
   // echo "Data copied successfully.<br>";
} else {
    echo "No data found in source table.<br>";
}

// Close connections
$sourceConn->close();
$targetConn->close();
?>









<?php
// Database connection details
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_plann';

$targetHost = 'localhost';
$targetUser = 'planatir_task_managemen';
$targetPass = 'Bishan@1919'; // Update this with the actual password
$targetDb = 'planatir_task_managemen';

// Connect to source database
$sourceConn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);
if ($sourceConn->connect_error) {
    die("Source connection failed: " . $sourceConn->connect_error);
}

// Connect to target database
$targetConn = new mysqli($targetHost, $targetUser, $targetPass, $targetDb);
if ($targetConn->connect_error) {
    die("Target connection failed: " . $targetConn->connect_error);
}

// Step 1: Delete existing data in the target table
$deleteQuery = "DELETE FROM tobeplan1";
if ($targetConn->query($deleteQuery) === TRUE) {
 //   echo "Existing data in target table deleted successfully.<br>";
} else {
    die("Error deleting data in target table: " . $targetConn->error);
}

// Step 2: Fetch data from source table
$selectQuery = "SELECT * FROM tobeplan1";
$result = $sourceConn->query($selectQuery);

if ($result && $result->num_rows > 0) {
    // Prepare insert query for the target table
    $insertQuery = "INSERT INTO tobeplan1 (id, icode, tobe, erp, stockonhand) VALUES ";

    $insertValues = [];
    while ($row = $result->fetch_assoc()) {
        // Collect data for each row
        $insertValues[] = "('". $row['id'] ."', '". $row['icode'] ."', '". $row['tobe'] ."', '". $row['erp'] ."', '". $row['stockonhand'] ."')";
    }

    // If we have values to insert, construct and execute the insert query
    if (count($insertValues) > 0) {
        $insertQuery .= implode(", ", $insertValues);

        if ($targetConn->query($insertQuery) === TRUE) {
            //echo "Data copied successfully.<br>";
        } else {
            echo "Error inserting data: " . $targetConn->error . "<br>";
        }
    } else {
        echo "No data to insert.<br>";
    }
} else {
    echo "No data found in source table.<br>";
}

// Close connections
$sourceConn->close();
$targetConn->close();
?>






<?php
// Database connection
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

// SQL to insert data from plannew to plannew34
$sql = "INSERT INTO `plannew34` (
            `plan_id`, `erp`, `Customer`, `icode`, `description`, `tobe`, `press`, 
            `press_name`, `mold_id`, `mold_name`, `cavity_id`, `cavity_name`, 
            `cuing_group_id`, `cuing_group_name`, `start_date`, `end_date`, 
            `tires_per_mold`
        )
        SELECT 
            `plan_id`, `erp`, `Customer`, `icode`, `description`, `tobe`, `press`, 
            `press_name`, `mold_id`, `mold_name`, `cavity_id`, `cavity_name`, 
            `cuing_group_id`, `cuing_group_name`, `start_date`, `end_date`, 
            `tires_per_mold`
        FROM `plannew`";

// Execute the query
if ($conn->query($sql) === TRUE) {
    //echo "Data successfully inserted from plannew to plannew34.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
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

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // SQL query to update the `brand` column
    $sql = "
        UPDATE plannew34 p
        JOIN realstock r ON p.icode = r.icode
        SET p.brand = r.brand
        WHERE r.brand IS NOT NULL
    ";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        //echo "Records updated successfully.";
    } else {
        throw new Exception("Error updating records: " . $conn->error);
    }

    // Close connection
    $conn->close();
} catch (Exception $e) {
    // Handle exception
    echo "Error: " . $e->getMessage();
}
?>







<?php
// Database connection details
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

// SQL query to update press_name in plannew34
$sql = "
    UPDATE `plannew34` p34
    JOIN `press_cavity` pc ON p34.`cavity_id` = pc.`cavity_id`
    JOIN `press` pr ON pc.`press_id` = pr.`press_id`
    SET p34.`press_name` = pr.`press_name`
    WHERE pr.`is_available` = 1 AND p34.`cavity_id` = pc.`cavity_id`;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    //echo "Press names updated successfully.";
} else {
    echo "Error updating press names: " . $conn->error;
}

// Close the connection
$conn->close();
?>











<?php
// Database connection
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

// Get unique Mold IDs for dropdown
$mold_ids_result = $conn->query("SELECT DISTINCT mold_id FROM mold ORDER BY id");

// Get unique Mold Sizes for dropdown
$mold_sizes_result = $conn->query("SELECT DISTINCT mold_size FROM mold_list ORDER BY mold_size");

// Get unique Brands for dropdown
$brands_result = $conn->query("SELECT DISTINCT brand FROM plannew34 ORDER BY brand");

// Get selected filters
$selected_mold_id = isset($_POST['mold_id']) ? $_POST['mold_id'] : '';
$selected_mold_size = isset($_POST['mold_size']) ? $_POST['mold_size'] : '';
$selected_brand = isset($_POST['brand']) ? $_POST['brand'] : '';

// Build WHERE clause for filtering   
$where_clauses = [];
if ($selected_mold_id) {
    $where_clauses[] = "mold.mold_id = '$selected_mold_id'";
}
if ($selected_mold_size) {
    $where_clauses[] = "mold_list.mold_size = '$selected_mold_size'";
}
if ($selected_brand) {
    $where_clauses[] = "realstock.brand = '$selected_brand'";
}
$where_clause = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : '';

// Get default date range
$default_sql = "SELECT MIN(DATE(start_date)) as min_date, MAX(DATE(end_date)) as max_date FROM plannew";
$default_result = $conn->query($default_sql);

if (!$default_result) {
    die("Error in default date range query: " . $conn->error);
}
 
$default_range = $default_result->fetch_assoc();
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : $default_range['min_date'];
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : $default_range['max_date'];

// Fetch holiday dates within the selected date range
$holiday_sql = "SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN '$start_date' AND '$end_date'";
$holiday_result = $conn->query($holiday_sql);

if (!$holiday_result) {
    die("Error fetching holidays: " . $conn->error);
}

// Create an array of holiday dates for easy lookup
$holidays = array();
if ($holiday_result->num_rows > 0) {
    while($holiday_row = $holiday_result->fetch_assoc()) {
        $holidays[] = $holiday_row['holiday_date'];
    }
}

$sql = "SELECT 
            mold.id, 
            mold.mold_id, 
            mold_list.mold_size, 
            GROUP_CONCAT(DISTINCT mold_list.icode ORDER BY mold_list.icode SEPARATOR ', ') AS icodes, 
            GROUP_CONCAT(DISTINCT realstock.brand ORDER BY realstock.brand SEPARATOR ', ') AS brands, 
            GROUP_CONCAT(DISTINCT DATE(plannew.start_date), ':', DATE(plannew.end_date) ORDER BY plannew.start_date SEPARATOR '; ') AS dates,
            GROUP_CONCAT(DISTINCT plannew34.brand ORDER BY plannew34.brand SEPARATOR ', ') AS plannew34_brands,
            GROUP_CONCAT(DISTINCT plannew34.press_name ORDER BY plannew34.press_name SEPARATOR ', ') AS press_names
        FROM mold 
        LEFT JOIN plannew ON mold.mold_id = plannew.mold_id 
        LEFT JOIN mold_list ON mold.mold_id = mold_list.mold_id 
        LEFT JOIN realstock ON TRIM(mold_list.icode) = TRIM(realstock.icode) 
        LEFT JOIN plannew34 ON mold.mold_id = plannew34.mold_id
        $where_clause 
        GROUP BY mold.id, mold.mold_id, mold_list.mold_size 
        ORDER BY mold.id";

$result = $conn->query($sql);

if (!$result) {
    die("Error in query: " . $conn->error);
}

// Inline CSS Styles
echo "<style>
    .container {
    margin: 0 auto;
    max-width: 1450px;
    padding: 20px;
    background-color: #f0f0f0;
}
    .schedule-container { 
        overflow-x: auto; 
        margin: 20px;
    }
    table { 
        border-collapse: collapse; 
        width: max-content;
    }
    th{ 
        border: 1px solid #ddd; 
        padding: 8px; 
        text-align: center;
        white-space: nowrap;
    }
    td { 
       border: 1px solid #ddd; 
        padding: 8px; 
        text-align: center;
    }
    th { 
        background-color: #F28018; 
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #f5f5f5;
    }
    th.sticky {
        position: sticky;
        left: 0;
        background-color: #F28018;
        z-index: 2;
    }
    td.sticky {
        position: sticky;
        left: 0;
        background-color: #f2f2f2;
        z-index: 1;
    }
    th.sticky:nth-child(2), td.sticky:nth-child(2) { left: 50px; width: 100px; }
    th.sticky:nth-child(3), td.sticky:nth-child(3) { left: 150px; width: 100px; }
    th.sticky:nth-child(4), td.sticky:nth-child(4) { left: 250px; width: 100px; }
    th.sticky:nth-child(5), td.sticky:nth-child(5) { left: 350px; width: 100px; }
    input[type='submit'] {
        padding: 5px 15px;
        background-color: #000000;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    button {
        background-color: rgb(207, 15, 15);
        padding: 5px 15px;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .holiday {
        background-color: #ffcccc !important;
    }
    th.holiday {
        background-color: #ff9999 !important;
        color: #800000;
    }
</style>";

// Render the title
echo "<div class='container'>
        <h1 style='text-align: center; font-family: Cantarell, sans-serif;'>MOLD UTILIZATION</h1>";
// Render the form with an additional Brand filter
echo "<form method='post'>
        <label><b>Start Date:</b> 
            <input type='date' name='start_date' value='" . htmlspecialchars($start_date) . "' style='padding: 10px; width: 170px; border: 1px solid #CCCCCC; border-radius: 4px; font-family: \"Cantarell\", sans-serif; font-weight: regular;' required>
        </label>
        <label><b>End Date: </b>
            <input type='date' name='end_date' value='" . htmlspecialchars($end_date) . "' style='padding: 10px; width: 170px; border: 1px solid #CCCCCC; border-radius: 4px; font-family: \"Cantarell\", sans-serif; font-weight: regular;' required>
        </label>
        <label><b>Mold ID:</b> 
        <select name='mold_id' style='padding: 10px; width: 180px; border: 1px solid #CCCCCC; border-radius: 4px; font-family: \"Cantarell\", sans-serif; font-weight: regular;'>
            <option value=''>All</option>";

// Populate Mold ID dropdown
if ($mold_ids_result->num_rows > 0) {
    while ($row = $mold_ids_result->fetch_assoc()) {
        $selected = ($selected_mold_id === $row['mold_id']) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($row['mold_id']) . "' $selected>" . htmlspecialchars($row['mold_id']) . "</option>";
    }
}

echo "    </select>
        </label>
        <label><b>Mold Size:</b> 
        <select name='mold_size' style='padding: 10px; width: 170px; border: 1px solid #CCCCCC; border-radius: 4px; font-family: \"Cantarell\", sans-serif; font-weight: regular;'>
            <option value=''>All</option>";

// Populate Mold Size dropdown
if ($mold_sizes_result->num_rows > 0) {
    while ($row = $mold_sizes_result->fetch_assoc()) {
        $selected = ($selected_mold_size === $row['mold_size']) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($row['mold_size']) . "' $selected>" . htmlspecialchars($row['mold_size']) . "</option>";
    }
}

echo "    </select>
        </label>
        <label><b>Brand:</b> 
        <select name='brand' style='padding: 10px; width: 170px; border: 1px solid #CCCCCC; border-radius: 4px; font-family: \"Cantarell\", sans-serif; font-weight: regular;'>
            <option value=''>All</option>";

// Populate Brand dropdown
if ($brands_result->num_rows > 0) {
    while ($row = $brands_result->fetch_assoc()) {
        $selected = ($selected_brand === $row['brand']) ? "selected" : "";
        echo "<option value='" . htmlspecialchars($row['brand']) . "' $selected>" . htmlspecialchars($row['brand']) . "</option>";
    }
}

echo "    </select>
        </label>
        <input type='submit' style='width: 90px; height: 30px;' value='Filter'>
        <button type='button' style='width: 90px; height: 30px;' onclick='window.location.href=\"" . $_SERVER['PHP_SELF'] . "\"'>Clear</button>
      </form>";

// Add legend for holidays
echo "<div style='margin: 10px 0; padding: 5px; background-color: #f9f9f9; border: 1px solid #ddd;'>
        <span style='background-color: #ffcccc; padding: 2px 8px; margin-right: 5px; border: 1px solid #ddd;'>&nbsp;&nbsp;&nbsp;</span>
        <span><b>Holiday</b></span>
      </div>";

// Add this button above the table
echo "<button style='margin-bottom: 2px; background-color: #4CAF50; color: white; border: none; padding: 10px 10px; font-size: 16px; cursor: pointer;' onclick='window.location.href=\"export_to_csv.php?start_date=" . urlencode($start_date) . "&end_date=" . urlencode($end_date) . "&mold_id=" . urlencode($selected_mold_id) . "&mold_size=" . urlencode($selected_mold_size) . "&brand=" . urlencode($selected_brand) . "\"'>Download Excelsheet</button>";

// Render the table
echo "<div class='schedule-container'>";
echo "<table>";
echo "<tr>
        <th class='sticky'>Mold ID</th>
        <th class='sticky'>Mold Size</th>
        <th class='sticky'>Press Names</th>  
        <th class='sticky'>Brand</th>
        <th class='sticky'>Planning Brands</th>";

// Generate headers with unique dates (including holidays with special styling)
$current_date = strtotime($start_date);
$end_timestamp = strtotime($end_date);
$date_indexes = array(); // To keep track of which dates are included in the table
$column_index = 0;

while ($current_date <= $end_timestamp) {
    $current_date_str = date('Y-m-d', $current_date);
    $is_holiday = in_array($current_date_str, $holidays);
    $holiday_class = $is_holiday ? " class='holiday'" : "";
    
    echo "<th$holiday_class>" . date('d-m', $current_date) . "</th>";
    $date_indexes[$current_date_str] = $column_index++;
    
    $current_date = strtotime('+1 day', $current_date);
}

echo "</tr>";

// Fill rows with data
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='sticky' style='background-color: #dc7633;'>" . htmlspecialchars($row['mold_id']) . "</td>";
        echo "<td class='sticky' style='background-color: #eb984e;'>" . htmlspecialchars($row['mold_size']) . "</td>";
        echo "<td class='sticky' style='background-color: #e59866;'>" . htmlspecialchars($row['press_names']) . "</td>"; 
        echo "<td class='sticky' style='background-color: #edbb99;'>" . htmlspecialchars($row['brands']) . "</td>";
        echo "<td class='sticky' style='background-color: #f6ddcc;'>" . htmlspecialchars($row['plannew34_brands']) . "</td>";
        
        // Query to get the per_day value for the selected mold_id
        $per_day_result = $conn->query("SELECT mold_id, per_day FROM mold_list WHERE mold_id = '" . $row['mold_id'] . "' LIMIT 1");

        $per_day_value = 0; // Default value if no result
        if ($per_day_result && $per_day_result->num_rows > 0) {
            $per_day_row = $per_day_result->fetch_assoc();
            $per_day_value = $per_day_row['per_day'];
        }

        // Create an array for all dates in the range (including holidays)
        $current_date = strtotime($start_date);
        $ticks = array();
        
        while ($current_date <= $end_timestamp) {
            $current_date_str = date('Y-m-d', $current_date);
            $ticks[$current_date_str] = '';
            $current_date = strtotime('+1 day', $current_date);
        }

        // Fill ticks for dates when the mold is in use
        if (!empty($row['dates'])) {
            $dates_array = explode('; ', $row['dates']);
            foreach ($dates_array as $date_range) {
                $date_parts = explode(':', $date_range);
                if (count($date_parts) === 2) {
                    $record_start = strtotime($date_parts[0]);
                    $record_end = strtotime($date_parts[1]);
                    
                    $current_date = $record_start;
                    while ($current_date <= $record_end) {
                        $current_date_str = date('Y-m-d', $current_date);
                        // Mark both holiday and non-holiday dates, but only set values for non-holidays
                        if (!in_array($current_date_str, $holidays)) {
                            $ticks[$current_date_str] = $per_day_value;
                        }
                        $current_date = strtotime('+1 day', $current_date);
                    }
                }
            }
        }

        // Output the ticks (per_day values or holiday indicator)
        foreach ($ticks as $date => $tick) {
            $is_holiday = in_array($date, $holidays);
            if ($is_holiday) {
                echo "<td class='holiday'>HOLIDAY</td>";
            } else {
                echo "<td>" . htmlspecialchars($tick) . "</td>";
            }
        }
        
        echo "</tr>";
    }
} else {
    // Calculate the number of columns (including holiday dates)
    $current_date = strtotime($start_date);
    $date_count = 0;
    while ($current_date <= $end_timestamp) {
        $date_count++;
        $current_date = strtotime('+1 day', $current_date);
    }
    $num_columns = 5 + $date_count; // 5 fixed columns + all dates
    echo "<tr><td colspan='$num_columns'>No data found</td></tr>";
}

echo "</table>";
echo "</div>";
echo "</div>"; // Close container div

// Close the database connection
$conn->close();
?>