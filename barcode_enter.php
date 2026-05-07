




<?php
// Database connection details
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

// SQL query to delete all data from the table
$sql = "DELETE FROM barcode";

if ($conn->query($sql) === TRUE) {
   
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Barcode Data</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Insert Barcode Data</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="quality_approved">Quality Approved:</label><br>
        <input type="date" id="quality_approved" name="quality_approved" required><br><br>

        <label for="expire_date">Expire Date:</label><br>
        <input type="date" id="expire_date" name="expire_date"><br><br>

        <label for="staff_name">Staff Name:</label><br>
        <input type="text" id="staff_name" name="staff_name" required><br><br>

        <input type="submit" value="Submit">
    </form>

    <hr>

    <h2>Barcode Data</h2>
    <table>
        <thead>
            <tr>
                <th>Quality Approved</th>
                <th>Expire Date</th>
                <th>Staff Name</th>
            </tr>
        </thead>
        <tbody>
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

            // If form is submitted, insert data into the barcode table
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $quality_approved = $_POST["quality_approved"];
                $expire_date = $_POST["expire_date"];
                $staff_name = $_POST["staff_name"];

                $sql = "INSERT INTO barcode (quality_approved, expire_date, staff_name) VALUES ('$quality_approved', '$expire_date', '$staff_name')";

                if ($conn->query($sql) === TRUE) {
                    echo "<p>New record created successfully</p>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }

            // Fetch and display data from the barcode table
            $sql = "SELECT quality_approved, expire_date, staff_name FROM barcode";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["quality_approved"] . "</td>";
                    echo "<td>" . $row["expire_date"] . "</td>";
                    echo "<td>" . $row["staff_name"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No data found</td></tr>";
            }

            // Close connection
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXT</title>
</head>
<body>

<!-- Button to redirect -->
<button id="redirectButton">Click to Redirect</button>

<script>
// JavaScript to handle button click event
document.getElementById("redirectButton").onclick = function() {
    // Redirect to another page
    window.location.href = "barcode_enter2.php";
};
</script>

</body>
</html>
