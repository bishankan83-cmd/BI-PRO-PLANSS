<?php
// Establish database connection
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Retrieve all unique icodes from the production_plan table
$sql = "SELECT DISTINCT icode FROM production_plan";
$result = mysqli_query($conn, $sql);

// Check if the query executed successfully
if ($result) {
    // Check if there are any rows returned
    if (mysqli_num_rows($result) > 0) {
        echo "<h3>Production Plan for All ICodes</h3>";
        echo "<form method='post' action='savedata.php'>";
        echo "<table>";
        echo "<tr>
            <th>ICode</th>
            <th>Description</th>
            <th>Curing Group</th>
            <th>Press</th>
            <th>Mold</th>
            <th>Cavity</th>
            <th>Order Quantity</th>
            <th>To Be Produced</th>
        </tr>";

        // Iterate over each row in the result set
        while ($row = mysqli_fetch_assoc($result)) {
            $icode = $row['icode'];

            // Retrieve data for the current icode
            $data = getDataForIcode($conn, $icode);

            // Display data in the table row
            echo "<tr>";
            echo "<td>{$data['icode']}</td>";
            echo "<td>{$data['description']}</td>";
            echo "<td>{$data['curingGroup']}</td>";
            echo "<td><select name='press_$icode'>" . getDropdownOptions($data['pressOptions']) . "</select></td>";
            echo "<td><select name='mold_$icode'>" . getDropdownOptions($data['moldOptions']) . "</select></td>";
            echo "<td><select name='cavity_$icode'>" . getDropdownOptions($data['cavityOptions']) . "</select></td>";
            echo "<td>{$data['tireSize']}</td>";
            echo "<td>{$data['tireQuantity']}</td>";
            // ...
            echo "</tr>";
        }

        echo "</table>";
        echo "<button type='submit' name='submit'>Submit</button>";
        echo "</form>";
    } else {
        echo "No data found in the production plan";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);

function getDataForIcode($conn, $icode) {
    $data = array();

    // Retrieve data from the production_plan table for the given icode
    $sql = "SELECT DISTINCT icode, description, curing_group_name FROM production_plan WHERE icode = '$icode'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $data['icode'] = $row['icode'];
        $data['description'] = $row['description'];
        $data['curingGroup'] = $row['curing_group_name'];

        // Retrieve available press options for the tire type
        $data['pressOptions'] = getPressOptions($conn, $icode);

        // Retrieve available mold options for the tire type
        $data['moldOptions'] = getMoldOptions($conn, $icode);

        // Retrieve available cavity options for the tire type
        $data['cavityOptions'] = getCavityOptions($conn, $icode);

        // Retrieve tire quantity for the tire type
        $data['tireQuantity'] = getTireQuantity($conn, $icode);

        // Retrieve tire size for the tire type
        $data['tireSize'] = getTireSize($conn, $icode);

        // ...
    }

    return $data;
}

// Rest of the functions remain unchanged.
// ...
?>
