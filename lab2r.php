<?php
// Database credentials
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL DELETE query
    $sql = "
    DELETE t1
    FROM `another_table_name11` t1
    INNER JOIN (
      SELECT 
      
        `inputDate`,
        `shift`,
        `compound_name`,
        `description`,
        `cstock`,
        `batch`,
        `pallet`,
        `weight`
      FROM `another_table_name11`
      GROUP BY 
        `inputDate`, 
        `shift`, 
        `compound_name`, 
        `description`, 
        `cstock`, 
        `batch`, 
        `pallet`, 
        `weight`
      HAVING COUNT(*) > 1
    ) t2 ON t1.`inputDate` = t2.`inputDate`
       AND t1.`shift` = t2.`shift`
       AND t1.`compound_name` = t2.`compound_name`
       AND t1.`description` = t2.`description`
       AND t1.`cstock` = t2.`cstock`
       AND t1.`batch` = t2.`batch`
       AND t1.`pallet` = t2.`pallet`
       AND t1.`weight` = t2.`weight`;

    ";

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo "Duplicate rows deleted successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}



// Close the database connection
$pdo = null;
?>



<?php
// Database credentials
$host = 'localhost';
$dbname = 'planatir_task_managemen';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL DELETE query
    $sql = "
    DELETE t1
    FROM `another_table_name33` t1
    INNER JOIN (
      SELECT 
      
        `inputDate`,
        `shift`,
        `compound_name`,
        `description`,
        `cstock`,
        `batch`,
        `pallet`,
        `weight`
      FROM `another_table_name33`
      GROUP BY 
        `inputDate`, 
        `shift`, 
        `compound_name`, 
        `description`, 
        `cstock`, 
        `batch`, 
        `pallet`, 
        `weight`
      HAVING COUNT(*) > 1
    ) t2 ON t1.`inputDate` = t2.`inputDate`
       AND t1.`shift` = t2.`shift`
       AND t1.`compound_name` = t2.`compound_name`
       AND t1.`description` = t2.`description`
       AND t1.`cstock` = t2.`cstock`
       AND t1.`batch` = t2.`batch`
       AND t1.`pallet` = t2.`pallet`
       AND t1.`weight` = t2.`weight`;

    ";

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo "Duplicate rows deleted successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}



// Close the database connection
$pdo = null;
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXT</title>
</head>
<body>

<!-- Button to redirect -->
<button id="redirectButtonn">Add Data One Time</button>

<script>
// JavaScript to handle button click event
document.getElementById("redirectButtonn").onclick = function() {
    // Redirect to another page
    window.location.href = "barcode_enterr.php";
};
</script>

</body>
</html>


<!DOCTYPE html>
<html lang="en">

<body>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 15000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        input[type="date"],
        input[type="text"] {
            padding: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #F28018;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: black;
        }

        input[type="submit"]:focus {
            outline: none;
        }
    </style>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
$query = "SELECT * FROM another_table_name11 ORDER BY CAST(batch AS UNSIGNED) ASC";

$result = mysqli_query($connection, $query);
// Check if there are any results
if(mysqli_num_rows($result) > 0) {
    // Output table header for the first part of the table
    echo "<div class='container'>";
    echo "<form method='post' action=''>";
    echo "<table>";
    echo "<tr><th>id</th><th>Date</th><th>Shift</th><th>Compound Name</th><th>Data enter supervisor</th><th>DES</th></tr>";
    
    // Initialize variables to keep track of previous values
    $prevInputDate = "";
    $prevShift = "";
    $prevCompoundName = "";
    $prevDescription = "";
    $prevCStock = "";

    // Output data rows for the first part of the table
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        // Output existing data if it's different from the previous one
        if ($row["inputDate"] != $prevInputDate) {
            echo "<td>".$row["id"]."</td>";
            echo "<td>".$row["inputDate"]."</td>";
        }
        if ($row["shift"] != $prevShift) {
            echo "<td>".$row["shift"]."</td>";
        }
        if ($row["compound_name"] != $prevCompoundName) {
            echo "<td>".$row["compound_name"]."</td>";
        }
        if ($row["description"] != $prevDescription) {
            echo "<td>".$row["description"]."</td>";
        }
        if ($row["cstock"] != $prevCStock) {
            echo "<td>".$row["cstock"]."</td>";
        }
        echo "</tr>";

        // Update previous values
        $prevInputDate = $row["inputDate"];
        $prevShift = $row["shift"];
        $prevCompoundName = $row["compound_name"];
        $prevDescription = $row["description"];
        $prevCStock = $row["cstock"];

        
    }
    
    echo "</table>";
    echo "</div>";
} else {
    echo "No results found.";
}

