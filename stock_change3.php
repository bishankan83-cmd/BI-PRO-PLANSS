
    

<?php
if (isset($_POST['action']) && $_POST['action'] === 'delete_selected_stocks') {
    // Database connection parameters
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

    // Update cstock in realstock based on selected_stocks before deleting
    $update_sql = "UPDATE realstock
                   SET cstock = cstock + 1
                   WHERE icode IN (
                       SELECT icode FROM selected_stocks
                   )";

    if ($conn->query($update_sql) === TRUE) {
        // Proceed to delete selected stocks from 'stocks' table
        $delete_sql = "DELETE FROM stocks WHERE SQ IN (
            SELECT SQ FROM selected_stocks
        )";

        if ($conn->query($delete_sql) === TRUE) {
            echo "Selected stocks updated and deleted successfully.";
        } else {
            echo "Error deleting selected stocks: " . $conn->error;
        }
    } else {
        echo "Error updating realstock: " . $conn->error;
    }

    $conn->close();
}
?>







<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Example: Delete specific rows from selected_stocks
$sqlDelete = "DELETE FROM selected_stocks";

if (mysqli_query($conn, $sqlDelete)) {
    echo "Selected rows deleted successfully.";
} else {
    echo "Error deleting rows: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>
