<?php

// Database connection parameters
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the bcompound98 table
$sql = "SELECT * FROM bcompound98";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Email parameters
    $to1 = "bishan.k@atire.com"; // Email recipient 1
    $to2 = "chandimal.c@atire.com"; // Email recipient 2

    $subject = "Update from Mixing Department";
    
    // Create the email header
    $message = "<html><body>";
    $message .= "<p>Dear Team,</p>";
    $message .= "<p>update has been made by the Mixing Department. Please review the details below and Re genarate the QR code.</p>";
    $message .= "<table border='1' cellspacing='0' cellpadding='5'>";
    $message .= "<tr>
                    <th>IID</th>
                    <th>ID</th>
                    <th>Input Date</th>
                    <th>Shift</th>
                    <th>Compound Name</th>
                    <th>Description</th>
                    <th>CStock</th>
                    <th>Batch</th>
                    <th>Pallet</th>
                    <th>Created At</th>
                    <th>Weight</th>
                    <th>Serial Number</th>
                 </tr>";
    
    // Fetching and formatting data
    while($row = $result->fetch_assoc()) {
        $message .= "<tr>
                        <td>" . $row["iid"] . "</td>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["inputDate"] . "</td>
                        <td>" . $row["shift"] . "</td>
                        <td>" . $row["compound_name"] . "</td>
                        <td>" . $row["description"] . "</td>
                        <td>" . $row["cstock"] . "</td>
                        <td>" . $row["batch"] . "</td>
                        <td>" . $row["pallet"] . "</td>
                        <td>" . $row["created_at"] . "</td>
                        <td>" . $row["weight"] . "</td>
                        <td>" . $row["serial_number"] . "</td>
                     </tr>";
    }

    $message .= "</table>";
    $message .= "<p>Best Regards,<br>BI PRO PLAN S </p>";
    $message .= "</body></html>";
    
    // Set content-type for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: planningtool@plan.atire.com";

    // Send email to recipient 1
    if (mail($to1, $subject, $message, $headers)) {
        echo "Email sent successfully to recipient 1";
    } else {
        echo "Failed to send email to recipient 1";
    }

    // Send email to recipient 2
    if (mail($to2, $subject, $message, $headers)) {
        echo "Email sent successfully to recipient 2";
    } else {
        echo "Failed to send email to recipient 2";
    }
} else {
    echo "No data found in bcompound98 table";
}

// Close connection
$conn->close();
header("Location: edit_mix2.php");
exit();

?>
