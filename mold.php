<?php
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

// Insert data if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $mold_id = $_POST["mold_id"];
    $mold_name = $_POST["mold_name"];
    $quantity = $_POST["quantity"];
    $availability_date = $_POST["availability_date"];
    
    // Call the insertMold function
    insertMold($mold_id, $mold_name, $quantity, $availability_date);
}

// Update data if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $mold_id = $_POST["mold_id"];
    $new_quantity = $_POST["new_quantity"];
    
    // Call the updateMold function
    updateMold($mold_id, $new_quantity);
}

// Delete data if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $mold_id = $_POST["mold_id"];
    
    // Call the deleteMold function
    deleteMold($mold_id);
}

// Function to insert data into the mold table
function insertMold($mold_id, $mold_name, $quantity, $availability_date) {
    global $conn;
    $sql = "INSERT INTO mold (mold_id, mold_name, quantity, availability_date) VALUES ('$mold_id', '$mold_name', $quantity, '$availability_date')";
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Function to update data in the mold table
function updateMold($mold_id, $new_quantity) {
    global $conn;
    $sql = "UPDATE mold SET quantity=$new_quantity WHERE mold_id='$mold_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Function to delete data from the mold table
function deleteMold($mold_id) {
    global $conn;
    $sql = "DELETE FROM mold WHERE mold_id='$mold_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mold Management System</title>
</head>
<body>
    <h1>Mold Management System</h1>
    
    <h2>Add New Mold</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        Mold ID: <input type="text" name="mold_id"><br>
        Mold Name: <input type="text" name="mold_name"><br>
        Quantity: <input type="number" name="quantity"><br>
        Availability Date: <input type="date" name="availability_date"><br>
        <input type="submit" name="submit" value="Add Mold">
    </form>
    
 
    <h2>Delete Mold</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        Mold ID: <input type="text" name="mold_id"><br>
        <input type="submit" name="delete" value="Delete Mold">
    </form>
</body>
</html>
