<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $record_id = $_GET['id'];
    
    $sql = "SELECT * FROM template WHERE id = $record_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Edit Record</title>
        </head>
        <body>
            <h2>Edit Record</h2>
            <form method="post" action="update.php">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                ICode: <input type="text" name="icode" value="<?php echo $row['icode']; ?>"><br>
                CStock: <input type="text" name="cstock" value="<?php echo $row['cstock']; ?>"><br>
                Date: <input type="text" name="date" value="<?php echo $row['date']; ?>"><br>
                Shift: <input type="text" name="shift" value="<?php echo $row['shift']; ?>"><br>
                Description: <input type="text" name="description" value="<?php echo $row['description']; ?>"><br>
                <input type="submit" value="Update">
            </form>
        </body>
        </html>
        <?php
    }
} else {
    echo "Record ID not provided.";
}

$conn->close();
?>
