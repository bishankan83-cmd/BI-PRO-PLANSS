








<h2>Filtered Results</h2>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redirect Button Example</title>
</head>
<body>
  <button onclick="window.location.href = 'worder_result_edit.php';"> Edit Work Order</button>
</body>
</html>













<?php

// MySQL database connection details
$servername = "localhost"; // Replace with your MySQL server name
$username = "planatir_task_managemen"; // Replace with your MySQL username
$password = "Bishan@1919"; // Replace with your MySQL password
$dbname = "planatir_task_managemen"; // Replace with your MySQL database name

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to delete data from the rword table
    $sql = "DELETE FROM `worder_result`";

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the database connection
$conn = null;

?>








<!DOCTYPE html>
<html>
<head>
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
  
    
    <?php
    // Database connection parameters
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $dbname = "planatir_task_managemen";

    // Establish a database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize variables for filtering
    $conditions = [];
    $params = [];

    // Build the SQL query based on provided filters
    $sql = "SELECT * FROM worder WHERE 1";

    if (!empty($_GET['customer'])) {
        $conditions[] = "Customer LIKE :customer";
        $params['customer'] = '%' . $_GET['customer'] . '%';
    }

    if (!empty($_GET['wono'])) {
        $conditions[] = "wono LIKE :wono";
        $params['wono'] = '%' . $_GET['wono'] . '%';
    }

    if (!empty($_GET['ref'])) {
        $conditions[] = "ref LIKE :ref";
        $params['ref'] = '%' . $_GET['ref'] . '%';
    }

    if (!empty($_GET['erp'])) {
        $conditions[] = "erp LIKE :erp";
        $params['erp'] = '%' . $_GET['erp'] . '%';
    }

    // If there are conditions, concatenate them with AND
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // Prepare and execute the SQL query with filters
    $stmt = $conn->prepare($sql);

    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display results
    if (count($results) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Date</th><th>Customer</th><th>WO No</th><th>Reference</th><th>ERP</th><th>Item Code</th><th>Size</th><th>Brand</th><th>Color</th><th>Fit</th><th>Rim</th><th>Construction</th><th>Weight</th><th>PTV</th><th>New</th><th>CBM</th><th>KGS</th></tr>";
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Customer']) . "</td>";
            echo "<td>" . htmlspecialchars($row['wono']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ref']) . "</td>";
            echo "<td>" . htmlspecialchars($row['erp']) . "</td>";
            echo "<td>" . htmlspecialchars($row['icode']) . "</td>";
            echo "<td>" . htmlspecialchars($row['t_size']) . "</td>";
            echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
            echo "<td>" . htmlspecialchars($row['col']) . "</td>";
            echo "<td>" . htmlspecialchars($row['fit']) . "</td>";
            echo "<td>" . htmlspecialchars($row['rim']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cons']) . "</td>";
            echo "<td>" . htmlspecialchars($row['fweight']) . "</td>";
            echo "<td>" . htmlspecialchars($row['ptv']) . "</td>";
            echo "<td>" . htmlspecialchars($row['new']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cbm']) . "</td>";
            echo "<td>" . htmlspecialchars($row['kgs']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No results found.";
    }

    // Close the connection
    $conn = null;
    ?>

</body>
</html>



<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

try {
    // Connect to your target database
    $conn_target = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn_target->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Iterate through fetched results and insert into worder_result table
    foreach ($results as $row) {
        $sql_insert = "INSERT INTO worder_result (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, Cons, fWeight, ptv, new, cbm, kgs)
                       VALUES (:date, :customer, :wono, :ref, :erp, :icode, :t_size, :brand, :col, :fit, :rim, :cons, :fweight, :ptv, :new, :cbm, :kgs)";

        $stmt_insert = $conn_target->prepare($sql_insert);
        $stmt_insert->execute([
            ':date' => $row['date'],
            ':customer' => $row['Customer'],
            ':wono' => $row['wono'],
            ':ref' => $row['ref'],
            ':erp' => $row['erp'],
            ':icode' => $row['icode'],
            ':t_size' => $row['t_size'],
            ':brand' => $row['brand'],
            ':col' => $row['col'],
            ':fit' => $row['fit'],
            ':rim' => $row['rim'],
            ':cons' => $row['cons'],
            ':fweight' => $row['fweight'],
            ':ptv' => $row['ptv'],
            ':new' => $row['new'],
            ':cbm' => $row['cbm'],
            ':kgs' => $row['kgs']
        ]);
    }

    echo "Data inserted successfully into worder_result.";

    // Close the connection to your target database
    $conn_target = null;
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection to your main database
$conn = null;
?>
