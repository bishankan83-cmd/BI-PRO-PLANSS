<?php
if (isset($_POST['icode'])) {
    $icode = $_POST['icode'];

    // Establish a database connection
    $con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    // Use a prepared statement to prevent SQL injection
    $query = "SELECT * FROM realstock WHERE icode = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $icode);
    mysqli_stmt_execute($stmt);

    $query_run = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($query_run) > 0) {
        while ($items = mysqli_fetch_assoc($query_run)) {
            echo "<tr>";
            echo "<td>" . $items['icode'] . "</td>";
            echo "<td>" . $items['t_size'] . "</td>";
            echo "<td>" . $items['brand'] . "</td>";
            echo "<td>" . $items['col'] . "</td>";
            echo "<td>" . $items['rim'] . "</td>";
            echo "<td>" . $items['cstock'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No results found for the provided Item Code: $icode</td></tr>";
    }
} else {
    echo "<tr><td colspan='6'>Please enter an Item Code to search for.</td></tr>";
}
?>
