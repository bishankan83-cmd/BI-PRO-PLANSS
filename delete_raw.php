<?php
// Database connection details
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

// SQL query to insert data from tobeplan_plan into tobeplan_tem
$sql1 = "INSERT INTO tobeplan_tem (id, icode, tobe, erp, stockonhand)
         SELECT id, icode, tobe, erp, stockonhand
         FROM tobeplan_plan";


$sql2 = "INSERT INTO process_plan_tem (id, icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date)
         SELECT id, icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, press_name, press_id, erp, serial, is_completed, is_highlighted, first_tobe, start_date
         FROM process_plan";


// Execute the first query
if ($conn->query($sql1) === TRUE) {
    // Execute the second query
    if ($conn->query($sql2) === TRUE) {
        // Redirect to another page if both queries are successful
        header("Location: plannew45.php");
        exit(); // Ensure no further code is executed after redirection
    } else {
        // Show an error message if the second query fails
        echo "Error in process_plan_tem: " . $sql2 . "<br>" . $conn->error;
    }
} else {
    // Show an error message if the first query fails
    echo "Error in tobeplan_tem: " . $sql1 . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>
