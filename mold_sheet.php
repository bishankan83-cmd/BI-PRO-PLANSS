<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to duplicate data in each column of the 'mold_sheet' table and create a new table
$sql = "
CREATE TABLE IF NOT EXISTS `mold_sheet_duplicate` AS
SELECT
  `id`,
  `mold1` AS `duplicated_data`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold2`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold3`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold4`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold5`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold6`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold7`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold8`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold9`
FROM `mold_sheet`
UNION ALL
SELECT
  `id`,
  `mold10`
FROM `mold_sheet`
CROSS JOIN (
  SELECT 1 AS `iteration`
  UNION ALL
  SELECT 2 UNION ALL
  SELECT 3 UNION ALL
  SELECT 4 UNION ALL
  SELECT 5 UNION ALL
  SELECT 6 UNION ALL
  SELECT 7 UNION ALL
  SELECT 8 UNION ALL
  SELECT 9 UNION ALL
  SELECT 10 UNION ALL
  SELECT 11 UNION ALL
  SELECT 12 UNION ALL
  SELECT 13 UNION ALL
  SELECT 14 UNION ALL
  SELECT 15 UNION ALL
  SELECT 16 UNION ALL
  SELECT 17 UNION ALL
  SELECT 18 UNION ALL
  SELECT 19 UNION ALL
  SELECT 20 UNION ALL
  SELECT 21 UNION ALL
  SELECT 22
) AS numbers;
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data duplicated successfully!";
} else {
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
