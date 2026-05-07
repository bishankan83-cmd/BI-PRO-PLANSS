<?php
// Establish database connection
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the ERP ID from the form submission
    $erp = isset($_POST['erp']) ? $_POST['erp'] : '';

    // Validate the ERP ID (you can add your own validation logic here)
    if (empty($erp)) {
        die("Please enter a valid ERP ID");
    }

    // Sanitize the ERP ID to prevent SQL injection
    $erp = mysqli_real_escape_string($conn, $erp);

    // Generate Production Plan

    // Retrieve the tire IDs, quantities, and descriptions for the ERP, excluding negative quantities
    $sql = "SELECT wt.icode, wt.tobe, t.description
        FROM tobeplan wt
        INNER JOIN tire t ON wt.icode = t.icode
        INNER JOIN tire_mold tm ON t.icode = tm.icode
        INNER JOIN mold m ON tm.mold_id = m.mold_id
        WHERE wt.erp = '$erp' AND wt.tobe >= 0"; // Exclude negative quantities
    $result = mysqli_query($conn, $sql);

    // Check if the query executed successfully
    if ($result) {
        // Check if the ERP exists
        if (mysqli_num_rows($result) > 0) {
            // Split the tire IDs, quantities, and descriptions
            $tires = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $icode = $row['icode'];
                $tobe = $row['tobe'];
                $description = $row['description'];
                $tires[] = array('icode' => $icode, 'tobe' => $tobe, 'description' => $description);
            }

            // Iterate over each tire in the ERP
            foreach ($tires as $tire) {
                $icode = $tire['icode'];
                $tobe = $tire['tobe'];
                $description = $tire['description'];

                $sql = "SELECT p.press_id, p.press_name, m.mold_id, m.mold_name, c.cavity_id, c.cavity_name
                    FROM press p
                    INNER JOIN mold_press mp ON p.press_id = mp.press_id
                    INNER JOIN mold m ON mp.mold_id = m.mold_id
                    INNER JOIN press_cavity pc ON p.press_id = pc.press_id
                    INNER JOIN cavity c ON pc.cavity_id = c.cavity_id
                    INNER JOIN tire_mold tm ON m.mold_id = tm.mold_id
                    INNER JOIN tire t ON tm.icode = t.icode
                    WHERE p.is_available = 1 AND m.is_available = 1 AND c.is_available = 1 AND t.icode = '$icode' AND (t.cuing_group_id = 0 OR t.cuing_group_id = (SELECT cuing_group_id FROM tire WHERE icode = '$icode'))";
                $result2 = mysqli_query($conn, $sql);

                // Check if the query executed successfully
                if ($result2) {
                    // Iterate over each mold and cavity combination
                    while ($row2 = mysqli_fetch_assoc($result2)) {
                        $press_id = $row2['press_id'];
                        $press_name = $row2['press_name'];
                        $mold_id = $row2['mold_id'];
                        $mold_name = $row2['mold_name'];
                        $cavity_id = $row2['cavity_id'];
                        $cavity_name = $row2['cavity_name'];

                        $sql = "INSERT INTO production_plan (erp, icode, description, press_id, press_name, mold_id, mold_name, cavity_id, cavity_name, cuing_group_id, cuing_group_name)
                            VALUES ('$erp', '$icode', '$description', '$press_id', '$press_name', '$mold_id', '$mold_name', '$cavity_id', '$cavity_name', (SELECT cuing_group_id FROM tire WHERE icode = '$icode'), (SELECT cuing_group_name FROM tire WHERE icode = '$icode'))";
                        mysqli_query($conn, $sql);
                    }
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            }
        } else {
            echo "No tires found for the given ERP ID";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Production Plan Generator</title>
</head>

<body>
    <h2>Production Plan Generator</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="erp">ERP ID:</label>
        <input type="text" id="erp" name="erp" required>
        <button type="submit">Generate Plan</button>
    </form>
</body>

</html>

