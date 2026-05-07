<?php
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
   // Connect to the MySQL database
   $servername = "localhost";
   $username = "planatir_task_managemen";
   $password = "Bishan@1919";
   $dbname = "planatir_task_managemen";

   $conn = new mysqli($servername, $username, $password, $dbname);

   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }

   $id = $_GET['id'];

   // Fetch the data for the specified ID from the 'dwork' table
   $sql = "SELECT id, quantity FROM dwork WHERE id = $id";
   $result = $conn->query($sql);

   if ($result->num_rows == 1) {
       $row = $result->fetch_assoc();
   } else {
       echo "Record not found.";
       exit();
   }
} else {
   echo "Invalid request.";
   exit();
}
?>

<form action="update1.php" method="post">
   <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
   Quantity: <input type="text" name="quantity" value="<?php echo $row['quantity']; ?>"><br>
   <input type="submit" value="Save">
</form>

<?php
$conn->close();
?>
