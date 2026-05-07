<?php



if (isset($_POST['export_excel'])) {
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Create a connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create a file pointer
    $output = fopen('shift_plan.csv', 'w');

    
    // Add headers to the CSV file
    fputcsv($output, array(
        'Cavity Name',
        'Mold Name',
        'ICode',
        'Description',
        'Rim',
        'Brand',
        'Type',
        'Colour',
        'Green Tire Weight',
        'Order',
        'Plan Pcs',
        'Plan Weight'
    ));

    // Fetch and write data to the CSV file
    $sql = "SELECT * FROM shift_plan";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];

        // Your existing code to retrieve additional details and calculations
        // Replace the following placeholder comments with your actual code

        // Retrieve additional details from another table (Example)
        $tireDetailsSql = "SELECT Description, Brand, Type, Colour, Rim, greenweight FROM tire_details WHERE icode = '$icode'";
        $tireDetailsResult = $conn->query($tireDetailsSql);
        if ($tireDetailsResult->num_rows > 0) {
            $tireDetails = $tireDetailsResult->fetch_assoc();
        } else {
            $tireDetails = array("Description" => "N/A", "Brand" => "N/A", "Type" => "N/A", "Colour" => "N/A", "Rim" => "N/A", "greenweight" => "N/A");
        }

        // Calculate and display the sum of positive icode tobes (Example)
        $positiveIcodeTobesSql = "SELECT SUM(tobe) AS sum_positive_tobes FROM tobeplan1 WHERE icode = '$icode' AND tobe > 0";
        $positiveIcodeTobesResult = $conn->query($positiveIcodeTobesSql);
        if ($positiveIcodeTobesResult->num_rows > 0) {
            $positiveIcodeTobesData = $positiveIcodeTobesResult->fetch_assoc();
            $sumPositiveTobes = $positiveIcodeTobesData["sum_positive_tobes"];
        } else {
            $sumPositiveTobes = 0;
        }

        // Get Plan Pcs (Example)
        $planPcs = $row["tobe"];

        // Calculate and display the product of Green Tire Weight and Plan Pcs (Example)
        $greenTireWeight = floatval($tireDetails["greenweight"]);

        $product = $greenTireWeight * $planPcs;

        // Write the row to the CSV file
        fputcsv($output, array(
            $row['cavity_name'],
            $row['mold_name'],
            $row['icode'],
            $tireDetails["Description"],
            $tireDetails["Rim"],
            $tireDetails["Brand"],
            $tireDetails["Type"],
            $tireDetails["Colour"],
            $tireDetails["greenweight"],
            $positiveIcodeTobesData["sum_positive_tobes"],
            $planPcs,
            $product
        ));
    }

    // Close the file pointer
    fclose($output);

    // Set headers to force download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="shift_plan.csv"');
    readfile('shift_plan.csv');
    exit;
}

// Close the database connection
$conn->close();

?>