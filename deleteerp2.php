<?php
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

// Define the function to update cavity availability date
function updateCavityAvailability() {
    global $conn;

    // Get current date and time
    $current_datetime = date('Y-m-d H:i:s');

    // Fetch all cavity_id values from the plannew table
    $sql_select = "SELECT DISTINCT cavity_id FROM derp";
    $result = $conn->query($sql_select);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cavity_id = $row['cavity_id'];
            
            // Update the cavity table with the current date and time for the matching cavity_id
            $sql_update = "UPDATE cavity SET availability_date = NOW() WHERE cavity_id = $cavity_id";
            if ($conn->query($sql_update) !== TRUE) {
                echo "Error updating cavity availability date: " . $conn->error;
                return;
            }
        }
        echo "Cavity availability date updated successfully for all records.";
    } else {
        echo "No records found in the plannew table.";
    }
}

// Define the function to update cavity availability date
function updateMoldAvailability() {
    global $conn;

    // Get current date and time
    $current_datetime = date('Y-m-d H:i:s');

    // Fetch all cavity_id values from the plannew table
    $sql_select = "SELECT DISTINCT mold_id FROM derp";
    $result = $conn->query($sql_select);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $mold_id = $row['mold_id'];
            
            // Update the cavity table with the current date and time for the matching cavity_id
            $sql_update = "UPDATE mold SET availability_date = NOW() WHERE mold_id = $mold_id";
            if ($conn->query($sql_update) !== TRUE) {
                echo "Error updating cavity availability date: " . $conn->error;
                return;
            }
        }
        echo "Mold availability date updated successfully for all records.";
    } else {
        echo "No records found in the plannew table.";
    }
}


// Check if the button is clicked
if (isset($_POST['update_button'])) {
    updateCavityAvailability();
    updateMoldAvailability();

       // Redirect to another_page.php after updates
       header("Location: deleteerp3.php");
       exit; // Make sure to exit the script to prevent further executi
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Cavity Availability Date</title>
    <title>Update Cavity and Mold Availability Date</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>


</head>
<body>
    <div class="container">
        <h1>Please click to next</h1>
        <form method="POST">
            <div class="btn-container">
                <button type="submit" name="update_button" class="btn">Next</button>
            </div>
        </form>
    </div>
</body>
</html>