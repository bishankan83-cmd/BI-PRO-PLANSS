<!DOCTYPE html>
<html>
<head>
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f7f7f7;
    margin: 0;
    padding: 0;
  }

  #container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  h1 {
    color: #3498db; /* Blue color */
    text-align: center;
  }

  form {
    margin-bottom: 20px;
  }

  label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #666;
  }

  input[type="date"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    outline: none;
  }

  input[type="submit"] {
    display: block;
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #3498db; /* Blue color */
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }

  input[type="submit"]:hover {
    background-color: #2980b9; /* Slightly darker blue on hover */
  }

  table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 20px;
  }

  th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
  }

  th {
    background-color: #f2f2f2;
    font-weight: bold;
  }

  tr:nth-child(even) {
    background-color: #f2f2f2;
  }
</style>
</head>
<body>

<div id="container">
  <h1>Task Management Data</h1>

  <form method="post">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date">
    <input type="submit" value="Get Data">
  </form>
  <form action="check_indi2.php" method="post">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date">
    <input type="submit" name="export_excel" value="Export to Excel">
  </form>
  <?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $selected_start_date = $_POST["start_date"];
      echo "<h2>Selected Start Date: " . $selected_start_date . "</h2>";
  }

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

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $start_date = $_POST["start_date"];

      // SQL query to retrieve related data including press_id and press_name
      $sql = "SELECT md.icode, md.id_count, cv.cavity_name, ml.mold_name, pc.press_id, p.press_name, md.end_date, md.start_time
      FROM merged_data md
      LEFT JOIN cavity cv ON md.cavity_id = cv.cavity_id
      LEFT JOIN mold ml ON md.mold_id = ml.mold_id
      LEFT JOIN press_cavity pc ON md.cavity_id = pc.cavity_id
      LEFT JOIN press p ON pc.press_id = p.press_id
      WHERE md.start_date = '$start_date'";

      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        echo "<table><tr><th>icode</th><th>Description</th><th>Cavity name</th><th>Mold name</th><th>Press Name</th><th>To be</th><th>Start Time</th><th>End Time</th></tr>";
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $icode = $row["icode"];
            $cavity_name = $row["cavity_name"];
            $mold_name = $row["mold_name"];
            $id_count = $row["id_count"];
            $press_id = $row["press_id"];
            $press_name = $row["press_name"];
            $end_date = $row["end_date"];
            $start_date_db = $row["start_time"]; // Fetch start_date from the query result
            
            // Fetch description from the "tire" table based on the fetched icode
            $description_sql = "SELECT description FROM tire WHERE icode = '$icode'";
            $description_result = $conn->query($description_sql);
            $description = "";
    
            if ($description_result->num_rows > 0) {
                $description_row = $description_result->fetch_assoc();
                $description = $description_row["description"];
            }


            
    
            echo "<tr><td>" . $icode . "</td><td>" . $description . "</td><td>" . $cavity_name . "</td><td>" . $mold_name . "</td><td>" . $press_name . "</td><td>" . $id_count . "</td><td>" . $start_date_db . "</td><td>" . $end_date . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "No results for the given start date.";
    }
  }

  // Close connection
  $conn->close();
  ?>

</div>

</body>
</html>