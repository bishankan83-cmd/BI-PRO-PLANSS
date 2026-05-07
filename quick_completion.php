<!DOCTYPE html>
<html>
<head>
    <title>Execute Query</title>
</head>
<body>
    <form method="post">
        <input type="submit" name="execute_query" value="Execute Query">
    </form>

    <?php
// Establish a database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to insert adjusted records
$insertQuery = "
INSERT INTO new_table (erp, icode,description, press_id, press_name, mold_id, mold_name, cavity_id, cavity_name, start_date, end_date)
SELECT pp.erp, pp.icode, pp.description, pp.press_id, pp.press_name, pp.mold_id, pp.mold_name, pp.cavity_id, pp.cavity_name, pp.start_date, pp.end_date
FROM production_plan pp
INNER JOIN (
  SELECT icode, MIN(end_date) AS end_date
  FROM production_plan
  WHERE DATEDIFF(end_date, start_date) 
  GROUP BY icode
) AS subquery ON pp.icode = subquery.icode AND pp.end_date = subquery.end_date
GROUP BY pp.icode; -- Select only one option per tire type (assuming 'icode' represents the tire type);

";

// SQL queries to update availability dates
$updatePressQuery = "
UPDATE press p
INNER JOIN new_table nt ON p.press_id = nt.press_id
SET p.availability_date = nt.end_date;
";

$updateMoldQuery = "
UPDATE mold m
INNER JOIN new_table nt ON m.mold_id = nt.mold_id
SET m.availability_date = nt.end_date;
";

$updateCavityQuery = "
UPDATE cavity c
INNER JOIN new_table nt ON c.cavity_id = nt.cavity_id
SET c.availability_date = nt.end_date;
";

// Execute the insert query
if ($conn->query($insertQuery) === TRUE) {
    echo "Records inserted successfully.<br>";
} else {
    echo "Error inserting records: " . $conn->error;
}

// Execute the update queries
if ($conn->query($updatePressQuery) === TRUE) {
    echo "Press availability dates updated successfully.<br>";
} else {
    echo "Error updating press availability dates: " . $conn->error;
}

if ($conn->query($updateMoldQuery) === TRUE) {
    echo "Mold availability dates updated successfully.<br>";
} else {
    echo "Error updating mold availability dates: " . $conn->error;
}

if ($conn->query($updateCavityQuery) === TRUE) {
    echo "Cavity availability dates updated successfully.<br>";
} else {
    echo "Error updating cavity availability dates: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
