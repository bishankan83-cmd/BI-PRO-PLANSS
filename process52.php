<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Query to get the number of molds for each icode from quick_plan table
$moldQuery = "SELECT icode, COUNT(DISTINCT mold_id) AS mold_count FROM quick_plan GROUP BY icode";
$moldResult = $conn->query($moldQuery);

// Check if the mold query was successful
if ($moldResult) {
    // Fetch the results as an associative array
    while ($row = $moldResult->fetch_assoc()) {
        $icode = $row['icode'];
        $moldCount = $row['mold_count'];

        // Query to get the number of tobes for each icode from tobeplan table
        $tobeQuery = "SELECT tobe FROM tobeplan WHERE icode = '$icode'";
        $tobeResult = $conn->query($tobeQuery);

        // Check if the tobe query was successful
        if ($tobeResult) {
            // Fetch the tobe count
            $tobeRow = $tobeResult->fetch_assoc();
            $tobeCount = $tobeRow['tobe'];

            $ratio = ceil(($moldCount > 0) ? ($tobeCount / $moldCount) : 0);

            // Query to get mold_id and cavity_id from quick_plan
            $moldInfoQuery = "SELECT mold_id, cavity_id FROM quick_plan WHERE icode = '$icode'";
            $moldInfoResult = $conn->query($moldInfoQuery);

            if ($moldInfoResult) {
                // Fetch and insert data into the "process" table
                while ($moldInfoRow = $moldInfoResult->fetch_assoc()) {
                    $moldId = $moldInfoRow['mold_id'];
                    $cavityId = $moldInfoRow['cavity_id'];

                    // Query to get cavity_name based on cavity_id
                    $cavityNameQuery = "SELECT cavity_name FROM cavity WHERE cavity_id = '$cavityId'";
                    $cavityNameResult = $conn->query($cavityNameQuery);

                    if ($cavityNameResult) {
                        $cavityNameRow = $cavityNameResult->fetch_assoc();
                        $cavityName = $cavityNameRow['cavity_name'];

                        // Query to get mold_name based on mold_id
                        $moldNameQuery = "SELECT mold_name FROM mold WHERE mold_id = '$moldId'";
                        $moldNameResult = $conn->query($moldNameQuery);

                        if ($moldNameResult) {
                            $moldNameRow = $moldNameResult->fetch_assoc();
                            $moldName = $moldNameRow['mold_name'];

                            // Insert data into the "process" table
                            $insertQuery = "INSERT INTO process (icode, mold_id, tires_per_mold, cavity_id, mold_name, cavity_name, erp, serial, is_completed) VALUES ('$icode', '$moldId', '$ratio', '$cavityId', '$moldName', '$cavityName','YourERP', 'YourSerial', 0)";

                            if ($conn->query($insertQuery) === TRUE) {
                                
                            } else {
                               
                            }
                        } else {
                            // Handle mold name query error
                            echo "Error: " . $moldNameQuery . "<br>" . $conn->error;
                        }

                        // Free the mold name result set
                        $moldNameResult->free();
                    } else {
                        // Handle cavity name query error
                        echo "Error: " . $cavityNameQuery . "<br>" . $conn->error;
                    }

                    // Free the cavity name result set
                    $cavityNameResult->free();
                }

                // Free the mold info result set
                $moldInfoResult->free();
            } else {
                // Handle mold info query error
                echo "Error: " . $moldInfoQuery . "<br>" . $conn->error;
            }

            // Free the tobe result set
            $tobeResult->free();
        } else {
            // Handle tobe query error
            echo "Error: " . $tobeQuery . "<br>" . $conn->error;
        }
    }

    // Free the mold result set
    $moldResult->free();
} else {
    // Handle mold query error
    echo "Error: " . $moldQuery . "<br>" . $conn->error;
}



    // Redirect to another page after the data is inserted successfully
   // header("Location: updatepro.php");
   // exit(); // Make sure to add this exit() to stop further execution
header("Location: planview2.php");
    exit();


// Close the connection
$conn->close();
?>

