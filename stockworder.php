
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

$sql = "SELECT work_order.id, MAX(work_order.datetime) AS datetime, work_order.erp, MAX(worder.ref) AS ref
FROM work_order
JOIN worder ON work_order.erp = worder.erp
GROUP BY work_order.erp
ORDER BY MAX(work_order.datetime) DESC";  // Order by datetime in descending order
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>
            <tr> <th>Reference</th>
             
                <th>ERP</th>
                <th>Datetime</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
        <td>" . $row["ref"] . "</td>
                
                <td>" . $row["erp"] . "</td>
                <td>" . $row["datetime"] . "</td>
               
              </tr>";
    }

    echo "</table>";
} else {
    echo "No results found.";
}

// Close connection
$conn->close();
?>

