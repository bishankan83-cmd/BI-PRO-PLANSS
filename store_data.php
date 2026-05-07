<?php
if (isset($_POST['submit'])) {
    $icode = $_POST['icode'];
    $t_size = $_POST['t_size'];
    $brand = $_POST['brand'];
    $col = $_POST['col'];
    $rim = $_POST['rim'];
    $cstock = $_POST['cstock'];

    // Connect to MySQL database
    $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Update the 'realstock' table
    $realStockUpdateSQL = "UPDATE realstock
        SET t_size = '$t_size',
            brand = '$brand',
            col = '$col',
            rim = '$rim',
            cstock = '$cstock'
        WHERE icode = '$icode'";

    // Update the 'stock' table
    $stockUpdateSQL = "UPDATE stock
        SET t_size = '$t_size',
            brand = '$brand',
            col = '$col',
            rim = '$rim',
            cstock = '$cstock'
        WHERE icode = '$icode'";

    // Perform the updates
    if (mysqli_query($conn, $realStockUpdateSQL) && mysqli_query($conn, $stockUpdateSQL)) {
        echo "Data stored successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