?>




    


</body>
</html>


    <div class="container">
        <form method="post" action="">
            <!-- Second Table -->
  <!-- Second Table -->
<table>
    <tr>
        <th>Job number</th>
        <th>Batch</th>
        <th>Pallet</th>
        <th>Weight</th>
        <th>Date of Quality Approved</th>
        <th>Date of Expire</th>
        <th>Name of Staff</th>
        <th>SG Value</th>
        <th>Hardness</th>
        <th>MH</th>
        <th>ML</th>
        <th>T10</th>
        <th>T90</th>
        <th>Rebound (%)</th>
    </tr>
    <?php
    // Rewind the result pointer to start from the beginning
    mysqli_data_seek($result, 0);

    // Output data rows for the second part of the table
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        // Output input fields for additional columns
        echo "<td>".$row["serial_number"]."</td>";
        echo "<td>".$row["batch"]."</td>";
        echo "<td>".$row["pallet"]."</td>";
        echo "<td>".$row["weight"]."</td>";
        echo "<td><input type='date' name='quality_approved[".$row["id"]."]' value='".$row["quality_approved"]."'></td>";
        echo "<td><input type='date' name='expire_date[".$row["id"]."]' value='".$row["expire_date"]."'></td>";
        echo "<td><input type='text' name='staff_name[".$row["id"]."]' value='".$row["staff_name"]."'></td>";
        echo "<td><input type='text' name='sg_value[".$row["id"]."]' value='".$row["sg_value"]."'></td>";
        echo "<td><input type='text' name='hardness[".$row["id"]."]' value='".$row["hardness"]."'></td>";
        
        // Fetch corresponding values from importmix table
        $importmix_query = "SELECT * FROM importmix WHERE CompoundID='{$row["compound_name"]}' AND Batch='{$row["batch"]}' LIMIT 1";
        $importmix_result = mysqli_query($connection, $importmix_query);
        if(mysqli_num_rows($importmix_result) > 0) {
            $importmix_row = mysqli_fetch_assoc($importmix_result);
            // Output the fetched values as input fields for user edit
            echo "<td><input type='text' name='mh[".$row["id"]."]' value='".$importmix_row["MH"]."'></td>";
            echo "<td><input type='text' name='ml[".$row["id"]."]' value='".$importmix_row["ML"]."'></td>";
            echo "<td><input type='text' name='t10[".$row["id"]."]' value='".$importmix_row["Tc10"]."'></td>";
            echo "<td><input type='text' name='t90[".$row["id"]."]' value='".$importmix_row["Tc90"]."'></td>";
        } else {
            // Output empty cells if no matching data found in importmix
            echo "<td><input type='text' name='mh[".$row["id"]."]'></td>";
            echo "<td><input type='text' name='ml[".$row["id"]."]'></td>";
            echo "<td><input type='text' name='t10[".$row["id"]."]'></td>";
            echo "<td><input type='text' name='t90[".$row["id"]."]'></td>";
        }
        
        // Output remaining input fields
        echo "<td><input type='text' name='rebound[".$row["id"]."]' value='".$row["rebound"]."'></td>";
        echo "</tr>";
    }
    ?>
</table>



<form method="post" action="updaterr.php">
    <!-- Your form elements -->
    <input type="submit" name="submit" id="updateButton" value="Update">
