<?php

// Connect to the database
$conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

if (isset($_POST['submit'])) {
    $icode = $_POST['icode'];
    $t_size = $_POST['t_size'];
    $brand = $_POST['brand'];
    $col = $_POST['col'];
    $fit = $_POST['fit'];
    $rim = $_POST['rim'];
    $cstock = $_POST['cstock'];
    $date=$_POST['date'];
    $fweight=$_POST['fweight'];
    $shift=$_POST['shift'];

    

  // Connect to MySQL database
  $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

  // Check connection
  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  // Insert the form data into the 'stock' table


$sql1= "INSERT INTO daily_production (shift, date, icode, t_size, brand, fit, col, fweight, cstock)
VALUES ('$shift', '$date', '$icode', '$t_size', '$brand', '$fit', '$col', '$fweight', '$cstock')";

$sql2 = "UPDATE stock
        SET t_size = '$t_size',
            brand = '$brand',
            col = '$col',
            fit = '$fit',
            rim = '$rim',
            cstock = '$cstock'
        WHERE icode = '$icode'";


// Perform the SQL queries
if (mysqli_query($conn, $sql1) && mysqli_query($conn, $sql2)) {
  echo "Data stored and updated successfully!";
} else {
  echo "Error: " . mysqli_error($conn);
}

    mysqli_close($conn);
}
?>