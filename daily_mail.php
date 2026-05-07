<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Establish a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Continuously check for new records in the 'template' table
while (true) {
    $sql = "SELECT * FROM `template` ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Send an email
        $to = "bishankan83@gmail.com";
        $subject = "New Data Inserted";
        $message = "New data inserted with ID: " . $row["id"];
        $headers = "From: your_email@example.com";

        mail($to, $subject, $message, $headers);

        // You can add additional processing logic here if needed

        // Sleep for a while before checking again
        sleep(60); // Sleep for 60 seconds (adjust as needed)
    } else {
        // No new records found, sleep for a while before checking again
        sleep(60); // Sleep for 60 seconds (adjust as needed)
    }
}

// Close the database connection when done (this code will never execute)
$conn->close();
?>