</form>

           
        </form>
    </div>
    <script>
        
    // Function to handle arrow key presses for the SG Value column and the Hardness column
    function handleArrowKeyPress(event) {
        const activeElement = document.activeElement;
        const tagName = activeElement.tagName.toLowerCase();

        if (tagName === 'input') {
            const inputName = activeElement.getAttribute('name');
            const isSGValue = inputName.startsWith('sg_value');
            const isHardness = inputName.startsWith('hardness');

            if (isSGValue || isHardness) {
                const inputs = document.querySelectorAll(`input[name^="${isSGValue ? 'sg_value' : 'hardness'}"]`);
                const currentIndex = Array.from(inputs).indexOf(activeElement);

                if (event.key === 'ArrowDown') {
                    const nextIndex = currentIndex + 1;
                    if (nextIndex < inputs.length) {
                        inputs[nextIndex].focus();
                    } else {
                        // If reached the end of the column, prevent default behavior (scrolling)
                        event.preventDefault();
                    }
                } else if (event.key === 'ArrowUp') {
                    const prevIndex = currentIndex - 1;
                    if (prevIndex >= 0) {
                        inputs[prevIndex].focus();
                    } else {
                        // If reached the beginning of the column, prevent default behavior (scrolling)
                        event.preventDefault();
                    }
                } else if (event.key === 'Enter') {
                    const nextIndex = currentIndex + 1;
                    if (nextIndex < inputs.length) {
                        inputs[nextIndex].focus();
                    } else {
                        // If reached the end of the column, prevent default behavior (form submission)
                        event.preventDefault();
                    }
                }
            }
        }
    }

    // Add event listener for arrow key presses
    document.addEventListener('keydown', handleArrowKeyPress);

    
</script>


    <?php
    // Handle form submission for updating additional columns
    if(isset($_POST['submit'])) {
        // Prepare and bind parameters for update
        $update_query = "UPDATE another_table_name11 SET  quality_approved=?, expire_date=?, staff_name=?, sg_value=?, hardness=?, mh=?, ml=?, t10=?, t90=?, rebound=? WHERE id=?";
        $stmt = $connection->prepare($update_query);
        $stmt->bind_param("ssssssssssi", $quality_approved, $expire_date, $staff_name, $sg_value, $hardness, $mh, $ml, $t10, $t90, $rebound, $id);
        
        // Update data in the table
        foreach($_POST['quality_approved'] as $id => $value) {
            $quality_approved = $_POST['quality_approved'][$id];
            $expire_date = $_POST['expire_date'][$id];
            $staff_name = $_POST['staff_name'][$id];
            $sg_value = $_POST['sg_value'][$id];
            $hardness = $_POST['hardness'][$id];
            $mh = $_POST['mh'][$id];
            $ml = $_POST['ml'][$id];
            $t10 = $_POST['t10'][$id];
            $t90 = $_POST['t90'][$id];
            $rebound = $_POST['rebound'][$id];
            $id = $id;
            
            // Execute the update statement
            $stmt->execute();
        }
        
        // Close statement
        $stmt->close();
        
        // Provide feedback to the user and refresh the page after data update
       // echo "<div class='container'>Data updated successfully.</div>";
    }

    // Check if 'Next' button is clicked and all table columns have data before redirecting
    if(isset($_POST['next'])) {
        $check_query = "SELECT * FROM another_table_name11 WHERE quality_approved IS NULL OR expire_date IS NULL OR staff_name IS NULL OR sg_value IS NULL OR hardness IS NULL OR mh IS NULL OR ml IS NULL OR t10 IS NULL OR t90 IS NULL OR rebound IS NULL LIMIT 1";
        $check_result = mysqli_query($connection, $check_query);
        if(mysqli_num_rows($check_result) === 0) {
            // Redirect to another page
            header("Location: lab3.php");
            exit();
        } else {
            echo "<div class='container'>Please fill in all the table columns before proceeding.</div>";
        }
    }
    ?>




</body>

</html>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXT</title>
</head>
<body>

<!-- Button to redirect -->
<button id="redirectButton">Genrate QR</button>

<script>
// JavaScript to handle button click event
document.getElementById("redirectButton").onclick = function() {
    // Redirect to another page
    window.location.href = "lab3r.php";
};
</script>

</body>
</html>
