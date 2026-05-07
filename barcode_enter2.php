<?php

// MySQLi connection parameters
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

// SQL query to update data in another_table_one based on barcode table
$sql = "UPDATE another_table_name AS a
        JOIN barcode AS b ON 1=1
        SET a.quality_approved = b.quality_approved,
            a.expire_date = b.expire_date,
            a.staff_name = b.staff_name";

if ($conn->query($sql) === TRUE) {
    echo "Data updated successfully";
} else {
    echo "Error updating data: " . $conn->error;
}

// Close connection
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXT</title>
</head>
<body>

<!-- Button to redirect -->
<button id="redirectButton">Click to Redirect</button>

<script>
// JavaScript to handle button click event
document.getElementById("redirectButton").onclick = function() {
    // Redirect to another page
    window.location.href = "lab2.php";
};
</script>

</body>
</html>
