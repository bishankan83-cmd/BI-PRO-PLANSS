<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Connect to the MySQL database
   $servername = "localhost";
   $username = "planatir_task_managemen";
   $password = "Bishan@1919";
   $dbname = "planatir_task_managemen";

   $conn = new mysqli($servername, $username, $password, $dbname);

   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }

   $id = $_POST['id'];
   $quantity = $_POST['quantity'];

   // Update the "quantity" in the 'dwork' table
   $sql = "UPDATE dwork SET quantity = '$quantity' WHERE id = $id";

   if ($conn->query($sql) === TRUE) {
       echo "Quantity updated successfully.";
       header("Location: dwork.php"); // Redirect to the original page.
       exit();
   } else {
       echo "Error updating quantity: " . $conn->error;
   }

   $conn->close();
}
?>
