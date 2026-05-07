<!DOCTYPE html>
<html>
<head>
    <title>Tire Filter</title>
</head>
<body>
    <h2>Tire Filter</h2>
    <form method="get" action="">
        <label for="icode">Tire Code:</label>
        <input type="text" id="icode" name="icode" placeholder="Enter Tire Code" value="<?php echo isset($_GET['icode']) ? htmlspecialchars($_GET['icode']) : ''; ?>">

        <label for="description">Description:</label>
        <input type="text" id="description" name="description" placeholder="Enter Description" value="<?php echo isset($_GET['description']) ? htmlspecialchars($_GET['description']) : ''; ?>">

        <button type="submit">Apply Filter</button>
    </form>

    <?php
    // PHP code for fetching and displaying filtered data goes here
    // Database connection parameters
    $hostname = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    // Create connection
    $conn = new mysqli($hostname, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Initialize variables to hold filter values
    $icodeFilter = isset($_GET['icode']) ? $_GET['icode'] : '';
    $descriptionFilter = isset($_GET['description']) ? $_GET['description'] : '';

    // Prepare SQL query with filters
    $sql = "SELECT tire_mold.icode, tire_mold.mold_id, tire_details.description 
            FROM tire_mold 
            INNER JOIN tire_details ON tire_mold.icode = tire_details.icode";

    // Add filters if provided
    if (!empty($icodeFilter)) {
        $sql .= " WHERE tire_mold.icode LIKE '%$icodeFilter%'";
    }

    if (!empty($descriptionFilter)) {
        // Add 'AND' if a filter has already been applied
        $sql .= empty($icodeFilter) ? " WHERE " : " AND ";
        $sql .= " tire_details.description LIKE '%$descriptionFilter%'";
    }

    // Execute the query
    $result = $conn->query($sql);

    echo "<table border='1'>";
    echo "<tr><th>Tire Code</th><th>Description</th><th>Mold name</th></tr>";

    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["icode"]. "</td><td>" . $row["description"]. "</td><td>" . $row["mold_id"]. "</td></tr>";
        }
    } else {
        echo "<tr><td colspan='3'>0 results</td></tr>";
    }
    echo "</table>";

    $conn->close();
    ?>
</body>
</html>
