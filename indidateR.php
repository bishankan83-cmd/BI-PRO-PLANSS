<?php
// Database connection settings
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen"; // Change this to your new database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to create the new table
$sqlCreateTable = "
CREATE TABLE IF NOT EXISTS new_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT,
    icode VARCHAR(255),
    start_date DATETIME,
    end_date DATETIME,
    erp VARCHAR(255),
    Customer VARCHAR(255),
    description TEXT
)
";

if ($conn->query($sqlCreateTable) === TRUE) {
    echo "New table created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error;
    $conn->close();
    exit();
}

// Fetch data from the database and separate it
$sql = "SELECT * FROM `plannew` ORDER BY `plan_id`, `start_date`";
$result = $conn->query($sql);

// Separate the data by `plan_id` and store it in an array
$separatedData = array();
while ($row = $result->fetch_assoc()) {
    $planId = $row['plan_id'];
    $startDate = date("Y-m-d", strtotime($row['start_date']));
    $endDate = date("Y-m-d", strtotime($row['end_date']));
    $icode = $row['icode'];

    // If this is the first date of the plan_id and it's not the same as the end_date,
    // set the end_date to 23:59:59
    if (!isset($separatedData[$planId][$icode])) {
        if ($endDate != $startDate) {
            // Do not modify the original $row['end_date']
            $endDateTime = date("Y-m-d 23:59:59", strtotime($row['start_date']));
            $separatedData[$planId][$icode][] = array(
                'date' => $startDate,
                'row' => array(
                    'start_date' => $row['start_date'],
                    'end_date' => $endDateTime,
                    'erp' => $row['erp'],
                    'Customer' => $row['Customer'],
                    'description' => $row['description']
                )
            );
        }
    }

    // For all other dates, set the start_date to 00:00:00 and end_date to 23:59:59
    // and store them in the separatedData array
    $currentDate = $startDate;
    while ($currentDate < $endDate) {
        $currentDate = date("Y-m-d", strtotime($currentDate . ' +1 day'));
        $endDateTime = date("Y-m-d 23:59:59", strtotime($currentDate));
        $separatedData[$planId][$icode][] = array(
            'date' => $currentDate,
            'row' => array(
                'start_date' => date("Y-m-d 00:00:00", strtotime($currentDate)),
                'end_date' => $endDateTime,
                'erp' => $row['erp'],
                'Customer' => $row['Customer'],
                'description' => $row['description']
            )
        );
    }
}

// Now $separatedData contains separate arrays for each `plan_id` and `icode`,
// each containing data for different dates with their respective rows

// Insert data into the new table
foreach ($separatedData as $planId => $icodeData) {
    foreach ($icodeData as $icode => $dateData) {
        foreach ($dateData as $item) {
            $date = $item['date'];
            $row = $item['row'];

            // SQL query to insert data into the new table
            $sqlInsertData = "
            INSERT INTO new_table (plan_id, icode, start_date, end_date, erp, Customer, description)
            VALUES ('{$planId}', '{$icode}', '{$row['start_date']}', '{$row['end_date']}', '{$row['erp']}', '{$row['Customer']}', '{$row['description']}')
            ";

            if ($conn->query($sqlInsertData) !== TRUE) {
                echo "Error inserting data: " . $conn->error;
            }
        }
    }
}



$conn->close();

header("Location: updateindiR.php");
exit();
?>
