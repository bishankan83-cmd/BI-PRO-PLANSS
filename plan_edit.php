<?php
// Check if the required parameters are set
if (isset($_GET['user_start_time']) && isset($_GET['user_end_time']) && isset($_GET['selected_data'])) {
    // Retrieve the parameters
    $user_start_time = $_GET['user_start_time'];
    $user_end_time = $_GET['user_end_time'];
    
    // Decode the JSON data
    $selectedData = json_decode($_GET['selected_data'], true);
    
    // Database connection details
    $dbHost = "localhost";
    $dbUser = "planatir_task_managemen";
    $dbPass = "Bishan@1919";
    $dbName = "planatir_task_managemen";
    
    // Create a database connection
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Define the SQL INSERT statement
    $insertQuery = "INSERT INTO shift_plan(user_start_time, user_end_time, icode, mold_name, cavity_name, tobe) VALUES (?, ?, ?, ?, ?, ?)";
    
    // Prepare the statement
    $stmt = $conn->prepare($insertQuery);
    
    // Loop through the selected data and insert it into the database
    foreach ($selectedData as $data) {
        $icode = $data['icode'];
        $mold_name = $data['mold_name'];
        $cavity_name = $data['cavity_name'];
        $tobe = $data['tobe'];
        
        // Bind parameters and execute the statement
        $stmt->bind_param("ssssss", $user_start_time, $user_end_time, $icode, $mold_name, $cavity_name, $tobe);
        $stmt->execute();
    }
    
    // Close the database connection
    $stmt->close();
    $conn->close();
} else {
    // Handle the case where parameters are missing
    echo "Missing parameters. Please go back to the previous page and try again.";
    // You can add a link to navigate back to the previous page if needed.
    exit; // Stop execution
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Update Shift Plan</title>
</head>


<!DOCTYPE html>
<html lang="en">
<head>
<style>       /* Your CSS styles */
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
            background-color: rgba(255, 255, 255, 0.8); /* Add a semi-transparent white background to the container */
            padding: 50px;
            border-radius: 20px; /* Add rounded corners to the container */
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

    </style>
<body>
<div class="container">
    <h2>Update Shift Plan</h2>
    <form action="update_shift_plan.php" method="post">
        <label for="inputDate">Date:</label>
        <input type="date" id="inputDate" name="inputDate" required>

        <label for="shift">Shift:</label>
        <select name="shift" id="shift">
            <option value="DAY A">DAY A</option>
            <option value="DAY B">DAY B</option>
            <option value="DAY C">DAY C</option>
            <option value="NIGHT A">NIGHT A</option>
            <option value="NIGHT B">NIGHT B</option>
            <option value="NIGHT C">NIGHT C</option>
        </select>

        <input type="submit" value="Update Shift Plan">
    </form>
</body>
</html>
