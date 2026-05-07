<?php
// Replace with your actual database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query
$sql = "
SELECT
  n.id,
  n.plan_id,
  n.tires_per_mold,
  n.creation_time,
  n.total_id_count,
  n.icode,
  n.mold_id,
  n.cavity_id,
  n.difference,
  p.end_date
FROM
  new_table3 n
LEFT JOIN
  plannew p ON n.plan_id = p.plan_id;
";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through the result set
    while ($row = $result->fetch_assoc()) {
      

        // Update the end_date in the cavity table
        $cavityId = $row["cavity_id"];
        $endDate = $row["end_date"];

        $updateCavitySql = "UPDATE cavity SET availability_date = '$endDate' WHERE cavity_id = $cavityId";
        if ($conn->query($updateCavitySql) === TRUE) {
           
        } else {
           
        }

        // Update the end_date in the mold table
        $moldId = $row["mold_id"];
        $endDate = $row["end_date"];
        
        $updateMoldSql = "UPDATE mold SET availability_date = '$endDate' WHERE mold_id = $moldId";
        if ($conn->query($updateMoldSql) === TRUE) {
           // echo "Mold end_date updated successfully<br>";
        } else {
            //echo "Error updating mold end_date: " . $conn->error . "<br>";
        }
    }
} else {
    echo "0 results";
}

// Close the connection
$conn->close();
header("Location: deletemerge.php");
exit();
?>