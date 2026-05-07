<?php
// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Check if the required data is set
    if (isset($_POST['inputDate']) && isset($_POST['shift']) && isset($_POST['tire_id']) && isset($_POST['mold_name']) && isset($_POST['cavity_name']) && isset($_POST['plan'])) {
        // Retrieve the form data
        $inputDate = $_POST['inputDate'];
        $shift = $_POST['shift'];
        $tireId = $_POST['tire_id'];
        $moldName = $_POST['mold_name'];
        $cavityName = $_POST['cavity_name'];
        $plan = $_POST['plan'];

        // Create a database connection
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Define an SQL query to insert the data into the database (using your provided table name)
        $sql = "INSERT INTO shift_plan (date, shift, tire_id, mold_name, cavity_name, plan) VALUES (?, ?, ?, ?, ?, ?)";

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error in preparing statement: " . $conn->error);
        }

        // Loop through the data and execute the query for each row
        for ($i = 0; $i < count($tireId); $i++) {
            $stmt->bind_param("ssssss", $inputDate, $shift, $tireId[$i], $moldName[$i], $cavityName[$i], $plan[$i]);
            $stmt->execute();
        }

        // Close the prepared statement
        $stmt->close();

        // Close the database connection
        $conn->close();

        // Inform the user that data has been inserted
        echo "Data inserted successfully into the database.";
    } else {
        // Handle the case where required form data is missing
        echo "Missing form data. Please go back to the previous page and try again.";
    }
}
?>
