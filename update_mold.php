<!DOCTYPE html>
<html>
<head>
    <title>Tire Filter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            margin: 20px auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Enable horizontal scrolling */
        }

        table {
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed; /* Fixed layout to enable fixed table headers */
            width: 100%;
        }

        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
            
        }

        th {
            background-color: #f28018;
            color: #ffffff;
            font-weight: bold;
            position: sticky; /* Sticky position to fix headers */
            top: 0; /* Position headers at the top */
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            margin-right: 10px;
        }

        input[type="text"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button[type="submit"] {
            padding: 8px 16px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #f28018 ;
        }
    </style>
</head>
<body>
    <div class="container">
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
        $sql = "SELECT tire_mold.id, tire_mold.icode, tire_mold.mold_id, tire_details.description 
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

        echo "<form method='post' action='update_mold2.php'>";
        echo "<table border='1'>";
        echo "<tr><th>Tire Code</th><th>Description</th><th>Mold name</th><th>Action</th></tr>";

        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["icode"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
                echo "<td><input type='text' name='mold_id[]' value='" . htmlspecialchars($row["mold_id"]) . "'></td>";
                // Include hidden input for id
                echo "<input type='hidden' name='id[]' value='" . $row["id"] . "'>";
                echo "<td><button type='submit'>Update</button></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>0 results</td></tr>";
        }
        echo "</table>";
        echo "</form>";

        $conn->close();
        ?>
    </div>
</body>
</html>
