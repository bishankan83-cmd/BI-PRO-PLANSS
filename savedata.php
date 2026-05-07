<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Establish database connection
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Retrieve the ERP ID from the form submission
    $erp = isset($_POST['erp']) ? $_POST['erp'] : '';

    // Validate the ERP ID (you can add your own validation logic here)
    if (empty($erp)) {
        die("Please enter a valid ERP ID");
    }

    // Sanitize the ERP ID to prevent SQL injection
    $erp = mysqli_real_escape_string($conn, $erp);

    // Iterate over the form fields to retrieve the selected molds
    foreach ($_POST as $field => $value) {
        // Check if the field starts with "mold_"
        if (strpos($field, 'mold_') === 0) {
            $mold_id = mysqli_real_escape_string($conn, $value);
            
            // Retrieve the icode for the mold
            $icode = substr($field, 5); // Remove "mold_" prefix from the field name

            // Retrieve the selected press for the mold
            $press_field = 'press_' . $icode;
            $press_id = isset($_POST[$press_field]) ? mysqli_real_escape_string($conn, $_POST[$press_field]) : '';

            // Insert the mold information into the database
            $sql = "INSERT INTO your_table_name (erp, icode, press, mold) VALUES ('$erp', '$icode', '$press_id', '$mold_id')";
            $result = mysqli_query($conn, $sql);

            // Check if the query executed successfully
            if ($result === false) {
                die("Query error: " . mysqli_error($conn));
            }
        }
    }

    // Close the database connection
    mysqli_close($conn);

    // Redirect or display a success message
    // ...
}
?>
