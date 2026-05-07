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
        FROM tobeplan_plan_plan wt
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
                WHERE p.is_available = 1 AND m.is_available = 1 AND c.is_available = 1 AND t.icode = '$icode' AND (t.cuing_group_id = 0 OR t.cuing_group_id = (SELECT cuing_group_id FROM tire WHERE icode = '$icode'))
                ORDER BY mp.id ASC"; // Order by the ID column in ascending order
        
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

            // Close the database connection
            mysqli_close($conn);

            // Redirect to another page
            header("Location: tire_cavity.php");
            exit();


        } else {
            echo "No tires found for the given ERP ID";

             // Insert data into tobeplan1
             $sqlInsert = "INSERT INTO tobeplan1 SELECT * FROM tobeplan_plan_plan WHERE erp = '$erp'";
             mysqli_query($conn, $sqlInsert);
        
            // Delete data from tobeplan_plan_plan and move it to tobeplan1
            $sqlDelete = "DELETE FROM tobeplan_plan_plan WHERE erp = '$erp'";
            mysqli_query($conn, $sqlDelete);
         
            header("Location:dashboard.php");
            exit();
           
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }

}
?>
<!DOCTYPE html>
<!-- Rest of the HTML code -->


<!DOCTYPE html>
<html>
<head>
    <title>Production Plan </title>
    <!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 50px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        h1 {
            color: #000000;
            font-weight: bold;
            font-size: 24px;
        }

        form {
            margin-top: 20px;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Cantarell Bold', sans-serif;
        }

        input[type="submit"]:hover {
            background-color: #FFA726;
        }

        .generate-button {
    background-color: #F28018;
    color: #FFFFFF;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-family: 'Cantarell', sans-serif;
    text-decoration: none;
}

.generate-button:hover {
    background-color: #FFA500; /* Change the background color on hover */
}

    </style>
</head>
<body>
    <div class="container">
        <h2>Production Plan </h2>
        <h3>Please enter ERP Number</h3>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="erp"></label>
            <input type="text" id="erp" name="erp" required>
            <button type="submit" class="generate-button">Generate Plan</button>

        </form>

        <?php
        // ... The existing PHP code for generating the table ...
        ?>
    </div>
</body>
</html>

