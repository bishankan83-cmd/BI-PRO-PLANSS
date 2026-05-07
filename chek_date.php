<?php
// Replace these values with your database credentials
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

// Function to check if a date is a holiday in Sri Lanka
function isHoliday($date, $conn) {
    // Query to retrieve holidays from the database
    $holidayQuery = "SELECT holiday_date FROM holidays WHERE holiday_date = '$date'";
    $holidayResult = $conn->query($holidayQuery);

    // Check if the date is a holiday
    return $holidayResult->num_rows > 0;
}

// Example query to retrieve data from the database
$sql = "SELECT plan_id, start_date, end_date FROM plannew1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $startDate = new DateTime($row['start_date']);
        $endDate = new DateTime($row['end_date']);

        // Check each date in the range
        while ($startDate <= $endDate) {
            $currentDate = $startDate->format('Y-m-d');

            if (isHoliday($currentDate, $conn)) {
                echo "Plan ID: " . $row['plan_id'] . " - Date $currentDate is a holiday in Sri Lanka.\n";

                // Add one more day to the end_date for holiday dates
                $newEndDate = new DateTime($row['end_date']);
                $newEndDate->modify('+1 day');

                // Update the record in the database
                $updateSql = "UPDATE plannew1 SET end_date = '" . $newEndDate->format('Y-m-d H:i:s') . "' WHERE plan_id = " . $row['plan_id'];
                if ($conn->query($updateSql) === TRUE) {
                    echo "Plan ID: " . $row['plan_id'] . " - End date updated successfully.\n";
                } else {
                    echo "Error updating end date for plan ID: " . $row['plan_id'] . " - " . $conn->error . "\n";
                }
            }

            $startDate->modify('+1 day'); // Move to the next day
        }
    }
} else {
    echo "No records found";
}

$conn->close();
header("Location: copy_plan.php");
exit();
?>
