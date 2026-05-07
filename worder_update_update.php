




<?php
// Database connection
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

// Query to check the number of unique `erp` values
$sql = "SELECT COUNT(DISTINCT erp) as unique_erp_count FROM tobeplan";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the result
    $row = $result->fetch_assoc();
    if ($row['unique_erp_count'] > 1) {
        // If there is more than one unique `erp`, redirect to plannew45new2 page and show the message
        echo "<script>
            alert('Please generate before planning');
            window.location.href = 'plannew45new2.php';
        </script>";
    } elseif ($row['unique_erp_count'] == 1) {
        // If there is exactly one unique `erp`, check if there's any data
        $sql_data = "SELECT COUNT(*) as count FROM tobeplan";
        $result_data = $conn->query($sql_data);
        if ($result_data->num_rows > 0) {
            $row_data = $result_data->fetch_assoc();
            if ($row_data['count'] > 0) {
                // If there is data in the table, redirect to plannew45 page and show the message
                echo "<script>
                    alert('Please generate before planning');
                    window.location.href = 'plannew45.php';
                </script>";
            } else {
                // If there is no data, proceed with the normal flow
                echo "No data in the table. Proceeding as normal.";
            }
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // If there are no `erp` values, proceed with the normal flow
        echo "No data in the table. Proceeding as normal.";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>











<!DOCTYPE html>
<html>
<head>
    <title>Filter Page</title>
</head>
<body>
    <h2>Filter by Customer, WO No, Reference, and ERP</h2>
    <form action="worder_result.php" method="GET">
        <label>Customer:</label>
        <select name="customer">
            <option value="">Select Customer</option>
            <?php
            // Database connection parameters
            $servername = "localhost";
            $username = "planatir_task_managemen";
            $password = "Bishan@1919";
            $dbname = "planatir_task_managemen";

            // Establish a database connection
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query to fetch distinct customers
            $sql_customer = "SELECT DISTINCT Customer FROM worder";
            $stmt_customer = $conn->prepare($sql_customer);
            $stmt_customer->execute();
            $customers = $stmt_customer->fetchAll(PDO::FETCH_COLUMN);

            // Display options
            foreach ($customers as $customer) {
                echo "<option value='" . htmlspecialchars($customer) . "'>" . htmlspecialchars($customer) . "</option>";
            }

            // Close the connection
            $conn = null;
            ?>
        </select>
        <br><br>
        <label>WO No:</label>
        <select name="wono">
            <option value="">Select WO No</option>
            <?php
            // Re-establish connection if needed
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query to fetch distinct WO Nos
            $sql_wono = "SELECT DISTINCT wono FROM worder";
            $stmt_wono = $conn->prepare($sql_wono);
            $stmt_wono->execute();
            $wonos = $stmt_wono->fetchAll(PDO::FETCH_COLUMN);

            // Display options
            foreach ($wonos as $wono) {
                echo "<option value='" . htmlspecialchars($wono) . "'>" . htmlspecialchars($wono) . "</option>";
            }

            // Close the connection
            $conn = null;
            ?>
        </select>
        <br><br>
        <label>Reference:</label>
        <select name="ref">
            <option value="">Select Reference</option>
            <?php
            // Re-establish connection if needed
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query to fetch distinct References
            $sql_ref = "SELECT DISTINCT ref FROM worder";
            $stmt_ref = $conn->prepare($sql_ref);
            $stmt_ref->execute();
            $refs = $stmt_ref->fetchAll(PDO::FETCH_COLUMN);

            // Display options
            foreach ($refs as $ref) {
                echo "<option value='" . htmlspecialchars($ref) . "'>" . htmlspecialchars($ref) . "</option>";
            }

            // Close the connection
            $conn = null;
            ?>
        </select>
        <br><br>
        <label>ERP:</label>
        <select name="erp">
            <option value="">Select ERP</option>
            <?php
            // Re-establish connection if needed
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query to fetch distinct ERPs
            $sql_erp = "SELECT DISTINCT erp FROM worder";
            $stmt_erp = $conn->prepare($sql_erp);
            $stmt_erp->execute();
            $erps = $stmt_erp->fetchAll(PDO::FETCH_COLUMN);

            // Display options
            foreach ($erps as $erp) {
                echo "<option value='" . htmlspecialchars($erp) . "'>" . htmlspecialchars($erp) . "</option>";
            }

            // Close the connection
            $conn = null;
            ?>
        </select>
        <br><br>
        <input type="submit" value="Filter">
    </form>
</body>
</html>
