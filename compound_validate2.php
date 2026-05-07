


<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #F28018;
        }
        input[type="text"] {
            border: none; /* Remove border */
            
            width: 100%;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #F28018;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: black;
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

// Fetch all data from the table
$query = "SELECT * FROM another_table_name2";
$result = mysqli_query($connection, $query);

// Check if there are any results
if(mysqli_num_rows($result) > 0) {
    // Output table header
    echo "<form method='post' action=''>";
    echo "<table border='1'>";
    echo "<tr><th>id</th><th>Date</th><th>Shift</th><th>Compound Name</th><th>ERP Cords</th><th>Data enter supervisor</th><th>Batch</th><th>Pallet</th><th>Weight</th><th>Date of Quality Approved</th><th>Date of Expire</th><th>Name of Staff</th><th>SG Value</th><th>Hardness</th><th>MH</th><th>ML</th><th>T10</th><th>T90</th><th>Rebound(%)</th></tr>";
    
    // Output data rows
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>".$row["id"]."</td>";
        echo "<td>".$row["inputDate"]."</td>";
        echo "<td>".$row["shift"]."</td>";
        echo "<td>".$row["compound_name"]."</td>";
        echo "<td>".$row["description"]."</td>";
        echo "<td>".$row["cstock"]."</td>";
        echo "<td>".$row["batch"]."</td>";
        echo "<td>".$row["pallet"]."</td>";

        echo "<td>".$row["weight"]."</td>";
       
        // New additional columns
        echo "<td><input type='date' name='quality_approved[".$row["id"]."]' value='".$row["quality_approved"]."'></td>";
        echo "<td><input type='date' name='expire_date[".$row["id"]."]' value='".$row["expire_date"]."'></td>";
        echo "<td><input type='text' name='staff_name[".$row["id"]."]' value='".$row["staff_name"]."'></td>";
        echo "<td><input type='text' name='sg_value[".$row["id"]."]' value='".$row["sg_value"]."'></td>";
        echo "<td><input type='text' name='hardness[".$row["id"]."]' value='".$row["hardness"]."'></td>";
        echo "<td><input type='text' name='mh[".$row["id"]."]' value='".$row["mh"]."'></td>";
        echo "<td><input type='text' name='ml[".$row["id"]."]' value='".$row["ml"]."'></td>";
        echo "<td><input type='text' name='t10[".$row["id"]."]' value='".$row["t10"]."'></td>";
        echo "<td><input type='text' name='t90[".$row["id"]."]' value='".$row["t90"]."'></td>";
        echo "<td><input type='text' name='rebound[".$row["id"]."]' value='".$row["rebound"]."'></td>";
        
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<input type='submit' name='submit' value='Update'>";
    echo "<input type='submit' name='next' value='Next'>";
    echo "</form>";
} else {
    echo "No results found.";
}

// Handle form submission for updating additional columns
if(isset($_POST['submit'])) {
    // Prepare and bind parameters for update
    $update_query = "UPDATE another_table_name2 SET  quality_approved=?, expire_date=?, staff_name=?, sg_value=?, hardness=?, mh=?, ml=?, t10=?, t90=?, rebound=? WHERE id=?";
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
    echo "Data updated successfully.";
}

// Check if 'Next' button is clicked and all table columns have data before redirecting
if(isset($_POST['next'])) {
    $check_query = "SELECT * FROM another_table_name2 WHERE quality_approved IS NULL OR expire_date IS NULL OR staff_name IS NULL OR sg_value IS NULL OR hardness IS NULL OR mh IS NULL OR ml IS NULL OR t10 IS NULL OR t90 IS NULL OR rebound IS NULL LIMIT 1";
    $check_result = mysqli_query($connection, $check_query);
    if(mysqli_num_rows($check_result) === 0) {
        // Redirect to another page
        header("Location: compound_validate3.php");
        exit();
    } else {
        echo "Please fill in all the table columns before proceeding.";
    }
}

?>
