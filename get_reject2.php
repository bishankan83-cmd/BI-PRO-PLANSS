<style>
        /* Your CSS styles */

        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif;
        }

        h1 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="date"],
        select {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
        }

        .btn-container {
            margin-top: 20px;
            text-align: center;
        }

        input[type="button"],
        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="button"]:hover,
        input[type="submit"]:hover {
            background-color: #333333;
        }
    </style>



<?php
// Database connection parameters
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create a database connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// SQL query to retrieve data from the template table
$query = "SELECT * FROM template2b2";

// Execute the query
$result = $mysqli->query($query);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Item Code</th><th>Description</th><th>No Of Reject</th><th>Date</th><th>Shift</th><th>Reason</th></tr>";

    // Fetch and display data
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['icode'] . "</td>";

         // Query the tire table for the description based on the icode
         $tireQuery = "SELECT description FROM tire WHERE icode = '{$row['icode']}'";
         $tireResult = $mysqli->query($tireQuery);
 
         if ($tireResult && $tireRow = $tireResult->fetch_assoc()) {
             echo "<td>" . $tireRow['description'] . "</td>";
         } else {
             echo "<td>No description found</td>";
         }

        echo "<td>" . $row['cstock'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['shift'] . "</td>";
        echo "<td>" . $row['reason'] . "</td>";

       

        echo "</tr>";
    }

    echo "</table>";

    // Free the result sets
    $result->free();
} else {
    echo "Error: " . $mysqli->error;
}

// Close the database connection
$mysqli->close();
?>
