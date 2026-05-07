<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedDate = $_POST["inputDate"];
    $selectedShift = $_POST["shift"];

    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM daily_plan WHERE Date = '$selectedDate' AND Shift = '$selectedShift'";
    $result = $conn->query($sql);

    echo "<form method='post' action=''>";
    echo "<table border='1'>";
    echo "<tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Icode</th>
            <th>Description</th>
            <th>MoldName</th>
            <th>CavityName</th>
            <th>Plan</th>
            <th>Actual Production</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['Date']}</td>
            <td>{$row['Shift']}</td>
            <td>{$row['Icode']}</td>
            <td>{$row['Description']}</td>
            <td>{$row['MoldName']}</td>
            <td>{$row['CavityName']}</td>
            <td>{$row['Plan']}</td>
            <td><input type='text' name='additionalData[]'></td>
            <input type='hidden' name='dates[]' value='{$row['Date']}'>
            <input type='hidden' name='shifts[]' value='{$row['Shift']}'>
            <input type='hidden' name='icodes[]' value='{$row['Icode']}'>
            <input type='hidden' name='descriptions[]' value='{$row['Description']}'>
            <input type='hidden' name='moldNames[]' value='{$row['MoldName']}'>
            <input type='hidden' name='cavityNames[]' value='{$row['CavityName']}'>
            <input type='hidden' name='plans[]' value='{$row['Plan']}'>
        </tr>";
    }

    echo "</table>";

    // Include the inputDate and shift in the form
    echo "<input type='hidden' name='inputDate' value='$selectedDate'>";
    echo "<input type='hidden' name='shift' value='$selectedShift'>";

    echo "<input type='submit' value='Submit Data'>";
    echo "</form>";

    if (isset($_POST['additionalData'])) {
        $additionalData = $_POST['additionalData'];
        $dates = $_POST['dates'];
        $shifts = $_POST['shifts'];
        $icodes = $_POST['icodes'];
        $descriptions = $_POST['descriptions'];
        $moldNames = $_POST['moldNames'];
        $cavityNames = $_POST['cavityNames'];
        $plans = $_POST['plans'];

        foreach ($additionalData as $index => $data) {
            $escapedData = $conn->real_escape_string($data);
            $date = $conn->real_escape_string($dates[$index]);
            $shift = $conn->real_escape_string($shifts[$index]);
            $icode = $conn->real_escape_string($icodes[$index]);
            $description = $conn->real_escape_string($descriptions[$index]);
            $moldName = $conn->real_escape_string($moldNames[$index]);
            $cavityName = $conn->real_escape_string($cavityNames[$index]);
            $plan = $conn->real_escape_string($plans[$index]);

            $sql = "INSERT INTO daily_plan_data (Date, Shift, Icode, Description, MoldName, CavityName, Plan, AdditionalData)
                    VALUES ('$date', '$shift', '$icode', '$description', '$moldName', '$cavityName', '$plan', '$escapedData')";

            if ($conn->query($sql) !== TRUE) {
                header("Location: dashboard.php");
                exit();
            }
        }
    }

    $conn->close();
    
  
    
}
?>
