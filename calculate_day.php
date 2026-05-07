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

// Function to split and display data based on time slots within date range
function displayDataBasedOnTimeSlots($conn) {
    $sql = "SELECT * FROM plannew";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $currentDate = new DateTime($row['start_date']);
            $endDate = new DateTime($row['end_date']);
            
            while ($currentDate <= $endDate) {
                // Define the time slots
                $dayStart = clone $currentDate;
                $dayStart->setTime(7, 0);

                $dayEnd = clone $currentDate;
                $dayEnd->setTime(18, 59, 59);

                $nightStart = clone $currentDate;
                $nightStart->setTime(19, 0);

                $nightEnd = clone $currentDate;
                $nightEnd->setTime(23, 59, 59);

                $midnightStart = clone $currentDate;
                $midnightStart->modify('+1 day');
                $midnightStart->setTime(0, 0, 0);

                $midnightEnd = clone $currentDate;
                $midnightEnd->modify('+1 day');
                $midnightEnd->setTime(00, 59, 59);

                echo "<h2>Data for " . $currentDate->format('Y-m-d') . "</h2>";

                echo "<h3>DAY (7:00 AM to 7:00 PM)</h3>";
                displayRowIfInRange($row, $dayStart, $dayEnd);

                echo "<h3>NIGHT (7:00 PM to 11:59 PM)</h3>";
                displayRowIfInRange($row, $nightStart, $nightEnd);

                echo "<h3>MIDNIGHT (00:00 AM to 7:00 AM)</h3>";
                displayRowIfInRange($row, $midnightStart, $midnightEnd);

                // Move to the next day
                $currentDate->modify('+1 day');
            }
            echo "<hr>";
        }
    } else {
        echo "No results found.";
    }
}

// Function to display the row if it falls within the given range
function displayRowIfInRange($row, $rangeStart, $rangeEnd) {
    $startDateTime = new DateTime($row['start_date']);
    $endDateTime = new DateTime($row['end_date']);
    
    if (($startDateTime >= $rangeStart && $startDateTime <= $rangeEnd) ||
        ($endDateTime >= $rangeStart && $endDateTime <= $rangeEnd) ||
        ($startDateTime <= $rangeStart && $endDateTime >= $rangeEnd)) {
        
        echo "<table border='1'>
                <tr>
                    <th>ID</th>
                    <th>Plan ID</th>
                    <th>ERP</th>
                    <th>Customer</th>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>To Be</th>
                    <th>Press</th>
                    <th>Press Name</th>
                    <th>Mold ID</th>
                    <th>Mold Name</th>
                    <th>Cavity ID</th>
                    <th>Cavity Name</th>
                    <th>Curing Group ID</th>
                    <th>Curing Group Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Tires per Mold</th>
                </tr>
                <tr>
                    <td>{$row['id']}</td>
                    <td>{$row['plan_id']}</td>
                    <td>{$row['erp']}</td>
                    <td>{$row['Customer']}</td>
                    <td>{$row['icode']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['tobe']}</td>
                    <td>{$row['press']}</td>
                    <td>{$row['press_name']}</td>
                    <td>{$row['mold_id']}</td>
                    <td>{$row['mold_name']}</td>
                    <td>{$row['cavity_id']}</td>
                    <td>{$row['cavity_name']}</td>
                    <td>{$row['cuing_group_id']}</td>
                    <td>{$row['cuing_group_name']}</td>
                    <td>{$row['start_date']}</td>
                    <td>{$row['end_date']}</td>
                    <td>{$row['tires_per_mold']}</td>
                </tr>
              </table>";
    } else {
        echo "No results found for this time slot.";
    }
    echo "<br>";
}

// Fetch and display data based on time slots
displayDataBasedOnTimeSlots($conn);

// Close connection
$conn->close();
?>
