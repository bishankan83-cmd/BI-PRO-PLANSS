<?php
// Replace these variables with your actual database credentials
$hostname = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';


// Create a database connection
$connection = mysqli_connect($hostname, $username, $password, $database);

// Check if the connection was successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to retrieve the highest availability_date for each mold_id and cavity_id
$query = "SELECT
    pn.mold_id,
    pn.cavity_id,
    MAX(m.availability_date) AS highest_mold_availability_date,
    MAX(c.availability_date) AS highest_cavity_availability_date
FROM
    plannew pn
LEFT JOIN
    mold m ON pn.mold_id = m.mold_id
LEFT JOIN
    cavity c ON pn.cavity_id = c.cavity_id
GROUP BY
    pn.mold_id,
    pn.cavity_id";

// Execute the query
$result = mysqli_query($connection, $query);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Display the results
while ($row = mysqli_fetch_assoc($result)) {
    echo "Mold ID: " . $row['mold_id'] . " | Cavity ID: " . $row['cavity_id'] . " | Highest Mold Availability Date: " . $row['highest_mold_availability_date'] . " | Highest Cavity Availability Date: " . $row['highest_cavity_availability_date'] . "<br>";
}

// Close the database connection
mysqli_close($connection);
?>