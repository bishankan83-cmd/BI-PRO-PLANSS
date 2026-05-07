<?php
// Include the database connection settings
$hostname = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a database connection
$connection = mysqli_connect($hostname, $username, $password, $database);

// Check if the "Copy Data" button is clicked
 
    // SQL query to copy data from the 'shift_plan' table to the 'daily_plan' table
    $copyQuery = "INSERT INTO daily_plan (Date, Shift, Icode, MoldName, CavityName, Plan)
                 SELECT date, shift, icode, mold_name, cavity_name, tobe
                 FROM shift_plan";

    // Execute the copy query
    if (mysqli_query($connection, $copyQuery)) {
        echo "Data copied successfully to the 'daily_plan' table.";
        
        // Now, delete the data from the 'shift_plan' table
        $deleteQuery = "DELETE FROM shift_plan";

        if (mysqli_query($connection, $deleteQuery)) {
            echo "Data deleted from the 'shift_plan' table.";
        } else {
            echo "Error deleting data from 'shift_plan': " . mysqli_error($connection);
        }
    } else {
        echo "Error copying data: " . mysqli_error($connection);
    }


// Close the database connection
mysqli_close($connection);
header("Location: dashboard.php");
exit();
?>
