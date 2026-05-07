<!DOCTYPE html>
<html>
<head>
    <title>Date Range Query</title>
</head>
<body>
    <h2>Enter Date Range</h2>
    <form method="post" action="">
        Start Date: <input type="date" name="start_date" required>
        End Date: <input type="date" name="end_date" required>
        <input type="submit" name="submit" value="Submit">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve user input
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // MySQL server configuration
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $database = "planatir_task_managemen";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "SELECT DISTINCT p.id, p.erp_number, p.select_option, p.dispatch_date, r.ref
        FROM pros p
        INNER JOIN dwork2 r ON p.erp_number = r.erp
        WHERE p.dispatch_date BETWEEN '$start_date' AND '$end_date'
        ORDER BY p.id";

        // Execute SQL query
        $result = $conn->query($sql);

        // Check if any rows were returned
        if ($result->num_rows > 0) {
            // Output data of each row
            echo "<h2>Results</h2>";
            echo "<table border='1'><tr><th>ID</th><th>ERP Number</th><th>Select Option</th><th>Dispatch Date</th><th>Reference</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["id"]."</td><td>".$row["erp_number"]."</td><td>".$row["select_option"]."</td><td>".$row["dispatch_date"]."</td><td>".$row["ref"]."</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }

        // Close connection
        $conn->close();
    }
?>

    
</body>
</html>
