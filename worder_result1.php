<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtered Results</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Filtered Results</h2>

<form action="worder_result.php" method="GET">
    <label for="customer">Customer:</label>
    <select id="customer" name="customer">
        <option value="">Select Customer</option>
        <?php
        // Database connection parameters
        $servername = "localhost";
        $username = "planatir_task_managemen";
        $password = "Bishan@1919";
        $dbname = "planatir_task_managemen";

        try {
            // Establish a database connection
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query to fetch distinct customers
            $sql = "SELECT DISTINCT Customer FROM worder ORDER BY Customer";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Generate options for dropdown
            foreach ($customers as $customer) {
                echo "<option value='" . htmlspecialchars($customer) . "'>" . htmlspecialchars($customer) . "</option>";
            }

        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </select>

    <label for="wono">WO No:</label>
    <select id="wono" name="wono">
        <option value="">Select WO No</option>
        <?php
        // Query to fetch distinct WO numbers
        $sql = "SELECT DISTINCT wono FROM worder ORDER BY wono";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $wonos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Generate options for dropdown
        foreach ($wonos as $wono) {
            echo "<option value='" . htmlspecialchars($wono) . "'>" . htmlspecialchars($wono) . "</option>";
        }
        ?>
    </select>

    <label for="ref">Reference:</label>
    <select id="ref" name="ref">
        <option value="">Select Reference</option>
        <?php
        // Query to fetch distinct references
        $sql = "SELECT DISTINCT ref FROM worder ORDER BY ref";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $refs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Generate options for dropdown
        foreach ($refs as $ref) {
            echo "<option value='" . htmlspecialchars($ref) . "'>" . htmlspecialchars($ref) . "</option>";
        }
        ?>
    </select>

    

    <input type="submit" value="Filter">
</form>

</body>
</html>
