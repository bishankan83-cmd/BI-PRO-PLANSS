



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Button</title>
</head>
<body>
    <!-- Form with a tton that redirects to another page -->
    <form action="worder_result1.php" method="get">
        <button type="submit">click Back</button>
    </form>
</body>
</html>







<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input (prevent SQL injection)
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, $input);
}

// Page to redirect after operation
$redirectPage = "success_page.php"; // Change this to your desired page

// Check if form submitted for updating, deleting, or inserting data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update data
    if (isset($_POST['update'])) {
        $id = sanitize($conn, $_POST['id']);
        $icode = sanitize($conn, $_POST['icode']);
        $t_size = sanitize($conn, $_POST['t_size']);
        $brand = sanitize($conn, $_POST['brand']);
        $col = sanitize($conn, $_POST['col']);
        $fit = sanitize($conn, $_POST['fit']);
        $rim = sanitize($conn, $_POST['rim']);
        $cons = sanitize($conn, $_POST['cons']);
        $fweight = sanitize($conn, $_POST['fweight']);
        $ptv = sanitize($conn, $_POST['ptv']);
        $new = sanitize($conn, $_POST['new']);
        $cbm = sanitize($conn, $_POST['cbm']);
        $kgs = sanitize($conn, $_POST['kgs']);

        // Update query
        $sql_update = "UPDATE worder_result SET 
                        icode='$icode', 
                        t_size='$t_size', 
                        brand='$brand', 
                        col='$col', 
                        fit='$fit', 
                        rim='$rim', 
                        cons='$cons', 
                        fweight='$fweight', 
                        ptv='$ptv', 
                        new='$new', 
                        cbm='$cbm', 
                        kgs='$kgs' 
                        WHERE id='$id'";
        
        if ($conn->query($sql_update) === TRUE) {
            header("Location: $redirectPage?message=update_success");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    // Delete data
    if (isset($_POST['delete'])) {
        $id = sanitize($conn, $_POST['id']);

        // Delete query
        $sql_delete = "DELETE FROM worder_result WHERE id='$id'";

        if ($conn->query($sql_delete) === TRUE) {
            header("Location: $redirectPage?message=delete_success");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }

    // Insert new data
    if (isset($_POST['insert'])) {
        $icode = sanitize($conn, $_POST['icode']);
        $t_size = sanitize($conn, $_POST['t_size']);
        $brand = sanitize($conn, $_POST['brand']);
        $col = sanitize($conn, $_POST['col']);
        $fit = sanitize($conn, $_POST['fit']);
        $rim = sanitize($conn, $_POST['rim']);
        $cons = sanitize($conn, $_POST['cons']);
        $fweight = sanitize($conn, $_POST['fweight']);
        $ptv = sanitize($conn, $_POST['ptv']);
        $new = sanitize($conn, $_POST['new']);
        $cbm = sanitize($conn, $_POST['cbm']);
        $kgs = sanitize($conn, $_POST['kgs']);

        // Insert query
        $sql_insert = "INSERT INTO worder_result (icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs) 
                       VALUES ('$icode', '$t_size', '$brand', '$col', '$fit', '$rim', '$cons', '$fweight', '$ptv', '$new', '$cbm', '$kgs')";
        
        if ($conn->query($sql_insert) === TRUE) {
            header("Location: $redirectPage?message=insert_success");
            exit();
        } else {
            echo "Error inserting record: " . $conn->error;
        }
    }
}

// Query to fetch data from worder_result table
$sql_select = "SELECT * FROM worder_result";
$result = $conn->query($sql_select);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h2>Edit Data</h2>


    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Button</title>
</head>
<body>
    <!-- Form with a button that redirects to another page -->
    <form action="new_rev.php" method="get">
        <button type="submit">Re Plan Order</button>
    </form>
</body>
</html>
    <table>
        <tr>
            <th>ICode</th>
            <th>T Size</th>
            <th>Brand</th>
            <th>Color</th>
            <th>Fit</th>
            <th>Rim</th>
            <th>Cons</th>
            <th>F Weight</th>
            <th>PTV</th>
            <th>New</th>
            <th>CBM</th>
            <th>KGS</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
                echo "<input type='hidden' name='id' value='" . $row["id"] . "'>";
                echo "<td><input type='text' name='icode' value='" . htmlspecialchars($row["icode"]) . "'></td>";
                echo "<td><input type='text' name='t_size' value='" . htmlspecialchars($row["t_size"]) . "'></td>";
                echo "<td><input type='text' name='brand' value='" . htmlspecialchars($row["brand"]) . "'></td>";
                echo "<td><input type='text' name='col' value='" . htmlspecialchars($row["col"]) . "'></td>";
                echo "<td><input type='text' name='fit' value='" . htmlspecialchars($row["fit"]) . "'></td>";
                echo "<td><input type='text' name='rim' value='" . htmlspecialchars($row["rim"]) . "'></td>";
                echo "<td><input type='text' name='cons' value='" . htmlspecialchars($row["cons"]) . "'></td>";
                echo "<td><input type='text' name='fweight' value='" . htmlspecialchars($row["fweight"]) . "'></td>";
                echo "<td><input type='text' name='ptv' value='" . htmlspecialchars($row["ptv"]) . "'></td>";
                echo "<td><input type='text' name='new' value='" . htmlspecialchars($row["new"]) . "'></td>";
                echo "<td><input type='text' name='cbm' value='" . htmlspecialchars($row["cbm"]) . "'></td>";
                echo "<td><input type='text' name='kgs' value='" . htmlspecialchars($row["kgs"]) . "'></td>";
                echo "<td>";
                echo "<input type='submit' name='update' value='Update'>";
                echo "<input type='submit' name='delete' value='Delete'>";
                echo "</td>";
                echo "</form>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='13'>No records found</td></tr>";
        }
        ?>
        <!-- Form for inserting new record -->
        <tr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <td><input type="text" name="icode" required></td>
                <td><input type="text" name="t_size" required></td>
                <td><input type="text" name="brand" required></td>
                <td><input type="text" name="col" required></td>
                <td><input type="text" name="fit" required></td>
                <td><input type="text" name="rim" required></td>
                <td><input type="text" name="cons" required></td>
                <td><input type="text" name="fweight" required></td>
                <td><input type="text" name="ptv" required></td>
                <td><input type="text" name="new" required></td>
                <td><input type="text" name="cbm" required></td>
                <td><input type="text" name="kgs" required></td>
                <td><input type="submit" name="insert" value="Insert"></td>
            </form>
        </tr>
    </table>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
