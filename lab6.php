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

// SQL script to create trigger
$trigger_sql = "
DELIMITER //

CREATE TRIGGER update_another_table_data
AFTER UPDATE ON importmix FOR EACH ROW
BEGIN
    -- Update another_table_name based on batch
    UPDATE another_table_name
    SET serial_number = NEW.SerialNumber,
        MH = NEW.MH,
        ML = NEW.ML,
        T10 = NEW.Tc10,
        T90 = NEW.Tc90
    WHERE batch = NEW.JobNumber;
END;
//

DELIMITER ;
";

// Execute trigger creation SQL
if ($connection->multi_query($trigger_sql)) {
    do {
        // Consume all results from multi_query until there are no more
    } while ($connection->more_results() && $connection->next_result());
} else {
    echo "Error creating trigger: " . $connection->error;
}

// Close connection
$connection->close();

?>
