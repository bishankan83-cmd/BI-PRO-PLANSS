

<!DOCTYPE html>
<html>
<head>
    <title>Insert Date into Database</title>
</head>
<body>
    <form method="post" action="">
        <label for="date_input">Enter Date:</label>
        <input type="date" id="date_input" name="date_input">
        <input type="submit" value="Insert Date">
    </form>
</body>
</html>


<?php
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$connection = new mysqli($hostname, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $givenDate = $_POST['date_input'];

    // Sanitize and validate the given date
    $givenDate = mysqli_real_escape_string($connection, $givenDate);

    // Modify the SQL statement to include the date_id column
    $sql = "INSERT INTO dates (date_id, dates_c) VALUES (1, '$givenDate')";

    if ($connection->query($sql) === TRUE) {
        // Close the database connection
        $connection->close();

        // Redirect to another page after successful insertion
        header("Location: daily_reject2.php");
        exit();
    } else {
        echo "Error inserting date: " . $connection->error;
    }
}

$connection->close();
?>
