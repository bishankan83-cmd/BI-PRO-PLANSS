




<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost"; // Database server address and port
$username = "planatir_task_managemen"; // Database username
$password = "Bishan@1919"; // Database password
$database = "planatir_task_managemen"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the button is clicked
if (isset($_POST['insert_data'])) {
    // SQL query to fetch data from the `calculated_data_stock` table
    $sql_select = "SELECT * FROM calculated_data_stock";
    $result = $conn->query($sql_select);

    // Check if data exists in the source table
    if ($result->num_rows > 0) {
        // Loop through each row from the source table and insert into `calculated_stock_copy`
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $date = $row['date'];
            $erp = $row['erp'];
            $icode = $row['icode'];
            $description = $row['description'];
            $mold_id = $row['mold_id'];
            $cavity_id = $row['cavity_id'];
            $start_date = $row['start_date'];
            $end_date = $row['end_date'];
            $plan = $row['plan'];
            $calculated_green_tire_weight = $row['calculated_green_tire_weight'];
            $calculated_stgreen_tire_weight = $row['calculated_stgreen_tire_weight'];

            // SQL query to insert data into `calculated_stock_copy`
            $sql_insert = "INSERT INTO calculated_data_stock_copy 
                            (id, date, erp, icode, description, mold_id, cavity_id, start_date, end_date, plan, calculated_green_tire_weight, calculated_stgreen_tire_weight) 
                            VALUES 
                            ('$id', '$date', '$erp', '$icode', '$description', '$mold_id', '$cavity_id', '$start_date', '$end_date', '$plan', '$calculated_green_tire_weight', '$calculated_stgreen_tire_weight')";

            // Execute the insert query
            if ($conn->query($sql_insert) === TRUE) {
                // Redirect to another page after successful insert
                header("Location: stock_add.php"); // Change to the page you want to redirect to
                exit; // Ensure no further code is executed
            } else {
                echo "Error inserting record for ID: $id - " . $conn->error . "<br>";
            }
        }
    } else {
        echo "No records found in `calculated_data_stock` to insert.";
    }
}

// Close the connection
$conn->close();
?>
