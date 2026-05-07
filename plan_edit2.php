
<!-- Add a button to go to another page -->
<form action="plan_report.php">
    <input type="submit" value="Click To Next">
</form>


<style>
        /* Your CSS styles */

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="date"],
        select {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
        }

        .btn-container {
            margin-top: 20px;
            text-align: center;
        }

        input[type="button"],
        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="button"]:hover,
        input[type="submit"]:hover {
            background-color: #333333;
        }
    </style>

<?php
// Database connection settings for the shift_plan table
$hostname = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a database connection for the shift_plan table
$connection = mysqli_connect($hostname, $username, $password, $database);

// Check if the connection was successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Database connection settings for the tire table
$tireHostname = "localhost";
$tireUsername = "planatir_task_managemen";
$tirePassword = "Bishan@1919";
$tireDatabase = "planatir_task_managemen";

// Handle form submissions
if (isset($_POST['copy_data'])) {
    // Handle the copy_data form submission here
    // You can add the logic to copy data to the next step.
} elseif (isset($_POST['update'])) {
    // Handle the update form submission here
    $id = $_POST['id'];
    $icode = mysqli_real_escape_string($connection, $_POST['icode']);

    $moldName = mysqli_real_escape_string($connection, $_POST['moldName']);
    $cavityName = mysqli_real_escape_string($connection, $_POST['cavityName']);
    $plan = mysqli_real_escape_string($connection, $_POST['plan']);

    $updateQuery = "UPDATE shift_plan 
                    SET icode='$icode', mold_name='$moldName', 
                    cavity_name='$cavityName', tobe='$plan' 
                    WHERE id='$id'";

    if (mysqli_query($connection, $updateQuery)) {
        echo "Record updated successfully.";
    } else {
        echo "Error updating record: " . mysqli_error($connection);
    }
}

// SQL query to retrieve data from the shift_plan table
$query = "SELECT * FROM shift_plan";

// Execute the query
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Display the data in an HTML table with editable fields for specific columns
echo "<table border='1'>
    <tr>
     
        <th>Icode</th>
        <th>Description</th>
        <th>MoldName</th>
        <th>CavityName</th>
        <th>StartTime</th>
        <th>EndTime</th>
        <th>Plan</th>
        <th>Actions</th>
    </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $icode = $row['icode'];

    // Connect to the "tire" database
    $tireConnection = mysqli_connect($tireHostname, $tireUsername, $tirePassword, $tireDatabase);

    // Check if the connection was successful
    if (!$tireConnection) {
        die("Connection to tire database failed: " . mysqli_connect_error());
    }

    // Query the tire database to get the description based on icode
    $tireQuery = "SELECT description FROM tire WHERE icode = '$icode'";
    $tireResult = mysqli_query($tireConnection, $tireQuery);

    if (!$tireResult) {
        die("Query to tire database failed: " . mysqli_error($tireConnection));
    }

    $tireRow = mysqli_fetch_assoc($tireResult);
    $description = $tireRow['description'];

    // Close the connection to the tire database
    mysqli_close($tireConnection);

    echo "<form method='post' action=''>
    <tr>
        
        <td><input type='text' name='icode' value='" . $icode . "'></td>
        <td>" . $description . "</td>
        <td><input type='text' name='moldName' value='" . $row['mold_name'] . "'></td>
        <td><input type='text' name='cavityName' value='" . $row['cavity_name'] . "'></td>
        <td>" . $row['user_start_time'] . "</td>
        <td>" . $row['user_end_time'] . "</td>
        <td><input type='text' name='plan' value='" . $row['tobe'] . "'></td>
        <td><input type='submit' name='update' value='Update'></td>
    </tr>
    </form>";
}

echo "</table>";

// Close the database connections
mysqli_close($connection);
?>
