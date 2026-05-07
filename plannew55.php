<!DOCTYPE html>
<html>
<head>
    <title>Production Plan Editor</title>
    <style>
        table {
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h2>Production Plan Editor</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="erp">ERP ID:</label>
        <input type="text" id="erp" name="erp" required>
        <button type="submit">Generate Plan</button>
    </form>



<?php
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Establish database connection
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Retrieve the ERP ID from the form submission
    $erp = isset($_POST['erp']) ? $_POST['erp'] : '';

    // Validate the ERP ID (you can add your own validation logic here)
    if (empty($erp)) {
        die("Please enter a valid ERP ID");
    }

    // Sanitize the ERP ID to prevent SQL injection
    $erp = mysqli_real_escape_string($conn, $erp);

    // Retrieve the data from the production_plan table for the given ERP ID
    $sql = "SELECT DISTINCT icode, description FROM production_plan WHERE erp = '$erp'";
    $result = mysqli_query($conn, $sql);

    // Check if the query executed successfully
    if ($result) {
        // Check if there are any rows returned
        if (mysqli_num_rows($result) > 0) {
            echo "<h3>Selected Values:</h3>";
            echo "<table>";
            echo "<tr><th>ICode</th><th>Description</th><th>Press</th><th>Mold</th><th>Cavity</th><th>Time Taken</th></tr>";

            // Iterate over each row in the result set
            while ($row = mysqli_fetch_assoc($result)) {
                $icode = $row['icode'];
                $description = $row['description'];

                // Retrieve the selected press value
                $press = isset($_POST['press_' . $icode]) ? $_POST['press_' . $icode] : '';

                // Retrieve the selected mold value
                $mold = isset($_POST['mold_' . $icode]) ? $_POST['mold_' . $icode] : '';

                // Retrieve the selected cavity value
                $cavity = isset($_POST['cavity_' . $icode]) ? $_POST['cavity_' . $icode] : '';

                // Calculate the time taken based on the selected press, mold, and cavity
                $timeTaken = calculateTimeTaken($conn, $erp, $icode, $press, $mold, $cavity);

                // Update the start and end dates in the production_plan table
                updateStartEndDates($conn, $erp, $icode, $timeTaken);

                echo "<tr>";
                echo "<td>$icode</td>";
                echo "<td>$description</td>";
                echo "<td>$press</td>";
                echo "<td>$mold</td>";
                echo "<td>$cavity</td>";
                echo "<td>$timeTaken</td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "No data found in the production plan for ERP: $erp";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}

// Function to calculate the time taken based on the selected press, mold, and cavity
function calculateTimeTaken($conn, $erp, $icode, $press, $mold, $cavity) {
    $sql = "SELECT press.availability_date AS press_date, mold.availability_date AS mold_date, cavity.availability_date AS cavity_date
            FROM press
            JOIN mold ON press.press = mold.press
            JOIN cavity ON mold.mold = cavity.mold
            WHERE press.press = '$press' AND mold.mold = '$mold' AND cavity.cavity = '$cavity' AND press.erp = '$erp'";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $pressDate = strtotime($row['press_date']);
        $moldDate = strtotime($row['mold_date']);
        $cavityDate = strtotime($row['cavity_date']);

        // Calculate the time taken in seconds
        $timeTaken = max($pressDate, $moldDate, $cavityDate) - min($pressDate, $moldDate, $cavityDate);

        // Convert the time taken to hours and minutes
        $hours = floor($timeTaken / 3600);
        $minutes = floor(($timeTaken % 3600) / 60);

        return sprintf("%02d:%02d", $hours, $minutes);
    }

    return '';
}

// Function to update the start and end dates in the production_plan table
function updateStartEndDates($conn, $erp, $icode, $timeTaken) {
    $sql = "UPDATE slected_plan SET start_date = NOW(), end_date = DATE_ADD(start_date, INTERVAL '$timeTaken' HOUR_MINUTE) WHERE erp = '$erp' AND icode = '$icode'";
    mysqli_query($conn, $sql);
}
?>
